<?php
// ajax/facturas/cancelar.php
header('Content-Type: application/json; charset=utf-8');
@ini_set('display_errors', 0);
@ini_set('html_errors', 0);

session_start();
require_once __DIR__ . '/../../lib/clsConsultas.php';
$clsConsulta = new Consultas();

/* ==============================
   Configuración del endpoint PAC
   ============================== */
$endpoint = "https://ws.multifacturas.com/api/"; // Igual que timbrar.php (dev)

/* ============ Helpers (mismos criterios que timbrar.php) ============ */
function jerr($msg)
{
    echo json_encode(['success' => false, 'msg' => $msg], JSON_UNESCAPED_UNICODE);
    exit;
}

function postMF(array $datos, string $endpoint, bool $strictTLS = true): string
{
    $datospost = [
        'json' => json_encode($datos, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        'modo' => 'JSON'
    ];
    $ch = curl_init($endpoint);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $datospost,
        CURLOPT_CONNECTTIMEOUT => 15,
        CURLOPT_TIMEOUT => 60,
        CURLOPT_SSL_VERIFYPEER => $strictTLS,
        CURLOPT_SSL_VERIFYHOST => $strictTLS ? 2 : 0,
    ]);
    $res = curl_exec($ch);
    if ($res === false) {
        $err = curl_error($ch);
        curl_close($ch);
        jerr("cURL error: $err");
    }
    curl_close($ch);
    return $res;
}

// Los CSD están en: admin/ajax/timbrado/csd/{id_empresa}/{archivo}
function getCsdPath(int $idEmpresa, string $filename): string
{
    $csdDir = __DIR__ . '/../timbrado/csd/' . $idEmpresa . '/';
    return $csdDir . $filename;
}
function derToB64(string $path): string
{
    if (!is_file($path) || !is_readable($path)) {
        throw new RuntimeException("No puedo leer: $path");
    }
    return base64_encode(file_get_contents($path));
}
function cerDerVigente(string $cerDerPath): array
{
    $der = @file_get_contents($cerDerPath);
    if ($der === false) return ["ok" => false, "msg" => "No puedo leer el CER: $cerDerPath"];
    $pem = "-----BEGIN CERTIFICATE-----\n"
        . chunk_split(base64_encode($der), 64, "\n")
        . "-----END CERTIFICATE-----\n";
    $x = openssl_x509_read($pem);
    if (!$x) return ["ok" => false, "msg" => "No se pudo leer el certificado."];
    $p = openssl_x509_parse($x);
    $desde = $p['validFrom_time_t'] ?? 0;
    $hasta = $p['validTo_time_t'] ?? 0;
    $now = time();
    return [
        "ok" => ($now >= $desde && $now <= $hasta),
        "msg" => "Vigencia: " . date('c', $desde) . " a " . date('c', $hasta)
    ];
}

/* ===== Entradas ===== */
$idFactura = (int)($_POST['id'] ?? 0);
$motivo    = trim((string)($_POST['motivo'] ?? '02')); // 01 requiere uuid de sustitución
$uuidSust  = trim((string)($_POST['uuid_sustitucion'] ?? ''));

if ($idFactura <= 0) jerr('Falta id de la factura.');
if (!preg_match('/^(01|02|03|04)$/', $motivo)) jerr('Motivo inválido. Usa 01/02/03/04');
if ($motivo === '01' && $uuidSust === '') jerr('El motivo 01 requiere UUID de sustitución.');

/* ===== Cargar cabecera (uuid, empresa, etc.) ===== */
$sqlCab = "
  SELECT cf.*, cli.razon_social AS rec_razon, CAST(cli.rfc AS CHAR) AS rec_rfc
  FROM cab_facturas cf
  JOIN cat_clientes cli ON cli.id = cf.id_receptor
  WHERE cf.id = {$idFactura}
  LIMIT 1";
$cab = $clsConsulta->consultaGeneral($sqlCab);
$cab = $cab[1] ?? null;
if (!$cab) jerr('Factura no encontrada');

$uuid = trim((string)($cab['uuid'] ?? ''));
if ($uuid === '') jerr('La factura no tiene UUID (no está timbrada).');

$idEmpresa = 0;
if (isset($cab['id_empresa']) && (int)$cab['id_empresa'] > 0) {
    $idEmpresa = (int)$cab['id_empresa'];
} elseif (!empty($_SESSION['id_empresa'])) {
    $idEmpresa = (int)$_SESSION['id_empresa'];
}
if ($idEmpresa <= 0) $idEmpresa = 1;

/* ===== Empresa (emisor) ===== */
$emp = $clsConsulta->consultaGeneral("SELECT * FROM cat_empresas WHERE id={$idEmpresa} LIMIT 1");
$emp = $emp[1] ?? null;
if (!$emp) jerr('Configura la empresa (tabla cat_empresas).');

