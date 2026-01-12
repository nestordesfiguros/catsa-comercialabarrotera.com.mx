<?php
include '../../lib/clsConsultas.php';
$clsConsulta=new Consultas();
$id_usuario=$_POST['id_usuario'];
$comentario=$_POST['comentario'];

$cook_fact_serie='';
    $cook_fact_no='';
    $cook_fact_folio='';
    $cook_fact_no_estimacion='';
    $cook_id_factura='';
    if(isset($_COOKIE['fact_serie'])) {    
      $cook_fact_serie=$_COOKIE['fact_serie'];
      $cook_fact_no=$_COOKIE['fact_no'];
      $cook_id_factura=$_COOKIE['id_factura'];
      $cook_fact_folio=(isset($_COOKIE['fact_folio']))?$_COOKIE['fact_folio']:'';
    //  $cook_fact_no_estimacion=$_COOKIE['fact_no_estimacion'];          
    }

$con="SELECT
post_venta.fact_id 
, post_venta.fact_serie
, post_venta.fact_fecha
, post_venta.fact_no_estimacion
, post_venta.fact_cliente
, cat_clientes.razon_social
FROM
post_venta
INNER JOIN cat_clientes 
    ON (post_venta.fact_cliente = cat_clientes.id)
WHERE post_venta.fact_serie='".$cook_fact_serie."' AND post_venta.fact_no='".$cook_fact_no."' ";
//echo $con;
$rs=$clsConsulta->consultaGeneral($con);
if($clsConsulta->numrows>0){
    $razon_social=$rs[1]['razon_social'];
    $cliente_id=$rs[1]['fact_cliente'];
    $idFactura=$rs[1]['fact_id'];
 //   $fact_no_estimacion=$rs[1]['fact_no_estimacion'];
}


/* *********************************** */
/*          Enío de correos            */
/* **********************************  */

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

// echo 'Comienza: ##########################################';
//var_dump($_POST);
//var_dump($_POST['correoDestinatario']);

$correoDest=str_replace("\n",",",$_POST['correoDestinatario']);  
$correoDestin=str_replace(",Destinatario","",$correoDest);
$correoDestArray=explode(",",$correoDestin);
/*
foreach ($correoDestArray as $mailDest){           
    echo '-->'.$mailDest;
}
*/
$correoCC1=str_replace("\n",",",$_POST['correoCC']);  
$correoCC2=str_replace("CC","",$correoCC1);
$correoCC=explode(",",$correoCC2);

$correoCCO1=str_replace("\n",",",$_POST['correoCCO']);  
$correoCCO2=str_replace("CCO","",$correoCCO1);
$correoCCO=explode(",",$correoCCO2);

