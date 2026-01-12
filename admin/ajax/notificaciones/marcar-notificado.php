<?php 
include '../../lib/clsConsultas.php';

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $clsConsulta=new Consultas();
    $sql = "UPDATE cab_pedidos SET notificado = 1 WHERE id = $id";
    $clsConsulta->aplicaQuery($sql);
}
?>