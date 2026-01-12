<!-- facturas-altas.php -->
<!-- Imask js -->
<script src="https://unpkg.com/imask"></script>

<!-- Content Header (Page header) -->
<div class="ms-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="inicio">Inicio</a></li>
            <li class="breadcrumb-item"><a href="facturas">Facturas</a></li>
            <li class="breadcrumb-item active" aria-current="page">Factura Alta</li>
        </ol>
    </nav>
</div>

<!-- Main content -->
<section class="content">
    <div class="row">
        <form method="post" action="" id="formAltas">
            <div class="d-flex justify-content-center align-items-center w-100">
                <div class="card col-12">
                    <div class="card-header">
                        <div class="row gy-3">
                            <div class="row gy-3">
                                <div class="col-2">
                                    <div class="form-group form-outline ms-1">
                                        <input type="text" class="form-control text-end" value="<?= $cat . '' . $subcat; ?>" disabled />
                                        <label class="form-label" for="fact_serie">Folio</label>
                                    </div>
                                </div>
                                <div class="col-2">
                                    <div class="form-group form-outline ms-1">
                                        <input type="date" id="fact_fecha" class="form-control" name="fact_fecha" value="<?= $fecha_bd; ?>" />
                                        <label class="form-label" for="fact_fecha">Fecha de la Factura</label>
                                    </div>
                                </div>

                            </div>

                            <div class="row gy-3">
                                <div class="col-4">
                                    <label>Forma de págo</label>
                                    <select id="forma_pago" class="form-select" name="forma_pago" aria-label="Forma de pago">
                                        <option value="">Selecciona la forma de pago</option>
                                        <?php
                                        $res = $clsConsulta->consultaGeneral('SELECT * FROM cat_formas_pago');
                                        foreach ($res as $v => $val) {
                                            echo '<option value="' . htmlspecialchars($val['descripcion']) . '" data-id="' . (int)$val['id'] . '" data-codigo="' . htmlspecialchars($val['codigo']) . '">' . htmlspecialchars($val['descripcion'])  . '</option>';
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
                                        foreach ($res as $v => $val) {
                                            echo '<option value="' . htmlspecialchars($val['descripcion']) . '" data-id="' . (int)$val['id'] . '" data-codigo="' . htmlspecialchars($val['codigo']) . '">' .  htmlspecialchars($val['descripcion']) . ' (' . htmlspecialchars($val['codigo']) . ') </option>';
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
                                        foreach ($res as $v => $val) {
                                            // Value = CLAVE SAT (p.ej. G01). También dejamos data-id por si lo necesitas.
                                            echo '<option value="' . htmlspecialchars($val['clave']) . '" data-id="' . (int)$val['id'] . '">' . htmlspecialchars($val['descripcion']) . ' (' . htmlspecialchars($val['clave']) . ')</option>';
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>


                            <div class="mt-3 me-3">
                                <div id="listacontratos" class="form-outline form-group">
                                    <input type="text" id="fact_cliente" class="form-control" list="listaClientes" name="fact_cliente" />
                                    <label class="form-label" for="fact_cliente">Cliente</label>
                                    <datalist id="listaClientes">
                                        <?php
                                        // antes: SELECT * FROM cat_clientes
                                        $res = $clsConsulta->consultaGeneral('SELECT * FROM cat_clientes ORDER BY id DESC');
                                        foreach ($res as $v => $val) {
                                            echo '<option value="' . htmlspecialchars($val['razon_social']) . '" data-id="' . (int)$val['id'] . '"></option>';
                                        }
                                        ?>

                                    </datalist>
                                </div>
                                <small id="errorCliente" class="text-danger" style="display:none"></small>
                            </div>
                        </div>
                    </div>

                    <!-- Captura de productos -->
                    <div class="card-body">
                        <div class="col-12">
                            <div class="row">
                                <div class="col-2">
                                    <button type="button" class="btn btn-primary btn-sm" onclick="FnAgregarModal();">Agregar Producto</button>
                                </div>
                                <div class="col-2">
                                    <button type="button" class="btn btn-outline-primary btn-sm" id="btnSeleccionarRemision">
                                        <i class="fa fa-file-alt"></i> Seleccionar remisión
                                    </button>
                                </div>
                                <div class="col-4 text-end">
                                    <button type="button" class="btn btn-success btn-sm" id="btnGuardar">
                                        <i class="fa fa-save"></i> Guardar
                                    </button>
                                </div>
                                <div class="col-4 text-end">
                                    <button type="button" class="btn btn-warning btn-sm" id="btnGuardarTimbrar">
                                        <i class="fa fa-stamp"></i> Guardar y timbrar
                                    </button>
                                </div>
                            </div>

                        </div>

                        <div class="mt-3">
                            <table id="tablaProductos" class="table table-bordered table-striped">
                                <thead class="bg-dark text-white" style="height:20px;">
                                    <tr>
                                        <th class="text-center" style="width:150px;">Cantidad</th>
                                        <th class="text-center">Producto</th>
                                        <th class="text-center" style="width:150px;">Precio U.</th>
                                        <th class="text-center" style="width:150px;">Total</th>
                                        <th class="text-center" style="width:80px;">Borrar</th>
                                    </tr>
                                </thead>
                                <tbody id="tbodyProductos">
                                    <tr id="filaVacia">
                                        <td colspan="5">Ningún producto agregado</td>
                                    </tr>
                                </tbody>
                            </table>

                            <div class="text-end">
                                <strong>Total:</strong> <span id="totalPedido">$0.00</span>
                            </div>
                        </div>
                    </div>

                    <!-- Hidden -->
                    <input type="hidden" id="fact_serie" class="form-control text-end" name="fact_serie" value="<?= $cat; ?>" />
                    <input type="hidden" id="fact_folio" class="form-control text-end" name="fact_folio" value="<?= $subcat; ?>" />
                    <input type="hidden" name="id_forma_pago" id="id_forma_pago">
                    <input type="hidden" name="id_metodo_pago" id="id_metodo_pago">
                    <input type="hidden" name="cliente_id" id="clienteId">
                    <input type="hidden" name="total" id="inputSumaTotal">
                    <input type="hidden" name="fact_id_usuario" value="<?php echo $_SESSION['id_user']; ?>">
                </div>
            </div>
        </form>
    </div>
</section>

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


<!-- Modal -->
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
                            <th>Clave</th>
                            <th>Producto</th>
                            <th>Precio</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $con = "SELECT * FROM cat_productos WHERE estatus=1";
                        $rs = $clsConsulta->consultaGeneral($con);
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
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<script>
    // ===============================
    // IDs ESPERADOS EN EL HTML
    // ===============================
    // Form principal:           #formAltas
    // Botón seleccionar remisión #btnSeleccionarRemision
    // Botón guardar              #btnGuardar
    // Botón guardar y timbrar    #btnGuardarTimbrar
    // Tabla catálogo productos   #TableListaProductos (en el modal #modalAddProductos)
    // Tbody de productos         #tbodyProductos
    // Modal de remisiones        #modalRemisiones  (con #tablaRemisiones y #buscarRemision)
    // Datalist clientes          #listaClientes (input #fact_cliente)
    // Selects                    #forma_pago, #metodo_pago, #uso_cfdi
    // Hidden ids                 #clienteId, #id_forma_pago, #id_metodo_pago, #id_uso_cfdi (si lo usas), #inputSumaTotal
    // (Este script agregará #remision_id si no existe)


    (function() {
        // ---------- Utilidades ----------
        function parseJSONSeguro(data) {
            try {
                if (typeof data !== 'string') return data;
                const txt = data.trim();
                const m = txt.match(/\{[\s\S]*\}$/);
                return JSON.parse(m ? m[0] : txt);
            } catch (e) {
                console.error('JSON inválido:', data, e);
                return null;
            }
        }

        function confirmUI(titulo, msg, onOK, onCancel) {
            if (window.alertify && alertify.confirm) {
                alertify.confirm(titulo, msg, onOK, onCancel || function() {})
                    .set('labels', {
                        ok: 'Sí',
                        cancel: 'No'
                    });
            } else {
                if (confirm(`[${titulo}] ${msg}`)) onOK();
                else if (onCancel) onCancel();
            }
        }

        function notifyOK(msg) {
            (window.alertify ? alertify.success(msg) : alert(msg));
        }

        function notifyERR(msg) {
            (window.alertify ? alertify.error(msg) : alert(msg));
        }

        // ---------- Spinner / Bloqueo de UI ----------
        function ensureBlocker() {
            if ($('#uiBlocker').length) return;
            const html = `
        <div id="uiBlocker" style="position:fixed;inset:0;background:rgba(255,255,255,.75);z-index:20000;display:none;align-items:center;justify-content:center;backdrop-filter:blur(2px);">
          <div style="text-align:center;padding:24px 28px;border-radius:12px;background:#fff;box-shadow:0 10px 30px rgba(0,0,0,.15)">
            <div class="spinner-border" role="status" aria-label="Cargando..."></div>
            <div id="uiBlockerText" class="mt-3 fw-semibold">Procesando...</div>
          </div>
        </div>`;
            $('body').append(html);
        }

        function bloquearUI(mensaje) {
            ensureBlocker();
            $('#uiBlockerText').text(mensaje || 'Procesando...');
            $('body').css('overflow', 'hidden');
            $('#uiBlocker').fadeIn(100);
        }

        function setBloqueoMensaje(mensaje) {
            if ($('#uiBlocker').is(':visible')) $('#uiBlockerText').text(mensaje || 'Procesando...');
        }

        function desbloquearUI() {
            $('#uiBlocker').fadeOut(120, function() {
                $('body').css('overflow', '');
            });
        }

        // ---------- Evitar submit nativo ----------
        $('#formAltas').on('submit', function(e) {
            e.preventDefault();
            return false;
        });

        // Asegurar hidden remision_id
        if ($('#remision_id').length === 0) {
            $('#formAltas').append('<input type="hidden" id="remision_id" name="remision_id">');
        }

        // ===================================================
        // CLIENTE (DATALIST) — Elegir id más ALTO si hay duplicados
        // ===================================================
        let _bloquearResolverCliente = false;

        function resolverClienteIdDesdeInput() {
            if (_bloquearResolverCliente) return;
            const val = $('#fact_cliente').val();
            let maxId = 0;
            $('#listaClientes option').each(function() {
                if ($(this).val() === val) {
                    const id = parseInt($(this).data('id'), 10) || 0;
                    if (id > maxId) maxId = id;
                }
            });
            if (maxId > 0) $('#clienteId').val(maxId);
            else $('#clienteId').val('');
        }
        $('#fact_cliente').on('input change blur', resolverClienteIdDesdeInput);

        // ---------- Selects de pago / uso CFDI ----------
        $('#forma_pago').on('change', function() {
            $('#id_forma_pago').val($(this).find('option:selected').data('id') || '');
        });
        $('#metodo_pago').on('change', function() {
            $('#id_metodo_pago').val($(this).find('option:selected').data('id') || '');
        });
        $('#uso_cfdi').on('change', function() {
            $('#id_uso_cfdi').val($(this).find('option:selected').data('id') || '');
        });

        // ---------- Modal de productos ----------
        window.FnAgregarModal = function() {
            $('#modalAddProductos').modal('show');
        };
        window.FnCerrarModal = function() {
            $('#modalAddProductos').modal('hide');
        };

        // DataTable del modal (si está disponible)
        if ($.fn.DataTable && $('#TableListaProductos').length) {
            const oTable = $('#TableListaProductos').DataTable({
                ordering: true,
                pageLength: 10,
                responsive: true,
                language: {
                    url: "assets/datatables/Spanish.json",
                    sSearch: '<i class="fa fa-search"></i> Buscar'
                }
            });
            $('#search').on('keyup', function() {
                oTable.search($(this).val()).draw();
            });
        }

        // Click en fila del catálogo -> agregar a la factura
        $('#TableListaProductos').on('click', 'tbody tr', function() {
            const $tr = $(this);
            const id = $tr.data('id');
            const clave = $tr.data('clave') || '';
            const nombre = $tr.data('nombre') || '';
            const precio = parseFloat(String($tr.data('precio') || '').replace(/,/g, '')) || 0;

            if ($(`input[name="clave[]"][value="${clave}"]`).length) {
                notifyERR('¡Este producto ya está en la lista!');
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
            $('#tbodyProductos').append(row);
            const $n = $('#tbodyProductos tr').last();
            calcularTotal($n);
            actualizarMensajeTablaVacia();
            FnCerrarModal();
        });

        // ---------- Totales ----------
        function actualizarMensajeTablaVacia() {
            const $tb = $('#tbodyProductos');
            const hay = $tb.children('tr.fila-producto').length > 0;
            if (!hay && $('#filaVacia').length === 0) {
                $tb.append('<tr id="filaVacia"><td colspan="5">Ningún producto agregado</td></tr>');
            } else if (hay) {
                $('#filaVacia').remove();
            }
        }

        function calcularTotal($fila) {
            const c = parseFloat($fila.find('.cantidad').val()) || 0;
            const pu = parseFloat($fila.find('.precio-unitario').val()) || 0;
            const imp = c * pu;
            $fila.find('.total').text(new Intl.NumberFormat('es-MX', {
                style: 'currency',
                currency: 'MXN'
            }).format(imp));
            actualizarSumaTotal();
        }

        function actualizarSumaTotal() {
            let suma = 0;
            $('#tbodyProductos tr.fila-producto').each(function() {
                const t = $(this).find('.total').text().replace(/\$|,/g, '').trim();
                suma += parseFloat(t) || 0;
            });
            $('#inputSumaTotal').val(suma);
            if ($('#totalPedido').length) {
                $('#totalPedido').text(new Intl.NumberFormat('es-MX', {
                    style: 'currency',
                    currency: 'MXN'
                }).format(suma));
            }
        }
        $('#tbodyProductos')
            .on('input', '.cantidad, .precio-unitario', function() {
                calcularTotal($(this).closest('tr'));
            })
            .on('click', '.btn-eliminar', function() {
                const $fila = $(this).closest('tr');
                const nombre = $fila.find('td:eq(1)').text().trim();
                confirmUI('Confirmación', `¿Eliminar "${nombre}"?`, function() {
                    $fila.remove();
                    actualizarMensajeTablaVacia();
                    actualizarSumaTotal();
                    notifyOK('Eliminado');
                });
            });

        // ---------- Validación (si está el plugin) ----------
        if ($.validator && $('#formAltas').length) {
            $.validator.addMethod("regex", function(v, e, re) {
                if (re.constructor != RegExp) re = new RegExp(re);
                else if (re.global) re.lastIndex = 0;
                return this.optional(e) || re.test(v);
            }, "Formato inválido");
            $("#formAltas").validate({
                rules: {
                    forma_pago: {
                        required: true
                    },
                    metodo_pago: {
                        required: true
                    },
                    uso_cfdi: {
                        required: true
                    },
                    fact_cliente: {
                        required: true,
                        minlength: 3
                    }
                },
                messages: {
                    forma_pago: "Selecciona la forma de pago",
                    metodo_pago: "Selecciona el método de pago",
                    uso_cfdi: "Selecciona el uso CFDI",
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

        // ---------- Remisiones (prefill) ----------
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

                    _bloquearResolverCliente = true;
                    $('#fact_cliente').val(r.cliente ? (r.cliente.razon_social || '') : '');
                    $('#clienteId').val(r.cliente ? (r.cliente.id || '') : '');
                    $('#remision_id').val(r.id_remision || '');
                    _bloquearResolverCliente = false;

                    $('#tbodyProductos').empty();
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
                        $('#tbodyProductos').append(row);
                        calcularTotal($('#tbodyProductos tr').last());
                    });
                    actualizarMensajeTablaVacia();
                    actualizarSumaTotal();
                    $('#modalRemisiones').modal('hide');
                    notifyOK('Remisión cargada');
                }).fail(() => notifyERR('Fallo de red'));
            });
        });

        // ---------- Guardado ----------
        async function guardarFactura() {
            if ($.validator && $('#formAltas').length && !$("#formAltas").valid()) {
                notifyERR('Corrige los campos requeridos');
                return null;
            }
            if ($('#clienteId').val() === '') {
                notifyERR('Selecciona un cliente');
                return null;
            }
            if ($('#tbodyProductos tr.fila-producto').length === 0) {
                notifyERR('Agrega al menos un producto');
                return null;
            }

            const datos = $("#formAltas").serialize();
            return new Promise((resolve) => {
                $.ajax({
                    type: 'POST',
                    url: 'ajax/facturas/guardar.php',
                    data: datos,
                    success: function(resp) {
                        const j = parseJSONSeguro(resp);
                        if (j && j.status === 'ok' && j.id_factura) {
                            resolve(j.id_factura);
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

        // ---------- Botón Guardar ----------
        $(document).on('click', '#btnGuardar', function() {
            confirmUI('Confirmar', '¿Deseas guardar la factura?', async function() {
                bloquearUI('Guardando factura...');
                const id = await guardarFactura();
                if (id) {
                    notifyOK('Factura guardada');
                    // Mantener bloqueo hasta redirección
                    setTimeout(() => location.href = 'facturas', 600);
                } else {
                    desbloquearUI();
                }
            });
        });

        // ---------- Botón Guardar + Timbrar ----------
        $(document).on('click', '#btnGuardarTimbrar', function() {
            confirmUI('Confirmar', '¿Deseas guardar y timbrar la factura?', async function() {
                bloquearUI('Guardando factura...');
                const id = await guardarFactura();
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
                }).fail(() => {
                    notifyERR('Fallo de red/servidor');
                    desbloquearUI();
                });
            });
        });

        // Estado inicial
        actualizarMensajeTablaVacia();
        actualizarSumaTotal();
        resolverClienteIdDesdeInput();

    })(); // IIFE
</script>