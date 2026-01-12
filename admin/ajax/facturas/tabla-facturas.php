<?php
// ajax/facturas/tabla-facturas.php 
header('Content-Type: application/json; charset=utf-8');
@ini_set('display_errors', 0);
@ini_set('html_errors', 0);

require_once __DIR__ . '/../../lib/clsConsultas.php';
$clsConsulta = new Consultas();

/**
 * Donde timbrar.php guarda los archivos:
 *   FS (servidor):  __DIR__ . '/../timbrados'
 *   WEB (público):  ajusta según tu routing. Si tu carpeta /ajax/facturas/../timbrados
 *                   es accesible como /timbrados en web, usa 'timbrados'.
 */
$FS_TIMBRADOS  = realpath(__DIR__ . '/../timbrados') ?: (__DIR__ . '/../timbrados');
$WEB_TIMBRADOS = 'ajax/timbrados'; // ruta web correcta: /admin/ajax/timbrados


$sql = "
  SELECT
    f.id, f.serie, f.folio, f.fecha, f.subtotal, f.total, f.uuid, f.fecha_timbrado,
    IFNULL(f.timbrada, 0) AS timbrada,
    c.razon_social
  FROM cab_facturas f
  JOIN cat_clientes c ON c.id = f.id_receptor
  WHERE IFNULL(f.activo,1) = 1
  ORDER BY f.fecha DESC, f.id DESC
";
$rs = $clsConsulta->consultaGeneral($sql);

$data = [];

if (is_array($rs) && $clsConsulta->numrows > 0) {
    foreach ($rs as $row) {
        $id       = (int)$row['id'];
        $serie    = (string)$row['serie'];
        $folio    = (string)$row['folio'];
        $folioUI  = '<div class="text-center"><code>' . htmlspecialchars($serie . $folio) . '</code></div>';

        $fechaUI  = '<div class="text-center">' . htmlspecialchars($row['fecha']) . '</div>';
        $cliente  = htmlspecialchars($row['razon_social']);

        $subtotal = (float)($row['subtotal'] ?? 0);
        $total    = (float)($row['total'] ?? 0);

        $subtotalUI = '<div class="text-end">$' . number_format($subtotal, 2, '.', ',') . '</div>';
        $totalUI    = '<div class="text-end"><b>$' . number_format($total, 2, '.', ',') . '</b></div>';

        // ¿Timbrada?
        $uuidCol   = isset($row['uuid']) ? trim((string)$row['uuid']) : '';
        $timbradaC = ((int)$row['timbrada'] === 1);
        $timbrada  = $timbradaC || ($uuidCol !== '');

        // Rutas de archivos
        $xmlFS  = $FS_TIMBRADOS . "/factura_{$id}.xml";
        $xmlWeb = $WEB_TIMBRADOS . "/factura_{$id}.xml";
        $xmlOK  = is_file($xmlFS);

        // -------- Columna "Ver" --------
        if ($timbrada) {
            $verBtns = [];

            // XML (si existe) — ahora DESCARGA el archivo
            if ($xmlOK) {
                $fileName = "factura_{$serie}{$folio}" . ($uuidCol ? "_{$uuidCol}" : "") . ".xml";
                $verBtns[] = '<a class="btn btn-outline-secondary btn-sm me-1" '
                    . 'href="' . htmlspecialchars($xmlWeb) . '" '
                    . 'download="' . htmlspecialchars($fileName) . '" '
                    . 'title="Descargar XML"><i class="fa fa-file-code"></i> XML</a>';
            } else {
                $verBtns[] = '<button class="btn btn-outline-secondary btn-sm me-1" type="button" disabled title="XML no disponible"><i class="fa fa-file-code"></i> XML</button>';
            }

            // PDF (siempre va a generador)
            $verBtns[] = '<a class="btn btn-outline-secondary btn-sm" href="pdf/pdf-factura.php?id=' . $id . '" target="_blank" title="Ver PDF"><i class="fa fa-file-pdf"></i> PDF</a>';

            // *** Se QUITA el botón de QR ***
            $verUI = '<div class="text-center">' . implode('', $verBtns) . '</div>';
        } else {
            $verUI = '<div class="text-center">'
                . '<button class="btn btn-outline-secondary btn-sm me-1" type="button" disabled title="XML"><i class="fa fa-file-code"></i> XML</button>'
                . '<button class="btn btn-outline-secondary btn-sm" type="button" disabled title="PDF"><i class="fa fa-file-pdf"></i> PDF</button>'
                . '</div>';
        }

        // -------- Estatus (SOLO estatus) --------
        if ($timbrada) {
            $estatusCol = '<div class="text-center"><span class="badge bg-success p-2"><i class="fa fa-stamp me-1"></i>Timbrada</span></div>';
        } else {
            $estatusCol = '<div class="text-center"><span class="badge bg-secondary p-2">Borrador</span></div>';
        }

        // -------- Acciones (SOLO botones de acción) --------
        $btnEnviar = '<button type="button" class="btn btn-sm btn-primary btn-enviar-cfdi" '
            . 'title="Enviar CFDI por correo" data-id="' . $id . '">'
            . '<i class="fa fa-envelope"></i></button>';

        $btnCancelar = '<button type="button" class="btn btn-sm btn-danger btn-cancelar-cfdi" '
            . 'title="Cancelar CFDI" data-id="' . $id . '">'
            . '<i class="fa fa-times-circle"></i></button>';

        if ($timbrada) {
            // Acciones para timbrada
            $acciones = '<div class="d-flex justify-content-center align-items-center gap-2">'
                . '<button class="btn btn-outline-primary btn-sm btn-retimbrar" type="button" data-id="' . $id . '" title="Regenerar archivos / Reintentar"><i class="fa fa-sync-alt"></i></button>'
                . '<button class="btn btn-secondary btn-sm" type="button" disabled title="Editar (bloqueado)"><i class="fa fa-edit"></i></button>'
                . ' ' . $btnEnviar
                . ' ' . $btnCancelar
                . '</div>';
        } else {
            // Acciones para borrador
            $acciones = '<div class="d-flex justify-content-center align-items-center gap-2">'
                . '<button class="btn btn-warning btn-sm btn-timbrar" type="button" data-id="' . $id . '"><i class="fa fa-stamp"></i> Timbrar</button>'
                . '<button class="btn btn-outline-primary btn-sm btn-retimbrar" type="button" data-id="' . $id . '" title="Reintentar timbrado"><i class="fa fa-sync-alt"></i></button>'
                . '<a class="btn btn-primary btn-sm" href="facturas-modificar/' . $id . '" title="Editar"><i class="fa fa-edit"></i></a>'
                . '</div>';
        }

        // Orden de columnas del DataTable (ahora son 8 columnas)
        $data[] = [
            $folioUI,    // 1 Folio
            $fechaUI,    // 2 Fecha
            $cliente,    // 3 Cliente
            $subtotalUI, // 4 Subtotal
            $totalUI,    // 5 Total
            $verUI,      // 6 Ver
            $estatusCol, // 7 Estatus (solo estatus)
            $acciones    // 8 Acciones (solo botones)
        ];
    }
}

// Respuesta para DataTables
echo json_encode(['data' => $data], JSON_UNESCAPED_UNICODE);
exit;
