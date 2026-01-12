<?php
// Obtener módulos con info suficiente para agrupar
$sql = "SELECT m1.id,
               m1.nombre,
               m1.archivo,
               m1.icono,
               m1.observaciones,
               m1.modulo_padre,
               m2.nombre AS padre
        FROM modulos m1
        LEFT JOIN modulos m2 ON m1.modulo_padre = m2.id
        ORDER BY m1.nombre";
$modulos = $clsConsulta->consultaGeneral($sql);

// Agrupar: padres e hijos
$padres = [];
$hijosPorPadre = [];

foreach ($modulos as $m) {
    if (empty($m['modulo_padre'])) {
        $padres[$m['id']] = $m; // es padre
    } else {
        $hijosPorPadre[$m['modulo_padre']][] = $m; // es hijo de modulo_padre
    }
}

// Ordenar por nombre (por si la BD no lo dejó perfecto)
// Mantén las claves (IDs) de $padres
uasort($padres, function ($a, $b) {
    return strcasecmp($a['nombre'], $b['nombre']);
});

foreach ($hijosPorPadre as $pid => &$lst) {
    usort($lst, function ($a, $b) {
        return strcasecmp($a['nombre'], $b['nombre']);
    });
}
unset($lst);
?>

<section class="content">
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-12">
                <div class="card animation slide-in-down">
                    <div class="card-body">
                        <h1 class="h3 mb-3">Módulos y Menús</h1>
                        <a href="modulos-altas" class="btn btn-success mb-3">+ Nuevo módulo</a>

                        <table class="table table-bordered align-middle">
                            <thead class="table-dark">
                                <tr>
                                    <th>Nombre</th>
                                    <th>Archivo</th>
                                    <th>Icono</th>
                                    <th>Módulo Padre</th>
                                    <th>Observaciones</th>
                                    <th style="width:220px;">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                // 1) Pintar cada padre (gris) y enseguida sus hijos (sin bg)
                                foreach ($padres as $p):
                                ?>
                                    <tr class="table-secondary">
                                        <td><?= htmlspecialchars($p['nombre']) ?></td>
                                        <td><?= htmlspecialchars($p['archivo']) ?></td>
                                        <td>
                                            <?php if (!empty($p['icono'])): ?>
                                                <i class="<?= htmlspecialchars($p['icono']) ?>"></i>
                                                <?= htmlspecialchars($p['icono']) ?>
                                            <?php endif; ?>
                                        </td>

                                        <td>-</td>
                                        <td><?= htmlspecialchars($p['observaciones'] ?? '') ?></td>
                                        <td class="col-acciones">
                                            <div class="btn-group btn-group-sm" role="group" aria-label="Acciones">
                                                <a href="modulos-editar/<?= (int)$p['id'] ?>" class="btn btn-primary me-2" title="Editar">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <button type="button" class="btn btn-danger btn-eliminar me-2" data-id="<?= (int)$p['id'] ?>" title="Eliminar">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                                <button type="button" class="btn btn-info btn-codigo"
                                                    data-nombre="<?= htmlspecialchars($p['nombre']) ?>"
                                                    data-archivo="<?= htmlspecialchars($p['archivo']) ?>"
                                                    data-icono="<?= htmlspecialchars($p['icono']) ?>"
                                                    data-padre=""
                                                    data-observaciones="<?= htmlspecialchars($p['observaciones'] ?? '') ?>"
                                                    title="Ver código">
                                                    <i class="fas fa-code"></i>
                                                </button>
                                            </div>
                                        </td>


                                    </tr>
                                    <?php
                                    // Hijos del padre actual
                                    if (!empty($hijosPorPadre[$p['id']])):
                                        foreach ($hijosPorPadre[$p['id']] as $h):
                                    ?>
                                            <tr>
                                                <td><?= htmlspecialchars($h['nombre']) ?></td>
                                                <td><?= htmlspecialchars($h['archivo']) ?></td>
                                                <td>
                                                    <?php if (!empty($h['icono'])): ?>
                                                        <i class="<?= htmlspecialchars($h['icono']) ?>"></i>
                                                        <?= htmlspecialchars($h['icono']) ?>
                                                    <?php endif; ?>
                                                </td>

                                                <td><?= htmlspecialchars($p['nombre']) ?></td>
                                                <td><?= htmlspecialchars($h['observaciones'] ?? '') ?></td>
                                                <td class="d-flex gap-2">
                                                    <a href="modulos-editar/<?= (int)$h['id'] ?>" class="btn btn-primary btn-sm" title="Editar">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <button type="button"
                                                        class="btn btn-danger btn-sm btn-eliminar"
                                                        data-id="<?= (int)$h['id'] ?>"
                                                        title="Eliminar">
                                                        <i class="fas fa-trash"></i>
                                                    </button>

                                                    <button type="button"
                                                        class="btn btn-info btn-sm btn-codigo"
                                                        data-nombre="<?= htmlspecialchars($h['nombre']) ?>"
                                                        data-archivo="<?= htmlspecialchars($h['archivo']) ?>"
                                                        data-icono="<?= htmlspecialchars($h['icono']) ?>"
                                                        data-padre="<?= htmlspecialchars($p['nombre']) ?>"
                                                        data-observaciones="<?= htmlspecialchars($h['observaciones'] ?? '') ?>"
                                                        title="Ver código">
                                                        <i class="fas fa-code"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php
                                        endforeach;
                                    endif;
                                endforeach;

                                // 2) Si existen hijos cuyo padre ya no está (órfanos), mostrarlos al final
                                // 2) Hijos cuyo padre NO está en $padres (órfanos): mostrarlos al final
                                if (!empty($hijosPorPadre)) {
                                    foreach ($hijosPorPadre as $pid => $lst) {
                                        if (isset($padres[$pid])) continue; // su padre existe: ya se pintaron arriba
                                        foreach ($lst as $h) { ?>
                                            <tr>
                                                <td><?= htmlspecialchars($h['nombre']) ?></td>
                                                <td><?= htmlspecialchars($h['archivo']) ?></td>
                                                <td>
                                                    <?php if (!empty($h['icono'])): ?>
                                                        <i class="<?= htmlspecialchars($h['icono']) ?>"></i>
                                                        <?= htmlspecialchars($h['icono']) ?>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?= htmlspecialchars($h['padre'] ?? '(Sin padre)') ?></td>
                                                <td><?= htmlspecialchars($h['observaciones'] ?? '') ?></td>
                                                <td class="d-flex gap-2">
                                                    <a href="modulos-editar/<?= (int)$h['id'] ?>" class="btn btn-primary btn-sm" title="Editar">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <button type="button"
                                                        class="btn btn-danger btn-sm btn-eliminar"
                                                        data-id="<?= (int)$h['id'] ?>"
                                                        title="Eliminar">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                    <button type="button"
                                                        class="btn btn-info btn-sm btn-codigo"
                                                        data-nombre="<?= htmlspecialchars($h['nombre']) ?>"
                                                        data-archivo="<?= htmlspecialchars($h['archivo']) ?>"
                                                        data-icono="<?= htmlspecialchars($h['icono']) ?>"
                                                        data-padre="<?= htmlspecialchars($h['padre'] ?? '') ?>"
                                                        data-observaciones="<?= htmlspecialchars($h['observaciones'] ?? '') ?>"
                                                        title="Ver código">
                                                        <i class="fas fa-code"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                <?php }
                                    }
                                }

                                ?>
                            </tbody>
                        </table>

                        <!-- Modal simple (sin Bootstrap backdrop), como el original -->
                        <div id="codigoModal" class="modal" tabindex="-1" style="
                            display:none; position:fixed; top:10%; left:10%; width:80%;
                            max-height:80vh; overflow:auto;
                            background:#fff; padding:20px; border:1px solid #ccc;
                            box-shadow:0 0 10px rgba(0,0,0,.35); z-index:1050;">
                            <h5>Ejemplo de código SQL para insertar este módulo</h5>
                            <pre id="codigoSQL" style="background:#eee; padding:10px; overflow:auto;"></pre>
                            <h5>Ejemplo de manejo de permisos en PHP</h5>
                            <pre id="codigoPHP" style="background:#eee; padding:10px; overflow:auto;"></pre>
                            <div class="text-end">
                                <button class="btn btn-secondary btn-cerrar-modal">Cerrar</button>
                            </div>
                        </div>
                        <!-- /Modal simple -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
    $(function() {
        // ELIMINAR
        $('.table').on('click', '.btn-eliminar', function(e) {
            e.preventDefault();
            const id = $(this).data('id');
            const $row = $(this).closest('tr');

            if (!id) return;

            alertify.confirm('Eliminar módulo',
                '¿Confirmas eliminar este módulo?',
                function onOk() {
                    // Mostrar spinner global (tu regla)
                    document.getElementById('spinner').style.display = 'block';

                    $.ajax({
                            url: 'ajax/modulos/eliminar.php',
                            method: 'POST',
                            dataType: 'json',
                            data: {
                                id: id
                            }
                        })
                        .done(function(resp) {
                            if (resp && resp.ok) {
                                // Quita la fila o recarga si prefieres
                                $row.remove();
                                alertify.success('Módulo eliminado');
                            } else {
                                alertify.error(resp && resp.msg ? resp.msg : 'No se pudo eliminar');
                            }
                        })
                        .fail(function() {
                            alertify.error('Error de comunicación');
                        })
                        .always(function() {
                            document.getElementById('spinner').style.display = 'none';
                        });
                },
                function onCancel() {
                    // cancelado
                }
            ).set('labels', {
                ok: 'Sí, eliminar',
                cancel: 'Cancelar'
            });
        });
    });
