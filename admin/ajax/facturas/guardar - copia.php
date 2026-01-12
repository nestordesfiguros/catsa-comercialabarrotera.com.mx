<?php
session_start();
include '../../lib/clsConsultas.php';
$clsConsulta=new Consultas();

//var_dump($_POST);
$fecha=$_POST["fact_fecha"];
$fact_serie =$_POST["fact_serie"]; 
$fact_folio =$_POST["fact_folio"]; 
$id_usuario=$_SESSION["id_user"];
$forma_pago=$_POST["id_forma_pago"];
$metodo_pago=$_POST["id_metodo_pago"];
$id_receptor=$_POST['cliente_id'];
$producto_ids = $_POST['producto_id']; // Array de id_productos
//$claves = $_POST['clave']; // Array de claves
$cantidades = $_POST['cantidad']; // Array de cantidades
$precio_venta = $_POST['precio_venta']; // Array de precios

$con="INSERT INTO cab_facturas (id_emisor, id_receptor, serie, folio, forma_pago, metodo_pago, fecha, id_usuario) VALUES (1, '$id_receptor', '$fact_serie', '$fact_folio', $forma_pago, $metodo_pago, '$fecha',  '$id_usuario')";
//echo $con.' <br>';
$rs=$clsConsulta->guardarGeneral($con);
$id_factura=$clsConsulta->ultimoid;

// Recorrer los productos y hacer la inserción o actualización en la base de datos
foreach ($producto_ids as $index => $id_producto) {
    ///$clave = $claves[$index];
    $cantidad = $cantidades[$index];
    $precio=$precio_venta[$index];
    $con = "INSERT INTO mov_facturas (id_factura, id_producto, cantidad, precio) VALUES ('$id_factura', '$id_producto', '$cantidad', '$precio')";
    //echo $con;
    $rs=$clsConsulta->aplicaQuery($con);
    //  Resta la cantidad a los productos y la guarda en la base de datos

    $conr="SELECT cantidad_disponible FROM cat_productos WHERE id_producto=".$id_producto;
    $res=$clsConsulta->consultaGeneral($conr);
    $cantInv = $res[1]['cantidad_disponible'];
    $resta=$cantInv-$cantidad;
    $rscant="UPDATE cat_productos SET cantidad_disponible=$resta WHERE id_producto=".$id_producto;
    $clsConsulta->aplicaQuery($rscant);
}
$fact_folio++;
$con="UPDATE parametros SET fact_serie='$fact_serie', fact_no= '$fact_folio' WHERE id=1";
$clsConsulta->aplicaQuery($con);
echo 'success';
?>