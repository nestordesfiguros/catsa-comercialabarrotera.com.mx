<?php
// admin/php/navbar.php

// Este archivo debe ser incluido desde index.php, que ya contiene session_start()
// Asegúrate de que $nav esté definido en index.php como: $nav = $cat ?? '';

?>

<nav class="navbar navbar-expand-lg bg-white shadow mb-3">
  <div class="container">
    <a class="navbar-brand" href="inicio">
      <img src="img/logo-inicio.png" alt="Volta" class="img-fluid" style="max-width: 150px">
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavAltMarkup" aria-controls="navbarNavAltMarkup" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarNavAltMarkup">
      <div class="navbar-nav">
        <?php
        $archivoActual = $nav ?? '';
        $idModuloActivo = null;
        $idModuloPadreActivo = null;

        // Detectar módulo activo (por archivo)
        foreach ($_SESSION['modulos'] as $id => $mod) {
          if ($mod['archivo'] === $archivoActual) {
            $idModuloActivo = $id;
            break;
          }
        }

        // Si el módulo tiene padre, resaltamos ese
        if ($idModuloActivo !== null) {
          $idModuloPadreActivo = $_SESSION['modulos'][$idModuloActivo]['modulo_padre'] ?? $idModuloActivo;
        }

        foreach ($_SESSION['modulos'] as $idModulo => $datos) {
          if (!isset($_SESSION['permisos'][$idModulo]['ver']) || $_SESSION['permisos'][$idModulo]['ver'] != 1) continue;

          // Solo mostrar módulos principales
          if (!empty($datos['modulo_padre'])) continue;

          $activo = ($idModulo == $idModuloPadreActivo) ? 'btn-secondary' : 'btn-light';

          $iconoHTML = '';
          if (!empty($datos['icono'])) {
            $iconoHTML = '<i class="fa-2x ' . $datos['icono'] . ' mt-2"></i>';
          }

          echo '
            <div class="text-center">
              <a class="btn ' . $activo . ' btn-square-sm" aria-current="page" href="' . $datos['archivo'] . '">
                  ' . $iconoHTML . '
                  <p style="font-size: 10px;">' . strtoupper($datos['nombre']) . '</p>
              </a>
            </div>';
        }
        ?>

        <div class="text-center">
          <a class="btn btn-light btn-square-sm" aria-current="page" href="salir">
            <i class="fa-2x fa-right-from-bracket fa-solid mt-2"></i>
            <p style="font-size: 10px;">Salir</p>
          </a>
        </div>

        <!-- Parte derecha del navbar -->
        <div class="text-center">
          <div class="row  d-none d-md-flex align-items-center gap-1">
            <div>
              <?php echo '<span>Bienvenido: <b style="font-size: 10px;">' . $_SESSION['nombre'] . '</b></span>'; ?>
            </div>
            <div>
              <?php if (isset($_SESSION['empresas_disponibles'], $_SESSION['razon_social']) && count($_SESSION['empresas_disponibles']) > 1): ?>
                <div class="d-flex justify-content-center">
                  <button
                    type="button"
                    class="btn btn-light border rounded-pill px-3 py-1 d-flex align-items-center gap-2 company-pill shadow-sm"
                    data-bs-toggle="modal"
                    data-bs-target="#modalEmpresas"
                    title="<?php echo htmlspecialchars($_SESSION['razon_social']); ?>"
                    aria-label="Empresa actual: <?php echo htmlspecialchars($_SESSION['razon_social']); ?>. Click para cambiar.">
                    <i class="fa fa-building"></i>
                    <span class="company-text text-truncate" style="font-size: 10px;">
                      <?php echo htmlspecialchars($_SESSION['razon_social']); ?>
                    </span>
                    <i class="fa fa-chevron-down small opacity-50"></i>
                  </button>
                </div>
              <?php endif; ?>
            </div>
          </div>
        </div>

        <!-- <div class="text-center pt-3">
          <a href="perfil-user">
            <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" fill="currentColor" class="bi bi-person-circle" viewBox="0 0 16 16">
              <path d="M11 6a3 3 0 1 1-6 0 3 3 0 0 1 6 0z" />
              <path fill-rule="evenodd" d="M0 8a8 8 0 1 1 16 0A8 8 0 0 1 0 8zm8-7a7 7 0 0 0-5.468 11.37C3.242 11.226 4.805 10 8 10s4.757 1.225 5.468 2.37A7 7 0 0 0 8 1z" />
            </svg>
          </a>
        </div> -->

      </div>
    </div>
</nav>

<!-- Bienvenida en móviles -->
<div class="d-block d-md-none text-center">
  <div class="row">
    <div>
      <?php echo '<small>Bienvenido: <b>' . $_SESSION['nombre'] . '</b></small>'; ?>
    </div>
  </div>
</div>