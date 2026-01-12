<div class="ms-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="inicio">Inicio</a></li>
            <li class="breadcrumb-item"><a href="utilerias">Configuración</a></li>
            <li class="breadcrumb-item active" aria-current="page">Administrar Roles</li>
        </ol>
    </nav>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Gestión de Roles</h5>
            </div>
            <div class="card-body">

                <form id="formNuevoRol" class="row g-3">
                    <div class="col-md-6">
                        <input type="text" name="nombre" class="form-control" placeholder="Nombre del rol" required>
                    </div>
                    <div class="col-md-6 text-end">
                        <button type="submit" class="btn btn-success">Agregar rol</button>
                    </div>
                </form>

                <hr>

                <table class="table table-bordered mt-4" id="tablaRoles">
                    <thead class="table-secondary">
                        <tr>
                            <th>Nombre del rol</th>
                            <th>Permisos</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Cargado por JS -->
                    </tbody>
                </table>

            </div>
        </div>
    </div>
</section>

<script src="js/admin-roles.js"></script>