//$correoCC=(isset($_POST['correosCC']))? $_POST['correosCC']:'';
//$correoCCO=(isset($_POST['correosCCO']))? $_POST['correosCCO']:'';
$asunto=$_POST['asunto'];

       
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
    <img src="http://petrea-vinculacion.mustango.com.mx/img/logo2.png" alt"Logo petrea capital">
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
    <h3 style="margin-top:0cm;box-sizing:border-box" align="right"><span style="font-size:13.5pt;font-family:&quot;Segoe UI&quot;,sans-serif;color:#3d4852">
    
    <br></span></h3>
    <h1 style="margin-top:0cm;box-sizing:border-box"><span style="font-size:13.5pt;font-family:&quot;Segoe UI&quot;,sans-serif;color:#3d4852">
    
    <br>  </span>
    </h1>
    <p style="margin-top:0cm;line-height:18.0pt;box-sizing:border-box">
    <span style="font-size:12.0pt;font-family:&quot;Segoe UI&quot;,sans-serif;color:black">
    Reciba un cordial saludo
    
    </span></p><p style="margin-top:0cm;line-height:18.0pt;box-sizing:border-box"><span style="font-size:12.0pt;font-family:&quot;Segoe UI&quot;,sans-serif;color:black"><br></span><span style="font-size:12.0pt;font-family:&quot;Segoe UI&quot;,sans-serif"><u></u><u></u></span>
    
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
        <!--cuerpo del correo -->
        <center>        
        <br>
        Envío por este medio documentación de la post venta<br>'.$asunto.'
        <br>
        '.$comentario.'
        <br>
        Agradecemos su amable atención <br><br>
        Quedamos atentos a su pronta respuesta
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
    <!--span style="font-size:12.0pt;font-family:&quot;Segoe UI&quot;,sans-serif;color:black">Saludos,<br>Vinculación</span></p><p style="margin-top:0cm;line-height:18.0pt;box-sizing:border-box"><span style="font-size:12.0pt;font-family:&quot;Segoe UI&quot;,sans-serif;color:black"><br></span></p><p style="margin-top:0cm;line-height:18.0pt;box-sizing:border-box"><span style="font-size:12.0pt;font-family:&quot;Segoe UI&quot;,sans-serif;color:black">C.C.:<br></span><span style="font-size:12.0pt;font-family:&quot;Segoe UI&quot;,sans-serif"></span-->
    </p>
    <table style="width:100.0%;border:none;border-top:solid #e8e5ef 1.0pt;box-sizing:border-box" width="100%" cellspacing="0" cellpadding="0" border="1">
    <tbody>
    <tr>
    <td style="border:none;padding:18.75pt 0cm 0cm 0cm;box-sizing:border-box">
    <p style="margin-top:0cm;line-height:18.0pt;box-sizing:border-box">
    <span style="font-size:10.5pt;font-family:&quot;Segoe UI&quot;,sans-serif">
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
        $mail->setFrom('notify@mustango.com.mx', 'Sistema Vinculación');
        foreach ($correoDestArray as $mailDest){           
            $mail->addAddress($mailDest, $razon_social); 
        }
        if(!isset($correoCC)){
            foreach ($correoCC as $mailDest){           
                $mail->addCC($mailDest, $razon_social); 
            }
        }
        
        if(!isset($correoCCO)){
            foreach ($correoCCo as $mailDest){           
                $mail->addBCC($mailDest, $razon_social); 
            }
        }
            //Add a recipient
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
        $ruta='../../expedientes/post_venta/';    
        $con="SELECT fact_arch_nombre FROM post_venta_archivos WHERE fact_arch_id_factura=".$cook_id_factura;
        $rs=$clsConsulta->consultaGeneral($con);
        if($clsConsulta->numrows>0){
            foreach($rs as $v=>$val){
                $mail->addAttachment($ruta.$val['fact_arch_nombre']); 
            }    
        }

        $con="SELECT nombre FROM post_venta_archivos_adicionales WHERE id_post_venta=".$cook_id_factura;        
        $rs=$clsConsulta->consultaGeneral($con);
        if($clsConsulta->numrows>0){
            foreach($rs as $v=>$val){
                $mail->addAttachment($ruta.$val['nombre']); 
            }    
        }
        
        //Attachments
    //    $mail->addAttachment('/var/tmp/file.tar.gz');         //Add attachments
    //    $mail->addAttachment('/tmp/image.jpg', 'new.jpg');    //Optional name

        //Content
        $mail->isHTML(true);                                  //Set email format to HTML
        $mail->Subject = $asunto;
        $mail->Body    = $mensaje;
        $mail->CharSet = 'UTF-8';
        $mail->SetLanguage("es", "phpmailer/language");
        $mail->Encoding="base64";  //this code very important
    //    $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

        $mail->send();
    //    echo 'Message se ha enviado';
        $enviado=1;
        $errorEnviado='Enviado ';
        $resultado='{"resultado":"enviado"}';
    } catch (Exception $e) {
    //    echo "El correo no ha sido enviado. Mailer Error: {$mail->ErrorInfo}";
        $errorEnviado="No se envío | Error: {$mail->ErrorInfo}";
        $enviado=2;
        $resultado='{"resultado":"error"}';
    }
    
    $con="UPDATE post_venta SET fact_estatus_proceso=".$enviado.", fact_destinatario='".$correoDestin."', fact_cc='".$correoCC2."', fact_cco='".$correoCCO2."' WHERE fact_id=".$cook_id_factura;
    $clsConsulta->aplicaQuery($con);   
    /*
    $conin="INSERT INTO estimaciones_enviadas (id_factura, no_estimacion, asunto, destinatario, cc, cco, comentarios, fecha_envio, id_usuario, compilado, enviado, errorEnviado) VALUES (".$idFactura.", ".$cook_fact_no_estimacion.", '".$asunto."', '".$correoDestin."', '".$correoCC2."', '".$correoCCO2."', '".$comentario."',NOW(), '".$id_usuario."', '".$archivos."', '".$enviado."', '".$errorEnviado."' )";    
    $clsConsulta->aplicaQuery($conin);     
*/
    echo $resultado;

?>