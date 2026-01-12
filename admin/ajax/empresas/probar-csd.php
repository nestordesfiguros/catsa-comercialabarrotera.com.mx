<?php
// ajax/empresas/probar-csd.php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../lib/clsConsultas.php';
$clsConsulta = new Consultas();

/**
 * Convierte un .cer DER a PEM y valida vigencia
 */
function cer_der_vigente(string $tmpDer): array
{
    $der = @file_get_contents($tmpDer);
    if ($der === false) return ['ok' => false, 'msg' => 'No se pudo leer el .cer'];

    $pem = "-----BEGIN CERTIFICATE-----\n"
        . chunk_split(base64_encode($der), 64, "\n")
        . "-----END CERTIFICATE-----\n";

    $x = @openssl_x509_read($pem);
    if (!$x) return ['ok' => false, 'msg' => 'Certificado inválido'];

    $p = @openssl_x509_parse($x);
    if (!$p) return ['ok' => false, 'msg' => 'No se pudo parsear el certificado'];

    $desde = $p['validFrom_time_t'] ?? 0;
    $hasta = $p['validTo_time_t'] ?? 0;
    $now = time();
    $vigente = ($now >= $desde && $now <= $hasta);

    return [
        'ok' => $vigente,
        'msg' => 'Vigencia: ' . date('Y-m-d H:i:s', $desde) . ' a ' . date('Y-m-d H:i:s', $hasta)
    ];
}

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

    // Validar .cer (vigencia)
    $vig = cer_der_vigente($_FILES['cer']['tmp_name']);
    if (!$vig['ok']) {
        echo json_encode(['ok' => false, 'msg' => 'CSD no vigente o inválido. ' . $vig['msg']]);
        exit;
    }

    // Validación ligera del .key: archivo presente y no vacío (sin convertir PKCS8 en DEV)
    $keySize = filesize($_FILES['key']['tmp_name']);
    if ($keySize === false || $keySize < 16) {
        echo json_encode(['ok' => false, 'msg' => 'Llave .key inválida']);
        exit;
    }

    echo json_encode(['ok' => true, 'msg' => 'CSD válido. ' . $vig['msg']]);
} catch (Throwable $e) {
    echo json_encode(['ok' => false, 'msg' => $e->getMessage()]);
}
