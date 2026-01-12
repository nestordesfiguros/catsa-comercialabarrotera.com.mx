<?php
$id = isset($cat) ? (int)$cat : 0;
$productos = $clsConsulta->consultaGeneral("SELECT * FROM cat_productos WHERE estatus=1");
?>

<!-- ENCABEZADO -->
<div class="ms-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="inicio">Inicio</a></li>
            <li class="breadcrumb-item"><a href="ofertas">Ofertas y Promociones</a></li>
            <li class="breadcrumb-item active" aria-current="page">Modificar Oferta</li>
        </ol>
    </nav>
</div>

<form id="formOfertaModificar" method="post">
    <input type="hidden" name="id_oferta" id="id_oferta" value="<?php echo $id ?>">
    <div class="card p-4">
        <h5 class="mb-4">Modificar Oferta/Promoción</h5>

        <div class="row mb-3">
            <div class="col-md-6">
                <div class="form-outline">
                    <input type="text" id="nombre" name="nombre" class="form-control" required autocomplete="off" />
                    <label class="form-label" for="nombre">Nombre de la Oferta</label>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-outline">
                    <select id="tipo_oferta" name="tipo_oferta" class="form-select" required>
                        <option value="">Seleccione...</option>
                        <option value="porcentaje">Descuento por porcentaje</option>
                        <option value="monto_fijo">Precio fijo</option>
                        <option value="combo">Combo (Producto + Bonus)</option>
                        <option value="cantidad">Precio por cantidad</option>
                    </select>
                    <label class="form-label" for="tipo_oferta">Tipo de Oferta</label>
                </div>
                <div id="ayudaTipoOferta" class="alert alert-info mt-3" style="display:none;">
                    <strong>Ejemplo:</strong>
                    <div id="textoEjemplo"></div>
                </div>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-6">
                <label class="form-label" for="fecha_inicio_fecha">Fecha de Inicio</label>
                <input type="date" id="fecha_inicio_fecha" name="fecha_inicio_fecha" class="form-control" />
            </div>
            <div class="col-md-6">
                <label class="form-label" for="fecha_fin_fecha">Fecha de Fin</label>
                <input type="date" id="fecha_fin_fecha" name="fecha_fin_fecha" class="form-control" />
            </div>
        </div>

        <div class="form-outline mb-3">
            <textarea id="descripcion" name="descripcion" class="form-control" rows="2"></textarea>
            <label class="form-label" for="descripcion">Descripción</label>
        </div>

        <!-- Botón de agregar producto -->
        <div class="text-start mb-3">
            <button type="button" class="btn btn-primary" id="btnAgregarProducto" disabled>
                <i class="fas fa-plus me-2"></i>AGREGAR PRODUCTO
            </button>
        </div>

        <div class="table-responsive">
            <table id="tablaProductosOferta" class="table table-bordered mt-3">
                <thead id="theadProductos"></thead>
                <tbody id="tbodyProductos">
                    <tr>
                        <td colspan="4" class="text-center">Cargando productos...</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            <button type="submit" class="btn btn-primary" id="btnGuardarOferta" disabled>GUARDAR CAMBIOS</button>
        </div>
    </div>
</form>

