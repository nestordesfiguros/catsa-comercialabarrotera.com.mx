<?php
include '../../lib/clsConsultas.php';
$clsConsulta=new Consultas();
/*
$id=$_POST['id'];
$res='{';
$con="SELECT * presupuestos_archivos WHERE id_presupuesto=".$id;
$rs=$clsConsulta->consultaGeneral($con);
if($clsConsulta->numrows>0){
    foreach($rs as $v=>$val){
        $res.='"name": "'.$val['archivo'].'"';
    }
}

return $res;
*/


//$ds          = '/'; 
 
$targetPath = '../../documentos/';  
if (!empty($_FILES)) {
    $tempFile = $_FILES['file']['tmp_name'];              
    $targetFile =  $targetPath. $_FILES['file']['name'];  
    move_uploaded_file($tempFile,$targetFile);
 
} else {                                                           
    $result  = array();
 
    $files = scandir($targetPath);                 //1
    if ( false!==$files ) {
        foreach ( $files as $file ) {
            if ( '.'!=$file && '..'!=$file) {       //2
                $obj['name'] = $file;
                $obj['size'] = filesize($targetPath.$file);
                $result[] = $obj;
            }
        }
    }
     
    header('Content-type: text/json');              //3
    header('Content-type: application/json');
    echo json_encode($result);
}
?>