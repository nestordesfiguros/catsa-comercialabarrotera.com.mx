<?php
// admin/contenido/lista-precios.php
// Requiere: $clsConsulta ya inicializado en tu layout/controller
// Requiere sesión activa

// Empresa: soporte para sesiones existentes (id_empresa)
$idEmpresa = 0;
if (isset($_SESSION['id_empresa'])) $idEmpresa = (int)$_SESSION['id_empresa'];

$whereEmpresa = "";
if ($idEmpresa > 0) {
    $whereEmpresa = " AND (id_empresa = {$idEmpresa} OR id_empresa IS NULL OR id_empresa = 0)";
}

// Nombres de lista por defecto
$nombreLista = [
    1 => 'precio01',
    2 => 'precio02',
    3 => 'precio03',
    4 => 'precio04',
    5 => 'precio05'
];

$sqlNombres = "SELECT
    precio01_nombre, precio02_nombre, precio03_nombre, precio04_nombre, precio05_nombre
FROM cat_productos
WHERE estatus = 1 {$whereEmpresa}
ORDER BY id_producto ASC
LIMIT 1";

$rsNom = $clsConsulta->consultaGeneral($sqlNombres);
if ($clsConsulta->numrows > 0) {
    $row = $rsNom[1];
    $nombreLista[1] = !empty($row['precio01_nombre']) ? $row['precio01_nombre'] : 'precio01';
    $nombreLista[2] = !empty($row['precio02_nombre']) ? $row['precio02_nombre'] : 'precio02';
    $nombreLista[3] = !empty($row['precio03_nombre']) ? $row['precio03_nombre'] : 'precio03';
    $nombreLista[4] = !empty($row['precio04_nombre']) ? $row['precio04_nombre'] : 'precio04';
    $nombreLista[5] = !empty($row['precio05_nombre']) ? $row['precio05_nombre'] : 'precio05';
}

// Almacenes (solo de la empresa actual)
$almacenes = [];
$almacenDefaultId = 0;

$sqlAlm = "SELECT id, almacen
           FROM cat_almacenes
           WHERE estatus = 1 AND id_empresa = {$idEmpresa}
           ORDER BY almacen DESC";
$rsAlm = $clsConsulta->consultaGeneral($sqlAlm);
if ($clsConsulta->numrows > 0) {
    $primero = true;
    foreach ($rsAlm as $a) {
        $almacenes[] = $a;
        if ($primero) {
            $almacenDefaultId = (int)$a['id'];
            $primero = false;
        }
    }
}
?>

