<?PHP
class Fechas{
	public $fecha_bd;
    public $diaHoy;
    public $mesHoy;
    public $anioHoy;
		
	function diaSemana($numdia){
		switch ($numdia){
			case 0:
				$dia='Domingo';
			break;
			case 1:
				$dia='Lunes';
			break;
			case 2:
				$dia='Martes';
			break;
			case 3:
				$dia='Miércoles';
			break;
			case 4:
				$dia='Jueves';
			break;
			case 5:
				$dia='Viernes';
			break;
			case 6:
				$dia='Sábado';
			break;
			
		}
		return $dia;
	}
	
	function mesaNum($mes){
		switch ($mes){
			case 'Enero':
				$mes ='01';
			break;
			case 'Febrero':
				$mes ='02';
			break;
			case 'Marzo':
				$mes ='03';
			break;
			case 'Abril':
				$mes ='04';
			break;
			case 'Mayo':
				$mes ='05';
			break;
			case 'Junio':
				$mes ='06';
			break;
			case 'Julio':
				$mes ='07';
			break;
			case 'Agosto':
				$mes ='08';
			break;			
			case 'Septiembre':
				$mes ='09';
			break;
			case 'Octubre':
				$mes ='10';
			break;
			case 'Noviembre':
				$mes ='11';
			break;
			
			case 'Diciembre':
				$mes ='12';
			break;			
		}
		return $mes;
	}
	
	function numaMes($mes){
		switch ($mes){
			case '01':
				$mes ='Enero';
			break;
			case '02':
				$mes ='Febrero';
			break;
			case '03':
				$mes ='Marzo';
			break;
			case '04':
				$mes ='Abril';
			break;
			case '05':
				$mes ='Mayo';
			break;
			case '06':
				$mes ='Junio';
			break;
			case '07':
				$mes ='Julio';
			break;
			case '08':
				$mes ='Agosto';
			break;
			case '09':
				$mes ='Septiembre';
			break;
			case '10':
				$mes ='Octubre';
			break;
			case '11':
				$mes ='Noviembre';
			break;
			case '12':
				$mes ='Diciembre';
			break;			
		}
		return $mes;
	}
	
	function numaMesCorto($mes){
		switch ($mes){
			case '01':
				$mes ='ene';
			break;
			case '02':
				$mes ='feb';
			break;
			case '03':
				$mes ='mar';
			break;
			case '04':
				$mes ='abr';
			break;
			case '05':
				$mes ='may';
			break;
			case '06':
				$mes ='Jun';
			break;
			case '07':
				$mes ='Jul';
			break;
			case '08':
				$mes ='Ago';
			break;
			case '09':
				$mes ='Sep';
			break;
			case '10':
				$mes ='Oct';
			break;
			case '11':
				$mes ='Nov';
			break;
			case '12':
				$mes ='Dic';
			break;			
		}
		return $mes;
	}
		
	function fecha_Hoy(){
		$this->tiempo();
		$hora = date("H:i:s",time());
		$desde= date("d/m/Y"); 
		$year=substr($desde,6,4);
		$month=substr($desde,3,2);
		$day=substr($desde,0,2);
		$date1= $day."-".$month."-".$year;  
		$fecha=$year.'-'.$month.'-'.$day;
		//echo $day.' de '.$month.' del '.$year;				
		$fecha_hoy=$day.' de '.$this->numaMes($month).' del '.$year;
	//	echo '<span class="fecha_hoy">'.$fecha_hoy.'</span>';
		return $fecha_hoy;
	}
	
	function separa_fechas_En($fecha){
		$fech=array();
		$fech[0]=substr($fecha,0,4);
		$fech[1]=substr($fecha,5,2);
		$fech[2]=substr($fecha,8,2);
		return $fech;
	}
	
	function anio_actual(){
		$this->tiempo();
		$hora = date("H:i:s",time());
		$desde= date("d/m/Y"); 
		$year=substr($desde,6,4);
		$month=substr($desde,3,2);
		$day=substr($desde,0,2);
		$date1= $day."-".$month."-".$year;  
		$fecha=$year.'-'.$month.'-'.$day;
		//echo $day.' de '.$month.' del '.$year;				
		$fecha_hoy=$year;
	//	echo '<span class="fecha_hoy">'.$fecha_hoy.'</span>';
		return $fecha_hoy;
	}
    
    /*
    function mesActual(){        
        $fecha=$this-dame_fecha_bd();
        $anioActual=substr($fecha,0,4);
		$mesActual=substr($fecha,5,2);
		$diaActual=substr($fecha,8,2);
        return $mesActual;
    }
    */
    function mesActualLetras(){  
        $this->tiempo();
        $desde= date("d/m/Y"); 
		$year=substr($desde,6,4);
		$month=substr($desde,3,2);
		$day=substr($desde,0,2);
        $mesActual=$this->numaMes($month);        
        return $mesActual;
    }
	