$emRFC  = trim((string)$emp['rfc']);
$emNom  = trim((string)$emp['razon_social']);
$emReg  = trim((string)($emp['regimen_fiscal'] ?? '601'));

/* ===== CSD activo ===== */
$csdRow = $clsConsulta->consultaGeneral("
  SELECT * FROM empresas_csd
  WHERE id_empresa={$idEmpresa} AND activo=1
  ORDER BY id_csd DESC LIMIT 1
");
$csdRow = $csdRow[1] ?? null;
if (!$csdRow) jerr("No hay un CSD activo para la empresa {$idEmpresa}.");

/* Campos tolerantes a nombres distintos */
$cerRel  = (string)($csdRow['ruta_cer'] ?? ($csdRow['cer_path'] ?? ($csdRow['cer_file'] ?? '')));
$keyRel  = (string)($csdRow['ruta_key'] ?? ($csdRow['key_path'] ?? ($csdRow['key_file'] ?? '')));
$csdPass = (string)($csdRow['pass_csd'] ?? ($csdRow['pass'] ?? ''));

if ($cerRel === '' || $keyRel === '') jerr('Faltan rutas de CER/KEY en el CSD activo.');
if ($csdPass === '') jerr('Falta la contraseña del CSD.');

$cerDer = getCsdPath($idEmpresa, $cerRel);
$keyDer = getCsdPath($idEmpresa, $keyRel);
if (!is_file($cerDer)) jerr("No encuentro el certificado: $cerDer");
if (!is_file($keyDer)) jerr("No encuentro la llave: $keyDer");

$vig = cerDerVigente($cerDer);
if (!$vig['ok']) jerr("Tu CSD no está vigente: {$vig['msg']}");

/* ===== Payload de cancelación (MultiFacturas) =====
   Documentación de cancelación: acción "cancelar" con credenciales del PAC y CSD
*/
$datos = [
    "accion"         => "cancelar",
    "produccion"     => "NO", // en prod cambia a "SI"
    "rfc"            => $emRFC,
    "uuid"           => $uuid,
    "motivo"         => $motivo,
    "uuid_sustitucion" => ($motivo === '01' ? $uuidSust : ""),
    "PAC" => [
        "usuario"     => "EKU9003173C9",   // Credenciales Dev (mismas que en tu timbrado de ejemplo)
        "pass"        => "clave_pac",
        "produccion"  => "NO"
    ],
    "conf" => [
        "cer"  => derToB64($cerDer),
        "key"  => derToB64($keyDer),
        "pass" => $csdPass
    ]
];

/* ===== Llamar PAC ===== */
try {
    // En dev: TLS laxo; en prod: usa true
    $resp  = postMF($datos, $endpoint, false);
    $json  = json_decode($resp, true);

    if (!is_array($json)) {
        jerr("Respuesta no válida del PAC: " . $resp);
    }

    // Formatos posibles que entrega el PAC (cubrir variantes comunes)
    $estatus     = trim((string)($json['estatus_cancelacion'] ?? $json['estatus'] ?? ''));
    $acuseB64    = (string)($json['acuse'] ?? $json['acuse_xml'] ?? '');
    $acuseXmlStr = '';

    if ($acuseB64 !== '') {
        $bin = base64_decode($acuseB64, true);
        $acuseXmlStr = ($bin === false) ? $acuseB64 : $bin;
    } elseif (!empty($json['xml'])) {
        $bin = base64_decode($json['xml'], true);
        $acuseXmlStr = ($bin === false) ? (string)$json['xml'] : $bin;
    }

    // Guardar acuse en disco (si lo hay)
    $acusePath = '';
    if ($acuseXmlStr !== '') {
        $dirOut = __DIR__ . '/../timbrados';
        if (!is_dir($dirOut)) @mkdir($dirOut, 0775, true);
        $acusePath = $dirOut . "/acuse_cancelacion_{$idFactura}.xml";
        @file_put_contents($acusePath, $acuseXmlStr, LOCK_EX);
    }

    // Si el PAC confirmo cancelación aceptada, actualizamos estatus
    $cancelada = false;
    $estatusLower = strtolower($estatus);
    if (strpos($estatusLower, 'cancel') !== false || strpos($estatusLower, 'acept') !== false) {
        // Marcamos CANCELADA (sin suponer más columnas)
        $clsConsulta->aplicaQuery("UPDATE cab_facturas SET estatus='CANCELADA' WHERE id={$idFactura}");
        $cancelada = true;
    }

    echo json_encode([
        'success' => true,
        'msg'     => $cancelada ? 'Cancelación aceptada' : 'Solicitud de cancelación enviada',
        'estatus' => $estatus,
        'acuse'   => ($acusePath !== '' ? basename($acusePath) : null)
    ], JSON_UNESCAPED_UNICODE);
    exit;
} catch (Throwable $e) {
    jerr('Error al cancelar: ' . $e->getMessage());
}
