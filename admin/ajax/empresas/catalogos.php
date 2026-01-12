<?php
session_start();
require_once '../../lib/clsConsultas.php';
$cls = new Consultas();
$tipo = $_POST['tipo'] ?? '';

function tablaExiste(Consultas $cls, string $tabla): bool
{
    $t = $cls->escape($tabla);
    $sql = "SELECT 1 FROM information_schema.tables
          WHERE table_schema = DATABASE() AND table_name = '{$t}' LIMIT 1";
    $rs = $cls->aplicaQuery($sql);
    return ($rs && $rs->num_rows > 0);
}

if ($tipo === 'estados_y_regimen') {
    $estados = [];
    if (tablaExiste($cls, 'estados')) {
        $rsE = $cls->aplicaQuery("SELECT id, nombre FROM estados ORDER BY nombre");
        while ($e = mysqli_fetch_assoc($rsE)) {
            $estados[] = ["id_estado" => (int)$e['id'], "nombre" => $e['nombre']];
        }
    }
    $regimenes = [];
    if (tablaExiste($cls, 'cat_regimen_fiscal')) {
        $rsR = $cls->aplicaQuery("SELECT codigo, descripcion, tipo_persona FROM cat_regimen_fiscal WHERE vigente=1 ORDER BY codigo");
        while ($r = mysqli_fetch_assoc($rsR)) {
            $regimenes[] = ["codigo" => $r['codigo'], "descripcion" => $r['descripcion'], "tipo_persona" => $r['tipo_persona']];
        }
    }
    echo json_encode(["ok" => true, "estados" => $estados, "regimenes" => $regimenes]);
    exit;
}

if ($tipo === 'municipios') {
    $id_estado = intval($_POST['id_estado'] ?? 0);
    if ($id_estado <= 0) {
        echo json_encode(["ok" => true, "municipios" => []]);
        exit;
    }

    $tabla = null;
    $fk = null;
    $idcol = 'id';
    $name = 'nombre';
    if (tablaExiste($cls, 'municipios')) {
        $tabla = 'municipios';
        $fk = 'estado_id';
    } elseif (tablaExiste($cls, 'cat_municipios')) {
        $tabla = 'cat_municipios';
        $fk = 'id_estado';
        $idcol = 'id_municipio';
    }

    if (!$tabla) {
        echo json_encode(["ok" => true, "municipios" => []]);
        exit;
    }

    $sql = "SELECT {$idcol} AS id_municipio, {$name} AS nombre FROM {$tabla} WHERE {$fk}={$id_estado} ORDER BY {$name}";
    $rsM = $cls->aplicaQuery($sql);
    $municipios = [];
    while ($m = mysqli_fetch_assoc($rsM)) {
        $municipios[] = ["id_municipio" => (int)$m['id_municipio'], "nombre" => $m['nombre']];
    }
    echo json_encode(["ok" => true, "municipios" => $municipios]);
    exit;
}

echo json_encode(["ok" => false, "msg" => "Tipo inválido"]);
