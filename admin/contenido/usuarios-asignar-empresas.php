<?php
$id_usuario = $cat;
//$id_personal = $subcat;
if ($id_usuario <= 0) {
    echo "Usuario inválido";
    exit;
}

$usuario = $clsConsulta->consultaGeneral("SELECT nombre FROM usuarios WHERE id = $id_usuario");
$empresas = $clsConsulta->consultaGeneral("SELECT id, razon_social FROM cat_empresas WHERE estatus = 1 ORDER BY razon_social");
$asignadas = $clsConsulta->consultaGeneral("SELECT id_empresa FROM usuarios_empresas WHERE id_usuario = $id_usuario");
$empresas_usuario = is_array($asignadas) ? array_column($asignadas, 'id_empresa') : [];

?>
<div class="ms-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="utilerias">Utilerias</a></li>
            <li class="breadcrumb-item"><a href="usuarios">usuarios</a></li>
            <li class="breadcrumb-item active" aria-current="page"> Asignar Empresas</li>
        </ol>
    </nav>
</div>
<section class="content">

    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="mb-3">Asignar empresas a <strong><?= htmlspecialchars($usuario[1]['nombre']) ?></strong></h4>

                        <form id="formAsignarEmpresas">
                            <input type="hidden" name="id_usuario" value="<?= $id_usuario ?>">

                            <table id="tablaEmpresas" class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th class="text-center">Asignar</th>
                                        <th>Empresa</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($empresas as $e): ?>
                                        <tr>
                                            <td class="text-center">
                                                <input type="checkbox" class="chk-empresa" name="empresas[]" value="<?= $e['id'] ?>"
                                                    <?= in_array($e['id'], $empresas_usuario) ? 'checked' : '' ?>>
                                            </td>
                                            <td><?= htmlspecialchars($e['razon_social'] ?? '') ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>

                            <div class="text-end">
                                <button type="submit" class="btn btn-primary">Guardar asignación</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
    $(document).ready(function() {
        const tabla = $('#tablaEmpresas').DataTable({
            paging: false,
            searching: true,
            ordering: true,
            info: false,
            language: {
                url: "https://cdn.datatables.net/plug-ins/1.10.15/i18n/Spanish.json",
                sSearch: '<i class="fa fa-search" aria-hidden="true"></i> Buscar'
            }
        });

        $('#formAsignarEmpresas').on('submit', function(e) {
            e.preventDefault();

            const seleccionadas = $('input[name="empresas[]"]:checked').length;

            if (seleccionadas === 0) {
                alertify.error('Debe seleccionar al menos una empresa');
                return;
            }

            const datos = $(this).serialize();

            alertify.confirm('¿Asignar estas empresas al usuario?', function() {
                $.ajax({
                    url: 'ajax/usuarios/guardar-empresas.php',
                    method: 'POST',
                    data: datos,
                    success: function(res) {
                        try {
                            const json = JSON.parse(res);
                            if (json.success) {
                                alertify.success('Empresas asignadas correctamente');
                            } else {
                                alertify.error(json.message || 'Error al guardar');
                            }
                        } catch (e) {
                            console.error(res);
                            alertify.error('Error inesperado');
                        }
                    }
                });
            });
        });

    });
</script>