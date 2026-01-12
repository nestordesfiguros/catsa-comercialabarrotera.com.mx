<?php
// admin/ajax/reportes/exportar-excel-stock-minimo-maximo.php
require_once '../../lib/clsConsultas.php';

// Obtener parámetros
$idAlmacen = $_GET['id_almacen'] ?? 0;
$idCategoria = $_GET['id_categoria'] ?? 0;
$idProveedor = $_GET['id_proveedor'] ?? 0;
$tipoAlerta = $_GET['tipo_alerta'] ?? '';
$ordenarPor = $_GET['ordenar_por'] ?? 'diferencia_min';
$mostrarSolo = $_GET['mostrar_solo'] ?? '';
$nivelCritico = $_GET['nivel_critico'] ?? 20;

$clsConsulta = new Consultas();

// Construir consulta (misma que en PDF)
$sql = "
    SELECT 
        cp.id_producto,
        cp.clave,
        cp.nombre AS producto,
        cc.nombre_categoria AS categoria,
        ca.almacen,
        COALESCE(i.cantidad, 0) AS existencia,
        cp.stock_minimo,
        cp.stock_maximo,
        (COALESCE(i.cantidad, 0) - cp.stock_minimo) AS diferencia_min,
        (COALESCE(i.cantidad, 0) - cp.stock_maximo) AS diferencia_max,
        CASE 
            WHEN cp.stock_minimo > 0 THEN 
                (COALESCE(i.cantidad, 0) / cp.stock_minimo) * 100 
            ELSE 100 
        END AS porcentaje_min,
        cp.precio_compra AS costo_unitario,
        (cp.stock_minimo - COALESCE(i.cantidad, 0)) * cp.precio_compra AS valor_riesgo,
        cp.estado
    FROM 
        cat_productos cp
    LEFT JOIN 
        inventarios i ON cp.id_producto = i.id_producto
    LEFT JOIN 
        cat_categorias cc ON cp.id_categoria = cc.id_categoria
    LEFT JOIN 
        cat_almacenes ca ON i.id_almacen = ca.id
    LEFT JOIN 
        cat_proveedores cprov ON cp.id_proveedor = cprov.id
    WHERE 
        cp.estado = 'activo'
";

$params = [];
$types = "";

// Aplicar filtros (igual que en PDF)
if ($idAlmacen > 0) {
    $sql .= " AND i.id_almacen = ?";
    $params[] = $idAlmacen;
    $types .= "i";
}

if ($idCategoria > 0) {
    $sql .= " AND cp.id_categoria = ?";
    $params[] = $idCategoria;
    $types .= "i";
}

if ($idProveedor > 0) {
    $sql .= " AND cp.id_proveedor = ?";
    $params[] = $idProveedor;
    $types .= "i";
}

if (!empty($tipoAlerta)) {
    switch ($tipoAlerta) {
        case 'stock_minimo':
            $sql .= " AND COALESCE(i.cantidad, 0) <= cp.stock_minimo";
            break;
        case 'stock_maximo':
            $sql .= " AND COALESCE(i.cantidad, 0) >= cp.stock_maximo AND cp.stock_maximo > 0";
            break;
        case 'sin_existencia':
            $sql .= " AND COALESCE(i.cantidad, 0) = 0";
            break;
        case 'critico':
            $sql .= " AND COALESCE(i.cantidad, 0) > 0 AND COALESCE(i.cantidad, 0) <= cp.stock_minimo AND cp.stock_minimo > 0";
            break;
    }
}

if ($mostrarSolo === 'con_alerta') {
    $sql .= " AND (COALESCE(i.cantidad, 0) <= cp.stock_minimo OR COALESCE(i.cantidad, 0) >= cp.stock_maximo OR COALESCE(i.cantidad, 0) = 0)";
} elseif ($mostrarSolo === 'sin_alerta') {
    $sql .= " AND COALESCE(i.cantidad, 0) > cp.stock_minimo AND (COALESCE(i.cantidad, 0) < cp.stock_maximo OR cp.stock_maximo = 0)";
}

