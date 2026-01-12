<?php
/* contenido/facturas-modificar.php
 * URL esperada: facturas-modificar/{id}
 * Variables disponibles: $nav, $cat, $subcat, $pagina, $subpagina
 * $cat = id de la factura a modificar
 */
$id_factura = isset($cat) ? (int)$cat : 0;
if ($id_factura <= 0) {
    echo '<div class="alert alert-danger m-3">ID de factura inválido.</div>';
    return;
}
?>

<div class="ms-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="inicio">Inicio</a></li>
            <li class="breadcrumb-item"><a href="facturas">Facturas</a></li>
            <li class="breadcrumb-item active" aria-current="page">Modificar factura #<?= (int)$id_factura; ?></li>
        </ol>
    </nav>
</div>

<section class="content">
    <div class="row">
        <form method="post" action="" id="formModificar">
            <div class="d-flex justify-content-center align-items-center w-100">
                <div class="card col-12">
                    <div class="card-header">
                        <div class="row gy-3">
                            <div class="col-2">
                                <div class="form-group form-outline ms-1">
                                    <input type="text" id="folio_ui" class="form-control text-end" value="" disabled />
                                    <label class="form-label">Folio</label>
                                </div>
                            </div>
                            <div class="col-2">
                                <div class="form-group form-outline ms-1">
                                    <input type="date" id="fact_fecha" class="form-control" name="fact_fecha" />
                                    <label class="form-label" for="fact_fecha">Fecha de la Factura</label>
                                </div>
                            </div>
                            <div class="col-4">
                                <label>Forma de págo</label>
                                <select id="forma_pago" class="form-select" name="forma_pago" aria-label="Forma de pago">
                                    <option value="">Selecciona la forma de pago</option>
                                    <?php
                                    $res = $clsConsulta->consultaGeneral('SELECT * FROM cat_formas_pago');
                                    if ($clsConsulta->numrows > 0) {
                                        foreach ($res as $i => $val) {
                                            echo '<option value="' . htmlspecialchars($val['descripcion']) . '" data-id="' . (int)$val['id'] . '" data-codigo="' . htmlspecialchars($val['codigo']) . '">' . htmlspecialchars($val['descripcion'])  . '</option>';
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="col-4">
                                <label>Método de págo</label>
                                <select id="metodo_pago" class="form-select" name="metodo_pago" aria-label="Método de pago">
                                    <option value="">Selecciona el método de pago</option>
                                    <?php
                                    $res = $clsConsulta->consultaGeneral('SELECT * FROM cat_metodos_pago');
                                    if ($clsConsulta->numrows > 0) {
                                        foreach ($res as $i => $val) {
                                            echo '<option value="' . htmlspecialchars($val['descripcion']) . '" data-id="' . (int)$val['id'] . '" data-codigo="' . htmlspecialchars($val['codigo']) . '">' . htmlspecialchars($val['descripcion']) . ' (' . htmlspecialchars($val['codigo']) . ')</option>';
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="col-4">
                                <label>Uso CFDI</label>
                                <select id="uso_cfdi" class="form-select" name="id_uso_cfdi" aria-label="Uso CFDI">
                                    <option value="">Selecciona el uso CFDI</option>
                                    <?php
                                    $res = $clsConsulta->consultaGeneral('SELECT * FROM cat_uso_cfdi ORDER BY descripcion ASC');
                                    if ($clsConsulta->numrows > 0) {
                                        foreach ($res as $i => $val) {
                                            echo '<option value="' . htmlspecialchars($val['clave']) . '" data-id="' . (int)$val['id'] . '">' . htmlspecialchars($val['descripcion']) . ' (' . htmlspecialchars($val['clave']) . ')</option>';
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="col-8">
                                <div id="listaclientes" class="form-outline form-group">
                                    <input type="text" id="fact_cliente" class="form-control" list="listaClientes" name="fact_cliente" />
                                    <label class="form-label" for="fact_cliente">Cliente</label>
                                    <datalist id="listaClientes">
                                        <?php
                                        $res = $clsConsulta->consultaGeneral('SELECT * FROM cat_clientes ORDER BY id DESC');
                                        if ($clsConsulta->numrows > 0) {
                                            foreach ($res as $i => $val) {
                                                echo '<option value="' . htmlspecialchars($val['razon_social']) . '" data-id="' . (int)$val['id'] . '"></option>';
                                            }
                                        }
                                        ?>
                                    </datalist>
                                </div>
                                <small id="errorCliente" class="text-danger" style="display:none"></small>
                            </div>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="row mb-2">
                            <div class="col-3">
                                <button type="button" class="btn btn-primary btn-sm" id="btnAgregarProducto">
                                    Agregar producto
                                </button>
                            </div>
                            <div class="col-3">
                                <button type="button" class="btn btn-outline-primary btn-sm" id="btnSeleccionarRemision">
                                    <i class="fa fa-file-alt"></i> Cargar remisión
                                </button>
                            </div>
                            <div class="col-3 text-end">
                                <button type="button" class="btn btn-success btn-sm" id="btnGuardarCambios">
                                    <i class="fa fa-save"></i> Guardar cambios
                                </button>
                            </div>
                            <div class="col-3 text-end">
                                <button type="button" class="btn btn-warning btn-sm" id="btnGuardarTimbrar" disabled>
                                    <i class="fa fa-stamp"></i> Guardar y timbrar
                                </button>
                            </div>
                        </div>

                        <table id="tablaProductosEdit" class="table table-bordered table-striped">
                            <thead class="bg-dark text-white">
                                <tr>
                                    <th class="text-center" style="width:150px;">Cantidad</th>
                                    <th class="text-center">Producto</th>
                                    <th class="text-center" style="width:150px;">Precio U.</th>
                                    <th class="text-center" style="width:150px;">Total</th>
                                    <th class="text-center" style="width:80px;">Borrar</th>
                                </tr>
                            </thead>
                            <tbody id="tbodyProductosEdit">
                                <tr id="filaVacia">
                                    <td colspan="5">Sin conceptos</td>
                                </tr>
                            </tbody>
                        </table>

                        <div class="text-end">
                            <strong>Total:</strong> <span id="totalFactura">$0.00</span>
                        </div>
                    </div>

                    <!-- Hidden -->
                    <input type="hidden" id="id_factura" name="id_factura" value="<?= (int)$id_factura; ?>">
                    <input type="hidden" id="fact_serie" name="fact_serie" value="">
                    <input type="hidden" id="fact_folio" name="fact_folio" value="">
                    <input type="hidden" name="id_forma_pago" id="id_forma_pago">
                    <input type="hidden" name="id_metodo_pago" id="id_metodo_pago">
                    <input type="hidden" name="cliente_id" id="clienteId">
                    <input type="hidden" name="total" id="inputSumaTotal">
                    <input type="hidden" name="remision_id" id="remision_id">
                </div>
            </div>
        </form>
    </div>
</section>

<!-- Modal catálogo de productos (reutiliza el de altas si existe estilos) -->
<div class="modal fade" id="modalAddProductos" tabindex="-1" aria-labelledby="modalAddProductosLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalAddProductosLabel">Productos</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" id="btnCerrarModalProd"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3 p-3">
                    <div class="row d-flex justify-content-end">
                        <div class="form-outline col-6">
                            <input type="text" id="searchProd" class="form-control">
                            <label for="searchProd" class="form-label">Buscar</label>
                        </div>
                    </div>
                </div>
                <table id="TableListaProductosEdit" class="table table-bordered table-striped w-100">
                    <thead>
                        <tr>
                            <th>Clave</th>
                            <th>Producto</th>
                            <th>Precio</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $con = "SELECT * FROM cat_productos WHERE estatus=1";
                        $rs = $clsConsulta->consultaGeneral($con);
                        if ($clsConsulta->numrows > 0) {
                            foreach ($rs as $v => $val) {
                                $id_producto  = (int)$val['id_producto'];
                                $clave        = htmlspecialchars($val['clave'], ENT_QUOTES, 'UTF-8');
                                $nombre       = htmlspecialchars($val['nombre'], ENT_QUOTES, 'UTF-8');
                                $precio_venta = number_format((float)$val['precio_venta'], 2, ".", ",");
                                echo '<tr data-id="' . $id_producto . '" data-clave="' . $clave . '" data-nombre="' . $nombre . '" data-precio="' . $precio_venta . '">';
                                echo '<td>' . $clave . '</td>';
                                echo '<td><b class="text-primary" style="cursor:pointer;">' . $nombre . '</b></td>';
                                echo '<td class="text-end"><b class="text-primary" style="cursor:pointer;">$' . $precio_venta . '</b></td>';
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

<!-- Modal Remisiones (igual que en altas) -->
<div class="modal fade" id="modalRemisiones" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Remisiones pendientes</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-2">
                    <input type="text" id="buscarRemision" class="form-control" placeholder="Buscar...">
                </div>
                <table class="table table-sm table-bordered" id="tablaRemisiones">
                    <thead class="table-light">
                        <tr>
                            <th>Folio</th>
                            <th>Cliente</th>
                            <th>Fecha</th>
                            <th class="text-end">Total</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody><!-- se llena por AJAX --></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    (function() {
        // Utilidades notificaciones
        function notifyOK(msg) {
            (window.alertify ? alertify.success(msg) : alert(msg));
        }

        function notifyERR(msg) {
            (window.alertify ? alertify.error(msg) : alert(msg));
        }

        function confirmUI(titulo, msg, onOK, onCancel) {
            if (window.alertify && alertify.confirm) {
                alertify.confirm(titulo, msg, onOK, onCancel || function() {}).set('labels', {
                    ok: 'Sí',
                    cancel: 'No'
                });
            } else {
                if (confirm('[' + titulo + '] ' + msg)) onOK();
                else if (onCancel) onCancel();
            }
        }

        function parseJSONSeguro(data) {
            try {
                if (typeof data !== 'string') return data;
                const t = data.trim();
                const m = t.match(/\{[\s\S]*\}$/);
                return JSON.parse(m ? m[0] : t);
            } catch (e) {
                console.error('JSON inválido', e);
                return null;
            }
        }

        // Bloqueo UI
        function ensureBlocker() {
            if ($('#uiBlocker').length) return;
            $('body').append(`
      <div id="uiBlocker" style="position:fixed;inset:0;background:rgba(255,255,255,.75);z-index:20000;display:none;align-items:center;justify-content:center;backdrop-filter:blur(2px);">
        <div style="text-align:center;padding:24px 28px;border-radius:12px;background:#fff;box-shadow:0 10px 30px rgba(0,0,0,.15)">
          <div class="spinner-border" role="status" aria-label="Cargando..."></div>
          <div id="uiBlockerText" class="mt-3 fw-semibold">Procesando...</div>
        </div>
      </div>`);
        }

        function bloquearUI(msg) {
            ensureBlocker();
            $('#uiBlockerText').text(msg || 'Procesando...');
            $('body').css('overflow', 'hidden');
            $('#uiBlocker').fadeIn(100);
        }

        function desbloquearUI() {
            $('#uiBlocker').fadeOut(120, () => {
                $('body').css('overflow', '');
            });
        }

        function setBloqueoMensaje(m) {
            if ($('#uiBlocker').is(':visible')) $('#uiBlockerText').text(m || 'Procesando...');
        }

        // Campos / elementos
        const $tbody = $('#tbodyProductosEdit');
        const idFactura = parseInt($('#id_factura').val(), 10) || 0;

        // Datatable catálogo productos (client-side como en altas)
        if ($.fn.DataTable && $('#TableListaProductosEdit').length) {
            const o = $('#TableListaProductosEdit').DataTable({
                ordering: true,
                pageLength: 10,
                responsive: true,
                language: {
                    url: "assets/datatables/Spanish.json",
                    sSearch: '<i class="fa fa-search"></i> Buscar'
                }
            });
            $('#searchProd').on('keyup', function() {
                o.search($(this).val()).draw();
            });
        }

        // Resolver clienteId desde datalist
        let _lockCliente = false;

        function resolverClienteId() {
            if (_lockCliente) return;
            const val = $('#fact_cliente').val();
            let maxId = 0;
            $('#listaClientes option').each(function() {
                if ($(this).val() === val) {
                    const id = parseInt($(this).data('id'), 10) || 0;
                    if (id > maxId) maxId = id;
                }
            });
            $('#clienteId').val(maxId || '');
        }
        $('#fact_cliente').on('input change blur', resolverClienteId);
        $('#forma_pago').on('change', function() {
            $('#id_forma_pago').val($(this).find('option:selected').data('id') || '');
        });
        $('#metodo_pago').on('change', function() {
            $('#id_metodo_pago').val($(this).find('option:selected').data('id') || '');
        });

        // Cálculo totales
        function actualizarMensajeVacio() {
            const hay = $tbody.children('tr.fila-producto').length > 0;
            if (!hay && $('#filaVacia').length === 0) $tbody.append('<tr id="filaVacia"><td colspan="5">Sin conceptos</td></tr>');
            else if (hay) $('#filaVacia').remove();
        }

        function calcularTotalFila($tr) {
            const c = parseFloat($tr.find('.cantidad').val()) || 0;
            const p = parseFloat($tr.find('.precio-unitario').val()) || 0;
            const imp = c * p;
            $tr.find('.total').text(new Intl.NumberFormat('es-MX', {
                style: 'currency',
                currency: 'MXN'
            }).format(imp));
        }

        function actualizarSumaTotal() {
            let suma = 0;
            $tbody.find('tr.fila-producto .total').each(function() {
                const t = $(this).text().replace(/\$|,/g, '').trim();
                suma += parseFloat(t) || 0;
            });
            $('#inputSumaTotal').val(suma);
            $('#totalFactura').text(new Intl.NumberFormat('es-MX', {
                style: 'currency',
                currency: 'MXN'
            }).format(suma));
        }
        $tbody.on('input', '.cantidad, .precio-unitario', function() {
            const $tr = $(this).closest('tr');
            calcularTotalFila($tr);
            actualizarSumaTotal();
        }).on('click', '.btn-eliminar', function() {
            const $tr = $(this).closest('tr');
            const nombre = $tr.find('td:eq(1)').text().trim();
            confirmUI('Confirmación', '¿Eliminar "' + nombre + '"?', function() {
                $tr.remove();
                actualizarMensajeVacio();
                actualizarSumaTotal();
                notifyOK('Eliminado');
            });
        });

        // Agregar producto (modal)
        $('#btnAgregarProducto').on('click', function() {
            $('#modalAddProductos').modal('show');
        });
        $('#TableListaProductosEdit').on('click', 'tbody tr', function() {
            const $tr = $(this);
            const id = $tr.data('id');
            const clave = $tr.data('clave') || '';
            const nombre = $tr.data('nombre') || '';
            const precio = parseFloat(String($tr.data('precio') || '').replace(/,/g, '')) || 0;

            if ($('input[name="clave[]"][value="' + clave + '"]').length) {
                notifyERR('El producto ya está en la lista.');
                return;
            }
            const row = `
      <tr class="fila-producto">
        <td><input type="number" class="form-control cantidad text-end" value="1" min="1" name="cantidad[]" required></td>
        <td>${nombre}</td>
        <td><input type="number" class="form-control precio-unitario text-end" value="${precio}" step="0.01" min="0.01" name="precio_unitario[]" required></td>
        <td class="text-center total">$0.00</td>
        <td>
          <input type="hidden" name="producto_id[]" value="${id}">
          <input type="hidden" name="clave[]" value="${clave}">
          <div class="text-center"><i class="fas fa-trash fa-lg btn-eliminar text-danger" style="cursor:pointer;"></i></div>
        </td>
      </tr>`;
            $tbody.append(row);
            const $n = $tbody.find('tr.fila-producto').last();
            calcularTotalFila($n);
            actualizarMensajeVacio();
            actualizarSumaTotal();
            $('#modalAddProductos').modal('hide');
        });

        // Cargar datos de factura
        function cargarFactura() {
            bloquearUI('Cargando factura...');
            $.post('ajax/facturas/cargar-factura.php', {
                id: idFactura
            }, function(r) {
                desbloquearUI();
                const j = parseJSONSeguro(r) || {};
                if (!j.success) {
                    notifyERR(j.msg || 'No se pudo cargar');
                    return;
                }

                // Header
                $('#fact_serie').val(j.cab.serie || '');
                $('#fact_folio').val(j.cab.folio || '');
                $('#folio_ui').val(String((j.cab.serie || '') + (j.cab.folio || '')));
                $('#fact_fecha').val(j.cab.fecha || '');
                $('#btnGuardarTimbrar').prop('disabled', !!(j.cab.uuid || '').trim() || (parseInt(j.cab.timbrada, 10) === 1));

                _lockCliente = true;
                $('#fact_cliente').val(j.cab.razon_social || '');
                $('#clienteId').val(j.cab.id_receptor || '');
                _lockCliente = false;

                // selects
                // forma_pago (id_forma_pago) ya viene como entero
                $('#id_forma_pago').val(j.cab.forma_pago || '');
                $('#forma_pago option').each(function() {
                    if (parseInt($(this).data('id'), 10) == parseInt(j.cab.forma_pago, 10)) $(this).prop('selected', true);
                });
                // método pago: j.cab.metodo_pago = código SAT (varchar(3))
                $('#metodo_pago option').each(function() {
                    if (String($(this).data('codigo') || '').trim() === String(j.cab.metodo_pago || '').trim()) {
                        $(this).prop('selected', true);
                        $('#id_metodo_pago').val($(this).data('id') || '');
                    }
                });
                // uso cfdi: clave SAT
                $('#uso_cfdi option').each(function() {
                    if (String($(this).val() || '').trim() === String(j.cab.uso_cfdi || '').trim()) $(this).prop('selected', true);
                });

                // Detalle
                $tbody.empty();
                (j.items || []).forEach(function(it) {
                    const row = `
          <tr class="fila-producto">
            <td><input type="number" class="form-control cantidad text-end" value="${it.cantidad||1}" min="1" name="cantidad[]" required></td>
            <td>${it.descripcion||('Producto '+(it.id_producto||''))}</td>
            <td><input type="number" class="form-control precio-unitario text-end" value="${it.precio||0}" step="0.01" min="0.01" name="precio_unitario[]" required></td>
            <td class="text-center total">$0.00</td>
            <td>
              <input type="hidden" name="producto_id[]" value="${it.id_producto||''}">
              <input type="hidden" name="clave[]" value="${it.clave||''}">
              <div class="text-center"><i class="fas fa-trash fa-lg btn-eliminar text-danger" style="cursor:pointer;"></i></div>
            </td>
          </tr>`;
                    $tbody.append(row);
                    calcularTotalFila($tbody.find('tr.fila-producto').last());
                });
                actualizarMensajeVacio();
                actualizarSumaTotal();
            }).fail(function() {
                desbloquearUI();
                notifyERR('Fallo de red');
            });
        }

        // Validación (si está jquery.validate)
        if ($.validator && $('#formModificar').length) {
            $.validator.addMethod("regex", function(v, e, re) {
                if (re.constructor != RegExp) re = new RegExp(re);
                else if (re.global) re.lastIndex = 0;
                return this.optional(e) || re.test(v);
            }, "Formato inválido");
            $("#formModificar").validate({
                rules: {
                    forma_pago: {
                        required: true
                    },
                    metodo_pago: {
                        required: true
                    },
                    id_uso_cfdi: {
                        required: false
                    },
                    fact_cliente: {
                        required: true,
                        minlength: 3
                    }
                },
                messages: {
                    forma_pago: "Selecciona la forma de pago",
                    metodo_pago: "Selecciona el método de pago",
                    fact_cliente: "Selecciona el cliente"
                },
                errorElement: 'span',
                errorPlacement: function(err, el) {
                    err.addClass('invalid-feedback text-danger');
                    if (el.closest('td').length) el.closest('td').append(err);
                    else $(el).after(err);
                },
                highlight: function(el) {
                    $(el).addClass('is-invalid');
                },
                unhighlight: function(el) {
                    $(el).removeClass('is-invalid').addClass('is-valid');
                }
            });
        }

        // Guardar cambios
        async function guardarCambios() {
            if ($.validator && $('#formModificar').length && !$("#formModificar").valid()) {
                notifyERR('Corrige los campos requeridos');
                return null;
            }
            if (($('#clienteId').val() || '') === '') {
                notifyERR('Selecciona un cliente');
                return null;
            }
            if ($tbody.find('tr.fila-producto').length === 0) {
                notifyERR('Agrega al menos un producto');
                return null;
            }

            const datos = $("#formModificar").serialize();
            return new Promise((resolve) => {
                $.ajax({
                    type: 'POST',
                    url: 'ajax/facturas/modificar.php',
                    data: datos,
                    success: function(resp) {
                        const j = parseJSONSeguro(resp);
                        if (j && j.status === 'ok') {
                            resolve(j.id_factura || idFactura);
                        } else {
                            notifyERR(j && j.msg ? j.msg : 'No se pudo guardar');
                            resolve(null);
                        }
                    },
                    error: function() {
                        notifyERR('Fallo de red/servidor');
                        resolve(null);
                    }
                });
            });
        }

        $('#btnGuardarCambios').on('click', function() {
            confirmUI('Confirmar', '¿Deseas guardar los cambios de la factura?', async function() {
                bloquearUI('Guardando cambios...');
                const id = await guardarCambios();
                if (id) {
                    notifyOK('Cambios guardados');
                    setTimeout(() => location.href = 'facturas', 600);
                } else {
                    desbloquearUI();
                }
            });
        });

        // Guardar + Timbrar (solo si no estaba timbrada)
        $('#btnGuardarTimbrar').on('click', function() {
            confirmUI('Confirmar', '¿Guardar y timbrar la factura?', async function() {
                bloquearUI('Guardando cambios...');
                const id = await guardarCambios();
                if (!id) {
                    desbloquearUI();
                    return;
                }
                setBloqueoMensaje('Timbrando CFDI...');
                $.post('ajax/facturas/timbrar.php', {
                    id: id
                }, function(r) {
                    const j = parseJSONSeguro(r) || {};
                    if (j.success) {
                        notifyOK('Timbrada. UUID: ' + (j.uuid || ''));
                        setTimeout(() => location.href = 'facturas', 700);
                    } else {
                        notifyERR(j.msg || 'Error al timbrar');
                        desbloquearUI();
                    }
                }).fail(function() {
                    notifyERR('Fallo de red/servidor');
                    desbloquearUI();
                });
            });
        });

        // Remisiones (igual que en altas)
        $(document).on('click', '#btnSeleccionarRemision', function() {
            $.getJSON('ajax/facturas/remisiones-pendientes.php', function(r) {
                if (!r || r.success === false) {
                    notifyERR(r && r.msg ? r.msg : 'No se pudieron cargar remisiones');
                    return;
                }
                const $tb = $('#tablaRemisiones tbody').empty();
                (r.items || []).forEach(it => {
                    $tb.append(`<tr>
          <td>${it.folio}</td>
          <td>${it.cliente}</td>
          <td>${it.fecha}</td>
          <td class="text-end">$${Number(it.total||0).toFixed(2)}</td>
          <td><button class="btn btn-primary btn-sm btn-elegir-remision" data-id="${it.id}">Elegir</button></td>
        </tr>`);
                });
                $('#modalRemisiones').modal('show');
            }).fail(() => notifyERR('Fallo de red'));
        });
        $(document).on('keyup', '#buscarRemision', function() {
            const q = $(this).val().toLowerCase();
            $('#tablaRemisiones tbody tr').each(function() {
                $(this).toggle($(this).text().toLowerCase().indexOf(q) > -1);
            });
        });
        $(document).on('click', '.btn-elegir-remision', function() {
            const idRem = $(this).data('id');
            confirmUI('Confirmar', '¿Usar esta remisión para llenar la factura?', function() {
                $.getJSON('ajax/facturas/prefill-desde-remision.php', {
                    id: idRem
                }, function(r) {
                    if (!r || r.success === false) {
                        notifyERR(r && r.msg ? r.msg : 'Remisión no disponible');
                        return;
                    }
                    _lockCliente = true;
                    $('#fact_cliente').val(r.cliente ? (r.cliente.razon_social || '') : '');
                    $('#clienteId').val(r.cliente ? (r.cliente.id || '') : '');
                    $('#remision_id').val(r.id_remision || '');
                    _lockCliente = false;

                    $tbody.empty();
                    (r.items || []).forEach(it => {
                        const row = `
            <tr class="fila-producto">
              <td><input type="number" class="form-control cantidad text-end" value="${it.cantidad||1}" min="1" name="cantidad[]" required></td>
              <td>${it.nombre || ('Producto '+it.id_producto)}</td>
              <td><input type="number" class="form-control precio-unitario text-end" value="${it.precio||0}" step="0.01" min="0.01" name="precio_unitario[]" required></td>
              <td class="text-center total">$0.00</td>
              <td>
                <input type="hidden" name="producto_id[]" value="${it.id_producto}">
                <input type="hidden" name="clave[]" value="${it.clave||''}">
                <div class="text-center"><i class="fas fa-trash fa-lg btn-eliminar text-danger" style="cursor:pointer;"></i></div>
              </td>
            </tr>`;
                        $tbody.append(row);
                        calcularTotalFila($tbody.find('tr.fila-producto').last());
                    });
                    actualizarMensajeVacio();
                    actualizarSumaTotal();
                    $('#modalRemisiones').modal('hide');
                    notifyOK('Remisión cargada');
                }).fail(() => notifyERR('Fallo de red'));
            });
        });

        // Inicial
        cargarFactura();
    })();
</script>