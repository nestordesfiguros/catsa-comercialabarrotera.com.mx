<?php
$con = "SELECT
    cab_pedidos.*
    , cat_clientes.razon_social
FROM
    cab_pedidos
    INNER JOIN cat_clientes 
        ON (cab_pedidos.id_cliente = cat_clientes.id) 
WHERE cab_pedidos.id=" . $cat;
$rs = $clsConsulta->consultaGeneral($con);
$idPedido = $rs[1]['num_pedido'];
$Cliente = $rs[1]['razon_social'];
$estatus = $rs[1]['estatus'];
$fecha = $rs[1]['fecha'];
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Detalle del Pedido #<?= $idPedido; ?></h5>
                    <a href="pedidos" class="btn btn-outline-light btn-sm">
                        <i class="fas fa-arrow-left me-1"></i> Volver a pedidos
                    </a>
                </div>
                <div class="card-body">

                    <!-- Información general del pedido -->
                    <div class="mb-4">
                        <h6>Información del Pedido</h6>
                        <div class="row">
                            <div class="col-md-4">
                                <p><strong>Fecha:</strong> <?= $fecha; ?></p>
                            </div>
                            <div class="col-md-4">
                                <p><strong>Cliente:</strong> <?= $Cliente; ?> </p>
                            </div>
                            <div class="col-md-4">
                                <p><strong>Estado:</strong> <span class="badge bg-success"><?= $estatus; ?></span></p>
                            </div>
                        </div>
                    </div>

                    <!-- Tabla de productos del pedido -->
                    <div class="table-responsive">
                        <table class="table table-striped table-hover align-middle">
                            <thead class="table-light">
                                <?php
                                $conProd = "SELECT
                                    mov_pedidos.*
                                    , cat_productos.clave
                                    , cat_productos.nombre
                                    , cat_productos.descripcion
                                FROM
                                    mov_pedidos
                                    INNER JOIN cat_productos 
                                        ON (mov_pedidos.id_producto = cat_productos.id_producto)
                                        WHERE mov_pedidos.id_pedido =".$cat;
                                        $rs=$clsConsulta->consultaGeneral($conProd);
                                ?>
                                <tr>
                                    <th>Producto</th>
                                    <th>Cant.</th>
                                    <th>Precio unitario</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $total=0;
                                foreach($rs as $v=>$val){
                                    $producto=$val['nombre'];
                                    $cantidad=$val['cantidad'];
                                    $precio=$val['precio'];
                                    $subtotal= $cantidad*$precio;
                                    $total=$total+$subtotal;
                                    echo '<tr>
                                        <td>'.$producto.'</td>
                                        <td class="text-end">'.$cantidad.'</td>
                                        <td class="text-end">$'.number_format($precio,'2','.',',').'</td>
                                        <td class="text-end">$'.number_format($subtotal,'2','.',',').'</td>
                                    </tr>';
                                }
                                ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="3" class="text-end"><strong>Total:</strong></td>
                                    <td><strong>$<?= number_format($total,'2','.',',')?></strong></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>