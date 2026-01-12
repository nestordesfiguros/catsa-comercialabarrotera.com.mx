<?php
require '../vendor/autoload.php';

use setasign\Fpdi\Tcpdf\Fpdi;

// Lista de archivos PDF a combinar
$files = [
    'filespdf/archivo1.pdf',
    'filespdf/archivo2.pdf',
    'filespdf/archivo3.pdf'
];

// Crea una instancia de FPDI
$pdf = new Fpdi();

// Itera sobre cada archivo
foreach ($files as $file) {
    // Agrega una nueva página al documento final
    $pdf->AddPage();

    // Importa la página del archivo actual
    $pageCount = $pdf->setSourceFile($file);
    $tplIdx = $pdf->importPage(1);
    $pdf->useTemplate($tplIdx);

    // Si hay más de una página en el archivo, itera y agrega las páginas restantes
    for ($i = 2; $i <= $pageCount; $i++) {
        $pdf->AddPage();
        $tplIdx = $pdf->importPage($i);
        $pdf->useTemplate($tplIdx);
    }
}

// Ruta del archivo PDF final (directorio raíz + nombre de archivo)
$filePath = __DIR__ . '/archivo_final.pdf';

// Guarda el archivo PDF final
$pdf->Output($filePath, 'F');

echo 'Exito..!';
?>