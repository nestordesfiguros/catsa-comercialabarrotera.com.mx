<?php
// contenido/auditoria-inventario.php
// Vista: Auditoría de Inventario
?>
<div class="container-fluid py-3">
    <h4 class="mb-3">Auditoría de Inventario</h4>

    <div class="row g-2 align-items-end mb-3">
        <div class="col-md-3">
            <label class="form-label">Almacén</label>
            <select id="f_almacen" class="form-select">
                <option value="">— Todos —</option>
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label">Producto (código / nombre)</label>
            <input id="f_producto" type="text" class="form-control" placeholder="Buscar...">
        </div>
        <div class="col-md-2">
            <label class="form-label">Desde</label>
            <input id="f_desde" type="date" class="form-control">
        </div>
        <div class="col-md-2">
            <label class="form-label">Hasta</label>
            <input id="f_hasta" type="date" class="form-control">
        </div>
        <div class="col-md-2 d-grid">
            <button id="btnFiltrar" class="btn btn-primary">FILTRAR</button>
        </div>
        <div class="col-md-3 d-grid">
            <button id="btnAjustarTodo" class="btn btn-success">APLICAR TODO (Auto)</button>
            <small class="text-muted">Tomará filtros de arriba.</small>
        </div>

    </div>

    <div class="table-responsive">
        <table id="tblAuditoria" class="table table-striped table-hover" style="width:100%">
            <thead>
                <tr>
                    <th>SKU</th>
                    <th>Producto</th>
                    <th>Almacén</th>
                    <th>Teórico</th>
                    <th>Contado</th>
                    <th>Diferencia</th>
                    <th>Estatus</th>
                    <th>Últ. mov.</th>
                    <th>Acciones</th>
                </tr>
            </thead>
        </table>
    </div>
</div>

<!-- Modal Detalle -->
<div class="modal fade" id="modalDetalle" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detalle de Diferencias</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body" id="detalleBody">Cargando…</div>
        </div>
    </div>
</div>

<!-- Modal Historial -->
<div class="modal fade" id="modalHistorial" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Historial de Movimientos</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body" id="historialBody">Cargando…</div>
        </div>
    </div>
</div>

