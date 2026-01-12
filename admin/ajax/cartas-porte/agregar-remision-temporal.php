<?php
session_start();
$id = intval($_POST['id'] ?? 0);

if ($id > 0) {
    if (!isset($_SESSION['remisiones_cartaporte'])) {
        $_SESSION['remisiones_cartaporte'] = [];
    }

    if (!in_array($id, $_SESSION['remisiones_cartaporte'])) {
        $_SESSION['remisiones_cartaporte'][] = $id;
    }

    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'ID inválido']);
}
