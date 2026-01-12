<?php
$idVendedor = $_SESSION["id_vendedor"];

// Primero obtenemos todos los pedidos del vendedor
$con = "
SELECT 
    c.id, c.num_pedido, c.fecha, c.estatus,
    cl.razon_social as cliente,
    COALESCE((SELECT SUM(precio * cantidad) FROM mov_pedidos WHERE id_pedido = c.id), 0) as total
FROM cab_pedidos c
LEFT JOIN cat_clientes cl ON c.id_cliente = cl.id
WHERE c.id_vendedor = $idVendedor
ORDER BY c.fecha DESC
";
$pedidos = $clsConsulta->consultaGeneral($con);
?>

<style>
    .card-header {
        background-color: #f8f9fa;
    }

    .btn-primary {
        background-color: #4e73df;
        border-color: #4e73df;
    }

    .btn-primary:hover {
        background-color: #3a5ec0;
        border-color: #3a5ec0;
    }

    .dropdown-item.active {
        background-color: #f8f9fa;
        color: #000;
        font-weight: 500;
    }

    .dropdown-item i {
        width: 20px;
        text-align: center;
    }

    #searchInput:focus {
        border-color: #4e73df;
        box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25);
    }

    .input-group-text {
        background-color: #f8f9fa;
    }

    #fechaDesde,
    #fechaHasta {
        max-width: 120px;
    }

    #aplicarFechas,
    #limpiarFechas {
        width: 40px;
    }

    .estado-icon {
        font-size: 1.2em;
    }

    @media (max-width: 768px) {

        #fechaDesde,
        #fechaHasta {
            max-width: 100%;
        }

        .col-md-6 {
            margin-bottom: 10px;
        }

        .card-header {
            flex-direction: column;
        }

    }


    .datepicker {
        background-color: white;
        cursor: pointer;
    }

    .ui-datepicker {
        z-index: 1151 !important;
        /* Para que aparezca sobre otros elementos */
    }

    @media (max-width: 576px) {
        .datepicker {
            font-size: 14px;
        }
    }

</style>

