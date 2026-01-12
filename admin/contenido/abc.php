<style>
	#loader{
		font-size:18px;
		position:absolute;
		width:100%;
		height:100%;
		z-index:1000;
		text-align:center;
		padding-top:100px;
		color:#060;
	}
	.box-body{
		text-align:center;
	}
	.err{
		text-align:center;
		color:#F00;
	}	
</style>

<!--div class="content-wrapper">
	<section class="content">
		<div class="box-header">
			<h3 id="loader">Espere un momento ...</h3>
		</div>
		<div class="box-body">
				<img src="img/ajax-loader.gif" width="100" height="100" alt="Accesando al sistema">
		</div-->

<div>&nbsp;</div>
<div>&nbsp;</div>
<div>&nbsp;</div>	
<div class="content-wrapper">
	<section class="content">
<?PHP
//ini_set('memory_limit', '2024M'); // or you could use 1G
//ini_set('max_execution_time', 720);
//var_dump($_POST);
//echo $nav.' / '.$cat.' / '.$subcat.'<br>';
include 'lib/cls_FTP.php';
$clsFTP = new FTP();

if(isset($cat)){
	switch ($cat){	
        case 'usuario-altas':
            $nombre=$_POST['nombre'];
            $apellido1=$_POST['apellido1'];
            $apellido2=$_POST['apellido2'];
            $usuarioID=$_POST['id'];   
            $correo=strtolower($_POST['usr']);
            $rol=$_POST['rol'];
            $url="usuarios-editar/".$id."/1";
            $con="INSERT INTO usuarios (nombre, apellido1, apellido2, usr, rol) VALUES ('".$nombre."', '".$apellido1."', '".$apellido2."', '".$correo."', ".$rol.")";			
			$clsConsulta->guardarGeneral($con);            
        break;
        case 'usuario-modificar':
            $id=$_POST['id'];
            $nombre=$_POST['nombre'];
            $apellido1=$_POST['apellido1'];
            $apellido2=$_POST['apellido2'];
            $usuarioID=$_POST['id'];   
            $correo=strtolower($_POST['usr']);
            $rol=$_POST['rol'];
            $url="usuarios-editar/".$id."/1";
            $con="UDATE usuarios SET nombre='".$nombre."', apellido1='".$apellido1."', apellido2='".$apellido2."', usr='".$correo."', rol=".$rol." WHERE id=".$id;			
			$clsConsulta->aplicaQuery($con);            
        break;
            
		case 'altausuario':   // Agrega un usuario a la base de datos
			$nombre=$_POST['nombre'];
			$usr=$_POST['usr'];
			$pwd=$_POST['pwd'];
			$salt = substr ($usr, 0, 2);
			$clave_crypt = crypt ($pwd, $salt);
			$con="INSERT INTO usuarios (nombre, usr, pwd, clave) VALUES ('".$nombre."', '".$usr."', '".$clave_crypt."', '".$pwd."')";			
			$clsConsulta->guardarGeneral($con);
			
			$ultimoId=$clsConsulta->ultimoid; 
			$con="INSERT INTO accesos (id_personal) VALUES (".$ultimoId.")";		// Asigna a la tabla accesos al usuario
			$clsConsulta->aplicaQuery($con);
			
			$url='personal';
		break;
        case 'inhabilitar':
            $id = $_POST['id'];
            $tabla = $_POST['tabla'];
            $estatus=($_POST['estatus']==1)?0:1;            
            $con="UPDATE ".$tabla." SET estatus=".$estatus.", updated_at=NOW() WHERE id=".$id;
      //      echo $con;
            $clsConsulta->aplicaquery($con);
        break;
        case 'modifica-presupuesto':
        //    var_dump($_POST);
            $cliente = $_POST['cliente'];
            $clienteid = $_POST['cliente_id'];
            
            $con="SELECT id, UPPER(razon_social) FROM cat_clientes WHERE razon_social='".$cliente."'";        
            $rs=$clsConsulta->consultaGeneral($con);
            foreach($rs as $v=>$val){
                $cliente_id=$val['id'];
            }
            
            $id = $_POST['id'];
        //    $folio = $_POST['folio'];            
            $descripcion = $_POST['descripcion'];
            $monto = $_POST['monto'];
            $folio = $_POST['folio'];            
            $comentario ='';            
            $id_usuario=$_SESSION['id_user'];
            $accion='Modificado';
            $seccion='presupuestos';
            
            $comentario="Presupuesto Modificado el día ".$clsFecha->fecha_Hoy();
            
            $fecha_recepcion = $_POST['fecha_recepcion'];
 
            $con="UPDATE presupuestos SET cliente_id=".$cliente_id.", monto=".$monto.", fecha_recepcion='".$fecha_recepcion."', descripcion='".$descripcion."',  updated_at= NOW() WHERE id=".$id;
        //    echo $con;
            $clsConsulta->aplicaQuery($con);
            $clsConsulta->bitacora($folio, $id_usuario, $accion, $comentario, $seccion);
            
            $con="UPDATE presupuestos SET estado='Revision' WHERE id=".$id;         
            $clsConsulta->aplicaQuery($con);
            
            $id_usuario=$_SESSION['id_user'];
            $seccion='presupuestos';
            $clsConsulta->bitacora($folio, $id_usuario, $accion, $comentario, $seccion);
                        
            
            $con="SELECT * FROM clientes_correos WHERE cliente_id='".$clienteid."' AND tipo_proceso=4 AND tipo_correo='Destinatario'";               
            $nombreDestinatario='';
            $rs=$clsConsulta->consultaGeneral($con);
            if($clsConsulta->numrows>0){   
                foreach($rs as $v=>$val){              
                //    $tipo_correo = $val['tipo_correo'];         
                    $nombreDestinatario .= ' '.$val['titulo'].' '.$val['nombre'].',';
                //    $tituloDestinatario = $val['titulo'];
                    $correoDestinatario = $val['correo'].',';
                }    
                $nombreDestinatario=trim($nombreDestinatario, ',');
                $correoDestinatario=trim($correoDestinatario, ',');


                $con="SELECT * FROM clientes_correos WHERE cliente_id='".$clienteid."' AND tipo_proceso=4 AND tipo_correo='CC'";   
                $rs=$clsConsulta->consultaGeneral($con);
                if($clsConsulta->numrows>0){   
                    foreach($rs as $v=>$val){              
                    //    $tipo_correo = $val['tipo_correo'];         
                        $nombrecc .= ' '.$val['titulo'].' '.$val['nombre'].',';
                    //    $tituloDestinatario = $val['titulo'];
                        $correocc = $val['correo'].',';
                    }    
                    $nombrecc=trim($nombrecc, ',');
                    $correocc=trim($correocc, ',');
                }else{
                    $correocc='';
                    $nombrecc='';
                }

                $con="SELECT * FROM clientes_correos WHERE cliente_id='".$clienteid."' AND tipo_proceso=4 AND tipo_correo='CCO'";      
                //echo $con;
                $rs=$clsConsulta->consultaGeneral($con);
                if($clsConsulta->numrows>0){   
                    foreach($rs as $v=>$val){              
                    //    $tipo_correo = $val['tipo_correo'];         
                        $nombrecco .= ' '.$val['titulo'].' '.$val['nombre'].',';
                    //    $tituloDestinatario = $val['titulo'];
                        $correocco = $val['correo'].',';
                    }    
                    $nombrecco=trim($nombrecco, ',');
                    $correocco=trim($correocco, ',');
                }else{
                    $correocco='';
                    $nombrecco='';
                }
                include 'plantillas/aprueba-presupuesto.php';
            }
            
            
        break;
        case 'presupuestosestado':
            
            $id = $_POST['idestado'];
            $folio = $_POST['folio'];
            $estado = $_POST['estado'];
            $clienteid = $_POST['cliente_id'];         
            $comentario2 = (isset($_POST['comentario']))?$_POST['comentario']:'';            
            $id_usuario=$_SESSION['id_user'];            
            $accion=$estado;
            $seccion='presupuestos';
            
            if($estado=='Aprobado'){
                $comentario="Presupuesto Aprobado el día ".$clsFecha->fecha_Hoy().' '.$comentario2;
                $tipoProceso=2;
            }
            if($estado=='Rechazado'){
                $comentario="Presupuesto Rechazado el día ".$clsFecha->fecha_Hoy().' '.$comentario2;
                $tipoProceso=3;
            }
            
            
            $con="SELECT * FROM presupuestos WHERE folio='".$folio."'";
            $rs=$clsConsulta->consultaGeneral($con);
            foreach($rs as $v=>$val){
                $descripcion=$val['descripcion'];
                $fecha_recepcion=$clsFecha->fecha_letra($val['fecha_recepcion']);
            }
            
            $con="UPDATE presupuestos SET estado='".$estado."' WHERE id=".$id;         
            $clsConsulta->aplicaQuery($con);
            
            $id_usuario=$_SESSION['id_user'];
            $seccion='presupuestos';
            $clsConsulta->bitacora($folio, $id_usuario, $accion, $comentario, $seccion);
                        
            
            $con="SELECT * FROM clientes_correos WHERE cliente_id='".$clienteid."' AND tipo_proceso=".$tipoProceso." AND tipo_correo='Destinatario'";               
            $nombreDestinatario='';
            $rs=$clsConsulta->consultaGeneral($con);
            if($clsConsulta->numrows>0){   
                foreach($rs as $v=>$val){              
                //    $tipo_correo = $val['tipo_correo'];         
                    $nombreDestinatario .= ' '.$val['titulo'].' '.$val['nombre'].',';
                //    $tituloDestinatario = $val['titulo'];
                    $correoDestinatario = $val['correo'].',';
                }    
                $nombreDestinatario=trim($nombreDestinatario, ',');
                $correoDestinatario=trim($correoDestinatario, ',');


                $con="SELECT * FROM clientes_correos WHERE cliente_id='".$clienteid."' AND tipo_proceso=".$tipoProceso." AND tipo_correo='CC'";   
                $rs=$clsConsulta->consultaGeneral($con);
                if($clsConsulta->numrows>0){   
                    foreach($rs as $v=>$val){              
                    //    $tipo_correo = $val['tipo_correo'];         
                        $nombrecc .= ' '.$val['titulo'].' '.$val['nombre'].',';
                    //    $tituloDestinatario = $val['titulo'];
                        $correocc = $val['correo'].',';
                    }    
                    $nombrecc=trim($nombrecc, ',');
                    $correocc=trim($correocc, ',');
                }else{
                    $correocc='';
                    $nombrecc='';
                }

               
                $con="SELECT
                clientes_correos.correo
                , clientes_correos.nombre
                , clientes_correos.sucursal
                , clientes_correos_procesos.id_proceso
                , clientes_correos_procesos.tipo_correo
                FROM
                clientes_correos_procesos
                INNER JOIN clientes_correos 
                    ON (clientes_correos_procesos.id_correo = clientes_correos.id)
                WHERE clientes_correos.cliente_id=1 
                ".$addSucursal."
                AND clientes_correos_procesos.id_proceso=1
                AND clientes_correos_procesos.estatus=1";
                //echo $con;
                $rs=$clsConsulta->consultaGeneral($con);

                foreach($rs as $v=>$val){
                    $correoDestinatario=$val['correo'];
                    $correoSucursal=$val['sucursal'];
                    $nombreDestinatario=$val['nombre'];
                    $tipoCorreo=$val['tipo_correo'];
                        
                    include 'plantillas/aprueba-presupuesto.php';
                }
            }
                        
            
        break;
        case 'usuarioAlta':
            $nombre=$val['nombre'];
            $apellido1=$val['apellido1'];
            $apellido2=$val['apellido2'];
            $usuarioID=$val['id'];               
            $rol=$val['rol'];            
             
            $con="INSERT INTO usuarios (nombre, apellido1, apellido2, fecha_alta, rol) VALUES ('".$nombre."', '".$apellido1."', '".$apellido2."', ".$estatus.",  NOW(), ".$rol.") ";	
        //    echo $con;
            $clsConsulta->guardarGeneral($con); 
            
        break;
        case 'imgparametros':
         //   var_dump($_POST);
            $archivo=$_POST['archivo'];
            $titulo=$_POST['titulo'];
            $url='configuracion-parametros';
            $ruta='img/';
            $rutafile=$ruta.$archivo;
            
            if($titulo=='logo'){
                $nuevoNombre=$ruta.'logo.png';
                $fileborrar=$ruta.'logo.png';
            }
            if($titulo=='favicon'){
                $nuevoNombre=$ruta.'favicon.png';
                $fileborrar=$ruta.'favicon.png';
            }
            
            if(file_exists($rutafile)){
           //     echo 'si existe: '.$ruta; 
                if($rutafile!=$nuevoNombre){
                    if(file_exists($fileborrar)){
                        unlink($fileborrar);
                    }
                    rename($rutafile,$nuevoNombre);
                }
            }else{
            //    echo 'No existe: '.$ruta;      
                rename($rutafile,$nuevoNombre);
            }
            
        break;
        case 'modificatitulo':
            $titulo=$_POST['titulopage'];
            $url='configuracion-parametros';
            $con="UPDATE parametros SET titulo='".$titulo."' WHERE id=1";            
            $clsConsulta->aplicaQuery($con);
        break;
        case 'apruebaSiroc':
         //   var_dump($_POST);
            $id=$_POST['id'];
            $nombreDestinatario='';
            $nombrecc='';
            $nombrecco='';
                
            $con="SELECT * FROM clientes_sirocs WHERE id=".$id;
            $rs=$clsConsulta->consultaGeneral($con);
            foreach($rs as $v=>$val){
                $folio = $val['folio'];  // Folio siroc                
         //       $razon_social=$val['razon_social'];
                $descripcion = $val['descripcion'];                
                $clienteid = $val['cliente_id'];
            //    $presupuesto_id = $val['presupuesto_id'];
                $imss = $val['imss'];                                         
                $fecha_firma = $val['fecha_firma'];
                $fecha_cierre_siroc = $val['fecha_cierre_siroc'];
            }
            
            $con="UPDATE clientes_sirocs SET estado='Aprobado' WHERE id=".$id;
            $clsConsulta->aplicaQuery($con);
            
            $id_usuario=$_SESSION['id_user'];
            $accion='Aprobado';
            $seccion='siroc';
            $comentario="Siroc aprobado el día ".$clsFecha->fecha_Hoy();
            $clsConsulta->bitacora($folio, $id_usuario, $accion, $comentario, $seccion);         
            
            $con="SELECT * FROM clientes_correos WHERE cliente_id='".$clienteid."' AND tipo_proceso=5 AND tipo_correo='Destinatario'";      
            //echo $con;
            $rs=$clsConsulta->consultaGeneral($con);
            if($clsConsulta->numrows>0){   
                foreach($rs as $v=>$val){              
                //    $tipo_correo = $val['tipo_correo'];         
                    $nombreDestinatario .= ' '.$val['titulo'].' '.$val['nombre'].',';
                //    $tituloDestinatario = $val['titulo'];
                    $correoDestinatario = $val['correo'].',';
                }    
                $nombreDestinatario=trim($nombreDestinatario, ',');
                $correoDestinatario=trim($correoDestinatario, ',');
            }else{
                $correoDestinatario='';
            }

        //    echo 'destinatario:  '.$correoDestinatario;
            
            
            if($correoDestinatario!=''){
                $con="SELECT * FROM clientes_correos WHERE cliente_id='".$clienteid."' AND tipo_proceso=5 AND tipo_correo='CC'";      
                //echo $con;
                $rs=$clsConsulta->consultaGeneral($con);
                if($clsConsulta->numrows>0){   
                    foreach($rs as $v=>$val){              
                    //    $tipo_correo = $val['tipo_correo'];         
                        $nombrecc .= ' '.$val['titulo'].' '.$val['nombre'].',';
                    //    $tituloDestinatario = $val['titulo'];
                        $correocc = $val['correo'].',';
                    }    
                    $nombrecc=trim($nombrecc, ',');
                    $correocc=trim($correocc, ',');
                }else{
                    $correocc='';
                }

                $con="SELECT * FROM clientes_correos WHERE cliente_id='".$clienteid."' AND tipo_proceso=5 AND tipo_correo='CCO'";      
                //echo $con;
                $rs=$clsConsulta->consultaGeneral($con);
                if($clsConsulta->numrows>0){   
                    foreach($rs as $v=>$val){              
                    //    $tipo_correo = $val['tipo_correo'];         
                        $nombrecco .= ' '.$val['titulo'].' '.$val['nombre'].',';
                    //    $tituloDestinatario = $val['titulo'];
                        $correocco = $val['correo'].',';
                    }    
                    $nombrecco=trim($nombrecco, ',');
                    $correocco=trim($correocco, ',');
                }else{
                    $correocco='';
                }
                include 'plantillas/aprueba-siroc.php';
            }
        break;
        case 'borrarClientesCorreos':
            $id=$_POST['id'];
            $con="DELETE FROM clientes_correos_documentos WHERE id_correo=".$id;
            $clsConsulta->aplicaQuery($con);
            /*
            $con="DELETE FROM clientes_correos_contactos WHERE id_correo=".$id;
            $clsConsulta->aplicaQuery($con);
            */
            $con="DELETE FROM clientes_correos WHERE id=".$id;
            $clsConsulta->aplicaQuery($con);

        break;
        
            		
	}
	if(isset($_POST['url'])){ $url=$_POST['url']; }
//	echo $url.'<br>';
	//	echo $con.'<br>';
//	var_dump($_POST);
}else{
    
//	var_dump($_POST);
	include 'lib/cls_AlBaCa.php'; 	
}
if(isset($_POST['url'])){ $url=$_POST['url']; }
echo '<script type="text/javascript">
		  location.href="'.$url.'";
	  </script>';
?>
	</section>
</div>
