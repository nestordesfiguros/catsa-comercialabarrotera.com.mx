<?php
class Progreso extends Consultas{   

    public function tiempo_todos_expedientes($id_contrato){		
		$desde= date("d/m/Y"); 
		$year=substr($desde,6,4);
		$month=substr($desde,3,2);
		$day=substr($desde,0,2);
		$hoy= $year."-".$month."-".$day; 
        $sumaDias=0;
        $avance=0;
        $color='#0ebd06'; // Verde
				
        /* CALCULA TIEMPO */
		$con="SELECT * FROM contratos WHERE id=".$id_contrato;
        $rs=Consultas::consultaGeneral($con);        
        if($rs[1]['es_contrato']==1){
            foreach($rs as $v=>$val){
                $folio=$val['folio'];
                $fecha_inicio=$val['fecha_inicio'];
                $fecha_termino=$val['fecha_termino'];
            }
            $totaldias=$this->dias_transcurridos($fecha_inicio,$fecha_termino);
            $fechaInicio = new DateTime($fecha_inicio);		
            $fechaTermino = new DateTime($fecha_termino);
            $fechaHoy = new DateTime($hoy);

            if($fecha_termino>$fechaHoy){
            //    echo 'se pasó de dias ';         
                $color='#da2800'; // Rojo
            }else{
                $diasContrato=$this->dias_transcurridos($fecha_inicio,$fecha_termino);
            }
    //      echo '<br> inicio: '.$fecha_inicio.' <br> Termino: '.$fecha_termino.' <br> Hoy:',$hoy;

            $con="SELECT
                MAX(expedientes.aplazamiento) AS aplaza
            FROM
                expedientes
                INNER JOIN definicion_documentos 
                    ON (expedientes.documento_id = definicion_documentos.id)
            WHERE  expedientes.contrato_id=".$id_contrato."  
            AND definicion_documentos.aplazamiento=1  ";
            $rs=Consultas::consultaGeneral($con);        
            if($this->numrows>0){
                if(isset($rs[1]['aplaza'])){             
                    foreach($rs as $v=>$val){
                        $fecha_fin=$val['aplaza'];
                    }
                    $sumaDias=$this->dias_transcurridos($fecha_termino,$fecha_fin);
                }
                
            }
            
            $DiasTranscurridos=$this->dias_transcurridos($fecha_inicio,$hoy);
            $dias100porciento=$diasContrato;
            if($sumaDias>0){
                $dias100porciento=$diasContrato+$sumaDias;
            }      

            if($DiasTranscurridos>$dias100porciento){
                $color='#da2800'; // Rojo
                $diasporciento=101;
            }else{
                $diasporciento=intdiv(($DiasTranscurridos*100),$dias100porciento);
                if($diasporciento>50){
                    $color='#ead605';
                }
                if($diasporciento>100){
                    $color='#da2800';
                }
            }
        }else{
            $color='#f1f1f1';
        }
/*
        echo '<br> dias transcurridos: '.$DiasTranscurridos;
        echo '<br> dias 100%: '.$dias100porciento;
        echo '<br> %: '.$diasporciento;
*/
        /* TERMINA TIEMPO */

        /* CALCULA ARCHIVOS */

        $valor=$color; //  Color
               
		return $valor; 
				
	}

    function dias_transcurridos($fecha_inicio,$fecha_fin){
        //	echo $fecha_i.' - '.$fecha_f.'<br>';
            $datetime1 = new DateTime($fecha_inicio);		
            $datetime2 = new DateTime($fecha_fin);
            
            $dif = $datetime1->diff($datetime2);		
            $diferencia=$dif->format('%a');
            //echo $diferencia;
            $diferencia++;		
            return $diferencia;
    }


