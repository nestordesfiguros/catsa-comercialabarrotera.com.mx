<?php
require_once '../admin/lib/clsConsultas.php';
$clsConsulta = new Consultas();
?>
<div class="container py-4">
    <h2><i class="fas fa-shopping-cart me-2"></i>Carrito de Compras</h2>
    <div id="cartItemsContainer">
        <!-- Los items del carrito se cargarán aquí via JavaScript -->
    </div>
    <div class="card mt-3">
        <div class="card-body">
            <div class="d-flex justify-content-between">
                <h5 class="mb-0">Total:</h5>
                <h5 class="mb-0" id="cartTotal">$0.00</h5>
            </div>
        </div>
    </div>
    <button class="btn btn-success btn-lg mt-3 w-100" id="checkoutBtn">
        <i class="fas fa-cash-register me-2"></i>Procesar Venta
    </button>
</div>

<script>
$(document).ready(function() {
    const CART_KEY = 'venta_carrito';
    let cart = JSON.parse(localStorage.getItem(CART_KEY)) || [];
    
    function loadCartItems() {
        if (cart.length === 0) {
            $('#cartItemsContainer').html(`
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>El carrito está vacío
                </div>
            `);
            $('#cartTotal').text('$0.00');
            return;
        }
        
        let html = '';
        let total = 0;
        
        cart.forEach((item, index) => {
            const itemTotal = item.price * item.quantity;
            total += itemTotal;
            
            html += `
                <div class="card mb-3 cart-item" data-id="${item.id}">
                    <div class="card-body">
                        <div class="d-flex">
                            <img src="../img/productos/${item.image}" width="80" height="80" 
                                 class="rounded me-3" style="object-fit: cover;">
                            <div class="flex-grow-1">
                                <h5 class="mb-1">${item.name}</h5>
                                <p class="mb-1 text-muted small">Código: ${item.code}</p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="input-group" style="width: 120px;">
                                        <button class="btn btn-outline-secondary btn-sm minus-btn" type="button">
                                            <i class="fas fa-minus"></i>
                                        </button>
                                        <input type="number" class="form-control form-control-sm text-center quantity-input" 
                                               value="${item.quantity}" min="1" max="${item.stock}">
                                        <button class="btn btn-outline-secondary btn-sm plus-btn" type="button">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    </div>
                                    <div>
                                        <span class="fw-bold">$${itemTotal.toFixed(2)}</span>
                                        <small class="text-muted d-block">($${item.price.toFixed(2)} c/u)</small>
                                    </div>
                                </div>
                            </div>
                            <button class="btn btn-link text-danger remove-item" data-index="${index}">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            `;
        });
        
        $('#cartItemsContainer').html(html);
        $('#cartTotal').text('$' + total.toFixed(2));
    }
    
    // Cargar items al iniciar
    loadCartItems();
    
    // Eventos para modificar cantidades
    $('#cartItemsContainer')
        .on('click', '.plus-btn', function() {
            const input = $(this).siblings('.quantity-input');
            const newValue = parseInt(input.val()) + 1;
            if (newValue <= parseInt(input.attr('max'))) {
                input.val(newValue).trigger('change');
            }
        })
        .on('click', '.minus-btn', function() {
            const input = $(this).siblings('.quantity-input');
            const newValue = parseInt(input.val()) - 1;
            if (newValue >= parseInt(input.attr('min'))) {
                input.val(newValue).trigger('change');
            }
        })
        .on('change', '.quantity-input', function() {
            const index = $(this).closest('.cart-item').index();
            const newQuantity = parseInt($(this).val());
            
            if (newQuantity > 0 && newQuantity <= cart[index].stock) {
                cart[index].quantity = newQuantity;
                localStorage.setItem(CART_KEY, JSON.stringify(cart));
                loadCartItems();
                alertify.success('Cantidad actualizada');
            } else {
                alertify.error('Cantidad inválida');
                $(this).val(cart[index].quantity);
            }
        })
        .on('click', '.remove-item', function() {
            const index = $(this).data('index');
            const productName = cart[index].name;
            
            alertify.confirm(
                'Eliminar producto',
                `¿Seguro que deseas eliminar ${productName} del carrito?`,
                function() {
                    cart.splice(index, 1);
                    localStorage.setItem(CART_KEY, JSON.stringify(cart));
                    loadCartItems();
                    alertify.success('Producto eliminado');
                    // Actualizar contador en otras páginas
                    if (window.opener) {
                        window.opener.postMessage({ cartUpdated: true }, '*');
                    }
                },
                function() {
                    alertify.error('Cancelado');
                }
            );
        });
    
    // Procesar venta
    $('#checkoutBtn').click(function() {
        if (cart.length === 0) {
            alertify.error('El carrito está vacío');
            return;
        }
        
        alertify.confirm(
            'Confirmar venta',
            '¿Desea procesar esta venta?',
            function() {
                // Aquí iría tu lógica AJAX para guardar la venta
                alertify.success('Venta procesada correctamente');
                localStorage.removeItem(CART_KEY);
                cart = [];
                loadCartItems();
            },
            function() {
                alertify.error('Venta cancelada');
            }
        );
    });
});
</script>