<?php
session_start();

// Limpiar carrito y pedido activo
unset($_SESSION['pedidoActivo']);
unset($_SESSION['carrito']); // si usas una variable así

// Redirigir al inicio o donde gustes

header('Location: ../inicio');

exit;

