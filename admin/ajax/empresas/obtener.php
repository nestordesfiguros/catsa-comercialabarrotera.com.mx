<?php
session_start();
require_once '../../lib/clsConsultas.php';
$cls = new Consultas();

/**
 * Resolución de ID robusta:
 *  1) Extrae siempre de la URL (HTTP_REFERER o REQUEST_URI) -> prioridad
 *  2) Si no hay en URL, toma el POST
 *  3) Si ambos existen y difieren, se usa el de la URL
 */
function idDesdeUrl(): int
{
    $uri = $_SERVER['HTTP_REFERER'] ?? $_SERVER['REQUEST_URI'] ?? '';
    if ($uri && preg_match('~empresas-(?:editar|timbrado)/(\d+)~i', $uri, $m)) {
        return (int)$m[1];
    }
    return 0;
}

$idUrl = idDesdeUrl();
$idPost = isset($_POST['id']) ? (int)$_POST['id'] : 0;

$id = $idUrl > 0 ? $idUrl : $idPost;

if ($id <= 0) {
    echo json_encode(["ok" => false, "msg" => "ID inválido"]);
    exit;
}

$sql = "SELECT * FROM cat_empresas WHERE id=" . $cls->sanitizar($id, true) . " LIMIT 1";
$rs  = $cls->aplicaQuery($sql);

if (!$rs || $rs->num_rows === 0) {
    echo json_encode(["ok" => false, "msg" => "No encontrado"]);
    exit;
}

echo json_encode(["ok" => true, "data" => mysqli_fetch_assoc($rs)]);
