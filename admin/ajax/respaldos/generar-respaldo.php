<?php
session_start();
require_once '../../lib/config.php';

date_default_timezone_set('America/Mexico_City');

$carpeta = __DIR__ . '/../../../respaldosbd';
if (!file_exists($carpeta)) {
    mkdir($carpeta, 0777, true);
}

// Construir nombre del archivo
$fechaHora = date('Ymd_His');
$archivoNombre = "respaldo_{$fechaHora}.sql";
$rutaCompleta = $carpeta . '/' . $archivoNombre;

// Construir el comando mysqldump usando las variables de config.php
$comando = "mysqldump -h {$dbHost} -u {$dbUser} " . ($dbPass ? "-p{$dbPass} " : "") . "{$dbName} > \"{$rutaCompleta}\"";

// Ejecutar respaldo
exec($comando, $output, $resultado);

// Validar si se generó correctamente
if ($resultado === 0 && file_exists($rutaCompleta)) {
    // Registrar en base de datos
    require_once '../../lib/clsConsultas.php';
    $clsConsulta = new Consultas();

    $id_usuario = $_SESSION['id_user'] ?? 0;
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    $navegador = $_SERVER['HTTP_USER_AGENT'] ?? '';

    $sql = "INSERT INTO respaldos_bd (nombre_archivo, fecha_hora, id_usuario, ip, navegador)
            VALUES ('$archivoNombre', NOW(), $id_usuario, '$ip', '$navegador')";
    $clsConsulta->aplicaquery($sql);

    echo json_encode(['status' => 'ok']);
} else {
    echo json_encode(['status' => 'error']);
}
