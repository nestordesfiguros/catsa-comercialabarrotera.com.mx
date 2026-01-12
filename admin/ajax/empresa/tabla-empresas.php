<?php
include '../../lib/clsConsultas.php';
$clsConsulta = new Consultas();

$data_array = [];

$con = "SELECT * FROM cat_empresas WHERE estatus=1";
$rs = $clsConsulta->consultaGeneral($con);
if ($clsConsulta->numrows > 0) {
    foreach ($rs as $v => $val) {
        $id = $val['id'];
        $estatusActual = $val['estatus'];

        $estatusA = ($val['estatus'] == 1) ? '<i style="cursor:pointer;" class="fas fa-check-circle fa-lg text-success"  onClick="valorEstatus(' . $id . ',' . $val['estatus'] . ')"></i>' : '<i style="cursor:pointer;" class="fas fa-ban fa-lg text-danger"  onClick="valorEstatus(' . $id . ',' . $val['estatus'] . ')"></i>';

        $razon_social = '<div>' . $val['razon_social'] . '</div>';
        $datosFacturacion = '<div class="text-center"><a class="link-info" href="configuracion-clientes-datos-facturacion/' . $id . '"><i class="fas fa-file-invoice-dollar fa-lg"></i></a></div>';
        $contacto = '<div class="text-center"> <i class="fa-regular fa-address-card fa-lg text-info" data-bs-toggle="modal" data-bs-target="#modalContactos" onclick="fncontactos('.$id.')" style="cursor:pointer"></i> </div>';
        $editar = '<div class="text-center"><i class="fas fa-edit fa-lg text-info" style="cursor:pointer;" onclick="editar(' . $id . ',' . $estatusActual . ');" ></i></div>';
        if ($val['mapa'] != '') {
            $mapa = '<div class="text-center"><a href="' . $val['mapa'] . '" target="blank" class="text-info"><i class="fas fa-map-marked-alt fa-lg"></i></a></div>';
        } else {
            $mapa = '<div class="text-center"><i class="fas fa-map-marked-alt fa-lg"></i> </div>';
        }

        $estatus = '<div class="text-center">' . $estatusA . '</div>';

        $data_array[] = array(
            $razon_social,   
            $editar,
            $estatus
        );
    }
}

$new_array = ["data" => $data_array];
echo json_encode($new_array);
