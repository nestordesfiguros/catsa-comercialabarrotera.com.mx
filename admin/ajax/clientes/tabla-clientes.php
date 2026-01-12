<?php
// admin/ajax/clientes/tabla-clientes.php
session_start();
header('Content-Type: application/json');

include '../../lib/clsConsultas.php';
$clsConsulta = new Consultas();

function safe_html($value)
{
    return $value !== null ? htmlspecialchars((string)$value, ENT_QUOTES) : '';
}

// Empresa actual
$idEmpresa = 0;
if (isset($_SESSION['id_empresa'])) $idEmpresa = (int)$_SESSION['id_empresa'];
elseif (isset($_SESSION['empresa'])) $idEmpresa = (int)$_SESSION['empresa'];

$whereEmpresa = "";
if ($idEmpresa > 0) {
    // compatibilidad legacy
    $whereEmpresa = " AND (c.id_empresa = {$idEmpresa} OR c.id_empresa IS NULL OR c.id_empresa = 0)";
}

// ====== Nombres de listas desde cat_productos (fallback Lista X) ======
$listaNombres = [
    1 => 'Lista 1',
    2 => 'Lista 2',
    3 => 'Lista 3',
    4 => 'Lista 4',
    5 => 'Lista 5',
];

if ($idEmpresa > 0) {
    $sqlNombres = "
        SELECT
            p.precio01_nombre, p.precio02_nombre, p.precio03_nombre, p.precio04_nombre, p.precio05_nombre
        FROM cat_almacenes a
        INNER JOIN cat_productos p ON p.id_almacen = a.id
        WHERE a.id_empresa = {$idEmpresa}
          AND p.estatus = 1
        ORDER BY a.almacen ASC, p.id_producto ASC
        LIMIT 1
    ";
    $rsN = $clsConsulta->consultaGeneral($sqlNombres);
    if ($clsConsulta->numrows > 0) {
        $row = $rsN[1];
        for ($i = 1; $i <= 5; $i++) {
            $k = 'precio0' . $i . '_nombre';
            $v = isset($row[$k]) ? trim((string)$row[$k]) : '';
            if ($v !== '' && strtoupper($v) !== 'NULL' && !preg_match('/^precio0?' . $i . '$/i', $v)) {
                $listaNombres[$i] = $v;
            }
        }
    }
}

// ====== Clientes (mostrar activos e inactivos) ======
$data_array = [];

$con = "
    SELECT
        c.id, c.no_cliente, c.razon_social, c.nombre_comercial, c.contacto, c.mapa, c.estatus, c.lista_precios
    FROM cat_clientes c
    WHERE 1=1
      AND (c.deleted_at IS NULL)
      {$whereEmpresa}
    ORDER BY c.id DESC
";

$rs = $clsConsulta->consultaGeneral($con);

if ($clsConsulta->numrows > 0) {
    foreach ($rs as $idx => $val) {
        $id = (int)$val['id'];
        $estatusActual = (int)($val['estatus'] ?? 0);
        $no_cliente = safe_html($val['no_cliente'] ?? '');

        $razon = trim((string)($val['razon_social'] ?? ''));
        $razon = ($razon === '' || strtoupper($razon) === 'NULL') ? '' : $razon;

        $nomCom = trim((string)($val['nombre_comercial'] ?? ''));
        $nomCom = ($nomCom === '' || strtoupper($nomCom) === 'NULL') ? '' : $nomCom;

        $contacto = trim((string)($val['contacto'] ?? ''));
        $contacto = ($contacto === '' || strtoupper($contacto) === 'NULL') ? '' : $contacto;

        $lista = isset($val['lista_precios']) ? (int)$val['lista_precios'] : 0;
        $listaLabel = ($lista >= 1 && $lista <= 5) ? $listaNombres[$lista] : 'Sin asignar';

        $estatusIcon = ($estatusActual === 1)
            ? '<i style="cursor:pointer;" class="fas fa-check-circle fa-lg text-success" onClick="valorEstatus(' . $id . ',' . $estatusActual . ')"></i>'
            : '<i style="cursor:pointer;" class="fas fa-ban fa-lg text-danger" onClick="valorEstatus(' . $id . ',' . $estatusActual . ')"></i>';

        $razon_social = '<div>' . safe_html($razon) . '</div>';
        $nombre_comercial = '<div>' . safe_html($nomCom) . '</div>';
        $lista_precio = '<div class="text-center"><span class="badge bg-light text-dark border">' . safe_html($listaLabel) . '</span></div>';
        $contacto_html = '<div>' . safe_html($contacto) . '</div>';

        $editar = '<div class="text-center"><i class="fas fa-edit fa-lg text-info" style="cursor:pointer;" onclick="editar(' . $id . ');"></i></div>';

        $mapaVal = trim((string)($val['mapa'] ?? ''));
        if ($mapaVal !== '' && strtoupper($mapaVal) !== 'NULL') {
            $mapa = '<div class="text-center"><a href="' . safe_html($mapaVal) . '" target="_blank" class="text-info"><i class="fas fa-map-marked-alt fa-lg"></i></a></div>';
        } else {
            $mapa = '<div class="text-center text-muted"><i class="fas fa-map-marked-alt fa-lg"></i></div>';
        }

        $estatus = '<div class="text-center">' . $estatusIcon . '</div>';

        $data_array[] = [
            $no_cliente,
            $razon_social,
            $nombre_comercial,
            $lista_precio,
            $contacto_html,
            $editar,
            $mapa,
            $estatus
        ];
    }
}

echo json_encode(["data" => $data_array]);
