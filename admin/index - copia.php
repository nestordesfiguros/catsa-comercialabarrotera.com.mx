<?PHP
session_start();
//include 'lib/clsSeguridad.php';
// No mostrar los errores de PHP
//error_reporting(0);

require_once 'vendor/autoload.php';
date_default_timezone_set('Etc/GMT+6');
//session_start();
$time = date("H:i:s");
if (isset($_SESSION['id_user'])) {
    require('php/menu.php');
    include 'lib/clsConsultas.php';
    $clsConsulta = new Consultas();
    //   include 'lib/clsCadenas.php';
    include 'lib/clsFechas.php';
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


?>
    <!DOCTYPE html>
    <html>

    <head>
        <base href="<?php echo $base; ?>">
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Administrador | Abarrotes</title>
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
        <link rel="stylesheet" href="assets/fontawesome/css/all.min.css">
        <!-- <link href="dist/fontawesome6/css/fontawesome.css" rel="stylesheet">
        <link href="dist/fontawesome6/css/brands.css" rel="stylesheet">
        <link href="dist/fontawesome6/css/solid.css" rel="stylesheet"> -->
        <!-- Ionicons -->
        <!-- <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css"> -->

        <!-- Bootstrap -->

        <link href="assets/bootstrap-5-02/css/bootstrap.min.css" rel="stylesheet">
        <!-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous"> -->


        <!-- Font Awesome -->
        <!-- <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet" /> -->
        <!-- Google Fonts -->
        <!-- <link href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700&display=swap" rel="stylesheet" /> -->

        <!-- Ajax Google jQ -->
        <!--script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.3/jquery.min.js"></script-->

        <!-- Jquey -->
        <script src="assets/js/jquery-3.6.3.min.js"></script>
        <!-- <script src=" https://code.jquery.com/jquery-3.6.3.min.js" integrity="sha256-pvPw+upLPUjgMXY0G+8O0xUf+/Im1MZjXxxgOcBQBXU=" crossorigin="anonymous"></script> -->

        <!-- Validate jq -->
        <script src="assets/js/validate/jquery.validate.min.js"></script>
        <script src="assets/js/validate/additional-methods.min.js"></script>
        <!-- Incluir el archivo de traducción al español -->
        <script src="assets/js/validate//messages_es.min.js"></script>

        <!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.5/jquery.validate.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.5/additional-methods.min.js"></script> -->
        <!-- Incluir el archivo de traducción al español -->
        <!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.5/localization/messages_es.min.js"></script> -->


        <!-- Datatables -->
        <link rel="stylesheet" href="assets/datatables/css/jquery.dataTables.min.css">
        <link rel="stylesheet" href="assets/datatables/css/fixedHeader.dataTables.min.css">

        <!-- CSS  AlertifyJS -->
        <link rel="stylesheet" href="assets/alertify/css/alertify.min.css" />
        <!-- Default theme -->
        <link rel="stylesheet" href="assets/alertify/css/default.min.css" />
        <!-- Semantic UI theme -->
        <link rel="stylesheet" href="assets/alertify/css/semantic.min.css" />
        <!-- Bootstrap theme -->
        <link rel="stylesheet" href="assets/alertify/css/bootstrap.min.css" />
        <!-- JavaScript Alertify -->
        <script src="assets/alertify/js/alertify.min.js"></script>


        <!-- CSS -->
        <!-- <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/css/alertify.min.css" /> -->
        <!-- Default theme -->
        <!-- <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/css/themes/default.min.css" /> -->
        <!-- Semantic UI theme -->
        <!-- <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/css/themes/semantic.min.css" /> -->
        <!-- Bootstrap theme -->
        <!-- <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/css/themes/bootstrap.min.css" /> -->
        <!-- JavaScript Alertify -->
        <!-- <script src="https://cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/alertify.min.js"></script> -->



        <!-- Tags Input -->
        <link rel="stylesheet" href="dist/tagsinput/bootstrap-tagsinput.css">
        <!--link rel="stylesheet" href="dist/tagsinput/app.css"-->


        <!-- MDB -->
        <!--link href="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/6.2.0/mdb.min.css" rel="stylesheet"-->
        <link href="dist/mdb5/css/mdb.min.css" rel="stylesheet">
        <!-- <link href="dist/mdb5/css/modules/datatable.min.css" rel="stylesheet"> -->

        <!-- Custom css -->
        <link rel="stylesheet" href="css/custom.css">
        <style>
            /* FULL SCREEN */

            #modalSpiner .modal-content {
                background-color: rgba(0, 0, 0, 0.3);
                /* Fondo opaco */
            }

            #preloader {
                position: fixed;
                z-index: 9999;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background-color: white;
            }

            .bootstrap-tagsinput .tag {
                margin-right: 2px;
                color: #666666 !important;
                background-color: #E3EBF7 !important;
                padding: 0.2rem 0.5rem 0.2rem 0.5rem;
                /* up  start down end */
                border-radius: 5px;
                margin-top: 15px !important;
                margin-bottom: 15px !important;
                width: 100% !important;
            }

            .bootstrap-tagsinput {
                width: 100%;
            }

            table.dataTable {
                width: 100% !important;
            }



            /*
Toolt tips personalizados utilizando data-title
*/
            [data-title]:hover:after {
                opacity: 1;
                transition: all 0.1s ease 0.5s;
                visibility: visible;
            }

            [data-title]:after {
                content: attr(data-title);
                background-color: #E3EBF7;
                color: #000;
                font-size: 11px;
                font-family: Arial, Helvetica, sans-serif;
                position: absolute;
                padding: 3px 20px;
                bottom: -.6em;
                right: 100%;
                white-space: nowrap;
                box-shadow: 1px 1px 3px #222222;
                opacity: 0;
                border: 1px solid #111111;
                z-index: 99999;
                visibility: hidden;
                border-radius: 6px;
                border-color: #E3EBF7;

            }

            [data-title] {
                position: relative;
            }

            datalist {
                background-color: #E3EBF7 !important;
                color: #000 !important;
            }

            #datalistOptions {
                background-color: #E3EBF7 !important;
                color: #000 !important;
            }

            /*
            span{        
                position:relative;
                display:block;        
                box-shadow:1px 1px 3px gray;
                   
            }
            */
            .alertify-notifier .ajs-message.ajs-custom {
                background: transparent;
                box-shadow: none;
                padding: 0;
            }
        </style>
        <script>
            window.onload = function() {
                $('#preloader').fadeOut();
            }
        </script>





    </head>

    <body class="hold-transition sidebar-mini layout-fixed">
        <!-- Preloader -->
        <div id="preloader">
            <div class="d-flex justify-content-center align-items-center vh-100 bg-white">
                <div class="spinner-border text-primary" role="status" style="width: 4rem; height: 4rem;">
                    <span class="visually-hidden">Cargando...</span>
                </div>
            </div>
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
        <div class="modal fade" id="modalSpiner" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false" aria-hidden="true">
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




        <!-- Tags Input -->
        <script src="dist/tagsinput/bootstrap-tagsinput.min.js"></script>

        <!-- Data Tables-->
        <!--link rel="stylesheet" href="https://cdn.datatables.net/1.13.3/css/jquery.dataTables.min.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.2.0/css/bootstrap.min.css">
        <link rel="stylesheet" href="https://cdn.datatables.net/1.13.3/css/dataTables.bootstrap5.min.css"-->
        <link rel="stylesheet" href="assets/datatables/css/dataTables.bootstrap5.min.css">

        <!-- <script src="https://cdn.datatables.net/fixedheader/3.3.2/js/dataTables.fixedHeader.min.js"></script>
        <script src="https://cdn.datatables.net/1.13.3/js/dataTables.bootstrap5.min.js"></script> -->

        <!-- <script src="assets/datatables/js/dataTables.fixedHeader.min.js"></script>-->
        <script src="assets/datatables/js/dataTables.bootstrap5.min.js"></script>

        <!-- Extra datatable -->
        <script src="assets/datatables/js/jquery.dataTables.min.js"></script>
        <script src="assets/datatables/js/dataTables.buttons.min.js"></script>
        <script src="assets/datatables/js/jszip.min.js"></script>
        <!-- <script src="assets/datatables/js/pdfmake.min.js"></script> -->
        <script src="assets/datatables/js/vfs_fonts.js"></script>
        <script src="assets/datatables/js/buttons.html5.min.js"></script>
        <script src="assets/datatables/js/buttons.print.min.js"></script>
        <script src="assets/datatables/js/dataTables.responsive.min.js"></script>
        <!-- <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
        <script src="https://cdn.datatables.net/buttons/2.3.6/js/dataTables.buttons.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
        <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.html5.min.js"></script>
        <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.print.min.js"></script>
        <script src="https://cdn.datatables.net/responsive/2.4.1/js/dataTables.responsive.min.js"></script> -->

        <!-- jQuery Knob -->
        <script src="plugins/jquery-knob/jquery.knob.min.js"></script>
        <!-- Sparkline -->
        <script src="plugins/sparklines/sparkline.js"></script>

        <script src="assets/bootstrap-5-02/js/bootstrap.bundle.min.js"></script>

        <!-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-kenU1KFdBIe4zVF0s0G1M5b4hcpxyD9F7jL+jjXkk+Q2h455rYXK/7HAuoJl+0I4" crossorigin="anonymous"></script> -->

        <script>
            /** NOTIFICACIONES */
            /*
                        setInterval(() => {
                            fetch('ajax/notificaciones/check-nuevos-pedidos.php')
                                .then(response => response.json())
                                .then(data => {
                                    if (data.nuevo) {
                                        const htmlMensaje = `
                                <div style="background-color:#F1F1F1; color: #007bff; padding: 10px; border-radius: 8px; font-weight: bold;">
                                    Nuevo pedido no. ${data.noPedido}<br>
                                    Vendedor: ${data.vendedor}
                                </div>
                            `;
                                        alertify.notify(htmlMensaje, 'custom', 8);
                                        fetch(`ajax/notificaciones/marcar-notificado.php?id=${data.id}`);
                                    }
                                });
                        }, 5000);
                        */
        </script>

        <script>
            (function($) {
                // Delegado a todo el documento: funciona con vistas cargadas dinámicamente
                $(document).on('input', 'input, textarea', function() {
                    const el = this;

                    // Tipos que no se transforman
                    const tiposIgnorar = new Set([
                        'number', 'password', 'email', 'file', 'date', 'datetime-local', 'time',
                        'url', 'tel', 'color', 'range', 'hidden'
                    ]);
                    if (tiposIgnorar.has(el.type)) return;

                    // Excepciones marcadas en el DOM
                    if (el.dataset.keepcase === '1' || $(el).hasClass('case-normal')) return;

                    // Excepciones por id o name (si quieres mantener tu lista)
                    const excepciones = new Set(['correo', 'mapa', 'email', 'pwd', 'pwd2', 'mail', 'usr', 'pass', 'mailanterior']);
                    if (excepciones.has(el.id) || excepciones.has(el.name)) return;

                    // Transformar a MAYÚSCULAS y conservar la posición del cursor
                    const s = el.selectionStart,
                        e = el.selectionEnd;
                    el.value = el.value.toUpperCase();
                    if (s != null && e != null) el.setSelectionRange(s, e);
                });
            })(jQuery);
        </script>


        <script>
            $(document).on('init.dt', function(e, settings) {
                const $wrap = $(settings.nTableWrapper);
                // Evita que el plugin de inputs de MDB lo procese
                $wrap.find('.dataTables_filter input')
                    .attr('data-mdb-skip', '1');
            });
        </script>

        <script>
            function fntCambiarEmpresa() {
                var empresa = $("#empresa").val();

                $.ajax({
                    type: "POST",
                    url: "ajax/configuracion/cambia-empresa.php",
                    data: {
                        empresa: empresa,
                        nav: function() {
                            return <?php echo '"' . $nav . '"'; ?>
                        }
                    },
                    success: function(data) {
                        //    console.log(data);
                        if (data == 1) {
                            alert("Fallo el server");
                        } else {
                            var json = JSON.parse(data);
                            location = json.location;
                            //   location.reload(); 
                        }
                    }
                }); /* End ajax */
            }

            function avisoRol() {
                alertify.alert('No tienes acceso a esta Opción').setHeader('<em> Aviso..!</em> ');
            }

            $(document).ready(function() {




                /* Formato de moneda a input*/
                /*
                $(".monedas").keyup(
                    function() {
                        $(event.target).val(function(index, value) {
                            return value.replace(/\D/g, "")
                                .replace(/([0-9])([0-9]{2})$/, '$1.$2')
                                .replace(/\B(?=(\d{3})+(?!\d)\.?)/g, ",");
                        });
                    }
                );
                */
                var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'))
                var popoverList = popoverTriggerList.map(function(popoverTriggerEl) {
                    return new bootstrap.Popover(popoverTriggerEl)
                })

            });
        </script>


        <!-- MDB -->
        <script type="text/javascript" src="dist/mdb5/js/mdb.min.js"></script>
        <!--<script src="dist/mdb5/js/modules/datatable.min.js"></script> -->

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