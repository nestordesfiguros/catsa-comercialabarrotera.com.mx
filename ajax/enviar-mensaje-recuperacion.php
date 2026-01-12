<?php
include '../admin/lib/clsConsultas.php';
$clsConsulta=new Consultas();
//Load Composer's autoloader
require '../admin/vendor/autoload.php';

$para=$_POST['email'];
$token=bin2hex(random_bytes(64));

$mensaje='
<!doctype html>
<html>
    <head>
        <meta charset="utf-8">    
    </head>

    <body style="font-family:Arial, Helvetica, sans-serif; padding:25px;">

        <table style="width:100.0%;background:#edf2f7; margin-bottom:20px;" width="100%" cellspacing="0" cellpadding="0" border="0">            
            <tbody>
                <tr>
                    <td style="padding:18.75pt 0cm 18.75pt 0cm">
                        <p class="MsoNormal" style="text-align:center" align="center"><span style="font-family:&quot;Segoe UI&quot;,sans-serif">
                            <img src="http://petrea-vinculacion.mustango.com.mx/img/logo2.png" alt="Logo petrea capital">
                        </span>
                        </p>
                    </td>
                </tr>
                <tr>
                    <td align="center">
                        <div style="text-align: center;">
                            <h4>Has solicitado la recuperación de tu contraseña</h4>
                        </div>
                        <div  style="text-align: center;">
                            <p>Click <a href="http://www.petrea-vinculacion.mustango.com.mx/acciones/recuperapwd.php?id='.$token.'">Aquí</a> para recuperar tu contraseña</p>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <br><br><br>
                        &nbsp;
                    </td>
                </tr>
            </tbody>
        </table> 
        <br><br>        
        <div>
            <small><b>AVISO DE CONFIDENCIALIDAD Y CONFIABILIDAD </b>-- La información contenida en este mensaje y sus anexos es confidencial, podría constituir información privilegiada y su no divulgación está protegida por la ley. Dicha información está dirigida únicamente a su(s) destinatario(s). Si usted no es el destinatario a quién esta comunicación va dirigida, en este acto se le notifica que cualquier uso, incluyendo sin limitarse a la diseminación, distribución, divulgación o copia de este mensaje y sus anexos está estrictamente prohibida. Si usted no es el destinatario de esta comunicación, le rogamos nos lo notifique inmediatamente y la borre de su sistema de cómputo. TÉNGALO EN CUENTA—El mensaje contenido en esta comunicación no implica la existencia de convenio alguno o firma vinculante, expresa o implícita, a menos que en el mensaje contenido exista declaración expresa en tal sentido.</small>
        </div>
    </body>
</html> ';

//Import PHPMailer classes into the global namespace
//These must be at the top of your script, not inside a function
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

$con="SELECT * FROM usuarios WHERE usr='".$para."'";
$rs=$clsConsulta->consultaGeneral($con);
if($clsConsulta->numrows>0){
    $nombre=$rs[1]['nombre'];
    $id_usuario=$rs[1]['id'];
    
    //Create an instance; passing `true` enables exceptions
    $mail = new PHPMailer(true);

    try {
        //Server settings
        $mail->SMTPDebug = SMTP::DEBUG_SERVER;                      //Enable verbose debug output
        $mail->isSMTP();                                            //Send using SMTP
        $mail->Host       = 'smtp.ionos.com';                     //Set the SMTP server to send through
        $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
        $mail->Username   = 'notify@mustango.com.mx';                     //SMTP username
        $mail->Password   = 'Mustang0##2022D34#';                               //SMTP password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            //Enable implicit TLS encryption
        $mail->Port       = 465;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

        //Recipients
        $mail->setFrom('notify@mustango.com.mx', 'Petréa Cápital Vinculación');
        $mail->addAddress($para, $nombre);     //Add a recipient
    //   $mail->addAddress('ellen@example.com');               //Name is optional
    //    $mail->addReplyTo('info@example.com', 'Information');
    //    $mail->addCC('cc@example.com');
    //    $mail->addBCC('bcc@example.com');

        //Attachments
    //    $mail->addAttachment('/var/tmp/file.tar.gz');         //Add attachments
    //    $mail->addAttachment('/tmp/image.jpg', 'new.jpg');    //Optional name

        //Content
        $mail->isHTML(true);                                  //Set email format to HTML
        $mail->Subject = 'Solicitud para Recuperar contraseña';
        $mail->Body    = $mensaje;
        $mail->CharSet = 'UTF-8';
        $mail->SetLanguage("es", "phpmailer/language");
        $mail->Encoding="base64";  //this code very important
    //    $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

        $mail->send();
        echo 'Message has been sent';        
        $cons="INSERT INTO usuarios_recuperar_pwd (id_usuario, correo, token, created_at) VALUES (".$id_usuario.", '".$para."', '".$token."', NOW() )";        
        $clsConsulta->aplicaQuery($cons);

    } catch (Exception $e) {
     //   echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
}
?>