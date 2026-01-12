<div class="ms-5">
  <nav aria-label="breadcrumb">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="pruebas">Pruebas</a></li>
    </ol>
  </nav>
</div>



<div class="container">
<?php
header('Content-Type: text/html; charset=utf-8');
echo "<form method='POST'>";
echo "<input type='text' name='direccion_envio' value='HERMANOS ALDAMA , CENTRAL DE ABASTOS, 37490, LEÓN, Guanajuato'><br>";
echo "<input type='submit'>";
echo "</form>";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "<br>Var dump:<br>";
    var_dump($_POST['direccion_envio']);
    echo "<br>mb_detect_encoding: " . mb_detect_encoding($_POST['direccion_envio']);
    echo "<br>Raw: " . $_POST['direccion_envio'];
}
?>
</div>