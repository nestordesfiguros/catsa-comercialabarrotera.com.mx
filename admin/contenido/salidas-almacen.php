<?php
// contenido/salidas-almacen.php
// Asume $clsConsulta disponible para cargar catálogos de almacenes / clientes
$almacenes = $clsConsulta->consultaGeneral("SELECT id, almacen FROM cat_almacenes WHERE estatus=1 ORDER BY almacen");
$clientes  = $clsConsulta->consultaGeneral("SELECT id, razon_social FROM cat_clientes ORDER BY razon_social");
?>
<div class="ms-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="inicio">Inicio</a></li>
            <li class="breadcrumb-item active" aria-current="page">Salidas de almacén</li>
        </ol>
    </nav>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <div class="row col-12">
                            <div class="col-10">
                                <div class="row">
                                    <div class="col-12 col-md-2">
                                        <a type="button" class="btn btn-secondary btn-fixed mt-2 mt-md-0" href="productos"><i class="fa-solid fa-cubes"></i> Productos </a>
                                    </div>
                                    <div class="col-12 col-md-2">
                                        <a type="button" class="btn btn-secondary btn-fixed mt-2 mt-md-0 me-2" href="lista-precios"><i class="fa-solid fa-file-invoice-dollar"></i> Lista de Precios </a>
                                    </div>
                                    <div class="col-12 col-md-2">
                                        <a type="button" class="btn btn-info btn-fixed mt-2 mt-md-0" href="almacen-entradas-altas"><i class="fa fa-plus"></i> Entradas</a>
                                    </div>
                                    <div class="col-12 col-md-2">
                                        <button class="btn btn-info btn-fixed mt-2 mt-md-0" id="btnNuevaSalida">
                                            <i class="fas fa-plus"></i> Nueva salida
                                        </button>
                                    </div>
                                    <div class="col-12 col-md-2">
                                        <a type="button" class="btn btn-secondary btn-fixed mt-2 mt-md-0" href="ofertas"><i class="fas fa-tags"></i> Ofertas </a>
                                    </div>
                                    <div class="col-2">
                                        <div class="col-12">
                                            <div class="form-outline  mt-2 mt-md-0" data-mdb-input-init>
                                                <input type="text" id="search" class="form-control" />
                                                <label class="form-label" for="form12">Buscar</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="tablaSalidas" class="table table-bordered table-hover w-100">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Fecha</th>
                                        <th>Cliente</th>
                                        <th>Almacén</th>
                                        <th>Total</th>
                                        <th>Estatus</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</section>

<!-- Spinner full-screen -->
<div id="spinner" style="display:none;position:fixed;inset:0;background:rgba(255,255,255,.5);z-index:1055;">
    <div class="d-flex h-100 w-100 align-items-center justify-content-center">
        <div class="spinner-border" role="status"></div>
    </div>
</div>

