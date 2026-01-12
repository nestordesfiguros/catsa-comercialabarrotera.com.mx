<?php 
include '../../lib/clsConsultas.php';
$clsConsulta=new Consultas();

if(isset($_COOKIE['id_factura'])) {     
    $id_factura=$_COOKIE['id_factura'];
  }
var_dump($_POST);
  $archivo = explode("|",$_POST['archivos']);
  foreach($archivo as $file){
      if($file!=''){
          $conf="INSERT INTO post_venta_archivos_adicionales(id_post_venta, nombre, fecha_alta) VALUES (".$id_factura.", '".$file."', NOW() )";
          echo $conf.'<br>';
          $clsConsulta->aplicaQuery($conf);   
      }            
  }


?>