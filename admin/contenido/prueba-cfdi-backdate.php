<?php

/** UTF-8 sin BOM **/

declare(strict_types=1);

if (!headers_sent()) {
    header('Content-Type: application/json; charset=UTF-8');
}

function jj($d)
{
    return json_encode($d, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
}
function normp($p)
{
    return str_replace('\\', '/', $p);
}
function joinp(...$parts)
{
    return preg_replace('~[\\/]+~', '/', implode('/', $parts));
}
function file_text(string $p): ?string
{
    return is_file($p) ? trim((string)file_get_contents($p)) : null;
}

$PAC_URL  = 'https://ws.multifacturas.com/api/';
$PAC_USER = 'DEMO700101XXX';
$PAC_PASS = 'DEMO700101XXX';
$CSD_PASS = '12345678a';

$here     = normp(__DIR__);                // .../admin/contenido
$adminDir = normp(dirname(__DIR__));       // .../admin
$csdDir   = joinp($adminDir, 'ajax/timbrado/csd');

$cerPath = isset($_REQUEST['cerTxt']) ? normp((string)$_REQUEST['cerTxt']) : joinp($csdDir, 'Base64delCer.txt');
$keyPath = isset($_REQUEST['keyTxt']) ? normp((string)$_REQUEST['keyTxt']) : joinp($csdDir, 'Base64delKey.txt');

$cerB64 = file_text($cerPath);
$keyB64 = file_text($keyPath);

if (!$cerB64 || !$keyB64) {
    echo jj([
        'error'  => 'No encuentro o no pude leer los TXT con base64 de CSD',
        'cerTxt' => $cerPath,
        'keyTxt' => $keyPath,
    ]);
    exit;
}

/* ==== Datos base del CFDI ==== */
$mes   = date('m');
$anio4 = date('Y');

$base = [
    'version_cfdi'     => '4.0',
    'validacion_local' => 'NO',
    'PAC' => [
        'usuario'    => $PAC_USER,
        'pass'       => $PAC_PASS,
        'produccion' => 'NO',
    ],
    'conf' => [
        'cer'  => $cerB64,
        'key'  => $keyB64,
        'pass' => $CSD_PASS,
    ],
    'factura' => [
        'fecha_expedicion' => 'AUTO',
        'serie'            => 'A',
        'folio'            => (string)rand(1000, 9999),
        'tipocomprobante'  => 'I',         // Ingreso
        'moneda'           => 'MXN',
        'metodo_pago'      => 'PUE',
        'forma_pago'       => '01',
        'LugarExpedicion'  => '45079',
        'Exportacion'      => '01',
        'subtotal'         => 100.00,
        'total'            => 116.00,
    ],
    'emisor' => [
        'rfc'           => 'EKU9003173C9',
        'nombre'        => 'ESCUELA KEMPER URGATE',
        'RegimenFiscal' => '603',
    ],
    'receptor' => [
        'rfc'                     => 'XAXX010101000',
        'nombre'                  => 'PUBLICO EN GENERAL',
        'UsoCFDI'                 => 'S01',
        'DomicilioFiscalReceptor' => '45079',
        'RegimenFiscalReceptor'   => '616',
    ],
    'conceptos' => [[
        'cantidad'      => 1,
        'unidad'        => 'H87',
        'descripcion'   => 'Venta al público en general',
        'valorunitario' => 100.00,
        'importe'       => 100.00,
        'ClaveProdServ' => '01010101',
        'ClaveUnidad'   => 'H87',
        'ObjetoImp'     => '02',
        'Impuestos' => [
            'Traslados' => [[
                'Base'       => 100.00,
                'Impuesto'   => '002',
                'TipoFactor' => 'Tasa',
                'TasaOCuota' => '0.160000',
                'Importe'    => 16.00,
            ]],
        ],
    ]],
    'impuestos' => [
        'TotalImpuestosTrasladados' => 16.00,
        'translados' => [[
            'Base'       => 100.00,
            'impuesto'   => '002',
            'tasa'       => '0.160000',
            'importe'    => 16.00,
            'TipoFactor' => 'Tasa',
        ]],
    ],
];

/* ==== Generar variantes del nodo Información Global ==== */
function payload_variant(array $base, string $where, string $keyset, string $mes, string $anio): array
{
    $p = $base;
    if ($keyset === 'Año') {
        $node = ['Periodicidad' => '04', 'Meses' => $mes, 'Año' => $anio];     // 04 = Mensual (si tu PAC usa otros catálogos, ajusta)
    } elseif ($keyset === 'Anio') {
        $node = ['Periodicidad' => '04', 'Meses' => $mes, 'Anio' => $anio];
    } else { // minus
        $node = ['periodicidad' => '04', 'meses' => $mes, 'anio' => $anio];
    }
    if ($where === 'factura') {
        if ($keyset === 'minus') $p['factura']['informacion_global'] = $node;
        else $p['factura']['InformacionGlobal'] = $node;
    } else { // root
        if ($keyset === 'minus') $p['informacion_global'] = $node;
        else $p['InformacionGlobal'] = $node;
    }
    return $p;
}

$variantes = [
    ['nombre' => 'A_en_factura',           'payload' => payload_variant($base, 'factura', 'Año',  $mes, $anio4)],
    ['nombre' => 'Anio_en_factura',        'payload' => payload_variant($base, 'factura', 'Anio', $mes, $anio4)],
    ['nombre' => 'minus_en_factura',       'payload' => payload_variant($base, 'factura', 'minus', $mes, $anio4)],
    ['nombre' => 'A_en_root',              'payload' => payload_variant($base, 'root',   'Año',  $mes, $anio4)],
    ['nombre' => 'Anio_en_root',           'payload' => payload_variant($base, 'root',   'Anio', $mes, $anio4)],
    ['nombre' => 'minus_en_root',          'payload' => payload_variant($base, 'root',   'minus', $mes, $anio4)],
];

function post_pac(string $url, array $payload): array
{
    $post = [
        'json' => json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        'modo' => 'JSON',
    ];
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL            => $url,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $post,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CONNECTTIMEOUT => 30,
        CURLOPT_TIMEOUT        => 120,
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_MAXREDIRS      => 1,
        CURLOPT_USERAGENT      => 'api-mf',
        CURLOPT_SSL_VERIFYPEER => 0,
        CURLOPT_SSL_VERIFYHOST => 0,
    ]);
    $res = curl_exec($ch);
    $err = $res === false ? curl_error($ch) : null;
    curl_close($ch);
    return [$post['json'], $res ? json_decode($res, true) : null, $err];
}

