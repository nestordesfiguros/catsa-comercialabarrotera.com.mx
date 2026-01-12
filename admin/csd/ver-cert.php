<?php
header('Content-Type: text/plain; charset=utf-8');
$cer = @file_get_contents(__DIR__ . '/csd.cer.pem');
if (!$cer) {
    echo "No se leyó csd.cer.pem\n";
    exit;
}
$rc = @openssl_x509_read($cer);
$info = @openssl_x509_parse($rc, false);
echo "RFC (CN): " . ($info['subject']['x500UniqueIdentifier'] ?? $info['subject']['serialNumber'] ?? 'desconocido') . "\n";
echo "Serial (NoCertificado): " . ($info['serialNumber'] ?? 'n/a') . "\n";
echo "Subject: " . print_r($info['subject'], true) . "\n";
