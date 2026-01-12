<?php //require('functions.php');
/* ================== */
/* menu de navegación */
/* ================== */

/*
 ** reporte de errores
 */
//error_reporting(0);

/*
 ** zona horaria
 */
date_default_timezone_set("America/Mexico_City");

/*
 ** url de referencia
 */
$base = $_SERVER["HTTP_HOST"];
$base = $base . $_SERVER["PHP_SELF"];
//$base = 'http://'.trim($base, 'index.php');
$base = "http://" . $base . "index.php";
$uri = "http://" . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"];
$ruta = "http://" . $_SERVER["HTTP_HOST"];
$ruta .= "/";

/*
 ** navegar
 */
$archivo = "";
$navegar = "contenido/inicio.php";

/* primer nível */
if (isset($_GET["nav"])) {
    $nav = $_GET["nav"];
    $archivo = "contenido/" . $nav . ".php";

    if (file_exists($archivo)) {
        $navegar = $archivo;
    } else {
        $navegar = "404.php";
    }

    /* segundo nível */
    if (isset($_GET["cat"])) {
        $cat = $_GET["cat"];
        $nav = $_GET["nav"];
        //		echo 'variables='.$nav.' - '.$cat;
        $archivo = "contenido/" . $nav . ".php";
        if (file_exists($archivo)) {
            $navegar = $archivo;
        } else {
            $navegar = "404.php";
        }

        /* tercer nível */
        if (isset($_GET["subcat"])) {
            $subcat = $_GET["subcat"];
            $cat = $_GET["cat"];
            $nav = $_GET["nav"];
            //		echo 'variables='.$nav.' / '.$cat.' / '.$subcat;
            $archivo = "contenido/" . $nav . ".php";
            if (file_exists($archivo)) {
                $navegar = $archivo;
            } else {
                $navegar = "404.php";
            }
        }

        /* cuarto nível */
        if (isset($_GET["pagina"])) {
            $subcat = $_GET["subcat"];
            $cat = $_GET["cat"];
            $pagina = $_GET["pagina"];
            $nav = $_GET["nav"];
            //		echo 'variables='.$nav.' / '.$cat.' / '.$subcat.'/'.$pagina;
            $archivo = "contenido/" . $nav . ".php";
            if (file_exists($archivo)) {
                $navegar = $archivo;
            } else {
                $navegar = "404.php";
            }
        }

        /* Quinto nível */
        if (isset($_GET["subpagina"])) {
            $subcat = $_GET["subcat"];
            $cat = $_GET["cat"];
            $pagina = $_GET["pagina"];
            $pagina = $_GET["subpagina"];
            $nav = $_GET["nav"];
            //		echo 'variables='.$nav.' / '.$cat.' / '.$subcat.'/'.$pagina.'/'.$subpagina;
            $archivo = "contenido/" . $nav . ".php";
            if (file_exists($archivo)) {
                $navegar = $archivo;
            } else {
                $navegar = "404.php";
            }
        }
    }
}
?>
