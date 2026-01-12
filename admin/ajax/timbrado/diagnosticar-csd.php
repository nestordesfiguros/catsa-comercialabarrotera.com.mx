<?php

/**
 * admin/ajax/timbrado/diagnosticar-csd.php
 *
 * MODOS:
 * - probar_archivos: recibe PFX o CER+KEY por $_FILES + pass, y valida SIN instalar.
 *      Campos:
 *          modo = 'probar_archivos'
 *          tipo = 'pfx' | 'cerkey'
 *          pass = <string>
 *          pfx  = file (si tipo = pfx)
 *          cer  = file (si tipo = cerkey)
 *          key  = file (si tipo = cerkey)
 *
 * - probar_instalado (default): valida los PEM ya instalados en /csd
 *      Archivos esperados:
 *          ./csd/csd.cer.pem
 *          ./csd/csd.key.pem
 *      Opcionalmente admite pass via POST (si tu key PEM estuviera cifrada):
 *          pass = <string>
 */

// Si tu openssl.exe NO está en PATH, descomenta y ajusta la siguiente constante:
// define('OPENSSL_BIN', 'C:\\OpenSSL-Win64\\bin\\openssl.exe');

header('Content-Type: application/json; charset=utf-8');

try {
    if (!extension_loaded('openssl')) {
        throw new Exception('Extensión OpenSSL no habilitada en PHP.');
    }

    $modo = $_POST['modo'] ?? 'probar_instalado';

    if ($modo === 'probar_archivos') {
        $tipo = $_POST['tipo'] ?? '';
        $pass = $_POST['pass'] ?? '';

        if ($tipo === 'pfx') {
            // ----- PFX -----
            if (!isset($_FILES['pfx']) || $_FILES['pfx']['error'] !== UPLOAD_ERR_OK) {
                throw new Exception('No se recibió el archivo .pfx');
            }

            $pkcs12 = file_get_contents($_FILES['pfx']['tmp_name']);
            $certs = [];
            if (!@openssl_pkcs12_read($pkcs12, $certs, $pass)) {
                throw new Exception('PFX inválido o contraseña incorrecta.');
            }

            $cerPem = $certs['cert'] ?? null;
            $keyPem = $certs['pkey'] ?? null;
            if (!$cerPem || !$keyPem) {
                throw new Exception('No fue posible extraer CERT y KEY del PFX.');
            }

            $x509 = @openssl_x509_read($cerPem);
            $pkey = @openssl_pkey_get_private($keyPem);
            if (!$x509 || !$pkey) {
                throw new Exception('Certificado o llave privada inválidos.');
            }

            $out = buildReport($x509, $pkey);
            echo json_encode(['success' => true] + $out);
            exit;
        }

        if ($tipo === 'cerkey') {
            // ----- CER -----
            if (!isset($_FILES['cer']) || $_FILES['cer']['error'] !== UPLOAD_ERR_OK) {
                throw new Exception('No se recibió el archivo .cer');
            }
            $cerRaw = file_get_contents($_FILES['cer']['tmp_name']);
            $cerPem = ensurePemCert($cerRaw);           // DER -> PEM si es necesario
            $x509   = @openssl_x509_read($cerPem);
            if (!$x509) {
                throw new Exception('El .cer no es válido (DER corrupto o no corresponde a un X.509).');
            }

            // ----- KEY -----
            if (!isset($_FILES['key']) || $_FILES['key']['error'] !== UPLOAD_ERR_OK) {
                throw new Exception('No se recibió el archivo .key');
            }

            $keyRaw = file_get_contents($_FILES['key']['tmp_name']);
            if (isPem($keyRaw)) {
                // KEY ya en PEM (cifrada o no)
                $pkey = @openssl_pkey_get_private($keyRaw, $pass);
                if (!$pkey) {
                    // intenta sin pass por si estuviera sin cifrar
                    $pkey = @openssl_pkey_get_private($keyRaw);
                }
                if (!$pkey) throw new Exception('La KEY en PEM no pudo abrirse (¿contraseña correcta?).');
            } else {
                // KEY en DER → convertir a PEM con openssl CLI (pkcs8 y, si falla, rsa)
                list($pkey, $log) = convertDerKeyToPemAndLoad($_FILES['key']['tmp_name'], $pass);
                if (!$pkey) {
                    throw new Exception(
                        "La KEY está en DER y no pudo convertirse a PEM.\n" .
                            "Revisa la contraseña y que OpenSSL CLI esté disponible (ajusta OPENSSL_BIN si es necesario).\n" .
                            "Log de OpenSSL:\n" . $log
                    );
                }
            }

            $out = buildReport($x509, $pkey);
            echo json_encode(['success' => true] + $out);
            exit;
        }

        throw new Exception('Tipo inválido para probar_archivos (usa pfx o cerkey).');
    }

    // ===== MODO: probar_instalado =====
    $baseDir    = __DIR__ . '/csd';
    $cerPemPath = $baseDir . '/csd.cer.pem';
    $keyPemPath = $baseDir . '/csd.key.pem';

    if (!file_exists($cerPemPath) || !file_exists($keyPemPath)) {
        throw new Exception('Faltan PEM instalados en: ' . $baseDir . ' (csd.cer.pem / csd.key.pem)');
    }

    $cerPem = file_get_contents($cerPemPath);
    $keyPem = file_get_contents($keyPemPath);

    $x509 = @openssl_x509_read($cerPem);
    if (!$x509) throw new Exception('El certificado instalado (csd.cer.pem) no es válido.');

    $pass = $_POST['pass'] ?? null;
    $pkey = @openssl_pkey_get_private($keyPem, $pass);
    if (!$pkey) {
        // intenta sin pass por si la key PEM instalada no está cifrada
        $pkey = @openssl_pkey_get_private($keyPem);
    }
    if (!$pkey) throw new Exception('La llave instalada (csd.key.pem) no es válida o requiere pass.');

    $out = buildReport($x509, $pkey);
    echo json_encode(['success' => true] + $out);
    exit;
} catch (Exception $e) {
    echo json_encode(['success' => false, 'msg' => $e->getMessage()]);
    exit;
}

