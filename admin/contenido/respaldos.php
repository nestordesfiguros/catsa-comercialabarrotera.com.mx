<div class="ms-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="inicio">Inicio</a></li>
            <li class="breadcrumb-item"><a href="utilerias">Utilerías</a></li>
            <li class="breadcrumb-item active" aria-current="page">Respaldos</li>
        </ol>
    </nav>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="row d-flex justify-content-center">
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-database me-2"></i> Respaldos de Base de Datos</h5>
                        <button class="btn btn-success" id="btnGenerar">
                            <i class="fas fa-download me-1"></i> Generar respaldo
                        </button>
                    </div>
                    <div class="card-body">
                        <div id="spinnerResp" class="text-center my-3 d-none">
                            <div class="spinner-border" role="status">
                                <span class="visually-hidden">Generando respaldo...</span>
                            </div>
                            <div>Generando respaldo, por favor espera...</div>
                        </div>
                        <table id="tablaRespaldos" class="table table-bordered table-hover w-100">
                            <thead class="table-dark text-center">
                                <tr>
                                    <th>Fecha</th>
                                    <th>Archivo</th>
                                    <th>Usuario</th>
                                    <th>IP</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
    $(document).ready(function() {
        var tabla = $('#tablaRespaldos').DataTable({
            ajax: 'ajax/respaldos/listar-respaldos.php',
            serverSide: true,
            processing: true,
            order: [
                [0, 'desc']
            ],
            columns: [{
                    data: 'fecha'
                },
                {
                    data: 'nombre_archivo'
                },
                {
                    data: 'usuario'
                },
                {
                    data: 'ip_origen'
                },
                {
                    data: 'ruta_guardado',
                    orderable: false,
                    render: function(data) {
                        return `<a href="${data}" class="btn btn-outline-primary btn-sm" target="_blank">
            <i class="fas fa-file-download"></i> Descargar
        </a>`;
                    }
                }

            ],
            language: {
                url: "assets/datatables/Spanish.json"
            }
        });

        $('#btnGenerar').on('click', function() {
            $('#spinnerResp').removeClass('d-none');
            $.post('ajax/respaldos/generar-respaldo.php', {}, function(resp) {
                $('#spinnerResp').addClass('d-none');
                if (resp.trim() == 'ok') {
                    alertify.success('Respaldo generado correctamente');
                    tabla.ajax.reload();
                } else {
                    alertify.error('Error al generar respaldo');
                }
            });
        });
    });
</script>