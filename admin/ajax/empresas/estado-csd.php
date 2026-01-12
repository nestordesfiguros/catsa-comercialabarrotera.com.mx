<?php
// ajax/empresas/estado-csd.php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../lib/clsConsultas.php';
$clsConsulta = new Consultas();

try {
    $id = isset($_POST['id_empresa']) ? (int)$_POST['id_empresa'] : 0;
    if ($id <= 0) {
        echo json_encode(['ok' => false, 'msg' => 'ID inválido']);
        exit;
    }

    // Último registro del CSD de la empresa
    $sql = "SELECT id_csd, ruta_cer, ruta_key, activo
            FROM empresas_csd
            WHERE id_empresa = {$id}
            ORDER BY id_csd DESC
            LIMIT 1";
    $rowset = $clsConsulta->consultaGeneral($sql);
    $row = $rowset[1] ?? null;

    $cer = false;
    $key = false;
    $activo = false;
    if ($row) {
        $cer = !empty($row['ruta_cer']);
        $key = !empty($row['ruta_key']);
        $activo = ((int)$row['activo'] === 1);
        // Opcional: verificar existencia real de archivo
        $cer = $cer && file_exists(__DIR__ . "/../timbrado/csd/{$id}/" . basename($row['ruta_cer']));
        $key = $key && file_exists(__DIR__ . "/../timbrado/csd/{$id}/" . basename($row['ruta_key']));
    }

    echo json_encode(['ok' => true, 'cer' => $cer, 'key' => $key, 'activo' => $activo], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    echo json_encode(['ok' => false, 'msg' => $e->getMessage()]);
}
