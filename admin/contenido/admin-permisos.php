<!-- contenido/admin-permisos.php -->
<div class="ms-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="inicio">Inicio</a></li>
            <li class="breadcrumb-item active" aria-current="page">Permisos por Rol</li>
        </ol>
    </nav>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Gestión de permisos por rol</h5>
            </div>
            <div class="card-body">
                <div class="form-group mb-3">
                    <label for="rolSelect">Selecciona un rol:</label>
                    <select class="form-select" id="rolSelect"></select>
                </div>

                <div id="tablaPermisosContainer" class="table-responsive d-none">
                    <form id="formPermisos">
                        <table class="table table-bordered align-middle text-center">
                            <thead>
                                <tr>
                                    <th>Módulo</th>
                                    <th>Tipo</th> <!-- Nueva columna tipo -->
                                    <th>
                                        Ver <br>
                                        <input type="checkbox" id="checkAllVer">
                                    </th>
                                    <th>
                                        Crear <br>
                                        <input type="checkbox" id="checkAllCrear">
                                    </th>
                                    <th>
                                        Editar <br>
                                        <input type="checkbox" id="checkAllEditar">
                                    </th>
                                    <th>
                                        Eliminar <br>
                                        <input type="checkbox" id="checkAllEliminar">
                                    </th>
                                </tr>
                            </thead>

                            <tbody id="tablaPermisosBody">
                                <!-- Aquí se insertan dinámicamente los módulos vía JS -->
                            </tbody>
                        </table>
                        <div class="text-end mt-3">
                            <button type="submit" class="btn btn-success">Guardar permisos</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Modal Cambio de Rol -->
<div class="modal fade" id="modalCambioRol" tabindex="-1" aria-labelledby="modalCambioRolLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="formCambioRol">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalCambioRolLabel">Cambiar Rol del Usuario</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="idUsuario" name="idUsuario">
                    <div class="mb-3">
                        <label for="rolNuevo" class="form-label">Selecciona nuevo rol:</label>
                        <select id="rolNuevo" name="rolNuevo" class="form-select" required>
                            <!-- Se cargan dinámicamente -->
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Guardar</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="js/admin-permisos.js"></script>