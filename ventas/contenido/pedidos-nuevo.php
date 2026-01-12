<?php
// pedidos-nuevo.php
require_once '../admin/lib/clsConsultas.php';

$clsConsulta = new Consultas();



// Obtener el cliente actual de la sesión

if (isset($_SESSION['pedidoActivo'])) {

    $idCliente = $_SESSION['pedidoActivo'];
} else {

    $idCliente = $cat; // Cliente por defecto

    $_SESSION['pedidoActivo'] = $idCliente;
}



// Consulta para obtener el nombre del cliente

$con = "SELECT razon_social FROM cat_clientes WHERE id = " . $idCliente;

$rs = $clsConsulta->consultaGeneral($con);

$clienteActual = isset($rs[1]['razon_social']) ? $rs[1]['razon_social'] : 'Cliente no seleccionado';

?>



<style>
    .search-container {

        position: sticky;

        top: 0;

        z-index: 1020;

        background: white;

        padding: 15px 0;

        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);

    }



    .product-img-container {

        height: 180px;

        display: flex;

        align-items: center;

        justify-content: center;

        background: #f8f9fa;

        padding: 15px;

    }



    .product-img {

        max-height: 100%;

        max-width: 100%;

        object-fit: contain;

    }



    .card {

        transition: all 0.3s ease;

        border-radius: 8px !important;

        overflow: hidden;

        height: 100%;

    }



    .card:hover {

        transform: translateY(-5px);

        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);

    }



    .card-title {

        font-size: 1rem;

        font-weight: 600;

        display: -webkit-box;

        -webkit-line-clamp: 2;

        -webkit-box-orient: vertical;

        overflow: hidden;

        text-overflow: ellipsis;

        min-height: 3rem;

        margin-bottom: 0.5rem;

    }



    .card-text {

        font-size: 0.85rem;

        display: -webkit-box;

        -webkit-line-clamp: 2;

        -webkit-box-orient: vertical;

        overflow: hidden;

        text-overflow: ellipsis;

    }



    .cart-counter {

        font-size: 0.75rem;

    }
</style>



<!-- Barra de búsqueda -->

<div class="search-container">
    <div class="container">
        <div class="input-group input-group-lg">
            <span class="input-group-text bg-primary text-white">
                <i class="fas fa-search"></i>
            </span>
            <input type="text" class="form-control" id="quickSearch" placeholder="Buscar productos...">
            <button class="btn btn-outline-secondary" type="button" id="scanBarcode">
                <i class="fas fa-barcode"></i>
            </button>
            <!-- <button class="btn btn-primary position-relative ms-2">
                    <i class="fas fa-shopping-cart"></i>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger cart-counter">
                        0
                    </span>
                </button> -->
        </div>
    </div>
</div>

<!-- Contenido principal -->

