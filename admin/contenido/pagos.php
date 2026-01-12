<?php
$semanaActual = date('W');

?>

<!-- Content Header (Page header) -->
<div class="ms-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="inicio">inicio</a></li>
            <li class="breadcrumb-item active" aria-current="page">Pagos </li>
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
                        <div class="row">
                            <div class="col-8">
                                <a type="button" class="btn btn-secondary btn-fixed mt-2 mt-md-0" href="clientes"><i class="fa fa-users"></i> Clientes </a> &nbsp;
                                <a type="button" class="btn btn-secondary btn-fixed mt-2 mt-md-0" href="pedidos"><i class="fas fa-file-invoice"></i> Pedidos </a> &nbsp;
                                <a type="button" class="btn btn-secondary btn-fixed mt-2 mt-md-0" href="facturas"><i class="fas fa-file-invoice"></i> Facturas </a> &nbsp;
                                <a type="button" class="btn btn-secondary btn-fixed mt-2 mt-md-0" href="remisiones"><i class="fas fa-file-invoice"></i> Remisiones </a> &nbsp;
                                <a type="button" class="btn btn-info btn-fixed mt-2 mt-md-0" href="pagos-altas"><i class="fas fa-plus"></i> Pagos </a> &nbsp;
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
                        <table id="tablaFacturas" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>No. Factura</th>
                                    <th>Fecha</th>
                                    <th>Folio</th>
                                    <th class="text-end">Monto</th>
                                    <th>Estatus</th>
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