	function fecha_letra($fechabd){
		$this->tiempo();		
		$year=substr($fechabd,0,4);
		$month=substr($fechabd,5,2);
		$day=substr($fechabd,8,2);
		$date1= $day."-".$month."-".$year;  
		//$fecha=$year.'-'.$month.'-'.$day;
		// echo $fechabd.'<br>';
		// echo $day.' de '.$month.' del '.$year.'<br>';				
		$fecha=$day.' de '.$this->numaMes($month).' del '.$year;
	//	echo '<span class="fecha_hoy">'.$fecha_hoy.'</span>';
		return $fecha;
	}
	
	function fechaEntoEs($fechabd){
		$this->tiempo();		
		$year=substr($fechabd,0,4);
		$month=substr($fechabd,5,2);
		$day=substr($fechabd,8,2); 
		$fecha=$day.'-'.$month.'-'.$year;
		return $fecha;
	}
    function fechaEstoEn($fecha){
		$this->tiempo();		
		$year=substr($fecha,6,4);
		$month=substr($fecha,3,2);
		$day=substr($fecha,0,2); 
		$fecha= $year."-".$month."-".$day;
		return $fecha;
	}
	
	function dame_fecha_bd(){
		$this->tiempo();
		$hora = date("H:i:s",time());
		$desde= date("d/m/Y"); 
		$year=substr($desde,6,4);
		$month=substr($desde,3,2);
		$day=substr($desde,0,2);
		$fecha= $year."-".$month."-".$day; 
		$this->fecha_bd=$fecha; 		
		return $fecha;
	}
	
	function fechaHora(){
		$this->tiempo();
		$hora = date("H:i:s",time());
		$desde= date("d/m/Y"); 
		$year=substr($desde,6,4);
		$month=substr($desde,3,2);
		$day=substr($desde,0,2);
		$fechahora= $year."-".$month."-".$day.' '.$hora;  		
		return $fechahora;
	}
    
    function datetime(){
		$this->tiempo();
		$hora = date("His",time());
		$desde= date("d/m/Y"); 
		$year=substr($desde,6,4);
		$month=substr($desde,3,2);
		$day=substr($desde,0,2);
		$fechahora= $year.$month.$day.$hora;  		
		return $fechahora;
	}
	
	function fecha_mestoLetra($fechabd){	
		$this->tiempo();		
		$year=substr($fechabd,0,4);
		$month=substr($fechabd,5,2);
		$day=substr($fechabd,8,2);
		$mes=$this->numaMes($month);
		$fecha=$day.'-'.$mes.'-'.$year;
		return $fecha;
	}
	
	function restaXdias($restar){
		$this->tiempo();
		$fecha_hoy=$this->dame_fecha_bd();
		$fecha = new DateTime($fecha_hoy);		
		//echo 'Dia hoy: '.$dia_hoy.' / Dia corte: '.$dia.'<br>';
		$dias='P'.$restar.'D';
		$fecha->sub(new DateInterval($dias)); // resta 6 dias a la fecha
		$nuevafecha=$fecha->format('Y-m-d');
		return $nuevafecha;
	}
    
    function restadiasafecha($restar,$fechaE){
		$this->tiempo();
   //     echo $fechaEs.'<br>';
	 //   $fecha_hoy=$this->fechaEstoEn($fechaEs);
      //  echo $fecha_hoy.'<br>';
		$fecha = new DateTime($fechaE);				
		$dias='P'.$restar.'D';
		$fecha->sub(new DateInterval($dias)); // resta 6 dias a la fecha
		$nuevafecha=$fecha->format('Y-m-d');
		return $nuevafecha;
	}
	
	function fecha_diaSemanaLetra($fechabd){
		$dias = array('Domingo','Lunes','Martes','Miercoles','Jueves','Viernes','Sabado');
		$fecha = $dias[date('N', strtotime($fechabd))];
		return $fecha;
	}
	
	function get_nombre_dia($fecha){
	   $fechats = strtotime($fecha); //pasamos a timestamp

		//el parametro w en la funcion date indica que queremos el dia de la semana
		//lo devuelve en numero 0 domingo, 1 lunes,....
		switch (date('w', $fechats)){
			case 0: return "Domingo"; break;
			case 1: return "Lunes"; break;
			case 2: return "Martes"; break;
			case 3: return "Miercoles"; break;
			case 4: return "Jueves"; break;
			case 5: return "Viernes"; break;
			case 6: return "Sabado"; break;
		}
	}
	
	function hora(){
		$this->tiempo();
		$hora = date("H:i:s",time());
		return $hora;
	}
	function tiempo(){
		/*
		date_default_timezone_set('UTC');
		
		date_default_timezone_set("America/Mexico_City");
		setlocale(LC_TIME, 'spanish'); */
		date_default_timezone_set('Etc/GMT+6');
	}
	
