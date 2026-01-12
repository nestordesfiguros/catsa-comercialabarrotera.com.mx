<div class="container-fluid py-3">
    <div class="card shadow-sm">
        <!-- Encabezado -->
        <div class="card-header bg-white border-0 py-3">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center">
                <div class="mb-3 mb-md-0">
                    <h1 class="h4 mb-1">
                        <i class="fas fa-clipboard-list text-primary me-2"></i>
                        Mis Clientes
                    </h1>
                    <p class="text-muted small mb-0">Administra y revisa todos tus pedidos</p>
                </div>
            </div>
        </div>

        <div class="card-body border-top py-3">
            <table id="tablaClientes" class="table table-striped table-hover" style="width:100%">
                <thead>
                    <tr class="bg-dark text-white">
                        <th>Razón Social</th>
                        <th>Nombre Comercial</th>
                        <th class="text-center">Ubicación</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $rs = $clsConsulta->consultaGeneral("SELECT
                        cat_clientes.razon_social,
                        cat_clientes.nombre_comercial,
                        cat_clientes.mapa
                    FROM
                        vendedores_clientes
                        INNER JOIN cat_clientes 
                            ON (vendedores_clientes.id_cliente = cat_clientes.id) 
                    WHERE vendedores_clientes.id_vendedor=" . $_SESSION['id_vendedor']);

                    if ($clsConsulta->numrows > 0) {
                        foreach ($rs as $val) {
                            $razon_social = $val['razon_social'];
                            $nombre_comercial = $val['nombre_comercial'] ?? '';
                            $mapa_link = '';

                            if (!empty($val['mapa'])) {
                                $mapa_link = '<a href="' . $val['mapa'] . '" target="_blank" title="Ver ubicación">
                            <i class="fas fa-map-marker-alt text-info"></i>
                          </a>';
                            }
                    ?>
                            <tr>
                                <td><?= $razon_social; ?></td>
                                <td><?= $nombre_comercial; ?></td>
                                <td class="text-center"><?= $mapa_link; ?></td>
                            </tr>
                    <?php
                        }
                    }
                    ?>

                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Asegúrate de tener estos scripts antes de este bloque -->
<script>
    $(document).ready(function() {
        $('#tablaClientes').DataTable({
            language: {
                url: 'https://cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json'
            },
            dom: '<"top"lf>rt<"bottom"ip>',
            responsive: true,
            initComplete: function() {
                $('[title]').tooltip({
                    placement: 'top',
                    trigger: 'hover'
                });
            }
        });
    });
</script>