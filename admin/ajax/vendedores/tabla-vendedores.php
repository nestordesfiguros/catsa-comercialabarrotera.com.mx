<?php
include '../../lib/clsConsultas.php';
$clsConsulta = new Consultas();

$data_array = [];

$con = "SELECT * FROM cat_vendedores WHERE estatus= 1";
$rs = $clsConsulta->consultaGeneral($con);
if ($clsConsulta->numrows > 0) {
    foreach ($rs as $v => $val) {
        $id = $val['id'];
        $nombre = $val['nombre'].' '.$val['apellido1'].' '.$val['apellido2'];
        $nombreValor = "'".$val['nombre'].' '.$val['apellido1'].' '.$val['apellido2']."'";
        $apellido1 = $val['apellido1'];
        $apellido2 = $val['apellido2'];
        $curp = $val['curp'];
        $calle = $val['calle'];
        $num_ext = $val['num_ext'];
        $num_int = $val['num_int'];
        $colonia = $val['colonia'];
        $cp = $val['cp'];
        $id_municipio = $val['id_municipio'];
        $id_estado = $val['id_estado'];
        $localidad = $val['localidad'];
        $tel = $val['tel'];
        $telcontacto = $val['telcontacto'];
        $correo = $val['correo'];
        $fecha_alta = $val['fecha_alta'];
        $fecha_modificacion = $val['fecha_modificacion'];
        $fecha_baja = $val['fecha_baja'];
        $usr = $val['usr'];
        $pwd = $val['pwd'];        
        $estatus = $val['estatus'];  
        //$comision = $val['comision'];

        $estatusA ='<i style="cursor:pointer;" class="far fa-trash-alt fa-lg text-danger" onClick="borrar(' . $id . ',' . $nombreValor. ');"></i>';
        $nombre = '<div>' . $nombre . '</div>';                
        $comision = '<div class="text-end">' . $val['comision'] . '%</div>';
        $clientes = '<div class="text-center"> <i class="fas fa-user-tag fa-lg text-info" onclick="fnClientes(' . $id . ');" style="cursor:pointer;"></i> </div>';
        $tel = '<div class="text-center">' . $val['tel'] . '</div>';
        $editar = '<div class="text-center"><i class="fas fa-edit fa-lg text-info" style="cursor:pointer;" onclick="editar(' . $id . ',' . $estatus . ');" data-bs-toggle="modal" data-bs-target="#modaleditar"></i></div>';
          
        $estatusActual = '<div class="text-center">' . $estatusA . '</div>';
        $accesos = '<div class="text-center"><i class="fas fa-user-lock fa-lg text-info" style="cursor:pointer;" onclick="fnAcceso(' . $id . ');"></i></div>';

        $data_array[] = array(
            $nombre,            
            $tel,
            $comision,
            $clientes,
            $accesos,
            $editar,           
            $estatusActual
        );
    }
}

$new_array = ["data" => $data_array];
echo json_encode($new_array);
