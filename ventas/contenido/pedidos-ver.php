<?php



// Inicializa $id_pedido a null por defecto

$id_pedido = null;



// Obtener el ID del pedido desde la sesión

if (isset($_SESSION['pedidoActivo'])) {

    $id_pedido = $_SESSION['pedidoActivo'];
}



// Obtener el cliente actual de la sesión (esto ya lo tienes)

if (isset($_SESSION['pedidoActivo'])) {

    $idCliente = $_SESSION['pedidoActivo'];
} else {

    $idCliente = $cat; // Cliente por defecto (aunque aquí ya deberías tener la sesión)

    $_SESSION['pedidoActivo'] = $idCliente;
}



if ($id_pedido === null) {

    // Redireccionar o mostrar mensaje si no hay pedido

    echo '<div class="alert alert-danger">No se ha seleccionado ningún pedido</div>';
} else {

    require_once '../admin/lib/clsConsultas.php';

    $clsConsulta = new Consultas();



    // Obtener información del cliente (esto ya lo tienes)

    $con = "SELECT razon_social FROM cat_clientes WHERE id = " . $idCliente;

    $rs = $clsConsulta->consultaGeneral($con);

    $clienteActual = isset($rs[1]['razon_social']) ? $rs[1]['razon_social'] : 'Cliente no seleccionado';





?>



    <style>
        .product-img {

            width: 80px;

            height: 80px;

            object-fit: cover;

            border-radius: 4px;

        }



        .quantity-control {

            width: 120px;

        }



        .sticky-summary {

            position: sticky;

            top: 20px;

        }

        .empty-cart {
            min-height: 300px;
        }

        .btn-quantity {
            width: 35px;
        }

        .order-item {
            transition: all 0.3s ease;
        }

        .order-item:hover {

            background-color: #f8f9fa;

        }
    </style>

    </head>



    <body>

        <!-- Navbar -->

        <nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4">

            <div class="container">

                <a class="navbar-brand" href="#">

                    <i class="fas fa-file-invoice me-2"></i> Cliente: <?= htmlspecialchars($clienteActual); ?>

                </a>

                <div class="d-flex align-items-center">

                    <a href="pedidos-nuevo/<?= $id_pedido; ?>" class="btn btn-outline-light me-2">

                        <i class="fas fa-arrow-left me-1"></i> Productos

                    </a>

                    <?php if (isset($_SESSION['pedidoActivo'])): ?>

                        <button id="btnCancelarPedido" class="btn btn-danger ms-2">

                            <i class="fas fa-times-circle me-1"></i> Cancelar

                        </button>

                    <?php endif; ?>

                </div>

            </div>

        </nav>



        <script>
            document.getElementById('btnCancelarPedido')?.addEventListener('click', function() {

                alertify.confirm(

                    'Cancelar Pedido',

                    '¿Estás seguro que deseas cancelar este pedido? Se perderá toda la información del carrito.',

                    function() {

                        // Limpiar el carrito antes de redireccionar

                        localStorage.removeItem('venta_carrito');

                        window.location.href = 'ajax/cancelar-pedido.php';

                    },

                    function() {
                        alertify.error('Cancelado');
                    }

                ).set('labels', {

                    ok: 'Sí, cancelar',

                    cancel: 'No'

                });

            });
        </script>



        <!-- Contenido Principal -->

        <div class="container mb-5">
            <div class="row">

                <!-- Lista de Productos -->
                <div class="col-lg-8">
                    <div class="card mb-4">
                        <div class="card-header bg-white d-flex justify-content-between align-items-center">
                            <h4 class="mb-0"><i class="fas fa-list me-2"></i>Productos en el Pedido</h4>
                            <button class="btn btn-sm btn-outline-danger" id="clearCartBtn">
                                <i class="fas fa-trash me-1"></i> Vaciar Todo
                            </button>
                        </div>

                        <div class="card-body p-0">
                            <div id="orderItemsContainer">
                                <!-- Mensaje temporal hasta que se cargue el carrito -->
                                <div class="text-center p-4">
                                    <div class="spinner-border text-primary"></div>
                                    <p class="mt-2">Cargando productos...</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>


                <!-- Resumen del Pedido -->

                <div class="col-lg-4">
                    <div class="card sticky-summary">
                        <div class="card-header bg-white">
                            <h4 class="mb-0"><i class="fas fa-receipt me-2"></i>Resumen del Pedido</h4>
                        </div>

                        <div class="card-body">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Subtotal:</span>
                                <span id="subtotalAmount">$0.00</span>
                            </div>

                            <!-- <div class="d-flex justify-content-between mb-2">
                                <span>IVA (16%):</span>
                                <span id="taxAmount">$0.00</span>
                            </div> -->

                            <hr>

                            <div class="d-flex justify-content-between fw-bold fs-5">
                                <span>Total:</span>
                                <span id="totalAmount">$0.00</span>
                            </div>

                            <button onclick="fnGuardarPedido(<?= $id_pedido; ?>)" class="btn btn-primary btn-lg w-100 mt-3">
                                <i class="fas fa-save me-2"></i> Guardar pedido
                            </button>

                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Spinner overlay personalizado -->
        <div id="savingOverlay" class="position-fixed top-0 start-0 w-100 h-100 d-none"
            style="background-color: rgba(0,0,0,0.5); z-index: 1050;">
            <div class="d-flex justify-content-center align-items-center h-100">
                <div class="spinner-border text-light" role="status">
                    <span class="visually-hidden">Guardando...</span>
                </div>
            </div>
        </div>


        <script>
            $(document).ready(function() {

                const CART_KEY = 'venta_carrito';

                let cart = JSON.parse(localStorage.getItem(CART_KEY)) || [];

                const idVendedor = <?= $_SESSION["id_vendedor"] ?? 0; ?>;



                // Función para validar y limpiar el carrito

                function validateCart() {

                    cart = cart.filter(item => {

                        return item &&

                            item.id &&

                            item.name &&

                            item.price !== null &&

                            !isNaN(item.price) &&

                            item.quantity &&

                            !isNaN(item.quantity);

                    });

                    saveCart();

                }



                // Inicializar la vista del carrito

                function initCartView() {

                    //  validateCart(); // Limpiar el carrito antes de mostrar

                    updateCartCounter();

                    renderCartItems();

                    calculateOrderTotals();

                }



                // Renderizar los items del carrito

                function renderCartItems() {

                    const container = $('#orderItemsContainer');



                    if (cart.length === 0) {

                        container.html(`

                <div class="empty-cart p-4 text-center">

                    <i class="fas fa-shopping-cart fa-4x text-muted mb-3"></i>

                    <h5 class="text-muted">No hay productos en el pedido</h5>

                    <a href="pedidos-nuevo/<?= $id_pedido; ?>" class="btn btn-primary mt-3">

                        <i class="fas fa-plus me-2"></i>Agregar Productos

                    </a>

                </div>

            `);

                        $('#clearCartBtn').hide();

                        return;

                    }



                    $('#clearCartBtn').show();



                    let html = '';

                    cart.forEach((item, index) => {

                        // Asegurarse de que el precio sea un número válido

                        const price = parseFloat(item.price) || 0;

                        const quantity = parseInt(item.quantity) || 1;

                        const itemTotal = price * quantity;

                        const stock = parseInt(item.stock) || 0;



                        html += `

                <div class="p-3 border-bottom order-item" data-index="${index}">

                    <div class="d-flex">

                        <img src="../img/productos/${item.image || 'default-product.png'}" 

                             class="product-img me-3" 

                             alt="${item.name || 'Producto'}"

                             onerror="this.src='../img/productos/default-product.png'">

                        <div class="flex-grow-1">

                            <div class="d-flex justify-content-between">

                                <h6 class="mb-1">${item.name || 'Producto sin nombre'}</h6>

                                <button class="btn btn-link text-danger p-0 remove-item-btn">

                                    <i class="fas fa-times"></i>

                                </button>

                            </div>

                            ${item.code ? `<p class="small text-muted mb-2">Código: ${item.code}</p>` : ''}

                            <div class="d-flex justify-content-between align-items-center">

                                <div class="input-group quantity-control">

                                    <button class="btn btn-outline-secondary btn-sm btn-quantity minus-btn" type="button">

                                        <i class="fas fa-minus"></i>

                                    </button>

                                    <input type="number" class="form-control form-control-sm text-center quantity-input" 

                                           value="${quantity}" min="1" max="${stock}">

                                    <button class="btn btn-outline-secondary btn-sm btn-quantity plus-btn" type="button">

                                        <i class="fas fa-plus"></i>

                                    </button>

                                </div>

                                <div class="text-end">

                                    <span class="fw-bold">$${itemTotal.toFixed(2)}</span>

                                    <small class="text-muted d-block">($${price.toFixed(2)} c/u)</small>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>

            `;

                    });



                    container.html(html);

                }



                // Calcular totales del pedido

                function calculateOrderTotals() {

                    const subtotal = cart.reduce((sum, item) => {

                        const price = parseFloat(item.price) || 0;

                        const quantity = parseInt(item.quantity) || 0;

                        return sum + (price * quantity);

                    }, 0);



                    const tax = subtotal * 0.16; // IVA 16%

                    const total = subtotal;



                    $('#subtotalAmount').text('$' + subtotal.toFixed(2));

                    $('#taxAmount').text('$' + tax.toFixed(2));

                    $('#totalAmount').text('$' + total.toFixed(2));

                }



                // Actualizar contador del carrito

                function updateCartCounter() {

                    const totalItems = cart.reduce((total, item) => {

                        const quantity = parseInt(item.quantity) || 0;

                        return total + quantity;

                    }, 0);

                    $('.cart-counter').text(totalItems);

                }

                // Guardar carrito en localStorage
                function saveCart() {
                    localStorage.setItem(CART_KEY, JSON.stringify(cart));
                    updateCartCounter();
                    calculateOrderTotals();
                }

                // Botón Guardar pedido: deshabilitar + mostrar overlay al iniciar, y revertir al completar
                window.fnGuardarPedido = function(idCliente) {
                    // ...validaciones previas...
                    $('#btnGuardarPedido').prop('disabled', true);
                    $('#savingOverlay').removeClass('d-none');

                    $.ajax({
                        type: "POST",
                        url: "ajax/guardar-pedido.php",
                        data: {
                            items: cart,
                            id_cliente: idCliente,
                            idVendedor: idVendedor
                        },
                        dataType: "json",
                        beforeSend: function() {
                            alertify.message('Guardando pedido...');
                        },
                        success: function(response) {
                            if (response.success) {
                                alertify.success('Pedido guardado correctamente');
                                localStorage.removeItem(CART_KEY);
                                window.location.href = "pedido-guardado";
                            } else {
                                alertify.error(response.message || 'Error al guardar el pedido');
                            }
                        },
                        error: function(xhr, status, error) {
                            alertify.error('Error en el servidor: ' + error);
                            console.error(xhr.responseText);
                        },
                        complete: function() {
                            // Rehabilitar botón y ocultar overlay
                            $('#btnGuardarPedido').prop('disabled', false);
                            $('#savingOverlay').addClass('d-none');
                        }
                    });
                };

                // Vaciar todo el carrito

                $('#clearCartBtn').click(function() {
                    alertify.confirm(
                        'Vaciar Carrito',
                        '¿Estás seguro de que deseas eliminar todos los productos del pedido?',
                        () => {
                            cart = [];
                            saveCart();
                            renderCartItems();
                            alertify.success('Carrito vaciado correctamente');
                        },
                        () => alertify.error('Acción cancelada')
                    );
                });



                // Eventos para modificar cantidades

                $(document)

                    .on('click', '.plus-btn', function() {

                        const input = $(this).siblings('.quantity-input');

                        const newValue = parseInt(input.val()) + 1;

                        const max = parseInt(input.attr('max')) || 999;

                        if (newValue <= max) {

                            input.val(newValue).trigger('change');

                        }

                    })

                    .on('click', '.minus-btn', function() {

                        const input = $(this).siblings('.quantity-input');

                        const newValue = parseInt(input.val()) - 1;

                        if (newValue >= 1) {

                            input.val(newValue).trigger('change');

                        }

                    })

                    .on('change', '.quantity-input', function() {

                        const index = $(this).closest('.order-item').data('index');

                        const newQuantity = parseInt($(this).val()) || 1;

                        const maxStock = parseInt(cart[index]?.stock) || 999;



                        if (newQuantity > 0 && newQuantity <= maxStock) {

                            cart[index].quantity = newQuantity;

                            saveCart();

                            alertify.success('Cantidad actualizada');

                        } else {

                            alertify.error('Cantidad inválida');

                            $(this).val(cart[index]?.quantity || 1);

                        }

                    })

                    .on('click', '.remove-item-btn', function() {

                        const index = $(this).closest('.order-item').data('index');

                        const productName = cart[index]?.name || 'Este producto';



                        alertify.confirm(

                            'Eliminar Producto',

                            `¿Seguro que deseas eliminar <strong>${productName}</strong> del pedido?`,

                            () => {

                                cart.splice(index, 1);

                                saveCart();

                                renderCartItems();

                                alertify.success('Producto eliminado');

                            },

                            () => alertify.error('Acción cancelada')

                        );

                    });



                // Inicializar vista del carrito

                initCartView();

            });
        </script>

    <?php } ?>