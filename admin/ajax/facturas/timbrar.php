<?php
// ajax/facturas/timbrar.php
header('Content-Type: application/json; charset=utf-8');
@ini_set('display_errors', 0);
@ini_set('html_errors', 0);

session_start();
require_once __DIR__ . '/../../lib/clsConsultas.php';
$clsConsulta = new Consultas();

/* ==============================
   Configuración PAC/endpoint
   ============================== */
$endpoint = "https://ws.multifacturas.com/api/"; // Desarrollo
// En PROD, cambia a tu endpoint productivo y activa TLS estricto (ver postMF).

/* ============ Helpers ============ */
function pathAbsFromAdmin(string $p): string
{
    // Convierte una ruta relativa (respecto a /admin) a absoluta
    if ($p === '') return $p;
    if (preg_match('~^[a-zA-Z]:\\\\|^/~', $p)) { // absoluta (Windows o Unix)
        return $p;
    }
    // __DIR__ = .../admin/ajax/facturas  => dirname(__DIR__,2) = .../admin
    return rtrim(dirname(__DIR__, 2), '/\\') . '/' . ltrim($p, '/\\');
}

// NUEVA FUNCIÓN: Obtener ruta correcta del CSD basada en la estructura de instalar-csd.php
function getCsdPath(int $idEmpresa, string $filename): string
{
    // Los archivos se guardan en: admin/ajax/timbrado/csd/{id_empresa}/
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
        echo json_encode(["success" => false, "msg" => "cURL error: $err"], JSON_UNESCAPED_UNICODE);
        exit;
    }
    curl_close($ch);
    return $res;
}
function fmt2($n)
{
    return number_format((float)$n, 2, '.', '');
}
function fecha_cfdi_40($f)
{
    $ts = $f ? strtotime($f) : false;
    if (!$ts || $ts < strtotime('2010-01-01 00:00:00')) $ts = time();
    return date('Y-m-d\TH:i:s', $ts);
}
function jerr($msg)
{
    echo json_encode(['success' => false, 'msg' => $msg], JSON_UNESCAPED_UNICODE);
    exit;
}

/* ===== ID de factura ===== */
$id = (int)($_POST['id'] ?? 0);
if ($id <= 0) jerr('Falta id de factura');

/* ===== Cabecera de factura + receptor ===== */
$sqlCab = "
SELECT
    cf.*,
    cli.razon_social   AS rec_razon,
    CAST(cli.rfc AS CHAR) AS rec_rfc,
    IFNULL(cli.cp,'')  AS rec_cp,
    IFNULL(cli.regimen_fiscal,'') AS rec_regimen,
    IFNULL(cli.id_uso_cfdi,0) AS rec_uso_id
FROM cab_facturas cf
JOIN cat_clientes cli ON cli.id = cf.id_receptor
WHERE cf.id = {$id}
LIMIT 1";
$cab = $clsConsulta->consultaGeneral($sqlCab);
$cab = $cab[1] ?? null;
if (!$cab) jerr('Factura no encontrada');

/* ===== Empresa (emisor) – multiempresa =====
   1) De la cabecera cf.id_empresa
   2) Si no existe, de la sesión
   3) si no, usa 1
*/
$idEmpresa = 0;
if (isset($cab['id_empresa']) && (int)$cab['id_empresa'] > 0) {
    $idEmpresa = (int)$cab['id_empresa'];
} elseif (!empty($_SESSION['id_empresa'])) {
    $idEmpresa = (int)$_SESSION['id_empresa'];
}
if ($idEmpresa <= 0) $idEmpresa = 1;

$emp = $clsConsulta->consultaGeneral("SELECT * FROM cat_empresas WHERE id={$idEmpresa} LIMIT 1");
$emp = $emp[1] ?? null;
if (!$emp) jerr('Configura la empresa (tabla cat_empresas)');

$emRFC    = trim((string)$emp['rfc']);
$emNom    = trim((string)$emp['razon_social']);
$emReg    = trim((string)($emp['regimen_fiscal'] ?? '601'));

