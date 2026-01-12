<?php
// contenido/empresas-editar.php
// $id normalmente viene por la ruta: empresas-editar/{id}
$id_emp = isset($id) ? (int)$id : (isset($_GET['id']) ? (int)$_GET['id'] : 0);
?>
<div class="ms-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="inicio">Inicio</a></li>
            <li class="breadcrumb-item"><a href="utilerias">Utilerías</a></li>
            <li class="breadcrumb-item"><a href="empresas">Empresas</a></li>
            <li class="breadcrumb-item active" aria-current="page">Editar</li>
        </ol>
    </nav>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="card card-body">
            <h5>Editar empresa</h5>

            <form id="formEmpresa" autocomplete="off">
                <input type="hidden" id="empresa_id" name="id" value="<?= $id_emp ?>">

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Razón Social</label>
                        <input type="text" class="form-control" id="razon_social" name="razon_social" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Nombre Comercial</label>
                        <input type="text" class="form-control" id="nombre_comercial" name="nombre_comercial">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">RFC</label>
                        <input type="text" class="form-control" id="rfc" name="rfc" maxlength="13" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Correo</label>
                        <input type="email" class="form-control" id="correo" name="correo">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Régimen fiscal (SAT)</label>
                        <select id="regimen_fiscal" name="regimen_fiscal" class="form-select" required>
                            <option value="">Selecciona...</option>
                        </select>
                    </div>

                    <div class="col-md-12">
                        <label class="form-label">Calle</label>
                        <input type="text" class="form-control" id="calle" name="calle">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Número exterior</label>
                        <input type="text" class="form-control" id="num_ext" name="num_ext">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Número interior</label>
                        <input type="text" class="form-control" id="num_int" name="num_int">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Código Postal</label>
                        <input type="text" class="form-control" id="cp" name="cp" maxlength="5">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Estado</label>
                        <select id="id_estado" name="id_estado" class="form-select">
                            <option value="">Selecciona...</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Municipio</label>
                        <select id="id_municipio" name="id_municipio" class="form-select">
                            <option value="">Selecciona...</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Colonia</label>
                        <input type="text" class="form-control" id="colonia" name="colonia">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Teléfono 1</label>
                        <input type="text" class="form-control" id="tel1" name="tel1">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Teléfono 2</label>
                        <input type="text" class="form-control" id="tel2" name="tel2">
                    </div>
                </div>

                <div class="mt-3 d-flex gap-2">
                    <a href="empresas" class="btn btn-secondary">
                        <i class="fa-solid fa-xmark"></i> Cancelar
                    </a>
                    <button class="btn btn-primary" type="submit">
                        <i class="fa-solid fa-save"></i> Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>
</section>

<!-- JS del formulario (usa validate.js y AJAX) -->
<script src="js/empresas-form.js"></script>