<!-- Tags Input -->
<link rel="stylesheet" href="../admin/dist/tagsinput/bootstrap-tagsinput.css">
<!--link rel="stylesheet" href="dist/tagsinput/app.css"-->
<script src="../admin/dist/tagsinput/bootstrap-tagsinput.min.js"></script>
<?php
    include '../../lib/clsConsultas.php';
    $clsConsulta=new Consultas();
    $cat=(isset($cat))?$cat:'';
    $id=$_POST['id'];    
    $con="SELECT * FROM cat_clientes WHERE id=".$id;  
    $rs=$clsConsulta->consultaGeneral($con);
    if($clsConsulta->numrows>0){
        foreach($rs as $v=>$val){                        
            $razon_social=$val['razon_social'];
            $sucursales=$val['sucursales'];        
            $rfc=$val['rfc'];                        
            $contacto=$val['contacto']; 
     //       $registro_patronal=$val['registro_patronal']; 
            $repse=$val['repse'];
            $estatus=($val['estatus']==1)? 'checked':'';
            $presupuesto=($val['presupuesto']==1)? 'checked':'';
            $siroc=($val['siroc']==1)? 'checked':'';
            $expediente=($val['expediente']==1)? 'checked':'';
            $def_documentos=($val['def_documentos']==1)? 'checked':'';
        }
        
    }
?>
<form method="post" id="formClientesEditar" action="">
    <div class="modal-body">                                                       
        <div class="form-group">
              <label for="razon_social">Razón Social <!--span class="text-danger">*</span--></label>
              <input type="text" class="form-control mayusculas" name="razon_social" id="razon" placeholder="Razón Social" 
              value="<?php echo  $razon_social; ?>"  >                                    
        </div>  
        <div class="form-group ">
            <label for="sucursales">Sucursales </label>                
            <input type="text" name="sucursales" class="form-control "  data-role="tagsinput" value="<?php echo  $sucursales; ?>"/>                
        </div>  
        <div class="form-group ">
              <label>Contacto </label>
              <input type="text" class="form-control mayusculas" name="contacto" placeholder="Nombre del contacto" value="<?php echo  $contacto; ?>" >
        </div>
        <div class="form-group ">
              <label>RFC <!--span class="text-danger">*</span--></label>
              <input type="text" class="form-control mayusculas" name="rfc" placeholder="RFC" id="rfc"  value="<?php echo  $rfc; ?>">
        </div>    
        <!--div class="col-12">&nbsp;</div>
        <div class="form-group ">
              <label>Registro Patronal</label>
              <input type="text" class="form-control mayusculas" name="registro_patronal" id="registropatronal" placeholder="Registro Patronal"  value="<?php echo  $registro_patronal; ?>">
        </div-->
        <div class="form-group">
              <label>Repse</label>
              <input type="text" class="form-control mayusculas" name="repse" id="repse" placeholder="REPSE"  value="<?php echo  $repse; ?>">
        </div>
            <!-- Botones -->
        <!--div class="row p-3">
            <div class="col-6">
                <div class="custom-control custom-switch">
                    <div class="form-check form-switch">
                        <input name="def_documentos" class="form-check-input" type="checkbox" id="def_documentos" <?php echo $def_documentos; ?> >
                        <label class="form-check-label" for="def_documentos">Definición de documentos</label>
                    </div>   
                </div>  
                <small><b>Encendido: </b> Permite la definición de documentos <br> <b>Apagado: </b> Desactiva la definición de documentos </small>                                        
            </div>                
            <div class="col-6">
                <div class="form-check form-switch">
                    <input name="presupuesto" class="form-check-input" type="checkbox" id="presupuesto" <?php echo $presupuesto; ?>>
                    <label class="form-check-label" for="presupuesto">Presupuesto</label>
                </div> 

                 <small>
                     <b>Encendido: </b>El cliente estará disponible para el registro <br>
                     <b>Apagado</b>: El cliente no estará disponible para la captura de presupuestos
                </small> 
            </div>
            <div class="form-group col-6">
                <div class="form-check form-switch">
                    <input name="siroc" class="form-check-input" type="checkbox" id="siroc" <?php echo $siroc; ?>>
                    <label class="form-check-label" for="siroc">Siroc</label>
                </div>  
                 <small>
                     <b>Encendido: </b> El cliente estará disponible para la captura de siroc <br>
                     <b>Apagado</b>: El cliente no estará disponible para la captura de siroc
                </small> 
            </div-->
            <!--div class="form-group col-6">
                <div class="form-check form-switch">
                    <input name="expediente" class="form-check-input" type="checkbox" id="expediente" <?php echo $expediente; ?>>
                    <label class="form-check-label" for="expediente">Expediente completo</label>
                </div> 
                 <small>
                     <b>Encendido: </b> Los contrats de este cliente deben de estar completos <br>
                     <b>Apagado</b>: Los contratos de este cliente no requerirán expediente completo para iniciar el proceso de estimaciones<br>
                    <b>NOTA:</b> Un epxediente incompleto puede repercutir en el correcto seguimiento de un contrato
                </small> 
            </div-->                                                             
        </div>                                           
    </div>   
    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>            
        <button type="submit" class="btn btn-primary"> Guardar </button>
        <input type="hidden" name="id" value="<?php echo $id; ?>">
        <input type="hidden" name="accion" value="MODIFICAR">
        <input type="hidden" name="url" value="clientes-presupuestos/<?php echo $cat; if(isset($subcat)){ echo '/'.$subcat;} ?>">
    </div>