<!-- MODAL PRODUCTOS -->
<div class="modal fade" id="modalAddProductos" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Productos</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3 p-3">
                    <div class="row d-flex justify-content-end">
                        <div class="form-outline col-6">
                            <input type="text" id="search" class="form-control">
                            <label for="search" class="form-label">Buscar</label>
                        </div>
                    </div>
                </div>
                <table id="TableListaProductos" class="table table-bordered table-striped w-100">
                    <thead>
                        <tr>
                            <th>Clave</th>
                            <th>Producto</th>
                            <th>Precio</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($productos as $val): ?>
                            <tr data-id="<?= $val['id_producto'] ?>" data-clave="<?= $val['clave'] ?>" data-nombre="<?= $val['nombre'] ?>" data-precio="<?= is_numeric($val['precio_venta']) ? number_format((float)$val['precio_venta'], 2, '.', '') : '0.00' ?>">
                                <td><?= $val['clave'] ?></td>
                                <td><b class="text-primary" style="cursor:pointer;"><?= $val['nombre'] ?></b></td>
                                <td class="text-end"><b class="text-primary" style="cursor:pointer;">$<?= is_numeric($val['precio_venta']) ? number_format((float)$val['precio_venta'], 2, '.', ',') : '0.00' ?></b></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    // Archivo: js/ofertas/modificar-oferta.js

    $(document).ready(function() {
        const id_oferta = $('#id_oferta').val();
        const tbody = $('#tbodyProductos');
        let productosExistentes = [];

        const oTable = $('#TableListaProductos').DataTable({
            ordering: true,
            pageLength: 10,
            responsive: true,
            language: {
                url: "assets/datatables/Spanish.json",
                sSearch: '<i class="fa fa-search" aria-hidden="true"></i> Buscar'
            }
        });

        $('#search').keyup(function() {
            oTable.search($(this).val()).draw();
        });

        function renderTabla(tipo, productos) {
            const thead = $('#theadProductos');
            thead.empty();
            tbody.empty();

            let headers = '';
            switch (tipo) {
                case 'porcentaje':
                    headers = '<tr><th>Producto</th><th>% Descuento</th><th></th></tr>';
                    break;
                case 'monto_fijo':
                    headers = '<tr><th>Producto</th><th>Precio Fijo ($)</th><th></th></tr>';
                    break;
                case 'combo':
                    headers = '<tr><th>Producto</th><th>Tipo</th><th>Precio Fijo ($)</th><th></th></tr>';
                    break;
                case 'cantidad':
                    headers = '<tr><th>Producto</th><th>Cantidad Mínima</th><th>Precio Fijo ($)</th><th></th></tr>';
                    break;
            }
            thead.html(headers);

            if (productos.length === 0) {
                tbody.html('<tr><td colspan="4" class="text-center">Ningún producto agregado</td></tr>');
                return;
            }

            productosExistentes = productos.map(p => p.id_producto);

            productos.forEach(p => {
                const nombreProd = p.nombre || 'Producto #' + p.id_producto;
                let fila = '';
                switch (tipo) {
                    case 'porcentaje':
                        fila = `<tr>
                        <td><input type="hidden" name="productos[]" value="${p.id_producto}">${nombreProd}</td>
                        <td><input type="number" name="valor_oferta[]" class="form-control" value="${p.valor_oferta}" min="0" max="100" step="0.01" required /></td>
                        <td><button type="button" class="btn btn-sm btn-danger borrar-fila"><i class="fas fa-trash"></i></button></td>
                    </tr>`;
                        break;
                    case 'monto_fijo':
                        fila = `<tr>
                        <td><input type="hidden" name="productos[]" value="${p.id_producto}">${nombreProd}</td>
                        <td><input type="number" name="valor_oferta[]" class="form-control" value="${p.valor_oferta}" min="0" step="0.01" required /></td>
                        <td><button type="button" class="btn btn-sm btn-danger borrar-fila"><i class="fas fa-trash"></i></button></td>
                    </tr>`;
                        break;
                    case 'combo':
                        const tipoProd = p.producto_bonus == 1 ? 'bonus' : 'principal';
                        const disablePrecio = tipoProd === 'bonus' ? 'disabled' : '';
                        const valor = tipoProd === 'bonus' ? 0 : p.valor_oferta;
                        fila = `<tr>
                        <td><input type="hidden" name="productos[]" value="${p.id_producto}">${nombreProd}</td>
                        <td>
                            <select name="tipo_producto[]" class="form-select tipo-producto">
                                <option value="principal" ${tipoProd === 'principal' ? 'selected' : ''}>Principal</option>
                                <option value="bonus" ${tipoProd === 'bonus' ? 'selected' : ''}>Bonus</option>
                            </select>
                        </td>
                        <td><input type="number" name="valor_oferta[]" class="form-control precio-oferta" value="${valor}" min="0" step="0.01" ${disablePrecio}></td>
                        <td><button type="button" class="btn btn-sm btn-danger borrar-fila"><i class="fas fa-trash"></i></button></td>
                    </tr>`;
                        break;
                    case 'cantidad':
                        fila = `<tr>
                        <td><input type="hidden" name="productos[]" value="${p.id_producto}">${nombreProd}</td>
                        <td><input type="number" name="cantidad_minima[]" class="form-control" value="${p.cantidad_minima}" min="1" required /></td>
                        <td><input type="number" name="valor_oferta[]" class="form-control" value="${p.valor_oferta}" min="0" step="0.01" required /></td>
                        <td><button type="button" class="btn btn-sm btn-danger borrar-fila"><i class="fas fa-trash"></i></button></td>
                    </tr>`;
                        break;
                }
                tbody.append(fila);
            });

            $('#btnAgregarProducto').prop('disabled', false);
            $('#btnGuardarOferta').prop('disabled', false);
        }

        $('#tipo_oferta').change(function() {
            const tipo = $(this).val();
            $('#btnAgregarProducto').prop('disabled', !tipo);
            $('#btnGuardarOferta').prop('disabled', true);

            $('#ayudaTipoOferta').hide();
            $('#textoEjemplo').html('');

            const textos = {
                porcentaje: "Aplica un descuento en porcentaje a un producto.",
                monto_fijo: "Establece un precio fijo para un producto.",
                combo: "Agrupa varios productos, puedes asignar uno como bonus.",
                cantidad: "Establece un precio especial al comprar una cantidad mínima."
            };

            if (textos[tipo]) {
                $('#textoEjemplo').html(textos[tipo]);
                $('#ayudaTipoOferta').show();
            }

            renderTabla(tipo, []);
            productosExistentes = [];
        });

        $('#btnAgregarProducto').click(() => {
            if (!$('#tipo_oferta').val()) {
                alertify.error("Selecciona el tipo de oferta primero.");
                return;
            }
            $('#modalAddProductos').modal('show');
        });

        $('#TableListaProductos tbody').on('click', 'tr', function() {
            const tipo = $('#tipo_oferta').val();
            const id = $(this).data('id');
            const nombre = $(this).data('nombre');
            const precio = parseFloat($(this).data('precio')) || 0;

            if (productosExistentes.includes(id)) {
                alertify.error("Este producto ya fue agregado.");
                return;
            }

            let fila = '';
            switch (tipo) {
                case 'porcentaje':
                    fila = `<tr>
                    <td><input type="hidden" name="productos[]" value="${id}">${nombre}</td>
                    <td><input type="number" name="valor_oferta[]" class="form-control" value="0" min="0" max="100" required /></td>
                    <td><button type="button" class="btn btn-sm btn-danger borrar-fila"><i class="fas fa-trash"></i></button></td>
                </tr>`;
                    break;
                case 'monto_fijo':
                    fila = `<tr>
                    <td><input type="hidden" name="productos[]" value="${id}">${nombre}</td>
                    <td><input type="number" name="valor_oferta[]" class="form-control" value="${precio}" min="0" required /></td>
                    <td><button type="button" class="btn btn-sm btn-danger borrar-fila"><i class="fas fa-trash"></i></button></td>
                </tr>`;
                    break;
                case 'combo':
                    fila = `<tr>
                    <td><input type="hidden" name="productos[]" value="${id}">${nombre}</td>
                    <td>
                        <select name="tipo_producto[]" class="form-select tipo-producto">
                            <option value="principal">Principal</option>
                            <option value="bonus">Bonus</option>
                        </select>
                    </td>
                    <td><input type="number" name="valor_oferta[]" class="form-control precio-oferta" value="${precio}" min="0" step="0.01" /></td>
                    <td><button type="button" class="btn btn-sm btn-danger borrar-fila"><i class="fas fa-trash"></i></button></td>
                </tr>`;
                    break;
                case 'cantidad':
                    fila = `<tr>
                    <td><input type="hidden" name="productos[]" value="${id}">${nombre}</td>
                    <td><input type="number" name="cantidad_minima[]" class="form-control" value="1" min="1" required /></td>
                    <td><input type="number" name="valor_oferta[]" class="form-control" value="${precio}" min="0" required /></td>
                    <td><button type="button" class="btn btn-sm btn-danger borrar-fila"><i class="fas fa-trash"></i></button></td>
                </tr>`;
                    break;
            }

            $('#tbodyProductos').find('tr:contains("Ningún producto")').remove();
            $('#tbodyProductos').append(fila);
            $('#modalAddProductos').modal('hide');
            $('#btnGuardarOferta').prop('disabled', false);
            productosExistentes.push(id);
        });

        $(document).on('click', '.borrar-fila', function() {
            const id = $(this).closest('tr').find('input[name="productos[]"]').val();
            productosExistentes = productosExistentes.filter(p => p != id);
            $(this).closest('tr').remove();
            if ($('#tbodyProductos tr').length === 0) {
                $('#tbodyProductos').html('<tr><td colspan="4" class="text-center">Ningún producto agregado</td></tr>');
                $('#btnGuardarOferta').prop('disabled', true);
            }
        });

        $('#formOfertaModificar').submit(function(e) {
            e.preventDefault();
            const datos = $(this).serialize();
            alertify.confirm('Aviso', '¿Deseas guardar los cambios?',
                function() {
                    $.post('ajax/ofertas/modificar-oferta.php', datos, function(res) {
                        if (res == '0') {
                            alertify.success("Oferta modificada");
                            location.href = 'ofertas';
                        } else {
                            alertify.error("Error al modificar");
                        }
                    });
                },
                function() {
                    alertify.error('Cancelado');
                }
            ).set('labels', {
                ok: 'Sí',
                cancel: 'No'
            });
        });

        // Cargar datos iniciales
        $.post('ajax/ofertas/obtener-oferta.php', {
            id: id_oferta
        }, function(res) {
            if (res.success) {
                $('#nombre').val(res.oferta.nombre);
                $('#descripcion').val(res.oferta.descripcion);
                $('#fecha_inicio_fecha').val(res.oferta.fecha_inicio.substr(0, 10));
                $('#fecha_fin_fecha').val(res.oferta.fecha_fin.substr(0, 10));
                $('#tipo_oferta').val(res.oferta.tipo_oferta).trigger('change');
                renderTabla(res.oferta.tipo_oferta, res.productos);
            }
        }, 'json');
    });
</script>