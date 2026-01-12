<!-- admin/contenido/productos.php -->

<div class="ms-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="productos">Inicio</a></li>
            <li class="breadcrumb-item active" aria-current="page"> Productos</li>
        </ol>
    </nav>
</div>

<?php
// Empresa actual desde sesión
$idEmpresa = 0;
if (isset($_SESSION['id_empresa'])) $idEmpresa = (int)$_SESSION['id_empresa'];
elseif (isset($_SESSION['empresa'])) $idEmpresa = (int)$_SESSION['empresa'];

$whereEmpresa = "";
if ($idEmpresa > 0) {
    $whereEmpresa = " WHERE estatus = 1 AND id_empresa = {$idEmpresa}";
} else {
    $whereEmpresa = " WHERE estatus = 1";
}

// Almacenes (solo empresa actual)
$rsAlmacenes = $clsConsulta->consultaGeneral("SELECT id, almacen FROM cat_almacenes {$whereEmpresa} ORDER BY id ASC");
?>

<!-- Main content -->
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
                                        <a type="button" class="btn btn-info btn-fixed mt-2 mt-md-0 me-2" href="productos-altas"><i class="fa fa-plus"></i> Agregar </a>
                                    </div>
                                    <div class="col-12 col-md-2">
                                        <a type="button" class="btn btn-secondary btn-fixed mt-2 mt-md-0 me-2" href="lista-precios"><i class="fa-solid fa-file-invoice-dollar"></i> Lista de Precios </a>
                                    </div>
                                    <div class="col-12 col-md-2">
                                        <a type="button" class="btn btn-secondary btn-fixed mt-2 mt-md-0 me-2" href="almacen-entradas"><i class="fa-solid fa-warehouse"></i> Entradas </a>
                                    </div>
                                    <div class="col-12 col-md-2">
                                        <a type="button" class="btn btn-secondary btn-fixed mt-2 mt-md-0 me-2" href="salidas-almacen"><i class="fa-solid fa-warehouse"></i> Salidas </a>
                                    </div>
                                    <div class="col-12 col-md-2">
                                        <a type="button" class="btn btn-secondary btn-fixed mt-2 mt-md-0 me-2" href="ofertas"><i class="fas fa-tags"></i> Ofertas </a>
                                    </div>

                                    <div class="col-12 col-md-2">
                                        <div class="col-12">
                                            <div class="form-outline" data-mdb-input-init>
                                                <input type="text" id="search" class="form-control" />
                                                <label class="form-label" for="form12">Buscar</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Filtro almacén (sin "Todos", default primer almacén) -->
                            <div class="row col-12 mt-3">
                                <div class="col-12 col-md-3">
                                    <div class="form-outline mt-2 mt-md-0">
                                        <select id="filtroAlmacen" class="form-select">
                                            <?php
                                            if ($clsConsulta->numrows > 0) {
                                                $esPrimero = true;
                                                foreach ($rsAlmacenes as $alm) {
                                                    $idA = $alm['id'];
                                                    $nom = $alm['almacen'];

                                                    echo '<option value="' . $idA . '" ' . ($esPrimero ? 'selected' : '') . '>' . $nom . '</option>';
                                                    $esPrimero = false;
                                                }
                                            } else {
                                                echo '<option value="" selected disabled>Sin bodegas/almacenes disponibles</option>';
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                    <div class="card-body">
                        <table id="tablaProductos" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th class="text-center">Clave</th>
                                    <th class="text-center">Nombre</th>
                                    <th class="text-center">Disponible</th>
                                    <th class="text-center">U. de M.</th>
                                    <th class="text-center">Ver / Modificar</th>
                                    <th class="text-center">Editar</th>
                                    <th class="text-center">Borrar</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    </div>
</section>

<!--MODAL Cambiar de estatus-->
<div class="modal fade" id="modalEdditar" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Producto: Cambiar estatus</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" onclick="cerrarEstatus();"></button>
            </div>

            <div class="modal-body">
                <h5><span id="estatusActual"></span></h5>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" onclick="cerrarEstatus();">Cerrar</button>
                <button type="button" class="btn btn-primary" onclick="modificaEstatus();"> Modificar </button>
                <input type="hidden" name="estatus" id="estatusnuevo">
                <input type="hidden" name="fin" value="modificar">
                <input type="hidden" name="tabla" value="cat_productos">
                <input type="hidden" id="idCliente">
            </div>
        </div>
    </div>
</div>

<!-- Modal Ver Fotos -->
<div class="modal fade" id="modalVerfoto" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel"><span id="nombrep"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="foto"></div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    function fnVerFoto(fotoProducto, nombreProducto) {
        $("#foto").html('<img src="../img/productos/' + fotoProducto + '" class="img-fluid">');
        $("#nombrep").html(nombreProducto);
    }

    function fotos(id) {
        location.href = "productos-fotos/" + id;
    }

    function modificaEstatus() {
        const idCliente = $("#idCliente").val();
        const estatusnuevo = $("#estatusnuevo").val();

        $.ajax({
            url: "ajax/productos/modifica-estatus.php",
            type: "POST",
            data: {
                "idCliente": idCliente,
                "estatusnuevo": estatusnuevo
            },
            success: function(data) {
                if (data == 1) {
                    alert("Fallo el server");
                } else {
                    var table = $('#tablaProductos').DataTable();
                    table.ajax.reload(null, false);
                    $("#modalEdditar").modal('toggle');
                }
            }
        });
    }

    function cerrarEstatus() {
        $("#modalEdditar").modal('toggle');
    }

    function valorEstatus(id, estatus) {
        $("#modalEdditar").modal('show');

        $("#idCliente").val(id);
        if (estatus == 1) {
            $("#estatusActual").html('¿Quieres modificar el estatus a <b class="text-danger">Inactivo</b>?');
            estatus = 0;
        } else {
            $("#estatusActual").html('¿Quieres modificar el estatus a <b class="text-success">Activo</b>?');
            estatus = 1;
        }
        $("#estatusnuevo").val(estatus);
    }

    function borrar(id, producto) {
        alertify.confirm('Aviso..!', '¿Deseas borrar el producto ' + producto + '?',
            function() {
                $.ajax({
                    type: "POST",
                    url: "ajax/productos/borrar.php",
                    data: {
                        id: id
                    },
                    success: function(data) {
                        if (data == 1) {
                            alert("Fallo el server");
                        } else {
                            var table = $('#tablaProductos').DataTable();
                            table.ajax.reload(null, false);
                        }
                    }
                });
            },
            function() {
                alertify.error('Cancel')
            }
        ).set('labels', {
            ok: 'Si',
            cancel: 'No'
        });
    };

    function editar(id) {
        location.href = "productos-editar/" + id;
    };

    $(document).ready(function() {

        var table = $('#tablaProductos').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: 'ajax/productos/tabla-productos.php',
                type: 'GET',
                data: function(d) {
                    d.id_almacen = $('#filtroAlmacen').val();
                }
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
                targets: [4, 5, 6],
                orderable: false
            }],
        });

        $('#search').keyup(function() {
            table.search($(this).val()).draw();
        });

        $('#filtroAlmacen').change(function() {
            table.ajax.reload();
        });
    });
</script>