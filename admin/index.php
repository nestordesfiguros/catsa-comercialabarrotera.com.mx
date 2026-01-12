<?php
session_start();
//include 'lib/clsSeguridad.php';
//error_reporting(0);

require_once 'vendor/autoload.php';
date_default_timezone_set('Etc/GMT+6');

include 'lib/clsConsultas.php';
$clsConsulta = new Consultas();

$time = date("H:i:s");

if (isset($_SESSION['id_user'])) {
    require('php/menu.php');

    include 'lib/clsFechas.php';
    $clsFecha = new Fechas();

    $fecha_hoy  = $clsFecha->fecha_Hoy();
    $fecha_bd   = $clsFecha->dame_fecha_bd();
    $fechahora  = $clsFecha->fechaHora();

    $hora       = date("H:i:s");
    $anio_hoy   = substr($fecha_bd, 0, 4);
    $mes_hoy    = substr($fecha_bd, 5, 2);
    $dia_hoy    = substr($fecha_bd, 8, 2);

    $logo       = 'no_img.png?update=rand()';
    $favicon    = 'no_favicon.png?update=rand()';
    $titulo     = 'Sin titulo';
    $clickAviso = ' onclick="avisoRol();"'; // funcion de click para los avisos de los permisos

    // Mostrar modal si no hay razón social y hay múltiples empresas disponibles
    $mostrar_modal = (!isset($_SESSION['razon_social']) || empty($_SESSION['razon_social']))
        && (count($_SESSION['empresas_disponibles'] ?? []) > 1);
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

        <!-- Font Awesome -->
        <link rel="stylesheet" href="assets/fontawesome/css/all.min.css">

        <!-- Bootstrap -->
        <link href="assets/bootstrap-5-02/css/bootstrap.min.css" rel="stylesheet">

        <!-- Jquey -->
        <script src="assets/js/jquery-3.6.3.min.js"></script>
        <!-- <script src=" https://code.jquery.com/jquery-3.6.3.min.js" integrity="sha256-pvPw+upLPUjgMXY0G+8O0xUf+/Im1MZjXxxgOcBQBXU=" crossorigin="anonymous"></script> -->

        <!-- Validate jq -->
        <script src="assets/js/validate/jquery.validate.min.js"></script>
        <script src="assets/js/validate/additional-methods.min.js"></script>
        <!-- Incluir el archivo de traducción al español -->
        <script src="assets/js/validate/messages_es.min.js"></script>

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

        <!-- Tags Input -->
        <link rel="stylesheet" href="dist/tagsinput/bootstrap-tagsinput.css">
        <!--link rel="stylesheet" href="dist/tagsinput/app.css"-->


        <!-- MDB -->
        <link href="dist/mdb5/css/mdb.min.css" rel="stylesheet">

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

            #search {
                border-color: #032759ff !important;
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
        <div id="spinner" style="display: none;">
            <div class="spinner-border text-warning" style="width: 10rem; height: 10rem;" role="status">
                <span class="visually-hidden">Cargando...</span>
            </div>
            <!-- <img src="img/loaders/volta-logo.png" alt="Volta"> -->
        </div>

        <div class="wrapper">
            <!-- Navbar -->
            <?php include "php/navbar.php"; ?>
            <!-- /.navbar -->

            <div class="content-wrapper">
                <?php
                require $navegar;
                include 'php/footer.php';
                ?>
            </div>
            <!-- /.content-wrapper -->
        </div>
        <!-- ./wrapper -->

        <!-- Modal Empresas -->
        <div class="modal fade" id="modalEmpresas" tabindex="-1" data-bs-backdrop="static" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog  modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Selecciona la empresa a trabajar</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3 p-2 bg-light border rounded small">
                            <div> <strong>Empresa actual:</strong></div>
                            <div><?php if (isset($_SESSION['razon_social'])) {
                                        echo htmlspecialchars($_SESSION['razon_social']);
                                    } else {
                                        echo 'No hay empresa seleccionada';
                                    } ?></div>
                        </div>

                        <input type="text" class="form-control mb-2" id="filtroEmpresas" placeholder="Buscar empresa">

                        <select class="form-select" id="empresaSelect" size="8">
                            <?php
                            echo '<option value="" selected disabled>Selecciona una empresa</option>';
                            $con = "SELECT * FROM cat_empresas";
                            $rs = $clsConsulta->consultaGeneral($con);
                            if ($clsConsulta->numrows > 0) {
                                foreach ($rs as $v => $val) {
                                    if ($val['id'] != ($_SESSION['id_empresa'] ?? 0)) {
                                        echo '<option value="' . $val['id'] . '">' . htmlspecialchars($val['razon_social']) . '</option>';
                                    }
                                }
                            }
                            ?>
                        </select>
                    </div>

                    <div class="modal-footer">
                        <?php if (isset($_SESSION['id_empresa'])): ?>
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                        <?php endif; ?>
                        <button type="button" class="btn btn-primary" onclick="fnCambiarEmpresa()">Seleccionar empresa</button>
                    </div>
                </div>
            </div>
        </div>


        <!-- jQuery -->
        <!-- <script src="assets/js/jquery-3.6.3.min.js"></script> -->

        <!-- Bootstrap -->
        <script src="assets/bootstrap-5-02/js/bootstrap.bundle.min.js"></script>

        <!-- DataTables core -->
        <script src="assets/datatables/js/jquery.dataTables.min.js"></script>
        <!-- Integración Bootstrap -->
        <script src="assets/datatables/js/dataTables.bootstrap5.min.js"></script>

        <!-- Extensiones que SÍ tengas físicamente -->
        <script src="assets/datatables/js/dataTables.responsive.min.js"></script>
        <!-- (Quita responsive.bootstrap5.min.js si no existe para evitar 404) -->
        <!-- <script src="assets/datatables/js/responsive.bootstrap5.min.js"></script> -->

        <script src="assets/datatables/js/dataTables.buttons.min.js"></script>
        <!-- (Quita buttons.bootstrap5.min.js si no existe) -->
        <!-- <script src="assets/datatables/js/buttons.bootstrap5.min.js"></script> -->
        <script src="assets/datatables/js/jszip.min.js"></script>
        <script src="assets/datatables/js/buttons.html5.min.js"></script>
        <script src="assets/datatables/js/buttons.print.min.js"></script>

        <!-- Alertify (si lo usas) -->
        <script src="assets/alertify/js/alertify.min.js"></script>

        <!-- jQuery Validate (después de jQuery y antes de cualquier código que lo use) -->
        <script src="assets/js/validate/jquery.validate.min.js"></script>
        <script src="assets/js/validate/additional-methods.min.js"></script>
        <script src="assets/js/validate/messages_es.min.js"></script>

        <!-- MDB solo si lo necesitas para otros componentes (no su datatable) -->
        <!-- <script src="dist/mdb5/js/mdb.min.js"></script> -->


        <!-- MDB -->
        <!-- <script type="text/javascript" src="dist/mdb5/js/mdb.min.js"></script> -->

        <script>
            // Inicializar tooltips (una sola vez)
            document.addEventListener('DOMContentLoaded', function() {
                const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
                tooltipTriggerList.forEach(function(el) {
                    new bootstrap.Tooltip(el);
                });
            });

            // Búsqueda simple en el select del modal (si existe)
            document.addEventListener('DOMContentLoaded', function() {
                const filtro = document.getElementById('filtroEmpresas');
                const select = document.getElementById('empresaSelect');
                if (filtro && select) {
                    filtro.addEventListener('input', function() {
                        const term = this.value.toLowerCase();
                        const opts = select.querySelectorAll('option');
                        opts.forEach(o => {
                            if (!o.value) return; // deja el placeholder
                            o.style.display = o.textContent.toLowerCase().includes(term) ? '' : 'none';
                        });
                    });
                }
            });

            // Fallback para abrir el modal si algún data-attribute no dispara
            document.addEventListener('click', function(e) {
                const btn = e.target.closest('[data-bs-target="#modalEmpresas"]');
                if (!btn) return;
                const modalEl = document.getElementById('modalEmpresas');
                if (!modalEl) return;
                const modal = bootstrap.Modal.getOrCreateInstance(modalEl, {
                    backdrop: 'static',
                    keyboard: false
                });
                modal.show();
            });

            function fnCambiarEmpresa() {
                const idEmpresa = $('#empresaSelect').val();
                if (!idEmpresa) {
                    alertify.error('Selecciona una empresa');
                    return;
                }

                $.ajax({
                    url: 'ajax/usuarios/cambiar-empresa.php',
                    type: 'POST',
                    data: {
                        id_empresa: idEmpresa
                    },
                    success: function(res) {
                        try {
                            const json = typeof res === 'string' ? JSON.parse(res) : res;
                            if (json.success) {
                                window.location.reload();
                            } else {
                                alertify.error(json.message || "No se pudo cambiar la empresa.");
                            }
                        } catch (e) {
                            alertify.error("Error procesando la respuesta del servidor");
                            console.error(res);
                        }
                    }
                });
            }

            // Mostrar modal al cargar si aplica
            document.addEventListener('DOMContentLoaded', function() {
                <?php if ($mostrar_modal): ?>
                    const modalEl = document.getElementById('modalEmpresas');
                    if (modalEl) {
                        const modal = new bootstrap.Modal(modalEl, {
                            backdrop: 'static',
                            keyboard: false
                        });
                        modal.show();
                    }
                <?php endif; ?>
            });

            // Spinner
            document.body.classList.add('loading');
            window.onload = function() {
                document.getElementById('spinner').style.display = 'none';
                document.body.classList.remove('loading');
            };
        </script>
    </body>

    </html>
<?php
} else {
    session_unset();
    setcookie(session_name(), '', time() - 42000, '/');
    session_write_close();
    include 'login.php';
    exit;
}
?>