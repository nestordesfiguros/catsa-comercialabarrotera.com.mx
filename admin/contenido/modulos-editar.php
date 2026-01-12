<?php
$id = $cat;
if (!$id) die("ID inválido");

$mensaje = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $clsConsulta->escape($_POST['nombre']);
    $archivo = $clsConsulta->escape($_POST['archivo']);
    $icono = $clsConsulta->escape($_POST['icono']);
    $modulo_padre = $_POST['modulo_padre'] ?: 'NULL';
    $observaciones = $clsConsulta->escape($_POST['observaciones'] ?? '');

    $sql = "UPDATE modulos SET 
                nombre='$nombre', 
                archivo='$archivo', 
                icono='$icono', 
                modulo_padre=" . ($modulo_padre === 'NULL' ? 'NULL' : intval($modulo_padre)) . ",
                observaciones='$observaciones'
            WHERE id=$id";
    $res = $clsConsulta->aplicaquery($sql);
    if ($res) {
        $mensaje = "Módulo actualizado con éxito.";
    } else {
        $mensaje = "Error al actualizar módulo.";
    }
}

// Obtener datos actuales
$modulo = $clsConsulta->consultaGeneral("SELECT * FROM modulos WHERE id=$id");
if (!$modulo) die("Módulo no encontrado");
$modulo = $modulo[1];

$modulos = $clsConsulta->consultaGeneral("SELECT id, nombre FROM modulos WHERE modulo_padre IS NULL AND id <> $id ORDER BY nombre");
?>

<section class="content">
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-12">
                <div class="card animation slide-in-down">

                    <!-- /.card-header -->
                    <div class="card-body">

                        <h1>Editar módulo</h1>
                        <?php if ($mensaje): ?>
                            <div class="alert alert-info"><?= htmlspecialchars($mensaje) ?></div>
                        <?php endif; ?>

                        <form method="POST">
                            <div class="mb-3">
                                <label>Nombre</label>
                                <input type="text" name="nombre" value="<?= htmlspecialchars($modulo['nombre']) ?>" class="form-control case-normal" required />
                            </div>
                            <div class="mb-3">
                                <label>Archivo</label>
                                <input type="text" name="archivo" value="<?= htmlspecialchars($modulo['archivo']) ?>" class="form-control case-normal" required />
                            </div>
                            <div class="mb-3">
                                <label>Icono (clase fontawesome)</label>
                                <input type="text" name="icono" value="<?= htmlspecialchars($modulo['icono']) ?>" class="form-control case-normal" />
                            </div>
                            <div class="mb-3">
                                <label>Módulo padre (opcional)</label>
                                <select name="modulo_padre" class="form-select">
                                    <option value="">-- Ninguno --</option>
                                    <?php foreach ($modulos as $m): ?>
                                        <option value="<?= $m['id'] ?>" <?= $m['id'] == $modulo['modulo_padre'] ? 'selected' : '' ?>><?= htmlspecialchars($m['nombre']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label>Observaciones (opcional)</label>
                                <textarea name="observaciones" class="form-control" rows="3"><?= htmlspecialchars($modulo['observaciones'] ?? '') ?></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">Guardar</button>
                            <a href="modulos-lista" class="btn btn-secondary">Regresar</a>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    </div>
</section>