    function expediente_avance_apertura($id_contrato){
		$porcentaje=0;
        $obligatorios='';
        $aprobacion='';
        $andObligatorios='';
        $andTotalExpedientes='';
		$con="SELECT 
        CONCAT(expedientes.id,',') AS obligatorios
        FROM expedientes 
          INNER JOIN definicion_documentos 
            ON (
              expedientes.documento_id = definicion_documentos.id
            ) 
        WHERE expedientes.contrato_id = ".$id_contrato." 
          AND definicion_documentos.ciclo_id = 1  
          AND definicion_documentos.obligatorio = 1
        ";        
		$rs=Consultas::consultaGeneral($con);  
        if(isset($rs[1]['aprobacion'])) { 
            foreach($rs as $v=>$val){
                $obligatorios.=$val['obligatorios'];
            }
            $obligatorios=trim($obligatorios,",");
        }

        if(!empty($obligatorios)){
            $andObligatorios='AND expedientes.id NOT IN ('.$obligatorios.')';
        }
        $con="SELECT 
        CONCAT(expedientes.id,',') AS aprobacion
        FROM expedientes 
            INNER JOIN definicion_documentos 
            ON (
                expedientes.documento_id = definicion_documentos.id
            ) 
        WHERE expedientes.contrato_id = ".$id_contrato." 
            AND definicion_documentos.ciclo_id = 1  
            AND definicion_documentos.solicita_aprobacion = 1 ".$andObligatorios;                   
        $rs=Consultas::consultaGeneral($con); 
        if(isset($rs[1]['aprobacion'])) {
            foreach($rs as $v=>$val){
                $aprobacion.=$val['aprobacion'];
            }
            $aprobacion=trim($aprobacion,",");
        }                

        if($obligatorios!=''){
            $todos=$obligatorios.','.$aprobacion;
        }else{
            $todos=$aprobacion;
        }        
        $todos=trim($todos,",");
        $todosExpedientes=explode(",",$todos);        
        $x=0;
        foreach($todosExpedientes as $te){            
            $x++;
        }                
        if($x > 1){
            $andTotalExpedientes='AND expedientes.id IN('.$todos.')';
        }
        /* Incompletos */
        $con="SELECT COUNT(*) AS total FROM expedientes WHERE contrato_id = ".$id_contrato." AND estatus=0 ".$andTotalExpedientes;
    //    echo '<br>'.$con.'<br>';
        $rs=Consultas::consultaGeneral($con); 
        if($this->numrows>0){
            $incompletos=$rs[1]['total'];
        }
        /* Completos */
        $con="SELECT COUNT(*) AS total FROM expedientes WHERE contrato_id = ".$id_contrato." AND  estatus=1 ".$andTotalExpedientes;
        $rs=Consultas::consultaGeneral($con); 
        $completos=$rs[1]['total'];       
        
        $todosExp=$completos+$incompletos;
        if($todos>0){
            $porcentajeUnitario=100/$todosExp;
            $porcentaje=$completos*$porcentajeUnitario;
        }else{
            $porcentaje=0;
        }
        
/*
        echo '<br> Apertura Completos '.$completos.'<br>';
        echo '<br> Apertura Incompletos '.$incompletos.'<br>';
        echo '<br> porcentaje '.$porcentaje.'<br>';
*/
		return $porcentaje;

	}


    function expediente_avance_transcurso($id_contrato){
        $porcentaje=0;
        $obligatorios='';
        $aprobacion='';
        $andObligatorios='';
        $andTotalExpedientes='';
		$con="SELECT 
        CONCAT(expedientes.id,',') AS obligatorios
        FROM expedientes 
          INNER JOIN definicion_documentos 
            ON (
              expedientes.documento_id = definicion_documentos.id
            ) 
        WHERE expedientes.contrato_id = ".$id_contrato." 
          AND definicion_documentos.ciclo_id = 2  
          AND definicion_documentos.obligatorio = 1
        ";   
		$rs=Consultas::consultaGeneral($con);  
        if(isset($rs[1]['obligatorios'])) { 
            foreach($rs as $v=>$val){
                $obligatorios.=$val['obligatorios'];
            }
            $obligatorios=trim($obligatorios,",");
            $andObligatorios='AND expedientes.id NOT IN ('.$obligatorios.')';
        }
       
        $con="SELECT 
        CONCAT(expedientes.id,',') AS aprobacion
        FROM expedientes 
            INNER JOIN definicion_documentos 
            ON (
                expedientes.documento_id = definicion_documentos.id
            ) 
        WHERE expedientes.contrato_id = ".$id_contrato." 
            AND definicion_documentos.ciclo_id = 2  
            AND definicion_documentos.solicita_aprobacion = 1 ".$andObligatorios;            
        $rs=Consultas::consultaGeneral($con); 
        if(isset($rs[1]['aprobacion'])) {
            foreach($rs as $v=>$val){
                $aprobacion.=$val['aprobacion'];
            }
            $aprobacion=trim($aprobacion,",");
        }                

        if($obligatorios!=''){
            $todos=$obligatorios.','.$aprobacion;
        }else{
            $todos=$aprobacion;
        }        
        $todos=trim($todos,",");
        $todosExpedientes=explode(",",$todos);        
        $x=0;
        foreach($todosExpedientes as $te){
            $x++;
        }                
        if($x > 1){
            $andTotalExpedientes='AND expedientes.id IN('.$todos.')';
        }
        /* Incompletos */
        $con="SELECT COUNT(*) AS total FROM expedientes WHERE estatus=0 ".$andTotalExpedientes;    
        $rs=Consultas::consultaGeneral($con); 
        $incompletos=$rs[1]['total'];

        /* Completos */
        $con="SELECT COUNT(*) AS total FROM expedientes WHERE estatus=1 ".$andTotalExpedientes;    
        $rs=Consultas::consultaGeneral($con); 
        $completos=$rs[1]['total'];   
        
        $todosExp=$completos+$incompletos;
        if($todos>0){
            $porcentajeUnitario=100/$todosExp;
            $porcentaje=$completos*$porcentajeUnitario;
        }else{
            $porcentaje=0;
        }


		return $porcentaje;
    }	

