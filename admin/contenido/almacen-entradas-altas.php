<?php
// contenido/almacen-entradas-altas.php

$idEmpresa = isset($_SESSION['id_empresa']) ? (int)$_SESSION['id_empresa'] : 0;

// Obtener datos necesarios (FILTRADOS)
$proveedores = $clsConsulta->consultaGeneral("SELECT id, razon_social, nombre_comercial as nombre FROM cat_proveedores WHERE estatus=1 AND id_empresa={$idEmpresa} ORDER BY nombre_comercial ASC");
if (!is_array($proveedores) || $clsConsulta->numrows <= 0) $proveedores = [];

$almacenes = $clsConsulta->consultaGeneral("SELECT id, almacen FROM cat_almacenes WHERE estatus=1 AND id_empresa={$idEmpresa} ORDER BY almacen ASC");
if (!is_array($almacenes) || $clsConsulta->numrows <= 0) $almacenes = [];

// ODCs disponibles por empresa
$odcs = $clsConsulta->consultaGeneral("
    SELECT c.id, c.fecha, c.id_proveedor,
           COALESCE(p.nombre_comercial, p.nombre_Comercial, 'Proveedor no especificado') AS proveedor_nombre
    FROM cab_compras c
    LEFT JOIN cat_proveedores p ON c.id_proveedor = p.id
    WHERE c.id_empresa = {$idEmpresa}
      AND c.estatus = 'procesada'
      AND c.id NOT IN (
          SELECT id_odc FROM cab_entradas_almacen
          WHERE id_empresa = {$idEmpresa} AND id_odc IS NOT NULL
      )
    ORDER BY c.id DESC
");
if (!is_array($odcs) || $clsConsulta->numrows <= 0) $odcs = [];

$fecha_bd = date('Y-m-d');
?>

<div class="ms-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="inicio">Inicio</a></li>
            <li class="breadcrumb-item"><a href="almacen-entradas">Entradas</a></li>
            <li class="breadcrumb-item active">Alta</li>
        </ol>
    </nav>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <form action="" method="post" id="formEntradaAlmacen">
                        <div class="card-header bg-primary text-white">
                            <h5 class="card-title">Registro de Entrada</h5>
                        </div>

                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label class="form-label">Fecha *</label>
                                        <input type="date" name="fecha" class="form-control" value="<?= $fecha_bd ?>" required>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="form-label">Orden de Compra</label>
                                        <input type="text" id="odcInput" class="form-control" list="odcList" placeholder="Buscar ODC" autocomplete="off">
                                        <datalist id="odcList">
                                            <?php if (count($odcs) > 0): ?>
                                                <?php foreach ($odcs as $odc): ?>
                                                    <option value="ODC-<?= (int)$odc['id'] ?>"
                                                        data-id="<?= (int)$odc['id'] ?>"
                                                        data-proveedor-id="<?= (int)$odc['id_proveedor'] ?>"
                                                        data-proveedor-nombre="<?= htmlspecialchars($odc['proveedor_nombre']) ?>">
                                                        <?= htmlspecialchars("ODC-{$odc['id']} - {$odc['proveedor_nombre']}") ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </datalist>
                                        <input type="hidden" name="odc_id" id="odcId">
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label class="form-label">Almacén Destino *</label>
                                        <select name="almacen_id" class="form-control" required>
                                            <option value="">Seleccionar...</option>
                                            <?php foreach ($almacenes as $alm): ?>
                                                <option value="<?= (int)$alm['id'] ?>"><?= htmlspecialchars($alm['almacen']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label class="form-label">Proveedor *</label>
                                        <select name="proveedor_id" id="proveedorSelect" class="form-control" required>
                                            <option value="">Seleccionar...</option>
                                            <?php foreach ($proveedores as $prov): ?>
                                                <option value="<?= (int)$prov['id'] ?>"><?= htmlspecialchars($prov['nombre']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="form-label">Documento de Referencia</label>
                                        <input type="text" name="referencia" class="form-control" placeholder="Ej: Factura 123">
                                    </div>
                                </div>

                                <div class="col-md-8">
                                    <div class="form-group">
                                        <label class="form-label">Observaciones</label>
                                        <textarea name="observaciones" class="form-control" rows="1"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card-header bg-secondary text-white">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="card-title">Productos Recibidos</h5>
                                <button type="button" class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#productosModal">
                                    <i class="fas fa-plus"></i> Agregar
                                </button>
                            </div>
                        </div>

                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead class="bg-dark text-white">
                                        <tr>
                                            <th width="15%">Cantidad Recibida</th>
                                            <th width="10%">Unidad</th>
                                            <th>Producto</th>
                                            <th width="15%">Cantidad Pendiente</th>
                                            <th width="5%"></th>
                                        </tr>
                                    </thead>
                                    <tbody id="productosTableBody">
                                        <tr id="noProductsRow">
                                            <td colspan="5" class="text-center">No hay productos agregados</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div class="form-check mt-3">
                                <input class="form-check-input" type="checkbox" name="pedido_incompleto" id="pedidoIncompleto">
                                <label class="form-check-label" for="pedidoIncompleto">
                                    Marcar como pedido incompleto
                                </label>
                            </div>

                            <div id="incompletoObservacionesContainer" style="display: none;">
                                <div class="form-group mt-2">
                                    <label>Observaciones sobre lo faltante</label>
                                    <textarea name="incompleto_observaciones" class="form-control" rows="2"></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="card-footer text-right">
                            <button type="button" class="btn btn-secondary" onclick="history.back()">
                                <i class="fas fa-times"></i> Cancelar
                            </button>
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-check"></i> Registrar Entrada
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<div class="modal fade" id="productosModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Seleccionar Productos</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body productos-scroll">

                <div class="mb-3">
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                        <input type="text" id="buscarProducto" class="form-control" placeholder="Buscar producto...">
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-striped" id="productosModalTable">
                        <thead>
                            <tr>
                                <th width="50px"></th>
                                <th>Clave</th>
                                <th>Producto</th>
                                <th>Existencia</th>
                                <th>Unidad</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Catálogo tolerante (según tu documento) por empresa
                            $whereBase = "p.estatus=1 AND (
                                p.id_empresa={$idEmpresa}
                                OR EXISTS (SELECT 1 FROM inventarios i WHERE i.id_empresa={$idEmpresa} AND i.id_producto=p.id_producto)
                                OR EXISTS (SELECT 1 FROM cat_almacenes a WHERE a.id=p.id_almacen AND a.id_empresa={$idEmpresa})
                            )";

                            $productos = $clsConsulta->consultaGeneral("
                                SELECT p.id_producto, p.clave, p.nombre, p.cantidad_disponible,
                                       u.nombre_unidad as unidad
                                FROM cat_productos p
                                LEFT JOIN cat_unidades_de_medida u ON p.unidad_medida = u.id_unidad
                                WHERE {$whereBase}
                                ORDER BY p.nombre
                            ");

                            if ($clsConsulta->numrows > 0 && is_array($productos)) {
                                foreach ($productos as $prod) {
                                    echo '<tr data-id="' . (int)$prod['id_producto'] . '"
                                              data-clave="' . htmlspecialchars($prod['clave']) . '"
                                              data-nombre="' . htmlspecialchars($prod['nombre']) . '"
                                              data-unidad="' . htmlspecialchars($prod['unidad']) . '">
                                            <td><input type="checkbox" class="producto-check"></td>
                                            <td>' . htmlspecialchars($prod['clave']) . '</td>
                                            <td>' . htmlspecialchars($prod['nombre']) . '</td>
                                            <td>' . htmlspecialchars($prod['cantidad_disponible']) . '</td>
                                            <td>' . htmlspecialchars($prod['unidad']) . '</td>
                                          </tr>';
                                }
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="agregarProductosSeleccionados()">
                    <i class="fas fa-plus"></i> Agregar Selección
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    let productosODC = {};

    $(document).ready(function() {

        function prepararModalProductos() {
            $('.producto-check').prop('checked', false);
            $('#buscarProducto').val('');
            $('#productosModalTable tbody tr').show();
        }

        $('#productosModal').on('show.bs.modal', function() {
            prepararModalProductos();
        });

        $('#buscarProducto').keyup(function() {
            const search = $(this).val().toLowerCase();
            $('#productosModalTable tbody tr').each(function() {
                const text = $(this).text().toLowerCase();
                $(this).toggle(text.includes(search));
            });
        });

        $('#odcInput').on('input', function() {
            const opcion = $('#odcList option').filter(function() {
                return $(this).val() === $('#odcInput').val();
            });

            if (opcion.length > 0) {
                $('input[name="referencia"]').prop('readonly', true).addClass('bg-light');
            } else {
                $('input[name="referencia"]').prop('readonly', false).removeClass('bg-light');
            }

            if (opcion.length > 0) {
                const odcId = opcion.data('id');
                const proveedorId = opcion.data('proveedor-id');

                $('#odcId').val(odcId);
                $('#proveedorSelect').val(proveedorId).trigger('change');

                $('input[name="referencia"]').val(`ODC-${odcId}`);

                cargarProductosODC(odcId);
            } else {
                $('#odcId').val('');
                $('input[name="referencia"]').val('');
            }
        });

        $('#pedidoIncompleto').change(function() {
            $('#incompletoObservacionesContainer').toggle(this.checked);
        });

        // Al modificar la cantidad recibida
        $('#productosTableBody').on('input', '.cantidad-recibida', function() {
            const row = $(this).closest('tr');
            const cantidadODC = row.data('cantidad-odc');
            const cantidadRecibida = parseFloat($(this).val()) || 0;
            const pendiente = cantidadODC - cantidadRecibida;

            row.find('.pendiente-cell')
                .text(pendiente)
                .data('cantidad-pendiente', pendiente)
                .toggleClass('text-danger', pendiente > 0);

            // Si la cantidad recibida es igual a la original, desmarcar "pedido incompleto"
            const allEqual = checkAllEqual(); // Función que revisa si todas las cantidades son igual a las originales

            if (allEqual) {
                $('#pedidoIncompleto').prop('checked', false);
                $('#incompletoObservacionesContainer').hide();
                $('textarea[name="incompleto_observaciones"]').val('');
            } else {
                $('#pedidoIncompleto').prop('checked', true);
                $('#incompletoObservacionesContainer').show();
            }

            if (pendiente > 0) {
                $('#pedidoIncompleto').prop('checked', true);
                $('#incompletoObservacionesContainer').show();
            }
        });

        // Función que revisa si todas las cantidades son iguales a las originales
        function checkAllEqual() {
            let allEqual = true;

            $('#productosTableBody tr').each(function() {
                const cantidadODC = $(this).data('cantidad-odc');
                const cantidadRecibida = parseFloat($(this).find('.cantidad-recibida').val()) || 0;

                if (cantidadODC !== cantidadRecibida) {
                    allEqual = false;
                    return false; // Sale del bucle si encuentra una diferencia
                }
            });

            return allEqual;
        }

        function cargarProductosODC(odcId) {
            $.ajax({
                url: 'ajax/almacen-entradas/obtener-productos-odc.php',
                method: 'POST',
                data: {
                    odc_id: odcId
                },
                dataType: 'json',
                beforeSend: function() {
                    $('#productosTableBody').html('<tr><td colspan="5" class="text-center">Cargando productos de ODC...</td></tr>');
                },
                success: function(response) {
                    productosODC = {};

                    if (response.success && response.productos.length > 0) {
                        $('#productosTableBody').empty();

                        response.productos.forEach(function(producto) {
                            productosODC[producto.id_producto] = producto.cantidad;

                            const row = `
                            <tr data-id="${producto.id_producto}" data-cantidad-odc="${producto.cantidad}">
                                <td>
                                    <input type="number" name="cantidades[${producto.id_producto}]"
                                           class="form-control cantidad-recibida"
                                           value="${producto.cantidad}" min="0" required>
                                </td>
                                <td>${producto.unidad || 'PZA'}</td>
                                <td>
                                    ${producto.nombre}
                                    <input type="hidden" name="productos[]" value="${producto.id_producto}">
                                    <input type="hidden" name="claves[]" value="${producto.clave}">
                                </td>
                                <td class="pendiente-cell" data-cantidad-pendiente="0">
                                    0
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-danger btn-sm btn-eliminar">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>`;

                            $('#productosTableBody').append(row);
                        });

                        $('#noProductsRow').remove();

                        $('.cantidad-recibida').on('input', function() {
                            const row = $(this).closest('tr');
                            const cantidadODC = row.data('cantidad-odc');
                            const cantidadRecibida = parseFloat($(this).val()) || 0;
                            const pendiente = cantidadODC - cantidadRecibida;

                            row.find('.pendiente-cell')
                                .text(pendiente)
                                .data('cantidad-pendiente', pendiente)
                                .toggleClass('text-danger', pendiente > 0);

                            if (pendiente > 0) {
                                $('#pedidoIncompleto').prop('checked', true);
                                $('#incompletoObservacionesContainer').show();
                            }
                        });

                    } else {
                        $('#productosTableBody').html('<tr id="noProductsRow"><td colspan="5">No hay productos en esta ODC</td></tr>');
                    }
                },
                error: function() {
                    alertify.error('Error al cargar productos de la ODC');
                    $('#productosTableBody').html('<tr id="noProductsRow"><td colspan="5">Error al cargar productos</td></tr>');
                }
            });
        }

        // Eliminar producto de la lista
        $(document).on('click', '.btn-eliminar', function() {
            const row = $(this).closest('tr');
            const nombre = row.find('td:eq(2)').text().trim();

            alertify.confirm('Confirmar', `¿Eliminar <strong>${nombre}</strong> de la lista?`,
                function() {
                    row.remove();
                    if ($('#productosTableBody tr').not('#noProductsRow').length === 0) {
                        $('#productosTableBody').html('<tr id="noProductsRow"><td colspan="5" class="text-center">No hay productos agregados</td></tr>');
                    }
                    alertify.success('Producto eliminado');
                },
                function() {
                    alertify.error('Operación cancelada');
                }
            );
        });

        // Validación de formulario
        $('#formEntradaAlmacen').validate({
            rules: {
                fecha: {
                    required: true
                },
                almacen_id: {
                    required: true
                },
                proveedor_id: {
                    required: true
                }
            },
            messages: {
                fecha: {
                    required: "La fecha es obligatoria"
                },
                almacen_id: {
                    required: "Selecciona un almacén"
                },
                proveedor_id: {
                    required: "Selecciona un proveedor"
                }
            },
            submitHandler: function(form) {
                const odcId = $('#odcId').val();
                if (!odcId) {
                    const ref = $('input[name="referencia"]').val().trim();
                    const obs = $('textarea[name="observaciones"]').val().trim();

                    if (!ref) {
                        alertify.alert('Error', 'El campo Documento de Referencia es obligatorio cuando no se selecciona una ODC');
                        return false;
                    }
                    if (!obs) {
                        alertify.alert('Error', 'El campo Observaciones es obligatorio cuando no se selecciona una ODC');
                        return false;
                    }
                }

                if ($('#productosTableBody tr').not('#noProductsRow').length === 0) {
                    alertify.alert('Error', 'Debes agregar al menos un producto');
                    return false;
                }

                let cantidadesValidas = true;
                $('.cantidad-recibida').each(function() {
                    if (!$(this).val() || parseFloat($(this).val()) <= 0) {
                        cantidadesValidas = false;
                        $(this).addClass('is-invalid');
                        return false;
                    }
                });

                if (!cantidadesValidas) {
                    alertify.alert('Error', 'Todas las cantidades deben ser mayores a cero');
                    return false;
                }

                const datos = $(form).serialize();

                const incompleto = $('#pedidoIncompleto').is(':checked');
                const msg = incompleto ? '¿Registrar entrada marcando como PEDIDO INCOMPLETO?' : '¿Confirmar registro de entrada de almacén?';

                alertify.confirm('Confirmar', msg,
                    function() {
                        $('#modalSpiner').modal('show');
                        $.ajax({
                            type: "POST",
                            url: "ajax/almacen-entradas/guardar.php",
                            data: datos,
                            success: function(response) {
                                $('#modalSpiner').modal('hide');
                                if ((response || '').trim() === 'success') {
                                    alertify.success('Entrada guardada correctamente.');
                                    window.location.href = 'almacen-entradas';
                                } else {
                                    alertify.error('Error al guardar la entrada: ' + response);
                                }
                            },
                            error: function(xhr) {
                                $('#modalSpiner').modal('hide');
                                alertify.error('Error de conexión con el servidor.');
                                console.error(xhr);
                            }
                        });
                    },
                    function() {
                        alertify.error('Operación cancelada');
                    }
                );

                return false;
            }
        });
    });
</script>

<style>
    .pendiente-cell.text-danger {
        font-weight: bold;
    }

    .bg-light {
        background-color: #f8f9fa !important;
    }

    .productos-scroll {
        max-height: 70vh;
        overflow-y: auto;
    }
</style>