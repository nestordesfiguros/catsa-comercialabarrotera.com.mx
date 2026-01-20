<?php
// admin/ajax/proveedores/tabla-proveedores.php
session_start();
include '../../lib/clsConsultas.php';
$clsConsulta = new Consultas();

// Asumimos que la variable $_SESSION['id_empresa'] contiene el ID de la empresa actual
$id_empresa = isset($_SESSION['id_empresa']) ? $_SESSION['id_empresa'] : 0;  // Default a 0 si no está seteado

$data_array = [];

// Actualizamos la consulta para considerar el filtro por empresa
$con = "SELECT * FROM cat_proveedores WHERE estatus=1 AND id_empresa = $id_empresa";
$rs = $clsConsulta->consultaGeneral($con);

if ($clsConsulta->numrows > 0) {
    foreach ($rs as $v => $val) {
        $id = $val['id'];
        $estatusActual = $val['estatus'];

        // Estatus de proveedor: íconos para activo/inactivo
        $estatusA = ($val['estatus'] == 1) ? '<i style="cursor:pointer;" class="fas fa-check-circle fa-lg text-success"  onClick="valorEstatus(' . $id . ',' . $val['estatus'] . ')"></i>' : '<i style="cursor:pointer;" class="fas fa-ban fa-lg text-danger"  onClick="valorEstatus(' . $id . ',' . $val['estatus'] . ')"></i>';

        // Datos del proveedor
        $razon_social = '<div>' . $val['razon_social'] . ' / ' . $val['nombre_Comercial'] . '</div>';
        $contacto = '<div>' . $val['contacto'] . '</div>';

        // Botón de edición
        $editar = '<div class="text-center"><i class="fas fa-edit fa-lg text-info" style="cursor:pointer;" onclick="editar(' . $id . ',' . $estatusActual . ');" data-bs-toggle="modal" data-bs-target="#modaleditar"></i></div>';

        // Mapa, si existe
        if ($val['mapa'] != '') {
            $mapa = '<div class="text-center"><a href="' . $val['mapa'] . '" target="blank" class="text-info"><i class="fas fa-map-marked-alt fa-lg"></i></a></div>';
        } else {
            $mapa = '<div class="text-center"><i class="fas fa-map-marked-alt fa-lg"></i> </div>';
        }

        // Estatus
        $estatus = '<div class="text-center">' . $estatusA . '</div>';

        // Agregamos los datos a la respuesta
        $data_array[] = array(
            $razon_social,
            $contacto,
            $editar,
            $mapa,
            $estatus
        );
    }
}

// Generamos la respuesta en formato JSON
$new_array = ["data" => $data_array];
echo json_encode($new_array);
