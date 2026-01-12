<?php
    $titulo='';
    $con="SELECT * FROM parametros WHERE id=1";
    $rs=$clsConsulta->consultaGeneral($con);
    if($clsConsulta->numrows>0){
        foreach($rs as $v=>$val){
            $email_smtp = $val['email_smtp'];
            $email_cuenta = $val['email_cuenta'];
            $email_password = $val['email_password'];
            $email_ssl = ($val['email_ssl']==1)?'checked':'';
            $email_puerto = $val['email_puerto'];
        }
    }                                    
?>
<style>
    span{        
        position:relative;
        display:block;        
        box-shadow:1px 1px 3px gray;
        left: 450px ;        
    }
    .bootstrap-tagsinput .tag {
      margin-right: 2px;
      color: white !important;
      background-color: #0d6efd;
      padding: 0.2rem;
    }
</style>


<div class="ms-5">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="configuracion">Sistema</a></li>    
        <li class="breadcrumb-item active" aria-current="page">Configuración de correos</li>
      </ol>
    </nav>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">                       
                        <h5 class="text-center p-3 bg-info bg-gradient">Parametros de correo</h5> 
                    </div>
                    <!-- /.card-header -->
                    <div class="card-body"> 
                        <div class="row d-flex justify-content-center">
                            <div class="col-4">
                                <p class="bg-secondary text-black p-2 rounded-3 shadow  text-center"><b>Configuración para envío de correo</b></p>
                                <div class="row">
                                    <!-- Form -->
                                    <form id="formcorreos" method="post" action="">
                                        <div class="form-group">
                                            <label for="email_cuenta">Correo</label>
                                            <input name="email_cuenta" class="form-control" type="text" value="<?php echo $email_cuenta; ?>">
                                        </div>
                                        <div class="form-group mt-3">
                                            <label for="email_password">Contraseña</label>
                                            <input name="email_password" class="form-control" type="password" value="<?php echo $email_password; ?>">
                                        </div>
                                        <div class="form-group mt-3">
                                            <label for="email_smtp">Servidor de salida</label>
                                            <input name="email_smtp" class="form-control" type="text" value="<?php echo $email_smtp; ?>">
                                        </div>
                                        <div class="form-group mt-3">
                                            <label for="email_puerto">Puerto de salida</label>
                                            <input name="email_puerto" class="form-control" type="text" value="<?php echo $email_puerto; ?>">
                                        </div>
                                        <div class="form-group mt-3">                                            
                                            <div class="custom-control form-switch">
                                              <label class="custom-control-label me-5" for="email_ssl">Tipo de Seguridad</label>
                                              <small class="me-5"><b> Inactivo: </b> Ninguna </small>    
                                              <input type="checkbox" name="email_ssl" class="form-check-input"  <?php  echo $email_ssl; ?> id="email_ssl">                                                
                                                <small><b> Activo: </b> SSL </small>                                                
                                            </div> 
                                        </div>  
                                        <div class="form-group mt-3">
                                            <div class="d-grid gap-2 mt-3">
                                                <!--button type="button" id="btntitulo" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalconfigcorreos" onclick="pasatitulo();">Aplicar cambios</button-->
                                                <button type="submit" id="btntitulo" class="btn btn-primary">Aplicar cambios</button>
                                            </div>
                                        </div>
                                    </form>
                                    <!-- /. Form -->
                                </div>
                            </div>
                            
                            <!--div class="col-8">
                                <div class="row">
                                    <div class="col-6">
                                        <?php 
                                        /*
                                        $activo='';
                                        $con="SELECT
                                            procesos_dominios.*
                                            , cat_procesos.proceso
                                        FROM
                                            procesos_dominios
                                            INNER JOIN cat_procesos 
                                                ON (procesos_dominios.id_proceso = cat_procesos.id)";
                                        $rs=$clsConsulta->consultaGeneral($con);                                        
                                        foreach($rs as $v=>$val){
                                            $activo=($val['activo']==1)?'checked':'';
                                            echo '<div class="custom-control form-switch mt-2">
                                              <small class="me-5">Inactivo</small>
                                              <input onclick="pasaparametro('.$val['id'].','.$val['activo'].')" id="'.$val['id'].'" type="checkbox" name="'.$val['id'].'" class="form-check-input" '.$activo.'> 
                                              <label class="custom-control-label" for="'.$val['id'].'"><small>Activo</small>  <b class="ms-5">'.$val['proceso'].'</b></label> </div>';               
                                        }
                                        */
                                        ?>
                                    </div>
                                    
                                    <div class="col-4">
                                        <div>
                                            <input name="tags" class="form-control" placeholder="petreacapital.com, mustango.com.mx" value="">
                                        </div>
                                        <div class="mt-1">
                                            <input name="tags" class="form-control" placeholder="petreacapital.com, mustango.com.mx" value="">
                                        </div>                                        
                                        <div class="mt-1">
                                            <input name="tags" class="form-control" placeholder="petreacapital.com, mustango.com.mx" value="">
                                        </div>
                                        <div class="mt-1">
                                            <input name="tags" class="form-control" placeholder="petreacapital.com, mustango.com.mx" value="">
                                        </div>
                                        <div class="mt-1">
                                            <input name="tags" class="form-control" placeholder="petreacapital.com, mustango.com.mx" value="">
                                        </div>
                                        <div class="mt-1">
                                            <input name="tags" class="form-control" placeholder="petreacapital.com, mustango.com.mx" value="">
                                        </div>
                                        <div class="mt-1">
                                            <input name="tags" class="form-control" placeholder="petreacapital.com, mustango.com.mx" value="">
                                        </div>
                                        <div class="mt-1">
                                            <input name="tags" class="form-control" placeholder="petreacapital.com, mustango.com.mx" value="">
                                        </div>
                                        <div class="mt-1">
                                            <input name="tags" class="form-control" placeholder="petreacapital.com, mustango.com.mx" value="">
                                        </div>
                                        <div class="mt-1">
                                            <input name="tags" class="form-control" placeholder="petreacapital.com, mustango.com.mx" value="">
                                        </div>
                                    </div>
                                </div>                                
                            </div-->
                            
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<script>
    
    
    
    function pasaparametro(id,activo){
        alertify.confirm(
            'Guardar Cambios', 
            '¿Quieres guardar los cambios?',                 
            function(){ 
             //   alertify.success('Ok') 
                $.ajax({
                    type: "POST",
                    url: "ajax/parametros/modifica-proceso-activo.php",
                    data: {id:id, activo:activo},
                    success: function(data)
                    {               
                   console.log(data);
                       if(data==1){
                            alert("Fallo el server");
                        }else{
                         //get the closable setting value.
                        var closable = alertify.alert().setting('closable');
                        alertify.alert('Aviso').setHeader('<em> Aviso </em> '); 
                        alertify.alert()
                          .setting({
                            'label':'Cerrar',
                            'message': 'Se han guardado los cambios' 
                           // 'onok': function(){ alertify.success('Great');}
                          }).show();
                        alertify.alert().set({ onclosing:function(){ /* location.reload(); */ }});
                        }
                    }
                });
            }
            , function(){                 
                if(activo==1){
                    $("#"+id).prop('checked','true');    
                }else{
                    $("#"+id).prop('checked',false);                    
                }
                
             //   alertify.error('Cancel')
        }).set('labels', {cancel:'Cancelar',ok:'Guardar'});  
    };
