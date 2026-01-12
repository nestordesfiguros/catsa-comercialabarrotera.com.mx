<?php
// ajax/facturas/modificar.php
header('Content-Type: application/json; charset=utf-8');
@ini_set('display_errors', 0);
@ini_set('html_errors', 0);

session_start();
require_once __DIR__ . '/../../lib/clsConsultas.php';
$clsConsulta = new Consultas();

try {
    $id_factura = (int)($_POST['id_factura'] ?? 0);
    if ($id_factura <= 0) {
        echo json_encode(['status' => 'error', 'msg' => 'ID inválido']);
        exit;
    }

    // cargar cabecera y validar que NO esté timbrada
    $cab = $clsConsulta->consultaGeneral("SELECT id, uuid, IFNULL(timbrada,0) AS timbrada FROM cab_facturas WHERE id={$id_factura} LIMIT 1");
    if (!is_array($cab) || $clsConsulta->numrows <= 0) {
        echo json_encode(['status' => 'error', 'msg' => 'Factura no existe']);
        exit;
    }
    $uuid = (string)($cab[1]['uuid'] ?? '');
    $timbrada = (int)($cab[1]['timbrada'] ?? 0);
    if (trim($uuid) !== '' || $timbrada === 1) {
        echo json_encode(['status' => 'error', 'msg' => 'La factura ya está timbrada; no se puede editar']);
        exit;
    }

    // Entradas
    $fecha          = $_POST["fact_fecha"]  ?? date('Y-m-d');
    $fact_serie     = $_POST["fact_serie"]  ?? '';
    $fact_folio     = (string)($_POST["fact_folio"] ?? '');
    $id_usuario     = (int)($_SESSION["id_user"] ?? 0);

    $id_receptor        = (int)($_POST['cliente_id'] ?? 0);
    $forma_pago_id      = (int)($_POST["id_forma_pago"]  ?? 0); // INT
    $metodo_pago_id     = (int)($_POST["id_metodo_pago"] ?? 0); // lookup -> código SAT
    $uso_cfdi_clave     = trim((string)($_POST['id_uso_cfdi'] ?? $_POST['id_uso_cfdi'] ?? '')); // opcional

    $producto_ids      = $_POST['producto_id']     ?? [];
    $cantidades        = $_POST['cantidad']        ?? [];
    $precios_unitarios = $_POST['precio_unitario'] ?? [];
    $claves_linea      = $_POST['clave']           ?? [];

    if ($id_receptor <= 0) {
        echo json_encode(['status' => 'error', 'msg' => 'Falta receptor']);
        exit;
    }
    if (!is_array($producto_ids) || count($producto_ids) === 0) {
        echo json_encode(['status' => 'error', 'msg' => 'Sin conceptos']);
        exit;
    }

    // método de pago -> código SAT
    $metodo_pago_codigo = '';
    if ($metodo_pago_id > 0) {
        $rsMp = $clsConsulta->consultaGeneral("SELECT codigo FROM cat_metodos_pago WHERE id={$metodo_pago_id} LIMIT 1");
        $metodo_pago_codigo = trim((string)($rsMp[1]['codigo'] ?? ''));
    }
    if ($metodo_pago_codigo === '') {
        echo json_encode(['status' => 'error', 'msg' => 'Método de pago inválido']);
        exit;
    }

    // CP cliente (lugar expedición)
    $rsCli = $clsConsulta->consultaGeneral("SELECT cp FROM cat_clientes WHERE id={$id_receptor} LIMIT 1");
    $cp_cli = isset($rsCli[1]['cp']) ? (int)$rsCli[1]['cp'] : 0;
    $lugar_expedicion = $cp_cli > 0 ? str_pad((string)$cp_cli, 5, '0', STR_PAD_LEFT) : '';
    if ($lugar_expedicion === '') {
        echo json_encode(['status' => 'error', 'msg' => 'El cliente no tiene CP registrado']);
        exit;
    }

    // Cálculo subtotal / IVA (respeta exento por producto)
    $subtotal = 0.00;
    $iva = 0.00;
    $n = count($producto_ids);
    for ($i = 0; $i < $n; $i++) {
        $idp  = (int)($producto_ids[$i] ?? 0);
        $cant = (float)($cantidades[$i] ?? 0);
        $pu   = (float)($precios_unitarios[$i] ?? 0);
        if ($idp <= 0 || $cant <= 0 || $pu <= 0) continue;
        $importe = $cant * $pu;
        $subtotal += $importe;
        $rsEx = $clsConsulta->consultaGeneral("SELECT IFNULL(excento,0) AS excento FROM cat_productos WHERE id_producto={$idp} LIMIT 1");
        $esEx = (int)($rsEx[1]['excento'] ?? 0) === 1;
        if (!$esEx) {
            $iva += round($importe * 0.16, 2);
        }
    }
    $total = round($subtotal + $iva, 2);

    // Transacción
    $clsConsulta->aplicaQuery("START TRANSACTION");

    // Revertir inventario previo y limpiar detalle
    $prev = $clsConsulta->consultaGeneral("SELECT id_producto, cantidad FROM mov_facturas WHERE id_factura={$id_factura} AND IFNULL(activo,1)=1");
    if (is_array($prev) && $clsConsulta->numrows > 0) {
        foreach ($prev as $i => $pv) {
            $idpAnt = (int)$pv['id_producto'];
            $cantAnt = (float)$pv['cantidad'];
            // regresar existencia
            $rsProd = $clsConsulta->consultaGeneral("SELECT cantidad_disponible FROM cat_productos WHERE id_producto={$idpAnt} LIMIT 1");
            $exist = (float)($rsProd[1]['cantidad_disponible'] ?? 0);
            $nuevo = $exist + $cantAnt;
            $clsConsulta->aplicaQuery("UPDATE cat_productos SET cantidad_disponible={$nuevo} WHERE id_producto={$idpAnt}");
        }
    }
    $clsConsulta->aplicaQuery("DELETE FROM mov_facturas WHERE id_factura={$id_factura}");

    // Actualizar cabecera
    $sqlCab = sprintf(
        "UPDATE cab_facturas
     SET id_receptor=%d, serie='%s', folio='%s', fecha='%s',
         forma_pago=%d, metodo_pago='%s', uso_cfdi='%s',
         lugar_expedicion='%s', subtotal=%.2f, total=%.2f,
         id_usuario=%d, estatus='PENDIENTE'
     WHERE id=%d",
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
        $id_usuario,
        $id_factura
    );
    $clsConsulta->aplicaQuery($sqlCab);

    // Insertar nuevo detalle y afectar inventario
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

        // disminuir existencia
        $nuevo = $existencia - $cant;
        if ($nuevo < 0) $nuevo = 0;
        $clsConsulta->aplicaQuery("UPDATE cat_productos SET cantidad_disponible={$nuevo} WHERE id_producto={$idp}");
    }

    $clsConsulta->aplicaQuery("COMMIT");
    echo json_encode(['status' => 'ok', 'id_factura' => $id_factura], JSON_UNESCAPED_UNICODE);
    exit;
} catch (Throwable $e) {
    try {
        $clsConsulta->aplicaQuery("ROLLBACK");
    } catch (\Throwable $e2) {
    }
    echo json_encode(['status' => 'error', 'msg' => 'Excepción: ' . $e->getMessage()], JSON_UNESCAPED_UNICODE);
    exit;
}
