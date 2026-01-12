<!-- Content Header (Page header) -->
<div class="ms-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="inicio">Inicio</a></li>
            <li class="breadcrumb-item"><a href="utilerias">Utilerias</a></li>
            <li class="breadcrumb-item active" aria-current="page">Almacenes </li>
        </ol>
    </nav>
</div>
<!-- /.content-header -->

<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <div class="row col-12">
                            <div class="col-8">
                                <a href="almacen-altas" class="btn btn-info"> <i class="fa-solid fa-plus"></i> Agregar Alamacen</a>
                            </div>
                            <div class="col-4">
                                <div class="form-outline" data-mdb-input-init>
                                    <input type="text" id="search" class="form-control" />
                                    <label class="form-label" for="form12">Buscar</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- /.card-header -->
                    <div class="card-body">
                        <table id="tablaAlmacenes" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th class="text-center">Almacén </th>
                                    <th class="text-center">Domicilio</th>
                                    <th class="text-center">Mapa</th>
                                    <th class="text-center">Editar</th>
                                    <th class="text-center">Estatus</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Modal Altas-->
<div class="modal fade" id="modalAltas" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Alta de Almacén</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" onclick="cerrarModalAltas();"></button>
            </div>
            <form method="post" action="" id="formEmpresas"> <!-- Form -->
                <div class="modal-body">
                    <div class="row">
                        <div class="form-group  g-3">
                            <div class="form-outline">
                                <input type="text" name="almacen" id="almacen" class="form-control " />
                                <label class="form-label" for="almacen">Nombre del Almacén</label>
                            </div>
                        </div>
                        <div class="form-group  g-3">
                            <div class="form-outline">
                                <input type="text" name="calle" id="calle" class="form-control " />
                                <label class="form-label" for="calle">Calle</label>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="row">
                                <div class="col-6 g-3">
                                    <div class="form-outline">
                                        <input type="text" name="num_ext" id="num_ext" class="form-control " />
                                        <label class="form-label" for="num_ext">Número Exterior</label>
                                    </div>
                                </div>
                                <div class="col-6 g-3">
                                    <div class="form-outline">
                                        <input type="text" name="num_int" id="num_int" class="form-control " />
                                        <label class="form-label" for="num_int">Número Interior</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group  g-3">
                            <div class="form-outline">
                                <input type="text" name="cp" id="cp" class="form-control" />
                                <label class="form-label" for="cp">Código Postal</label>
                            </div>
                        </div>
                        <div class="form-group g-3">

                            <select id="estado_id" name="estado_id" class="form-select">
                                <option value="" disabled selected>Selecciona un Estado</option>
                                <?php
                                $con = "SELECT * FROM estados  ORDER BY nombre ASC";
                                $rs = $clsConsulta->consultaGeneral($con);
                                foreach ($rs as $v => $val) {

                                    echo '<option value="' . $val['id'] . '">' . $val['nombre'] . '</option>';
                                }
                                ?>
                            </select>

                        </div>
                        <div class="form-group g-3">

                            <select name="municipio_id" id="municipio_id" class="form-select">
                                <option value="" disabled selected>Selecciona un Municipio</option>
                                <?php
                                $con = "SELECT * FROM municipios WHERE estado_id=11 ORDER BY nombre ASC";
                                $rs = $clsConsulta->consultaGeneral($con);
                                foreach ($rs as $v => $val) {
                                    echo '<option value="' . $val['id'] . '">' . $val['nombre'] . '</option>';
                                }
                                ?>
                            </select>

                        </div>

                        <div class="form-group g-3">
                            <div class="form-outline">
                                <input type="text" name="mapa" id="mapa" class="form-control " />
                                <label class="form-label" for="mapa">Link Mapa</label>
                            </div>
                        </div>

                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" onclick="cerrarModalAltas();">Cerrar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                    <input type="hidden" name="id_usuario" value="<?php echo $_SESSION['id_user']; ?>">
                </div>
            </form> <!-- /. Form -->
        </div>
    </div>
</div>


<!-- ModalcambiaEstatus -->
<div class="modal fade" id="ModalEstatus" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Cambio de Estatus de Almacén </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" onclick="cerrarModalEstatus();"></button>
            </div>
            <div class="modal-body">
                <div class="row col-12">

                    <span id="btnEstado" class="text-center"></span>

                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" onclick="cerrarModalEstatus();">Cerrar</button>
                <button type="button" class="btn btn-primary" onclick="fnModificarEstatus();">Modificar</button>
                <input type="hidden" name="id" id="idAlmacen">
                <input type="hidden" name="estatus" id="estatus">
            </div>
        </div>
    </div>
</div>


<script>
    function cerrarModalEstatus() {
        $("#ModalEstatus").modal('toggle');
    }

    function ModalcambiaEstatus(id, almacen, estatus) {
        $("#ModalEstatus").modal('show');
        $("#idAlmacen").val(id);
        $("#estatus").val(estatus);
        if (estatus === 0) {
            var texto = 'Cambiar estatus de ' + almacen + ' a <b class="text-success">Activo</b> ';
        } else {
            var texto = 'Cambiar estatus de ' + almacen + ' a <b class="text-danger">Inactivo</b> ';
        }
        $("#btnEstado").html(texto);
    }

    function fnModificarEstatus() {
        const id = $("#idAlmacen").val();
        var estatus = $("#estatus").val(); // Obtiene el valor de #estatus y lo asigna a la variable

        if (estatus == 1) {
            estatus = 0; // Si el valor es 1, asigna 0 a la variable estatus
        } else if (estatus == 0) {
            estatus = 1; // Si el valor es 0, asigna 1 a la variable estatus
        }
        $.ajax({
            url: "ajax/almacenes/cambia-estatus.php",
            type: "POST",
            data: {
                id: id,
                estatus: estatus
            },
            success: function(result) {
                //console.log(result);
                // Recarga la tabla 
                var table = $('#tablaAlmacenes').DataTable();
                table.ajax.reload(function(json) {
                    $('#tablaAlmacenes').val(json.lastInput);
                });
                $("#ModalEstatus").modal('toggle');
            }
        });
    }


    $(document).ready(function() {

        $("#cerrarEstatus").click(
            function() {
                $('#ModalEstatus').modal('toggle');
            }
        );
        $("#cerrarEstatusX").click(
            function() {
                $('#ModalEstatus').modal('toggle');
            }
        );

        $('#tablaAlmacenes').DataTable({
            ajax: 'ajax/almacenes/tabla_almacenes.php',
            ordering: true,
            fixedHeader: false,
            paging: true, // Agregar paginación                                                
            dom: "<'row'<'col-sm-6'l><'col-sm-2 mb-3'><'col-sm-4'p>>" +
                "<'row'<'col-sm-12'tr>>" +
                "<'row'<'col-sm-6 mt-3'l><'col-sm-2 mt-3'><'col-sm-4 mt-3'p>>",

            language: {
                url: "assets/datatables/Spanish.json"
            },
            responsive: true
        });

        var oTable = $('#tablaAlmacenes').DataTable();
        $('#search').keyup(function() {
            oTable.search($(this).val()).draw();
        })


    });
</script>