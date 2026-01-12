<?php
// ajax/almacen-salidas/estatus.php
session_start();
include '../../lib/clsConsultas.php';
$clsConsulta = new Consultas();

$idEmpresa = isset($_SESSION['id_empresa']) ? (int)$_SESSION['id_empresa'] : 0;
$idSalida  = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$estatusIn = isset($_POST['estatus']) ? (int)$_POST['estatus'] : 0;

if ($idEmpresa <= 0 || $idSalida <= 0 || !in_array($estatusIn, [1, 2, 3], true)) {
    echo "error";
    exit;
}

$map = [1 => 'pendiente', 2 => 'procesada', 3 => 'cancelada'];
$nuevo = $map[$estatusIn];

// Leer estado actual + almacén
$rs = $clsConsulta->consultaGeneral("
    SELECT id, id_almacen, estatus
    FROM cab_salidas_almacen
    WHERE id={$idSalida} AND id_empresa={$idEmpresa}
    LIMIT 1
");

if ($clsConsulta->numrows < 1) {
    echo "error: salida no encontrada";
    exit;
}

$actual = (string)$rs[1]['estatus'];
$idAlmacen = (int)$rs[1]['id_almacen'];

// Regla: si ya cancelada, no permitir cambios
if ($actual === 'cancelada') {
    echo "error: no se puede cambiar una salida cancelada";
    exit;
}

// Si no hay cambio
if ($actual === $nuevo) {
    echo "success";
    exit;
}

try {
    $clsConsulta->aplicaQuery("START TRANSACTION");

    // Si pasa a cancelada desde procesada -> revertir inventario
    if ($nuevo === 'cancelada' && $actual === 'procesada') {
        $movs = $clsConsulta->consultaGeneral("
            SELECT id_producto, cantidad
            FROM mov_salidas_almacen
            WHERE id_salida={$idSalida}
        ");

        if ($clsConsulta->numrows > 0 && is_array($movs)) {
            foreach ($movs as $m) {
                $pid = (int)$m['id_producto'];
                $qty = (float)$m['cantidad'];

                // Regresar a inventarios
                $rsInv = $clsConsulta->consultaGeneral("
                    SELECT id, cantidad
                    FROM inventarios
                    WHERE id_empresa={$idEmpresa} AND id_almacen={$idAlmacen} AND id_producto={$pid}
                    LIMIT 1
                ");

                if ($clsConsulta->numrows > 0) {
                    $idInv = (int)$rsInv[1]['id'];
                    $exist = (float)$rsInv[1]['cantidad'];
                    $clsConsulta->aplicaQuery("UPDATE inventarios SET cantidad=" . ($exist + $qty) . " WHERE id={$idInv} AND id_empresa={$idEmpresa}");
                } else {
                    // Si no existía, crearlo
                    $clsConsulta->aplicaQuery("INSERT INTO inventarios (id_empresa, id_almacen, id_producto, cantidad) VALUES ({$idEmpresa}, {$idAlmacen}, {$pid}, {$qty})");
                }

                // Compatibilidad cat_productos
                $rsProd = $clsConsulta->consultaGeneral("SELECT cantidad_disponible FROM cat_productos WHERE id_producto={$pid} LIMIT 1");
                if ($clsConsulta->numrows > 0) {
                    $disp = (float)($rsProd[1]['cantidad_disponible'] ?? 0);
                    $clsConsulta->aplicaQuery("UPDATE cat_productos SET cantidad_disponible=" . ($disp + $qty) . " WHERE id_producto={$pid}");
                }
            }
        }
    }

    // Si pasa de pendiente a procesada -> descontar inventario (por si llegas a usar pendiente)
    if ($nuevo === 'procesada' && $actual === 'pendiente') {
        $movs = $clsConsulta->consultaGeneral("
            SELECT id_producto, cantidad
            FROM mov_salidas_almacen
            WHERE id_salida={$idSalida}
        ");

        if ($clsConsulta->numrows > 0 && is_array($movs)) {
            // Validar primero
            foreach ($movs as $m) {
                $pid = (int)$m['id_producto'];
                $qty = (float)$m['cantidad'];

                $rsInv = $clsConsulta->consultaGeneral("
                    SELECT id, cantidad
                    FROM inventarios
                    WHERE id_empresa={$idEmpresa} AND id_almacen={$idAlmacen} AND id_producto={$pid}
                    LIMIT 1
                ");
                if ($clsConsulta->numrows < 1) {
                    $clsConsulta->aplicaQuery("ROLLBACK");
                    echo "error: producto sin inventario para procesar";
                    exit;
                }
                $exist = (float)$rsInv[1]['cantidad'];
                if ($qty > $exist) {
                    $clsConsulta->aplicaQuery("ROLLBACK");
                    echo "error: stock insuficiente para procesar";
                    exit;
                }
            }

            // Descontar
            foreach ($movs as $m) {
                $pid = (int)$m['id_producto'];
                $qty = (float)$m['cantidad'];

                $rsInv = $clsConsulta->consultaGeneral("
                    SELECT id, cantidad
                    FROM inventarios
                    WHERE id_empresa={$idEmpresa} AND id_almacen={$idAlmacen} AND id_producto={$pid}
                    LIMIT 1
                ");

                $idInv = (int)$rsInv[1]['id'];
                $exist = (float)$rsInv[1]['cantidad'];
                $clsConsulta->aplicaQuery("UPDATE inventarios SET cantidad=" . ($exist - $qty) . " WHERE id={$idInv} AND id_empresa={$idEmpresa}");

                $rsProd = $clsConsulta->consultaGeneral("SELECT cantidad_disponible FROM cat_productos WHERE id_producto={$pid} LIMIT 1");
                if ($clsConsulta->numrows > 0) {
                    $disp = (float)($rsProd[1]['cantidad_disponible'] ?? 0);
                    $dispNueva = $disp - $qty;
                    if ($dispNueva < 0) $dispNueva = 0;
                    $clsConsulta->aplicaQuery("UPDATE cat_productos SET cantidad_disponible={$dispNueva} WHERE id_producto={$pid}");
                }
            }
        }
    }

    // Actualizar estatus
    $clsConsulta->aplicaQuery("
        UPDATE cab_salidas_almacen
        SET estatus='{$nuevo}'
        WHERE id={$idSalida} AND id_empresa={$idEmpresa}
    ");

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