</form>

<script type="text/javascript">


$(document).ready(function() {

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
        
    
    $("#formClientesEditar").validate({        
      rules: {
        razon_social: {
            required: true,
            minlength: 5
      //      regex: /^[A-ZÑa-zñáéíóúÁÉÍÓÚ'°., ]+$/,  // Solo letras y espacios
            
        },
        contacto: {
            required: true,
            minlength: 5,
            regex: /^[a-zA-ZáÁéÉíÍóÓúÚñÑ\s]+$/
        },        
        repse:{
            required: true
          },
        rfc:{
            required: true,
            maxlength: 13,
            minlength: 12
         //   regex: /^[A-z]{3,4}\\d{6}[A-z\\d]{3}?$/     /* Valida RFC*/
        },
        registro_patronal:{
            required: true,
            maxlength: 11  
         //   regex: /^[a-zA-Z\s]*$/            
        }
      },
      messages: {
        razon_social: {
            required: "Campo obligatorio",
            regex: "Escribe solo letras y espacios"
        },
        contacto: {
            required: "Campo obligatorio",
            minlength: "Escriba más de 5 caracteres"
        },
        registro_patronal: {            
            required: "Campo obligatorio",
            maxlength: "Tiene que tener 11 caracteres"
        },
        repse: {
            required: "Campo obligatorio"        
        },
        rfc: { 
            required: "Campo obligatorio",
            maxlength: "El máximo de caractes es de 13",
            minlength: "El mínimo de caracteres es de 12"
         //   regex: "El RFC No tiene un formato valido"
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
    //      console.log('Entro');

        var datos = $("#formClientesEditar").serialize();
        
        alertify.confirm('Aviso..!', '¿Deseas Guardar los cambios?', 
            function(){
                //    alertify.success('Ok') 
                       var archivos=$("#archivo1").val();
                      $.ajax({
                            type: "POST",
                            url: "ajax/clientes/guardaclientes.php",
                            data: datos,
                            success: function(data)
                            {               
                                console.log(data);
                               if(data==1){
                                    alert("Fallo el server");
                                }else{

                                }
                            }
                        }); /* End ajax */
                     
                  location.reload();
            },
            function(){ alertify.error('Cancel')}
        ); 
          
          

        
      }
    },                                  
  );

});
    
</script>