/* ==== Ejecutar variantes ==== */
$intentos = [];
$exito = null;

foreach ($variantes as $var) {
    [$json_enviado, $resp, $err_curl] = post_pac($PAC_URL, $var['payload']);

    file_put_contents(joinp($here, "ultimo_enviado_{$var['nombre']}.json"), $json_enviado);
    file_put_contents(joinp($here, "ultimo_respuesta_{$var['nombre']}.json"), json_encode($resp, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

    $intentos[] = [
        'variante' => $var['nombre'],
        'resumen'  => $resp,
        'error_curl' => $err_curl,
    ];

    // éxito si ya no es 2 (validación faltante) o si trae timbre
    if ($resp && isset($resp['codigo_mf_numero']) && (int)$resp['codigo_mf_numero'] !== 2) {
        $exito = $var['nombre'];
        break;
    }
}

/* ==== Salida ==== */
echo jj([
    'peticion' => ['url' => $PAC_URL, 'modo' => 'JSON'],
    'csd' => [
        'cerTxt' => $cerPath,
        'keyTxt' => $keyPath,
        'cer_b64_len' => strlen($cerB64),
        'key_b64_len' => strlen($keyB64),
    ],
    'resultado' => [
        'exito_en_variante' => $exito,
        'intentos' => $intentos,
    ],
]);
