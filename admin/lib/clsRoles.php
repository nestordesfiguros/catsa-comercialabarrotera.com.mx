<?php

class Roles {
    function tomaRol($rol_id, $permiso_id){        
        $con="SELECT * FROM roles_permisos WHERE rol_id=".$rol_id." AND permiso_id=".$permiso_id;
        $rs=$clsConsulta->consultaGeneral($con);
        echo $clsConsulta->numrows;
        if($clsConsulta->numrows>0){
            $res='true';
        }else{
            $res='false';
        }
        return $res;
    }
}
?>