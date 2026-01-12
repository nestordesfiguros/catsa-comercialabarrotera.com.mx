<?php
session_start();
include '../../lib/clsConsultas.php';
$clsConsulta=new Consultas();
include '../../lib/clsFechas.php';
$clsFecha=new Fechas();

// var_dump($_POST);
//$accion = $_POST['accion']; 
$accion='ALTA';
$folio=$_POST['folio'];
$sucursal=(isset($_POST['sucursal']) && $_POST['sucursal']!='')?$_POST['sucursal']:'';
$cliente=strtoupper($_POST['cliente']);
$descripcion=$_POST['descripcion'];
$fecha_recepcion=$_POST['fecha_recepcion'];
$monto = str_replace("$","",$_POST['monto']);
$monto = str_replace(",","",$monto);
$impomontorte_contratado = trim($monto);

$estado = (isset($_POST['estado']))?$_POST['estado']:'Revision';
//$valor=$folio.',presupuesto'.',1';
//$valores= base64_encode ($valor);

$comentario = (isset($_POST['comentario']))?$_POST['comentario']:' Alta de presupuesto ';            
$id_usuario=$_SESSION['id_user'];
$seccion='presupuestos';

$con="SELECT id, UPPER(razon_social) FROM cat_clientes WHERE razon_social='".$cliente."'";      
// echo $con;
$rs=$clsConsulta->consultaGeneral($con);
foreach($rs as $v=>$val){
    $cliente_id=$val['id'];

} 


$con="INSERT INTO presupuestos (folio, sucursal, cliente_id, descripcion, fecha_recepcion, monto, estado, created_at) VALUES ('".$folio."', '".$sucursal."',".$cliente_id.", '".$descripcion."', '".$fecha_recepcion."', ".$monto.", '".$estado."', NOW())";
// echo $con; 
$clsConsulta->guardarGeneral($con); 
$idPresupuesto=$clsConsulta->ultimoid;    

$archivos = explode("|",$_POST['archivo']);

if($archivos!=''){
    foreach($archivos as $file){    
        if($file!=''){
            $conf="INSERT INTO presupuestos_archivos (id_presupuesto, archivo) VALUES (".$idPresupuesto.", '".$file."')";
            echo $conf.'<br>';
            $clsConsulta->aplicaQuery($conf);      
        }        
    }            
}

$clsConsulta->bitacora($folio, $id_usuario, $accion, $comentario, $seccion);

/* *********************************** */
/*          Enío de correos            */
/* **********************************  */

$id_presupuesto=$folio;
    $fecha_alta=$fecha_recepcion;
    // $descripcion='Descripcion';
  //  $copia_para='aldo.navarro@mustango.com.mx, salvador.nahon@mustango.com.mx';
        

    //Import PHPMailer classes into the global namespace
    //These must be at the top of your script, not inside a function
    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\SMTP;
    use PHPMailer\PHPMailer\Exception;

    //Load Composer's autoloader
    require '../../vendor/autoload.php';

    //Create an instance; passing `true` enables exceptions
    $mail = new PHPMailer(true);

$nombreDestinatario='';
$correoDestinatario='';
$nombrecc='';
$correocc='';
$nombrecco='';
$correocco='';

/*
$correoDestinatario=(isset($_POST['correosDesstinatario']))?$_POST['correosDesstinatario']:'';
$correoCC=(isset($_POST['correosCC']))? $_POST['correosCC']:'';
$correoCCO=(isset($_POST['correosCCO']))? $_POST['correosCCO']:'';
*/
$token=bin2hex(random_bytes(64));