/* ============================================================
 * Helpers
 * ============================================================ */

/** Detecta si un texto ya está en PEM (BEGIN/END). */
function isPem($txt)
{
    return (bool)preg_match('/-----BEGIN [^-]+-----/i', $txt);
}

/** Convierte un .cer en DER (binario) a PEM (texto) si hace falta. */
function ensurePemCert($cerRaw)
{
    if (isPem($cerRaw)) return $cerRaw;
    $b64 = chunk_split(base64_encode($cerRaw), 64, "\n");
    return "-----BEGIN CERTIFICATE-----\n{$b64}-----END CERTIFICATE-----\n";
}

/**
 * Busca openssl.exe:
 *  1) Constante OPENSSL_BIN (si está definida)
 *  2) PATH (simplemente "openssl")
 *  3) Varias rutas comunes en Windows
 */
function resolveOpensslBin()
{
    if (defined('OPENSSL_BIN') && constant('OPENSSL_BIN')) {
        return constant('OPENSSL_BIN');
    }
    $candidates = [
        'openssl', // PATH
        'C:\\OpenSSL-Win64\\bin\\openssl.exe',
        'C:\\Program Files\\OpenSSL-Win64\\bin\\openssl.exe',
        'C:\\Program Files\\Git\\usr\\bin\\openssl.exe',
        'C:\\wamp64\\bin\\apache\\apache2.4.54\\bin\\openssl.exe',
        'C:\\wamp64\\bin\\apache\\apache2.4.58\\bin\\openssl.exe',
        'C:\\wamp64\\bin\\php\\php8.2.0\\extras\\ssl\\openssl.exe',
    ];
    foreach ($candidates as $bin) {
        $out = [];
        $code = 0;
        // Usa escapeshellarg para respetar espacios en ruta
        @exec(escapeshellarg($bin) . ' version 2>&1', $out, $code);
        if ($code === 0) return $bin;
    }
    return 'openssl';
}

/**
 * Convierte una KEY en DER (PKCS#8 o PKCS#1) a PEM usando OpenSSL CLI.
 * Usa -passin file: para evitar problemas de comillas y espacios.
 * Devuelve array [resource|false $pkey, string $log].
 */
