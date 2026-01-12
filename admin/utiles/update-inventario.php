<?php
// update-inventario.php

header('Content-Type: text/html; charset=utf-8');
@ini_set('display_errors', 1);
@ini_set('html_errors', 1);

require_once __DIR__ . '/../lib/clsConsultas.php';
$clsConsulta = new Consultas();

// Verificar que se haya recibido un 'producto_id'
if (!isset($_POST['producto_id']) || empty($_POST['producto_id'])) {
    echo "Error: Producto no especificado.";
    exit;
}

$producto_id = (int)$_POST['producto_id'];

// Verificar que el producto exista
$rsProducto = $clsConsulta->consultaGeneral("SELECT nombre, cantidad_disponible FROM cat_productos WHERE id_producto = {$producto_id}");

if ($clsConsulta->numrows <= 0) {
    echo "Error: Producto con ID {$producto_id} no encontrado.";
    exit;
}

$nombre_producto = $rsProducto[1]['nombre'] ?? 'Producto no encontrado';
$cantidad_disponible = (int)$rsProducto[1]['cantidad_disponible'];

// Verificar la cantidad esperada (debe ser proporcionada por la lógica de verificación)
$cantidad_esperada = 20; // Este valor debería ser el que corresponde al producto y el movimiento

// Comparar la cantidad esperada con la cantidad disponible en inventario
$diferencia = $cantidad_disponible - $cantidad_esperada;

if ($diferencia == 0) {
    echo "<p>El inventario ya está correcto para el producto: {$nombre_producto}. No se requiere actualización.</p>";
    exit;
}

// Actualizar el inventario en la tabla `inventarios`
if ($diferencia > 0) {
    // Si el inventario es mayor que la cantidad esperada, restamos la diferencia
    $nuevo_inventario = $cantidad_disponible - $diferencia;
} else {
    // Si el inventario es menor que la cantidad esperada, sumamos la diferencia
    $nuevo_inventario = $cantidad_disponible + abs($diferencia);
}

// Actualizamos el inventario en `cat_productos`
$sqlUpdateProducto = "
    UPDATE cat_productos
    SET cantidad_disponible = {$nuevo_inventario}
    WHERE id_producto = {$producto_id}
";

if ($clsConsulta->aplicaQuery($sqlUpdateProducto)) {
    echo "<p>Inventario actualizado correctamente para el producto: {$nombre_producto}.</p>";
    echo "<p>Nuevo inventario total: {$nuevo_inventario}</p>";
    echo '<a href="verifica-inventario.php">Volver a Verificar Inventario</a>';
} else {
    echo "<p>Error al actualizar el producto {$nombre_producto}.</p>";
}

exit;