/* LugarExpedicion: primero cabecera; si no, CP empresa; si no, 45079 */
$lugarExp = trim((string)($cab['lugar_expedicion'] ?: ($emp['cp'] ?? '')));
if ($lugarExp === '') $lugarExp = '45079';

/* ===== CSD activo de la empresa (CER+KEY) ===== */
$csdRow = $clsConsulta->consultaGeneral("
  SELECT * FROM empresas_csd 
  WHERE id_empresa={$idEmpresa} AND activo=1 
  ORDER BY id_csd DESC LIMIT 1
");
$csdRow = $csdRow[1] ?? null;
if (!$csdRow) jerr("No hay un CSD activo para la empresa {$idEmpresa}. Instálalo en Utilerías → Empresas → Timbrado.");

$tipoCsd  = strtoupper(trim((string)($csdRow['tipo'] ?? 'CER_KEY')));
if ($tipoCsd !== 'CER_KEY') {
    jerr("Este timbrado está configurado para CER+KEY. El CSD encontrado es de tipo {$tipoCsd}.");
}

/* Campos tolerantes a nombres distintos */
$cerRel = (string)($csdRow['ruta_cer'] ?? ($csdRow['cer_path'] ?? ($csdRow['cer_file'] ?? '')));
$keyRel = (string)($csdRow['ruta_key'] ?? ($csdRow['key_path'] ?? ($csdRow['key_file'] ?? '')));
$csdPass = (string)($csdRow['pass_csd'] ?? ($csdRow['pass'] ?? ''));

if ($cerRel === '' || $keyRel === '') jerr('Faltan rutas de CER/KEY en el CSD activo.');

// CORRECCIÓN: Usar la nueva función getCsdPath en lugar de pathAbsFromAdmin
$cerDer = getCsdPath($idEmpresa, $cerRel);
$keyDer = getCsdPath($idEmpresa, $keyRel);

if (!is_file($cerDer)) jerr("No encuentro el certificado: $cerDer");
if (!is_file($keyDer)) jerr("No encuentro la llave: $keyDer");
if ($csdPass === '') jerr('Falta la contraseña del CSD.');

/* ===== Validar vigencia del certificado ===== */
$vig = cerDerVigente($cerDer);
if (!$vig['ok']) jerr("Tu CSD no está vigente: {$vig['msg']}");

/* ===== Catálogos / Formas y Métodos ===== */
// CORRECCIÓN: Buscar forma de pago por ID en cat_formas_pago
$fp = $clsConsulta->consultaGeneral("SELECT codigo FROM cat_formas_pago WHERE id=" . (int)$cab['forma_pago'] . " LIMIT 1");
$formaPago  = trim((string)($fp[1]['codigo'] ?? '01'));

// CORRECCIÓN: Buscar método de pago por ID en cat_metodos_pago
$metodoPago = '';
if (is_numeric($cab['metodo_pago'])) {
    $mp = $clsConsulta->consultaGeneral("SELECT codigo FROM cat_metodos_pago WHERE id=" . (int)$cab['metodo_pago'] . " LIMIT 1");
    $metodoPago = trim((string)($mp[1]['codigo'] ?? 'PUE'));
} else {
    $metodoPago = trim((string)$cab['metodo_pago']);
}

if ($metodoPago === '' || !preg_match('/^[A-Z0-9]{2,3}$/', $metodoPago)) {
    $metodoPago = 'PUE';
}

/* UsoCFDI receptor (clave) */
$usoCFDI = trim((string)($cab['uso_cfdi'] ?? ''));
if ($usoCFDI === '') {
    $cliUsoId = (int)($cab['rec_uso_id'] ?? 0);
    if ($cliUsoId > 0) {
        $u = $clsConsulta->consultaGeneral("SELECT clave FROM cat_uso_cfdi WHERE id={$cliUsoId} LIMIT 1");
        $usoCFDI = trim((string)($u[1]['clave'] ?? ''));
    }
}
if ($usoCFDI === '') $usoCFDI = 'G03';

/* ===== Conceptos ===== */
$det = $clsConsulta->consultaGeneral("
  SELECT m.id_producto, m.cantidad, m.precio, p.nombre, p.clave, p.unidad_medida, p.excento
  FROM mov_facturas m
  JOIN cat_productos p ON p.id_producto = m.id_producto
  WHERE m.id_factura = {$id}
");
if (!$det) jerr('La factura no tiene conceptos');

$conceptos = [];
$ivaTotal  = 0.00;
$subtotal  = 0.00;
$baseIVA   = 0.00;

foreach ($det as $r) {
    if (!is_array($r) || !isset($r['id_producto'])) continue;

    $cant = (float)$r['cantidad'];
    $pu   = (float)$r['precio'];
    if ($cant <= 0 || $pu <= 0) continue;

    $imp     = round($cant * $pu, 2);
    $subtotal += $imp;

    $exento = ((int)$r['excento'] === 1);

    $claveSAT   = (isset($r['clave']) && preg_match('/^\d{8}$/', (string)$r['clave'])) ? (string)$r['clave'] : '01010101';
    $unidadDesc = trim((string)($r['unidad_medida'] ?: 'PZA'));

    $c = [
        "ClaveProdServ"     => $claveSAT,
        "NoIdentificacion"  => (string)$r['id_producto'],
        "Cantidad"          => fmt2($cant),
        "ClaveUnidad"       => "H87",
        "Unidad"            => $unidadDesc,
        "Descripcion"       => $r['nombre'],
        "ValorUnitario"     => fmt2($pu),
        "Importe"           => fmt2($imp),
        "ObjetoImp"         => $exento ? "01" : "02"
    ];

    if (!$exento) {
        $baseIVA += $imp;
        $iva = round($imp * 0.16, 2);
        $ivaTotal += $iva;
        $c["Impuestos"] = [
            "Traslados" => [[
                "Base"       => fmt2($imp),
                "Impuesto"   => "002",
                "TipoFactor" => "Tasa",
                "TasaOCuota" => "0.160000",
                "Importe"    => fmt2($iva)
            ]]
        ];
    }

    $conceptos[] = $c;
}

$total = round($subtotal + $ivaTotal, 2);

/* Impuestos globales (sólo si hubo gravados) */
$imps = null;
if ($ivaTotal > 0) {
    $imps = [
        "TotalImpuestosTrasladados" => fmt2($ivaTotal),
        "translados" => [[
            "Base"      => fmt2($baseIVA),
            "impuesto"  => "002",
            "tasa"      => "0.160000",
            "importe"   => fmt2($ivaTotal),
            "TipoFactor" => "Tasa"
        ]]
    ];
}

/* ===== Receptor y reglas para RFC genérico ===== */
$receptorRFC        = strtoupper(trim((string)($cab['rec_rfc'] ?: "XAXX010101000")));
$receptorNombre     = trim((string)($cab['rec_razon'] ?: "Publico en General"));
$receptorEsGenerico = ($receptorRFC === 'XAXX010101000');

if ($receptorEsGenerico) {
    $usoCFDI         = 'S01';
    $regimenReceptor = '616';
    $receptorNombre  = 'Publico en General';
    $domFiscalRec    = $lugarExp;
} else {
    $domFiscalRec    = trim((string)($cab['rec_cp'] ?: $lugarExp));
    $regimenReceptor = trim((string)($cab['rec_regimen'] ?: '601'));
}

/* ===== Factura (cabecera CFDI) ===== */
$ts = strtotime($cab['fecha']);
$factura = [
    "condicionesDePago" => "CONTADO",
    "fecha_expedicion"  => fecha_cfdi_40($cab['fecha']),
    "folio"             => (string)$cab['folio'],
    "forma_pago"        => $formaPago,
    "LugarExpedicion"   => $lugarExp,
    "metodo_pago"       => $metodoPago,
    "moneda"            => "MXN",
    "serie"             => (string)$cab['serie'],
    "subtotal"          => fmt2($subtotal),
    "tipocambio"        => 1,
    "tipocomprobante"   => "I",
    "total"             => fmt2($total),
    "Exportacion"       => "01"
];

if ($receptorEsGenerico) {
    $factura["InformacionGlobal"] = [
        "Periodicidad" => "05",
        "Meses"        => date('m', $ts),
        "Año"          => date('Y', $ts)
    ];
}

/* ===== Payload final ===== */
$datos = [
    "version_cfdi"     => "4.0",
    "validacion_local" => "NO",
    "PAC" => [
        "usuario"     => "EKU9003173C9",  // credenciales dev de tu ejemplo
        "pass"        => "clave_pac",
        "produccion"  => "NO"
    ],
    "conf" => [
        "cer"  => derToB64($cerDer),
        "key"  => derToB64($keyDer),
        "pass" => $csdPass
    ],
    "factura"  => $factura,
    "emisor"   => [
        "rfc"           => $emRFC,
        "nombre"        => $emNom,
        "RegimenFiscal" => $emReg
    ],
    "receptor" => [
        "rfc"                       => $receptorRFC,
        "nombre"                    => $receptorNombre,
        "UsoCFDI"                   => $usoCFDI,
        "DomicilioFiscalReceptor"   => $domFiscalRec,
        "RegimenFiscalReceptor"     => $regimenReceptor
    ],
    "conceptos" => $conceptos
];
if ($imps) $datos["impuestos"] = $imps;

/* ===== Timbrar ===== */
try {
    // En dev: TLS laxo; en prod: usa true
    $resp = postMF($datos, $endpoint, false);
    $response = json_decode($resp, true);

    if (!is_array($response)) {
        echo json_encode(['success' => false, 'msg' => $resp], JSON_UNESCAPED_UNICODE);
        exit;
    }

    if (isset($response['cfdi'])) {
        // CORRECCIÓN: Ruta consistente para timbrados
        $dirOut = __DIR__ . '/../timbrados';
        if (!is_dir($dirOut)) @mkdir($dirOut, 0775, true);

        $xmlPath = $dirOut . "/factura_{$id}.xml";
        $xmlData = base64_decode($response['cfdi'], true);
        if ($xmlData === false) $xmlData = (string)$response['cfdi'];

        file_put_contents($xmlPath, $xmlData, LOCK_EX);

        if (!empty($response['png'])) {
            $pngData = base64_decode($response['png']);
            @file_put_contents($dirOut . "/factura_{$id}.png", $pngData);
        }
        if (!empty($response['pdf'])) {
            $pdfData = base64_decode($response['pdf']);
            if ($pdfData !== false) @file_put_contents($dirOut . "/factura_{$id}.pdf", $pdfData);
        }

        // UUID
        $uuid = '';
        if (!empty($response['uuid'])) {
            $uuid = trim((string)$response['uuid']);
        } else {
            libxml_use_internal_errors(true);
            $sx = @simplexml_load_string($xmlData);
            if ($sx) {
                $namespaces = $sx->getNamespaces(true);
                $tfd = $sx->xpath('//tfd:TimbreFiscalDigital');
                if (!$tfd && isset($namespaces['cfdi'])) {
                    $tfd = $sx->xpath('//cfdi:Complemento/tfd:TimbreFiscalDigital');
                }
                if ($tfd && isset($tfd[0]['UUID'])) {
                    $uuid = (string)$tfd[0]['UUID'];
                }
            }
        }

        // Guardar en cab_facturas (uuid/fecha)
        if ($uuid !== '') {
            $clsConsulta->aplicaQuery("UPDATE cab_facturas SET uuid='" . addslashes($uuid) . "', fecha_timbrado=NOW(), timbrada=1 WHERE id={$id}");
        } else {
            $clsConsulta->aplicaQuery("UPDATE cab_facturas SET fecha_timbrado=NOW(), timbrada=1 WHERE id={$id}");
        }

        echo json_encode(['success' => true, 'msg' => 'Timbrado OK', 'uuid' => $uuid], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Error del PAC / validación
    echo json_encode(['success' => false, 'msg' => $resp], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    jerr('Error al timbrar: ' . $e->getMessage());
}
