<?php
// admin/ajax/reportes/exportar-pdf-stock-minimo-maximo.php
require_once '../../lib/fpdf/fpdf.php';
require_once '../../lib/clsConsultas.php';

class PDF extends FPDF
{
    private $titulo;
    private $filtros;

    function setTitulo($titulo, $filtros)
    {
        $this->titulo = $titulo;
        $this->filtros = $filtros;
    }

    function Header()
    {
        // Logo
        $this->Image('../../assets/img/logo.png', 10, 8, 30);

        // Título
        $this->SetFont('Arial', 'B', 16);
        $this->Cell(0, 10, utf8_decode($this->titulo), 0, 1, 'C');

        // Filtros aplicados
        $this->SetFont('Arial', 'I', 10);
        $this->Cell(0, 8, utf8_decode($this->filtros), 0, 1, 'C');

        // Fecha de generación
        $this->SetFont('Arial', '', 10);
        $this->Cell(0, 6, 'Generado: ' . date('d/m/Y H:i:s'), 0, 1, 'C');

        // Línea separadora
        $this->Line(10, 30, 200, 30);
        $this->Ln(5);
    }

    function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, utf8_decode('Página ') . $this->PageNo() . '/{nb}', 0, 0, 'C');
    }

    function ImprovedTable($header, $data)
    {
        // Anchuras de las columnas
        $w = array(40, 25, 20, 15, 15, 15, 20, 20, 20, 25, 25);

        // Cabecera
        $this->SetFillColor(200, 220, 255);
        $this->SetTextColor(0);
        $this->SetDrawColor(0);
        $this->SetLineWidth(.3);
        $this->SetFont('Arial', 'B', 8);

        for ($i = 0; $i < count($header); $i++)
            $this->Cell($w[$i], 7, utf8_decode($header[$i]), 1, 0, 'C', true);
        $this->Ln();

        // Datos
        $this->SetFillColor(224, 235, 255);
        $this->SetTextColor(0);
        $this->SetFont('Arial', '', 7);

        $fill = false;
        $totalValorRiesgo = 0;

        foreach ($data as $row) {
            $this->Cell($w[0], 6, utf8_decode($row['producto']), 'LR', 0, 'L', $fill);
            $this->Cell($w[1], 6, utf8_decode($row['categoria']), 'LR', 0, 'L', $fill);
            $this->Cell($w[2], 6, utf8_decode($row['almacen']), 'LR', 0, 'L', $fill);
            $this->Cell($w[3], 6, utf8_decode($row['existencia']), 'LR', 0, 'C', $fill);
            $this->Cell($w[4], 6, utf8_decode($row['stock_minimo']), 'LR', 0, 'C', $fill);
            $this->Cell($w[5], 6, utf8_decode($row['stock_maximo']), 'LR', 0, 'C', $fill);
            $this->Cell($w[6], 6, utf8_decode($row['diferencia_min']), 'LR', 0, 'C', $fill);
            $this->Cell($w[7], 6, utf8_decode($row['diferencia_max']), 'LR', 0, 'C', $fill);
            $this->Cell($w[8], 6, utf8_decode($row['porcentaje_min']), 'LR', 0, 'C', $fill);
            $this->Cell($w[9], 6, utf8_decode($row['nivel']), 'LR', 0, 'C', $fill);
            $this->Cell($w[10], 6, utf8_decode($row['valor_riesgo']), 'LR', 0, 'R', $fill);
            $this->Ln();

            $valor = floatval(str_replace(['$', ','], '', $row['valor_riesgo']));
            $totalValorRiesgo += $valor;
            $fill = !$fill;

            // Verificar si necesita nueva página
            if ($this->GetY() > 260) {
                $this->AddPage();
                // Redibujar cabecera de tabla
                $this->SetFont('Arial', 'B', 8);
                for ($i = 0; $i < count($header); $i++)
                    $this->Cell($w[$i], 7, utf8_decode($header[$i]), 1, 0, 'C', true);
                $this->Ln();
                $this->SetFont('Arial', '', 7);
                $fill = false;
            }
        }

        // Línea de cierre
        $this->Cell(array_sum($w), 0, '', 'T');
        $this->Ln();

        // Total
        $this->SetFont('Arial', 'B', 9);
        $this->Cell(array_sum($w) - $w[10], 6, 'TOTAL VALOR EN RIESGO:', 1, 0, 'R', true);
        $this->Cell($w[10], 6, '$' . number_format($totalValorRiesgo, 2), 1, 1, 'R', true);
    }
}

// Obtener parámetros
$idAlmacen = $_GET['id_almacen'] ?? 0;
$idCategoria = $_GET['id_categoria'] ?? 0;
$idProveedor = $_GET['id_proveedor'] ?? 0;
$tipoAlerta = $_GET['tipo_alerta'] ?? '';
$ordenarPor = $_GET['ordenar_por'] ?? 'diferencia_min';
$mostrarSolo = $_GET['mostrar_solo'] ?? '';
$nivelCritico = $_GET['nivel_critico'] ?? 20;

$clsConsulta = new Consultas();

// Construir consulta (similar a la del DataTable)
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

// Aplicar filtros
$filtrosTexto = "Filtros aplicados: ";

if ($idAlmacen > 0) {
    $sql .= " AND i.id_almacen = ?";
    $params[] = $idAlmacen;
    $types .= "i";

    $sqlAlmacen = "SELECT almacen FROM cat_almacenes WHERE id = ?";
    $almacen = $clsConsulta->consultaPreparada($sqlAlmacen, [$idAlmacen], "i");
    $filtrosTexto .= "Almacén: " . $almacen[0]['almacen'] . ", ";
}