	function dias_transcurridos($fecha_i,$fecha_f){
	//	echo $fecha_i.' - '.$fecha_f.'<br>';
		$datetime1 = new DateTime($fecha_i);		
		$datetime2 = new DateTime($fecha_f);
		
		$dif = $datetime1->diff($datetime2);		
		$diferencia=$dif->format('%a');
		//echo $diferencia;
		$diferencia++;		
		return $diferencia;
	}
	function dias_pasados($fecha_inicial,$fecha_final){
		$dias = (strtotime($fecha_inicial)-strtotime($fecha_final))/86400;
		$dias = abs($dias); $dias = floor($dias);
		return $dias;
	}
	function semanas_transcurridas($fecha_i,$fecha_f){
		$datetime1 = new DateTime($fecha_i);
		$datetime2 = new DateTime($fecha_f);
		$diferencia = $datetime1->diff($datetime2);
		$semanas= floor(($diferencia->format('%a') / 7));
		// . ' semanas con ' . ($diferencia->format('%a') % 7) . ' días';
	 return $semanas;
	}
	
	function fechasEntreperiodos($fechaInicial,$FechaFinal){
		$comienzo = new DateTime($fechaInicial);
		$final = new DateTime($FechaFinal);
		// Necesitamos modificar la fecha final en 1 día para que aparezca en el bucle
		$final = $final->modify('+1 day');
		
		$intervalo = DateInterval::createFromDateString('1 day');
		$periodo = new DatePeriod($comienzo, $intervalo, $final);
		
		foreach ($periodo as $dt) {
			$lista[]= $dt->format("Y-m-d\n");
		}
		return $lista;
	}
	
	function entrefechas($fechaInicio,$fechaActual){
	/*	$fechaInicio ="28/02/1999";
		$fechaActual = "29/02/2000";
	*/
		$anioActual=substr($fechaActual,0,4);
		$mesActual=substr($fechaActual,5,2);
		$diaActual=substr($fechaActual,8,2);
		
		$anioInicio=substr($fechaInicio,0,4);
		$mesInicio=substr($fechaInicio,5,2);
		$diaInicio=substr($fechaInicio,8,2);  	

		$b = 0;
		$mes = $mesInicio-1;
		if($mes==2){
			if(($anioActual%4==0 && $anioActual%100!=0) || $anioActual%400==0){
				$b = 29;
			}else{
				$b = 28;
			}
		}else if($mes<=7){
			if($mes==0){
			 $b = 31;
		}else if($mes%2==0){
		  		$b = 30;
		  		}else{
		  			$b = 31;
		  		}
		}else if($mes>7){
		  		if($mes%2==0){
		  			$b = 31;
		  		}else{
		  			$b = 30;
		  		}
		  	}
		  
		   if(($anioInicio>$anioActual) || ($anioInicio==$anioActual && $mesInicio>$mesActual) || 
		  ($anioInicio==$anioActual && $mesInicio == $mesActual && $diaInicio>$diaActual)){
	//	  echo "La fecha de inicio ha de ser anterior a la fecha Actual";
		  }else{
		  	if($mesInicio <= $mesActual){
		  		$anios = $anioActual - $anioInicio;
		  		if($diaInicio <= $diaActual){
		  			$meses = $mesActual - $mesInicio;
		  			$dias = $diaActual - $diaInicio;
		  		}else{
		  			if($mesActual == $mesInicio){
		  				$anios = $anios - 1;
		  			}
		  			$meses = ($mesActual - $mesInicio - 1 + 12) % 12;
		  			$dias = $b-($diaInicio-$diaActual);
		  		}		  
		  	}else{
		  		$anios = $anioActual - $anioInicio - 1;		  
		  		if($diaInicio > $diaActual){
		  		$meses = $mesActual - $mesInicio -1 +12;
		  		$dias = $b - ($diaInicio-$diaActual);
		  	}else{
		  		$meses = $mesActual - $mesInicio + 12;
		  		$dias = $diaActual - $diaInicio;
		  	}
		  }
		  $fecha=$anios.'-'.$meses.'-'.$dias;
		  return $fecha;
	/*	  echo "Años: ".$anios." <br />";
		  echo "Meses: ".$meses." <br />";
		  echo "Días: ".$dies." <br />";1
		 */
		  }
	}

