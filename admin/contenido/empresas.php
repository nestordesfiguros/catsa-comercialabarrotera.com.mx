<?php /* contenido/empresas.php */ ?>
<div class="ms-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="inicio">Inicio</a></li>
            <li class="breadcrumb-item"><a href="utilerias">Utilerías</a></li>
            <li class="breadcrumb-item active" aria-current="page">Empresas</li>
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
                                <a class="btn btn-info btn-fixed mt-2 mt-md-0" href="empresas-altas"><i class="fa fa-plus"></i> Empresas</a>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="form-outline mt-2 mt-md-0" data-mdb-input-init>
                                    <input type="text" id="search" class="form-control" />
                                    <label class="form-label" for="search">Buscar</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <table id="tablaEmpresas" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th class="text-center">Razón Social</th>
                                    <th class="text-center">Acciones</th>
                                    <th class="text-center">Estatus</th>
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

<!-- Modal estatus -->
<div class="modal fade" id="modalEstatus" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Cambiar estatus</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" onclick="cerrarEstatus();"></button>
            </div>
            <div class="modal-body">
                <h5><span id="estatusActual"></span></h5>
                <input type="hidden" id="idEmpresaEstatus">
                <input type="hidden" id="estatusNuevo">
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal" onclick="cerrarEstatus();">Cerrar</button>
                <button class="btn btn-primary" onclick="modificaEstatus();">Modificar</button>
            </div>
        </div>
    </div>
</div>

<script src="js/empresas-lista.js"></script>