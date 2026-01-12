<?php
$fecha_ini = $_GET['fecha_ini'] ?? date('Y-m-01');
$fecha_fin = $_GET['fecha_fin'] ?? date('Y-m-d');
$estatus = $_GET['estatus'] ?? 'pendiente';
?>

<div class="ms-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="inicio">Inicio</a></li>
            <li class="breadcrumb-item active" aria-current="page">Salidas de almacén</li>
        </ol>
    </nav>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="card">
            <div class="card-header">
                <div class="row col-12">
                    <div class="col-8">
                        <div class="row">
                            <div class="col-12 col-md-2">
                                <a type="button" class="btn btn-secondary btn-fixed mt-2 mt-md-0" href="productos">Productos </a>
                            </div>
                            <div class="col-12 col-md-2">
                                <a type="button" class="btn btn-secondary btn-fixed mt-2 mt-md-0" href="almacen-entradas"><i class="fa-solid fa-warehouse"></i> Entradas </a>
                            </div>
                            <div class="col-12 col-md-2">
                                <a type="button" class="btn btn-info btn-fixed mt-2 mt-md-0" href="salidas-almacen"><i class="fa-solid fa-plus"></i> Salidas </a>
                            </div>
                            <div class="col-12 col-md-2">
                                <a type="button" class="btn btn-secondary btn-fixed mt-2 mt-md-0" href="ofertas"><i class="fas fa-tags"></i> Ofertas </a>
                            </div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="col-12">
                            <div class="form-outline" data-mdb-input-init>
                                <input type="text" id="search" class="form-control" />
                                <label class="form-label" for="form12">Buscar</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-body">
                <div class="bg-primary text-white">
                    <div class="d-flex justify-content-between align-items-center p-2">
                        <span>Salidas registradas</span>
                        <div>
                            <input type="date" id="fecha_ini" value="<?= $fecha_ini ?>" class="form-control d-inline w-auto">
                            <input type="date" id="fecha_fin" value="<?= $fecha_fin ?>" class="form-control d-inline w-auto">
                            <select id="filtro_estatus" class="form-control d-inline w-auto">
                                <option value="todos">Todos</option>
                                <option value="pendiente" <?= $estatus == 'pendiente' ? 'selected' : '' ?>>Pendiente</option>
                                <option value="procesada" <?= $estatus == 'procesada' ? 'selected' : '' ?>>Procesada</option>
                                <option value="cancelada" <?= $estatus == 'cancelada' ? 'selected' : '' ?>>Cancelada</option>
                            </select>
                        </div>
                    </div>
                </div>
                <table id="tablaSalidas" class="table table-bordered table-hover">
                    <thead class="bg-light">
                        <tr>
                            <th>ID</th>
                            <th>Fecha</th>
                            <th>Almacén</th>
                            <th>Vehículo</th>
                            <th>Operador</th>
                            <th>Remisiones</th>
                            <th>Total</th>
                            <th>Estatus</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tfoot class="bg-light">
                        <tr>
                            <th colspan="6" class="text-end">Total:</th>
                            <th id="footerTotal"></th>
                            <th colspan="2"></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</section>

<script src="js/salidas-almacen-listado.js"></script>