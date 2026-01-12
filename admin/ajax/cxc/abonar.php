<?php
session_start();
require_once '../../lib/clsConsultas.php';
$clsConsulta = new Consultas();

$logFile = __DIR__ . '/log_comisiones.txt';
file_put_contents($logFile, "== ABONO INICIADO ==\n", FILE_APPEND);

$id_cxc = intval($_POST['id_cxc']);
$concepto = $clsConsulta->escape(trim($_POST['concepto']));
$abono = floatval($_POST['abono']);
$fecha = isset($_POST['fecha']) && $_POST['fecha'] !== '' ? $_POST['fecha'] : date('Y-m-d');
$id_usuario = $_SESSION['id_user'] ?? 0;

file_put_contents($logFile, "Abono: $abono | Fecha: $fecha | ID Usuario: $id_usuario\n", FILE_APPEND);

if ($abono <= 0) {
    file_put_contents($logFile, "Abono inválido\n", FILE_APPEND);
    echo 'El abono debe ser mayor a cero';
    exit;
}

// 1. Guarda el abono
$sql = "INSERT INTO mov_cxc (id_cxc, concepto, abono, fecha, id_usuario)
        VALUES ($id_cxc, '$concepto', $abono, '$fecha', $id_usuario)";
$ok = $clsConsulta->guardarGeneral($sql);

if ($ok) {
    file_put_contents($logFile, "Abono guardado correctamente\n", FILE_APPEND);

    // 2. Resta saldo
    $sqlSaldo = "UPDATE cab_cxc SET saldo = saldo - $abono WHERE id_cxc = $id_cxc";
    $clsConsulta->aplicaquery($sqlSaldo);
    file_put_contents($logFile, "Saldo actualizado\n", FILE_APPEND);

    // 3. Marca como pagada si saldo <= 0
    $sqlEstatus = "UPDATE cab_cxc SET estatus = 'Pagada' WHERE id_cxc = $id_cxc AND saldo <= 0";
    $clsConsulta->aplicaquery($sqlEstatus);
    file_put_contents($logFile, "Intento de marcar como Pagada\n", FILE_APPEND);

    // 4. Verifica si ya está completamente pagada para comisión
    $sqlVerCxC = "
        SELECT 
            cxc.id_remision, 
            r.id_vendedor, 
            cxc.fecha_emision 
        FROM cab_cxc cxc
        INNER JOIN cab_remisiones r ON cxc.id_remision = r.id
        WHERE cxc.id_cxc = $id_cxc 
        AND cxc.saldo <= 0 
        AND cxc.estatus = 'Pagada'
    ";
    $resCxC = $clsConsulta->consultaGeneral($sqlVerCxC);

    file_put_contents($logFile, "Consulta CxC ejecutada\n", FILE_APPEND);

    if ($clsConsulta->numrows > 0 && isset($resCxC[1])) {
        $id_remision   = intval($resCxC[1]['id_remision'] ?? 0);
        $id_vendedor   = intval($resCxC[1]['id_vendedor'] ?? 0);
        $fechaCxC      = $resCxC[1]['fecha_emision'] ?? null;

        file_put_contents($logFile, "CxC válida: Remision=$id_remision | Vendedor=$id_vendedor | Fecha=$fechaCxC\n", FILE_APPEND);

        if (!$fechaCxC || !strtotime($fechaCxC)) {
            $fechaCxC = date('Y-m-d');
        }

        $inicio_semana = date('Y-m-d', strtotime('monday this week', strtotime($fechaCxC)));
        $fin_semana    = date('Y-m-d', strtotime('sunday this week', strtotime($fechaCxC)));

        // 5. Busca comisión
        $sqlCom = "SELECT id FROM comisiones WHERE id_vendedor = $id_vendedor AND fecha_inicio = '$inicio_semana' AND fecha_fin = '$fin_semana' LIMIT 1";
        $resCom = $clsConsulta->consultaGeneral($sqlCom);
        $id_comision = $resCom[1]['id'] ?? 0;

        if ($id_comision <= 0 && $id_vendedor > 0) {
            $sqlInsert = "INSERT INTO comisiones (id_vendedor, fecha_inicio, fecha_fin, estatus)
                          VALUES ($id_vendedor, '$inicio_semana', '$fin_semana', 'pendiente')";
            $clsConsulta->guardarGeneral($sqlInsert);
            $id_comision = $clsConsulta->ultimoid;
            file_put_contents($logFile, "Comisión creada: ID=$id_comision\n", FILE_APPEND);
        } else {
            file_put_contents($logFile, "Comisión ya existía: ID=$id_comision\n", FILE_APPEND);
        }

        // 6. Insertar detalle
        if ($id_comision > 0 && $id_cxc > 0 && $id_remision > 0) {
            $sqlPorc = "SELECT comision FROM cat_vendedores WHERE id = $id_vendedor LIMIT 1";
            $resPorc = $clsConsulta->consultaGeneral($sqlPorc);
            $porcentaje = floatval($resPorc[1]['comision'] ?? 0);

            $sqlMonto = "SELECT SUM(cantidad * precio) AS total FROM mov_remisiones WHERE id_remision = $id_remision";
            $resMonto = $clsConsulta->consultaGeneral($sqlMonto);
            $monto_venta = floatval($resMonto[1]['total'] ?? 0);

            if ($monto_venta > 0 && $porcentaje > 0) {
                $total_comision = round($monto_venta * $porcentaje / 100, 2);

                $sqlInsertDet = "INSERT INTO comisiones_detalle
                    (id_comision, id_cxc, id_remision, monto_venta, porcentaje, total_comision)
                    VALUES ($id_comision, $id_cxc, $id_remision, $monto_venta, $porcentaje, $total_comision)";
                $clsConsulta->guardarGeneral($sqlInsertDet);
                file_put_contents($logFile, "Comisión detalle insertada: $sqlInsertDet\n", FILE_APPEND);
            } else {
                file_put_contents($logFile, "Monto venta o porcentaje inválido. Monto=$monto_venta, Porc=$porcentaje\n", FILE_APPEND);
            }
        } else {
            file_put_contents($logFile, "Faltan datos para insertar detalle. ID comisión=$id_comision\n", FILE_APPEND);
        }
    } else {
        file_put_contents($logFile, "No se encontró CxC pagada para aplicar comisión\n", FILE_APPEND);
    }

    echo 'success';
} else {
    file_put_contents($logFile, "Error al guardar abono en mov_cxc\n", FILE_APPEND);
    echo 'Error al registrar abono';
}