<div class="ms-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="productos">Inicio</a></li>
            <li class="breadcrumb-item active" aria-current="page">Lista de precios</li>
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
                                    <div class="col-12 col-md-2 me-2">
                                        <a type="button" class="btn btn-secondary btn-fixed mt-2 mt-md-0" href="productos">
                                            <i class="fa-solid fa-cubes"></i> Productos
                                        </a>
                                    </div>
                                    <div class="col-12 col-md-2">
                                        <button type="button" class="btn btn-info btn-fixed mt-2 mt-md-0 me-2">
                                            Lista de Precios
                                        </button>
                                    </div>
                                    <div class="col-12 col-md-2">
                                        <a type="button" class="btn btn-secondary btn-fixed mt-2 mt-md-0 me-2" href="almacen-entradas">
                                            <i class="fa-solid fa-warehouse"></i> Entradas
                                        </a>
                                    </div>
                                    <div class="col-12 col-md-2">
                                        <a type="button" class="btn btn-secondary btn-fixed mt-2 mt-md-0 me-2" href="salidas-almacen">
                                            <i class="fa-solid fa-warehouse"></i> Salidas
                                        </a>
                                    </div>
                                    <div class="col-12 col-md-2">
                                        <a type="button" class="btn btn-secondary btn-fixed mt-2 mt-md-0 me-2" href="ofertas">
                                            <i class="fas fa-tags"></i> Ofertas
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <div class="col-2 text-end">
                                <div class="col-12">
                                    <div class="form-outline">
                                        <input type="text" id="search" class="form-control" />
                                        <label class="form-label" for="search">Buscar</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-2 g-2 align-items-end">

                            <div class="col-12 col-md-2">
                                <label class="form-label fw-bold">Almacén</label>
                                <select id="filtroAlmacen" class="form-select">
                                    <?php if (count($almacenes) === 0): ?>
                                        <option value="0" selected>No hay almacenes para esta empresa</option>
                                    <?php else: ?>
                                        <?php foreach ($almacenes as $a): ?>
                                            <option value="<?= (int)$a['id'] ?>" <?= ((int)$a['id'] === $almacenDefaultId) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($a['almacen'] ?? '', ENT_QUOTES) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                                <div class="form-text">Selecciona el almacén a administrar.</div>
                            </div>

                            <div class="col-12 col-md-2">
                                <label class="form-label fw-bold">Lista de precios</label>
                                <select id="selectListaPrecio" class="form-select">
                                    <option value="1"><?= htmlspecialchars($nombreLista[1], ENT_QUOTES) ?></option>
                                    <option value="2"><?= htmlspecialchars($nombreLista[2], ENT_QUOTES) ?></option>
                                    <option value="3"><?= htmlspecialchars($nombreLista[3], ENT_QUOTES) ?></option>
                                    <option value="4"><?= htmlspecialchars($nombreLista[4], ENT_QUOTES) ?></option>
                                    <option value="5"><?= htmlspecialchars($nombreLista[5], ENT_QUOTES) ?></option>
                                </select>
                                <div class="form-text">Selecciona qué lista deseas visualizar/editar.</div>
                            </div>

                            <div class="col-12 col-md-2">
                                <button type="button" class="btn btn-secondary w-100" onclick="abrirModalRenombrarLista();">
                                    <i class="fas fa-pen me-2"></i>Renombrar lista
                                </button>
                                <div class="form-text">Cambia el nombre global de la lista.</div>
                            </div>

                            <div class="col-12 col-md-2">
                                <button type="button" class="btn btn-info w-100" onclick="abrirModalAjusteGlobal();">
                                    <i class="fas fa-sliders-h me-2"></i>Ajuste global
                                </button>
                                <div class="form-text">Aumenta/disminuye precios masivamente.</div>
                            </div>

                            <div class="col-12 col-md-2">
                                <button type="button" class="btn btn-primary w-100" onclick="abrirModalCopiar();">
                                    <i class="fas fa-copy me-2"></i>Copiar a almacén
                                </button>
                                <div class="form-text">Copia productos seleccionados a otro almacén.</div>
                            </div>

                        </div>
                    </div>

                    <div class="card-body">
                        <?php if (count($almacenes) === 0): ?>
                            <div class="alert alert-warning mb-0">
                                No existen almacenes para la empresa actual. Crea un almacén para usar este módulo.
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table id="tablaListaPrecios" class="table table-bordered table-striped w-100">
                                    <thead>
                                        <tr>
                                            <th class="text-center" style="width:40px;">
                                                <input type="checkbox" id="chkAll">
                                            </th>
                                            <th class="text-center" style="width:70px;">Foto</th>
                                            <th class="text-center">Clave</th>
                                            <th class="text-center">Producto</th>
                                            <th class="text-center" style="width:140px;">Precio</th>
                                            <th class="text-center" style="width:80px;">Editar</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>

                            <div class="alert alert-light border mt-3 mb-0">
                                <small class="text-muted">
                                    * Aquí solo se modifica el <b>precio</b> del producto en la lista seleccionada del almacén seleccionado.
                                    No se eliminan productos ni se modifica su foto.
                                </small>
                            </div>
                        <?php endif; ?>
                    </div>

                </div>

            </div>
        </div>
    </div>
</section>

<!-- ==========================
     MODAL VER FOTO (GRANDE)
