<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../lib/clsConsultas.php';
$clsConsulta = new Consultas();

$id = (int)($_POST['id'] ?? 0);
if ($id <= 0) {
    echo json_encode(['success' => false, 'msg' => 'Falta id']);
    exit;
}

$F = $clsConsulta->consultaGeneral("SELECT * FROM cab_facturas WHERE id={$id} LIMIT 1");
if ($clsConsulta->numrows <= 0) {
    echo json_encode(['success' => false, 'msg' => 'Factura no encontrada']);
    exit;
}
$F = $F[1];

if ($F['estatus'] === 'PENDIENTE') {
    // Reintenta timbrado reutilizando el endpoint de timbrar
    $_POST['id'] = $id;
    include __DIR__ . '/timbrar.php';
    exit;
}

if ($F['estatus'] === 'TIMBRADA') {
    // Regenerar: asegurar XML en disco y preparar apertura de PDF
    $xmlFile = __DIR__ . '/../timbrado/timbrados/' . $F['serie'] . '_' . $F['folio'] . '.xml';
    $pdfUrl  = 'ajax/facturas/pdf.php?id=' . $id; // abre en pestaña; tu pdf.php genera on-the-fly

    // Si no existe XML en disco, no podemos re-timbrar (ilegal). Dejamos aviso.
    if (!file_exists($xmlFile)) {
        // Si guardas XML en DB, podrías reescribirlo aquí. Si no, avisa:
        echo json_encode([
            'success' => true,
            'action' => 'regenerado',
            'msg' => 'XML no encontrado en disco. Se abrirá el PDF generado dinámicamente.',
            'open_pdf' => $pdfUrl
        ]);
        exit;
    }

    echo json_encode([
        'success' => true,
        'action' => 'regenerado',
        'open_pdf' => $pdfUrl
    ]);
    exit;
}

echo json_encode(['success' => false, 'msg' => 'Acción no válida para el estatus actual']);
