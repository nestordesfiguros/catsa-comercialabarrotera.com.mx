<?php

/**
 * admin/ajax/timbrado/instalar-csd.php
 *
 * Instala el CSD en ./csd como:
 *   - csd.cer.pem
 *   - csd.key.pem (SIN contraseña)
 *
 * Soporta:
 *  - tipo=pfx    -> $_FILES['pfx'] + pass
 *  - tipo=cerkey -> $_FILES['cer'], $_FILES['key'] + pass
 *
 * Respuesta JSON:
 *  { success: bool, msg: string, rfc_cert?: string, nocert?: string, valid_from?: string, valid_to?: string }
 */

// Si tu openssl.exe no está en PATH, descomenta y AJUSTA:
define('OPENSSL_BIN', 'C:\\Program Files\\OpenSSL-Win64\\bin\\openssl.exe');

// MUY IMPORTANTE: apunta OPENSSL_MODULES a la carpeta donde está legacy.dll
putenv('OPENSSL_MODULES=C:\\Program Files\\OpenSSL-Win64\\bin');


header('Content-Type: application/json; charset=utf-8');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Usa POST para instalar el CSD.');
    }
    if (!extension_loaded('openssl')) {
        throw new Exception('Extensión OpenSSL no habilitada en PHP.');
    }

    $tipo = $_POST['tipo'] ?? '';
    $pass = $_POST['pass'] ?? '';

    if ($tipo !== 'pfx' && $tipo !== 'cerkey') {
        throw new Exception('Tipo inválido. Usa "pfx" o "cerkey".');
    }

    // Carpeta destino
    $baseDir = __DIR__ . '/csd';
    if (!is_dir($baseDir) && !@mkdir($baseDir, 0775, true)) {
        throw new Exception('No se pudo crear carpeta destino: ' . $baseDir);
    }
    $cerPemPath = $baseDir . '/csd.cer.pem';
    $keyPemPath = $baseDir . '/csd.key.pem';

    // Backups (opcionales)
    if (file_exists($cerPemPath)) @copy($cerPemPath, $cerPemPath . '.bak');
    if (file_exists($keyPemPath)) @copy($keyPemPath, $keyPemPath . '.bak');

    // ========= PFX =========
    if ($tipo === 'pfx') {
        if (!isset($_FILES['pfx']) || $_FILES['pfx']['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('No se recibió el archivo .pfx');
        }

        $pfxTmp = $_FILES['pfx']['tmp_name'];
        $pkcs12 = @file_get_contents($pfxTmp);
        if ($pkcs12 === false || $pkcs12 === '') {
            throw new Exception('No se pudo leer el archivo .pfx');
        }

        $cerPem = null;
        $keyPem = null;
        $logs   = [];

        // 1) Intento directo con PHP
        $certs = [];
        if (@openssl_pkcs12_read($pkcs12, $certs, $pass)) {
            $cerPem = $certs['cert'] ?? null;
            $keyPem = $certs['pkey'] ?? null;
        }

        // 2) Fallback CLI si hace falta
        if (!$cerPem || !$keyPem) {
            $bin = resolveOpensslBin();
            $outCer = tempnam(sys_get_temp_dir(), 'cer_') . '.pem';
            $outKey = tempnam(sys_get_temp_dir(), 'key_') . '.pem';

            $run = function ($cmd, $outfile) use (&$logs) {
                $o = [];
                $c = 0;
                @exec($cmd, $o, $c);
                $logs[] = "[cmd] {$cmd}\n[code] {$c}\n" . implode("\n", $o);
                return ($c === 0 && file_exists($outfile) && filesize($outfile) > 0) ? [true, implode("\n", $o)]
                    : [false, implode("\n", $o)];
            };

            // a) SIN providers
            $cmdCert_plain = escapeshellarg($bin) . ' pkcs12 -in ' . escapeshellarg($pfxTmp)
                . ' -passin ' . escapeshellarg('pass:' . $pass)
                . ' -clcerts -nokeys -out ' . escapeshellarg($outCer) . ' 2>&1';
            $cmdKey_plain  = escapeshellarg($bin) . ' pkcs12 -in ' . escapeshellarg($pfxTmp)
                . ' -passin ' . escapeshellarg('pass:' . $pass)
                . ' -nocerts -nodes -out ' . escapeshellarg($outKey) . ' 2>&1';

            list($okC, $out1C) = $run($cmdCert_plain, $outCer);
            list($okK, $out1K) = $run($cmdKey_plain,  $outKey);

            $needLegacy = (strpos($out1C, 'unsupported') !== false || strpos($out1K, 'unsupported') !== false
                || strpos($out1C, 'inner_evp_generic_fetch') !== false || strpos($out1K, 'inner_evp_generic_fetch') !== false
                || strpos($out1C, 'RC2-40-CBC') !== false || strpos($out1K, 'RC2-40-CBC') !== false);

            if (!($okC && $okK) && $needLegacy) {
                // b) con -legacy (OpenSSL 3)
                @unlink($outCer);
                @unlink($outKey);

                $cmdCert_legacy = escapeshellarg($bin) . ' pkcs12 -legacy -in ' . escapeshellarg($pfxTmp)
                    . ' -passin ' . escapeshellarg('pass:' . $pass)
                    . ' -clcerts -nokeys -out ' . escapeshellarg($outCer) . ' 2>&1';
                $cmdKey_legacy  = escapeshellarg($bin) . ' pkcs12 -legacy -in ' . escapeshellarg($pfxTmp)
                    . ' -passin ' . escapeshellarg('pass:' . $pass)
                    . ' -nocerts -nodes -out ' . escapeshellarg($outKey) . ' 2>&1';

                list($okC2, $out2C) = $run($cmdCert_legacy, $outCer);
                list($okK2, $out2K) = $run($cmdKey_legacy,  $outKey);

                $invalidLegacy = (strpos($out2C, 'Invalid command') !== false || strpos($out2K, 'Invalid command') !== false);

                if (!($okC2 && $okK2) && $invalidLegacy) {
                    // c) con providers explícitos (OpenSSL 3 con providers)
                    @unlink($outCer);
                    @unlink($outKey);

                    $cmdCert_prov = escapeshellarg($bin) . ' -provider legacy -provider default'
                        . ' pkcs12 -in ' . escapeshellarg($pfxTmp)
                        . ' -passin ' . escapeshellarg('pass:' . $pass)
                        . ' -clcerts -nokeys -out ' . escapeshellarg($outCer) . ' 2>&1';
                    $cmdKey_prov  = escapeshellarg($bin) . ' -provider legacy -provider default'
                        . ' pkcs12 -in ' . escapeshellarg($pfxTmp)
                        . ' -passin ' . escapeshellarg('pass:' . $pass)
                        . ' -nocerts -nodes -out ' . escapeshellarg($outKey) . ' 2>&1';

                    list($okC3, $out3C) = $run($cmdCert_prov, $outCer);
                    list($okK3, $out3K) = $run($cmdKey_prov,  $outKey);

                    if ($okC3 && $okK3) {
                        $cerPem = file_get_contents($outCer);
                        $keyPem = file_get_contents($outKey);
                    }
                } else if ($okC2 && $okK2) {
                    $cerPem = file_get_contents($outCer);
                    $keyPem = file_get_contents($outKey);
                }
            } else if ($okC && $okK) {
                $cerPem = file_get_contents($outCer);
                $keyPem = file_get_contents($outKey);
            }

            @unlink($outCer);
            @unlink($outKey);

            if (!$cerPem || !$keyPem) {
                throw new Exception("PFX inválido o contraseña incorrecta. (FALLÓ pkcs12_read y CLI)\n" . implode("\n---\n", $logs));
            }
        }

        // Guardar normalizados
        if (@file_put_contents($cerPemPath, normalizePem($cerPem)) === false) {
            throw new Exception('No se pudo escribir csd.cer.pem');
        }
        if (@file_put_contents($keyPemPath, normalizePem($keyPem)) === false) {
            throw new Exception('No se pudo escribir csd.key.pem');
        }

        // Verificación final del par
        $x509 = @openssl_x509_read($cerPem);
        $pkey = @openssl_pkey_get_private($keyPem) ?: @openssl_pkey_get_private($keyPem, $pass);
        if (!$x509 || !$pkey) throw new Exception('Certificado o llave no válidos tras guardar.');
        $report = buildReport($x509, $pkey);
        if (!$report['pair_ok']) throw new Exception('El CERT y la KEY del PFX no corresponden al mismo par.');

        @chmod($cerPemPath, 0640);
        @chmod($keyPemPath, 0640);

        echo json_encode([
            'success'    => true,
            'msg'        => 'CSD instalado desde PFX.',
            'rfc_cert'   => $report['rfc_cert'],
            'nocert'     => $report['nocert'],
            'valid_from' => $report['valid_from'],
            'valid_to'   => $report['valid_to']
        ]);
        exit;
    }

    // ========= CER + KEY =========
    if ($tipo === 'cerkey') {
        if (!isset($_FILES['cer']) || $_FILES['cer']['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('No se recibió el archivo .cer');
        }
        if (!isset($_FILES['key']) || $_FILES['key']['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('No se recibió el archivo .key');
        }

        // CER: DER -> PEM si hace falta
        $cerRaw = file_get_contents($_FILES['cer']['tmp_name']);
        $cerPem = ensurePemCert($cerRaw);
        $x509   = @openssl_x509_read($cerPem);
        if (!$x509) throw new Exception('El .cer no es válido (DER corrupto o no corresponde a X.509).');

        // KEY:
        $keyRaw = file_get_contents($_FILES['key']['tmp_name']);
        $keyPemToSave = null;

        if (isPem($keyRaw)) {
            $isEncrypted = (strpos($keyRaw, 'ENCRYPTED') !== false);
            if ($isEncrypted) {
                list($pemOut, $log) = stripPemPassWithCli($keyRaw, $pass);
                if ($pemOut === false) {
                    throw new Exception("No se pudo quitar la contraseña de la KEY PEM.\nLog:\n" . $log);
                }
                $keyPemToSave = $pemOut;
            } else {
                $keyPemToSave = $keyRaw;
            }
        } else {
            list($pkeyRes, $pemOut, $log) = convertDerKeyToPemAndLoad($_FILES['key']['tmp_name'], $pass);
            if (!$pkeyRes || !$pemOut) {
                throw new Exception(
                    "La KEY está en DER y no pudo convertirse a PEM.\n" .
                        "Revisa la contraseña y que OpenSSL CLI esté disponible.\n" .
                        "Log de OpenSSL:\n" . $log
                );
            }
            $keyPemToSave = $pemOut; // ya sin pass
        }

        // Guardar archivos destino
        if (@file_put_contents($cerPemPath, normalizePem($cerPem)) === false) {
            throw new Exception('No se pudo escribir csd.cer.pem');
        }
        if (@file_put_contents($keyPemPath, normalizePem($keyPemToSave)) === false) {
            throw new Exception('No se pudo escribir csd.key.pem');
        }

        // Verificación final del par
        $pkey = @openssl_pkey_get_private($keyPemToSave) ?: @openssl_pkey_get_private($keyPemToSave, $pass);
        if (!$pkey) throw new Exception('La KEY guardada no pudo abrirse.');
        $report = buildReport($x509, $pkey);
        if (!$report['pair_ok']) throw new Exception('El CERT y la KEY no corresponden al mismo par.');

        @chmod($cerPemPath, 0640);
        @chmod($keyPemPath, 0640);

        echo json_encode([
            'success'    => true,
            'msg'        => 'CSD instalado desde CER+KEY.',
            'rfc_cert'   => $report['rfc_cert'],
            'nocert'     => $report['nocert'],
            'valid_from' => $report['valid_from'],
            'valid_to'   => $report['valid_to']
        ]);
        exit;
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'msg' => $e->getMessage()]);
    exit;
}

/* ====================== Helpers ====================== */

function normalizePem($pem)
{
    $pem = preg_replace("/\r\n|\r|\n/", "\n", $pem);
    if (substr($pem, -1) !== "\n") $pem .= "\n";
    return $pem;
}
function isPem($txt)
{
    return (bool)preg_match('/-----BEGIN [^-]+-----/i', $txt);
}
function ensurePemCert($cerRaw)
{
    if (isPem($cerRaw)) return $cerRaw;
    $b64 = chunk_split(base64_encode($cerRaw), 64, "\n");
    return "-----BEGIN CERTIFICATE-----\n{$b64}-----END CERTIFICATE-----\n";
}
function resolveOpensslBin()
{
    if (defined('OPENSSL_BIN') && constant('OPENSSL_BIN')) return constant('OPENSSL_BIN');
    $candidates = [
        'openssl',
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
        @exec(escapeshellarg($bin) . ' version 2>&1', $out, $code);
        if ($code === 0) return $bin;
    }
    return 'openssl';
}

/** Quita pass de KEY PEM usando CLI. Devuelve [pemSinPass|false, log] */
function stripPemPassWithCli($pemIn, $pass)
{
    $bin = resolveOpensslBin();
    $in  = tempnam(sys_get_temp_dir(), 'inpem_');
    file_put_contents($in, $pemIn);
    $pf  = tempnam(sys_get_temp_dir(), 'pass_');
    file_put_contents($pf, (string)$pass);
    $out = tempnam(sys_get_temp_dir(), 'outpem_') . '.pem';

    $cmd = escapeshellarg($bin)
        . ' pkey -in ' . escapeshellarg($in)
        . ' -passin file:' . escapeshellarg($pf)
        . ' -out ' . escapeshellarg($out) . ' 2>&1';

    $log = [];
    $code = 0;
    @exec($cmd, $log, $code);
    $pemOut = (file_exists($out) && filesize($out) > 0) ? file_get_contents($out) : false;

    @unlink($in);
    @unlink($pf);
    @unlink($out);
    return [$pemOut, "[pkey strip] code={$code}\n" . implode("\n", $log)];
}

/** Convierte KEY DER (PKCS#8/PKCS#1) a PEM sin pass vía CLI. */
function convertDerKeyToPemAndLoad($derPath, $pass)
{
    $bin = resolveOpensslBin();
    $pf  = tempnam(sys_get_temp_dir(), 'pass_');
    file_put_contents($pf, (string)$pass);
    $tmp = tempnam(sys_get_temp_dir(), 'keypem_');
    $out = $tmp . '.pem';

    $logs = [];

    $cmd1 = escapeshellarg($bin)
        . ' pkcs8 -inform DER -in ' . escapeshellarg($derPath)
        . ' -passin file:' . escapeshellarg($pf)
        . ' -nocrypt -out ' . escapeshellarg($out) . ' 2>&1';
    $o1 = [];
    $c1 = 0;
    @exec($cmd1, $o1, $c1);
    $logs[] = "[pkcs8 -nocrypt] code={$c1}\n" . implode("\n", $o1);
    $ok = ($c1 === 0 && file_exists($out) && filesize($out) > 0);

    if (!$ok) {
        $cmd2 = escapeshellarg($bin)
            . ' rsa -inform DER -in ' . escapeshellarg($derPath)
            . ' -passin file:' . escapeshellarg($pf)
            . ' -out ' . escapeshellarg($out) . ' 2>&1';
        $o2 = [];
        $c2 = 0;
        @exec($cmd2, $o2, $c2);
        $logs[] = "[rsa] code={$c2}\n" . implode("\n", $o2);
        $ok = ($c2 === 0 && file_exists($out) && filesize($out) > 0);
    }

    if (!$ok) {
        @unlink($pf);
        @unlink($tmp);
        @unlink($out);
        return [false, null, implode("\n---\n", $logs)];
    }

    $pem  = file_get_contents($out);
    $pkey = @openssl_pkey_get_private($pem);

    @unlink($pf);
    @unlink($tmp);
    @unlink($out);

    if (!$pkey) {
        $logs[] = "No se pudo abrir la KEY resultante como PEM sin pass.";
        return [false, null, implode("\n---\n", $logs)];
    }
    return [$pkey, $pem, implode("\n---\n", $logs)];
}

/** Reporte rápido para verificar par y fechas */
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
