<?php
// ajax/facturas/timbrar_json.php

if (!isset($clsConsulta)) {
    require_once __DIR__ . '/../../lib/clsConsultas.php';
    $clsConsulta = new Consultas();
}

/* === CONFIG === */
$ENV  = 'dev'; // 'dev' | 'prod'
$WSDL = [
    'dev'  => 'https://dev.timbradorxpress.mx/ws/servicio.do?wsdl',
    'prod' => 'https://app.timbradorxpress.mx/ws/servicio.do?wsdl',
];
$APIKEY = 'TU_APIKEY_AQUI'; // <-- reemplaza

// === Unificamos rutas con dónde instalaste los PEM ===
// desde admin/ajax/facturas -> admin/ajax/timbrado/csd
$PATH_CERTS     = __DIR__ . '/../timbrado/csd';       // <--- ANTES estaba mal
$PATH_TIMBRADOS = __DIR__ . '/../timbrado/timbrados';
$PATH_LOGS      = __DIR__ . '/../timbrado/logs';
@mkdir($PATH_TIMBRADOS, 0777, true);
@mkdir($PATH_LOGS, 0777, true);

$log  = $PATH_LOGS . '/timbrar_json.log';
$logf = function ($m) use ($log) {
    @file_put_contents($log, '[' . date('Y-m-d H:i:s') . "] $m\n", FILE_APPEND);
};

