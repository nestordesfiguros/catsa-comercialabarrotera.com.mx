<?php
// contenido/reportes.php
?>

<div class="ms-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="inicio">Inicio</a></li>
            <li class="breadcrumb-item active" aria-current="page">Reportes</li>
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
                        <h3>Menú de Reportes</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <!-- Reporte de Ventas -->
                            <div class="col-12 col-md-6 col-lg-4 mb-3">
                                <div class="card h-100">
                                    <div class="card-header bg-primary text-white">
                                        <h5><i class="fas fa-chart-line me-2"></i>Ventas</h5>
                                    </div>
                                    <div class="card-body">
                                        <ul class="list-group list-group-flush">
                                            <li class="list-group-item"><a href="reportes-ventas-por-vendedor">Por Vendedor</a></li>
                                            <li class="list-group-item"><a href="reportes-ventas-por-producto">Por Producto</a></li>
                                            <li class="list-group-item"><a href="reportes-ventas-por-cliente">Por Cliente</a></li>
                                            <li class="list-group-item"><a href="reportes-ventas-generales-por-periodo">Generales por Periodo</a></li>
                                            <li class="list-group-item"><a href="reportes-detallado-facturas">Detallado de Facturas</a></li>
                                            <li class="list-group-item"><a href="reportes-resumen-notas-venta">Resumen de Notas de Venta</a></li>
                                            <li class="list-group-item"><a href="reportes-ventas-anuales-mensuales">Ventas Anuales/Mensuales</a></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <!-- Reporte de Compras -->
                            <div class="col-12 col-md-6 col-lg-4 mb-3">
                                <div class="card h-100">
                                    <div class="card-header bg-success text-white">
                                        <h5><i class="fas fa-shopping-cart me-2"></i>Compras</h5>
                                    </div>
                                    <div class="card-body">
                                        <ul class="list-group list-group-flush">
                                            <li class="list-group-item"><a href="reportes-compras-por-proveedor">Por Proveedor</a></li>
                                            <li class="list-group-item"><a href="reportes-compras-por-producto">Por Producto</a></li>
                                            <li class="list-group-item"><a href="reportes-compras-generales-por-periodo">Generales por Periodo</a></li>
                                            <li class="list-group-item"><a href="reportes-cuentas-por-pagar">Cuentas por Pagar</a></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <!-- Reporte de Inventarios -->
                            <div class="col-12 col-md-6 col-lg-4 mb-3">
                                <div class="card h-100">
                                    <div class="card-header bg-warning text-dark">
                                        <h5><i class="fas fa-boxes me-2"></i>Inventarios</h5>
                                    </div>
                                    <div class="card-body">
                                        <ul class="list-group list-group-flush">
                                            <li class="list-group-item"><a href="reportes-kardex">Kárdex</a></li>
                                            <li class="list-group-item"><a href="reportes-existencias-costos">Existencias y Costos</a></li>
                                            <li class="list-group-item"><a href="reportes-inventario-fisico">Inventario Físico</a></li>
                                            <li class="list-group-item"><a href="reportes-productos-mas-vendidos">Productos Más Vendidos</a></li>
                                            <li class="list-group-item"><a href="reportes-stock-minimo-maximo">Stock Mínimo y Máximo</a></li>
                                            <li class="list-group-item"><a href="reportes-productos-pendientes">Productos Pendientes</a></li>
                                            <li class="list-group-item"><a href="reportes-trazabilidad-productos">Trazabilidad de Productos</a></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <!-- Reporte de Clientes y CXC -->
                            <div class="col-12 col-md-6 col-lg-4 mb-3">
                                <div class="card h-100">
                                    <div class="card-header bg-info text-white">
                                        <h5><i class="fas fa-users me-2"></i>Clientes y CXC</h5>
                                    </div>
                                    <div class="card-body">
                                        <ul class="list-group list-group-flush">
                                            <li class="list-group-item"><a href="reportes-antiguedad-saldos">Antigüedad de Saldos</a></li>
                                            <li class="list-group-item"><a href="reportes-estado-cuenta">Estado de Cuenta</a></li>
                                            <li class="list-group-item"><a href="reportes-cartera-vencida">Cartera Vencida</a></li>
                                            <li class="list-group-item"><a href="reportes-catalogo-clientes">Catálogo de Clientes</a></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <!-- Reporte de Comisiones -->
                            <div class="col-12 col-md-6 col-lg-4 mb-3">
                                <div class="card h-100">
                                    <div class="card-header bg-secondary text-white">
                                        <h5><i class="fas fa-money-bill-wave me-2"></i>Comisiones</h5>
                                    </div>
                                    <div class="card-body">
                                        <ul class="list-group list-group-flush">
                                            <li class="list-group-item"><a href="reportes-comisiones-generales-por-periodo">Generales por Periodo</a></li>
                                            <li class="list-group-item"><a href="reportes-comisiones-por-vendedor">Por Vendedor</a></li>
                                            <li class="list-group-item"><a href="reportes-comisiones-pendientes">Comisiones Pendientes</a></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <!-- Reporte de Utilidades -->
                            <div class="col-12 col-md-6 col-lg-4 mb-3">
                                <div class="card h-100">
                                    <div class="card-header bg-danger text-white">
                                        <h5><i class="fas fa-chart-pie me-2"></i>Utilidades</h5>
                                    </div>
                                    <div class="card-body">
                                        <ul class="list-group list-group-flush">
                                            <li class="list-group-item"><a href="reportes-utilidades-por-venta-general">Por Venta General</a></li>
                                            <li class="list-group-item"><a href="reportes-utilidades-por-producto">Por Producto</a></li>
                                            <li class="list-group-item"><a href="reportes-utilidades-por-categoria">Por Categoría</a></li>
                                            <li class="list-group-item"><a href="reportes-cortes-utilidad">Cortes de Utilidad</a></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <!-- Reporte de Gastos -->
                            <div class="col-12 col-md-6 col-lg-4 mb-3">
                                <div class="card h-100">
                                    <div class="card-header bg-dark text-white">
                                        <h5><i class="fas fa-receipt me-2"></i>Gastos</h5>
                                    </div>
                                    <div class="card-body">
                                        <ul class="list-group list-group-flush">
                                            <li class="list-group-item"><a href="reportes-gastos-por-categoria">Por Categoría</a></li>
                                            <li class="list-group-item"><a href="reportes-gastos-por-periodo">Por Periodo</a></li>
                                            <li class="list-group-item"><a href="reportes-gastos-por-proveedor">Por Proveedor</a></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <!-- Reporte de Catálogos -->
                            <div class="col-12 col-md-6 col-lg-4 mb-3">
                                <div class="card h-100">
                                    <div class="card-header bg-purple text-white">
                                        <h5><i class="fas fa-list me-2"></i>Catálogos</h5>
                                    </div>
                                    <div class="card-body">
                                        <ul class="list-group list-group-flush">
                                            <li class="list-group-item"><a href="reportes-catalogo-productos">Catálogo de Productos</a></li>
                                            <li class="list-group-item"><a href="reportes-catalogo-proveedores">Catálogo de Proveedores</a></li>
                                            <li class="list-group-item"><a href="reportes-catalogo-vendedores">Catálogo de Vendedores</a></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
                <!-- /.card -->
            </div>
            <!-- /.col -->
        </div>
        <!-- /.row -->

    </div>
    <!-- /.container-fluid -->
</section>

<style>
    .bg-purple {
        background-color: #6f42c1 !important;
    }

    .list-group-item a {
        text-decoration: none;
        color: #495057;
        font-weight: 500;
        transition: color 0.3s ease;
    }

    .list-group-item a:hover {
        color: #007bff;
    }

    .card {
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        transition: box-shadow 0.3s ease;
    }

    .card:hover {
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    }
</style>