    function expediente_avance_cierre($id_contrato){
        $porcentaje=0;
        $obligatorios='';
        $aprobacion='';
        $andObligatorios='';
        $andTotalExpedientes='';
		$con="SELECT 
        CONCAT(expedientes.id,',') AS obligatorios
        FROM expedientes 
          INNER JOIN definicion_documentos 
            ON (
              expedientes.documento_id = definicion_documentos.id
            ) 
        WHERE expedientes.contrato_id = ".$id_contrato." 
          AND definicion_documentos.ciclo_id = 3  
          AND definicion_documentos.obligatorio = 1
        ";   
    
		$rs=Consultas::consultaGeneral($con);  
        if(isset($rs[1]['obligatorios'])) { 
            foreach($rs as $v=>$val){
                $obligatorios.=$val['obligatorios'];
            }
            $obligatorios=trim($obligatorios,",");
            $andObligatorios='AND expedientes.id NOT IN ('.$obligatorios.')';
        }     
       
        $con="SELECT 
        CONCAT(expedientes.id,',') AS aprobacion
        FROM expedientes 
            INNER JOIN definicion_documentos 
            ON (
                expedientes.documento_id = definicion_documentos.id
            ) 
        WHERE expedientes.contrato_id = ".$id_contrato." 
            AND definicion_documentos.ciclo_id = 3  
            AND definicion_documentos.solicita_aprobacion = 1 ".$andObligatorios;                
        $rs=Consultas::consultaGeneral($con); 
        if(isset($rs[1]['aprobacion'])) {
            foreach($rs as $v=>$val){
                $aprobacion.=$val['aprobacion'];
            }
            $aprobacion=trim($aprobacion,",");
        }                

        if($obligatorios!=''){
            $todos=$obligatorios.','.$aprobacion;
        }else{
            $todos=$aprobacion;
        }        
        $todos=trim($todos,",");
        $todosExpedientes=explode(",",$todos);        
        $x=0;
        foreach($todosExpedientes as $te){
            $x++;
        }                
        if($x > 1){
            $andTotalExpedientes='AND expedientes.id IN('.$todos.')';
        }

        /* Incompletos */
        $con="SELECT COUNT(*) AS total FROM expedientes WHERE estatus=0 ".$andTotalExpedientes;    
        $rs=Consultas::consultaGeneral($con); 
        $incompletos=$rs[1]['total'];
        /* Completos */
        $con="SELECT COUNT(*) AS total FROM expedientes WHERE estatus=1 ".$andTotalExpedientes;    
        $rs=Consultas::consultaGeneral($con); 
        $completos=$rs[1]['total'];   
        
        $todosExp=$completos+$incompletos;
        if($todos>0){
            $porcentajeUnitario=100/$todosExp;
            $porcentaje=$completos*$porcentajeUnitario;
        }else{
            $porcentaje=0;
        }

/*
        echo '<br> Cierre Completos '.$completos;
        echo '<br> Cierre Incompletos '.$incompletos;
        echo '<br> Cierre porcentaje '.$porcentaje.'<br>';
*/
		return $porcentaje;
    }



}


?>