</script>

<script>
    $(function() {
        // Abrir modal simple y cargar contenidos
        $('.btn-codigo').on('click', function(e) {
            e.preventDefault();

            const nombre = $(this).data('nombre') ?? '';
            const archivo = $(this).data('archivo') ?? '';
            const icono = $(this).data('icono') ?? '';
            const padre = $(this).data('padre') ?? '';
            const observ = $(this).data('observaciones') ?? '';

            const sql =
                `INSERT INTO modulos (nombre, archivo, icono, modulo_padre)
VALUES (
  '${String(nombre).replace(/'/g, "''")}',
  '${String(archivo).replace(/'/g, "''")}',
  '${String(icono).replace(/'/g, "''")}',
  ${padre ? "(SELECT id FROM modulos WHERE nombre = '" + String(padre).replace(/'/g, "''") + "')" : "NULL"}
);`;

            const phpEjemplo =
                `// Al inicio del archivo PHP:
if (!isset($_SESSION['permisos']['${archivo}']) || !$_SESSION['permisos']['${archivo}']['ver']) {
    die("No tienes permiso para ver este módulo.");
}

$perm = $_SESSION['permisos']['${archivo}'];

// Mostrar botón Crear solo si tiene permiso
if (!empty($perm['crear'])) {
    echo '<a href="${archivo}-altas" class="btn btn-primary mb-3">Nuevo ${nombre}</a>';
}

// Mostrar listado/contenido si puede ver
if (!empty($perm['ver'])) {
    // Código para mostrar contenido
}

// Botones Editar/Eliminar
if (!empty($perm['editar'])) {
    // Mostrar botón editar
}
if (!empty($perm['eliminar'])) {
    // Mostrar botón eliminar
}`;

            $('#codigoSQL').text(sql);
            $('#codigoPHP').text(phpEjemplo);
            $('#codigoModal').show();
        });

        // Cerrar modal simple
        $('#codigoModal .btn-cerrar-modal').on('click', function() {
            $('#codigoModal').hide();
        });
    });
</script>