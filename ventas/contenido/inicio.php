<?php 
$idVendedor = $_SESSION['id_vendedor'];
$rs = $clsConsulta->consultaGeneral("SELECT id FROM vendedores_clientes WHERE id_vendedor=$idVendedor");
$cantidadClientes=$clsConsulta->numrows;

$rs = $clsConsulta->consultaGeneral("SELECT id FROM cab_pedidos WHERE estatus ='pendiente' AND id_vendedor = $idVendedor");
$cantidadPedidos=$clsConsulta->numrows;

?>
<div class="container mt-5">

    <div class="row justify-content-center g-4"> <!-- Added gutter spacing -->

        <!-- Card Clientes -->

        <div class="col-12 col-md-6 col-lg-4">

            <div class="card h-100 border-0 shadow-sm hover-effect">

                <a href="clientes-vendedor" class="text-decoration-none">

                    <div class="card-body text-center p-4">

                        <div class="icon-container bg-primary-light mb-3 mx-auto">

                            <i class="fas fa-users fa-2x text-primary"></i>

                        </div>

                        <h3 class="h5 card-title text-dark mb-2">Clientes</h3>

                        <p class="text-muted small">Gestión de clientes y contactos</p>

                    </div>

                    <div class="card-footer bg-transparent border-0 pt-0">

                        <span class="badge bg-primary rounded-pill"><?= $cantidadClientes; ?> Asugnados</span>

                    </div>

                </a>

            </div>

        </div>



        <!-- Card Pedidos -->

        <div class="col-12 col-md-6 col-lg-4">

            <div class="card h-100 border-0 shadow-sm hover-effect">

                <a href="pedidos" class="text-decoration-none">

                    <div class="card-body text-center p-4">

                        <div class="icon-container bg-success-light mb-3 mx-auto">

                            <i class="fas fa-clipboard-list fa-2x text-success"></i>

                        </div>

                        <h3 class="h5 card-title text-dark mb-2">Pedidos</h3>

                        <p class="text-muted small">Consulta y seguimiento</p>

                    </div>

                    <div class="card-footer bg-transparent border-0 pt-0">

                        <span class="badge bg-success rounded-pill"><?= $cantidadPedidos; ?> Pendientes</span>

                    </div>

                </a>

            </div>

        </div>



        <!-- Card Nuevo Pedido -->

        <div class="col-12 col-md-6 col-lg-4">

            <div class="card h-100 border-0 shadow-sm hover-effect">

                <a href="pedidos-altas" class="text-decoration-none">

                    <div class="card-body text-center p-4">

                        <div class="icon-container bg-warning-light mb-3 mx-auto">

                            <i class="fas fa-plus-circle fa-2x text-warning"></i>

                        </div>

                        <h3 class="h5 card-title text-dark mb-2">Nuevo Pedido</h3>

                        <p class="text-muted small">Crear nuevo pedido</p>

                    </div>

                    <div class="card-footer bg-transparent border-0 pt-0">

                        <span class="badge bg-warning text-dark rounded-pill">Acción rápida</span>

                    </div>

                </a>

            </div>

        </div>

    </div>

</div>



<!-- Estilos personalizados -->

<style>

    .hover-effect {

        transition: all 0.3s ease;

        transform: translateY(0);

    }

    .hover-effect:hover {

        transform: translateY(-5px);

        box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important;

    }

    .icon-container {

        width: 60px;

        height: 60px;

        border-radius: 50%;

        display: flex;

        align-items: center;

        justify-content: center;

    }

    .bg-primary-light {

        background-color: rgba(13, 110, 253, 0.1);

    }

    .bg-success-light {

        background-color: rgba(25, 135, 84, 0.1);

    }

    .bg-warning-light {

        background-color: rgba(255, 193, 7, 0.1);

    }

</style>



