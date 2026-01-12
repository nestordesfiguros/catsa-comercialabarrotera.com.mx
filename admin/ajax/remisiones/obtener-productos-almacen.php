<?php
// ajax/remisiones/obtener-productos-almacen.php
include '../../lib/clsConsultas.php';
$clsConsulta = new Consultas();

// Obtener el almacén seleccionado
$almacenId = isset($_POST['almacenId']) ? (int)$_POST['almacenId'] : 0;

$data_array = [];

// Consulta optimizada
$con = "SELECT
        p.id_producto,
        p.clave,
        p.nombre,
        p.precio_sugerido,
        p.precio_minimo_venta,
        p.precio_venta,
        p.excento,
        c.nombre_categoria,
        IFNULL(i.cantidad, p.cantidad_disponible) as existencia
    FROM cat_productos p
    LEFT JOIN cat_categorias c ON p.id_categoria = c.id_categoria
    LEFT JOIN inventarios i ON p.id_producto = i.id_producto AND i.id_almacen = $almacenId
    WHERE p.estatus = 1
      AND (p.id_almacen = $almacenId OR i.id_almacen = $almacenId)
      AND (i.cantidad > 0 OR p.cantidad_disponible > 0)
    ORDER BY p.nombre";


$rs = $clsConsulta->consultaGeneral($con);

if ($clsConsulta->numrows > 0) {
    foreach ($rs as $val) {
        $precio_sugerido = $val['precio_sugerido'] ?? 0;
        $precio_minimo   = $val['precio_minimo_venta'] ?? 0;
        $existencia      = (int)($val['existencia'] ?? 0);

        $precioFormateado = ($precio_sugerido > 0)
            ? '$' . number_format($precio_sugerido, 2)
            : '<div class="text-danger">N/A</div>';

        // Habilitar botón solo con existencia y precio mínimo válidos
        if ($existencia > 0 && $precio_minimo > 0) {
            $boton = '<div class="text-center">
            <i class="fas fa-plus-circle fa-lg text-success btn-agregar"
               data-id="' . $val['id_producto'] . '"
               data-clave="' . htmlspecialchars($val['clave'] ?? '') . '"
               data-nombre="' . htmlspecialchars($val['nombre'] ?? '') . '"
               data-precio_venta="' . ($precio_sugerido ?: $precio_minimo) . '"
               data-precio_minimo="' . $precio_minimo . '"
               data-excento="' . ($val['excento'] ?? 0) . '"
               data-existencia="' . $existencia . '"
               title="Agregar"
               style="cursor:pointer"></i>
        </div>';
        } else {
            // Botón deshabilitado con explicación
            $motivo = ($existencia <= 0) ? 'Sin existencia' : 'Sin precio mínimo';
            $boton = '<div class="text-center text-muted" title="' . $motivo . '">
            <i class="fas fa-ban fa-lg"></i>
        </div>';
        }

        $data_array[] = array(
            '<div class="text-center">' . htmlspecialchars($val['clave'] ?? '') . '</div>',
            '<div>' . htmlspecialchars($val['nombre'] ?? '') . '</div>',
            '<div class="text-center">' . htmlspecialchars($val['nombre_categoria'] ?? '') . '</div>',
            '<div class="text-end">' . $existencia . '</div>',
            '<div class="text-end">' . $precioFormateado . '</div>',
            $boton
        );
    }
}

echo json_encode(["data" => $data_array]);
