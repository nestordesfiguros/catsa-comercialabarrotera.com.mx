<nav class="navbar navbar-expand-lg navbar-ligth bg-default shadow-sm">

  <div class="container-fluid">

    <!-- Logo y nombre de la empresa -->
    <a class="navbar-brand d-flex align-items-center" href="inicio">
      <img src="img/logo-inicio.png" alt="Distribuidora del Bajío" height="40" class="d-inline-block align-top me-2">
      <!-- <span class="d-none d-sm-inline">Distribuidora del Bajío</span> -->
    </a>


    <!-- ********************  -->
    <!-- Botón Carrito -->
    <!-- ********************  -->

    <?php

    $urlPedidos = 'inicio';

    if (isset($_SESSION['pedidoActivo'])) {
      $urlPedidos = 'pedidos-ver/' . $_SESSION['pedidoActivo'];
    } 
    ?>

    <a href="<?= $urlPedidos; ?>" class="btn btn-primary position-relative">
      <i class="fas fa-shopping-cart"></i>
      <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger cart-counter">
        0
      </span>
    </a>

    <a><?php 
    //  var_dump($_SESSION);
    ?></a>

    <a href="salir" class="brn btn-primary position-relative"> <i class="fas fa-sign-out-alt fa-2x me-2"></i></a>

    <!-- Menú principal -->

  </div>

</nav>



<!-- Script para confirmar salida -->

<!-- <script>

document.getElementById('btnSalir').addEventListener('click', function(e) {

  e.preventDefault();

  alertify.confirm(

    'Cerrar Sesión', 

    '¿Estás seguro que deseas salir del sistema?',

    function() {

      window.location.href = 'logout';

    },

    function() {

      alertify.error('Cancelado');

    }

  ).set('labels', {ok:'Sí, Salir', cancel:'Cancelar'});

});

</script> -->