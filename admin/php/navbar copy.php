<?PHP
/* *************  verifica el menú según los permisos del usuario */
include 'php/listaactive.php';
?>

<nav class="shadow navbar navbar-expand-lg bg-white mb-3 ">
  <div class="container">
    <a class="navbar-brand" href="inicio">
      <img src="img/logo-inicio.png" alt="Catsa" class="img-fluid" style="max-width: 150px">
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavAltMarkup" aria-controls="navbarNavAltMarkup" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNavAltMarkup">
      <div class="navbar-nav">
        <?php
        if (isset($nav)) {
          switch ($_SESSION['rol']) {
            case 1:
              echo $clientes;
              echo $productos;
              echo $proveedores;
              echo $reportes;
              echo $utilerias;
              echo $cartaPorte;
              //  echo $facturas; 
              break;
            case 2:
              echo $entradasAlmacenista;
              break;
          }
        }

        ?>
        <div class="text-center">
          <a class=" btn btn btn-light btn-square-lg" aria-current="page" href="salir">
            <i class="fa-solid fa-right-from-bracket fa-lg"></i>
            <p><small>Salir</small></p>
          </a>
        </div>

      </div>
    </div>
    <div>
      <?php echo $_SESSION['nombre']; ?>
    </div>
    <!--div class="row pt-0 text-end">
      <button class="btn btn-secondary rounded-pill border border-secondary shadow">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-person-circle" viewBox="0 0 16 16">
          <path d="M11 6a3 3 0 1 1-6 0 3 3 0 0 1 6 0z" />
          <path fill-rule="evenodd" d="M0 8a8 8 0 1 1 16 0A8 8 0 0 1 0 8zm8-7a7 7 0 0 0-5.468 11.37C3.242 11.226 4.805 10 8 10s4.757 1.225 5.468 2.37A7 7 0 0 0 8 1z" />
        </svg> &nbsp; <?php // echo $_SESSION['nombre']; 
                      ?>
      </button>
      <div id="nombreUsuario" class="visually-hidden"><?php // echo $_SESSION['nombre']; 
                                                      ?> </div>
    </div-->
  </div>
</nav>