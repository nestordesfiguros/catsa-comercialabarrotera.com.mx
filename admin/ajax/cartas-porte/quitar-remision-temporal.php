<?php
session_start();
$id = intval($_POST['id'] ?? 0);

if ($id > 0 && isset($_SESSION['remisiones_cartaporte'])) {
    $_SESSION['remisiones_cartaporte'] = array_diff(
        $_SESSION['remisiones_cartaporte'],
        [$id]
    );
    $_SESSION['remisiones_cartaporte'] = array_values($_SESSION['remisiones_cartaporte']); // Reindexar
}

echo json_encode(['success' => true]);