function convertDerKeyToPemAndLoad($derPath, $pass)
{
    $bin = resolveOpensslBin();

    // Temporales
    $passFile = tempnam(sys_get_temp_dir(), 'pass_');
    file_put_contents($passFile, (string)$pass);

    $tmpPem    = tempnam(sys_get_temp_dir(), 'keypem_');
    $tmpPemOut = $tmpPem . '.pem';

    $logs = [];

    // Intento 1: PKCS8 DER
    $cmd1 = escapeshellarg($bin)
        . ' pkcs8 -inform DER -in ' . escapeshellarg($derPath)
        . ' -passin file:' . escapeshellarg($passFile)
        . ' -out ' . escapeshellarg($tmpPemOut) . ' 2>&1';
    $out = [];
    $code = 0;
    @exec($cmd1, $out, $code);
    $logs[] = "[pkcs8] code={$code}\n" . implode("\n", $out);

    $converted = ($code === 0 && file_exists($tmpPemOut) && filesize($tmpPemOut) > 0);

    // Intento 2: PKCS#1 DER (rsa) si el 1ro falló
    if (!$converted) {
        $cmd2 = escapeshellarg($bin)
            . ' rsa -inform DER -in ' . escapeshellarg($derPath)
            . ' -passin file:' . escapeshellarg($passFile)
            . ' -out ' . escapeshellarg($tmpPemOut) . ' 2>&1';
        $out2 = [];
        $code2 = 0;
        @exec($cmd2, $out2, $code2);
        $logs[] = "[rsa] code={$code2}\n" . implode("\n", $out2);
        $converted = ($code2 === 0 && file_exists($tmpPemOut) && filesize($tmpPemOut) > 0);
    }

    if (!$converted) {
        @unlink($passFile);
        @unlink($tmpPem);
        @unlink($tmpPemOut);
        return [false, implode("\n---\n", $logs)];
    }

    // Carga la key PEM resultante (con pass por si quedó protegida)
    $pem  = file_get_contents($tmpPemOut);
    $pkey = @openssl_pkey_get_private($pem, $pass);
    if (!$pkey) {
        // intenta sin pass (por si quedó exportada sin cifrar)
        $pkey = @openssl_pkey_get_private($pem);
    }

    @unlink($passFile);
    @unlink($tmpPem);
    @unlink($tmpPemOut);

    if (!$pkey) {
        $logs[] = "No se pudo abrir la KEY resultante como PEM con/ sin pass.";
        return [false, implode("\n---\n", $logs)];
    }

    return [$pkey, implode("\n---\n", $logs)];
}

/**
 * Construye reporte (RFC, serie, vigencia y prueba de firma/verificación)
 * Devuelve array con:
 *  - rfc_cert, nocert, valid_from, valid_to
 *  - sign_ok, verify_ok, pair_ok
 */
function buildReport($x509, $pkey)
{
    $info = @openssl_x509_parse($x509);

    $rfc = null;
    if (!empty($info['subject'])) {
        $s = $info['subject'];
        if (!empty($s['x500UniqueIdentifier'])) $rfc = $s['x500UniqueIdentifier'];
        if (!$rfc && !empty($s['UID']))         $rfc = $s['UID'];
        if (!$rfc && !empty($s['serialNumber'])) $rfc = $s['serialNumber'];
    }

    $serial    = $info['serialNumberHex'] ?? ($info['serialNumber'] ?? null);
    $validFrom = !empty($info['validFrom_time_t']) ? gmdate('Y-m-d H:i:s', $info['validFrom_time_t']) : null;
    $validTo   = !empty($info['validTo_time_t'])   ? gmdate('Y-m-d H:i:s', $info['validTo_time_t'])   : null;

    // Prueba de firma/verificación
    $data = 'probe-' . uniqid('', true);
    $signature = '';
    $sign_ok = @openssl_sign($data, $signature, $pkey, OPENSSL_ALGO_SHA256);
    $verify_ok = false;
    if ($sign_ok) {
        $pub = @openssl_pkey_get_public($x509);
        $verify_ok = (openssl_verify($data, $signature, $pub, OPENSSL_ALGO_SHA256) === 1);
    }

    return [
        'rfc_cert'   => $rfc,
        'nocert'     => $serial,
        'valid_from' => $validFrom,
        'valid_to'   => $validTo,
        'sign_ok'    => (bool)$sign_ok,
        'verify_ok'  => (bool)$verify_ok,
        'pair_ok'    => (bool)$verify_ok
    ];
}
