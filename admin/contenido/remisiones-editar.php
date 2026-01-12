<?php
// remisiones-editar.php

// Empresa (multiempresa)
$idEmpresa = 0;
if (isset($_SESSION['id_empresa'])) $idEmpresa = (int)$_SESSION['id_empresa'];
elseif (isset($_SESSION['empresa'])) $idEmpresa = (int)$_SESSION['empresa'];

// Detectar ID desde rutas comunes: $id, $_GET['id'], $rutas[1]
$idRemision = 0;
if (isset($id)) $idRemision = (int)$id;
elseif (isset($_GET['id'])) $idRemision = (int)$_GET['id'];
elseif (isset($rutas) && isset($rutas[1])) $idRemision = (int)$rutas[1];

// Clientes SOLO de la empresa
$clientes = [];
$con = "SELECT id, razon_social
        FROM cat_clientes
        WHERE estatus=1 " . ($idEmpresa > 0 ? " AND id_empresa = {$idEmpresa} " : "") . "
        ORDER BY razon_social ASC";
$rs = $clsConsulta->consultaGeneral($con);
if ($clsConsulta->numrows > 0) {
    foreach ($rs as $v => $val) {
        if ($v === 0) continue;
        $clientes[] = [
            'id' => (int)$val['id'],
            'nombre' => $val['razon_social']
        ];
    }
}
?>

<div class="ms-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="inicio">Inicio</a></li>
            <li class="breadcrumb-item"><a href="remisiones">Remisiones</a></li>
            <li class="breadcrumb-item active" aria-current="page">
                <?= ($idRemision > 0 ? 'Editar' : 'Altas') ?>
            </li>
        </ol>
    </nav>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="row ">
            <div class="d-flex justify-content-center align-items-center w-100">
                <div class="col-10">
                    <div class="card">

                        <form action="" method="post" id="formPedidos">
                            <div class="card-header">

                                <div class="row g-3 align-items-end">
                                    <div class="col-6">
                                        <div class="form-outline">
                                            <input type="date" name="fecha" class="form-control" id="datepicker" value="<?= $fecha_bd ?>">
                                            <label for="datepicker" class="form-label">Fecha</label>
                                        </div>
                                        <div id="errorFecha" class="invalid-feedback mt-2" style="display: none;">
                                            Escribe una Fecha
                                        </div>
                                    </div>

                                    <div class="col-6">
                                        <div class="form-group">
                                            <div class="form-outline">
                                                <input type="text" id="clienteInput" class="form-control" name="cliente_nombre" list="clientes" placeholder="Selecciona un cliente..." autocomplete="off" />
                                                <label for="clienteInput" class="form-label">Cliente</label>
                                            </div>

                                            <datalist id="clientes">
                                                <?php foreach ($clientes as $cliente): ?>
                                                    <option value="<?= htmlspecialchars($cliente['nombre'], ENT_QUOTES) ?>" data-id="<?= (int)$cliente['id'] ?>"></option>
                                                <?php endforeach; ?>
                                            </datalist>
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <div class="form-outline">
                                            <input type="text" name="direccion_envio" class="form-control" id="domicilio" autocomplete="off">
                                            <label for="direccion_envio" class="form-label">Domicilio de entrega</label>
                                        </div>
                                    </div>
                                </div>

                                <?php if ($idRemision > 0): ?>
                                    <div class="row mt-3">
                                        <div class="col-12 d-flex justify-content-end gap-2">
                                            <button type="button" class="btn btn-outline-primary btn-sm" onclick="abrirModalProcesar();">
                                                <i class="fas fa-check me-1"></i> Procesar remisión
                                            </button>
                                            <a href="remisiones" class="btn btn-outline-secondary btn-sm">
                                                <i class="fas fa-arrow-left me-1"></i> Volver
                                            </a>
                                        </div>
                                    </div>
                                <?php endif; ?>

                            </div>

                            <div class="card-body">
                                <div class="col-12">
                                    <div class="row">
                                        <div class="col-6">
                                            <button type="button" class="btn btn-primary btn-sm" onclick="FnAgregarModal();">Agregar Producto</button>
                                        </div>
                                        <div class="col-6 text-end">
                                            <button type="submit" class="btn btn-success btn-sm">Guardar</button>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-3">
                                    <table id="tablaProductos" class="table table-bordered table-striped">
                                        <thead class="bg-dark text-white">
                                            <tr>
                                                <th class="text-center" style="width: 150px;">Cantidad</th>
                                                <th class="text-center">Producto</th>
                                                <th class="text-center">Precio</th>
                                                <th class="text-center">Borrar</th>
                                            </tr>
                                        </thead>
                                        <tbody id="tbodyProductos">
                                            <tr id="filaVacia">
                                                <td colspan="4">Ningún producto agregado</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <input type="hidden" name="id_remision" id="idRemision" value="<?= (int)$idRemision ?>">
                            <input type="hidden" name="id_cliente" id="clienteId" value="">
                        </form>

                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Modal Productos -->
