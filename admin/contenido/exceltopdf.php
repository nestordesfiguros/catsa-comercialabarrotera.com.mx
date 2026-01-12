<?php

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

//Inizio blocco solo per test standalone
$new_excel_path = "documentos/Copia de admProductosRedMayoreo.xlsx";
$reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader("Xlsx");
$spreadsheet = $reader->load("$new_excel_path"); 
//Fine blocco solo per test standalone

echo 'Termina todo bien <br> <br><br><br>';

//Conversione della variabile spreadsheet in pdf


    //Creazione del writer
    $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Mpdf');

    //Salvataggio del pfd
    //$pdf_path = 'pdf_finali/'.$name.'.pdf';
    $pdf_path = 'pdf/prueba.pdf';
    echo '<br>';
    echo $pdf_path;
    $writer->save($pdf_path);

?>
