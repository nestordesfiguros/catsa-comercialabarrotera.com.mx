<?php
// contenido/reportes-ventas-producto.php

// Carga combos (vendedores, clientes, productos)
$vendedores = [];
$clientes   = [];
$productos  = [];

$conV = "SELECT id, nombre FROM cat_vendedores WHERE estatus=1 ORDER BY nombre";
$rsV  = $clsConsulta->consultaGeneral($conV);
if (is_array($rsV)) foreach ($rsV as $row) $vendedores[] = ['id' => $row['id'], 'nombre' => $row['nombre']];

$conC = "SELECT id, razon_social FROM cat_clientes WHERE estatus=1 ORDER BY razon_social";
$rsC  = $clsConsulta->consultaGeneral($conC);
if (is_array($rsC)) foreach ($rsC as $row) $clientes[] = ['id' => $row['id'], 'nombre' => $row['razon_social']];

// Ajusta a tus columnas reales si difieren
$conP = "SELECT id_producto AS id, nombre FROM cat_productos ORDER BY nombre";
$rsP  = $clsConsulta->consultaGeneral($conP);
if (is_array($rsP)) {
    foreach ($rsP as $row) {
        // arrays 1-based OK porque iteramos por foreach
        $productos[] = ['id' => $row['id'], 'nombre' => $row['nombre']];
    }
}
?>

<div class="ms-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="inicio">Inicio</a></li>
            <li class="breadcrumb-item"><a href="reportes">Reportes</a></li>
            <li class="breadcrumb-item active" aria-current="page">Ventas por producto</li>
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
                        <h4 id="kpiVtaTotal" class="mt-2 mb-0">—</h4>
                    </div>
                </div>
            </div>
            <div class="col-12 col-lg-4">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Unidades</span>
                            <i class="fa fa-cubes"></i>
                        </div>
                        <h4 id="kpiUnidades" class="mt-2 mb-0">—</h4>
                    </div>
                </div>
            </div>
            <div class="col-12 col-lg-4">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Top producto</span>
                            <i class="fa fa-trophy"></i>
                        </div>
                        <h6 id="kpiTopProducto" class="mt-2 mb-0 text-truncate">—</h6>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filtros -->
        <div class="card mb-3">
            <div class="card-body">
                <div class="row g-3 align-items-end">
                    <div class="col-6 col-md-3">
                        <label class="form-label mb-1">Fecha inicio</label>
                        <input type="date" id="fechaIni" class="form-control" autocomplete="off" />
                    </div>
                    <div class="col-6 col-md-3">
                        <label class="form-label mb-1">Fecha fin</label>
                        <input type="date" id="fechaFin" class="form-control" autocomplete="off" />
                    </div>

                    <div class="col-12 col-md-3">
                        <label class="form-label mb-1">Vendedor</label>
                        <select id="filtroVendedor" class="form-select">
                            <option value="">Todos</option>
                            <?php foreach ($vendedores as $v): ?>
                                <option value="<?= (int)$v['id'] ?>"><?= htmlspecialchars($v['nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-12 col-md-3">
                        <label class="form-label mb-1">Cliente</label>
                        <select id="filtroCliente" class="form-select">
                            <option value="">Todos</option>
                            <?php foreach ($clientes as $c): ?>
                                <option value="<?= (int)$c['id'] ?>"><?= htmlspecialchars($c['nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-12 col-md-3">
                        <label class="form-label mb-1">Producto</label>
                        <select id="filtroProducto" class="form-select">
                            <option value="">Todos</option>
                            <?php foreach ($productos as $p): ?>
                                <option value="<?= (int)$p['id'] ?>"><?= htmlspecialchars($p['nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-12 col-md-2">
                        <label class="form-label mb-1">Tipo de venta</label>
                        <select id="filtroForma" class="form-select">
                            <option value="">Todas</option>
                            <option value="contado">Contado</option>
                            <option value="credito">Crédito</option>
                        </select>
                    </div>

                    <div class="col-12 col-md-1">
                        <label class="form-label mb-1">Estatus</label>
                        <select id="filtroEstatus" class="form-select">
                            <option value="">Todos</option>
                            <option value="procesada">Procesada</option>
                            <option value="cancelada">Cancelada</option>
                            <option value="pendiente">Pendiente</option>
                        </select>
                    </div>

                    <div class="col-12 col-md-2">
                        <button id="btnAplicar" class="btn btn-primary">
                            <i class="fa fa-filter"></i> Aplicar filtros
                        </button>
                    </div>
                    <div class="col-12 col-md-2">
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
                <table id="tablaVentasProducto" class="table table-striped table-hover w-100">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Unidades</th>
                            <th>Ventas</th>
                            <th>Precio Prom.</th>
                            <th>% Participación</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                    <tfoot>
                        <tr>
                            <th class="text-end">Totales:</th>
                            <th id="ftUnidades">—</th>
                            <th id="ftVentas">—</th>
                            <th></th>
                            <th></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

    </div>
</section>

<!-- JS del reporte -->
<script src="js/reportes/reportes-ventas-producto.js"></script>