<?php
    $con="SELECT * FROM usuarios WHERE id=".$cat;
    $rs=$clsConsulta->consultaGeneral($con);
    foreach($rs as $v=>$val){
        $nombre=$val['nombre'];
        $apellido1=$val['apellido1'];
        $apellido2=$val['apellido2'];
        $usuarioID=$val['id'];   
        $correo=$val['usr'];
        $rol=$val['rol'];
        $estatus=($val['estatus']==1)?'<b class="text-success">ACTIVO</b>':'<b class="text-danger">INACTIVO</b>';
    }
?>
<!-- Content Header (Page header) -->
<div class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h6 class="m-0"><i class="fa fa-user"></i> Usuario / Editar</h6>
      </div><!-- /.col -->
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="inicio">Inicio</a></li>
          <li class="breadcrumb-item"><a href="usuarios">Usuarios</a></li>
            <li class="breadcrumb-item active">Editar</li>
        </ol>
      </div><!-- /.col -->
    </div><!-- /.row -->
  </div><!-- /.container-fluid -->
</div>
<!-- /.content-header -->

<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <div class="row d-flex justify-content-center">
            <div class="col-6">
                <div class="card">
                    <div class="card-header">
                        <!--button class="btn btn-info" data-toggle="modal" data-target="#altas"><i class="fa fa-plus"></i> Agregar </button-->
                    </div>
                    <!-- /.card-header -->
                    <div class="card-body">
                        <form method="post" action="abc/usuario-modificar">                             
                            <div class="form-group">
                                  <label>Nombre</label>
                                  <input type="text" class="form-control mayusculas" name="nombre" value="<?php echo $nombre; ?>" pattern="[a-zA-Z ]{2,254}+" required>
                            </div>
                            <div class="form-group">
                                  <label>Apellido1</label>
                                  <input type="text" class="form-control mayusculas" name="apellido1" value="<?php echo $apellido1; ?>">
                            </div>
                            <div class="form-group">
                                  <label>Apellido2</label>
                                  <input type="text" class="form-control mayusculas" name="apellido2" value="<?php echo $apellido2; ?>">
                            </div>
                            <div class="form-group">
                                  <label>Correo</label>
                                  <input type="email" id="correo" class="form-control minusculas" name="usr" onblur="verificaMail();" value="<?php echo $correo; ?>" required>
                            </div>                            
                            <div class="form-group">
                              <label>Rol del Usuario </label>
                              <select name="rol" class="form-control" required>                                  
                                  <?php
                                    $con="SELECT * FROM roles";
                                    $rs=$clsConsulta->consultaGeneral($con);
                                    foreach($rs as $v=>$val){
                                        if($rol==$val['id']){
                                            echo '<option value="'.$val['id'].'" selected >'.$val['name'].'</option>';
                                        }else{
                                            echo '<option value="'.$val['id'].'">'.$val['name'].'</option>';
                                        }
                                        
                                    }
                                  ?>
                                  
                              </select>
                            </div>
                            <div class="form-group">                                
                                <input type="hidden" name="fin" value="modificar">
                                <input type="hidden" name="url" value="usuarios-editar/<?php echo $cat; ?>/1" >
                                <input type="hidden" name="id" value="<?php echo $cat; ?>" >
                                <input type="hidden" name="tabla" value="usuarios">                                                                
                                <div class="row col-12 mt-5">
                                    <div class="col-2">
                                        <button type="submit" id="guardar" class="btn btn-primary">Modificar</button> &nbsp; &nbsp;
                                    </div>
                                    <div class="col-2">
                                        <a href="usuarios" class="btn btn-default">Cancelar</a> 
                                    </div>
                                    
                                </div>                                
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <input type="hidden" id="mail" value="<?php echo $correo; ?>" >
    <?php
    if(isset($subcat)){
        switch ($subcat){
            case 1:
                $msg='Guardado';
                echo $clsAlerta->mensaje($msg, 'success');      // msg=  mensaje a enviar  tipo mensaje   
            break;
            case 2:
                $msg='El correo ya está asignado a otro usuario';
                echo $clsAlerta->mensaje($msg, 'danger');      // msg=  mensaje a enviar  tipo mensaje  
                echo '<script>
                $("#correo").focus();        
                $("#correo").addClass("is-invalid");        
                </script>';
            break;
        }
        
    }
    ?>
    <div id="resultadoCorreo"></div>
</section>
<!--script src="https://code.jquery.com/jquery-3.2.1.js"></script-->
<script>
    function verificaMail(){
        var correo=$('#correo').val();
        var mail=$('#mail').val();        
        if(mail!=correo){            
            $.ajax({        
                type: "POST", 
                url: "ajax/verificaCorreo.php",
                data: {correo:correo,id:<?php echo $cat; ?>},
                success: function(result){                    
                $("#resultadoCorreo").html(result);
            }});
        }
    }    
</script>


