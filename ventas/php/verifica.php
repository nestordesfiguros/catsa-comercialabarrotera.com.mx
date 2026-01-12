<?php
session_start();
header('Content-Type: application/json'); // Establecemos que la respuesta será JSON

include '../../admin/lib/clsConsultas.php';
$clsConsulta = new Consultas();

date_default_timezone_set('Etc/GMT+6');

// Inicializar array de respuesta
$response = [
    'success' => false,
    'message' => '',
    'vendedor' => ''
];

try {
    // Validar que los campos no estén vacíos
    if(empty($_POST['username'])) {
        throw new Exception('El vendedor de usuario es requerido');
    }
    
    if(empty($_POST['password'])) {
        throw new Exception('La contraseña es requerida');
    }

    $usr = trim($_POST['username']);
    $pwd = trim($_POST['password']);

    // Prevenir SQL Injection (asumiendo que tu clase Consultas no lo hace)
    // $usr = $clsConsulta->escape($usr); // Comentado temporalmente por si tu clase no tiene este método
    
    $salt = substr($usr, 0, 2);
    $clave_crypt = crypt($pwd, $salt);
    
    $query = "SELECT id, nombre, apellido1, apellido2, pwd 
              FROM cat_vendedores 
              WHERE usr = '$usr' AND pwd = '$clave_crypt'";
    //echo $query;
    // Consulta normal (reemplaza esto con consulta preparada si está disponible)
    $rs = $clsConsulta->consultaGeneral($query);
    
    if ($clsConsulta->numrows > 0) {
        foreach($rs as $rowEmp) {        
            $_SESSION["id_vendedor"] = $rowEmp['id'];
            $_SESSION['vendedor'] = $rowEmp['nombre'] . ' ' . $rowEmp['apellido1'] . ' ' . $rowEmp['apellido2'];
            $_SESSION['time'] = strtotime(date("Y-m-d H:i:s"));    
            
            $response['success'] = true;
            $response['vendedor'] = $_SESSION['vendedor'];
        }
    } else {
        $response['message'] = 'Usuario o contraseña incorrectos';
    }
    
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

// Limpiar el buffer de salida por si hay algún espacio/error antes
 if (ob_get_length()) ob_clean();

 echo json_encode($response);
 exit;
?>