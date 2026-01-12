<?php
// ajax/facturas/guardar.php
header('Content-Type: application/json; charset=utf-8');

session_start();
require_once __DIR__ . '/../../lib/clsConsultas.php';
$clsConsulta = new Consultas();

try {
    // =========================
    // ENTRADAS
    // =========================
    $fecha          = $_POST["fact_fecha"]  ?? date('Y-m-d');
    $fact_serie     = $_POST["fact_serie"]  ?? '';
    $fact_folio     = (string)($_POST["fact_folio"] ?? '0');
    $id_usuario     = (int)($_SESSION["id_user"] ?? 0);

    $id_receptor        = (int)($_POST['cliente_id'] ?? 0);
    $forma_pago_id      = (int)($_POST["id_forma_pago"]  ?? 0);   // INT en BD
    $metodo_pago_id     = (int)($_POST["id_metodo_pago"] ?? 0);   // lookup -> codigo SAT (varchar(3) en BD)
    $uso_cfdi_clave     = trim((string)($_POST['id_uso_cfdi'] ?? '')); // CLAVE SAT (G01, I01, etc.)

    $producto_ids      = $_POST['producto_id']     ?? [];
    $cantidades        = $_POST['cantidad']        ?? [];
    $precios_unitarios = $_POST['precio_unitario'] ?? [];
    $claves_linea      = $_POST['clave']           ?? [];
    $remision_id       = (int)($_POST['remision_id'] ?? 0);

    if ($id_receptor <= 0) {
        echo json_encode(['status' => 'error', 'msg' => 'Falta receptor']);
        exit;
    }
    if (!is_array($producto_ids) || count($producto_ids) === 0) {
        echo json_encode(['status' => 'error', 'msg' => 'Sin conceptos']);
        exit;
    }
    if ($uso_cfdi_clave === '') {
        echo json_encode(['status' => 'error', 'msg' => 'Selecciona el Uso CFDI']);
        exit;
    }

    // =========================
    // RESOLVER MÉTODO DE PAGO (código SAT) Y CP DEL CLIENTE
    // =========================
    // método de pago -> código (varchar(3) en cab_facturas)
    $metodo_pago_codigo = '';
    if ($metodo_pago_id > 0) {
        $rsMp = $clsConsulta->consultaGeneral("SELECT codigo FROM cat_metodos_pago WHERE id={$metodo_pago_id} LIMIT 1");
        $metodo_pago_codigo = trim((string)($rsMp[1]['codigo'] ?? ''));
    }
    if ($metodo_pago_codigo === '') {
        echo json_encode(['status' => 'error', 'msg' => 'Método de pago inválido']);
        exit;
    }

    // lugar_expedicion = CP del cliente (varchar(5))
    $rsCli = $clsConsulta->consultaGeneral("SELECT cp FROM cat_clientes WHERE id={$id_receptor} LIMIT 1");
    $cp_cli = isset($rsCli[1]['cp']) ? (int)$rsCli[1]['cp'] : 0;
    $lugar_expedicion = $cp_cli > 0 ? str_pad((string)$cp_cli, 5, '0', STR_PAD_LEFT) : '';
    if ($lugar_expedicion === '') {
        echo json_encode(['status' => 'error', 'msg' => 'El cliente no tiene CP registrado (lugar de expedición)']);
        exit;
    }

    // =========================
    // TRANSACCIÓN
    // =========================
    $clsConsulta->aplicaQuery("START TRANSACTION");

    // =========================
    // CÁLCULO SUBTOTAL / IVA (considerando exento por producto)
    // =========================
    $subtotal = 0.00;
    $iva      = 0.00;

    $n = count($producto_ids);
    for ($i = 0; $i < $n; $i++) {
        $idp  = (int)($producto_ids[$i] ?? 0);
        $cant = (float)($cantidades[$i] ?? 0);
        $pu   = (float)($precios_unitarios[$i] ?? 0);
        if ($idp <= 0 || $cant <= 0 || $pu <= 0) continue;

        $importe  = $cant * $pu;
        $subtotal += $importe;

        // Nota: campo en productos es 'excento'
        $rsEx = $clsConsulta->consultaGeneral("SELECT IFNULL(excento,0) AS excento FROM cat_productos WHERE id_producto={$idp} LIMIT 1");
        $esEx = (int)($rsEx[1]['excento'] ?? 0) === 1;

        if (!$esEx) {
            $iva += round($importe * 0.16, 2);
        }
    }
    $total = round($subtotal + $iva, 2);

    // =========================
    // CABECERA
    //   - metodo_pago: CÓDIGO SAT (varchar(3))
    //   - uso_cfdi: CLAVE SAT (varchar(3))
    //   - tipo_comprobante: '1'
    //   - lugar_expedicion: CP del cliente (5 dígitos)
    //   - estatus: 'PENDIENTE'
    // =========================
    $sqlCab = sprintf(
        "INSERT INTO cab_facturas
        (id_emisor, id_receptor, serie, folio, fecha, forma_pago, metodo_pago, uso_cfdi, tipo_comprobante, lugar_expedicion, subtotal, total, id_usuario, estatus)
        VALUES
        (1, %d, '%s', '%s', '%s', %d, '%s', '%s', '1', '%s', %.2f, %.2f, %d, 'PENDIENTE')",
        $id_receptor,
        addslashes($fact_serie),
        addslashes($fact_folio),
        addslashes($fecha),
        $forma_pago_id,
        addslashes($metodo_pago_codigo),
        addslashes($uso_cfdi_clave),
        addslashes($lugar_expedicion),
        $subtotal,
        $total,
        $id_usuario
    );
    $clsConsulta->guardarGeneral($sqlCab);
    $id_factura = (int)$clsConsulta->ultimoid;
    if ($id_factura <= 0) {
        $clsConsulta->aplicaQuery("ROLLBACK");
        echo json_encode(['status' => 'error', 'msg' => 'No se pudo crear la cabecera de factura']);
        exit;
    }

    // =========================
    // DETALLE + INVENTARIO
    // =========================
    for ($i = 0; $i < $n; $i++) {
        $idp   = (int)($producto_ids[$i] ?? 0);
        $cant  = (float)($cantidades[$i] ?? 0);
        $pu    = (float)($precios_unitarios[$i] ?? 0);
        $clave = isset($claves_linea[$i]) ? addslashes((string)$claves_linea[$i]) : '';

        if ($idp <= 0 || $cant <= 0 || $pu <= 0) continue;

        $rsProd = $clsConsulta->consultaGeneral("SELECT nombre, cantidad_disponible FROM cat_productos WHERE id_producto={$idp} LIMIT 1");
        $nombreProd = addslashes((string)($rsProd[1]['nombre'] ?? 'Producto ' . $idp));
        $existencia = (float)($rsProd[1]['cantidad_disponible'] ?? 0);

        $importe = round($cant * $pu, 2);

        $sqlDet = sprintf(
            "INSERT INTO mov_facturas (id_factura, id_producto, clave, cantidad, descripcion, precio, importe)
             VALUES (%d, %d, '%s', %.2f, '%s', %.2f, %.2f)",
            $id_factura,
            $idp,
            $clave,
            $cant,
            $nombreProd,
            $pu,
            $importe
        );
        $clsConsulta->aplicaQuery($sqlDet);

        // Inventario (evitar negativos)
        $nuevo = $existencia - $cant;
        if ($nuevo < 0) $nuevo = 0;
        $clsConsulta->aplicaQuery("UPDATE cat_productos SET cantidad_disponible={$nuevo} WHERE id_producto={$idp}");
    }

    // =========================
    // SIGUIENTE FOLIO
    // =========================
    $clsConsulta->aplicaQuery(sprintf(
        "UPDATE parametros SET fact_serie='%s', fact_no='%s' WHERE id=1",
        addslashes($fact_serie),
        addslashes((string)((int)$fact_folio + 1))
    ));

    // =========================
    // LIGAR REMISIÓN (si aplica)
    // =========================
    if ($remision_id > 0) {
        $clsConsulta->aplicaQuery("UPDATE cab_remisiones SET id_factura = {$id_factura} WHERE id = {$remision_id}");
    }

    // =========================
    // COMMIT
    // =========================
    $clsConsulta->aplicaQuery("COMMIT");

    echo json_encode(['status' => 'ok', 'id_factura' => $id_factura], JSON_UNESCAPED_UNICODE);
    exit;
} catch (Throwable $e) {
    try {
        $clsConsulta->aplicaQuery("ROLLBACK");
    } catch (\Throwable $e2) {
    }
    echo json_encode(['status' => 'error', 'msg' => 'Excepción en guardado: ' . $e->getMessage()], JSON_UNESCAPED_UNICODE);
    exit;
}
