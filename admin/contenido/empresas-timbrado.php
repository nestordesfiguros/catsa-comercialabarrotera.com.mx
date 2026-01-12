<?php
// contenido/empresas-timbrado.php
@ini_set('display_errors', 0);

require_once __DIR__ . '/../lib/clsConsultas.php';
$cls = new Consultas();

/* ID de empresa DESDE EL ROUTER (multiempresa) */
$idEmpresa = 0;
if (isset($cat)) {
    $idEmpresa = $cat ? (int)$cat : 0;
}
if ($idEmpresa <= 0) {
    echo '<div class="alert alert-danger">Empresa no válida.</div>';
    return;
}

/* Carga datos de la empresa solo para mostrar UI (nombre/RFC) */
$emp = $cls->consultaGeneral("SELECT id, razon_social, rfc FROM cat_empresas WHERE id=" . $idEmpresa . " LIMIT 1");
$empresa = $emp[1] ?? null;
if (!$empresa) {
    echo '<div class="alert alert-danger">Empresa no encontrada.</div>';
    return;
}

$rutaDestino = "ajax/timbrado/csd/{$idEmpresa}/";
?>
<div class="ms-2 mb-2">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="inicio">Inicio</a></li>
            <li class="breadcrumb-item"><a href="utilerias">Utilerías</a></li>
            <li class="breadcrumb-item"><a href="empresas">Empresas</a></li>
            <li class="breadcrumb-item active" aria-current="page"> Timbrado (CSD)</li>
        </ol>
    </nav>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="alert alert-light d-flex align-items-center justify-content-between border">
            <div>
                <strong><i class="fa fa-building"></i> Empresa:</strong>
                <span id="lbl-empresa"><?php echo htmlspecialchars($empresa['razon_social']); ?></span>
                &nbsp; &nbsp;
                <strong>RFC:</strong>
                <span id="lbl-rfc"><?php echo htmlspecialchars($empresa['rfc']); ?></span>
            </div>
            <div class="text-muted small">
                <i class="fa fa-folder-open"></i>
                Los archivos se guardarán en:
                <code><?php echo $rutaDestino; ?></code>
            </div>
        </div>

        <div class="row">
            <!-- Formulario -->
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">Datos para timbrado (CSD) – CER + KEY</div>
                    <div class="card-body">
                        <form id="formCSD" enctype="multipart/form-data" data-empresa-id="<?php echo (int)$idEmpresa; ?>">
                            <input type="hidden" name="id_empresa" id="id_empresa" value="<?php echo (int)$idEmpresa; ?>" />

                            <div class="mb-3">
                                <label class="form-label">Archivo CER</label>
                                <!-- Usar extensión, no MIME -->
                                <input type="file" class="form-control" name="cer" id="cer" />
                                <div class="form-text">Selecciona el certificado (.cer)</div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Archivo KEY</label>
                                <input type="file" class="form-control" name="key" id="key" />
                                <div class="form-text">Selecciona la llave privada (.key)</div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Contraseña del CSD</label>
                                <div class="input-group">
                                    <input type="password" class="form-control" name="pwd" id="pwd" placeholder="Contraseña del CSD" />
                                    <button type="button" class="btn btn-outline-secondary" id="btnTogglePwd" title="Mostrar/Ocultar">
                                        <i class="fa-regular fa-eye" id="icoEye"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-primary" id="btnProbar">
                                    <i class="fa fa-flask"></i> Probar CSD
                                </button>

                                <button type="button" class="btn btn-success" id="btnInstalar">
                                    <i class="fa fa-download"></i> Instalar CSD
                                </button>
                            </div>

                            <div class="mt-3" id="csdMsg"></div>
                        </form>

                        <p class="text-muted mt-3 mb-0 small">
                            En DEV puedes usar cualquier combinación válida de CER/KEY. En producción, usa tu CSD real.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Estado -->
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">Estado actual</div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>CER:</div>
                            <div id="estadoCER" class="text-muted">—</div>
                        </div>
                        <hr class="my-2" />
                        <div class="d-flex justify-content-between">
                            <div>KEY:</div>
                            <div id="estadoKEY" class="text-muted">—</div>
                        </div>
                        <hr class="my-2" />
                        <div class="d-flex justify-content-between">
                            <div>Activo:</div>
                            <div id="estadoActivo" class="text-muted">—</div>
                        </div>
                        <small class="text-muted d-block mt-3">Al instalar se activará el CSD para esta empresa y se desactivarán CSDs previos.</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- js del módulo -->
<script src="js/empresas-timbrado.js?v=3"></script>