if($sucursal!=''){
    $addSucursal=" AND  clientes_correos.sucursal='".$sucursal."'";
}else{
    $addSucursal='';
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
WHERE clientes_correos.cliente_id=".$cliente_id." 
".$addSucursal."
AND clientes_correos_procesos.id_proceso=1
AND clientes_correos_procesos.estatus=1";
//echo $con.'  procesos correo';
$rs=$clsConsulta->consultaGeneral($con);

foreach($rs as $v=>$val){
    $correoDestinatario=$val['correo'];
    $correoSucursal=$val['sucursal'];
    $nombreDestinatario=$val['nombre'];
    $tipoCorreo=$val['tipo_correo'];
       
    $mensaje='
        <!doctype html>
        <html>
        <head>
        <meta charset="utf-8">    
        </head>

        <body style="font-family:Arial, Helvetica, sans-serif; padding:25px;">
            
    <table style="width:100.0%;background:#edf2f7" width="100%" cellspacing="0" cellpadding="0" border="0">
    <tbody>
    <tr>
    <td style="padding:0cm 0cm 0cm 0cm">
    <div align="center">
    <table style="width:100.0%" width="100%" cellspacing="0" cellpadding="0" border="0">
    <tbody>
    <tr>
    <td style="padding:18.75pt 0cm 18.75pt 0cm">
    <p class="MsoNormal" style="text-align:center" align="center"><span style="font-family:&quot;Segoe UI&quot;,sans-serif">
    <img src="http://petrea-jornales.mustango.com.mx/img/logo2.png" alt"Logo petrea capital">
    </span></b>
    <u></u><u></u></span></p>
    </td>
    </tr>
    <tr>
    <td style="width:100.0%;border-top:solid #edf2f7 1.0pt;border-left:none;border-bottom:solid #edf2f7 1.0pt;border-right:none;background:#edf2f7;padding:0cm 0cm 0cm 0cm;box-sizing:border-box" width="100%">
    <div align="center">
    <table style="width:427.5pt;background:white;box-sizing:border-box;border-radius:2px" width="570" cellspacing="0" cellpadding="0" border="0">
    <tbody>
    <tr>
    <td style="padding:24.0pt 24.0pt 24.0pt 24.0pt;box-sizing:border-box;max-width:100vw">
    <h3 style="margin-top:0cm;box-sizing:border-box" align="right"><span style="font-size:13.5pt;font-family:&quot;Segoe UI&quot;,sans-serif;color:#3d4852">Fecha de recepcion del presupuesto: '.$fecha_alta.'<br></span></h3>
    <h1 style="margin-top:0cm;box-sizing:border-box"><span style="font-size:13.5pt;font-family:&quot;Segoe UI&quot;,sans-serif;color:#3d4852">'.$nombreDestinatario.'<br> Presente </span>
    </h1>
    <p style="margin-top:0cm;line-height:18.0pt;box-sizing:border-box">
    <span style="font-size:12.0pt;font-family:&quot;Segoe UI&quot;,sans-serif;color:black">Un
    nuevo Presupuesto se ha registrado.</span></p><p style="margin-top:0cm;line-height:18.0pt;box-sizing:border-box"><span style="font-size:12.0pt;font-family:&quot;Segoe UI&quot;,sans-serif;color:black"><br></span><span style="font-size:12.0pt;font-family:&quot;Segoe UI&quot;,sans-serif"><u></u><u></u></span>
    </p><p>Descripci&oacute;n: '.$descripcion.'</p>
    <p></p>
    <div align="center">
    <table style="width:100.0%;box-sizing:border-box" width="100%" cellspacing="0" cellpadding="0" border="0">
    <tbody>
    <tr>
    <td style="padding:0cm 0cm 0cm 0cm;box-sizing:border-box">
    <div align="center">
    <table style="width:100.0%;box-sizing:border-box" width="100%" cellspacing="0" cellpadding="0" border="0">
    <tbody>
    <tr>
    <td style="padding:0cm 0cm 0cm 0cm;box-sizing:border-box">
    <div align="center">
    <table style="box-sizing:border-box" cellspacing="0" cellpadding="0" border="0">
    <tbody>
    <tr>
    <td style="padding:0cm 0cm 0cm 0cm;box-sizing:border-box">
    <p class="MsoNormal">
    <span style="font-family:&quot;Segoe UI&quot;,sans-serif">
        <!--Button-->
        <center>
    ';
    if($tipoCorreo==1){
        $mensaje.= '
        
        <table align="center" cellspacing="0" cellpadding="0" width="100%">
        <tr>
            <td style="background-color: green; color: white; text-align: center; text-decoration: none; width: 100px; height: 25px; line-height: 25px; font-size: 14px; font-weight: bold; border-radius: 13px; ">
            <a href="http://petrea-vinculacion.mustango.com.mx/acciones/presupuestos-aprobar.php?token='.$token.'&f='.$folio.'&a=1&t=pa" style=" background-color: green; color: white; text-align: center; text-decoration: none; width: 100px; height: 25px; line-height: 25px; font-size: 14px; font-weight: bold; border-radius: 13px;" title="Haz click aquí para">Aprobar</a> &nbsp;
            </td>
            <td> &nbsp; &nbsp; &nbsp;</td>
            <td style="background-color: red; color: white; text-align: center; text-decoration: none; width: 100px; height: 25px; line-height: 25px; font-size: 14px; font-weight: bold; border-radius: 13px; ">
            <a href="http://petrea-vinculacion.mustango.com.mx/acciones/presupuestos-aprobar.php?token='.$token.'&f='.$folio.'&a=0&t=pa" style=" background-color: red; color: white; text-align: center; text-decoration: none; width: 100px; height: 35px; line-height: 55px; font-size: 14px; font-weight: bold; border-radius: 13px;" title="Haz click aquí para">Rechazar &nbsp;</a>&nbsp;
            </td>
        </tr>
        </table>
        ';
    }
    $mensaje.= '
    </center>
    </span>
    <u></u><u></u></span>
    </p>
    </td>
    </tr>
    </tbody>
    </table>
    </div>
    </td>
    </tr>
    </tbody>
    </table>
    </div>
    </td>
    </tr>
    </tbody>
    </table>
    </div>
    <p style="margin-top:0cm;line-height:18.0pt;box-sizing:border-box"><br></p>
    <p style="margin-top:0cm;line-height:18.0pt;box-sizing:border-box">
    <span style="font-size:12.0pt;font-family:&quot;Segoe UI&quot;,sans-serif;color:black">Saludos,<br>Vinculación</span></p><p style="margin-top:0cm;line-height:18.0pt;box-sizing:border-box"><span style="font-size:12.0pt;font-family:&quot;Segoe UI&quot;,sans-serif;color:black"><br></span></p><p style="margin-top:0cm;line-height:18.0pt;box-sizing:border-box"><span style="font-size:12.0pt;font-family:&quot;Segoe UI&quot;,sans-serif;color:black">C.C.:'.$nombrecc.'<br></span><span style="font-size:12.0pt;font-family:&quot;Segoe UI&quot;,sans-serif"></span>
    </p>
    <table style="width:100.0%;border:none;border-top:solid #e8e5ef 1.0pt;box-sizing:border-box" width="100%" cellspacing="0" cellpadding="0" border="1">
    <tbody>
    <tr>
    <td style="border:none;padding:18.75pt 0cm 0cm 0cm;box-sizing:border-box">
    <p style="margin-top:0cm;line-height:18.0pt;box-sizing:border-box">
    <span style="font-size:10.5pt;font-family:&quot;Segoe UI&quot;,sans-serif">Si
    tiene problemas para hacer clic en
    Puedes visitarlo dando clic aqui.
    botón, copie y pegue la URL a
    continuación en su navegador web segín la acción a tomar:

    
    <span class="m_-5040165421015974043break-all"><a href="http://petrea-vinculacion.mustango.com.mx/acciones/presupuestos-aprobar.php?token='.$token.'&f='.$folio.'&a=1&t=pa" target="_blank" </a></span><u></u><b>Aprobar</b><u></u></span>

   
    <span class="m_-5040165421015974043break-all"><a href="http://petrea-vinculacion.mustango.com.mx/acciones/presupuestos-aprobar.php?token='.$token.'&f='.$folio.'&a=0&t=pa" target="_blank" </a></span><u></u> <b>Rechazar</b><u></u></span>

    </p>
    </td>
    </tr>
    </tbody>
    </table>
    </td>
    </tr>
    </tbody>
    </table>
    </div>
    </td>
    </tr>
    <tr>
    <td style="padding:0cm 0cm 0cm 0cm;box-sizing:border-box">
    <div align="center">
    <table style="width:427.5pt;box-sizing:border-box" width="570" cellspacing="0" cellpadding="0" border="0">
    <tbody>
    <tr>
    <td style="padding:24.0pt 24.0pt 24.0pt 24.0pt;box-sizing:border-box">
    <p style="margin-top:0cm;text-align:center;line-height:18.0pt;box-sizing:border-box" align="center"><span style="font-size:9.0pt;font-family:&quot;Segoe UI&quot;,sans-serif;color:#b0adc5">©
    2023 vinculacion. Todos los derechos
    reservados.<u></u><u></u></span></p>
    </td>
    </tr>
    </tbody>
    </table>
    </div>
    <div><small><b>AVISO DE CONFIDENCIALIDAD Y CONFIABILIDAD </b>-- La información contenida en este mensaje y sus anexos es confidencial, podría constituir información privilegiada y su no divulgación está protegida por la ley. Dicha información está dirigida únicamente a su(s) destinatario(s). Si usted no es el destinatario a quién esta comunicación va dirigida, en este acto se le notifica que cualquier uso, incluyendo sin limitarse a la diseminación, distribución, divulgación o copia de este mensaje y sus anexos está estrictamente prohibida. Si usted no es el destinatario de esta comunicación, le rogamos nos lo notifique inmediatamente y la borre de su sistema de cómputo. TÉNGALO EN CUENTA—El mensaje contenido en esta comunicación no implica la existencia de convenio alguno o firma vinculante, expresa o implícita, a menos que en el mensaje contenido exista declaración expresa en tal sentido.</small></div>
    </td>
    </tr>
    </tbody>
    </table>
    </div>
    </td>
    </tr>
    </tbody>
    </table>
        </body>
        </html>
        ';
    

    try {
        //Server settings
        $mail->SMTPDebug = SMTP::DEBUG_SERVER;                      //Enable verbose debug output
        $mail->isSMTP();                                            //Send using SMTP
        $mail->Host       = 'smtp.ionos.com';                     //Set the SMTP server to send through
        $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
        $mail->Username   = 'notify@mustango.com.mx';                //SMTP username
        $mail->Password   = 'Mustang0##2022D34#';                    //SMTP password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            //Enable implicit TLS encryption
        $mail->Port       = 465;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

        //Recipients
        $mail->setFrom('notify@mustango.com.mx', 'Aviso');
        $mail->addAddress($correoDestinatario, 'Nuevo presupuesto');     //Add a recipient
    //   $mail->addAddress('ellen@example.com');               //Name is optional
    //    $mail->addReplyTo('info@example.com', 'Information');
    /*
        if($correocc!=''){
            $mail->addCC($correocc);    
        }
        if($correocco!=''){
            $mail->addBCC($correocco);    
        }
    */    
        if($archivos!=''){
            foreach($archivos as $file){    
                if($file!=''){
                    $mail->addAttachment('../../documentos/'.$file); 
                }
            }
        }
        //Attachments
    //    $mail->addAttachment('/var/tmp/file.tar.gz');         //Add attachments
    //    $mail->addAttachment('/tmp/image.jpg', 'new.jpg');    //Optional name

        //Content
        $mail->isHTML(true);                                  //Set email format to HTML
        $mail->Subject = 'Nuevo presupesto';
        $mail->Body    = utf8_decode($mensaje);
    //    $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

        $mail->send();
        echo 'Message has been sent';
        $enviado='Enviado';
    } catch (Exception $e) {
        echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        $enviado="No se envío | Error: {$mail->ErrorInfo}";
    }

    $conin="INSERT INTO presupuestos_correos (id_presupuesto, id_cliente, token, fecha_envio, correo, sucursal, enviado) VALUES (".$idPresupuesto.", ".$cliente_id.", '".$token."', NOW(), '".$correoDestinatario."', '".$sucursal."', '".$enviado."' )";    
    $clsConsulta->aplicaQuery($conin); 
}
?>