========================== -->
<div class="modal fade" id="modalVerFotoPrecio" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><span id="tituloFotoPrecio"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body text-center">
                <div id="contenedorFotoPrecio"></div>
            </div>
        </div>
    </div>
</div>

<!-- ========================================================= -->
<!-- MODAL: RENOMBRAR LISTA (BOOTSTRAP 5 PURO) -->
<!-- ========================================================= -->
<div class="modal fade" id="modalRenombrarLista" tabindex="-1" aria-labelledby="modalRenombrarListaLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header bg-secondary text-white">
                <h5 class="modal-title" id="modalRenombrarListaLabel">Renombrar lista</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <div class="modal-body">
                <div class="mb-3">
                    <div class="small text-muted">Nombre actual</div>
                    <div class="fw-bold" id="lp_lista_actual_label">-</div>
                </div>

                <div class="mb-2">
                    <label for="lp_nuevo_nombre" class="form-label fw-bold">Nuevo nombre</label>
                    <input type="text" class="form-control" id="lp_nuevo_nombre" maxlength="100" autocomplete="off">
                    <div class="form-text">Se aplicará globalmente a todos los productos de la empresa.</div>
                </div>

                <input type="hidden" id="lp_lista_idx" value="1">
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">CERRAR</button>
                <button type="button" class="btn btn-primary" id="btnRenombrarLista">
                    <i class="fas fa-save me-2"></i>MODIFICAR
                </button>
            </div>

        </div>
    </div>
</div>


<!-- ========================================================= -->
<!-- MODAL: MODIFICAR PRECIO (BOOTSTRAP 5 PURO) -->
<!-- ========================================================= -->
<div class="modal fade" id="modalEditarPrecio" tabindex="-1" aria-labelledby="modalEditarPrecioLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalEditarPrecioLabel">Modificar precio</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <div class="modal-body">
                <div class="mb-3">
                    <div class="small text-muted">Producto</div>
                    <div class="fw-bold text-uppercase" id="lp_prod_nombre">-</div>
                    <div class="small text-muted" id="lp_prod_clave">-</div>
                </div>

                <div class="mb-2">
                    <label for="lp_precio" class="form-label fw-bold">Precio actual</label>
                    <input type="text" class="form-control" id="lp_precio" inputmode="decimal" autocomplete="off" placeholder="0.00">
                    <div class="form-text">Este precio corresponde a la lista seleccionada.</div>
                </div>

                <input type="hidden" id="lp_id_producto" value="0">
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">CERRAR</button>
                <button type="button" class="btn btn-primary" id="btnModificarPrecio">
                    <i class="fas fa-save me-2"></i>MODIFICAR
                </button>
            </div>

        </div>
    </div>
</div>


<!-- ==========================
     MODAL AJUSTE GLOBAL
========================== -->
<div class="modal fade" id="modalAjusteGlobal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">Ajuste global de precios</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <div class="modal-body">
                <div class="mb-2">
                    <div class="small text-muted">Lista seleccionada</div>
                    <div class="fw-bold" id="ag_lista_actual"></div>
                </div>

                <div class="row g-2 mt-2">
                    <div class="col-12 col-md-6">
                        <label class="form-label fw-bold">Tipo</label>
                        <select class="form-select" id="ag_tipo">
                            <option value="porcentaje">Porcentaje (%)</option>
                            <option value="monto">Monto ($)</option>
                        </select>
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label fw-bold">Operación</label>
                        <select class="form-select" id="ag_operacion">
                            <option value="aumentar">Aumentar</option>
                            <option value="disminuir">Disminuir</option>
                        </select>
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-bold">Valor</label>
                        <input type="text" class="form-control" id="ag_valor" placeholder="Ej: 10 o 25.50">
                        <div class="form-text">Ejemplo: 10% o $25.50</div>
                    </div>

                    <div class="col-12 mt-2">
                        <label class="form-label fw-bold">Aplicar a</label>
                        <select class="form-select" id="ag_scope">
                            <option value="todos">Todos los productos</option>
                            <option value="filtrados">Solo productos filtrados (según búsqueda actual)</option>
                        </select>
                    </div>

                    <div class="col-12 mt-2">
                        <div class="alert alert-light border mb-0">
                            <small class="text-muted">
                                * El ajuste actualiza precios en BD. Los precios no quedarán negativos.
                            </small>
                        </div>
                    </div>

                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-info" onclick="aplicarAjusteGlobal();">
                    <i class="fas fa-bolt me-2"></i>Modificar
                </button>
            </div>

        </div>
    </div>
