<?php
// ajax/salidas-almacen/guardar.php
require_once '../../lib/clsConsultas.php';
header('Content-Type: application/json');
session_start();
$clsConsulta = new Consultas();

$cabecera   = $_POST['cabecera']   ?? null;
$detalles   = $_POST['detalles']   ?? [];
$remisiones = $_POST['remisiones'] ?? [];
$autoFix    = intval($_POST['resolver_faltantes'] ?? 0) === 1;

if (!$cabecera || !is_array($detalles) || count($detalles) == 0) {
    echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
    exit;
}

$id_almacen = intval($cabecera['id_almacen'] ?? 0);
$id_cliente = intval($cabecera['id_cliente'] ?? 0);
$obs        = $clsConsulta->escape($cabecera['observaciones'] ?? '');
$id_usuario = intval($_SESSION['id_usuario'] ?? 0);
if ($id_almacen <= 0) {
    echo json_encode(['success' => false, 'message' => 'Selecciona almacén']);
    exit;
}

/* 1) Verificar existencias y preparar faltantes */
$faltantes = []; // array de arrays con detalle por producto
foreach ($detalles as $d) {
    $idp  = intval($d['id_producto']);
    $cant = (float)$d['cantidad'];
    if ($idp <= 0 || $cant <= 0) {
        echo json_encode(['success' => false, 'message' => 'Partidas inválidas']);
        exit;
    }

    $ex = $clsConsulta->consultaGeneral("
        SELECT cantidad FROM inventarios 
        WHERE id_almacen=$id_almacen AND id_producto=$idp LIMIT 1
    ");
    $disp = (float)($ex[1]['cantidad'] ?? 0);

    if ($disp < $cant) {
        // Buscar nombre producto
        $np = $clsConsulta->consultaGeneral("SELECT nombre FROM cat_productos WHERE id_producto=$idp LIMIT 1");
        $nombreProd = $np[1]['nombre'] ?? 'Producto ' . $idp;

        // Buscar nombre almacén origen
        $na = $clsConsulta->consultaGeneral("SELECT almacen FROM cat_almacenes WHERE id=$id_almacen");
        $nombreAlm = $na[1]['almacen'] ?? ('Almacén ' . $id_almacen);

        // Sugerencias en otros almacenes
        $otros = $clsConsulta->consultaGeneral("
            SELECT i.id_almacen, a.almacen, i.cantidad
            FROM inventarios i
            INNER JOIN cat_almacenes a ON a.id=i.id_almacen
            WHERE i.id_producto=$idp AND i.id_almacen<>$id_almacen AND i.cantidad>0
            ORDER BY i.cantidad DESC
        ");
        $otras_existencias = [];
        if ($clsConsulta->numrows > 0) {
            foreach ($otros as $k => $o) {
                if ($k === 0) continue;
                $otras_existencias[] = [
                    'id_almacen' => (int)$o['id_almacen'],
                    'almacen'    => $o['almacen'],
                    'disponible' => (float)$o['cantidad']
                ];
            }
        }

        $faltantes[] = [
            'id_producto'      => $idp,
            'nombre'           => $nombreProd,
            'requerido'        => $cant,
            'disponible'       => $disp,
            'faltante'         => $cant - $disp,
            'almacen_origen'   => ['id' => $id_almacen, 'nombre' => $nombreAlm],
            'otras_existencias' => $otras_existencias
        ];
    }
}

/* 2) Si hay faltantes */
if (count($faltantes) > 0) {
    if (!$autoFix) {
        http_response_code(422);
        echo json_encode([
            'success'   => false,
            'code'      => 'INVENTORY_SHORTAGE',
            'message'   => 'Inventario insuficiente para algunos productos.',
            'faltantes' => $faltantes
        ]);
        exit;
    }

    // Con autoFix = resolver_faltantes, generamos traspasos
    $traspasos_generados = [];
    foreach ($faltantes as $f) {
        $idp = $f['id_producto'];
        $restante = $f['faltante'];

        $otros = $clsConsulta->consultaGeneral("
            SELECT id_almacen, cantidad
            FROM inventarios
            WHERE id_producto=$idp AND id_almacen<>$id_almacen AND cantidad>0
            ORDER BY cantidad DESC
        ");
        if (!$otros || $clsConsulta->numrows <= 0) {
            echo json_encode(['success' => false, 'message' => "No hay stock para cubrir faltante de {$f['nombre']}"]);
            exit;
        }

        foreach ($otros as $k => $row) {
            if ($k === 0) continue;
            $id_origen = (int)$row['id_almacen'];
            $disp_ori  = (float)$row['cantidad'];
            if ($disp_ori <= 0) continue;

            $mover = min($disp_ori, $restante);

            $obsT = "Auto-traspaso por salida-almacen al almacén #$id_almacen";
            $clsConsulta->guardarGeneral("
                INSERT INTO cab_traspasos_almacen (id_almacen_origen, id_almacen_destino, observaciones, estatus, id_usuario)
                VALUES ($id_origen, $id_almacen, '$obsT', 'activo', $id_usuario)
            ");
            $id_traspaso = (int)$clsConsulta->ultimoid;

            $clsConsulta->aplicaquery("
                INSERT INTO mov_traspasos_almacen (id_traspaso, id_producto, cantidad)
                VALUES ($id_traspaso, $idp, $mover)
            ");

            $clsConsulta->aplicaquery("
                UPDATE inventarios SET cantidad = IFNULL(cantidad,0) - $mover
                WHERE id_almacen=$id_origen AND id_producto=$idp
            ");
            $clsConsulta->aplicaquery("
                INSERT INTO inventarios (id_almacen, id_producto, cantidad)
                VALUES ($id_almacen, $idp, $mover)
                ON DUPLICATE KEY UPDATE cantidad = IFNULL(cantidad,0) + VALUES(cantidad)
            ");

            $traspasos_generados[] = [
                'id_traspaso' => $id_traspaso,
                'id_almacen_origen' => $id_origen,
                'id_producto' => $idp,
                'cantidad' => $mover
            ];

            $restante -= $mover;
            if ($restante <= 0) break;
        }

        if ($restante > 0) {
            echo json_encode(['success' => false, 'message' => "No se pudo cubrir faltante de {$f['nombre']}"]);
            exit;
        }
    }
}

/* 3) Crear cabecera salida */
$total = 0.0;
foreach ($detalles as $d) {
    $total += (float)$d['cantidad'] * (float)$d['precio_unitario'];
}
$clsConsulta->guardarGeneral("
  INSERT INTO cab_salidas_almacen (id_almacen, id_cliente, observaciones, total, estatus, id_usuario)
  VALUES ($id_almacen, " . ($id_cliente ?: 'NULL') . ", '$obs', $total, 'activo', $id_usuario)
");
$id_salida = (int)$clsConsulta->ultimoid;
if ($id_salida <= 0) {
    echo json_encode(['success' => false, 'message' => 'No se pudo crear la salida']);
    exit;
}

/* 4) Insertar remisiones relacionadas */
if (is_array($remisiones) && count($remisiones) > 0) {
    foreach ($remisiones as $id_rem) {
        $id_rem = (int)$id_rem;
        if ($id_rem > 0) {
            $clsConsulta->aplicaquery("INSERT IGNORE INTO rel_salidas_remisiones (id_salida, id_remision) VALUES ($id_salida, $id_rem)");
        }
    }
}

/* 5) Detalle + descuento inventario */
foreach ($detalles as $d) {
    $idp  = (int)$d['id_producto'];
    $cant = (float)$d['cantidad'];
    $pu   = (float)$d['precio_unitario'];
    $id_rem = isset($d['id_remision']) ? (int)$d['id_remision'] : 'NULL';
    $mot  = $clsConsulta->escape($d['motivo'] ?? '');

    $clsConsulta->aplicaquery("
        INSERT INTO mov_salidas_almacen (id_salida, id_producto, cantidad, precio_unitario, id_remision, motivo)
        VALUES ($id_salida, $idp, $cant, $pu, " . ($id_rem ?: 'NULL') . ", '$mot')
    ");

    $clsConsulta->aplicaquery("
        UPDATE inventarios
        SET cantidad = IFNULL(cantidad,0) - $cant
        WHERE id_almacen=$id_almacen AND id_producto=$idp
    ");
}

/* 6) Respuesta final */
echo json_encode([
    'success' => true,
    'message' => 'Salida registrada',
    'id_salida' => $id_salida
]);