try {
    if (!isset($id_factura, $fact_serie, $fact_folio)) {
        throw new Exception('Faltan variables de factura.');
    }

    $cerPEM = @file_get_contents($PATH_CERTS . '/csd.cer.pem');
    $keyPEM = @file_get_contents($PATH_CERTS . '/csd.key.pem');
    if (!$cerPEM || !$keyPEM) {
        throw new Exception('Faltan PEM en ' . $PATH_CERTS . '. Instala CSD en empresa.');
    }

    // ===== CABECERA =====
    $cabRs = $clsConsulta->consultaGeneral("SELECT * FROM cab_facturas WHERE id={$id_factura} LIMIT 1");
    if ($clsConsulta->numrows <= 0) throw new Exception("No existe cab_facturas.id={$id_factura}");
    $c = $cabRs[1];

    $em = $clsConsulta->consultaGeneral("SELECT * FROM empresa WHERE id={$c['id_emisor']} LIMIT 1");
    if ($clsConsulta->numrows <= 0) throw new Exception("No existe empresa.id={$c['id_emisor']}");
    $em = $em[1];

    $re = $clsConsulta->consultaGeneral("SELECT * FROM cat_clientes WHERE id={$c['id_receptor']} LIMIT 1");
    if ($clsConsulta->numrows <= 0) throw new Exception("No existe receptor id={$c['id_receptor']}");
    $re = $re[1];

    // Códigos SAT Forma/Metodo
    $fp = $clsConsulta->consultaGeneral("SELECT codigo FROM cat_formas_pago WHERE id={$c['forma_pago']} LIMIT 1");
    $mp = $clsConsulta->consultaGeneral("SELECT codigo FROM cat_metodos_pago WHERE id={$c['metodo_pago']} LIMIT 1");
    $forma = $fp[1]['codigo'] ?? '01';
    $metod = $mp[1]['codigo'] ?? 'PUE';

    // UsoCFDI (soporta clave, id o descripción)
    $usoVal = isset($usoCFDIValor) ? trim((string)$usoCFDIValor) : 'G01';
    $usoCFDI = 'G01';
    if ($usoVal !== '') {
        if (preg_match('/^[A-Z]\d{2}$/', $usoVal)) {
            $usoCFDI = $usoVal; // ya es clave (p.ej. G01)
        } elseif (ctype_digit($usoVal)) {
            $r = $clsConsulta->consultaGeneral("SELECT clave FROM cat_uso_cfdi WHERE id=" . (int)$usoVal . " LIMIT 1");
            if ($clsConsulta->numrows > 0) $usoCFDI = $r[1]['clave'];
        } else {
            $safe = method_exists($clsConsulta, 'sanitizar') ? $clsConsulta->sanitizar($usoVal) : $usoVal;
            $r = $clsConsulta->consultaGeneral("SELECT clave FROM cat_uso_cfdi WHERE descripcion='{$safe}' LIMIT 1");
            if ($clsConsulta->numrows > 0) $usoCFDI = $r[1]['clave'];
            if ($usoCFDI === 'G01') {
                $safe2 = strtoupper($safe);
                $r2 = $clsConsulta->consultaGeneral("SELECT clave FROM cat_uso_cfdi WHERE clave='{$safe2}' LIMIT 1");
                if ($clsConsulta->numrows > 0) $usoCFDI = $r2[1]['clave'];
            }
        }
    }

    // ===== CONCEPTOS =====
    $det = $clsConsulta->consultaGeneral("
        SELECT m.cantidad, m.precio,
               p.nombre, p.clave,
               COALESCE(p.clave_sat,'01010101') AS clave_sat,
               COALESCE(p.unidad_sat,'H87')     AS clave_unidad,
               COALESCE(p.unidad,'ACT')         AS unidad
        FROM mov_facturas m
        JOIN cat_productos p ON p.id_producto = m.id_producto
        WHERE m.id_factura = {$id_factura}
    ");
    if ($clsConsulta->numrows <= 0) throw new Exception("Factura sin conceptos");

    $fmt = fn($n) => number_format((float)$n, 2, '.', '');
    $conceptos = [];
    $sub = 0.00;
    $tras = 0.00;
    $ret = 0.00;

    foreach ($det as $r) {
        $cant = (float)$r['cantidad'];
        $pu   = (float)$r['precio'];
        $imp  = round($cant * $pu, 2);
        $sub += $imp;

        $base = $imp;
        $iva  = round($base * 0.16, 2);
        $tras += $iva;

        $conceptos[] = [
            "ClaveProdServ"    => $r['clave_sat'],
            "NoIdentificacion" => $r['clave'],
            "Cantidad"         => $fmt($cant),
            "ClaveUnidad"      => $r['clave_unidad'],
            "Unidad"           => $r['unidad'],
            "Descripcion"      => $r['nombre'],
            "ValorUnitario"    => $fmt($pu),
            "Importe"          => $fmt($imp),
            "ObjetoImp"        => "02",
            "Impuestos"        => [
                "Traslados" => [[
                    "Base"       => $fmt($base),
                    "Impuesto"   => "002",
                    "TipoFactor" => "Tasa",
                    "TasaOCuota" => "0.160000",
                    "Importe"    => $fmt($iva)
                ]]
            ]
        ];
    }
    $total = round($sub + $tras - $ret, 2);

    $json = [
        "Comprobante" => [
            "Version"           => "4.0",
            "Serie"             => (string)$c['serie'],
            "Folio"             => (string)$c['folio'],
            "Fecha"             => date('c', strtotime($c['fecha'])),
            "FormaPago"         => $forma,
            "SubTotal"          => $fmt($sub),
            "Moneda"            => "MXN",
            "TipoCambio"        => "1",
            "Total"             => $fmt($total),
            "TipoDeComprobante" => "I",
            "Exportacion"       => "01",
            "MetodoPago"        => $metod,
            "LugarExpedicion"   => ($em['cp_expedicion'] ?: $em['cp'] ?: '00000'),
            "Emisor" => [
                "Rfc"           => trim($em['rfc']),
                "Nombre"        => trim($em['razon_social']),
                "RegimenFiscal" => ($em['regimen_fiscal'] ?: '601')
            ],
            "Receptor" => [
                "Rfc"                     => trim($re['rfc']),
                "Nombre"                  => trim($re['razon_social']),
                "DomicilioFiscalReceptor" => ($re['cp'] ?: '00000'),
                "RegimenFiscalReceptor"   => ($re['regimen_fiscal'] ?: '601'),
                "UsoCFDI"                 => $usoCFDI
            ],
            "Conceptos" => $conceptos,
            "Impuestos" => [
                "TotalImpuestosTrasladados" => $fmt($tras),
                "Traslados" => [[
                    "Base"       => $fmt($sub),
                    "Impuesto"   => "002",
                    "TipoFactor" => "Tasa",
                    "TasaOCuota" => "0.160000",
                    "Importe"    => $fmt($tras)
                ]]
            ]
        ]
    ];

    $jsonB64 = base64_encode(json_encode($json, JSON_UNESCAPED_UNICODE));

    $client = new SoapClient($WSDL[$ENV], ['trace' => 0, 'exceptions' => true, 'connection_timeout' => 40]);
    $res = $client->__soapCall('timbrarJSON', [[
        'apikey'  => $APIKEY,
        'jsonB64' => $jsonB64,
        'keyPEM'  => $keyPEM,
        'cerPEM'  => $cerPEM
    ]]);

    $ret = $res->return ?? null;
    if (!$ret) throw new Exception('Respuesta SOAP inválida.');
    $code = (string)$ret->code;
    $msg  = (string)$ret->message;

    if ($code !== '200') {
        $logf("Fallo timbrarJSON id={$id_factura}: {$msg}");
        return; // silencioso
    }

    $data = json_decode((string)$ret->data, true);
    $xmlTimbrado = $data['XML'] ?? (string)$ret->data;
    if ($xmlTimbrado) {
        file_put_contents($PATH_TIMBRADOS . "/{$fact_serie}_{$fact_folio}.xml", $xmlTimbrado);
    }

    if (isset($data['UUID']) || isset($data['FechaTimbrado'])) {
        $sets = [];
        if (isset($data['UUID']))          $sets[] = "uuid='" . addslashes($data['UUID']) . "'";
        if (isset($data['FechaTimbrado'])) $sets[] = "fecha_timbrado='" . addslashes($data['FechaTimbrado']) . "'";
        if ($sets) {
            $sql = "UPDATE cab_facturas SET " . implode(',', $sets) . " WHERE id={$id_factura}";
            $clsConsulta->aplicaQuery($sql);
        }
    }
} catch (Throwable $e) {
    $logf('ERROR timbrarJSON: ' . $e->getMessage());
}
