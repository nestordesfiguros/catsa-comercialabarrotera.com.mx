<?php
// verifica-inventario.php

header('Content-Type: text/html; charset=utf-8');
@ini_set('display_errors', 1);
@ini_set('html_errors', 1);

require_once __DIR__ . '/../lib/clsConsultas.php';
$clsConsulta = new Consultas();

// Función para verificar el inventario con respecto a los movimientos
function verificarYActualizarInventario()
{
    global $clsConsulta;

    $resultados = [];

    // Verificar entradas (compras) procesadas y actualizar inventario
    $sqlEntradas = "
        SELECT mp.id_producto, mp.cantidad
        FROM mov_compras mp
        JOIN cab_compras cp ON cp.id = mp.id_orden_compra
        WHERE cp.estatus = 'procesada'
    ";

    $entradas = $clsConsulta->consultaGeneral($sqlEntradas);

    // Inicializamos el inventario a 0
    $inventario_acumulado = [];

    if ($clsConsulta->numrows > 0) {
        foreach ($entradas as $entrada) {
            // Verificar si la clave 'id_producto' está presente y tiene valor
            if (!isset($entrada['id_producto']) || (int)$entrada['id_producto'] <= 0) {
                continue; // Si no existe o es inválido, se omite
            }

            $producto_id = (int)$entrada['id_producto'];
            $cantidad = (float)$entrada['cantidad'];

            // Si el producto no está en el inventario acumulado, lo inicializamos en 0
            if (!isset($inventario_acumulado[$producto_id])) {
                $inventario_acumulado[$producto_id] = 0;
            }

            // Acumulamos las entradas
            $inventario_acumulado[$producto_id] += $cantidad;
        }
    } else {
        $resultados[] = ['estatus' => 'error', 'message' => 'No se encontraron compras procesadas'];
    }

    // Verificar salidas (remisiones) procesadas y actualizar inventario
    $sqlSalidas = "
        SELECT mr.id_producto, mr.cantidad
        FROM mov_remisiones mr
        JOIN cab_remisiones cr ON cr.id = mr.id_remision
        WHERE cr.estatus = 'procesada'
    ";

    $salidas = $clsConsulta->consultaGeneral($sqlSalidas);

    if ($clsConsulta->numrows > 0) {
        foreach ($salidas as $salida) {
            // Verificar si la clave 'id_producto' está presente y tiene valor
            if (!isset($salida['id_producto']) || (int)$salida['id_producto'] <= 0) {
                continue; // Si no existe o es inválido, se omite
            }

            $producto_id = (int)$salida['id_producto'];
            $cantidad = (float)$salida['cantidad'];

            // Si el producto no está en el inventario acumulado, lo inicializamos en 0
            if (!isset($inventario_acumulado[$producto_id])) {
                $inventario_acumulado[$producto_id] = 0;
            }

            // Restamos las salidas del inventario acumulado
            $inventario_acumulado[$producto_id] -= $cantidad;
        }
    } else {
        $resultados[] = ['estatus' => 'error', 'message' => 'No se encontraron remisiones procesadas'];
    }

    // Verificar las discrepancias entre el inventario acumulado y el inventario actual en `cat_productos`
    foreach ($inventario_acumulado as $producto_id => $cantidad_acumulada) {
        // Obtener el nombre y el inventario actual del producto
        $rsInventario = $clsConsulta->consultaGeneral("SELECT nombre, cantidad_disponible FROM cat_productos WHERE id_producto = {$producto_id}");

        if ($clsConsulta->numrows <= 0) {
            $resultados[] = [
                'producto_id' => $producto_id,
                'estatus' => 'error',
                'message' => "Producto con ID {$producto_id} no encontrado en cat_productos."
            ];
            continue;
        }

        $nombre_producto = $rsInventario[1]['nombre'] ?? 'Producto no encontrado';
        $cantidad_actual = (float)$rsInventario[1]['cantidad_disponible'];

        // Si la cantidad acumulada no coincide con el inventario actual, agregar discrepancia
        if ($cantidad_acumulada != $cantidad_actual) {
            $resultados[] = [
                'producto_id' => $producto_id,
                'estatus' => 'warning',
                'message' => [
                    'producto' => $nombre_producto,
                    'cantidad_esperada' => $cantidad_acumulada,
                    'inventario_actual' => $cantidad_actual
                ]
            ];
        }
    }

    return $resultados;
}

// Ejecutar la verificación y actualización
$response = verificarYActualizarInventario();

// Mostrar el resultado en HTML
echo "<html><head><title>Verificación de Inventario</title></head><body>";
echo "<h1>Resultados de la Verificación de Inventario</h1>";

if (empty($response)) {
    echo "<p>No se encontraron discrepancias en el inventario.</p>";
} else {
    echo "<table border='1' cellpadding='5' cellspacing='0'>";
    echo "<tr><th>Producto</th><th>Cantidad Esperada</th><th>Inventario Actual</th><th>Estatus</th><th>Acción</th></tr>";

    foreach ($response as $resultado) {
        // Asegurarse de que 'producto_id' esté presente y que no esté vacío
        if (!isset($resultado['producto_id']) || empty($resultado['producto_id'])) {
            continue; // Si no existe 'producto_id', omitir este resultado
        }

        echo "<tr>";
        // Acceder correctamente a los detalles del mensaje
        $message = $resultado['message'];

        // Obtener el nombre del producto, cantidad esperada y cantidad actual
        $producto_id = $resultado['producto_id'];
        $rsInventario = $clsConsulta->consultaGeneral("SELECT nombre, cantidad_disponible FROM cat_productos WHERE id_producto = {$producto_id}");
        $nombre_producto = $rsInventario[1]['nombre'] ?? 'Producto no encontrado';
        $cantidad_esperada = $message['cantidad_esperada'];
        $cantidad_actual = $message['inventario_actual'];

        echo "<td>" . $nombre_producto . "</td>";
        echo "<td>" . $cantidad_esperada . "</td>";
        echo "<td>" . $cantidad_actual . "</td>";
        echo "<td>" . $resultado['estatus'] . "</td>";
        echo "<td>" . "La entrada no coincide con el inventario actual." . "</td>";

        // Si es un warning, ofrecer opción de actualizar
        if ($resultado['estatus'] == 'warning') {
            echo "<td><form method='POST' action='update-inventario.php'>
                    <input type='hidden' name='producto_id' value='" . $resultado['producto_id'] . "' />
                    <button type='submit'>Actualizar Inventario</button>
                  </form></td>";
        } else {
            echo "<td></td>";
        }
        echo "</tr>";
    }

    echo "</table>";
}

echo "</body></html>";
exit;