<!-- Spinner bloqueo (usa #spinner según estándar) -->
<div id="spinner" style="display:none; position:fixed; inset:0; background:rgba(255,255,255,.6); z-index:1055;">
    <div class="d-flex justify-content-center align-items-center h-100">
        <div class="spinner-border" role="status"><span class="visually-hidden">Procesando…</span></div>
    </div>
</div>

<script>
    let dtAuditoria;

    document.addEventListener('DOMContentLoaded', () => {
        // Cargar almacenes
        fetch('ajax/inventario/almacenes-lista.php')
            .then(r => r.json())
            .then(j => {
                const sel = document.getElementById('f_almacen');
                (j.data || []).forEach(a => {
                    const o = document.createElement('option');
                    o.value = a.id;
                    o.textContent = a.nombre;
                    sel.appendChild(o);
                });
            });

        // DataTables — configuración obligatoria (server-side)
        dtAuditoria = $('#tblAuditoria').DataTable({
            processing: true,
            serverSide: true,
            dom: 'lrtip', // <— sin 'f' (filter). Mantiene length, table, info, pager.
            ajax: {
                url: 'ajax/inventario/auditoria-lista.php',
                type: 'POST',
                data: d => {
                    d.almacen = document.getElementById('f_almacen').value || '';
                    d.producto = document.getElementById('f_producto').value || '';
                    d.desde = document.getElementById('f_desde').value || '';
                    d.hasta = document.getElementById('f_hasta').value || '';
                }
            },
            language: {
                url: "assets/datatables/Spanish.json",
                sSearch: '<i class="fa fa-search" aria-hidden="true"></i> Buscar'
            },
            columns: [{
                    data: 'sku'
                },
                {
                    data: 'producto'
                },
                {
                    data: 'almacen'
                },
                {
                    data: 'teorico',
                    className: 'text-end'
                },
                {
                    data: 'contado',
                    className: 'text-end'
                },
                {
                    data: 'diferencia',
                    className: 'text-end',
                    render: (data, type, row) => {
                        const v = parseFloat((data ?? '0').toString().replace(/,/g, ''));
                        // Mantener ordenamiento numérico correcto
                        if (type === 'sort' || type === 'type') return isNaN(v) ? 0 : v;
                        // Mostrar en rojo si hay diferencia
                        if (!isNaN(v) && v !== 0) {
                            return `<span class="text-danger fw-semibold">${data}</span>`;
                        }
                        // Cero (o vacío) sin rojo
                        return `<span class="text-muted">${data || '0.0000'}</span>`;
                    }
                },
                {
                    data: 'estatus'
                },
                {
                    data: 'ultimo_mov'
                },
                {
                    data: null,
                    orderable: false,
                    render: row => {
                        const btnDet = `<button class="btn btn-sm btn-outline-primary me-1 btn-det" data-id="${row.id_producto}" data-almacen="${row.id_almacen}"><i class="fa fa-search"></i> Ver</button>`;
                        const btnHist = `<button class="btn btn-sm btn-outline-secondary me-1 btn-hist" data-id="${row.id_producto}" data-almacen="${row.id_almacen}"><i class="fa fa-history"></i> Historial</button>`;
                        const disabled = (parseFloat(row.diferencia) === 0 || row.aplicable === 0) ? 'disabled' : '';
                        const btnAj = `<button class="btn btn-sm btn-outline-success btn-ajustar" data-id="${row.id_producto}" data-almacen="${row.id_almacen}" ${disabled}><i class="fa fa-tools"></i> Ajustar</button>`;
                        return btnDet + btnHist + btnAj;
                    }
                }
            ],
            rowCallback: function(row, data) {
                const v = parseFloat((data.diferencia ?? '0').toString().replace(/,/g, ''));
                if (!isNaN(v) && v !== 0) {
                    $(row).addClass('table-danger'); // Bootstrap 5
                } else {
                    $(row).removeClass('table-danger');
                }
            },
            order: [
                [1, 'asc']
            ]
        });

        // Búsqueda externa opcional
        $('#f_producto').keyup(function() {
            dtAuditoria.search($(this).val()).draw();
        });

        // Filtros
        document.getElementById('btnFiltrar').addEventListener('click', () => dtAuditoria.ajax.reload());

        // Ver detalle
        $('#tblAuditoria').on('click', '.btn-det', function() {
            const id_producto = this.dataset.id,
                id_almacen = this.dataset.almacen;
            $('#detalleBody').html('Cargando…');
            new bootstrap.Modal('#modalDetalle').show();
            fetch('ajax/inventario/auditoria-detalle.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: new URLSearchParams({
                    id_producto,
                    id_almacen
                })
            }).then(r => r.text()).then(html => $('#detalleBody').html(html));
        });

        // Historial
        $('#tblAuditoria').on('click', '.btn-hist', function() {
            const id_producto = this.dataset.id,
                id_almacen = this.dataset.almacen;
            $('#historialBody').html('Cargando…');
            new bootstrap.Modal('#modalHistorial').show();
            fetch('ajax/inventario/auditoria-historial.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: new URLSearchParams({
                    id_producto,
                    id_almacen
                })
            }).then(r => r.text()).then(html => $('#historialBody').html(html));
        });

        // Aplicar ajuste (con elección de modo)
        $('#tblAuditoria').on('click', '.btn-ajustar', function() {
            const id_producto = this.dataset.id,
                id_almacen = this.dataset.almacen;
            const html = `
  <div>
    <label class="form-label">¿Cómo deseas cuadrar?</label>
    <div class="form-check"><input class="form-check-input" type="radio" name="modoaj" id="aj0" value="auto" checked><label class="form-check-label" for="aj0">Auto (recomendado)</label></div>
    <div class="form-check"><input class="form-check-input" type="radio" name="modoaj" id="aj1" value="conteo"><label class="form-check-label" for="aj1">Ajustar inventario al CONTEO físico (si existe)</label></div>
    <div class="form-check"><input class="form-check-input" type="radio" name="modoaj" id="aj2" value="kardex"><label class="form-check-label" for="aj2">Ajustar inventario a MOVIMIENTOS (kardex)</label></div>
    <div class="form-check"><input class="form-check-input" type="radio" name="modoaj" id="aj3" value="generar_salida"><label class="form-check-label" for="aj3">Generar SALIDA por remisiones procesadas sin salida</label></div>
    <div class="form-check"><input class="form-check-input" type="radio" name="modoaj" id="aj4" value="generar_entrada"><label class="form-check-label" for="aj4">Generar ENTRADA por compras/OC sin entrada</label></div>
  </div>`;

            alertify.confirm('Aplicar Ajuste', html,
                function ok() {
                    const modo = document.querySelector('input[name="modoaj"]:checked').value;
                    document.getElementById('spinner').style.display = 'block';
                    fetch('ajax/inventario/auditoria-aplicar-ajuste.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: new URLSearchParams({
                            id_producto,
                            id_almacen,
                            modo
                        })
                    }).then(r => r.json()).then(j => {
                        document.getElementById('spinner').style.display = 'none';
                        if (j.success) {
                            alertify.success(j.message || 'OK');
                            dtAuditoria.ajax.reload(null, false);
                        } else {
                            alertify.error(j.message || 'No se pudo completar');
                        }
                    }).catch(() => {
                        document.getElementById('spinner').style.display = 'none';
                        alertify.error('Error de red');
                    });
                },
                function cancel() {}
            ).set('labels', {
                ok: 'Continuar',
                cancel: 'Cancelar'
            });
        });
    });
    document.getElementById('btnAjustarTodo').addEventListener('click', () => {
        const almacen = document.getElementById('f_almacen').value || '';
        const producto = document.getElementById('f_producto').value || '';
        const desde = document.getElementById('f_desde').value || '';
        const hasta = document.getElementById('f_hasta').value || '';

        const form = `
    <div>
      <p>Se aplicará la lógica <b>Auto</b> a todos los productos del listado (con los filtros actuales).</p>
      <div class="form-check mb-2">
        <input class="form-check-input" type="checkbox" id="dryrun" checked>
        <label class="form-check-label" for="dryrun">Primero simular (dry-run)</label>
      </div>
      <label class="form-label">Límite por lote</label>
      <input id="limite" type="number" class="form-control" value="500" min="1" max="5000">
    </div>`;

        alertify.confirm('Aplicar TODO (Auto)', form,
            function ok() {
                const dry_run = document.getElementById('dryrun').checked ? 1 : 0;
                const limit = document.getElementById('limite').value || 500;
                document.getElementById('spinner').style.display = 'block';
                fetch('ajax/inventario/auditoria-aplicar-masivo.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: new URLSearchParams({
                        almacen,
                        producto,
                        desde,
                        hasta,
                        limit,
                        dry_run
                    })
                }).then(r => r.json()).then(j => {
                    document.getElementById('spinner').style.display = 'none';
                    if (!j.success) {
                        alertify.error(j.message || 'Error');
                        return;
                    }

                    const resumen = `
          <div class="text-start">
            <div><b>Procesados:</b> ${j.processed}</div>
            <div><b>Inventario al CONTEO:</b> ${j.applied_conteo}</div>
            <div><b>Inventario al KARDEX:</b> ${j.applied_kardex}</div>
            <div><b>Salidas generadas:</b> ${j.salidas_creadas}</div>
            <div><b>Entradas generadas:</b> ${j.entradas_creadas}</div>
            <div><b>Sin cambio:</b> ${j.sin_cambios}</div>
            <div><b>Errores:</b> ${j.errores}</div>
            ${dry_run?'<div class="mt-2 text-info"><b>Dry-run</b> (no se realizaron cambios). Vuelve a ejecutar sin la casilla para aplicar.</div>':''}
          </div>`;
                    alertify.alert('Resumen ejecución', resumen);
                    if (!dry_run) dtAuditoria.ajax.reload(null, false);
                }).catch(() => {
                    document.getElementById('spinner').style.display = 'none';
                    alertify.error('Error de red');
                });
            },
            function cancel() {}
        ).set('labels', {
            ok: 'Ejecutar',
            cancel: 'Cancelar'
        });
    });
</script>