<?php
// === Config: pon aquí tu CSD VIGENTE (DER) ===
$cerDer = "csd/CSD_Sucursal_1_EKU9003173C9_20230517_223850.cer"; // DER
$keyDer = "csd/CSD_Sucursal_1_EKU9003173C9_20230517_223850.key"; // DER
$csdPass = "12345678a";
$endpoint = "https://ws.multifacturas.com/api/";

// === Helpers ===
function derToB64(string $path): string
{
    if (!is_file($path) || !is_readable($path)) {
        throw new RuntimeException("No puedo leer: $path");
    }
    return base64_encode(file_get_contents($path));
}

// Validación opcional: que el .cer (DER) no esté expirado
function cerDerVigente(string $cerDerPath): array
{
    $der = file_get_contents($cerDerPath);
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
        die("cURL error: $err");
    }
    curl_close($ch);
    return $res;
}

// Chequeo de vigencia (opcional pero útil)
$vig = cerDerVigente($cerDer);
if (!$vig['ok']) {
    http_response_code(500);
    echo json_encode(["error" => "Tu CSD no está vigente. " . $vig['msg']], JSON_UNESCAPED_UNICODE);
    exit;
}

// === Payload ===
$datos = [
    "version_cfdi" => "4.0",
    "validacion_local" => "NO",
    "PAC" => [
        "usuario" => "AQUI_TU_RFC",   // si ya vas a producción: tu RFC dado de alta
        "pass"    => "CLAVE_PAC",      // la del panel del PAC
        "produccion" => "NO"
    ],
    "conf" => [
        "cer"  => derToB64($cerDer),
        "key"  => derToB64($keyDer),
        "pass" => $csdPass
    ],
    "factura" => [
        "condicionesDePago" => "CONTADO",
        "fecha_expedicion" => "AUTO",  // ← ahora sí, fecha actual
        "folio" => "1",
        "forma_pago" => "01",
        "LugarExpedicion" => "45079",
        "metodo_pago" => "PUE",
        "moneda" => "MXN",
        "serie" => "A",
        "subtotal" => 100.00,
        "tipocambio" => 1,
        "tipocomprobante" => "I",
        "total" => 116.00,
        "Exportacion" => "01"
    ],
    "emisor" => [
        "rfc" => "EKU9003173C9",
        "nombre" => "ESCUELA KEMPER URGATE",
        "RegimenFiscal" => "601"
    ],
    "receptor" => [
        "rfc" => "XAXX010101000",
        "nombre" => "Publico en General",
        "UsoCFDI" => "S01",
        "DomicilioFiscalReceptor" => "45079",
        "RegimenFiscalReceptor" => "616"
    ],
    "conceptos" => [
        [
            "cantidad" => 1,
            "unidad" => "SERV",
            "ID" => "001",
            "descripcion" => "SERVICIO",
            "valorunitario" => 100,
            "importe" => 100,
            "ClaveProdServ" => "01010101",
            "ClaveUnidad" => "ACT",
            "ObjetoImp" => "02",
            "Impuestos" => ["Traslados" => [["Base" => 100, "Impuesto" => "002", "TipoFactor" => "Tasa", "TasaOCuota" => "0.160000", "Importe" => 16]]]
        ]
    ],
    "impuestos" => [
        "TotalImpuestosTrasladados" => 16.00,
        "translados" => [
            ["Base" => 100, "impuesto" => "002", "tasa" => "0.160000", "importe" => 16.00, "TipoFactor" => "Tasa"]
        ]
    ]
];



// === Ejecutar ===
try {
    // En producción, intenta siempre con TLS estricto (true).
    $resp = postMF($datos, $endpoint, false);
    $response = json_decode($resp, true);
    if (isset($response['cfdi']) && isset($response['png'])) {
        if (base64_decode($response['cfdi'], true) !== false) {
            $xmlData = base64_decode($response['cfdi']);
        } else {
            $xmlData = $response['cfdi'];
        }

        file_put_contents('FACT576401.xml', $xmlData, LOCK_EX);

        $pngData = base64_decode($response['png']);
        file_put_contents('QR576401.png', $pngData);
    }
    echo $resp;
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
