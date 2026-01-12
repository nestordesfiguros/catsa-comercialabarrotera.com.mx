<?php
// ajax/facturas/enviar.php
header('Content-Type: application/json; charset=utf-8');

session_start();
require_once __DIR__ . '/../../lib/clsConsultas.php';
$clsConsulta = new Consultas();

// Composer (PHPMailer)
$autoload = dirname(__DIR__, 2) . '/vendor/autoload.php';
if (!is_file($autoload)) {
    echo json_encode(['success' => false, 'msg' => 'No se encontró vendor/autoload.php'], JSON_UNESCAPED_UNICODE);
    exit;
}
require_once $autoload;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/* ===== Entradas ===== */

$idFactura = (int)($_POST['id'] ?? 0);
$correoAlt = trim((string)($_POST['correo'] ?? '')); // Prioridad absoluta

if ($idFactura <= 0) {
    echo json_encode(['success' => false, 'msg' => 'Falta id de la factura'], JSON_UNESCAPED_UNICODE);
    exit;
}

/* ===== Cargar datos de factura + cliente + empresa ===== */
$sql = "
SELECT
  cf.id, cf.serie, cf.folio, cf.uuid, cf.id_receptor, cf.id_empresa,
  cli.razon_social AS cli_razon,
  CAST(cli.rfc AS CHAR) AS cli_rfc,
  IFNULL(cli.correo_factura, '') AS mail_preferente,
  IFNULL(cli.correo, '') AS mail_alt1
FROM cab_facturas cf
JOIN cat_clientes cli ON cli.id = cf.id_receptor
WHERE cf.id = {$idFactura}
LIMIT 1";
$rs = $clsConsulta->consultaGeneral($sql);
$cab = $rs[1] ?? null;
if (!$cab) {
    echo json_encode(['success' => false, 'msg' => 'Factura no encontrada'], JSON_UNESCAPED_UNICODE);
    exit;
}
$uuid  = trim((string)($cab['uuid'] ?? ''));
$serie = trim((string)($cab['serie'] ?? ''));
$folio = trim((string)($cab['folio'] ?? ''));

if ($uuid === '') {
    echo json_encode(['success' => false, 'msg' => 'La factura no está timbrada (sin UUID).'], JSON_UNESCAPED_UNICODE);
    exit;
}

$idEmpresa = (int)($cab['id_empresa'] ?? 0);
if ($idEmpresa <= 0 && !empty($_SESSION['id_empresa'])) {
    $idEmpresa = (int)$_SESSION['id_empresa'];
}
if ($idEmpresa <= 0) $idEmpresa = 1;

$emp = $clsConsulta->consultaGeneral("SELECT * FROM cat_empresas WHERE id={$idEmpresa} LIMIT 1");
$emp = $emp[1] ?? null;
$fromMail = '';
$fromName = '';
if ($emp) {
    // Ajusta si tu tabla cat_empresas solo tiene uno de estos campos
    $fromMail = trim((string)($emp['correo'] ?? ($emp['email'] ?? '')));
    $fromName = trim((string)($emp['razon_social'] ?? 'Facturación'));
}
if ($fromMail === '') $fromMail = 'no-reply@localhost';

/* ===== Destinatarios =====
   1) Si enviaron "correo" en POST, usarlo SIEMPRE (puede ser una lista separada por comas).
   2) Si no, usar el del cliente (correo_factura o correo).
*/
$toRaw = $correoAlt !== '' ? $correoAlt : ($cab['mail_preferente'] ?: $cab['mail_alt1']);
$toRaw = trim($toRaw);

if ($toRaw === '') {
    echo json_encode(['success' => false, 'msg' => 'No hay correo destino (captura uno en el modal o registra el correo del cliente).'], JSON_UNESCAPED_UNICODE);
    exit;
}

// Admite múltiples correos separados por coma
$destinatarios = array_filter(array_map('trim', explode(',', $toRaw)));

foreach ($destinatarios as $email) {
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'msg' => "Correo inválido: {$email}"], JSON_UNESCAPED_UNICODE);
        exit;
    }
}

/* ===== Rutas a XML y PDF =====
   Convención: admin/ajax/timbrados/factura_{id}.xml/pdf
*/
$dirOut  = __DIR__ . '/../timbrados';
$xmlPath = $dirOut . "/factura_{$idFactura}.xml";
$pdfPath = $dirOut . "/factura_{$idFactura}.pdf";
if (!is_file($xmlPath)) {
    echo json_encode(['success' => false, 'msg' => 'No se encontró el XML timbrado en disco.'], JSON_UNESCAPED_UNICODE);
    exit;
}

/* ===== Enviar correo ===== */
try {
    $mail = new PHPMailer(true);

    // SMTP (ajusta con tus credenciales reales)
    $mail->isSMTP();
    $mail->Host = 'mail.catsa-comercialabarrotera.com.mx';
    $mail->SMTPAuth = true;
    $mail->Username = 'noreply@catsa-comercialabarrotera.com.mx';
    $mail->Password = 'r;JO)$EojFyeix~n';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    $mail->setFrom($fromMail, ($fromName ?: 'Facturación'));
    // añade cada destinatario
    foreach ($destinatarios as $email) {
        $mail->addAddress($email);
    }
    // opcional: Reply-To de la empresa
    if ($fromMail !== 'no-reply@localhost') {
        $mail->addReplyTo($fromMail, ($fromName ?: 'Facturación'));
    }

    $mail->isHTML(true);
    $mail->Subject = "Factura {$serie}{$folio} - UUID {$uuid}";
    $mail->Body    = "<p>Estimado(a),</p>
                      <p>Adjuntamos su factura:</p>
                      <ul>
                        <li><b>Serie/Folio:</b> {$serie}{$folio}</li>
                        <li><b>UUID:</b> {$uuid}</li>
                      </ul>
                      <p>Se incluye el XML timbrado y el PDF.</p>
                      <p>Saludos.</p>";

    // Adjuntos
    $mail->addAttachment($xmlPath, "Factura_{$serie}{$folio}_{$uuid}.xml");
    if (is_file($pdfPath)) {
        $mail->addAttachment($pdfPath, "Factura_{$serie}{$folio}_{$uuid}.pdf");
    }

    $ok = $mail->send();

    echo json_encode([
        'success' => $ok ? true : false,
        'msg'     => $ok ? 'Correo enviado' : 'No fue posible enviar el correo'
    ], JSON_UNESCAPED_UNICODE);
    exit;
} catch (Exception $e) {
    echo json_encode(['success' => false, 'msg' => 'Mailer: ' . $e->getMessage()], JSON_UNESCAPED_UNICODE);
    exit;
}
