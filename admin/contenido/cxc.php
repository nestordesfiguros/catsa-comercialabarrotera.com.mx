<!-- Content Header (Page header) -->
<div class="ms-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="inicio">Inicio</a></li>
            <li class="breadcrumb-item active" aria-current="page">Cuentas por Cobrar </li>
        </ol>
    </nav>
</div>
<!-- /.content-header -->
<div class="container-fluid px-4">
    <div class="card shadow-sm">

        <div class="card-header">
            <div class="row col-12">
                <div class="col-12 col-md-10">
                    <a type="button" class="btn btn-secondary btn-fixed mt-2 mt-md-0" href="clientes"><i class="fa fa-users"></i> Clientes </a> &nbsp;
                    <a type="button" class="btn btn-secondary btn-fixed mt-2 mt-md-0" href="pedidos"><i class="fas fa-file-invoice"></i> Pedidos </a> &nbsp;
                    <a type="button" class="btn btn-secondary btn-fixed mt-2 mt-md-0" href="facturas"><i class="fas fa-file-invoice"></i> Facturas </a> &nbsp;
                    <a type="button" class="btn btn-secondary btn-fixed mt-2 mt-md-0" href="remisiones"><i class="fas fa-file-invoice"></i> Remisiones </a> &nbsp;
                    <a type="button" class="btn btn-info btn-fixed mt-2 mt-md-0" href="cxc"><i class="fas fa-hand-holding-usd"></i> CxC </a>
                    <a type="button" class="btn btn-secondary btn-fixed mt-2 mt-md-0" href="devoluciones"><i class="fas fa-undo"></i> Devoluciones </a>
                </div>
                <div class="col-12 col-md-2">
                    <div class="form-outline  mt-2 mt-md-0" data-mdb-input-init>
                        <input type="text" id="search" class="form-control" />
                        <label class="form-label" for="form12">Buscar</label>
                    </div>
                </div>
            </div>
        </div>

    </div>
    <div class="card-body">
        <form id="formFiltroCXC" class="row g-3 mb-4">
            <div class="input-group mb-3">
                <input type="text" id="nombre_cliente" class="form-control" placeholder="Selecciona un cliente" readonly required>
                <input type="hidden" id="id_cliente" name="id_cliente" required>
                <button type="button" class="btn btn-outline-secondary" id="btnBuscarCliente">
                    <i class="fas fa-search"></i>
                </button>
            </div>

            <div class="col-md-2">
                <label for="filtro_fecha_ini" class="form-label">Desde</label>
                <input type="date" class="form-control" id="filtro_fecha_ini" name="filtro_fecha_ini">
            </div>
            <div class="col-md-2">
                <label for="filtro_fecha_fin" class="form-label">Hasta</label>
                <input type="date" class="form-control" id="filtro_fecha_fin" name="filtro_fecha_fin">
            </div>
            <div class="col-md-2">
                <label for="filtro_estatus" class="form-label">Estatus</label>
                <select id="filtro_estatus" name="filtro_estatus" class="form-select">
                    <option value="">Todos</option>
                    <option value="Pendiente">Pendiente</option>
                    <option value="Pagada">Pagada</option>
                    <option value="Vencida">Vencida</option>
                    <option value="Cancelada">Cancelada</option>
                </select>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100" id="btnBuscarCXC">
                    <i class="fas fa-search"></i> Buscar
                </button>
            </div>
        </form>
        <div class="table-responsive">
            <table id="tablaCXC" class="table table-striped table-bordered nowrap" style="width:100%">
                <thead>
                    <tr>
                        <th>Folio</th>
                        <th>Cliente</th>
                        <th>Fecha Emisión</th>
                        <th>Fecha Vencimiento</th>
                        <th>Monto</th>
                        <th>Saldo</th>
                        <th>Estatus</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody><!-- DataTable --></tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="modalClientes" tabindex="-1" aria-labelledby="modalClientesLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Buscar Cliente</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table id="tablaClientes" class="table table-bordered table-hover" style="width:100%">
                        <tr>
                            <th>Razón Social</th>
                            <th>Nombre Comercial</th>
                            <th>Seleccionar</th>
                        </tr>
                        <tbody><!-- AJAX --></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Abonar -->
<div class="modal fade" id="modalAbonoCxc" tabindex="-1" aria-labelledby="modalAbonoCxcLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form id="formAbonoCxc" autocomplete="off">
            <div class="modal-content">
                <div class="modal-header bg-primary">
                    <h5 class="modal-title text-white" id="modalAbonoCxcLabel">
                        <i class="fas fa-dollar-sign"></i> Registrar abono a <span id="abonoCxcFolio"></span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar" onclick="cerrarModalAbono();"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id_cxc" id="abonoCxc_id_cxc">
                    <div class="mb-2">
                        <label class="form-label">Cliente</label>
                        <input type="text" id="abonoCxc_cliente" class="form-control" readonly>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Concepto</label>
                        <input type="text" name="concepto" class="form-control" placeholder="Ej. Pago parcial" maxlength="100" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Monto a abonar</label>
                        <input type="number" name="abono" id="abonoCxc_monto" class="form-control" min=".01" step="0.01" required>
                        <small class="text-muted" id="abonoCxc_saldo"></small>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Fecha</label>
                        <input type="date" name="fecha" class="form-control" value="<?= date('Y-m-d') ?>">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" onclick="cerrarModalAbono();">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Registrar abono</button>
                </div>
            </div>
        </form>
    </div>
</div>