<!-- Modal: Nueva/Editar Salida -->
<div class="modal fade" id="modalSalida" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title"><span id="tituloModalSalida">Nueva salida de almacén</span></h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">

                <form id="formSalida" autocomplete="off">
                    <input type="hidden" id="id_salida" name="id_salida" value="">

                    <!-- <div class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label mb-1">Almacén</label>
                            <select class="form-select" id="id_almacen" name="id_almacen" required>
                                <option value="">Seleccionar</option>
                                <?php foreach ($almacenes as $i => $a) {
                                    if ($i === 0) continue; ?>
                                    <option value="<?= $a['id'] ?>"><?= htmlspecialchars($a['almacen']) ?></option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label mb-1">Cliente (opcional)</label>
                            <select class="form-select" id="id_cliente" name="id_cliente">
                                <option value="">Sin cliente</option>
                                <?php foreach ($clientes as $i => $c) {
                                    if ($i === 0) continue; ?>
                                    <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['razon_social']) ?></option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label mb-1">Observaciones</label>
                            <input type="text" class="form-control" id="observaciones" name="observaciones">
                        </div>
                    </div> -->

                    <div class="row g-2 mt-3 align-items-center">
                        <div class="col-md-6">
                            <label class="form-label mb-1">Agregar remisiones</label>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-outline-primary" id="btnAbrirRemisiones">
                                    <i class="fa fa-search"></i> Buscar remisiones
                                </button>
                                <div class="form-check form-switch ms-2">
                                    <input class="form-check-input" type="checkbox" id="resolver_faltantes">
                                    <label class="form-check-label" for="resolver_faltantes" title="Si falta inventario, realizará traspasos automáticos desde otros almacenes">Resolver faltantes con traspasos automáticos</label>
                                </div>
                            </div>
                            <div class="form-text">Puedes seleccionar una o varias remisiones. Sus partidas se agregarán al detalle.</div>
                        </div>
                        <div class="col-md-6 text-end">
                            <h5 class="mb-0">Total: <span id="totalSalida">$0.00</span></h5>
                        </div>
                    </div>

                    <div class="table-responsive mt-3">
                        <table class="table table-striped" id="tablaDetalleSalida">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 45%">Producto</th>
                                    <th class="text-end" style="width: 12%">Cantidad</th>
                                    <th class="text-end" style="width: 16%">Precio unitario</th>
                                    <th style="width: 17%">Remisión</th>
                                    <th class="text-center" style="width: 10%">Acción</th>
                                </tr>
                            </thead>
                            <tbody id="tbodySalida">
                                <tr id="filaVacia">
                                    <td colspan="5" class="text-center text-muted">Sin partidas</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                </form>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" id="btnGuardarSalida" class="btn btn-primary">Guardar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Buscar remisiones -->
<div class="modal fade" id="modalRemisiones" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Seleccionar remisiones</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <table id="tablaRemisionesSelect" class="table table-bordered w-100">
                    <thead>
                        <tr>
                            <th>Remisión</th>
                            <th>Cliente</th>
                            <th>Fecha</th>
                            <th>Total</th>
                            <th>Agregar</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Ver salida -->
<div class="modal fade" id="modalVerSalida" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content" id="contenedorVerSalida">
            <!-- Se llena por AJAX -->
        </div>
    </div>
</div>
<!-- 
<script>
    (() => {
        // Contenedores (asegúrate que existan estos IDs en tu HTML)
        const $grpAlmacen = $('#grupoAlmacen'); // div contenedor del select Almacén
        const $grpCliente = $('#grupoCliente'); // div contenedor del select Cliente
        const $selAlmacen = $('#id_almacen'); // select almacén
        const $selCliente = $('#id_cliente'); // select cliente (si lo tienes) o input de solo lectura
        const $txtObserv = $('#observaciones'); // input observaciones
        const $tblDetalle = $('#tablaDetalle'); // tbody de partidas (si lo usas)

        // Remisiones seleccionadas en el modal "Buscar remisiones"
        let remisionesSeleccionadas = []; // [{id, id_almacen, almacen, id_cliente, cliente}]

        // Al hacer click en "AGREGAR remisiones" del modal de búsqueda
        // Supón que disparas este handler pasando los IDs:
        window.onRemisionesSeleccionadas = function(ids) {
            if (!ids || !ids.length) return;

            $.post('ajax/salidas-almacen/remisiones-info.php', {
                ids
            }, function(resp) {
                if (!resp || !resp.success) {
                    alertify.error(resp?.message || 'No se pudieron obtener las remisiones');
                    return;
                }

                remisionesSeleccionadas = resp.remisiones || [];

                // Rellenar detalle (productos) en tu tabla (ya lo tenías)
                // ...

                // UI: cliente/almacén
                aplicarLogicaClienteAlmacen(remisionesSeleccionadas);
            }, 'json');
        };

        function aplicarLogicaClienteAlmacen(rems) {
            if (!Array.isArray(rems) || rems.length === 0) {
                // Sin remisiones: mostrar campos normalmente
                mostrarGrupo($grpCliente, true);
                mostrarGrupo($grpAlmacen, true);
                $selCliente.prop('disabled', false).val('');
                $selAlmacen.prop('disabled', false).val('');
                return;
            }

            if (rems.length === 1) {
                // UNA remisión: ocultamos cliente/almacén porque ya vienen definidos
                const r = rems[0];
                setValorClienteSoloLectura(r.cliente);
                setValorAlmacenSoloLectura(r.almacen);

                mostrarGrupo($grpCliente, false);
                mostrarGrupo($grpAlmacen, false);

                // Guarda referencia interna por si la necesitas en el POST final
                $('#id_cliente_fijo').remove();
                $('#id_almacen_fijo').remove();
                $('<input type="hidden" id="id_cliente_fijo" name="id_cliente_fijo">').val(r.id_cliente).appendTo('form#frmSalida');
                $('<input type="hidden" id="id_almacen_fijo" name="id_almacen_fijo">').val(r.id_almacen).appendTo('form#frmSalida');

            } else {
                // VARIAS remisiones
                // Cliente: “Varios”
                setValorClienteSoloLectura('Varios');
                mostrarGrupo($grpCliente, true);
                $selCliente.prop('disabled', true); // informativo

                // Almacén: si todos iguales, bloquear; si diferentes, permitir elegir.
                const setAlms = new Set(rems.map(r => String(r.id_almacen)));
                if (setAlms.size === 1) {
                    const unico = rems[0];
                    setValorAlmacenSoloLectura(unico.almacen);
                    mostrarGrupo($grpAlmacen, true);
                    $selAlmacen.val(String(unico.id_almacen)).prop('disabled', true);
                } else {
                    // Diferentes almacenes en remisiones -> el usuario debe elegir almacén "origen" principal
                    mostrarGrupo($grpAlmacen, true);
                    $selAlmacen.prop('disabled', false).val('');
                    alertify.message('Selecciona el almacén origen para la salida (remisiones con almacenes diferentes).');
                }
            }
        }

        function mostrarGrupo($grp, show) {
            if (!$grp || !$grp.length) return;
            $grp.toggle(!!show);
        }

        function setValorClienteSoloLectura(texto) {
            const $ro = $('#cliente_ro'); // input/label de solo lectura
            if ($ro.length) $ro.val(texto);
        }

        function setValorAlmacenSoloLectura(texto) {
            const $ro = $('#almacen_ro'); // input/label de solo lectura
            if ($ro.length) $ro.val(texto);
        }

        // ====== Pintar faltantes en un modal bonito ======
        window.mostrarFaltantes = function(payload) {
            const items = Array.isArray(payload?.faltantes) ? payload.faltantes : [];
            if (!items.length) {
                alertify.error('Inventario insuficiente.');
                return;
            }

            // Construir tabla
            let html = `
      <div class="table-responsive">
        <table class="table table-sm table-bordered">
          <thead class="table-light">
            <tr>
              <th>Producto</th>
              <th class="text-end">Requerido</th>
              <th class="text-end">Disponible</th>
              <th class="text-end text-danger">Faltante</th>
              <th>Almacén origen</th>
              <th>Sugerencias</th>
            </tr>
          </thead>
          <tbody>
    `;
            items.forEach(it => {
                const sug = (it.otras_existencias || [])
                    .map(s => `<div>• ${s.almacen}: ${s.disponible}</div>`)
                    .join('') || '<span class="text-muted">Sin sugerencias</span>';

                html += `
        <tr>
          <td>${escapeHtml(it.nombre || ('Prod '+it.id_producto))}</td>
          <td class="text-end">${Number(it.requerido||0)}</td>
          <td class="text-end">${Number(it.disponible||0)}</td>
          <td class="text-end text-danger fw-bold">${Number(it.faltante||0)}</td>
          <td>${escapeHtml(it.almacen_origen?.nombre || '-')}</td>
          <td>${sug}</td>
        </tr>
      `;
            });
            html += `</tbody></table></div>`;

            alertify.alert('Inventario insuficiente', html).set('label', 'OK');
        };

        function escapeHtml(s) {
            return String(s || '')
                .replace(/&/g, '&amp;').replace(/</g, '&lt;')
                .replace(/>/g, '&gt;').replace(/"/g, '&quot;')
                .replace(/'/g, '&#39;');
        }

        // ====== Ejemplo de uso al guardar ======
        $('#btnGuardarSalida').on('click', function() {
            const fd = new FormData(document.getElementById('frmSalida'));
            // … agrega tus partidas …

            $.ajax({
                url: 'ajax/salidas-almacen/guardar.php',
                method: 'POST',
                data: fd,
                processData: false,
                contentType: false,
                dataType: 'json',
                beforeSend() {
                    $('#spinner').show();
                }
            }).done(function(r) {
                if (r && r.success) {
                    alertify.success('Salida registrada.');
                    // redirigir o limpiar
                } else if (r && r.code === 'INVENTORY_SHORTAGE') {
                    mostrarFaltantes(r);
                } else {
                    alertify.error(r?.message || 'No se pudo guardar.');
                }
            }).fail(function(xhr) {
                // si el backend mandó 422 con JSON de faltantes, lo mostramos:
                try {
                    const r = JSON.parse(xhr.responseText);
                    if (r?.code === 'INVENTORY_SHORTAGE') return mostrarFaltantes(r);
                } catch (e) {}
                alertify.error('Error de conexión');
            }).always(function() {
                $('#spinner').hide();
            });
        });
    })();
</script>

<script src="js/salidas-almacen.js"></script> -->