<?php
// Verificar autenticación
/*
if (!isset($_SESSION['id_puesto'])) {
    header('Location: ../index.php');
    exit;
}
*/
// Obtener estatus filter para el select
$estatus_filter = isset($_GET['estatus']) ? $clsConsulta->escape($_GET['estatus']) : '';
?>

<!-- Content Header (Page header) -->
<div class="ms-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="inicio">Inicio</a></li>
            <li class="breadcrumb-item active" aria-current="page">Cuentas por Cobrar</li>
        </ol>
    </nav>
</div>

<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <div class="row">
                            <div class="col-8">
                                <a type="button" class="btn btn-secondary btn-fixed mt-2 mt-md-0" href="clientes"><i class="fa fa-users"></i> Clientes</a>
                                <a type="button" class="btn btn-secondary btn-fixed mt-2 mt-md-0" href="pedidos"><i class="fas fa-file-invoice"></i> Pedidos</a>
                                <a type="button" class="btn btn-secondary btn-fixed mt-2 mt-md-0" href="facturas"><i class="fas fa-file-invoice"></i> Facturas</a>
                                <a type="button" class="btn btn-secondary btn-fixed mt-2 mt-md-0" href="remisiones"><i class="fas fa-file-invoice"></i> Remisiones</a>
                                <a type="button" class="btn btn-info btn-fixed mt-2 mt-md-0" href="cuentas-cobrar-pagos"><i class="fas fa-plus"></i> Registrar Pago</a>
                            </div>
                            <div class="col-4">
                                <div class="form-outline" data-mdb-input-init>
                                    <input type="text" id="search" class="form-control" />
                                    <label class="form-label" for="search">Buscar</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- /.card-header -->
                    <div class="card-body">
                        <?php if (isset($_GET['success'])): ?>
                            <script>
                                document.addEventListener('DOMContentLoaded', function() {
                                    alertify.success('<?php echo htmlspecialchars($_GET['success']); ?>');
                                });
                            </script>
                        <?php endif; ?>
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <select class="form-select" id="estatusFilter" onchange="reloadTable()">
                                    <option value="">Todos los estatus</option>
                                    <option value="PENDIEENTE" <?php echo $estatus_filter === 'PENDIEENTE' ? 'selected' : ''; ?>>Pendiente</option>
                                    <option value="PAGADA" <?php echo $estatus_filter === 'PAGADA' ? 'selected' : ''; ?>>Pagada</option>
                                </select>
                            </div>
                            <div class="col-md-8 text-end">
                                <button class="btn btn-primary" onclick="exportarExcel()">Exportar a Excel</button>
                                <button class="btn btn-secondary" onclick="exportarPDF()">Exportar a PDF</button>
                            </div>
                        </div>
                        <table id="facturasTable" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Folio</th>
                                    <th>Fecha</th>
                                    <th>Cliente</th>
                                    <th class="text-end">Total</th>
                                    <th class="text-end">Saldo</th>
                                    <th>Estatus</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
    $(document).ready(function() {
        var table = $('#facturasTable').DataTable({
            ajax: {
                url: 'ajax/cuentas-cobrar/tabla-clientes-cxc.php',
                type: 'POST',
                data: function(d) {
                    d.estatus = $('#estatusFilter').val();
                }
            },
            columns: [{
                    data: 'folio'
                },
                {
                    data: 'fecha'
                },
                {
                    data: 'razon_social'
                },
                {
                    data: 'total',
                    className: 'text-end'
                },
                {
                    data: 'saldo',
                    className: 'text-end'
                },
                {
                    data: 'estatus'
                },
                {
                    data: 'acciones'
                }
            ],
            language: {
                url: "assets/datatables/Spanish.json"
            },
            ordering: true,
            fixedHeader: true,
            responsive: true,
            pageLength: 10,
            dom: "<'row'<'col-sm-6'l><'col-sm-6'p>>" +
                "<'row'<'col-sm-12'tr>>" +
                "<'row'<'col-sm-6'l><'col-sm-6'p>>"
        });

        // Búsqueda dinámica
        $('#search').keyup(function() {
            table.search($(this).val()).draw();
        });
    });

    function reloadTable() {
        $('#facturasTable').DataTable().ajax.reload();
    }

    function exportarExcel() {
        var estatus = $('#estatusFilter').val();
        window.location.href = 'ajax/cuentas-cobrar/exportar-excel.php' + (estatus ? '?estatus=' + estatus : '');
    }

    function exportarPDF() {
        var estatus = $('#estatusFilter').val();
        window.location.href = 'ajax/cuentas-cobrar/exportar-pdf.php' + (estatus ? '?estatus=' + estatus : '');
    }
</script>