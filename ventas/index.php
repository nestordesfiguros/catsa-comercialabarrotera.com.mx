<?PHP
session_start();
//include 'lib/clsSeguridad.php';
// No mostrar los errores de PHP

//error_reporting(0);

//require_once 'vendor/autoload.php';
date_default_timezone_set('Etc/GMT+6');

$time = date("H:i:s");
if (isset($_SESSION['id_vendedor'])) {
    require('php/menu.php');
    include '../admin/lib/clsConsultas.php';
    $clsConsulta = new Consultas();
    //   include 'lib/clsCadenas.php';
    include '../admin/lib/clsFechas.php';
    $clsFecha = new Fechas();

    //require('php/_title.php');

    $fecha_hoy = $clsFecha->fecha_Hoy();
    $fecha_bd = $clsFecha->dame_fecha_bd();
    $fechahora = $clsFecha->fechaHora();

    $hora = date("H:i:s");
    $anio_hoy = substr($fecha_bd, 0, 4);
    $mes_hoy = substr($fecha_bd, 5, 2);
    $dia_hoy = substr($fecha_bd, 8, 2);

    $logo = 'no_img.png?update=rand()';
    $favicon = 'no_favicon.png?update=rand()';
    $titulo = 'Sin titulo';
    $clickAviso = ' onclick="avisoRol();"'; // funcion de click para los avisos de los permisos

    // Obtener el cliente actual de la sesión

    if (isset($_SESSION['pedidoActivo'])) {
        $idCliente = $_SESSION['pedidoActivo'];
    } else {
        if (isset($cat)) {
            $idCliente = $cat; // Cliente por defecto
            $_SESSION['pedidoActivo'] = $idCliente; // Guarda el ID del cliente en la sesión
        }
    }
?>

    <!DOCTYPE html>
    <html>

    <head>
        <base href="<?php echo $base; ?>">
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Vendedores | Distribuidora del Bajío</title>

        <!-- Favicon -->

        <!-- <link rel="icon" type="image/png" href="img/favicon.png?update=<?php echo rand(); ?>"> -->

        <!-- Favicon -->

        <link rel="icon" type="image/png" href="img/favicon-96x96.png" sizes="96x96" />

        <link rel="icon" type="image/svg+xml" href="img/favicon.svg" />

        <link rel="shortcut icon" href="img/favicon.ico" />

        <link rel="apple-touch-icon" sizes="180x180" href="img/apple-touch-icon.png" />

        <link rel="manifest" href="img/site.webmanifest" />

        <!-- Google Font: Source Sans Pro -->

        <!--link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback"-->

        <!-- Google Fonts Roboto -->

        <!-- <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700;900&display=swap"> -->

        <!-- Font Awesome -->

        <!-- <link href="../admin/dist/fontawesome6/css/fontawesome.css" rel="stylesheet">

        <link href="../admin/dist/fontawesome6/css/brands.css" rel="stylesheet">

        <link href="../admin/dist/fontawesome6/css/solid.css" rel="stylesheet"> -->

        <!-- Ionicons -->
        <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">

        <!-- Bootstrap -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">

        <!-- Font Awesome -->
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet" />

        <!-- Google Fonts -->
        <link href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700&display=swap" rel="stylesheet" />

        <!-- Jquey -->
        <script src="https://code.jquery.com/jquery-3.6.3.min.js" integrity="sha256-pvPw+upLPUjgMXY0G+8O0xUf+/Im1MZjXxxgOcBQBXU=" crossorigin="anonymous"></script>


        <!-- Validate jq -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.5/jquery.validate.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.5/additional-methods.min.js"></script>

        <!-- Incluir el archivo de traducción al español -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.5/localization/messages_es.min.js"></script>



        <!-- DataTables CSS -->
        <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">


        <!-- CSS  AlertifyJS -->

        <!-- CSS -->
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/css/alertify.min.css" />
        <!-- Default theme -->
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/css/themes/default.min.css" />
        <!-- Semantic UI theme -->
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/css/themes/semantic.min.css" />
        <!-- Bootstrap theme -->
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/css/themes/bootstrap.min.css" />
        <!-- JavaScript Alertify -->
        <script src="https://cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/alertify.min.js"></script>


        <!-- Agrega estas librerías adicionales en el head o antes del cierre del body -->
        <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.0/themes/base/jquery-ui.css">
        <script src="https://code.jquery.com/ui/1.13.0/jquery-ui.min.js"></script>
        <!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-ui-touch-punch/0.2.3/jquery.ui.touch-punch.min.js"></script> -->

        <!-- Custom css -->
        <!-- <link rel="stylesheet" href="css/custom.css"> -->

        <style>
            /* FULL SCREEN */

            #spinner {
                position: fixed;
                top: 0;
                left: 0;
                z-index: 9999;
                height: 100%;
                width: 100%;
                /* background: rgba(255, 255, 255, 1);  */
            }



            /* CENTRAR SPINNER*/

            #spinner img {
                width: 200px;
                position: absolute;
                left: 50%;
                top: 30%;
                transform: translate(-50%);
            }
        </style>

        <script>
            window.onload = function() {
                $("#spinner").fadeOut();
            }
        </script>

    </head>



    <body class="hold-transition sidebar-mini layout-fixed">

        <!-- Preloader -->

        <div id="spinner">
            <img src="img/loaders/temple-loader.gif" alt="spinner">
        </div>

        <!-- /. End preloader -->
        <div class="wrapper">

            <!-- Navbar -->
            <?php include "php/navbar.php"; ?>
            <!-- /.navbar -->

            <?php     // include 'php/rutanav.php'    
            ?>
            <!-- Fin ruta navegacion -->
            <!-- Content Wrapper. Contains page content -->

            <div class="content-wrapper">
                <?php
                require $navegar;
                include 'php/footer.php';
                ?>
            </div>

            <!-- /.content-wrapper -->



        </div>

        <!-- ./wrapper -->





        <!-- Modal spinner-->

        <!-- Modal Spinner usando Bootstrap 5 -->
        <div class="modal fade" id="modalSpiner" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-fullscreen d-flex justify-content-center align-items-center">
                <div class="modal-content bg-transparent border-0 shadow-none">
                    <div class="d-flex justify-content-center align-items-center w-100" id="spinner">
                        <div class="spinner-border text-primary" role="status" style="width: 4rem; height: 4rem;">
                            <span class="visually-hidden">Cargando...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>




        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-kenU1KFdBIe4zVF0s0G1M5b4hcpxyD9F7jL+jjXkk+Q2h455rYXK/7HAuoJl+0I4" crossorigin="anonymous"></script>



        <!-- Datatables -->

        <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>

        <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>



        <!-- <script>

            $('input').on('keyup', function() {

                // Verificar si el input es de tipo number, si es así, no hacer nada

                if ($(this).attr('type') === 'number') {

                    return; // Evita la modificación de campos tipo number

                }



                // Lista de IDs o nombres de campos que deben conservar mayúsculas/minúsculas

                const camposSensibles = [

                    'correo', 'mapa', 'email', 'pwd', 'pwd2', 'mail', 'usr',

                    '#correo', '#mapa', '#email', '#pwd', '#pwd2', '#mail', '#usr'

                ];



                // Verificar si el input actual está en la lista de excepciones

                const esCampoSensible = camposSensibles.some(campo =>

                    $(this).is(campo) ||

                    $(this).attr('id') === campo.replace('#', '') ||

                    $(this).attr('name') === campo

                );



                // Solo convertir a mayúsculas si NO es un campo sensible

                if (!esCampoSensible) {

                    $(this).val($(this).val().toUpperCase());

                }

            });

        </script> -->







    </body>



    </html>

<?PHP

} else {

    session_unset();

    session_destroy();

    session_write_close();

    //     setcookie(session_name(),'',0,'/');

    //    session_regenerate_id(true);



    include 'login.php';
}



?>