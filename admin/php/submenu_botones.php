<?php
// php/submenu_botones.php
// Requiere: lib/permisosHijos.php y $_SESSION['modulos'], $_SESSION['permisos']
// Parámetros opcionales (defínelos ANTES de incluir este archivo):
//   $__archivoActual   (string)  Archivo base actual (p.ej. $nav)
//   $__idPadreForzado  (int)     Si quieres forzar un padre específico
//   $__layout          (string)  'franja' (debajo del navbar) | 'grid' (dentro de la vista)
//   $__btnSize         (string)  'btn-square-sm' | 'btn-square-xl'
//   $__bgWrapperClass  (string)  Clases para wrapper (ej. "bg-light border-bottom")
//   $__btnColor        (string)  Clase de color base para botones inactivos (ej. 'btn-light')

// var_dump($_SESSION['modulos']);
// echo '<br>';
// var_dump($_SESSION['permisos']);
if (!class_exists('permisosHijos')) require_once __DIR__ . '/../lib/clsPermisosHijos.php';

$__archivoActual  = $__archivoActual  ?? ($nav ?? '');
$__idPadreForzado = $__idPadreForzado ?? null;
$__layout         = $__layout         ?? 'franja';      // 'franja' o 'grid'
$__btnSize        = $__btnSize        ?? 'btn-square-sm';
$__bgWrapperClass = $__bgWrapperClass ?? 'bg-light border-bottom';
$__btnColor       = $__btnColor       ?? 'btn-light';

// 1) Determinar padre activo
$idActivo = permisosHijos::detectarModuloActivoPorArchivo((string)$__archivoActual);
$idPadre  = $__idPadreForzado ?: permisosHijos::obtenerPadre($idActivo);
if (!$idPadre) return;

//echo 'prueba padre: ' . $idPadre . '<br>';

// 2) Obtener hijos visibles
$hijos = permisosHijos::obtenerHijosVisibles($idPadre);
if (empty($hijos)) return;

// 3) Render según layout
if ($__layout === 'franja'): ?>
    <div class="<?php echo htmlspecialchars($__bgWrapperClass); ?>">
        <div class="container py-2">
            <div class="d-flex gap-2 flex-wrap">
                <?php foreach ($hijos as $idHijo):
                    $h = $_SESSION['modulos'][$idHijo];
                    $activo = (!empty($h['archivo']) && $h['archivo'] === $__archivoActual) ? 'btn-secondary' : $__btnColor;
                    $icono  = !empty($h['icono']) ? '<i class="fa-2x ' . $h['icono'] . ' mt-2"></i>' : '';
                ?>
                    <div class="text-center">
                        <a class="btn <?php echo $activo, ' ', $__btnSize; ?>" href="<?php echo htmlspecialchars($h['archivo'] ?: '#'); ?>">
                            <?php echo $icono; ?>
                            <p style="font-size:10px;"><?php echo strtoupper(htmlspecialchars($h['nombre'])); ?></p>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

<?php else: // 'grid' dentro de una card/vista 
?>
    <div class="row justify-content-center">
        <?php foreach ($hijos as $idHijo):
            $h = $_SESSION['modulos'][$idHijo];
            $icono = !empty($h['icono']) ? '<i class=" ' . $h['icono'] . ' fa-2x"></i>' : '<i class="fa-2x fa-circle"></i>';
        ?>
            <div class="col-lg-2 col-sm-3 col-6 mt-2 pe-1">
                <a class="nav-link" href="<?php echo htmlspecialchars($h['archivo'] ?: '#'); ?>">
                    <button type="button" class="btn btn-secondary btn-square-xl w-100">
                        <?php echo $icono; ?>
                        <p class="f-small mt-2"><?php echo htmlspecialchars($h['nombre']); ?></p>
                    </button>
                </a>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>