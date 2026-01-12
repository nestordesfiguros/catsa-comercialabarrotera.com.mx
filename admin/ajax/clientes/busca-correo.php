<?php
include '../../lib/clsConsultas.php';
$clsConsulta = new Consultas();

$data = [];
$correo2 = '';
$correo = trim($_POST['correo']);
//$correo = strtoupper($correo);
if(isset($_POST['editar']) && $_POST['editar']==1){
    $correo2 = trim($_POST['correo2']);
}


if($correo != $correo2){
    if ($correo != '') {
        $con = "SELECT * FROM cat_proveedores WHERE correo='" . $correo . "'";
        // echo $con;
        $rs = $clsConsulta->consultaGeneral($con);
        
        if ($clsConsulta->numrows > 0) {   
            $nombre = $rs[1]['razon_social'];
            $data = [
                "existe" => true,
                "nombre" => $nombre
            ];
        } else {
            $data = [
                "existe" => false,
                "nombre" => ""
            ];
        }
    }    

}

header('Content-Type: application/json'); // Asegúrate de establecer el tipo de contenido
echo json_encode($data);
?>