<script>
    function cerrarModalAbono() {
        $('#modalAbonoCxc').modal('hide');
    }
    $(document).ready(function() {

        // Al dar clic en el botón de abonar
        $(document).on('click', '.btn-abonar-cxc', function() {
            const id_cxc = $(this).data('id');
            const saldo = $(this).data('saldo');
            const cliente = $(this).data('cliente');
            const folio = $(this).data('folio');
            $('#abonoCxc_id_cxc').val(id_cxc);
            $('#abonoCxc_cliente').val(cliente);
            $('#abonoCxcFolio').text(folio);
            $('#abonoCxc_monto').val('');
            $('#abonoCxc_saldo').text('Saldo actual: $' + parseFloat(saldo).toLocaleString('es-MX', {
                minimumFractionDigits: 2
            }));
            $('#modalAbonoCxc').modal('show');
        });

        // Registrar abono AJAX
        $('#formAbonoCxc').on('submit', function(e) {
            e.preventDefault();

            const monto = parseFloat($('#abonoCxc_monto').val());
            const saldoText = $('#abonoCxc_saldo').text().replace(/[^\d.,]/g, '').replace(',', '');
            const saldo = parseFloat(saldoText) || 0;

            if (monto > saldo) {
                alertify.error('El abono no puede ser mayor al saldo.');
                return false;
            }

            alertify.confirm('Confirmar abono',
                '¿Estás seguro de registrar este abono por $' + monto.toFixed(2) + '?',
                function() {
                    document.getElementById('spinner').style.display = 'block';
                    $.post('ajax/cxc/abonar.php', $('#formAbonoCxc').serialize(), function(resp) {
                        document.getElementById('spinner').style.display = 'none';
                        if (resp.trim() === 'success') {
                            alertify.success('Abono registrado correctamente');
                            $('#modalAbonoCxc').modal('hide');
                            if ($('#tablaCxc').length && typeof $('#tablaCxc').DataTable === "function") {
                                $('#tablaCxc').DataTable().ajax.reload(null, false);
                            } else {
                                location.reload();
                            }
                        } else {
                            alertify.error('Error: ' + resp);
                        }
                    });
                },
                function() {
                    alertify.message('Abono cancelado');
                }
            );
        });



        $('#btnBuscarCliente').on('click', function() {
            $('#modalClientes').modal('show');
            // Solo inicializar si no existe la DataTable
            if (!$.fn.DataTable.isDataTable('#tablaClientes')) {
                $('#tablaClientes').DataTable({
                    ajax: 'ajax/cxc/tabla-clientes.php',
                    columns: [{
                            data: 'razon_social',
                            title: 'Razón Social'
                        },
                        {
                            data: 'nombre_comercial',
                            title: 'Nombre Comercial'
                        },
                        {
                            data: null,
                            orderable: false,
                            render: function(data, type, row) {
                                return `<button type="button" class="btn btn-success btnSeleccionarCliente" 
                                    data-id="${row.id}" 
                                    data-nombre="${row.razon_social}" 
                                    data-comercial="${row.nombre_comercial}">
                                    Seleccionar
                                </button>`;
                            }
                        }
                    ],
                    pageLength: 10,
                    language: {
                        url: "assets/datatables/Spanish.json"
                    }
                });
            } else {
                $('#tablaClientes').DataTable().ajax.reload();
            }
        });

        // Al seleccionar cliente
        $(document).on('click', '.btnSeleccionarCliente', function() {
            let id = $(this).data('id');
            let nombre = $(this).data('nombre');
            let comercial = $(this).data('comercial');
            $('#id_cliente').val(id);
            $('#nombre_cliente').val(nombre + (comercial ? ' / ' + comercial : ''));
            $('#modalClientes').modal('hide');
        });

        var tablaCXC = $('#tablaCXC').DataTable({
            ajax: {
                url: 'ajax/cxc/tabla-cxc.php',
                type: 'POST',
                data: function(d) {
                    d.filtro_cliente = $('#id_cliente').val();
                    d.filtro_fecha_ini = $('#filtro_fecha_ini').val();
                    d.filtro_fecha_fin = $('#filtro_fecha_fin').val();
                    d.filtro_estatus = $('#filtro_estatus').val();
                }
            },
            ordering: true,
            order: [
                [2, 'desc']
            ], // Ordena por Fecha Emisión descendente
            pageLength: 10,
            dom: "<'row'<'col-sm-4'l><'col-sm-1'><'col-sm-7'p>>" +
                "<'row'<'col-sm-12'tr>>" +
                "<'row'<'col-sm-4'l><'col-sm-1'><'col-sm-7'p>>",
            language: {
                url: "https://cdn.datatables.net/plug-ins/1.10.15/i18n/Spanish.json",
                sSearch: '<i class="fa fa-search" aria-hidden="true"></i> Buscar'
            },
            responsive: true,
            columns: [{
                    data: 'folio'
                },
                {
                    data: 'cliente'
                },
                {
                    data: 'fecha_emision',
                    className: 'text-center'
                }, // centrado
                {
                    data: 'fecha_vencimiento'
                },
                {
                    data: 'monto',
                    className: 'text-end',
                    render: function(data) {
                        return '$' + parseFloat(data).toLocaleString('es-MX', {
                            minimumFractionDigits: 2
                        });
                    }
                },
                {
                    data: 'saldo',
                    className: 'text-end',
                    render: function(data) {
                        return '$' + parseFloat(data).toLocaleString('es-MX', {
                            minimumFractionDigits: 2
                        });
                    }
                },
                {
                    data: 'estatus',
                    className: 'text-center',
                },
                {
                    data: 'acciones',
                    orderable: false,
                    searchable: false
                }
            ]
        });

        $('#formFiltroCXC').on('submit', function(e) {
            e.preventDefault();
            tablaCXC.ajax.reload();
        });

        // Botón alta de nueva CxC
        $('#btnAltaCXC').on('click', function() {
            window.location.href = 'cxc-altas';
        });
    });
</script>