<?php
$con = "SELECT razon_social, sucursales FROM cat_clientes WHERE id=" . $cat;
$rs = $clsConsulta->consultaGeneral($con);
foreach ($rs as $v => $val) {
  $razon_social = $val['razon_social'];
  $sucursales = $val['sucursales'];
  $id_cliente = $val['sucursales'];
}
?>
<!-- Content Header (Page header) -->
<div class="ms-5">
  <nav aria-label="breadcrumb">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="inicio">Inicio</a></li>
      <li class="breadcrumb-item"><a href="clientes">Clientes</a></li>
      <li class="breadcrumb-item"><a href="clientes-lista/todos">Catálogo de clientes</a></li>
      <li class="breadcrumb-item active" aria-current="page"> Correos de <b><?php echo $razon_social; ?></b></li>
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
            <div class="row justify-content-between">
              <div class="col-6">
                <ul class="nav nav-pills">
                  <li class="nav-item">
                    <a style="cursor:pointer" class="nav-link text-dark active" data-bs-toggle="modal" data-bs-target="#modalaltas"><i class="fa fa-plus"></i> Agregar </a>
                  </li>
                </ul>
              </div>
              <div class="col-6">
                <ul class="nav nav-pills">
                  <li class="nav-item">
                    <div class="input-group rounded">
                      <input type="search" id="search" class="form-control rounded mt-3 ms-3" placeholder=" Buscar" aria-label="Buscar" />
                    </div>
                  </li>
                </ul>
              </div>
            </div>
          </div>
          <!-- /.card-header -->
          <div class="card-body">
            <table id="correos" class="table table-bordered table-striped">
              <thead>
                <tr>
                  <th>NOMBRE</th>
                  <th>CORREO</th>
                  <th>SUCURSAL</th>
                  <?php
                  $conp = "SELECT * FROM definicion_documentos WHERE id_cliente=" . $cat;
                  $rsp = $clsConsulta->ConsultaGeneral($conp);
                  if ($clsConsulta->numrows > 0) {
                    foreach ($rsp as $vp => $valp) {
                      $documentosLista[] = $valp['id'];
                      echo '<th class="text-center">' . $valp['nombre'] . '</th>';
                    }
                  }
                  ?>
                  <th class="text-center">Borrar</th>
                </tr>
              </thead>
              <tbody>
                <?PHP
                $con = "SELECT * FROM clientes_correos WHERE cliente_id=" . $cat;
                //        echo $con.'<br>';
                $rs = $clsConsulta->consultaGeneral($con);
                if ($clsConsulta->numrows > 0) {
                  foreach ($rs as $v => $val) {
                    $id = $val['id'];
                    $titulo = $val['titulo'];
                    $nombre = $val['nombre'];
                    $correo = $val['correo'];
                    $sucursal = $val['sucursal'];
                ?>
                    <tr>
                      <td><?PHP echo $titulo . ' ' . $nombre; ?></td>
                      <td><?PHP echo $correo; ?></td>

                      <td>
                        <?php
                        if ($sucursal != '') {
                          echo $sucursal;
                        }
                        ?>

                      </td>

                      <?php
                      foreach ($documentosLista as $dl) {
                        $conp = "SELECT * FROM clientes_correos_documentos WHERE id_documento=" . $dl;
                        $rsp = $clsConsulta->ConsultaGeneral($conp);
                        if ($clsConsulta->numrows > 0) {
                          foreach ($rsp as $vp => $valp) {
                            $id_correo = $valp['id_correo'];
                            $estatus = $valp['estatus'];
                            $id_proceso = $valp['id_documento'];
                            $tipo_correo = $valp['tipo_correo'];
                            if ($estatus == 1) {
                              echo '<td class="text-center">
                                          <div id="proceso' . $id_proceso . $id . '">
                                          <i style="cursor:pointer" title="Modifica el envío de correo" class="fa-solid fa-envelope-circle-check text-success" onclick="cambiaestatus(' . $id_correo . ',' . $id_proceso . ',' . $estatus . ',' . $tipo_correo . ');"></i>
                                          </div>                                        
                                          <div id="tipomail' . $id_proceso . $id . '">
                                            <select class="btn btn-secondary btn-sm" id="selecttipoenvio' . $id_proceso . $id . '" onchange="modificartipocorreo(' . $id_correo . ',' . $id_proceso . ',' . $cat . ');">';
                              if ($tipo_correo == 1) {

                                echo '<option value="1" selected>Destinatario</option>
                                              <option value="2">CC</option>
                                              <option value="3">CCO</option>';
                              }
                              if ($tipo_correo == 2) {
                                echo '<option value="1" >Destinatario</option>
                                              <option value="2" selected>CC</option>
                                              <option value="3">CCO</option>';
                              }
                              if ($tipo_correo == 3) {
                                echo '<option value="1">Destinatario</option>
                                              <option value="2">CC</option>
                                              <option value="3" selected>CCO</option>';
                              }
                              echo '
                                            </select>
                                          </div>
                                          </td>';
                            } else {
                              echo '<td class="text-center">
                                          <div id="proceso' . $id_proceso . $id . '">
                                          <i style="cursor:pointer" title="Modifica el envío de correo"  class="fa fa-envelope fa-lg text-danger" onclick="cambiaestatus(' . $id_correo . ',' . $id_proceso . ',' . $estatus . ',' . $tipo_correo . ');"></i>
                                          </div>
                                          </td>';
                            }
                          }
                        } else {
                          echo '<td class="text-center">
                                          <div id="proceso' . $dl . $id . '">
                                          <i style="cursor:pointer" title="Modifica el envío de correo"  class="fa fa-envelope fa-lg text-danger" onclick="cambiaestatus(' . $id_correo . ',' . $dl . ',' . $estatus . ',' . $tipo_correo . ');"></i>
                                          </div>
                                          </td>';
                        }
                      }
                      ?>

                      <td class="text-center"><a data-bs-toggle="modal" data-bs-target="#borrar" style="cursor:pointer" class="btn btn-danger btn-sm" onClick="borrar(<?PHP echo $id; ?>)"> <i class="fas fa-trash fa-lg ps-2 pe-2"></i> </a></td>
                    </tr>
                <?PHP
                  }
                }
                ?>


              </tbody>
            </table>
          </div>
          <!-- /.card-body -->
        </div>
        <!-- /.card -->
      </div>
      <!-- /.col -->
    </div>
    <!-- /.row -->
  </div>
  <!-- /.container-fluid -->