<div class="container-fluid px-3 py-4">
    <!-- Título del cliente -->
    <h5 class="mb-4">Pedido para: <?php echo htmlspecialchars($clienteActual); ?></h5>

    <!-- Resultados de búsqueda -->
    <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-3 d-none" id="searchResults"></div>

    <!-- Productos frecuentes -->
    <hr>
    <h3>Productos Frecuentes</h3>
    <div class="row mb-5">

        <div class="col-12">
            <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-3" id="frequentProducts">
                <?php
                $conFrecuentes = "
                    SELECT
                    p.*,
                    GREATEST(
                        COALESCE(inv_sum.total, p.cantidad_disponible) - COALESCE(res_sum.reservado, 0),
                        0
                    ) AS cantidad_total
                    FROM cat_productos p
                    LEFT JOIN (
                        SELECT id_producto, SUM(CASE WHEN cantidad > 0 THEN cantidad ELSE 0 END) AS total
                        FROM inventarios
                        GROUP BY id_producto
                    ) AS inv_sum ON inv_sum.id_producto = p.id_producto
                    LEFT JOIN (
                        SELECT id_producto, SUM(cantidad) AS reservado
                        FROM inventarios_reservas
                        WHERE estatus = 'reservado'
                        GROUP BY id_producto
                    ) AS res_sum ON res_sum.id_producto = p.id_producto
                    WHERE
                    p.estado = 'activo'
                    AND p.precio_sugerido > 0
                    AND p.precio_venta > 0
                    AND GREATEST(
                            COALESCE(inv_sum.total, p.cantidad_disponible) - COALESCE(res_sum.reservado, 0),
                            0
                        ) > 0
                    ORDER BY p.nombre ASC
                    ";


                $rsFrecuentes = $clsConsulta->consultaGeneral($conFrecuentes);

                if ($clsConsulta->numrows > 0 && is_array($rsFrecuentes)) {

                    foreach ($rsFrecuentes as $producto):
                        $idProd        = intval($producto['id_producto']);
                        $nombre        = htmlspecialchars($producto['nombre'] ?? '');
                        $presentacion  = htmlspecialchars($producto['presentacion'] ?? '');
                        $clave         = htmlspecialchars($producto['clave'] ?? '');
                        $categoria     = htmlspecialchars($producto['nombre_categoria'] ?? '');
                        $imagen        = htmlspecialchars($producto['imagen'] ?? 'no_img.png');
                        $cantidad      = $producto['cantidad_total'] ?? 0;
                        $tieneOferta   = false;

                        // Verificar si tiene oferta activa
                        $sqlOferta = "SELECT 1 FROM mov_ofertas_productos WHERE id_producto = $idProd AND valor_oferta > 0 LIMIT 1";
                        $rOferta   = $clsConsulta->consultaGeneral($sqlOferta);
                        if ($clsConsulta->numrows > 0) {
                            $tieneOferta = true;
                        }
                ?>
                        <div class="col">
                            <div class="card h-100 add-to-cart position-relative"
                                data-id="<?= $idProd ?>"
                                data-name="<?= $nombre ?>"
                                data-code="<?= $clave ?>"
                                data-price="<?= $producto['precio_venta'] ?>"
                                data-stock="<?= $cantidad ?>"
                                data-image="<?= $imagen ?>"
                                data-minprice="<?= $producto['precio_minimo_venta'] ?? 0 ?>"
                                data-preciosugerido="<?= $producto['precio_sugerido'] ?? 0 ?>"
                                data-preciomaximo="<?= $producto['precio_maximo_venta'] ?? 0 ?>">

                                <?php if ($tieneOferta): ?>
                                    <span class="badge bg-danger position-absolute top-0 start-0 m-2">
                                        <i class="fas fa-tags me-1"></i> Oferta
                                    </span>
                                <?php endif; ?>

                                <div class="product-img-container">
                                    <img src="../img/productos/<?= $imagen ?>"
                                        class="card-img-top product-img"
                                        alt="<?= $nombre ?>"
                                        onerror="this.src='../img/productos/no_img.png'">
                                </div>

                                <div class="card-body d-flex flex-column">
                                    <h5 class="card-title"><?= $nombre ?></h5>
                                    <?php if (!empty($presentacion)): ?>
                                        <p class="card-text text-muted small flex-grow-1"><?= $presentacion ?></p>
                                    <?php endif; ?>
                                    <div class="d-flex justify-content-between align-items-center mt-auto">
                                        <span class="badge <?= $cantidad > 0 ? 'bg-success' : 'bg-danger' ?>">
                                            <?= $cantidad > 0 ? 'Disponible' : 'Agotado' ?>: <?= $cantidad ?>
                                        </span>
                                        <span class="h5 text-success mb-0">
                                            <?php
                                            $precioOriginal = floatval($producto['precio_venta'] ?? 0);
                                            $precioOferta = null;

                                            // Verificar si tiene oferta activa
                                            $sqlOferta = "SELECT valor_oferta FROM mov_ofertas_productos WHERE id_producto = $idProd AND valor_oferta > 0 LIMIT 1";
                                            $rOferta   = $clsConsulta->consultaGeneral($sqlOferta);
                                            if ($clsConsulta->numrows > 0) {
                                                $precioOferta = floatval($rOferta[1]['valor_oferta']);
                                            }
                                            ?>

                                            <?php if ($precioOferta !== null): ?>
                                                <div class="text-end">
                                                    <div class="text-muted text-decoration-line-through small">
                                                        $<?= number_format($precioOriginal, 2) ?>
                                                    </div>
                                                    <div class="h5 text-success mb-0">
                                                        $<?= number_format($precioOferta, 2) ?>
                                                    </div>
                                                </div>
                                            <?php else: ?>
                                                <span class="h5 text-success mb-0">
                                                    $<?= number_format($precioOriginal, 2) ?>
                                                </span>
                                            <?php endif; ?>

                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                <?php endforeach;
                } ?>

            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {

        console.log('jQuery listo:', !!window.jQuery, $.fn.jquery);

        // Configuración inicial de Alertify
        alertify.defaults.theme.ok = "btn btn-primary";
        alertify.defaults.theme.cancel = "btn btn-danger";
        alertify.defaults.glossary.title = 'Carrito';
        alertify.defaults.notifier.position = 'top-right';

        // Variables globales
        const CART_KEY = 'venta_carrito';
        let cart = [];
        // Inicializar el carrito

        function initCart() {
            const savedCart = localStorage.getItem(CART_KEY);
            cart = savedCart ? JSON.parse(savedCart) : [];
            updateCartCounter();
        }

        // Actualizar contador del carrito
        function updateCartCounter() {
            const totalItems = cart.reduce((total, item) => total + item.quantity, 0);
            $('.cart-counter').text(totalItems);
        }

        // Mostrar modal de cantidad
        function showQuantityModal(product, callback) {
            const modalHtml = `
                <div class="modal fade" id="quantityModal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content border-0">
                            <div class="modal-header bg-white">
                                <h5 class="modal-title">
                                    <strong>${product.code}</strong> - ${product.name}
                                </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                            </div>
                            <div class="modal-body text-center">

                                <img src="../img/productos/${product.image}" class="img-fluid mb-3" style="max-height: 150px;" onerror="this.src='../img/productos/no_img.png'">

                                <div class="row mb-3">
                                    <div class="col-4">
                                        <small class="text-muted">P. Sugerido</small><br>
                                        <span class="fw-bold">$${parseFloat(product.precioSugerido).toFixed(2)}</span>
                                    </div>
                                    <div class="col-4">
                                        <small class="text-muted">P. Mínimo</small><br>
                                        <span class="fw-bold text-danger">$${parseFloat(product.minPrice).toFixed(2)}</span>
                                    </div>
                                    <div class="col-4">
                                        <small class="text-muted">P. Máximo</small><br>
                                        <span class="fw-bold text-secondary">$${parseFloat(product.precioMaximo).toFixed(2)}</span>
                                    </div>
                                </div>

                                <!-- Aquí se insertará la oferta si aplica -->
                                <div id="bloqueOferta"></div>

                                <div class="mb-3 text-start">
                                    <label class="form-label">P. Vendedor</label>
                                    <input type="number" step="0.01" min="0" id="inputPrecioVenta" class="form-control text-end" value="${product.price.toFixed(2)}">
                                    <div id="avisoPrecioMinimo" class="form-text text-danger d-none">El precio no puede ser menor al mínimo</div>
                                </div>

                                <div class="row text-start mb-3">
                                    <div class="col-6">
                                        <label class="form-label">Cantidad</label>
                                        <input type="number" id="productQuantity" class="form-control text-end" min="1" max="${product.stock}" value="1">
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label">Importe</label>
                                        <input type="text" id="importeTotal" class="form-control text-end" value="$${product.price.toFixed(2)}" readonly>
                                    </div>
                                </div>

                                <p class="text-muted mt-3 mb-1">Unidade(s) Disponible(s): <strong>${product.stock}</strong></p>

                            </div>
                            <div class="modal-footer bg-white">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                <button type="button" class="btn btn-primary" id="confirmAdd">Agregar al carrito</button>
                            </div>
                        </div>
                    </div>
                </div>
                `;

            $('body').append(modalHtml);
            const modal = new bootstrap.Modal(document.getElementById('quantityModal'));
            modal.show();
            console.log('id producto: ' + product.id);
            // Verificar si tiene oferta activa
            $.ajax({
                url: 'ajax/verificar-oferta.php',
                method: 'POST',
                data: {
                    id_producto: product.id
                },
                dataType: 'json',
                success: function(resp) {
                    console.log("Verificando oferta para ID:", product.id);
                    console.log("Respuesta de oferta:", resp);

                    if (resp.oferta) {
                        const oferta = resp.oferta;
                        const precioOferta = parseFloat(oferta.valor_oferta).toFixed(2);
                        const cantidadMinima = oferta.cantidad_minima || 1;
                        const textoOferta = `
                    <div class="alert alert-success text-start mt-2">
                        <i class="fas fa-tags me-2"></i>
                        <strong>Oferta:</strong> Precio especial <strong>$${precioOferta}</strong> al comprar mínimo ${cantidadMinima} unidad(es)
                    </div>
                `;
                        $('#bloqueOferta').html(textoOferta);
                        $('#inputPrecioVenta').val(precioOferta);
                    }
                }
            });

            // Eventos de validación y cálculo
            $('#inputPrecioVenta, #productQuantity').on('input', function() {
                const precio = parseFloat($('#inputPrecioVenta').val()) || 0;
                const cantidad = parseInt($('#productQuantity').val()) || 1;
                const precioMin = parseFloat(product.minPrice);

                if (precio < precioMin) {
                    $('#avisoPrecioMinimo').removeClass('d-none');
                } else {
                    $('#avisoPrecioMinimo').addClass('d-none');
                }

                const importe = (precio * cantidad).toFixed(2);
                $('#importeTotal').val(`$${importe}`);
            });

            $('#confirmAdd').click(function() {
                const cantidad = parseInt($('#productQuantity').val()) || 1;
                const precio = parseFloat($('#inputPrecioVenta').val()) || 0;
                const precioMin = parseFloat(product.minPrice);

                if (precio < precioMin) {
                    $('#avisoPrecioMinimo').removeClass('d-none');
                    $('#inputPrecioVenta').focus();
                    return;
                }
                if (cantidad <= 0 || cantidad > product.stock) {
                    alertify.error('Cantidad inválida o fuera de stock');
                    return;
                }

                // RESERVAR antes de agregar al carrito
                var sp = document.getElementById('spinner');
                if (sp) sp.style.display = 'block';
                $.ajax({
                    url: 'ajax/reservar-stock.php',
                    method: 'POST',
                    dataType: 'json',
                    data: {
                        id_producto: product.id,
                        cantidad: cantidad
                    },
                    success: function(resp) {
                        if (!resp || !resp.ok) {
                            alertify.error(resp?.msg || 'No se pudo reservar');
                            return;
                        }

                        // Adjunta referencias de reserva al item
                        const reservasRefs = resp.reservas || [];
                        const ok = addToCart(product, cantidad, precio, reservasRefs);
                        if (ok) {
                            alertify.success(`
                    <div class="d-flex align-items-center">
                        <img src="../img/productos/${product.image}" class="me-3" width="40" height="40" style="object-fit: cover; border-radius: 4px;">
                        <div>
                            <strong>${product.name}</strong> (${cantidad} ${cantidad === 1 ? 'unidad' : 'unidades'}) agregado<br>
                            <small>Total: $${(precio * cantidad).toFixed(2)}</small>
                        </div>
                    </div>
                `);
                        }
                    },
                    error: function() {
                        alertify.error('Error de reserva');
                    },
                    complete: function() {
                        var sp = document.getElementById('spinner');
                        if (sp) sp.style.display = 'none';
                    }
                });

                // cerrar modal
                const modalEl = document.getElementById('quantityModal');
                const modal = bootstrap.Modal.getInstance(modalEl);
                modal.hide();
                $('#quantityModal').remove();
            });


            $('#quantityModal').on('hidden.bs.modal', function() {
                $(this).remove();
            });
        }


        // Agregar producto al carrito
        function addToCart(product, quantity = 1, precioPersonalizado = null, reservasRefs = []) {
            const precioFinal = (precioPersonalizado !== null) ? precioPersonalizado : product.price;
            const existingItem = cart.find(item => item.id === product.id);

            if (existingItem) {
                // fusiona reservas
                existingItem.quantity += quantity;
                existingItem.price = precioFinal;
                existingItem.reservas = (existingItem.reservas || []).concat(reservasRefs);
            } else {
                cart.push({
                    id: product.id,
                    code: product.code,
                    name: product.name,
                    price: precioFinal,
                    image: product.image,
                    stock: product.stock, // visual
                    quantity: quantity,
                    reservas: reservasRefs // [{id_reserva, id_almacen, cantidad}]
                });
            }
            localStorage.setItem(CART_KEY, JSON.stringify(cart));
            updateCartCounter();
            return true;
        }


        function mostrarOpcionesOferta(ofertas, product, quantity) {
            // Construir HTML para modal de ofertas
            let html = `<div class="modal fade" id="modalOfertas">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Ofertas disponibles</h5>
                        </div>
                        <div class="modal-body">`;

            ofertas.forEach(oferta => {
                html += `<div class="oferta-item mb-3">
                    <h6>${oferta.nombre_oferta}</h6>
                    <p>${oferta.descripcion}</p>
                    ${calcularDescuentoHTML(oferta, product.price)}
                    <button class="btn btn-sm btn-primary" 
                            onclick="aplicarOferta(${JSON.stringify(product)}, ${quantity}, ${JSON.stringify(oferta)})">
                        Aplicar oferta
                    </button>
                </div>`;
            });

            html += `</div></div></div>`;

            $('body').append(html);
            $('#modalOfertas').modal('show');
        }

        function calcularDescuentoHTML(oferta, precioNormal) {
            switch (oferta.tipo_oferta) {
                case 'porcentaje':
                    const descuento = precioNormal * (oferta.valor_oferta / 100);
                    return `<p>${oferta.valor_oferta}% de descuento: $${(precioNormal - descuento).toFixed(2)} (antes $${precioNormal.toFixed(2)})</p>`;
                case 'monto_fijo':
                    return `<p>Precio especial: $${oferta.valor_oferta} (antes $${precioNormal.toFixed(2)})</p>`;
                case 'cantidad':
                    return `<p>Lleva ${oferta.cantidad_minima} al precio de $${oferta.valor_oferta} c/u</p>`;
                case 'combo':
                    return `<p>Lleva este producto + ${oferta.cantidad_bonus} del producto bonus</p>`;
            }
        }



        // Evento para agregar productos al carrito

        $(document).on('click', '.add-to-cart', function(e) {
            e.preventDefault();
            const productCard = $(this).closest('.card');
            const product = {
                id: productCard.data('id'),
                code: productCard.data('code'),
                name: productCard.data('name'),
                price: parseFloat(productCard.data('price')),
                stock: parseInt(productCard.data('stock')),
                image: productCard.data('image') || 'no_img.png',
                minPrice: parseFloat(productCard.data('minprice')) || 0,
                precioSugerido: parseFloat(productCard.data('preciosugerido')) || 0,
                precioMaximo: parseFloat(productCard.data('preciomaximo')) || 0,
            };

            if (product.stock <= 0) {
                alertify.error(`<i class="fas fa-exclamation-circle me-2"></i>${product.name} sin stock disponible`);
                return;
            }

            if (product.stock === 1) {
                if (addToCart(product, 1)) {
                    alertify.success(`
                            <div class="d-flex align-items-center">
                                <img src="../img/productos/${product.image}" class="me-3" width="40" height="40" style="object-fit: cover; border-radius: 4px;">
                                <div>
                                    <strong>${product.name}</strong> agregado<br>
                                    <small>$${product.price.toFixed(2)}</small>
                                </div>
                            </div>
                        `);
                }
            } else {
                showQuantityModal(product, function(quantity, precio) {
                    if (addToCart(product, quantity, precio)) {
                        alertify.success(`
                            <div class="d-flex align-items-center">
                                <img src="../img/productos/${product.image}" class="me-3" width="40" height="40" style="object-fit: cover; border-radius: 4px;">
                                <div>
                                    <strong>${product.name}</strong> (${quantity} ${quantity === 1 ? 'unidad' : 'unidades'}) agregado<br>
                                    <small>Total: $${(precio * quantity).toFixed(2)}</small>
                                </div>
                            </div>
                        `);
                    }
                });
            }
        });

        // Inicializar el carrito al cargar la página
        initCart();

        // Función para escapar HTML
        function escapeHtml(text) {
            return $('<div>').text(text).html();
        }



        // Búsqueda de productos

        // ---- BÚSQUEDA ROBUSTA ----
        const resultsContainer = $('#searchResults');
        const ENDPOINT = 'php/buscar-productos.php';

        function debounce(fn, wait) {
            let t;
            return function(...args) {
                clearTimeout(t);
                t = setTimeout(() => fn.apply(this, args), wait);
            };
        }

        function renderResultados(data) {
            resultsContainer.empty();
            if (data && (Array.isArray(data) ? data.length : Object.keys(data).length > 0)) {
                const productos = Array.isArray(data) ? data : Object.values(data);
                productos.forEach(producto => {
                    if (!producto) return;
                    const nombre = producto.nombre || '';
                    const presentacion = producto.presentacion || '';
                    const precioOriginal = parseFloat(producto.precio_venta || 0);
                    const precioOferta = parseFloat(producto.precio_oferta || 0);
                    const imagen = producto.imagen || 'no_img.png';
                    const cantidad = producto.cantidad_total || 0;
                    const tieneOferta = producto.tiene_oferta;

                    let badgeOferta = '';
                    let precioHTML = '';

                    if (tieneOferta && precioOferta > 0) {
                        badgeOferta = `<span class="badge bg-danger position-absolute top-0 start-0 m-2"><i class="fas fa-tags me-1"></i> Oferta</span>`;
                        precioHTML = `
            <div class="text-end w-100">
              <div class="text-muted text-decoration-line-through small">$${precioOriginal.toFixed(2)}</div>
              <div class="h5 text-success mb-0">$${precioOferta.toFixed(2)}</div>
            </div>`;
                    } else {
                        precioHTML = `<span class="h5 text-success mb-0">$${precioOriginal.toFixed(2)}</span>`;
                    }

                    resultsContainer.append(`
          <div class="col">
            <div class="card h-100 add-to-cart position-relative"
                data-id="${producto.id_producto}"
                data-name="${$('<div>').text(nombre).html()}"
                data-code="${$('<div>').text(producto.clave || '').html()}"
                data-price="${precioOriginal}"
                data-stock="${cantidad}"
                data-image="${$('<div>').text(imagen).html()}"
                data-minprice="${producto.precio_minimo_venta || 0}"
                data-preciosugerido="${producto.precio_sugerido || 0}"
                data-preciomaximo="${producto.precio_maximo_venta || 0}">
              ${badgeOferta}
              <div class="product-img-container">
                <img src="../img/productos/${imagen}" class="card-img-top product-img" alt="${$('<div>').text(nombre).html()}">
              </div>
              <div class="card-body d-flex flex-column">
                <h5 class="card-title">${$('<div>').text(nombre).html()}</h5>
                ${presentacion ? `<p class="card-text text-muted small flex-grow-1">${$('<div>').text(presentacion).html()}</p>` : ''}
                <div class="d-flex justify-content-between align-items-center mt-auto">
                  <span class="badge ${cantidad > 0 ? 'bg-success' : 'bg-danger'}">
                    ${cantidad > 0 ? 'Disponible' : 'Agotado'}: ${cantidad}
                  </span>
                  ${precioHTML}
                </div>
              </div>
            </div>
          </div>
        `);
                });
            } else {
                resultsContainer.html('<div class="col-12 text-center py-4 text-muted">No se encontraron productos</div>');
            }
            resultsContainer.removeClass('d-none');
        }

        const performSearch = debounce(function() {
            const query = $('#quickSearch').val().trim();
            if (query.length < 3) {
                resultsContainer.addClass('d-none').empty();
                return;
            }

            console.log('[buscar] →', ENDPOINT, 'q=', query); // <-- confirma en consola
            resultsContainer
                .html('<div class="col-12 text-center py-4"><div class="spinner-border text-primary"></div><p class="mt-2">Buscando productos...</p></div>')
                .removeClass('d-none');

            $.ajax({
                    url: ENDPOINT,
                    method: 'GET',
                    data: {
                        q: query
                    },
                    dataType: 'json',
                    cache: false
                })
                .done(function(data) {
                    console.log('[buscar][ok]', data);
                    renderResultados(data);
                })
                .fail(function(xhr, status, error) {
                    console.error('[buscar][error]', status, error, xhr.responseText);
                    resultsContainer
                        .html('<div class="col-12 text-center py-4 text-danger">Producto no encontrado</div>')
                        .removeClass('d-none');
                });
        }, 250);

        // Delegado + múltiples eventos para asegurar disparo
        $(document).on('input keyup change', '#quickSearch', performSearch);

        // Si se pulsa Enter, fuerza búsqueda
        $(document).on('keydown', '#quickSearch', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                performSearch();
            }
        });
    });
</script>