<!-- Modal -->
<div class="modal fade" id="modalAltaFacturas" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content ">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Agregar Factura <small> Semana actual: <?php echo $semanaActual; ?></small></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" id="cerrarAltasX"></button>
            </div>
            <form method="post" action="" id="formAltas">
                <div class="modal-body">
                    <div class="row col-12">
                        <div class="col-6">
                            <div class="row col-12 mb-3">
                                <div class="col-4">
                                    <div class="form-group form-outline ms-1">
                                        <input type="text" id="fact_serie" class="form-control" name="fact_serie" />
                                        <label class="form-label" for="fact_serie">Serie de la Factura</label>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="form-group form-outline ms-2">
                                        <input type="text" id="fact_no" name="fact_no" class="form-control mayusculas" />
                                        <label class="form-label" for="fact_no">Número de la Factura</label>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="form-group form-outline ms-1">
                                        <input type="date" id="fact_fecha" class="form-control" name="fact_fecha" />
                                        <label class="form-label" for="fact_fecha">Fecha de la Factura</label>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-3 me-3">
                                <div id="listacontratos" class="form-outline form-group">
                                    <input type="text" id="fact_folio" onblur="buscaCliente();" class="form-control mayusculas" name="fact_folio" />
                                    <label class="form-label" for="fact_folio">Folio</label>
                                </div>
                            </div>
                            <div class="mt-3 me-3">
                                <div id="listacontratos" class="form-outline form-group">
                                    <input type="text" id="fact_cliente" class="form-control mayusculas" name="fact_cliente" />
                                    <label class="form-label" for="fact_cliente">Cliente</label>
                                </div>
                            </div>
                            <div class="row col-12 mt-3">
                                <div class="col-4">
                                    <div class="form-group form-outline ">
                                        <input type="text" id="fact_monto" name="fact_monto" onblur="PorcentajeRetencion();" class="form-control monedas text-end" />
                                        <label class="form-label" for="fact_monto">Monto total $</label>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="form-group form-outline ">
                                        <input type="text" id="fact_amortizacion_anticipo" name="fact_amortizacion_anticipo" class="form-control monedas text-end" />
                                        <label class="form-label" for="fact_amortizacion_anticipo">Amortización anticipo$</label>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="form-group form-outline ">
                                        <input type="text" id="fact_retencion" name="fact_retencion" class="form-control text-end" />
                                        <label class="form-label" for="fact_retencion" id="porcentajeRetencion">Retención fondo de garantia </label>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-3">
                                <div class="form-group form-outline ">
                                    <input type="number" maxlength="2" max="52" min="1" id="fact_tentativa_cobro" name="fact_tentativa_cobro" class="form-control" value="<?php echo $semanaTentativa; ?>" />
                                    <label class="form-label" for="fact_tentativa_cobro">Semana tentativa de cobro</label>
                                </div>
                            </div>
                            <div class="mt-3">
                                <div class="form-group form-outline ">
                                    <textarea class="form-control" name="fact_descripcion" id="fact_descripcion" cols="30" rows="8"></textarea>
                                    <label class="form-label" for="fact_descripcion">Descripción</label>
                                </div>
                            </div>
                        </div>
                        <div class="row col-6">
                            <div class="col-12">
                                <div id="montoContrato"></div>
                            </div>

                        </div>
                    </div>
                </div>

                <div class="modal-footer text-start">
                    <button type="button" class="btn btn-secondary" id="cerrarAltas" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                    <input type="hidden" name="fact_id_usuario" value="<?php echo $_SESSION['id_user']; ?>">
                    <textarea name="archivo" id="archivo" style="visibility:hidden"></textarea>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function guardaFactura() {
        $.ajax({
            url: "ajax/facturas/alta-factura.php",
            type: "POST",
            data: {
                id_cliente: id_cliente
            },
            success: function(data) {
                console.log(data);
                if (data == 1) {
                    console.log("Fallo el server");
                } else {
                    // Recarga la tabla 
                    var table = $('#tablaFacturas').DataTable();
                    table.ajax.reload(function(json) {
                        $('#tablaFacturas').val(json.lastInput);
                    });

                    //     alertify.alert('Valor modificado').set('labels', {ok:'Cerrar'}).setHeader('Aviso'); 
                }
            }
        }); /* End ajax */
    };



    $(document).ready(function() {


        $("#altaFactura").click(
            function() {
                $("#modalAltaFacturas").modal('show');
                /*
                $('#modalAltaFacturas').modal('toggle');
                $('#modalAltaFacturas').modal('show');
                $('#modalAltaFacturas').modal('hide');
                */
            }
        );

        $('#modalAltaFacturas').on('shown.bs.modal', function() {
            $('#fact_serie').focus(); // focus al primer input
        })


        $("#cerrarAltasX").click(
            function() {
                $('#modalAltaFacturas').modal('toggle');
            }
        );
        $("#cerrarAltas").click(
            function() {
                $('#modalAltaFacturas').modal('toggle');
            }
        );

        $.validator.addMethod(
            "regex",
            function(value, element, regexp) {
                if (regexp.constructor != RegExp)
                    regexp = new RegExp(regexp);
                else if (regexp.global)
                    regexp.lastIndex = 0;
                return this.optional(element) || regexp.test(value);
            },
            "El Campo debe de tener solo letras y espacios"
        );


        /* Validación de formulario */
        $("#formAltas").validate({
            rules: {
                fact_no: {
                    required: true,
                    minlength: 5,
                    maxlength: 20,
                    remote: {
                        url: "ajax/facturas/busca-factura.php",
                        type: "POST",
                        data: {
                            factura: function() {
                                return $('#fact_no').val();
                            }
                        },
                        dataFilter: function(data) {
                            //    console.log(data);               
                            var json = JSON.parse(data);
                            //    console.log(json);
                            if (json.existe === "true" || json.existe === true) {
                                return '"true"';
                            } else {
                                return '"El número de factura ya existe"';
                            }
                        }
                    }
                },
                fact_monto: {
                    required: true,
                    minlength: 1,
                    maxlength: 30
                },
                fact_amortizacion_anticipo: {
                    required: true
                },
                fact_folio: {
                    required: true
                },
                fact_fecha: {
                    required: true
                },
                archivo: {
                    required: true
                }
            },
            messages: {
                fact_no: {
                    required: "Campo obligatorio",
                    minlength: "El mínimo de caracteres es 5",
                    maxlength: "El número máximo de caracteres es 20"
                },
                fact_monto: {
                    required: "Campo obligatorio",
                    minlength: "El monto mínimo es de 1",
                    maxlength: "El monto máximo es de 999,999,999,999.99"
                },
                fact_folio: {
                    required: "Campo obligatorio"
                },
                fact_fecha: {
                    required: "Campo obligatorio"
                },
                archivo: {
                    required: function() {
                        alertify.alert('Campo Obligatorio', 'Tienes que adjuntar por lo menos un archivo', function() {})
                    }
                }
            },

            errorElement: 'span',
            errorPlacement: function(error, element) {
                error.addClass('invalid-feedback');
                error.addClass('btn btn-danger btn-sm text-white');
                element.closest('.form-group').append(error);
            },
            highlight: function(element, errorClass, validClass) {
                $(element).addClass('is-invalid');

            },
            unhighlight: function(element, errorClass, validClass, error) {
                $(element).removeClass('is-invalid');
                $(element).addClass('is-valid');
                $(error).removeClass('btn btn-danger btn-sm text-white d-flex justify-content-end');
            },

            submitHandler: function(form) {
                //submit form
                event.preventDefault();
                //   console.log('Entro');

                var datos = $("#formAltas").serialize();

                alertify.confirm('Aviso..!', '¿Deseas guardar la factura?',
                    function() {

                        $.ajax({
                            type: "POST",
                            url: "ajax/facturas/guarda-factura.php",
                            data: datos,
                            success: function(data) {
                                console.log(data);
                                if (data == 1) {
                                    alert("Fallo el server");
                                } else {
                                    $("#formAltas")[0].reset(); //Limpiar el formulario
                                    //  location.href = "flujo-de-efectivo-contratos/todos/todos";
                                    //   location.reload();    
                                    var table = $('#tablaFacturas').DataTable();

                                    table.ajax.reload(function(json) {
                                        $('#tablaFacturas').val(json.lastInput);
                                    });
                                    //   $("#modalAltaFacturas .cerrarAltas").click() // cierra modal
                                    $('#modalAltaFacturas').modal('toggle');
                                    alertify.alert('Aviso', 'Se han guardado los cambios', function() {
                                        alertify.success('Aceptar');
                                    });
                                }

                            }
                        }); /* End ajax */

                    },
                    function() {
                        alertify.error('Cancel')
                    }
                ).set('labels', {
                    ok: 'Si',
                    cancel: 'No'
                });

            }

        });


        $('#tablaFacturas').DataTable({
            ajax: 'ajax/facturas/tabla-facturas.php',
            ordering: true,
            fixedHeader: true,
            dom: "<'row'<'col-sm-6'l><'col-sm-6'p>>" +
                "<'row'<'col-sm-12'tr>>" +
                "<'row'<'col-sm-6'l><'col-sm-6'p>>",
            initComplete: function(settings, json) {
                $('#custom_length').appendTo('body'); //jQuery for moving elements around
            },
            language: {
                url: "assets/datatables/Spanish.json",
                Search: '<i class="fa fa-search" aria-hidden="true"></i> Buscar'
            },
            responsive: true
        });

        var oTable = $('#tablaFacturas').DataTable();
        $('#search').keyup(function() {
            oTable.search($(this).val()).draw();
        })
    });

    /*
        function cambiaValor(id_documento,valor,campo){
          
            alertify.confirm('¿Deseas modificar este valor?', '¿Se modificará el valor?', 
                function(){                        
                    $.ajax({                    
                        url: "ajax/clientes/definicion-documentos-modifica-comportamiento.php",
                        type: "POST",
                        data: {id_documento:id_documento,valor:valor,campo:campo},
                        success: function(data)
                        {              
                           console.log(data);
                            if(data==1){
                                console.log("Fallo el server");
                            }else{
                                // Recarga la tabla 
                                var table = $('#documentos').DataTable();
                                table.ajax.reload( function ( json ) {
                                    $('#documentos').val( json.lastInput );
                                } ); 

                                alertify.alert('Valor modificado').set('labels', {ok:'Cerrar'}).setHeader('Aviso'); 
                            }
                        }
                    }); /* End ajax */
    /*                              
            },
            function(){ alertify.error('Cancel')}

        ).set('labels', {ok:'Si', cancel:'No'}).setHeader('Aviso');    

*/


    //    }; 
</script>