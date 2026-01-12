<?php
// ajax/empresas/guardar-csd.php
header('Content-Type: application/json; charset=utf-8');
@ini_set('display_errors', 0);

session_start();
require_once __DIR__ . '/../../lib/clsConsultas.php';
$clsConsulta = new Consultas();

$id = (int)($_POST['id_empresa'] ?? 0);
if ($id <= 0) {
    echo json_encode(['ok' => false, 'msg' => 'ID de empresa inválido']);
    exit;
}

$pwd = trim((string)($_POST['pwd'] ?? ''));
if ($pwd === '') {
    echo json_encode(['ok' => false, 'msg' => 'Escribe la contraseña del CSD']);
    exit;
}

if (empty($_FILES['cer']['tmp_name']) || empty($_FILES['key']['tmp_name'])) {
    echo json_encode(['ok' => false, 'msg' => 'Sube el CER y la KEY']);
    exit;
}

// Carpeta destino: admin/ajax/timbrado/csd/{id_empresa}/
$dirDestino = __DIR__ . '/../timbrado/csd/' . $id;
if (!is_dir($dirDestino)) @mkdir($dirDestino, 0775, true);

$cerName = basename($_FILES['cer']['name']);
$keyName = basename($_FILES['key']['name']);

$rutaCerRel = "timbrado/csd/{$id}/{$cerName}";
$rutaKeyRel = "timbrado/csd/{$id}/{$keyName}";

$cerAbs = __DIR__ . '/../' . $rutaCerRel;
$keyAbs = __DIR__ . '/../' . $rutaKeyRel;

if (!@move_uploaded_file($_FILES['cer']['tmp_name'], $cerAbs)) {
    echo json_encode(['ok' => false, 'msg' => 'No se pudo guardar el CER']);
    exit;
}
if (!@move_uploaded_file($_FILES['key']['tmp_name'], $keyAbs)) {
    echo json_encode(['ok' => false, 'msg' => 'No se pudo guardar la KEY']);
    exit;
}

// Registrar subida (SIN activar). En tu tabla: tipo ENUM('CER_KEY','PFX')
$tipoSQL = $clsConsulta->sanitizar('CER_KEY');
$cerSQL  = $clsConsulta->sanitizar($rutaCerRel);
$keySQL  = $clsConsulta->sanitizar($rutaKeyRel);
$pwdSQL  = $clsConsulta->sanitizar($pwd);

$q = "INSERT INTO empresas_csd
      (id_empresa, tipo, ruta_cer, ruta_key, pass_csd, activo, fecha_instalacion, ultimo_test)
      VALUES ({$id}, {$tipoSQL}, {$cerSQL}, {$keySQL}, {$pwdSQL}, 0, NOW(), NOW())";

$clsConsulta->aplicaQuery($q);

echo json_encode(['ok' => true, 'msg' => 'Archivos recibidos y verificados. Ahora puedes INSTALAR el CSD.']);