if ($idCategoria > 0) {
    $sql .= " AND cp.id_categoria = ?";
    $params[] = $idCategoria;
    $types .= "i";

    $sqlCategoria = "SELECT nombre_categoria FROM cat_categorias WHERE id_categoria = ?";
    $categoria = $clsConsulta->consultaPreparada($sqlCategoria, [$idCategoria], "i");
    $filtrosTexto .= "Categoría: " . $categoria[0]['nombre_categoria'] . ", ";
}

if ($idProveedor > 0) {
    $sql .= " AND cp.id_proveedor = ?";
    $params[] = $idProveedor;
    $types .= "i";

    $sqlProveedor = "SELECT razon_social FROM cat_proveedores WHERE id = ?";
    $proveedor = $clsConsulta->consultaPreparada($sqlProveedor, [$idProveedor], "i");
    $filtrosTexto .= "Proveedor: " . $proveedor[0]['razon_social'] . ", ";
}

if (!empty($tipoAlerta)) {
    switch ($tipoAlerta) {
        case 'stock_minimo':
            $sql .= " AND COALESCE(i.cantidad, 0) <= cp.stock_minimo";
            $filtrosTexto .= "Alerta: Stock Mínimo, ";
            break;
        case 'stock_maximo':
            $sql .= " AND COALESCE(i.cantidad, 0) >= cp.stock_maximo AND cp.stock_maximo > 0";
            $filtrosTexto .= "Alerta: Stock Máximo, ";
            break;
        case 'sin_existencia':
            $sql .= " AND COALESCE(i.cantidad, 0) = 0";
            $filtrosTexto .= "Alerta: Sin Existencia, ";
            break;
        case 'critico':
            $sql .= " AND COALESCE(i.cantidad, 0) > 0 AND COALESCE(i.cantidad, 0) <= cp.stock_minimo AND cp.stock_minimo > 0";
            $filtrosTexto .= "Alerta: Stock Crítico, ";
            break;
    }
}

if ($mostrarSolo === 'con_alerta') {
    $sql .= " AND (COALESCE(i.cantidad, 0) <= cp.stock_minimo OR COALESCE(i.cantidad, 0) >= cp.stock_maximo OR COALESCE(i.cantidad, 0) = 0)";
    $filtrosTexto .= "Mostrar: Con Alertas, ";
} elseif ($mostrarSolo === 'sin_alerta') {
    $sql .= " AND COALESCE(i.cantidad, 0) > cp.stock_minimo AND (COALESCE(i.cantidad, 0) < cp.stock_maximo OR cp.stock_maximo = 0)";
    $filtrosTexto .= "Mostrar: Sin Alertas, ";
}

$filtrosTexto .= "Nivel Crítico: " . $nivelCritico . "%";

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

// Preparar datos para la tabla
$data = [];
if ($datos && count($datos) > 0) {
    foreach ($datos as $item) {
        $existencia = intval($item['existencia']);
        $stockMinimo = intval($item['stock_minimo']);
        $stockMaximo = intval($item['stock_maximo']);
        $diferenciaMin = intval($item['diferencia_min']);
        $diferenciaMax = intval($item['diferencia_max']);
        $porcentajeMin = floatval($item['porcentaje_min']);
        $valorRiesgo = floatval($item['valor_riesgo']);

        // Determinar nivel
        if ($existencia === 0) {
            $nivel = 'Sin Existencia';
        } elseif ($existencia <= $stockMinimo) {
            $nivel = 'Stock Mínimo';
            if ($stockMinimo > 0 && $porcentajeMin <= $nivelCritico) {
                $nivel = 'Crítico';
            }
        } elseif ($stockMaximo > 0 && $existencia >= $stockMaximo) {
            $nivel = 'Stock Máximo';
        } else {
            $nivel = 'Normal';
        }

        $data[] = [
            'producto' => $item['clave'] . ' - ' . substr($item['producto'], 0, 30),
            'categoria' => $item['categoria'] ?? 'Sin categoría',
            'almacen' => $item['almacen'] ?? 'N/A',
            'existencia' => number_format($existencia),
            'stock_minimo' => number_format($stockMinimo),
            'stock_maximo' => $stockMaximo > 0 ? number_format($stockMaximo) : 'N/A',
            'diferencia_min' => $diferenciaMin,
            'diferencia_max' => $stockMaximo > 0 ? $diferenciaMax : 'N/A',
            'porcentaje_min' => number_format($porcentajeMin, 1) . '%',
            'nivel' => $nivel,
            'valor_riesgo' => '$' . number_format(max(0, $valorRiesgo), 2)
        ];
    }
}

// Crear PDF
$pdf = new PDF('L'); // Orientación landscape
$pdf->setTitulo('Reporte de Stock Mínimo y Máximo', rtrim($filtrosTexto, ', '));
$pdf->AliasNbPages();
$pdf->AddPage();

// Cabecera de la tabla
$header = ['Producto', 'Categoría', 'Almacén', 'Existencia', 'Stock Mín', 'Stock Máx', 'Dif. Mín', 'Dif. Máx', '% Stock Mín', 'Nivel', 'Valor Riesgo'];

// Datos
$pdf->ImprovedTable($header, $data);

// Salida
$pdf->Output('I', 'Reporte_Stock_Minimo_Maximo_' . date('Y-m-d') . '.pdf');
