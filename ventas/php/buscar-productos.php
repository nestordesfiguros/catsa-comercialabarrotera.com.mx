<?php
// php/buscar-productos.php
require_once '../../admin/lib/clsConsultas.php';
$clsConsulta = new Consultas();

header('Content-Type: application/json; charset=utf-8');

$q = isset($_GET['q']) ? trim($_GET['q']) : '';
if ($q === '') {
  echo json_encode([]);
  exit;
}

/* 1) LIKE robusto: elimina comillas que pudiera añadir sanitizar() y escapa comodines */
$term = $clsConsulta->sanitizar($q);
$term = trim($term, "'\"");                  // fuera comillas si sanitizar() las puso
$term = str_replace(['%', '_'], ['\\%', '\\_'], $term);
$like = "%{$term}%";

/* 2) ¿Existe inventarios_reservas? (auto–fallback si no) */
$hasRes = false;
$chk = $clsConsulta->consultaGeneral("
  SELECT COUNT(*) AS n
  FROM information_schema.tables
  WHERE table_schema = DATABASE() AND table_name = 'inventarios_reservas'
");
if (is_array($chk) && isset($chk[1]['n']) && (int)$chk[1]['n'] > 0) {
  $hasRes = true;
}

$reservasJoin = $hasRes
  ? "LEFT JOIN (
        SELECT id_producto, SUM(cantidad) AS reservado
        FROM inventarios_reservas
        WHERE estatus='reservado'
        GROUP BY id_producto
     ) AS res_sum ON res_sum.id_producto = p.id_producto"
  : ""; // sin join si no existe la tabla

$reservadoExpr = $hasRes ? "COALESCE(res_sum.reservado,0)" : "0";

/* 3) Query: suma todos los almacenes (solo positivos) y resta reservas si las hay */
$sql = "
SELECT *
FROM (
  SELECT
    p.id_producto,
    p.clave,
    p.nombre,
    p.descripcion,
    p.presentacion,
    p.precio_venta,
    p.precio_minimo_venta,
    p.precio_sugerido,
    p.precio_maximo_venta,
    p.imagen,
    p.codigo_barras,
    GREATEST(
      COALESCE(inv_sum.total, p.cantidad_disponible) - $reservadoExpr,
      0
    ) AS cantidad_total
  FROM cat_productos p
  LEFT JOIN (
      SELECT id_producto, SUM(CASE WHEN cantidad > 0 THEN cantidad ELSE 0 END) AS total
      FROM inventarios
      GROUP BY id_producto
  ) AS inv_sum ON inv_sum.id_producto = p.id_producto
  $reservasJoin
  WHERE
    p.estado = 'activo'
    AND p.precio_venta > 0 
    AND p.precio_sugerido > 0
    AND (
      p.nombre LIKE '$like'
      OR p.clave LIKE '$like'
      OR p.descripcion LIKE '$like'
      OR p.presentacion LIKE '$like'
      OR CAST(p.codigo_barras AS CHAR) LIKE '$like'
    )
) t
WHERE t.cantidad_total > 0
ORDER BY t.nombre ASC
";

$resultados = $clsConsulta->consultaGeneral($sql);
if (!is_array($resultados) || $clsConsulta->numrows <= 0) {
  echo json_encode([]);
  exit;
}

/* 4) Oferta (opcional) */
foreach ($resultados as &$prod) {
  $idProd = (int)$prod['id_producto'];
  $prod['tiene_oferta']  = false;
  $prod['precio_oferta'] = null;

  $o = $clsConsulta->consultaGeneral("
    SELECT valor_oferta
    FROM mov_ofertas_productos
    WHERE id_producto = $idProd AND valor_oferta > 0
    ORDER BY id DESC
    LIMIT 1
  ");
  if (is_array($o) && isset($o[1]['valor_oferta'])) {
    $prod['tiene_oferta']  = true;
    $prod['precio_oferta'] = (float)$o[1]['valor_oferta'];
  }
}

echo json_encode(array_values($resultados));
