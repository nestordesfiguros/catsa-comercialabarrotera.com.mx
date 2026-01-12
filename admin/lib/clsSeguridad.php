<?PHP 
session_start();
if(!isset($_SESSION['usuario']))header("./lib/logout.php"); 
$tiempo = (isset($_SESSION['time'])) ? $_SESSION['time'] : strtotime(date("Y-m-d H:i:s"));
$actual =  strtotime(date("Y-m-d H:i:s"));
$directorio_padre = dirname(__DIR__);
(($actual-$tiempo) >= 1200) ? header("Location: ./lib/logout.php") : $_SESSION['time'] =$actual;

/* 
    3600 segundos = 60 minutos
    1200 segundos =20 minutos
    900 segundos = 15 munutos
*/
?> 