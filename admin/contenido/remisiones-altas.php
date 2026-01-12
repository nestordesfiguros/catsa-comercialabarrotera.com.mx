<?php
// remisiones-altas.php

$idEmpresa = isset($_SESSION['id_empresa']) ? (int)$_SESSION['id_empresa'] : 0;

// =====================
// Clientes (multiempresa)
// =====================
$clientes = [];
$con = "SELECT id, razon_social 
        FROM cat_clientes 
        WHERE estatus=1 AND id_empresa={$idEmpresa}
        ORDER BY razon_social ASC";
$rs = $clsConsulta->consultaGeneral($con);
if ($clsConsulta->numrows > 0 && is_array($rs)) {
    foreach ($rs as $val) {
        $clientes[] = [
            'id' => $val['id'],
            'nombre' => $val['razon_social']
        ];
    }
}

// =====================
// Almacenes (multiempresa)
// =====================
$almacenes = [];
$con = "SELECT id, almacen 
        FROM cat_almacenes 
        WHERE estatus=1 AND id_empresa={$idEmpresa}
        ORDER BY almacen ASC";
$rs = $clsConsulta->consultaGeneral($con);
if ($clsConsulta->numrows > 0 && is_array($rs)) {
    foreach ($rs as $val) {
        $almacenes[] = [
            'id' => $val['id'],
            'nombre' => $val['almacen']
        ];
    }
}
?>

<div class="ms-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="inicio">Inicio</a></li>
            <li class="breadcrumb-item"><a href="remisiones">Remisiones</a></li>
            <li class="breadcrumb-item active" aria-current="page"> Altas</li>
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
                                <div class="row gy-3">
                                    <div class="col-2">
                                        <div class="form-outline">
                                            <input type="date" name="fecha" class="form-control" id="datepicker" value="<?= $fecha_bd ?>">
                                            <label for="datepicker" class="form-label">Fecha</label>
                                        </div>
                                        <div id="errorFecha" class="invalid-feedback mt-2" style="display: none;">
                                            Escribe una Fecha
                                        </div>
                                    </div>

                                    <div class="col-md-2">
                                        <select id="tipo_venta" name="tipo_venta" class="form-select" required>
                                            <option value="">Tipo de Venta</option>
                                            <option value="credito">Crédito</option>
                                            <option value="contado">Contado</option>
                                        </select>
                                    </div>

                                    <div class="col-md-4" id="plazo_credito_container" style="display:none;">
                                        <span>Plazo de Crédito (días)</span>
                                        <span>
                                            <input type="number" style="width: 80px;" id="plazo_credito" name="plazo_credito" class="" min="1" max="120" value="30">
                                        </span>
                                    </div>

                                    <div class="col-4">
                                        <div class="form-group">
                                            <div class="form-outline">
                                                <input type="text"
                                                    id="almacenInput"
                                                    class="form-control"
                                                    list="listAlmacenes"
                                                    placeholder="Selecciona un almacén..."
                                                    autocomplete="off" />
                                                <label for="almacenInput" class="form-label">Almacén</label>
                                            </div>

                                            <datalist id="listAlmacenes">
                                                <?php foreach ($almacenes as $almacen): ?>
                                                    <option value="<?= htmlspecialchars($almacen['nombre']) ?>"
                                                        data-id="<?= (int)$almacen['id'] ?>"></option>
                                                <?php endforeach; ?>
                                            </datalist>

                                            <small id="errorAlmacen" class="text-danger" style="display:none;">
                                                Debes seleccionar un almacén válido.
                                            </small>
                                        </div>
                                    </div>
                                </div>

                                <div class="row gy-3 mt-3">
                                    <div class="col-6">
                                        <div class="form-group">
                                            <div class="form-outline">
                                                <input type="text"
                                                    id="clienteInput"
                                                    class="form-control"
                                                    list="clientes"
                                                    placeholder="Selecciona un cliente..."
                                                    autocomplete="off" />
                                                <label for="clienteInput" class="form-label">Cliente</label>
                                            </div>

                                            <datalist id="clientes">
                                                <?php foreach ($clientes as $cliente): ?>
                                                    <option value="<?= htmlspecialchars($cliente['nombre']) ?>"
                                                        data-id="<?= (int)$cliente['id'] ?>"></option>
                                                <?php endforeach; ?>
                                            </datalist>

                                            <small id="errorCliente" class="text-danger" style="display:none;">
                                                Debes seleccionar un cliente válido.
                                            </small>
                                        </div>
                                    </div>

                                    <div class="col-6">
                                        <div class="col-12 col-md-12">
                                            <div class="form-outline">
                                                <input type="text" name="direccion_envio" class="form-control" id="domicilio" autocomplete="off">
                                                <label for="direccion_envio" class="form-label">Domicilio de entrega</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>

                            <div class="card-body">
                                <div class="col-12">
                                    <div class="row">
                                        <div class="col-4">
                                            <button type="button" class="btn btn-primary btn-sm" onclick="FnAgregarModal();">Agregar Producto</button>
                                        </div>
                                        <div class="col-4">
                                            <button type="submit" class="btn btn-success btn-sm">Guardar</button>
                                        </div>
                                        <div class="col-4 text-end">
                                            <h3><b> Total:</b> <span id="totalPedido">$0.00</span></h3>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-3">
                                    <table id="tablaProductos" class="table table-bordered table-striped">
                                        <thead class="bg-dark text-white">
                                            <tr>
                                                <th class="text-center" style="width: 100px;">Cantidad</th>
                                                <th>Producto</th>
                                                <th class="text-center" style="width: 150px;">Precio Unitario</th>
                                                <th class="text-center" style="width: 150px;">Total</th>
                                                <th class="text-center" style="width: 50px;">Borrar</th>
                                            </tr>
                                        </thead>
                                        <tbody id="tbodyProductos">
                                            <tr id="filaVacia">
                                                <td colspan="5">Ningún producto agregado</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- ✅ Campos que necesita el backend -->
                            <input type="hidden" name="id_cliente" id="clienteId">
                            <input type="hidden" name="id_almacen" id="almacenId"><!-- ✅ NUEVO -->
                            <input type="hidden" name="id_vendedor" id="vendedorId" value="0"><!-- ✅ preparado -->
                            <input type="hidden" name="productos" id="productos" value="">
                            <input type="hidden" name="total" id="totalInput">

                            <!-- ✅ Opcional: si no lo mandas, backend asume 1; pero lo dejo explícito -->
                            <input type="hidden" name="procesar" id="procesar" value="1">

                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

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
                            <th>Producto</th>
                            <th class="text-center">Categoría</th>
                            <th class="text-end">Cantidad</th>
                            <th class="text-end">Precio Venta</th>
                            <th class="text-center">Agregar</th>
                        </tr>
                    </thead>
                    <tbody>
                        <div id="productosalmacen"></div>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.inputmask/5.0.6/jquery.inputmask.min.js"></script>
<script src="js/remisiones-altas.js"></script>