</div>

<!-- ==========================
     MODAL COPIAR A OTRO ALMACÉN
========================== -->
<div class="modal fade" id="modalCopiarProductos" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Copiar productos a otro almacén</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <div class="modal-body">
                <div class="mb-2">
                    <div class="small text-muted">Seleccionados</div>
                    <div class="fw-bold" id="cp_count">0</div>
                </div>

                <div class="mb-2">
                    <label class="form-label fw-bold">Almacén destino</label>
                    <select id="cp_destino" class="form-select">
                        <?php
                        // Misma lista de almacenes, pero la opción actual se filtrará por JS al abrir modal
                        foreach ($almacenes as $a) {
                            echo '<option value="' . (int)$a['id'] . '">' . htmlspecialchars($a['almacen'] ?? '', ENT_QUOTES) . '</option>';
                        }
                        ?>
                    </select>
                    <div class="form-text">No se duplicarán productos si ya existen en el almacén destino (misma clave).</div>
                </div>

                <div class="alert alert-light border mb-0">
                    <small class="text-muted">
                        * Se copiarán los productos (cat_productos) al almacén destino. Esto no mueve inventario.
                    </small>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-primary" id="btnCopiarConfirmar">
                    <i class="fas fa-copy me-2"></i>Copiar
                </button>
            </div>

        </div>
    </div>
</div>

