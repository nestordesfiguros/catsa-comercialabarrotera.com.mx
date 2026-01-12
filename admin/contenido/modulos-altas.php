<?php
$mensaje = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $clsConsulta->escape($_POST['nombre']);
    $archivo = $clsConsulta->escape($_POST['archivo']);
    $icono = $clsConsulta->escape($_POST['icono']);
    $modulo_padre = $_POST['modulo_padre'] ?: 'NULL';
    $observaciones = $clsConsulta->escape($_POST['observaciones'] ?? '');

    $sql = "INSERT INTO modulos (nombre, archivo, icono, modulo_padre, observaciones) 
        VALUES ('$nombre', '$archivo', '$icono', " . ($modulo_padre === 'NULL' ? 'NULL' : intval($modulo_padre)) . ", '$observaciones')";
    $res = $clsConsulta->aplicaquery($sql);
    if ($res) {
        $mensaje = "Módulo creado con éxito.";
    } else {
        $mensaje = "Error al crear módulo.";
    }
}

$modulos = $clsConsulta->consultaGeneral("SELECT id, nombre FROM modulos WHERE modulo_padre IS NULL ORDER BY nombre");
?>


<section class="content">
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-12">
                <div class="card animation slide-in-down">

                    <!-- /.card-header -->
                    <div class="card-body">

                        <h1>Nuevo módulo</h1>
                        <?php if ($mensaje): ?>
                            <div class="alert alert-info"><?= htmlspecialchars($mensaje) ?></div>
                        <?php endif; ?>

                        <form id="frmModulo" method="POST">
                            <div class="mb-3">
                                <label>Nombre</label>
                                <input type="text" name="nombre" class="form-control case-normal" required /> <!-- ⬅ normal -->
                            </div>
                            <div class="mb-3">
                                <label>Archivo</label>
                                <input type="text" name="archivo" class="form-control case-normal" required /> <!-- ⬅ normal -->
                            </div>
                            <div class="mb-3">
                                <label>Icono (clase fontawesome)</label>
                                <input type="text" name="icono" class="form-control case-normal" placeholder="fa fa-list" /> <!-- ⬅ normal -->
                            </div>
                            <div class="mb-3">
                                <label>Módulo padre (opcional)</label>
                                <select name="modulo_padre" class="form-select">
                                    <option value="">-- Ninguno --</option>
                                    <?php foreach ($modulos as $m): ?>
                                        <option value="<?= $m['id'] ?>"><?= htmlspecialchars($m['nombre']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label>Observaciones (opcional)</label>
                                <textarea name="observaciones" class="form-control case-normal" rows="3"></textarea> <!-- ⬅ normal -->
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