// Ordenamiento
switch ($ordenarPor) {
    case 'diferencia_max':
        $orderColumn = 'diferencia_max';
        break;
    case 'producto':
        $orderColumn = 'cp.nombre';
        break;
    case 'existencia':
        $orderColumn = 'existencia';
        break;
    case 'nivel_critico':
        $orderColumn = 'porcentaje_min';
        break;
    default:
        $orderColumn = 'diferencia_min';
}

$sql .= " ORDER BY $orderColumn ASC, cp.nombre ASC";

// Ejecutar consulta
$datos = $clsConsulta->consultaPreparada($sql, $params, $types);

// Configurar headers para Excel
header('Content-Type: application/vnd.ms-excel; charset=utf-8');
header('Content-Disposition: attachment; filename="Reporte_Stock_Minimo_Maximo_' . date('Y-m-d') . '.xls"');
header('Pragma: no-cache');
header('Expires: 0');

// BOM para UTF-8
echo "\xEF\xBB\xBF";

// Crear contenido Excel
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <style>
        table {
            border-collapse: collapse;
            width: 100%;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 5px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }

        .title {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .subtitle {
            font-size: 14px;
            margin-bottom: 15px;
            color: #666;
        }

        .total {
            font-weight: bold;
            background-color: #e6f3ff;
        }

        .critico {
            background-color: #ffcccc;
        }

        .minimo {
            background-color: #fff2cc;
        }

        .maximo {
            background-color: #ccf2ff;
        }

        .normal {
            background-color: #ccffcc;
        }
    </style>
</head>

<body>
    <div class="title">Reporte de Stock Mínimo y Máximo</div>
    <div class="subtitle">
        Generado: <?php echo date('d/m/Y H:i:s'); ?><br>
        Filtros aplicados:
        <?php
        $filtros = [];
        if ($idAlmacen > 0) {
            $sqlAlmacen = "SELECT almacen FROM cat_almacenes WHERE id = ?";
            $almacen = $clsConsulta->consultaPreparada($sqlAlmacen, [$idAlmacen], "i");
            $filtros[] = "Almacén: " . $almacen[0]['almacen'];
        }
        if ($idCategoria > 0) {
            $sqlCategoria = "SELECT nombre_categoria FROM cat_categorias WHERE id_categoria = ?";
            $categoria = $clsConsulta->consultaPreparada($sqlCategoria, [$idCategoria], "i");
            $filtros[] = "Categoría: " . $categoria[0]['nombre_categoria'];
        }
        if ($idProveedor > 0) {
            $sqlProveedor = "SELECT razon_social FROM cat_proveedores WHERE id = ?";
            $proveedor = $clsConsulta->consultaPreparada($sqlProveedor, [$idProveedor], "i");
            $filtros[] = "Proveedor: " . $proveedor[0]['razon_social'];
        }
        if (!empty($tipoAlerta)) {
            $alertasText = [
                'stock_minimo' => 'Stock Mínimo Alcanzado',
                'stock_maximo' => 'Stock Máximo Excedido',
                'sin_existencia' => 'Sin Existencia',
                'critico' => 'Stock Crítico'
            ];
            $filtros[] = "Alerta: " . ($alertasText[$tipoAlerta] ?? $tipoAlerta);
        }
        if (!empty($mostrarSolo)) {
            $mostrarText = [
                'con_alerta' => 'Con Alertas',
                'sin_alerta' => 'Sin Alertas',
                'activos' => 'Productos Activos'
            ];
            $filtros[] = "Mostrar: " . ($mostrarText[$mostrarSolo] ?? $mostrarSolo);
        }
        $filtros[] = "Nivel Crítico: " . $nivelCritico . "%";
        echo implode(' | ', $filtros);
        ?>
    </div>

    <table>
        <thead>
            <tr>
                <th>Producto</th>
                <th>Categoría</th>
                <th>Almacén</th>
                <th>Existencia</th>
                <th>Stock Mínimo</th>
                <th>Stock Máximo</th>
                <th>Diferencia Mín</th>
                <th>Diferencia Máx</th>
                <th>% Stock Mín</th>
                <th>Nivel</th>
                <th>Alertas</th>
                <th>Valor en Riesgo</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $totalValorRiesgo = 0;
            $resumen = [
                'stock_minimo' => 0,
                'stock_maximo' => 0,
                'sin_existencia' => 0,
                'stock_critico' => 0
            ];

            if ($datos && count($datos) > 0) {
                foreach ($datos as $item) {
                    $existencia = intval($item['existencia']);
                    $stockMinimo = intval($item['stock_minimo']);
                    $stockMaximo = intval($item['stock_maximo']);
                    $diferenciaMin = intval($item['diferencia_min']);
                    $diferenciaMax = intval($item['diferencia_max']);
                    $porcentajeMin = floatval($item['porcentaje_min']);
                    $valorRiesgo = floatval($item['valor_riesgo']);

                    $totalValorRiesgo += max(0, $valorRiesgo);

                    // Determinar nivel y alertas
                    $nivel = '';
                    $alertas = [];
                    $rowClass = '';

                    if ($existencia === 0) {
                        $nivel = 'Sin Existencia';
                        $alertas[] = 'Sin Stock';
                        $rowClass = 'critico';
                        $resumen['sin_existencia']++;
                    } elseif ($existencia <= $stockMinimo) {
                        $nivel = 'Stock Mínimo';
                        $alertas[] = 'Mínimo';
                        $rowClass = 'minimo';
                        $resumen['stock_minimo']++;

                        if ($stockMinimo > 0 && $porcentajeMin <= $nivelCritico) {
                            $alertas[] = 'Crítico';
                            $nivel = 'Crítico';
                            $rowClass = 'critico';
                            $resumen['stock_critico']++;
                        }
                    } elseif ($stockMaximo > 0 && $existencia >= $stockMaximo) {
                        $nivel = 'Stock Máximo';
                        $alertas[] = 'Máximo';
                        $rowClass = 'maximo';
                        $resumen['stock_maximo']++;
                    } else {
                        $nivel = 'Normal';
                        $rowClass = 'normal';
                    }

                    echo '<tr class="' . $rowClass . '">';
                    echo '<td>' . htmlspecialchars($item['clave'] . ' - ' . $item['producto']) . '</td>';
                    echo '<td>' . htmlspecialchars($item['categoria'] ?? 'Sin categoría') . '</td>';
                    echo '<td>' . htmlspecialchars($item['almacen'] ?? 'N/A') . '</td>';
                    echo '<td>' . number_format($existencia) . '</td>';
                    echo '<td>' . number_format($stockMinimo) . '</td>';
                    echo '<td>' . ($stockMaximo > 0 ? number_format($stockMaximo) : 'N/A') . '</td>';
                    echo '<td>' . $diferenciaMin . '</td>';
                    echo '<td>' . ($stockMaximo > 0 ? $diferenciaMax : 'N/A') . '</td>';
                    echo '<td>' . number_format($porcentajeMin, 1) . '%</td>';
                    echo '<td>' . $nivel . '</td>';
                    echo '<td>' . implode(', ', $alertas) . '</td>';
                    echo '<td>$' . number_format(max(0, $valorRiesgo), 2) . '</td>';
                    echo '</tr>';
                }
            } else {
                echo '<tr><td colspan="12" style="text-align: center;">No se encontraron registros</td></tr>';
            }
            ?>
        </tbody>
        <tfoot>
            <tr class="total">
                <td colspan="11" style="text-align: right;"><strong>TOTAL VALOR EN RIESGO:</strong></td>
                <td><strong>$<?php echo number_format($totalValorRiesgo, 2); ?></strong></td>
            </tr>
        </tfoot>
    </table>

    <br>
    <div><strong>Resumen de Alertas:</strong></div>
    <ul>
        <li>Stock Mínimo Alcanzado: <?php echo $resumen['stock_minimo']; ?></li>
        <li>Stock Máximo Excedido: <?php echo $resumen['stock_maximo']; ?></li>
        <li>Sin Existencia: <?php echo $resumen['sin_existencia']; ?></li>
        <li>Stock Crítico: <?php echo $resumen['stock_critico']; ?></li>
    </ul>
</body>

</html>