<div class="modal fade" id="modalAddProductos" tabindex="-1" aria-labelledby="modalAddProductosLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalAddProductosLabel">Productos</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" onclick="FnCerrarModal();"></button>
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
                            <th class="text-center">Clave</th>
                            <th class="text-center">Producto</th>
                            <th class="text-center">Precio Venta</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Productos visibles para la empresa (igual criterio que usaste en lista-precios)
                        $whereEmpresaProd = "";
                        if ($idEmpresa > 0) {
                            $whereEmpresaProd = " AND (id_empresa = {$idEmpresa} OR id_empresa IS NULL OR id_empresa = 0) ";
                        }

                        $con = "SELECT id_producto, clave, nombre, excento, precio_venta
                                FROM cat_productos
                                WHERE estatus=1 AND precio_venta > 0 {$whereEmpresaProd}
                                ORDER BY nombre ASC";
                        $rs = $clsConsulta->consultaGeneral($con);
                        if ($clsConsulta->numrows > 0) {
                            foreach ($rs as $v => $val) {
                                if ($v === 0) continue;
                                $id_producto = (int)$val['id_producto'];
                                $precio_venta = (float)$val['precio_venta'];

                                echo '<tr data-id="' . $id_producto . '"
                                           data-clave="' . htmlspecialchars($val['clave'] ?? '', ENT_QUOTES) . '"
                                           data-excento="' . (int)($val['excento'] ?? 0) . '"
                                           data-nombre="' . htmlspecialchars($val['nombre'] ?? '', ENT_QUOTES) . '"
                                           data-precio_venta="' . $precio_venta . '">';
                                echo '<td>' . htmlspecialchars($val['clave'] ?? '', ENT_QUOTES) . '</td>';
                                echo '<td><b class="text-primary" style="cursor:pointer;">' . htmlspecialchars($val['nombre'] ?? '', ENT_QUOTES) . '</b></td>';
                                echo '<td class="text-end">$' . number_format($precio_venta, 2, ".", ",") . '</td>';
                                echo '</tr>';
                            }
                        }
                        ?>
                    </tbody>
                </table>

            </div>
        </div>
    </div>
</div>

<!-- Modal Procesar (reusa tus endpoints existentes) -->
<div class="modal fade" id="modalProcesarRemision" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Procesar remisión</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <div class="modal-body">
                <div class="mb-2">
                    <label class="form-label fw-bold">Selecciona almacén para surtir</label>
                    <select id="pr_id_almacen" class="form-select">
                        <option value="">Seleccionar almacén</option>
                    </select>
                    <small id="pr_error" class="text-danger d-none">Debes seleccionar un almacén.</small>
                </div>

                <div class="mt-2">
                    <label class="form-label">Comentario (opcional)</label>
                    <textarea id="pr_comentario" class="form-control" rows="2"></textarea>
                </div>
            </div>

            <div class="modal-footer">
                <button class="btn btn-outline-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button class="btn btn-primary" id="btnProcesarAhora">
                    <i class="fas fa-check me-2"></i>Procesar
                </button>
            </div>

        </div>
    </div>
</div>