</section>

<!--MODAL ALTAS-->
<div class="modal fade" id="modalaltas" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-md">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Agregar Correo</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form method="post" id="formCorreos" action="">
        <div class="modal-body">
          <div class="row">
            <div class="col-12">

              <div class="form-group">
                <?php
                if ($sucursales != '') {
                  $sucursal = explode(",", $sucursales);
                  echo '<div class="mt-3 mb-3">';
                  echo 'Sucursales: <br>';
                  foreach ($sucursal as $suc) {
                    echo '<div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="sucursal" value="' . $suc . '" id="flexRadioDefault1">
                                    <label class="form-check-label radio-inline" for="sucursal">
                                    ' . $suc . '
                                    </label>
                                </div>
                                ';
                  }
                  echo '</div>';
                }
                ?>
              </div>
              <div class="form-group">
                <label>Título</label>
                <input class="form-control" name="titulo" list="listtitulo" id="exampleDataList" placeholder="Título...">
                <datalist id="listtitulo">
                  <option value="Arq.">
                  <option value="Lic.">
                  <option value="Mtr.">
                  <option value="Dr.">
                  <option value="Ing.">
                  <option value="Tec.">
                </datalist>
              </div>
              <div class="form-group">
                <label>Nombre</label>
                <input type="text" class="form-control mayusculas" name="nombre" placeholder="Nombre del titular del correo">
              </div>
              <div class="form-group">
                <label>Correo</label>
                <input type="text" class="form-control minusculas" style="text-transform: lowercase ;" onkeyup="javascript:this.value=this.value.toLowerCase();" name="correo" id="correo">
              </div>
              <!--div class="form-group">
                    <label>Tipo de correo</label>
                    <select name="tipo_correo" id="tipo_correo" class="form-control">
                          <option value="">Selecciona un tipo</option>
                          <option value="Destinatario">Destinatario</option>
                          <option value="CC">CC</option>
                          <option value="CCO">CCO</option>
                    </select>
              </div-->
            </div>

          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
          <button type="submit" class="btn btn-primary"> Guardar </button>
          <input type="hidden" name="cliente_id" id="cliente_id" value="<?php echo $cat; ?>">
          <input type="hidden" name="accion" value="ALTA">
          <input type="hidden" name="url" value="clientes-correos/<?php echo $cat; ?>">
        </div>
      </form>
    </div>
  </div>
