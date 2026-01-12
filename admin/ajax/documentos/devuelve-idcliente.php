<?php
    include '../../lib/clsConsultas.php';
    $clsConsulta=new Consultas();
    $id='';
    $razon_social=$_POST['razon_social']; 
    if($razon_social=='TODOS')  {
        $id=0;
      //  $id=$clsConsulta->encode64($id_cliente);
    }else{
        $con="SELECT * FROM cat_clientes WHERE razon_social='".$razon_social."'"; 
        // echo $con;
        $rs=$clsConsulta->consultaGeneral($con);
        
        if($clsConsulta->numrows>0){
            foreach($rs as $v=>$val){                                                             
                $id_cliente = $val['id'];
            }
            $id=$clsConsulta->encode64($id_cliente);
        }
        
    }
    
    echo $id;
?>