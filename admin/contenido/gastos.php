<?php
// contenido/gastos.php
?>
<div class="ms-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="inicio">Inicio</a></li>
            <li class="breadcrumb-item active" aria-current="page">Gastos</li>
        </ol>
    </nav>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">

                <div class="card">
                    <div class="card-header">
                        <div class="row g-2 align-items-center">
                            <div class="col-lg-7">
                                <button onclick="FnGastosAltas()" type="button" class="btn btn-info btn-fixed" title="+ Gastos">
                                    <i class="fa-solid fa-plus"></i> Gastos
                                </button>
                                <!-- Botones export -->
                                <button id="btnExportCSV" type="button" class="btn btn-outline-secondary ms-2">
                                    <i class="fa-solid fa-file-csv"></i> CSV
                                </button>
                                <button id="btnExportPDF" type="button" class="btn btn-outline-secondary ms-2">
                                    <i class="fa-solid fa-file-pdf"></i> PDF
                                </button>
                            </div>

                            <!-- Filtros -->
                            <div class="col-lg-3">
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fa-solid fa-calendar-day"></i></span>
                                    <input type="date" id="filtroFechaInicio" class="form-control" />
                                    <input type="date" id="filtroFechaFin" class="form-control" />
                                </div>
                            </div>
                            <div class="col-lg-2">
                                <div class="form-outline" data-mdb-input-init>
                                    <input type="text" id="searchGastos" class="form-control" />
                                    <label class="form-label" for="searchGastos">Buscar</label>
                                </div>
                            </div>
                        </div>

                        <div class="row g-2 mt-2">
                            <div class="col-md-4">
                                <label class="form-label mb-0">Categoría</label>
                                <select id="filtroCategoria" class="form-select"></select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label mb-0">Método</label>
                                <select id="filtroMetodo" class="form-select"></select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label mb-0">Estatus</label>
                                <select id="filtroEstatus" class="form-select">
                                    <option value="">Todos</option>
                                    <option value="Activo">Activo</option>
                                    <option value="Cancelado">Cancelado</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="card-body">
                        <table id="tablaGastos" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Categoría</th>
                                    <th>Descripción</th>
                                    <th>Método</th>
                                    <th>Referencia</th>
                                    <th class="text-end">Monto</th>
                                    <th class="text-center">Estatus</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="5" class="text-end">Total filtrado:</th>
                                    <th id="totalFiltrado" class="text-end">0.00</th>
                                    <th colspan="2"></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </div>
</section>

<!-- Modal Gasto -->
<div class="modal fade" id="modalGasto" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form id="formGasto" novalidate>
                <div class="modal-header">
                    <h5 class="modal-title" id="tituloModalGasto">Nuevo gasto</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="idGasto" name="idGasto">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="fechaGasto" class="form-label">Fecha</label>
                            <input type="date" class="form-control" id="fechaGasto" name="fechaGasto" required>
                        </div>
                        <div class="col-md-6">
                            <label for="horaGasto" class="form-label">Hora</label>
                            <input type="time" class="form-control" id="horaGasto" name="horaGasto" required>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-8">
                            <label for="idCategoria" class="form-label">Categoría</label>
                            <div class="input-group">
                                <select class="form-select" id="idCategoria" name="idCategoria" required></select>
                                <button type="button" id="btnNuevaCategoria" class="btn btn-outline-secondary" title="Nueva categoría">
                                    <i class="fa-solid fa-tag"></i> <i class="fa-solid fa-plus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label for="montoGasto" class="form-label">Monto</label>
                            <input type="number" step="0.01" min="0" class="form-control text-end" id="montoGasto" name="montoGasto" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="descripcionGasto" class="form-label">Descripción</label>
                        <input type="text" class="form-control" id="descripcionGasto" name="descripcionGasto" required>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="idMetodo" class="form-label">Método de pago</label>
                            <select class="form-select" id="idMetodo" name="idMetodo" required></select>
                        </div>
                        <div class="col-md-6">
                            <label for="referenciaGasto" class="form-label">Referencia</label>
                            <input type="text" class="form-control" id="referenciaGasto" name="referenciaGasto">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="notasGasto" class="form-label">Notas</label>
                        <textarea class="form-control" id="notasGasto" name="notasGasto" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fa-solid fa-xmark"></i> Cerrar
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fa-solid fa-floppy-disk"></i> Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Nueva categoría -->
<div class="modal fade" id="modalCategoria" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content">
            <form id="formCategoria" novalidate>
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fa-solid fa-tag"></i> Nueva categoría</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3 form-group">
                        <label for="nombreCategoriaNuevo" class="form-label">Nombre de la categoría</label>
                        <input type="text" class="form-control" id="nombreCategoriaNuevo" name="nombreCategoriaNuevo" maxlength="120" required>
                    </div>
                    <div class="mb-0 form-group">
                        <label for="descCategoriaNuevo" class="form-label">Descripción (opcional)</label>
                        <input type="text" class="form-control" id="descCategoriaNuevo" name="descCategoriaNuevo" maxlength="255">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fa-solid fa-xmark"></i> Cancelar
                    </button>
                    <button id="btnGuardarCategoria" type="submit" class="btn btn-primary">
                        <i class="fa-solid fa-floppy-disk"></i> Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="js/gastos.js"></script>