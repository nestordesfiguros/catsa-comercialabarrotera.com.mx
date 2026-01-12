<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>MDB Datepicker Example</title>
  <!-- Enlace al CSS de MDBootstrap -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/5.0.0/mdb.min.css" rel="stylesheet">
</head>

<style>
  * {
    font-size: 14px;
  }
</style>

<body>
  <section class="w-100 p-4 d-flex justify-content-center pb-4">


    <div>
<?php
$db1 = new mysqli('localhost', 'root', '', 'abarrotes');
$db2 = new mysqli("distribuidora-del-bajio.com", "distribuidoradb_abarrotes", "igl6q5x?(9pA", "distribuidoradb_abarrotes");

function getSchema($db)
{
    $schema = [];
    $result = $db->query("SHOW TABLES");
    while ($row = $result->fetch_row()) {
        $table = $row[0];
        $result2 = $db->query("DESCRIBE `$table`");
        while ($col = $result2->fetch_assoc()) {
            $schema[$table][$col['Field']] = $col;
        }
    }
    return $schema;
}

$schema1 = getSchema($db1);
$schema2 = getSchema($db2);

echo "<pre>";

// 🔍 1. Verificar tablas faltantes entre bases
$tablas1 = array_keys($schema1);
$tablas2 = array_keys($schema2);

$soloEnDB1 = array_diff($tablas1, $tablas2);
$soloEnDB2 = array_diff($tablas2, $tablas1);

if ($soloEnDB1) {
    echo "🟥 Tablas que existen en *localhost/abarrotes* y NO en *distribuidora*:\n";
    foreach ($soloEnDB1 as $tabla) {
        echo " - $tabla\n";
    }
}

if ($soloEnDB2) {
    echo "\n🟦 Tablas que existen en *distribuidora* y NO en *localhost/abarrotes*:\n";
    foreach ($soloEnDB2 as $tabla) {
        echo " - $tabla\n";
    }
}

// 🔍 2. Comparar campos en tablas comunes
$tablasComunes = array_intersect($tablas1, $tablas2);

foreach ($tablasComunes as $tabla) {
    $campos1 = array_keys($schema1[$tabla]);
    $campos2 = array_keys($schema2[$tabla]);

    $faltanEnDB2 = array_diff($campos1, $campos2);
    $faltanEnDB1 = array_diff($campos2, $campos1);

    if ($faltanEnDB2 || $faltanEnDB1) {
        echo "\n⚠️ Diferencias en la tabla: $tabla\n";

        if ($faltanEnDB2) {
            echo "  - Campos que existen en *localhost/abarrotes* y faltan en *distribuidora*:\n";
            foreach ($faltanEnDB2 as $campo) {
                echo "    • $campo\n";
            }
        }

        if ($faltanEnDB1) {
            echo "  - Campos que existen en *distribuidora* y faltan en *localhost/abarrotes*:\n";
            foreach ($faltanEnDB1 as $campo) {
                echo "    • $campo\n";
            }
        }
    }
}

echo "\n✅ Comparación terminada.";
echo "</pre>";
?>

    </div>
  </section>

  <!-- Enlace a los scripts de MDBootstrap -->
  <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/5.0.0/mdb.min.js"></script>

</body>

</html>