	function dias_turno($turno,$inicio_fecha,$fin_fecha){
		$clsConsulta=new Consultas();
        $cong="SELECT * FROM cat_turnos WHERE id = ".$turno;      
        $rsg=$clsConsulta->consultaGeneral($cong);
        if($clsConsulta->numrows>0){ 
          foreach($rsg as $vg=>$valg){
            $lunes      =$valg['lunes'];
            $martes     =$valg['martes'];
            $miercoles  =$valg['miercoles'];
            $jueves     =$valg['jueves'];
            $viernes    =$valg['viernes'];
            $sabado     =$valg['sabado'];
            $domingo    =$valg['domingo'];

            if ($lunes==1){$dias[]=1;}
            if ($martes==1){$dias[]=2;}
            if ($miercoles==1){$dias[]=3;}
            if ($jueves==1){$dias[]=4;}
            if ($viernes==1){$dias[]=5;}
            if ($sabado==1){$dias[]=6;}
            if ($domingo==1){$dias[]=7;}
          }
        } 

        $contodor=0;
        $fechas='';
	//	echo ' fecha inicio: '.$inicio_fecha.'<br>';
		$inicio_fecha = new DateTime ($inicio_fecha);
		$fin_fecha = new DateTime ($fin_fecha);
        while( $inicio_fecha <= $fin_fecha){
            foreach ($dias as $dia) {
                if ($inicio_fecha->format('N')==$dia) {
                    $fechas.="'".$inicio_fecha->format('Y/m/d')."', ";
					
			//		$f="'".$inicio_fecha->format('Y/m/d')."', ";
			//		echo $contodor.' ] '.$f.' -> '.$dia.'<br>';
					
                    $contodor++;
				//	echo 'contodor: '.$contodor.'<br>';
                }else{
                    //$inicio_fecha->format('d/m/Y (N)').'<br/>';
                }
            }
            
            $inicio_fecha->modify("+1 days");
        }
		
        $long=strlen($fechas)-2;
		$cabecera=substr($fechas, 0, $long);
    	$resultado[0]=$cabecera;
    	$resultado[1]=$contodor;
    	return $resultado;
	}
	
	function mes_hoy($fecha){
		$month=substr($fecha,5,2);
		$mes=$this->numaMes($month);
		return $mes;
	}
	
	function dias_vacaciones($anios){

		if($anios==0){ $dias_vacaciones=0; }
		if($anios==1){ $dias_vacaciones=6; }
		if($anios==2){ $dias_vacaciones=8; }
		if($anios==3){ $dias_vacaciones=10; }
		if($anios==4){ $dias_vacaciones=12; }
		if($anios>=5 && $anios<=9){ $dias_vacaciones=14; }
		if($anios>=10 && $anios<=14){ $dias_vacaciones=16; }
		if($anios>=15 && $anios<=19){ $dias_vacaciones=18; }
		if($anios>=20 && $anios<=24){ $dias_vacaciones=20; }
		if($anios>=25 && $anios<=29){ $dias_vacaciones=22; }
		if($anios>=30 && $anios<=34){ $dias_vacaciones=24; }
		return $dias_vacaciones;
	}
	
	function primer_dia_mes($fecha){
		$fecha = new DateTime($fecha);
		$fecha->modify('first day of this month');
		return $fecha->format('Y/m/d');
	}
	
	function ultimo_dia_mes($fecha){
		$fecha = new DateTime($fecha);
		$fecha->modify('last day of this month');
		return $fecha->format('Y/m/d');
	}
	
		
	/** Ultimo dia del mes actual **/
	function ultimo_dia_mes_actual() { 
		$month = date('m');
		$year = date('Y');
		$day = date("d", mktime(0,0,0, $month+1, 0, $year));

		return date('Y-m-d', mktime(0,0,0, $month, $day, $year));
	}

	/** Primer dia del mes actual  **/
	function primer_dia_mes_actual() {
		$month = date('m');
		$year = date('Y');
		return date('Y-m-d', mktime(0,0,0, $month, 1, $year));
	}
	/** Ultimo dia de cualquier mes **/
	function ultimoDiaMes($anio,$mes) {
  		return date("d",(mktime(0,0,0,$anio+1,1,$mes)-1));
	}
	function entre_horas($horaini,$horafin){
    	$f1 = new DateTime($horaini);
    	$f2 = new DateTime($horafin);
    	$d = $f1->diff($f2);
    	return $d->format('%H:%I:%S');
	}
	
	function cuenta_dias($mes,$anio,$numero_dia){
		$count=0;
		$dias_mes=cal_days_in_month(CAL_GREGORIAN, $mes, $anio);
		for($i=1;$i<=$dias_mes;$i++)
		if(date('N',strtotime($anio.'-'.$mes.'-'.$i))==$numero_dia)
		$count++;
		return $count;
	}
	
	function diasdelMes ($mes, $anio){
		$diasMes=cal_days_in_month(CAL_GREGORIAN, $mes, $anio);
		return $diasMes;
	}

// 1 - lunes
// 2 - martes
// 3- miercoles
// ..
// 7 - domingo
}

?>