<div class="container-fluid py-3">
    <div class="card shadow-sm">
        <!-- Encabezado -->

        <div class="card-header bg-white border-0 py-3">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center">

                <div class="mb-3 mb-md-0">
                    <h1 class="h4 mb-1">
                        <i class="fas fa-clipboard-list text-success me-2"></i>
                        Mis Pedidos
                    </h1>
                    <p class="text-muted small mb-0">Administra y revisa todos tus pedidos </p>
                </div>

                <a href="pedidos-altas" class="btn btn-primary btn-lg">
                    <i class="fas fa-plus-circle me-2"></i>
                    Nuevo Pedido
                </a>                
            </div>
        </div>

        <!-- Filtros -->

        <div class="card-body border-top py-3">
            <div class="row g-3 align-items-center">
                <!-- Columna izquierda (Búsqueda y Fechas) -->
                <div class="col-md-8">
                    <div class="row g-2">
                        <!-- Barra de búsqueda -->
                        <div class="col-md-6">
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0">
                                    <i class="fas fa-search text-muted"></i>
                                </span>
                                <input type="text" id="searchInput" class="form-control border-start-0"
                                    placeholder="Buscar pedido o cliente...">
                                <button class="btn btn-outline-secondary" type="button" id="clearSearch">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Filtro por rango de fechas -->

                        <div class="col-md-6">
                            <div class="input-group">
                                <input type="text" id="fechaDesde" class="form-control datepicker" placeholder="Desde" readonly>
                                <span class="input-group-text">a</span>
                                <input type="text" id="fechaHasta" class="form-control datepicker" placeholder="Hasta" readonly>
                                <button class="btn btn-outline-secondary" type="button" id="aplicarFechas">
                                    <i class="fas fa-check"></i>
                                </button>
                                <button class="btn btn-outline-secondary" type="button" id="limpiarFechas">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Columna derecha (Filtros de estado y registros) -->

                <div class="col-md-4">
                    <div class="d-flex flex-wrap align-items-center justify-content-md-end gap-2">
                        <!-- Dropdown de estados -->
                        <div class="dropdown">
                            <button class="btn btn-outline-secondary dropdown-toggle" type="button"
                                id="dropdownFilter" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-filter me-2"></i>
                                Estado
                            </button>
                            <ul class="dropdown-menu" aria-labelledby="dropdownFilter">
                                <li><a class="dropdown-item filter-btn active" href="#" data-filter="">
                                        <i class="fas fa-list me-2"></i>Todos
                                    </a></li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li><a class="dropdown-item filter-btn" href="#" data-filter="pendiente">
                                        <i class="fas fa-clock text-warning me-2"></i>Pendientes
                                    </a></li>
                                <li><a class="dropdown-item filter-btn" href="#" data-filter="procesado">
                                        <i class="fas fa-check-circle text-success me-2"></i>Procesados
                                    </a></li>
                                <li><a class="dropdown-item filter-btn" href="#" data-filter="cancelado">
                                        <i class="fas fa-times-circle text-danger me-2"></i>Cancelados
                                    </a></li>
                            </ul>
                        </div>

                        <!-- Dropdown de registros por página -->

                        <div class="dropdown">
                            <button class="btn btn-outline-secondary dropdown-toggle" type="button"
                                id="dropdownRecords" data-bs-toggle="dropdown" aria-expanded="false">
                                Mostrar 10
                            </button>
                            <ul class="dropdown-menu" aria-labelledby="dropdownRecords">
                                <li><a class="dropdown-item records-per-page" href="#" data-records="10">10</a></li>
                                <li><a class="dropdown-item records-per-page" href="#" data-records="25">25</a></li>
                                <li><a class="dropdown-item records-per-page" href="#" data-records="50">50</a></li>
                                <li><a class="dropdown-item records-per-page" href="#" data-records="100">100</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabla de pedidos -->

        <div class="card-body p-0">
            <div class="table-responsive">
                <table id="tablaPedidos" class="table table-striped table-hover" style="width:100%">
                    <thead >
                        <tr class="bg-dark text-white">
                            <th>Pedido</th>
                            <th>Cliente</th>
                            <th>Fecha</th>
                            <th>Estado</th>
                            <th>Total</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php

                        if ($clsConsulta->numrows > 0) {
                            foreach ($pedidos as $pedido) {
                                $iconoEstado = '';
                                $colorClase = '';
                                $estadoTexto = ucfirst($pedido['estatus']);

                                switch ($pedido['estatus']) {
                                    case 'pendiente':
                                        $iconoEstado = 'fa-clock';
                                        $colorClase = 'text-warning';
                                        break;
                                    case 'procesado':
                                        $iconoEstado = 'fa-check-circle';
                                        $colorClase = 'text-success';
                                        break;
                                    case 'cancelado':
                                        $iconoEstado = 'fa-times-circle';
                                        $colorClase = 'text-danger';
                                        break;
                                }

                                echo '<tr>
                                        <td>
                                           <a href="pedido-detalle/' . $pedido['id'] . '" class="fw-bold">
                                                ' . htmlspecialchars($pedido['num_pedido'] ?? 'P-' . $pedido['id']) . '
                                            </a>
                                        </td>

                                        <td>' . htmlspecialchars($pedido['cliente'] ?? 'Cliente no especificado') . '</td>

                                        <td data-order="' . strtotime($pedido['fecha']) . '">' . date('d/m/Y H:i', strtotime($pedido['fecha'])) . '</td>
                                        <td data-filter="' . $pedido['estatus'] . '">
                                            <i class="fas ' . $iconoEstado . ' estado-icon ' . $colorClase . '" title="' . $estadoTexto . '"></i>
                                            <span class="d-none d-md-inline">' . $estadoTexto . '</span>
                                        </td>
                                        <td>$' . number_format($pedido['total'] ?? 0, 2) . '</td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="pedido-detalle.php?id=' . $pedido['id'] . '" class="btn btn-outline-primary" title="Ver detalle">
                                                    <i class="fas fa-eye"></i>
                                                </a>';
                                // Solo muestra editar si está pendiente

                                if ($pedido['estatus'] == 'pendiente') {

                                    echo '<a href="pedido-editar.php?id=' . $pedido['id'] . '" class="btn btn-outline-secondary" title="Editar">
                                                    <i class="fas fa-edit"></i>
                                                </a>';
                                }
                                echo '</div>
                                        </td>
                                    </tr>';
                            }
                        } 
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Moment.js para manejo de fechas -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/locale/es.min.js"></script>
<script>
    $(document).ready(function() {
        // Configuración del datepicker
        $.datepicker.regional['es'] = {
            closeText: 'Cerrar',
            prevText: '< Ant',
            nextText: 'Sig >',
            currentText: 'Hoy',
            monthNames: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'],
            monthNamesShort: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'],
            dayNames: ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'],
            dayNamesShort: ['Dom', 'Lun', 'Mar', 'Mié', 'Juv', 'Vie', 'Sáb'],
            dayNamesMin: ['Do', 'Lu', 'Ma', 'Mi', 'Ju', 'Vi', 'Sá'],
            weekHeader: 'Sm',
            dateFormat: 'yy-mm-dd',
            firstDay: 1,
            isRTL: false,
            showMonthAfterYear: false,
            yearSuffix: ''
        };

        $.datepicker.setDefaults($.datepicker.regional['es']);
        // Inicializar datepickers
        $("#fechaDesde, #fechaHasta").datepicker({
            changeMonth: true,
            changeYear: true,
            maxDate: new Date(),
            onSelect: function(selectedDate) {
                if (this.id === "fechaDesde") {
                    $("#fechaHasta").datepicker("option", "minDate", selectedDate);
                } else if (this.id === "fechaHasta") {
                    $("#fechaDesde").datepicker("option", "maxDate", selectedDate);
                }
            }
        });

        // Inicializar DataTable

        var table = $('#tablaPedidos').DataTable({
            language: {
                url: 'https://cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json'
            },
            dom: '<"top"lf>rt<"bottom"ip>',
            order: [
                [2, 'desc']

            ], // Ordenar por fecha descendente
            responsive: true,
            columnDefs: [{
                    targets: [3], // Columna de estado
                    searchable: true,
                    render: function(data, type, row) {
                        if (type === 'filter') {
                            return $(data).find('i').attr('title') || data;
                        }
                        return data;
                    }
                },
                {
                    targets: [2], // Columna de fecha
                    type: 'date-euro' // Tipo especial para formato dd/mm/yyyy
                }
            ],

            initComplete: function() {
                // Inicializar tooltips
                $('[title]').tooltip({
                    placement: 'top',
                    trigger: 'hover'
                });
            }
        });

        // Tipo de ordenación para fechas en formato dd/mm/yyyy
        $.fn.dataTable.ext.type.order['date-euro-pre'] = function(data) {
            if (!data) return 0;
            var dateParts = data.split(/[/ :]/);
            if (dateParts.length >= 5) {
                return Date.UTC(
                    parseInt(dateParts[2], 10),
                    parseInt(dateParts[1], 10) - 1,
                    parseInt(dateParts[0], 10),
                    parseInt(dateParts[3], 10),
                    parseInt(dateParts[4], 10)
                );
            }
            return 0;
        };
        
        // Función para aplicar filtro de fechas (actualizada)

        function aplicarFiltroFechas() {
            var fechaDesde = $('#fechaDesde').val();
            var fechaHasta = $('#fechaHasta').val();

            if (fechaDesde || fechaHasta) {
                $.fn.dataTable.ext.search.push(
                    function(settings, data, dataIndex) {
                        var fechaPedidoStr = data[2]; // Formato dd/mm/yyyy HH:mm
                        var fechaPedido = moment(fechaPedidoStr, 'DD/MM/YYYY HH:mm').format('YYYY-MM-DD');
                        var desdeValido = !fechaDesde || fechaPedido >= fechaDesde;
                        var hastaValido = !fechaHasta || fechaPedido <= fechaHasta;
                        return desdeValido && hastaValido;
                    }
                );
            } else {
                $.fn.dataTable.ext.search.pop();
            }
            table.draw();
        }

        // Eventos para los controles de fecha
        $('#aplicarFechas').click(aplicarFiltroFechas);
        $('#limpiarFechas').click(function() {
            $('#fechaDesde').val('');
            $('#fechaHasta').val('');
            $.fn.dataTable.ext.search.pop();
            table.draw();
        });

        // También se puede aplicar al cambiar las fechas directamente
        $('#fechaDesde, #fechaHasta').change(aplicarFiltroFechas);
        // Búsqueda general
        $('#searchInput').keyup(function() {
            table.search($(this).val()).draw();
        }).focus(function() {
            $(this).select();
        });

        $('#clearSearch').click(function() {

            $('#searchInput').val('').trigger('keyup');

        });

        // Filtrado por estado
        $('.filter-btn').click(function(e) {
            e.preventDefault();
            $('.filter-btn').removeClass('active');
            $(this).addClass('active');
            var filterValue = $(this).data('filter');
            table.columns(3).search(filterValue).draw();
        });

        // Cambiar número de registros mostrados
        $('.records-per-page').click(function(e) {
            e.preventDefault();
            var records = $(this).data('records');
            table.page.len(records).draw();
            $('#dropdownRecords').html(`Mostrar ${records}`);
        });
    });

</script>