</script>

<!--MODAL configuracion correos-->
<div class="modal fade" id="modalconfigcorreos" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Confirmar Acción</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
        <!-- FORM  -->
      <form action="abc/imgparametros" method="post">         
          <div class="modal-body">            
              <span id="mensaje"></span>
          </div>
          <div class="modal-footer">            
            <button type="button" class="btn btn-secondary" id="no" data-bs-dismiss="modal">No</button>  
            <button type="submit" id="si" class="btn btn-primary"> Si </button>                                                                             <input type="hidden" name="titulo" id="titulo">      
            <input type="hidden" name="archivo" id="archivo">
          </div>
       </form>
        <!-- . FORM  -->
    </div>    
  </div>
</div>

<script>
    $(document).ready(function(){ 
        
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
          return new bootstrap.Tooltip(tooltipTriggerEl)
        })

        
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
            "Revisa que el RFC tenga un formato valido"        
    );
        
    
    $("#formcorreos").validate({
          rules: {
            email_cuenta: {
                required: true,
                minlength: 5,
                maxlength: 100,
                regex: /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/
            }, 
            email_password:{
                required: true,
                maxlength: 50,
              },
            email_smtp: {
                required: true,
                maxlength: 50,                
            },                               
            email_puerto:{
                required: true,
                maxlength: 4,
                regex: /^[0-9]*$/
            }
          },
          messages: {
            email_cuenta: {
                required: 'Campo obligatorio',
                minlength: 'Escribe mas de 5 caracteres',
                maxlength: "Ha rebasado el máximo de caracteres permitido 100",
                regex: "Escribe un correo valido"                
            },
            email_password:{                              
                required: "Campo obligatorio",
                maxlength: "Ha rebasado el máximo de caracteres permitido 50"
              },
            email_smtp: {
                required: "Campo obligatorio",                                
                maxlength: "Ha rebasado el máximo de caracteres permitido 50"
            },            
            email_puerto: {
                required: "Campo obligatorio",
                maxlength: "El máximo de caracteres es 4",
                regex: "Solo se admiten números",
            } 
          },                      
          errorElement : 'span',  
            errorPlacement: function (error, element) {
              error.addClass('invalid-feedback');
              error.addClass('btn btn-danger btn-sm text-white');               
              element.closest('.form-group').append(error);
            },
            highlight: function (element, errorClass, validClass) {
              $(element).addClass('is-invalid');
                  
            },
            unhighlight: function (element, errorClass, validClass, error) {
              $(element).removeClass('is-invalid');
              $(element).addClass('is-valid'); 
                $(error).removeClass('btn btn-danger btn-sm text-white d-flex justify-content-end');
            },

          submitHandler: function(form){
             //submit form
            event.preventDefault();
         //     console.log('Entro');            
            var datos = $("#formcorreos").serialize();            
            alertify.confirm(
                'Guardar Cambios', 
                '¿Quieres guardar los cambios?',                 
                function(){ 
                 //   alertify.success('Ok') 
                    $.ajax({
                        type: "POST",
                        url: "ajax/parametros/guarda-config-correo.php",
                        data: datos,
                        success: function(data)
                        {               
                       console.log(data);
                           if(data==1){
                                alert("Fallo el server");
                            }else{
                             //get the closable setting value.
                            var closable = alertify.alert().setting('closable');
                            alertify.alert('Aviso').setHeader('<em> Aviso </em> '); 
                            alertify.alert()
                              .setting({
                                'label':'Cerrar',
                                'message': 'Se han guardado los cambios' 
                               // 'onok': function(){ alertify.success('Great');}
                              }).show();
                            alertify.alert().set({ onclosing:function(){ location.reload(); }});
                            }
                        }
                    });
                }
                , function(){ 
                 //   alertify.error('Cancel')
                }).set('labels', {cancel:'Cancelar',ok:'Guardar'});  
              
            
          }
        },                                  
      );
        
               
    });
</script>