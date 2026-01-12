<?php
// Obtener ID de la cuenta por cobrar
$id_cxc = isset($_GET['folio']) ? (int)$_GET['folio'] : 0;

// Consulta de datos base
$cxc = $clsConsulta->consultaPreparada("
    SELECT c.id, cl.razon_social, f.folio, c.monto_total, c.monto_pagado, 
           (c.monto_total - c.monto_pagado) AS saldo
    FROM cuentas_por_cobrar c
    INNER JOIN cat_clientes cl ON c.id_cliente = cl.id
    INNER JOIN cab_facturas f ON c.id_factura = f.id
    WHERE c.id = ?", [$id_cxc])[0];
?>

<div class="container mt-4">
    <h5>Registrar pago a: <strong><?= $cxc['razon_social'] ?></strong></h5>
    <p><strong>Folio factura:</strong> <?= $cxc['folio'] ?> | 
       <strong>Total:</strong> $<?= number_format($cxc['monto_total'], 2) ?> | 
       <strong>Saldo:</strong> $<?= number_format($cxc['saldo'], 2) ?></p>

    <form id="formPagoCXC">
        <input type="hidden" name="id_cxc" value="<?= $cxc['id'] ?>">

        <div class="mb-3">
            <label>Fecha de pago:</label>
            <input type="date" name="fecha" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Monto:</label>
            <input type="number" name="monto" step="0.01" max="<?= $cxc['saldo'] ?>" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Referencia:</label>
            <input type="text" name="referencia" class="form-control">
        </div>

        <div class="mb-3">
            <label>Método de pago:</label>
            <select name="metodo_pago" class="form-control" required>
                <option value="">Selecciona una opción</option>
                <option>Efectivo</option>
                <option>Transferencia</option>
                <option>Depósito</option>
                <option>Tarjeta</option>
            </select>
        </div>

        <button type="submit" class="btn btn-primary">Registrar Pago</button>
    </form>
</div>

<script>
    $('#formPagoCXC').on('submit', function(e) {
        e.preventDefault();

        $.ajax({
            url: 'php/tabla-clientes-cxc.php',
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    alertify.success(response.message);
                    setTimeout(() => window.history.back(), 1000);
                } else {
                    alertify.error(response.message);
                }
            },
            error: function(xhr) {
                alertify.error("Error de conexión con el servidor.");
            }
        });
    });
</script>
