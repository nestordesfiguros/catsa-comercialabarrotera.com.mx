<?php
include '../../lib/clsConsultas.php';
$clsConsulta=new Consultas();
$cliente=$_POST['id'];
$con="SELECT id, sucursales FROM cat_clientes WHERE razon_social='".$cliente."'";   
$rs=$clsConsulta->consultaGeneral($con);
if($clsConsulta->numrows>0){
    foreach($rs as $v=>$val){    
        $id_Cliente=$val['id']; 
        $sucursales=$val['sucursales']; 
    }
    
    if($sucursales!=''){    
        $sucursal=explode(",",$sucursales);       
        echo '<div class="mt-3 mb-3">'; 
        echo 'Selecciona una sucursal: <br>';                        
        foreach($sucursal as $suc){           
            $valores="'".$suc."',".$id_Cliente;
            echo '<div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="sucursal" value="'.$suc.'" id="flexRadioDefault1">
                    <label class="form-check-label radio-inline" for="sucursal">
                    '.$suc.'
                    </label>
                </div>
                ';
            
        }
        echo '</div>';
    }
}


?>