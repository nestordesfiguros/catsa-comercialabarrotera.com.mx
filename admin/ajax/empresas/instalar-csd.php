<?php
// ajax/empresas/instalar-csd.php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../lib/clsConsultas.php';
$clsConsulta = new Consultas();

/**
 * Guarda archivos en admin/ajax/timbrado/csd/{id}/ y activa el CSD
 */
try {
    $id = isset($_POST['id_empresa']) ? (int)$_POST['id_empresa'] : 0;
    if ($id <= 0) {
        echo json_encode(['ok' => false, 'msg' => 'ID inválido']);
        exit;
    }

    if (empty($_FILES['cer']['tmp_name']) || empty($_FILES['key']['tmp_name'])) {
        echo json_encode(['ok' => false, 'msg' => 'Sube .cer y .key']);
        exit;
    }
    $pwd = trim((string)($_POST['pwd'] ?? ''));
    if ($pwd === '') {
        echo json_encode(['ok' => false, 'msg' => 'Escribe la contraseña del CSD']);
        exit;
    }

    // Destino
    $dir = __DIR__ . '/../timbrado/csd/' . $id . '/';
    if (!is_dir($dir)) {
        if (!@mkdir($dir, 0775, true) && !is_dir($dir)) {
            echo json_encode(['ok' => false, 'msg' => 'No se pudo crear la carpeta destino']);
            exit;
        }
    }

    // Nombres (conserva original, evitando rutas)
    $cerName = basename($_FILES['cer']['name']);
    $keyName = basename($_FILES['key']['name']);

    $cerDest = $dir . $cerName;
    $keyDest = $dir . $keyName;

    if (!@move_uploaded_file($_FILES['cer']['tmp_name'], $cerDest)) {
        echo json_encode(['ok' => false, 'msg' => 'No se pudo guardar el .cer']);
        exit;
    }
    if (!@move_uploaded_file($_FILES['key']['tmp_name'], $keyDest)) {
        echo json_encode(['ok' => false, 'msg' => 'No se pudo guardar la .key']);
        exit;
    }

    // Desactivar CSDs previos de la empresa
    $clsConsulta->aplicaQuery("UPDATE empresas_csd SET activo=0 WHERE id_empresa={$id}");

    // Insertar nuevo CSD activo
    $tipo = "CER_KEY";
    $rutaCer = $cerName; // guardamos el nombre; en estado se resuelve con la carpeta {id}
    $rutaKey = $keyName;
    $pass = $clsConsulta->escape($pwd);

    $sql = "INSERT INTO empresas_csd
            (id_empresa, tipo, ruta_cer, ruta_key, pass_csd, activo, fecha_instalacion)
            VALUES ({$id}, '{$tipo}', '{$rutaCer}', '{$rutaKey}', '{$pass}', 1, NOW())";
    $clsConsulta->aplicaQuery($sql);

    echo json_encode(['ok' => true, 'msg' => 'CSD instalado y activado correctamente.']);
} catch (Throwable $e) {
    echo json_encode(['ok' => false, 'msg' => $e->getMessage()]);
}