<script>
    let tablaLP = null;
    let rowEditing = null;
    let iconEditing = null;

    // ---------- Helpers ----------
    function formatMoney2(n) {
        const num = Number(n);
        if (!Number.isFinite(num)) return '0.00';
        return new Intl.NumberFormat('es-MX', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }).format(num);
    }

    function parseMoney(v) {
        const s = String(v ?? '').trim().replace(/,/g, '');
        if (s === '') return NaN;
        const num = Number(s);
        return Number.isFinite(num) ? num : NaN;
    }

    function setInputInvalid($input, message) {
        $input.addClass('is-invalid').removeClass('is-valid');
        let $fb = $input.next('.invalid-feedback');
        if ($fb.length === 0) $fb = $('<div class="invalid-feedback"></div>').insertAfter($input);
        $fb.text(message);
    }

    function setInputValid($input) {
        $input.removeClass('is-invalid').addClass('is-valid');
        $input.next('.invalid-feedback').remove();
    }

    function getSelectedAlmacen() {
        return parseInt($('#filtroAlmacen').val() || '0', 10) || 0;
    }

    // ---------- Foto grande ----------
    function verFotoPrecio(nombre, imagen) {
        $('#tituloFotoPrecio').text(nombre);
        $('#contenedorFotoPrecio').html('<img src="../img/productos/' + imagen + '" class="img-fluid rounded">');
        $('#modalVerFotoPrecio').modal('show');
    }

    // ---------- Modal editar precio ----------
    function abrirModalEditarPrecioFromIcon(iconEl) {
        const $icon = $(iconEl);

        const idProducto = parseInt($icon.data('id'), 10) || 0;
        const clave = String($icon.data('clave') || '');
        const nombre = String($icon.data('nombre') || '');
        const precio = String($icon.data('precio') || '0.00');

        if (!idProducto) return alertify.error('Producto inválido');

        iconEditing = iconEl;
        rowEditing = tablaLP.row($icon.closest('tr'));

        $('#lp_id_producto').val(idProducto);
        $('#lp_prod_clave').text(clave);
        $('#lp_prod_nombre').text(nombre);
        $('#lp_precio').val(precio);

        const $precioInput = $('#lp_precio');
        $precioInput.removeClass('is-invalid is-valid');
        $precioInput.next('.invalid-feedback').remove();

        $('#modalEditarPrecio').modal('show');
        setTimeout(() => $precioInput.trigger('focus').select(), 150);
    }

    // Formatea al salir del input (solo UI)
    $(document).on('blur', '#lp_precio', function() {
        const $input = $(this);
        const num = parseMoney($input.val());
        if (!Number.isFinite(num) || num < 0) return;
        $input.val(formatMoney2(num));
    });

    // Regla: NO confirmar con Enter
    $(document).on('keydown', '#lp_precio', function(e) {
        if (e.key === 'Enter') e.preventDefault();
    });

    $(document).on('click', '#btnModificarPrecio', function() {
        modificarPrecioProducto();
    });

    function modificarPrecioProducto() {
        const idProducto = parseInt($('#lp_id_producto').val(), 10) || 0;
        const lista = parseInt($('#selectListaPrecio').val(), 10) || 1;
        const idAlmacen = getSelectedAlmacen();

        const $precioInput = $('#lp_precio');
        const precioNum = parseMoney($precioInput.val());

        if (!idProducto) return alertify.error('Producto inválido');
        if (!idAlmacen) return alertify.error('Selecciona un almacén');

        // ✅ Validación numérica / no negativo
        if (!Number.isFinite(precioNum)) {
            setInputInvalid($precioInput, 'El precio debe ser numérico.');
            return;
        }
        if (precioNum < 0) {
            setInputInvalid($precioInput, 'El precio no puede ser negativo.');
            return;
        }
        setInputValid($precioInput);

        alertify.confirm(
            'Confirmación',
            '¿Deseas modificar el precio de este producto en la lista seleccionada?',
            function() {
                $.ajax({
                    url: 'ajax/lista-precios/actualizar-precio.php',
                    method: 'POST',
                    dataType: 'json',
                    data: {
                        id_producto: idProducto,
                        lista: lista,
                        precio: precioNum.toFixed(2),
                        id_almacen: idAlmacen
                    },
                    success: function(resp) {
                        if (resp && resp.ok) {
                            const nuevoPrecioFmt = formatMoney2(resp.precio);

                            alertify.success('Precio actualizado');
                            $('#modalEditarPrecio').modal('hide');

                            // ✅ SOLO actualiza la fila editada (sin recargar tabla)
                            if (rowEditing && rowEditing.node()) {
                                const tr = rowEditing.node();
                                // precio columna: (0 chk,1 foto,2 clave,3 producto,4 precio,5 editar)
                                $(tr).find('td:eq(4)').html('<div class="text-end">$ ' + nuevoPrecioFmt + '</div>');
                            }

                            // ✅ actualiza data-precio del icono para próxima edición
                            if (iconEditing) {
                                $(iconEditing).attr('data-precio', nuevoPrecioFmt).data('precio', nuevoPrecioFmt);
                            }

                            rowEditing = null;
                            iconEditing = null;

                        } else {
                            alertify.error((resp && resp.msg) ? resp.msg : 'No se pudo actualizar');
                        }
                    },
                    error: function() {
                        alertify.error('Error de conexión');
                    }
                });
            },
            function() {
                alertify.error('Operación cancelada');
            }
        ).set('labels', {
            ok: 'Sí',
            cancel: 'No'
        });
    }

    // ---------- Modal renombrar lista ----------
    function abrirModalRenombrarLista() {
        const textoLista = $('#selectListaPrecio option:selected').text().trim();
        $('#lp_lista_actual_label').text(textoLista);
        $('#lp_nuevo_nombre').val(textoLista);
        $('#lp_lista_idx').val($('#selectListaPrecio').val());

        $('#modalRenombrarLista').modal('show');
        setTimeout(() => $('#lp_nuevo_nombre').focus().select(), 150);
    }

    $(document).on('click', '#btnRenombrarLista', function() {
        renombrarListaPrecio();
    });

    function renombrarListaPrecio() {
        const lista = parseInt($('#lp_lista_idx').val(), 10) || 1;
        const nuevo = ($('#lp_nuevo_nombre').val() || '').trim();
        const idAlmacen = getSelectedAlmacen();

        if (!idAlmacen) return alertify.error('Selecciona un almacén');
        if (!nuevo || nuevo.length < 2) return alertify.error('Nombre muy corto');

        alertify.confirm(
            'Confirmación',
            '¿Deseas renombrar esta lista globalmente?',
            function() {
                $.ajax({
                    url: 'ajax/lista-precios/actualizar-nombre-lista.php',
                    method: 'POST',
                    dataType: 'json',
                    data: {
                        lista,
                        nombre: nuevo,
                        id_almacen: idAlmacen
                    },
                    success: function(resp) {
                        if (resp && resp.ok) {
                            alertify.success('Nombre de lista actualizado');
                            $('#selectListaPrecio option:selected').text(nuevo);
                            $('#modalRenombrarLista').modal('hide');
                        } else {
                            alertify.error((resp && resp.msg) ? resp.msg : 'No se pudo actualizar');
                        }
                    },
                    error: function() {
                        alertify.error('Error de conexión');
                    }
                });
            },
            function() {
                alertify.error('Operación cancelada');
            }
        ).set('labels', {
            ok: 'Sí',
            cancel: 'No'
        });
    }

    // ---------- Ajuste global ----------
    function abrirModalAjusteGlobal() {
        const textoLista = $('#selectListaPrecio option:selected').text();
        $('#ag_lista_actual').text(textoLista);

        $('#ag_tipo').val('porcentaje');
        $('#ag_operacion').val('aumentar');
        $('#ag_valor').val('');
        $('#ag_scope').val('todos');

        $('#modalAjusteGlobal').modal('show');
        setTimeout(() => $('#ag_valor').focus(), 150);
    }

    function aplicarAjusteGlobal() {
        const lista = parseInt($('#selectListaPrecio').val(), 10) || 1;
        const tipo = $('#ag_tipo').val();
        const operacion = $('#ag_operacion').val();
        const scope = $('#ag_scope').val();
        const idAlmacen = getSelectedAlmacen();

        const valorRaw = $('#ag_valor').val().trim();
        const valor = parseFloat(String(valorRaw).replace(/,/g, ''));

        if (!idAlmacen) return alertify.error('Selecciona un almacén');
        if (!Number.isFinite(valor) || valor <= 0) return alertify.error('Valor inválido');

        const searchActual = (tablaLP) ? tablaLP.search() : '';

        let msg = '¿Aplicar ajuste global a la lista seleccionada?';
        if (scope === 'filtrados') {
            msg += '<br><small class="text-muted">Solo aplicará a lo filtrado por búsqueda actual.</small>';
        }

        alertify.confirm(
            'Confirmación',
            msg,
            function() {
                $.ajax({
                    url: 'ajax/lista-precios/ajuste-global.php',
                    method: 'POST',
                    dataType: 'json',
                    data: {
                        lista,
                        tipo,
                        operacion,
                        valor,
                        scope,
                        search: searchActual,
                        id_almacen: idAlmacen
                    },
                    success: function(resp) {
                        if (resp && resp.ok) {
                            alertify.success('Ajuste aplicado');
                            $('#modalAjusteGlobal').modal('hide');
                            // aquí sí conviene reload porque cambian muchas filas
                            if (tablaLP) tablaLP.ajax.reload(null, false);
                        } else {
                            alertify.error((resp && resp.msg) ? resp.msg : 'No se pudo aplicar');
                        }
                    },
                    error: function() {
                        alertify.error('Error de conexión');
                    }
                });
            },
            function() {
                alertify.error('Operación cancelada');
            }
        ).set('labels', {
            ok: 'Sí',
            cancel: 'No'
        });
    }

    // ---------- DataTable ----------
    $(document).ready(function() {
        if (!$('#tablaListaPrecios').length) return;

        tablaLP = $('#tablaListaPrecios').DataTable({
            serverSide: true,
            processing: true,
            ajax: {
                url: 'ajax/lista-precios/tabla-lista-precios.php',
                type: 'POST',
                data: function(d) {
                    d.lista = $('#selectListaPrecio').val();
                    d.id_almacen = getSelectedAlmacen();
                },
                dataSrc: 'data'
            },
            ordering: true,
            pageLength: 20,
            responsive: true,
            dom: "<'row'<'col-sm-4'l><'col-sm-1'><'col-sm-7'p>>" +
                "<'row'<'col-sm-12'tr>>" +
                "<'row'<'col-sm-4'l><'col-sm-1'><'col-sm-7'p>>",
            language: {
                url: "assets/datatables/Spanish.json",
                sSearch: '<i class="fa fa-search" aria-hidden="true"></i> Buscar'
            },
            columnDefs: [{
                    targets: [0, 1, 5],
                    orderable: false
                },
                {
                    targets: [0, 1, 5],
                    className: 'text-center'
                },
                {
                    targets: [4],
                    className: 'text-end'
                }
            ]
        });

        // ✅ Asegura función global (para onclick="")
        window.abrirModalCopiar = function() {
            // toma los IDs seleccionados
            const ids = [];
            document.querySelectorAll('.chkProd:checked').forEach(chk => {
                const v = parseInt(chk.value, 10);
                if (v) ids.push(v);
            });

            if (ids.length === 0) {
                alertify.error('Selecciona al menos un producto');
                return;
            }

            // muestra cantidad seleccionada
            const cpCount = document.getElementById('cp_count');
            if (cpCount) cpCount.textContent = String(ids.length);

            // deshabilita el almacén origen en destino
            const origen = parseInt(document.getElementById('filtroAlmacen')?.value || '0', 10) || 0;
            const sel = document.getElementById('cp_destino');
            if (sel) {
                [...sel.options].forEach(o => o.disabled = false);
                const optOrigen = sel.querySelector(`option[value="${origen}"]`);
                if (optOrigen) optOrigen.disabled = true;

                // selecciona primera opción válida
                const firstValid = [...sel.options].find(o => !o.disabled);
                if (firstValid) sel.value = firstValid.value;
            }

            // ✅ abre modal con Bootstrap 5 puro
            const el = document.getElementById('modalCopiarProductos');
            if (!el) {
                alertify.error('No se encontró el modal de copiar');
                return;
            }
            bootstrap.Modal.getOrCreateInstance(el).show();
        };


        // Buscar
        $('#search').on('keyup', function() {
            tablaLP.search($(this).val()).draw();
        });

        // ✅ Cambio de lista => recarga y cambia precios
        $('#selectListaPrecio').on('change', function() {
            $('#chkAll').prop('checked', false);
            tablaLP.ajax.reload(null, false);
        });

        // ✅ Cambio de almacén => recarga
        $('#filtroAlmacen').on('change', function() {
            $('#chkAll').prop('checked', false);
            tablaLP.ajax.reload(null, false);
        });

        // Click editar
        $('#tablaListaPrecios tbody').on('click', '.btn-editar-precio', function() {
            abrirModalEditarPrecioFromIcon(this);
        });

        // Click foto
        $('#tablaListaPrecios tbody').on('click', '.btn-ver-foto', function() {
            const nombre = String($(this).data('nombre') || '');
            const imagen = String($(this).data('imagen') || '');
            if (imagen) verFotoPrecio(nombre, imagen);
        });

        // Seleccionar todos
        $('#chkAll').on('change', function() {
            $('.chkProd').prop('checked', $(this).is(':checked'));
        });

        $('#tablaListaPrecios').on('draw.dt', function() {
            $('#chkAll').prop('checked', false);
        });
    });
</script>