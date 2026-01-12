<?PHP
include '../admin/lib/clsConsultas.php';
$clsConsulta= new Consultas();
$pwd=$_POST['pwd'];
$usr=$_POST['correo']; 
$id_usuario=$_POST['id_usuario'];

$salt = substr ($usr, 0, 2);
$clave_crypt = crypt ($pwd, $salt);

$con="UPDATE usuarios SET pwd='".$clave_crypt."', clave='".$pwd."',  fecha_modifica=NOW() WHERE id=".$id_usuario;
//echo $con;
$clsConsulta->aplicaQuery($con);

$con="UPDATE usuarios_recuperar_pwd SET usado=1, updated_at=NOW() WHERE id_usuario=".$id_usuario;
$clsConsulta->aplicaQuery($con);
?>