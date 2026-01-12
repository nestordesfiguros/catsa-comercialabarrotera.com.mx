<?php
require_once __DIR__ . '/../lib/clsConsultas.php';
$cls = new Consultas();

// Obtener emisor desde la tabla empresa
$emisor = $cls->consultaGeneral("SELECT rfc FROM empresa LIMIT 1")[1] ?? null;

if (!$emisor) {
    echo "❌ No se encontró el emisor en la base de datos.";
    exit;
}

$rfc = strtoupper(trim($emisor['rfc']));
$dir = __DIR__ . '/../certificados/';
$keyPath = $dir . $rfc . '.key';
$cerPath = $dir . $rfc . '.cer';

echo "<h3>🔍 Verificación de certificados para RFC: $rfc</h3>";

if (file_exists($keyPath)) {
    echo "✅ Archivo KEY encontrado: <code>$keyPath</code><br>";

    // Verificar permisos
    $keyPerms = substr(sprintf('%o', fileperms($keyPath)), -4);
    echo "&nbsp;&nbsp;Permisos: $keyPerms<br>";
} else {
    echo "❌ Archivo KEY NO encontrado: <code>$keyPath</code><br>";
}

if (file_exists($cerPath)) {
    echo "✅ Archivo CER encontrado: <code>$cerPath</code><br>";

    // Verificar vigencia del certificado
    $cerData = file_get_contents($cerPath);
    $certInfo = openssl_x509_parse($cerData);

    if ($certInfo) {
        $validoDesde = date('Y-m-d H:i:s', $certInfo['validFrom_time_t']);
        $validoHasta = date('Y-m-d H:i:s', $certInfo['validTo_time_t']);
        $ahora = time();

        $vigente = ($ahora >= $certInfo['validFrom_time_t'] && $ahora <= $certInfo['validTo_time_t']);

        echo "&nbsp;&nbsp;Válido desde: $validoDesde<br>";
        echo "&nbsp;&nbsp;Válido hasta: $validoHasta<br>";
        echo "&nbsp;&nbsp;Estado: " . ($vigente ? "✅ VIGENTE" : "❌ VENCIDO") . "<br>";
    } else {
        echo "&nbsp;&nbsp;❌ No se pudo leer la información del certificado<br>";
    }
} else {
    echo "❌ Archivo CER NO encontrado: <code>$cerPath</code><br>";
}

// Verificar directorio de timbrados
$timbradosDir = __DIR__ . '/../timbrados/';
if (!is_dir($timbradosDir)) {
    echo "❌ Directorio de timbrados no existe: <code>$timbradosDir</code><br>";
    echo "&nbsp;&nbsp;Creando directorio... ";
    if (mkdir($timbradosDir, 0755, true)) {
        echo "✅ CREADO<br>";
    } else {
        echo "❌ FALLÓ<br>";
    }
} else {
    echo "✅ Directorio de timbrados existe: <code>$timbradosDir</code><br>";

    // Verificar permisos de escritura
    if (is_writable($timbradosDir)) {
        echo "&nbsp;&nbsp;Permisos de escritura: ✅ OK<br>";
    } else {
        echo "&nbsp;&nbsp;Permisos de escritura: ❌ NO TIENE<br>";
    }
}

// Verificar configuración de empresa
$empresa = $cls->consultaGeneral("SELECT * FROM empresa LIMIT 1")[1] ?? null;
if ($empresa) {
    echo "<h3>🏢 Información de la empresa</h3>";
    echo "Razón social: " . ($empresa['razon_social'] ?? 'No definida') . "<br>";
    echo "RFC: " . ($empresa['rfc'] ?? 'No definido') . "<br>";
    echo "Regimen fiscal: " . ($empresa['regimen_fiscal'] ?? 'No definido') . "<br>";
    echo "CP: " . ($empresa['cp'] ?? 'No definido') . "<br>";
} else {
    echo "❌ No se encontró información de la empresa<br>";
}
