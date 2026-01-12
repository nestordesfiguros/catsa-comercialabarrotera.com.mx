<?php 
$base = $_SERVER["HTTP_HOST"];
$base = $base . $_SERVER["PHP_SELF"];
$base = 'http://'.trim($base, 'index.php');

//$base = "http://" . $base . "index.php";
/*
$uri = "http://" . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"];
$ruta = "http://" . $_SERVER["HTTP_HOST"];
$ruta .= "/";
$archivo = "";
*/
$navegar = "index.php";
?>