<?php
// ajax/almacen-salidas/guardar.php
session_start();
include '../../lib/clsConsultas.php';
$clsConsulta = new Consultas();

try {
    $idEmpresa = isset($_SESSION['id_empresa']) ? (int)$_SESSION['id_empresa'] : 0;
    $idUsuario = isset($_SESSION['id_user']) ? (int)$_SESSION['id_user'] : 0;

    if ($idEmpresa <= 0 || $idUsuario <= 0) {
        echo "error: Sesión inválida.";
        exit;
    }

    $fecha = isset($_POST['fecha']) ? $_POST['fecha'] : date('Y-m-d');
    $idAlmacen = isset($_POST['almacen_id']) ? (int)$_POST['almacen_id'] : 0;

    $tipo = isset($_POST['tipo_salida']) ? trim($_POST['tipo_salida']) : 'venta';
    $referencia = isset($_POST['referencia']) ? addslashes(trim($_POST['referencia'])) : '';
    $observaciones = isset($_POST['observaciones']) ? addslashes(trim($_POST['observaciones'])) : '';

    $idCliente = isset($_POST['cliente_id']) ? (int)$_POST['cliente_id'] : 0;
    $destinoLibre = '';
    if ($idCliente <= 0) {
        // Si no hay cliente id, tomamos el texto (opcional)
        $destinoLibre = isset($_POST['cliente_nombre']) ? addslashes(trim($_POST['cliente_nombre'])) : '';
        // En este form no enviamos cliente_nombre, así que usamos el input visible si quieres:
        // (No es obligatorio; puedes dejarlo en blanco)
    }

    $productoIds = isset($_POST['producto_id']) ? $_POST['producto_id'] : [];
    $cantidades = isset($_POST['cantidad']) ? $_POST['cantidad'] : [];

    if ($idAlmacen <= 0) {
        echo "error: Almacén inválido.";
        exit;
    }
    if (!is_array($productoIds) || count($productoIds) < 1) {
        echo "error: Sin productos.";
        exit;
    }
    if (!is_array($cantidades) || count($cantidades) < 1) {
        echo "error: Sin cantidades.";
        exit;
    }

    // Validar almacén pertenece a empresa
    $chkA = $clsConsulta->consultaGeneral("SELECT id FROM cat_almacenes WHERE id={$idAlmacen} AND id_empresa={$idEmpresa} AND estatus=1 LIMIT 1");
    if ($clsConsulta->numrows < 1) {
        echo "error: Almacén no pertenece a la empresa.";
        exit;
    }

    // Limpieza productos
    $items = [];
    for ($i = 0; $i < count($productoIds); $i++) {
        $pid = (int)$productoIds[$i];
        $qty = isset($cantidades[$i]) ? (float)$cantidades[$i] : 0;

        if ($pid > 0 && $qty > 0) {
            $items[] = ["id_producto" => $pid, "cantidad" => $qty];
        }
    }

    if (count($items) < 1) {
        echo "error: Cantidades inválidas.";
        exit;
    }

    // Transacción
    $clsConsulta->aplicaQuery("START TRANSACTION");

    $refSql = ($referencia !== '') ? "'{$referencia}'" : "NULL";
    $obsSql = ($observaciones !== '') ? "'{$observaciones}'" : "NULL";
    $cliSql = ($idCliente > 0) ? $idCliente : "NULL";
    $destLibreSql = ($destinoLibre !== '') ? "'{$destinoLibre}'" : "NULL";

    $tipoSql = addslashes($tipo);

    // Insert cabecera (por defecto PROCESADA)
    $sqlCab = "
        INSERT INTO cab_salidas_almacen
        (id_empresa, id_almacen, id_cliente, destino_libre, fecha, id_usuario, estatus, tipo_salida, referencia, observaciones)
        VALUES
        ({$idEmpresa}, {$idAlmacen}, {$cliSql}, {$destLibreSql}, '{$fecha}', {$idUsuario}, 'procesada', '{$tipoSql}', {$refSql}, {$obsSql})
    ";
    $clsConsulta->guardarGeneral($sqlCab);
    $idSalida = (int)$clsConsulta->ultimoid;

    if ($idSalida <= 0) {
        $clsConsulta->aplicaQuery("ROLLBACK");
        echo "error: No se pudo guardar la cabecera.";
        exit;
    }

    // Para cada item: validar inventario y descontar
    foreach ($items as $it) {
        $pid = (int)$it["id_producto"];
        $qty = (float)$it["cantidad"];

        // Leer existencia actual
        $rsInv = $clsConsulta->consultaGeneral("
            SELECT id, cantidad
            FROM inventarios
            WHERE id_empresa={$idEmpresa} AND id_almacen={$idAlmacen} AND id_producto={$pid}
            LIMIT 1
        ");

        if ($clsConsulta->numrows < 1) {
            $clsConsulta->aplicaQuery("ROLLBACK");
            echo "error: Producto sin inventario en este almacén (ID {$pid}).";
            exit;
        }

        $idInv = (int)$rsInv[1]['id'];
        $exist = (float)$rsInv[1]['cantidad'];

        if ($qty > $exist) {
            $clsConsulta->aplicaQuery("ROLLBACK");
            echo "error: Stock insuficiente para producto ID {$pid}. Existencia: {$exist}";
            exit;
        }

        // Insert movimiento
        $sqlMov = "
            INSERT INTO mov_salidas_almacen (id_salida, id_producto, cantidad)
            VALUES ({$idSalida}, {$pid}, {$qty})
        ";
        $clsConsulta->aplicaQuery($sqlMov);

        // Descontar inventario
        $nueva = $exist - $qty;
        $clsConsulta->aplicaQuery("UPDATE inventarios SET cantidad={$nueva} WHERE id={$idInv} AND id_empresa={$idEmpresa}");

        // Compatibilidad: disminuir cantidad_disponible en cat_productos (si lo sigues usando en UI)
        $rsProd = $clsConsulta->consultaGeneral("SELECT cantidad_disponible FROM cat_productos WHERE id_producto={$pid} LIMIT 1");
        if ($clsConsulta->numrows > 0) {
            $disp = (float)($rsProd[1]['cantidad_disponible'] ?? 0);
            $dispNueva = $disp - $qty;
            if ($dispNueva < 0) $dispNueva = 0;
            $clsConsulta->aplicaQuery("UPDATE cat_productos SET cantidad_disponible={$dispNueva} WHERE id_producto={$pid}");
        }
    }

    $clsConsulta->aplicaQuery("COMMIT");
    echo "success";
    exit;
} catch (Throwable $e) {
    try {
        $clsConsulta->aplicaQuery("ROLLBACK");
    } catch (Throwable $ignored) {
    }
    echo "error: " . $e->getMessage();
    exit;
}
