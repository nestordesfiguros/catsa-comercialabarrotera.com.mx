<?php
// *** NO incluir session_start ni requires aquí (se cargan desde index.php) ***
// Cargar combos de Vendedores y Clientes (sin asumir columnas extra)
$vendedores = [];
$clientes   = [];

$conV = "SELECT id, nombre FROM cat_vendedores ORDER BY nombre";
$rsV  = $clsConsulta->consultaGeneral($conV);
if ($clsConsulta->numrows > 0) {
    foreach ($rsV as $row) {
        $vendedores[] = ['id' => $row['id'], 'nombre' => $row['nombre']];
    }
}

$conC = "SELECT id, razon_social FROM cat_clientes ORDER BY razon_social";
$rsC  = $clsConsulta->consultaGeneral($conC);
if ($clsConsulta->numrows > 0) {
    foreach ($rsC as $row) {
        $clientes[] = ['id' => $row['id'], 'nombre' => $row['razon_social']];
    }
}
?>


<!-- daterangepicker -->
<script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>

<div class="ms-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="inicio">Inicio</a></li>
            <li class="breadcrumb-item active" aria-current="page">Reportes</li>
            <li class="breadcrumb-item active" aria-current="page">Ventas (Período)</li>
        </ol>
    </nav>
</div>

<section class="content">
    <div class="container-fluid">

        <!-- KPIs -->
        <div class="row g-3 mb-3">
            <div class="col-12 col-lg-4">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Total Ventas</span>
                            <i class="fa fa-dollar-sign"></i>
                        </div>
                        <h4 id="kpiTotalVentas" class="mt-2 mb-0">—</h4>
                    </div>
                </div>
            </div>
            <div class="col-12 col-lg-4">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Tickets</span>
                            <i class="fa fa-receipt"></i>
                        </div>
                        <h4 id="kpiTickets" class="mt-2 mb-0">—</h4>
                    </div>
                </div>
            </div>
            <div class="col-12 col-lg-4">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Ticket Promedio</span>
                            <i class="fa fa-chart-line"></i>
                        </div>
                        <h4 id="kpiTicketPromedio" class="mt-2 mb-0">—</h4>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filtros -->
        <!-- Filtros -->
        <div class="card mb-3">
            <div class="card-body">
                <div class="row g-3 align-items-end">
                    <!-- Fecha inicio -->
                    <div class="col-6 col-md-3">
                        <label class="form-label mb-1">Fecha inicio</label>
                        <input type="date" id="fechaIni" class="form-control" autocomplete="off" />
                    </div>

                    <!-- Fecha fin -->
                    <div class="col-6 col-md-3">
                        <label class="form-label mb-1">Fecha fin</label>
                        <input type="date" id="fechaFin" class="form-control" autocomplete="off" />
                    </div>

                    <!-- Vendedor -->
                    <div class="col-12 col-md-3">
                        <label class="form-label mb-1">Vendedor</label>
                        <select id="filtroVendedor" class="form-select">
                            <option value="">Todos</option>
                            <?php foreach ($vendedores as $v): ?>
                                <option value="<?= (int)$v['id'] ?>"><?= htmlspecialchars($v['nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Cliente -->
                    <div class="col-12 col-md-3">
                        <label class="form-label mb-1">Cliente</label>
                        <select id="filtroCliente" class="form-select">
                            <option value="">Todos</option>
                            <?php foreach ($clientes as $c): ?>
                                <option value="<?= (int)$c['id'] ?>"><?= htmlspecialchars($c['nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Forma de Pago -->
                    <div class="col-12 col-md-2">
                        <label class="form-label mb-1">Forma de Pago</label>
                        <select id="filtroForma" class="form-select">
                            <option value="">Todas</option>
                            <option value="contado">Contado</option>
                            <option value="credito">Crédito</option>
                        </select>

                    </div>

                    <!-- Estatus -->
                    <div class="col-12 col-md-1">
                        <label class="form-label mb-1">Estatus</label>
                        <select id="filtroEstatus" class="form-select">
                            <option value="">Todos</option>
                            <option value="procesada">Procesada</option>
                            <option value="cancelada">Cancelada</option>
                        </select>
                    </div>

                    <div class="col-12 d-flex gap-2">
                        <button id="btnAplicar" class="btn btn-primary">
                            <i class="fa fa-filter"></i> Aplicar filtros
                        </button>
                        <button id="btnLimpiar" class="btn btn-outline-secondary">
                            <i class="fa fa-eraser"></i> Limpiar
                        </button>
                    </div>
                </div>
            </div>
        </div>


        <!-- Tabla -->
        <div class="card">
            <div class="card-body">
                <table id="tablaVentasPeriodo" class="table table-striped table-hover w-100">
                    <thead class="table-light">
                        <tr>
                            <th>Fecha</th>
                            <th>Folio</th>
                            <th>Cliente</th>
                            <th>Vendedor</th>
                            <th>Total</th>
                            <th>Tipo de venta</th>
                            <th>Estatus</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                    <tfoot>
                        <tr>
                            <th colspan="4" class="text-end">Total mostrado:</th>
                            <th id="footerTotal">—</th>
                            <th colspan="2"></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

    </div>
</section>



<!-- JS del módulo -->
<script src="js/reportes/reportes.js"></script>