<script>
    function money2(n) {
        const num = Number(n);
        if (!Number.isFinite(num)) return '0.00';
        return num.toLocaleString('es-MX', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }

    function FnAgregarModal() {
        $('#modalAddProductos').modal('show');
    }

    function FnCerrarModal() {
        $('#modalAddProductos').modal('hide');
    }

    function actualizarMensajeTablaVacia() {
        const $tbody = $('#tbodyProductos');
        if ($tbody.children().length === 0) {
            $tbody.append('<tr id="filaVacia"><td colspan="4">No hay productos agregados</td></tr>');
        } else {
            $('#filaVacia').remove();
        }
    }

    function obtenerDatosCliente(clienteId) {
        $.ajax({
            url: 'ajax/pedidos/busca-cliente.php',
            method: 'POST',
            data: {
                idCliente: clienteId
            },
            dataType: 'json',
            success: function(respuesta) {
                if (!respuesta.error) {
                    const direccion = `
                        ${respuesta.calle} ${respuesta.num_ext || ''} 
                        ${respuesta.num_int ? 'Int. ' + respuesta.num_int : ''}, 
                        ${respuesta.colonia}, 
                        ${respuesta.cp}, 
                        ${respuesta.nmunicipio}, 
                        ${respuesta.nestado}
                    `.replace(/\n/g, '').replace(/  +/g, ' ');

                    $('#domicilio').val(direccion.trim()).removeClass('is-invalid').addClass('is-valid');
                    $('#clienteInput').removeClass('is-invalid').addClass('is-valid');
                }
            }
        });
    }

    function cargarRemision(idRemision) {
        $.ajax({
            url: 'ajax/remisiones/obtener-remision.php',
            method: 'POST',
            dataType: 'json',
            data: {
                id: idRemision
            },
            success: function(resp) {
                if (!resp || !resp.success) {
                    alertify.error((resp && resp.message) ? resp.message : 'No se pudo cargar la remisión');
                    return;
                }

                const r = resp.remision;

                if (r.fecha) $('#datepicker').val(String(r.fecha).substring(0, 10));

                $('#clienteInput').val(r.cliente_nombre || '');
                $('#clienteId').val(r.id_cliente || '');
                $('#domicilio').val(r.direccion_envio || '');

                $('#tbodyProductos').empty();
                (resp.productos || []).forEach(function(p) {
                    const idp = parseInt(p.id_producto, 10) || 0;
                    const clave = String(p.clave || '');
                    const nombre = String(p.nombre || '');
                    const cantidad = Number(p.cantidad || 0);
                    const precioU = Number(p.precio_unitario || 0);
                    const excento = parseInt(p.excento || 0, 10) || 0;

                    let iva = 0;
                    if (excento == 1) iva = precioU * 0.16;

                    const fila = `
                        <tr class="fila-producto">
                            <td>
                                <input type="number" class="form-control cantidad text-end" value="${cantidad}" min="1" name="cantidad[]" required>
                            </td>
                            <td>${nombre}</td>
                            <td class="text-end">$${money2(precioU)}</td>
                            <td class="text-center">
                                <input type="hidden" name="producto_id[]" value="${idp}">
                                <input type="hidden" name="clave[]" value="${clave}">
                                <input type="hidden" name="precio_venta[]" value="${precioU}">
                                <input type="hidden" name="iva[]" value="${iva}">
                                <i class="fas fa-trash fa-lg btn-eliminar text-danger" style="cursor:pointer;"></i>
                            </td>
                        </tr>
                    `;
                    $('#tbodyProductos').append(fila);
                });

                actualizarMensajeTablaVacia();
            },
            error: function() {
                alertify.error('Error de conexión al cargar remisión');
            }
        });
    }

    function guardarPedido(form) {
        const formData = new FormData(form);
        const idRem = parseInt($('#idRemision').val() || '0', 10) || 0;
        const url = (idRem > 0) ? 'ajax/remisiones/actualizar.php' : 'ajax/remisiones/guardar.php';

        $.ajax({
            url: url,
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(respuesta) {
                const r = String(respuesta || '').trim();
                if (r === 'success') {
                    alertify.success(idRem > 0 ? 'Remisión actualizada' : 'Remisión guardada');
                    if (idRem <= 0) location.href = "remisiones";
                } else {
                    alertify.alert('<b class="text-danger">Aviso</b>', r || 'No se pudo guardar');
                }
            },
            error: function(xhr) {
                alertify.error('Error de conexión: ' + xhr.statusText);
            }
        });
    }

    function abrirModalProcesar() {
        const idRem = parseInt($('#idRemision').val() || '0', 10) || 0;
        if (!idRem) return alertify.error('Remisión inválida');

        $('#pr_error').addClass('d-none');
        $('#pr_comentario').val('');
        $('#pr_id_almacen').empty().append('<option value="">Seleccionar almacén</option>');

        // OJO: este endpoint también debe ser multiempresa (filtrar cat_almacenes.id_empresa)
        $.post('ajax/remisiones/buscar-almacenes.php', {}, function(data) {
            (data || []).forEach(function(item) {
                $('#pr_id_almacen').append(`<option value="${item.id}">${item.text}</option>`);
            });
            $('#modalProcesarRemision').modal('show');
        }, 'json').fail(function() {
            alertify.error('No se pudieron cargar almacenes');
        });
    }

    $(document).on('click', '#btnProcesarAhora', function() {
        const idRem = parseInt($('#idRemision').val() || '0', 10) || 0;
        const idAlm = parseInt($('#pr_id_almacen').val() || '0', 10) || 0;
        const comentario = $('#pr_comentario').val() || '';

        if (!idAlm) {
            $('#pr_error').removeClass('d-none');
            return;
        }

        alertify.confirm('Confirmación', '¿Procesar esta remisión?', function() {
            $.ajax({
                url: 'ajax/remisiones/cambiar-estatus.php',
                method: 'POST',
                dataType: 'json',
                data: {
                    id: idRem,
                    estatus: 'procesada',
                    comentario: comentario,
                    id_almacen: idAlm
                },
                success: function(resp) {
                    if (resp && resp.success) {
                        alertify.success(resp.message || 'Remisión procesada');
                        $('#modalProcesarRemision').modal('hide');
                        location.href = "remisiones";
                    } else {
                        alertify.error((resp && resp.message) ? resp.message : 'No se pudo procesar');
                    }
                },
                error: function() {
                    alertify.error('Error de conexión al procesar');
                }
            });
        }, function() {
            alertify.message('Acción cancelada');
        });
    });

    $(document).ready(function() {

        $('#TableListaProductos').dataTable({
            ordering: true,
            pageLength: 10,
            dom: "<'row'<'col-sm-6'l><'col-sm-6'p>>" +
                "<'row'<'col-sm-12'tr>>" +
                "<'row'<'col-sm-6'l><'col-sm-6'p>>",
            language: {
                url: "assets/datatables/Spanish.json"
            },
            responsive: true
        });

        var oTable = $('#TableListaProductos').DataTable();
        $('#search').keyup(function() {
            oTable.search($(this).val()).draw();
        });

        $('#TableListaProductos tbody').on('click', 'tr', function() {
            const $fila = $(this);
            const clave = String($fila.data('clave') || '');
            const nombre = String($fila.data('nombre') || '');
            const id_producto = parseInt($fila.data('id'), 10) || 0;
            const precio_venta = Number($fila.data('precio_venta') || 0);
            const excento = parseInt($fila.data('excento') || 0, 10) || 0;

            if (!id_producto) return alertify.error('Producto inválido');

            if ($(`input[name="clave[]"][value="${clave}"]`).length > 0) {
                alertify.warning('¡Este producto ya está en la lista!');
                return;
            }

            let iva = 0;
            if (excento == 1) iva = precio_venta * 0.16;

            const nuevaFila = `
                <tr class="fila-producto">
                    <td>
                        <input type="number" class="form-control cantidad text-end" value="1" min="1" name="cantidad[]" required>
                    </td>
                    <td>${nombre}</td>
                    <td class="text-end">$${money2(precio_venta)}</td>
                    <td class="text-center">
                        <input type="hidden" name="producto_id[]" value="${id_producto}">
                        <input type="hidden" name="clave[]" value="${clave}">
                        <input type="hidden" name="precio_venta[]" value="${precio_venta}">
                        <input type="hidden" name="iva[]" value="${iva}">
                        <i class="fas fa-trash fa-lg btn-eliminar text-danger" style="cursor:pointer;"></i>
                    </td>
                </tr>
            `;

            $('#tbodyProductos').append(nuevaFila);
            actualizarMensajeTablaVacia();
            FnCerrarModal();
        });

        $('#tbodyProductos').on('click', '.btn-eliminar', function() {
            const $fila = $(this).closest('.fila-producto');
            const nombreProducto = $fila.find('td:eq(1)').text().trim();

            alertify.confirm(
                'Confirmación',
                `¿Eliminar el producto: ${nombreProducto}?`,
                function() {
                    $fila.remove();
                    actualizarMensajeTablaVacia();
                    alertify.success('Producto eliminado');
                },
                function() {
                    alertify.message('Acción cancelada');
                }
            );
        });

        $('#formPedidos').validate({
            rules: {
                fecha: {
                    required: true
                },
                cliente_nombre: {
                    required: true,
                    clientExists: true
                },
                direccion_envio: {
                    required: true
                }
            },
            messages: {
                fecha: {
                    required: "Escribe una fecha"
                },
                cliente_nombre: {
                    required: "Selecciona un cliente"
                },
                direccion_envio: {
                    required: "Escribe el domicilio de entrega"
                }
            },
            errorElement: 'div',
            errorPlacement: function(error, element) {
                error.addClass('invalid-feedback');
                error.insertAfter(element);
            },
            highlight: function(element) {
                $(element).addClass('is-invalid').removeClass('is-valid');
            },
            unhighlight: function(element) {
                $(element).removeClass('is-invalid').addClass('is-valid');
            },
            submitHandler: function(form) {
                guardarPedido(form);
            }
        });

        actualizarMensajeTablaVacia();

        $('#clienteInput').on('input', function() {
            const opcion = $('#clientes option').filter(function() {
                return $(this).val() === $('#clienteInput').val();
            });

            if (opcion.length > 0) {
                const clienteId = opcion.data('id');
                $('#clienteId').val(clienteId);
                obtenerDatosCliente(clienteId);
            } else {
                $('#clienteId').val('');
                $('#domicilio').val('');
                $('#clienteInput').removeClass('is-valid').addClass('is-invalid');
            }
        });

        $.validator.addMethod("clientExists", function() {
            const clienteId = $('#clienteId').val();
            return !!clienteId;
        }, "Este cliente no existe.");

        const idRem = parseInt($('#idRemision').val() || '0', 10) || 0;
        if (idRem > 0) cargarRemision(idRem);
    });
</script>