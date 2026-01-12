<style>
    .error{
        color: red;
    }
    input.error{
        border: 1px solid red;
    }
    
</style>
<!-- Content Header (Page header) -->
<div class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h6 class="m-0"><i class="fa fa-user"></i> Usuario / Alta</h6>
      </div><!-- /.col -->
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="inicio">Inicio</a></li>
          <li class="breadcrumb-item"><a href="usuarios">Usuarios</a></li>
            <li class="breadcrumb-item active">Alta</li>
        </ol>
      </div><!-- /.col -->
    </div><!-- /.row -->
  </div><!-- /.container-fluid -->
</div>
<!-- /.content-header -->

<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <div class="col-4">
            <div class="row d-flex justify-content-center">
                <div class="card">
                    <div class="card-header">
                        <!--button class="btn btn-info" data-toggle="modal" data-target="#altas"><i class="fa fa-plus"></i> Agregar </button-->
                    </div>
                    <!-- /.card-header -->
                    <div class="card-body">
                        <form method="post" id="formUsuarios" >    
                            <div class="col-md-8">
                                <div class="form-group">
                                      <label class="form-label">Nombre</label>
                                      <input type="text" id="nombre" class="form-control" name="nombre">
                                </div>
                            </div>
                            <div class="col-md-8">
                                <div class="form-group">
                                      <label>Apellido1</label>
                                      <input type="text" class="form-control" name="apellido1">
                                </div>
                            </div>
                            <div class="col-md-8">
                                <div class="form-group">
                                      <label>Apellido2</label>
                                      <input type="text" class="form-control" name="apellido2">
                                </div>
                            </div>
                            <div class="col-md-8">
                                <div class="form-group">
                                      <label>Correo</label>
                                      <input type="email" id="correo" class="form-control minusculas" name="usr"  >
                                </div>
                            </div>
                            <div class="col-md-8">
                                <div class="form-group">
                                  <label>Rol del Usuario</label>
                                  <select id="rol" name="rol" class="form-control validate[required]" >
                                      <option value="">Selecciona un Rol</option>
                                      <?php
                                        $con="SELECT * FROM roles";
                                        $rs=$clsConsulta->consultaGeneral($con);
                                        foreach($rs as $v=>$val){
                                            echo '<option value="'.$val['id'].'">'.$val['name'].'</option>';
                                        }
                                      ?>

                                  </select>
                                </div>
                            </div>
                            <div class="col-md-12"> 
                                <div class="form-group">                                
                                    <input type="hidden" name="fin" value="altas">                                
                                    <input type="hidden" name="tabla" value="usuarios">                                                                
                                    <div class="row col-12">
                                        <button type="submit" id="guardar" class="btn btn-primary">Guardar</button> &nbsp; &nbsp;
                                        <a href="usuarios" class="btn btn-default">Regresar</a> 
                                    </div>                                
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<script>
    /*
    window.onload = (event) => {
        alert ("hola");
        
    };
    */
    $.validator.addMethod(
        "regex",
            function(value, element, regexp) 
            {
                if (regexp.constructor != RegExp)
                    regexp = new RegExp(regexp);
                else if (regexp.global)
                    regexp.lastIndex = 0;
                return this.optional(element) || regexp.test(value);
            },
            "Please check your input."        
    );
        
    
    $("#formUsuarios").validate({
      rules: {
        nombre: {
            required: true,
            minlength: 5,
            regex: /^[a-zA-Z\s]*$/
        },
        apellido1: {
            required: true,
            minlength: 5,
            regex: /^[a-zA-Z\s]*$/
        },
          apellido2:{
              regex: /^[a-zA-Z\s]*$/
          },
        usr: {
          required: true,
          email: true,
          maxlength: 50,
          remote: {
              url:"ajax/verificaCorreo.php",              
              data: {
                    'correo': $('#usr').val()
                },
              type: 'json',
              success: function(data){
                alert(data);
              }            
          }
        },
        rol: { 
            required: true,            
        }
      },
      messages: {
        nombre: {
            required: "Escribe un nombre",
            regex: "Solo acepta letras y espacios"
        },
        apellido1: {
            required: "Escribe un Apellido",
            minlength: "Escriba más de 5 caracteres",
            regex: "Solo acepta letras y espacios"
        },
        apellido2: {            
            regex: "Solo acepta letras y espacios"
        },
        usr: {
          required: "Escribe un correo",
          email: "Tu correo necesita un formato nombre@dominio.com",
          maxlength: "El tamaño máximo es de 50 caracteres",
          remote: function() { return $.validator.format("{0} is already taken", $("#usr").val()) }
        },
        rol: { 
            required: "Seleccione un Rol" 
        }  
      },
      errorElement : 'span',
      errorPlacement: function (error, element) {
          error.addClass('invalid-feedback');
          element.closest('.form-group').append(error);
        },
        highlight: function (element, errorClass, validClass) {
          $(element).addClass('is-invalid');
        },
        unhighlight: function (element, errorClass, validClass) {
          $(element).removeClass('is-invalid');
          $(element).addClass('is-valid');    
        },
          
      submitHandler: function(form){
         //submit form
        event.preventDefault();

        var datos = $("#formUsuarios").serialize();

        $.ajax({
            type: "POST",
            url: "ajax/guardardatos.php",
            data: datos,
            success: function(data)
            {               
               if(data==1){
                    alert("Fallo el server");
                }else{
                    var closable = alertify.alert().setting('closable');
                    alertify.alert('Aviso').setHeader('<em> Aviso </em> '); 
                    //grab the dialog instance using its parameter-less constructor then set multiple settings at once.
                    alertify.alert()
                      .setting({
                        'label':'Cerrar',
                        'message': 'Se han guardado los cambios' ,
                     //   'onok': function(){ alertify.success('Exito..!');}
                      }).show();
                    //¿Aquí iría el borrar contenido del formulario no?
                   $("#formUsuarios")[0].reset(); //Limpiar el formulario
                }
            }
        });
      }
    },                                  
  );

    


 
    /*
    $("#formUsuarios").submit(function(event) {
        event.preventDefault();

        var datos = $(this).serialize();

        $.ajax({
            type: "POST",
            url: "ajax/guardardatos.php",
            data: datos,
            success: function(data)
            {               
               if(data==1){
                    alert("Fallo el server");
                }else{
                    
                    var closable = alertify.alert().setting('closable');
                    //grab the dialog instance using its parameter-less constructor then set multiple settings at once.
                    alertify.alert()
                      .setting({
                        'label':'Cerrar',
                        'message': 'Se han guardado los cambios' ,
                        'onok': function(){ alertify.success('Exito..!');}
                      }).show();
                    //¿Aquí iría el borrar contenido del formulario no?
                   $("#formUsuarios")[0].reset(); //Limpiar el formulario
                }
            }
        });
    });
	*/

</script>