</div>

<!--MODAL BORRAR-->
<div class="modal fade" id="borrar" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-md" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Borrar Correo</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <!-- FORM  -->
      <form action="abc/borrarClientesCorreos" method="post">
        <div class="modal-body">
          <h5>&iquest; Esta seguro de que quiere borrar este correo ?</h5>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-danger"> Borrar </button>
          <input type="hidden" name="fin" value="baja">
          <input type="hidden" name="tabla" value="clientes_correos">
          <input type="hidden" name="url" value="clientes-correos/<?php echo $cat; ?>">
          <input type="hidden" name="id" id="idborrar">
        </div>
      </form>
      <!-- . FORM  -->
    </div>
  </div>
</div>

<!-- page script -->
<script type="text/javascript">
  function borrar(idb) {
    document.getElementById('idborrar').value = idb;
  };

  function pasavalortipo(id) {
    $("#idtipoenvio").val(id);
  };

  function verificaTipoCorreo() {
    $.ajax({
      type: "POST",
      url: "ajax/clientes/verifica-tipo-correo.php",
      data: {
        id: id,
        idproceso: idproceso,
        tipo_correo: function() {
          return $("#selecttipoenvio" + idproceso + id).val();
        }
      },
      success: function(data) {
        //   console.log(data);
        if (data == 1) {
          console.log("Fallo el server");
        } else {
          $("#tipomail" + idproceso + id).html(data);
        }
      }
    }); /* End ajax */
  }

  function modificartipocorreo(id, idproceso, id_cliente) {
    alertify.confirm('¿Deseas modificar el Tipo de envío del correo?', '¿Deseas modificar el Tipo de envío del correo?',
      function() {
        $.ajax({
          type: "POST",
          url: "ajax/clientes/valida-tipo-envio-correo.php",
          data: {
            id: id,
            idproceso: idproceso,
            id_cliente: id_cliente,
            tipo_correo: function() {
              return $("#selecttipoenvio" + idproceso + id).val();
            }
          },
          dataFilter: function(data) {
            //  console.log(data);
            var json = JSON.parse(data);
            console.log(json);
            if (json.valor === "true" || json.valor === true) {
              $.ajax({
                type: "POST",
                url: "ajax/clientes/modifica-tipo-envio-correo.php",
                data: {
                  id: id,
                  idproceso: idproceso,
                  id_cliente: id_cliente,
                  tipo_correo: function() {
                    return $("#selecttipoenvio" + idproceso + id).val();
                  }
                },
                success: function(data) {
                  //   console.log(data);
                  if (data == 1) {
                    console.log("Fallo el server");
                  } else {
                    $("#tipomail" + idproceso + id).html(data);
                  }
                }

              }); /* End ajax */
            } else {
              alertify.alert()
                .setting({
                  'label': 'Cerrar',
                  'message': 'Tiene que existir por lo menos un destinatario',
                }).show().setHeader('Aviso ');

              $("#tipomail" + idproceso + id).html('<div id="tipomail' + idproceso + '">' +
                '<select class="btn btn-secondary btn-sm" id="selecttipoenvio' + idproceso + '"' +
                ' onchange="modificartipocorreo(' + idproceso + ',' + id + ',<?php echo $cat; ?>);">' +
                '<option value="1" selected>Destinatario</option>' +
                '<option value="2">CC</option>' +
                '<option value="3">CCO</option>' +
                '</select></div>');

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
    }).setHeader('Modifica Tipo de Envío de Correo ');

  };


  function cambiaestatus(id, id_proceso, estatus, tipo_correo) {
    if (estatus == 1) {
      var est = "No envíe"
    } else {
      var est = "Envíe"
    };
    alertify.confirm('¿Deseas que este proceso ' + est + ' el correo?', '¿Deseas Cambiar la acción?',
      function() {
        $.ajax({
          type: "POST",
          url: "ajax/clientes/modifica-estatus-correo.php",
          data: {
            id: id,
            id_proceso: id_proceso,
            estatus: estatus,
            tipo_correo: tipo_correo,
            id_cliente: <?php echo $cat; ?>
          },
          success: function(data) {
            console.log(data);
            if (data == 1) {
              console.log("Fallo el server");
            } else {
              $("#proceso" + id_proceso + id).html(data);
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
    }).setHeader('Modifica Enviar Correo ');

  }


  $(document).ready(function() {

    $(".check_todos").click(function(event) {
      if ($(this).is(":checked")) {
        $("input[type=checkbox]").prop("checked", true);
      } else {
        $("input[type=checkbox]").prop("checked", false);
      }
    });

    $('#correos').dataTable({
      ordering: false,
      pageLength: 25,
      dom: "<'row'<'col-sm-10'l><'col-sm-2'p>>" +
        "<'row'<'col-sm-12'tr>>" +
        "<'row'<'col-sm-10'l><'col-sm-2'p>>",
      initComplete: function(settings, json) {
        $('#custom_length').appendTo('body'); //jQuery for moving elements around
      },
      language: {
        url: "assets/datatables/Spanish.json",
        sSearch: '<i class="fa fa-search" aria-hidden="true"></i> Buscar'
      }

    });
    var oTable = $('#correos').DataTable();
    $('#search').keyup(function() {
      oTable.search($(this).val()).draw();
    })


    $.validator.addMethod(
      "regex",
      function(value, element, regexp) {
        if (regexp.constructor != RegExp)
          regexp = new RegExp(regexp);
        else if (regexp.global)
          regexp.lastIndex = 0;
        return this.optional(element) || regexp.test(value);
      },
      "Revisa que el RFC tenga un formato valido"
    );


    $("#formCorreos").validate({
      rules: {
        nombre: {
          required: true
        },
        sucursal: {
          required: true
        },

        correo: {
          required: true,
          regex: /^[\w-\.]+@([\w-]+\.)+[\w-]{2,4}$/

        }
      },
      messages: {
        nombre: {
          required: "Campo obligatorio"
        },
        sucursal: {
          required: "Campo obligatorio Selecciona una sucursal"
        },
        correo: {
          required: "Campo obligatorio",
          regex: 'Escribe un correo valido'
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
        //  event.preventDefault();
        //   console.log('Entro');

        var datos = $("#formCorreos").serialize();

        alertify.confirm('Aviso..!', '¿Deseas Guardar los cambios?',
          function() {
            //    alertify.success('Ok') 
            var archivos = $("#archivo1").val();
            $.ajax({
              type: "POST",
              url: "ajax/clientes/guardacorreos.php",
              data: datos,
              success: function(data) {
                //   console.log(data);
                if (data == 1) {
                  console.log("Fallo el server");
                } else {
                  var closable = alertify.alert().setting('closable');
                  location.reload();
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
        /* End alertify */


      }
    }, );

  });
  /*   
     $.ajax({
       url: "ajax/clientes/presenta-correos.php",
       type: "POST",    
       data: {
         id: <?php //  echo $cat; 
              ?>
       },
       success: function( result ) {
         $( "#presenta" ).html( result);
       }
     }); 
     */
</script>