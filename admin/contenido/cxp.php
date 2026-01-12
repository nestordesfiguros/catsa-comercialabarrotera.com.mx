<!-- contenido/cxp.php -->
<?php
$idEmpresa = isset($_SESSION['id_empresa']) ? (int)$_SESSION['id_empresa'] : 0;
?>

<div class="ms-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="inicio">Inicio</a></li>
            <li class="breadcrumb-item active" aria-current="page">Cuentas por Pagar</li>
        </ol>
    </nav>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <div class="row col-12">
                            <div class="col-12 col-md-8">
                                <a type="button" class="btn btn-secondary btn-fixed mt-2 mt-md-0" href="proveedores"><i class="fas fa-truck-moving"></i> Proveedores </a> &nbsp;
                                <a type="button" class="btn btn-secondary btn-fixed mt-2 mt-md-0" href="compras"><i class="fas fa-file-invoice"></i> Ordenes Compra </a> &nbsp;
                                <button class="btn btn-info btn-fixed mt-2 mt-md-0"> CxP </button>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="form-outline  mt-2 mt-md-0" data-mdb-input-init>
                                    <input type="text" id="search" class="form-control" />
                                    <label class="form-label" for="form12">Buscar</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <select id="filtroProveedor" class="form-select">
                                    <option value="">Todos los proveedores</option>
                                    <?php
                                    $proveedores = $clsConsulta->consultaGeneral("SELECT id, razon_social FROM cat_proveedores WHERE estatus=1 AND id_empresa={$idEmpresa} ORDER BY razon_social ASC");
                                    if ($clsConsulta->numrows > 0 && is_array($proveedores)) {
                                        foreach ($proveedores as $p) {
                                            echo '<option value="' . (int)$p['id'] . '">' . htmlspecialchars($p['razon_social']) . '</option>';
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select id="filtroEstatus" class="form-select">
                                    <option value="">Todos los estatus</option>
                                    <option value="pendiente" selected>Pendiente</option>
                                    <option value="parcial">Parcial</option>
                                    <option value="pagada">Pagada</option>
                                    <option value="vencida">Vencida</option>
                                </select>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table id="tablaCxP" class="table table-bordered table-hover align-middle w-100">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Proveedor</th>
                                        <th>Fecha</th>
                                        <th>Monto Total</th>
                                        <th>Monto Pagado</th>
                                        <th>Saldo</th>
                                        <th>Estatus</th>
                                        <th>Opciones</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
    $(document).ready(function() {
        var tablaCxP = $('#tablaCxP').DataTable({
            serverSide: true,
            processing: true,
            ajax: {
                url: 'ajax/cxp/tabla-cxp.php',
                type: 'POST',
                data: function(d) {
                    d.proveedor = $('#filtroProveedor').val();
                    d.estatus = $('#filtroEstatus').val();
                    d.search_global = $('#search').val();
                }
            },
            pageLength: 25,
            lengthMenu: [
                [10, 25, 50, 100],
                [10, 25, 50, 100]
            ],
            order: [
                [2, 'desc']
            ],
            columns: [{
                    data: 'id'
                },
                {
                    data: 'proveedor'
                },
                {
                    data: 'fecha'
                },
                {
                    data: 'monto_total',
                    render: $.fn.dataTable.render.number(',', '.', 2, '$')
                },
                {
                    data: 'monto_pagado',
                    render: $.fn.dataTable.render.number(',', '.', 2, '$')
                },
                {
                    data: 'saldo',
                    render: $.fn.dataTable.render.number(',', '.', 2, '$')
                },
                {
                    data: 'estatus',
                    render: function(data) {
                        let badge = 'secondary';
                        if (data == 'pendiente') badge = 'warning';
                        if (data == 'parcial') badge = 'info';
                        if (data == 'pagada') badge = 'success';
                        if (data == 'vencida') badge = 'danger';
                        return '<span class="badge bg-' + badge + '">' + data + '</span>';
                    }
                },
                {
                    data: null,
                    orderable: false,
                    render: function(data, type, row) {
                        return `<a href="cxp-detalle/${row.id}" class="btn btn-outline-primary btn-sm"><i class="fas fa-eye"></i></a>`;
                    }
                }
            ],
            language: {
                url: "assets/datatables/Spanish.json"
            }
        });

        $('#filtroProveedor, #filtroEstatus').on('change', function() {
            tablaCxP.ajax.reload();
        });

        let timeout = null;
        $('#search').on('input', function() {
            clearTimeout(timeout);
            timeout = setTimeout(function() {
                tablaCxP.ajax.reload();
            }, 400);
        });
    });
</script>