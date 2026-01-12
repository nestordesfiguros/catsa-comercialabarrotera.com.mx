<?PHP
class Fechas{
	public $fecha_bd;
		
	public function diaSemana($numdia){
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
	/*
	public function mesaNum($mes){
		echo 'MEs: '.$mes.'<br>';
		switch ($mes){
			case 'Enero':
				$mes =01;
			break;
			case 'Febrero':
				$mes =02;
			break;
			case 'Marzo':
				$mes =03;
			break;
			case 'Abril':
				$mes =04;
			break;
			case 'Mayo':
				$mes =05;
			break;
			case 'Junio':
				$mes =06;
			break;			
			case 'Julio':
				$mes =07;
			break;			
			case 'Agosto':
				$mes =08;
			break;			
			case 'Septiembre':
				$mes =09;
			break;
			case 'Octubre':
				$mes =10;
			break;
			case 'Noviembre':
				$mes =11;
			break;
			case 'Diciembre':
				$mes =12;
			break;		
				
		}
		return $mes;
	}
	*/
	public function numaMes($mes){
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
	
	public function numaMesCorto($mes){
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
		
	public function fecha_Hoy(){
		$this->tiempo();
		$hora = date("H:i:s",time());
		$desde= date("d/m/Y"); 
		$year=substr($desde,6,4);
		$month=substr($desde,3,2);
		$day=substr($desde,0,2);
		$date1= $day."-".$month."-".$year;  
		$fecha=$year.'-'.$month.'-'.$day;
		//echo $day.' de '.$month.' del '.$year;				
		$fecha_hoy=strftime(' %d de %B del %Y ',strtotime($date1));
	//	echo '<span class="fecha_hoy">'.$fecha_hoy.'</span>';
		return $fecha_hoy;
	}
	
	public function separa_fechas_En($fecha){
		$fech=array();
		$fech[0]=substr($fecha,0,4);
		$fech[1]=substr($fecha,5,2);
		$fech[2]=substr($fecha,8,2);
		return $fech;
	}
	
	public function anio_actual(){
		$this->tiempo();
		$hora = date("H:i:s",time());
		$desde= date("d/m/Y"); 
		$year=substr($desde,6,4);
		$month=substr($desde,3,2);
		$day=substr($desde,0,2);
		$date1= $day."-".$month."-".$year;  
		$fecha=$year.'-'.$month.'-'.$day;
		//echo $day.' de '.$month.' del '.$year;				
		$fecha_hoy=strftime('%Y',strtotime($date1));
	//	echo '<span class="fecha_hoy">'.$fecha_hoy.'</span>';
		return $fecha_hoy;
	}
	
	public function fecha_letra($fechabd){
		$this->tiempo();		
		$year=substr($fechabd,0,4);
		$month=substr($fechabd,5,2);
		$day=substr($fechabd,8,2);
		$date1= $day."-".$month."-".$year;  
		//$fecha=$year.'-'.$month.'-'.$day;
		// echo $fechabd.'<br>';
		// echo $day.' de '.$month.' del '.$year.'<br>';				
		$fecha=strftime(' %d de %B del %Y ',strtotime($date1));
	//	echo '<span class="fecha_hoy">'.$fecha_hoy.'</span>';
		return $fecha;
	}
	
	public function fechaEntoEs($fechabd){
		$this->tiempo();		
		$year=substr($fechabd,0,4);
		$month=substr($fechabd,5,2);
		$day=substr($fechabd,8,2); 
		$fecha=$day.'-'.$month.'-'.$year;
		return $fecha;
	}
	
	public function dame_fecha_bd(){
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
	
	public function fechaHora(){
		$this->tiempo();
		$hora = date("H:i:s",time());
		$desde= date("d/m/Y"); 
		$year=substr($desde,6,4);
		$month=substr($desde,3,2);
		$day=substr($desde,0,2);
		$fechahora= $year."-".$month."-".$day.' '.$hora;  		
		return $fechahora;
	}
	
	public function fecha_mestoLetra($fechabd){	
		$this->tiempo();		
		$year=substr($fechabd,0,4);
		$month=substr($fechabd,5,2);
		$day=substr($fechabd,8,2);
		$mes=$this->numaMes($month);
		$fecha=$day.'-'.$mes.'-'.$year;
		return $fecha;
	}
	
	public function fecha_diaSemanaLetra($fechabd){
		$dias = array('Domingo','Lunes','Martes','Miercoles','Jueves','Viernes','Sabado');
		$fecha = $dias[date('N', strtotime($fechabd))];
		return $fecha;
	}
	
	function hora(){
		$this->tiempo();
		$hora = date("H:i:s",time());
		return $hora;
	}
	function tiempo(){
		date_default_timezone_set('UTC');
		date_default_timezone_set("America/Mexico_City");
		setlocale(LC_TIME, 'spanish');
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
		$fecha = new DateTime();
		$fecha->modify('first day of this month');
		return $fecha->format('Y/m/d');
	}
	
	function ultimo_dia_mes($fecha){
		$fecha = new DateTime();
		$fecha->modify('last day of this month');
		return $fecha->format('Y/m/d');
	}
	
	// #################   FUNCTION PERIODO
	function periodo($tipo_nomina,$fecha_prenomina){
		
		$clsConsulta=new Consultas();
		// ######   TIPO DE NOMINA
		// Conocemos el tipo de nomina si es semanal o quincenal y en que dias aplica el corte
		$con="SELECT * FROM cat_tipo_nomina WHERE id=".$tipo_nomina;
		// echo $con.'<br>';
		  $rs=$clsConsulta->consultaGeneral($con);
		  if($clsConsulta->numrows>0){	
			  foreach($rs as $v=>$val){
					$tipo_nomina=$val['tipo_nomina'];
					$id_tipo_nomina=$val['id'];
					$nombre_nomina=$val['nombre'];
					$dia=$val['dia'];
					$dia1=$val['dia1'];
					$dia2=$val['dia2'];
				}
				switch ($dia){
					case 1:
						$ndia='LUNES';
					break;
					case 2:
						$ndia='MARTES';
					break;
					case 3:
						$ndia='MIERCOLES';
					break;
					case 4:
						$ndia='JUEVES';
					break;
					case 5:
						$ndia='VIERNES';
					break;
					case 6:
						$ndia='SABADO';
					break;
				}	  
		  }
		  
		
		if (isset($fecha_prenomina)) {
			$fecha_bd=$fecha_prenomina; // toma la fecha de hoy
		}else{
			$fecha_bd=$this->fecha_bd; // toma la fecha de hoy
		}
		 
		
		$year=substr($fecha_bd,0,4);
		$month=substr($fecha_bd,5,2);
		$day=substr($fecha_bd,8,2);
		$anio=$year;
		
	//	echo $anio.'<br>';
	//	echo $month.'<br>';
		if($tipo_nomina==1){ /* ****  QUINCENAL */
			$entre_dias=15;
			/*
			echo 'id nomina: '.$id.'<br>';
			echo 'Año acutal: '.$anio.'<br>';
			echo 'Mes actual: '.$month.'<br>';
			echo 'Dia1: '.$dia1.'<br>';
			echo 'Dia2: '.$dia2.'<br>';
			echo 'Day hoy: '.$day.'<br>';
			*/
			if($day>$dia1 and $day<=$dia2){
				$diai=$dia1+1;
				$diai=($diai<=9)? '0'.$diai:$diai;
				$fecha_inicio=$year.'-'.$month.'-'.$diai;
				$fecha_fin=$year.'-'.$month.'-'.$dia2;	
			}
			if($day>$dia1 and $day>$dia2){  // ajuste aqio >=
				$mes=$month+1;
				$diai=$dia2+1;
				$anio=$year;
				if($mes==13){
					$anio=$year+1;
					$mes=1;
				}
				$mes=($mes<=9)? '0'.$mes:$mes;
				$dia1=($dia1<=9)? '0'.$dia1:$dia1;
				$fecha_inicio=$year.'-'.$month.'-'.$diai;
				$fecha_fin=$anio.'-'.$mes.'-'.$dia1;	
			}
			
			if($day<=$dia1 and $day<$dia2){
				
				$mes=$month-1;
				if($mes==0){
					$mes=12;
					$anio=$year-1;
				}
				$diai=$dia2+1;
				$dia1=($dia1<=9)? '0'.$dia1:$dia1;
				$diai=($diai<=9)? '0'.$diai:$diai;
				$mes=($mes<=9)? '0'.$mes:$mes;
				$fecha_inicio=$anio.'-'.$mes.'-'.$diai;
				$fecha_fin=$year.'-'.$month.'-'.$dia1;	
			}
			
		//	echo 'Fecha inicio: '.$fecha_inicio.' -  fecha fin: '.$fecha_fin.'<br>';
		
		}else{ /* SEMANAL */
		//	echo 'fecha hoy: '.$fecha_bd.'<br>';
			$entre_dias=7;
			$fecha = new DateTime($fecha_bd);
			$dia_hoy=date("w",strtotime($fecha_bd)); // numero del dia de hoy
			//echo 'Dia hoy: '.$dia_hoy.' / Dia corte: '.$dia.'<br>';
			if($dia_hoy==$dia){ // ####  Si el dia de corte es igual al dia de hoy
				$fecha->sub(new DateInterval('P6D')); // resta 6 dias a la fecha
				$fecha_inicio=$fecha->format('Y-m-d');
				$fecha_fin=$fecha_bd; // fin del periodo
			//	echo 'fecha_inicio: '.$fecha_inicio.'<br>';
			//	echo 'fecha fin: '.$fecha_fin.'<br>';
			} // dia de corte diferente al dia de hoy
			if($dia_hoy>$dia){
				$resta=$dia_hoy-$dia;
				$resta=$resta-1;																				
				$restar='P'.$resta.'D';													
				$fecha->sub(new DateInterval($restar)); // resta x dias a la fecha
				$fecha_inicio=$fecha->format('Y-m-d');  // inicio del periodo de nomina
				$fecha2 = new DateTime($fecha_inicio);											
				$fecha2->add(new DateInterval('P6D')); // suma 7 dias a la fecha
				$fecha_fin=$fecha2->format('Y-m-d');

			}
			
			if($dia_hoy==0){
				$resta=6-$dia;																				
				$restar='P'.$resta.'D';													
				$fecha->sub(new DateInterval($restar)); // resta x dias a la fecha
				$fecha_inicio=$fecha->format('Y-m-d');  // inicio del periodo de nomina
				$fecha2 = new DateTime($fecha_inicio);											
				$fecha2->add(new DateInterval('P6D')); // suma 7 dias a la fecha
				$fecha_fin=$fecha2->format('Y-m-d');
			//	$fecha_fin=$fecha_bd; // fin del periodo
			}
			if($dia_hoy<$dia){	
				$atras=($dia==0)? 7-$dia_hoy:$dia-$dia_hoy; // dia de corte menos dia de hoy para saber cuantos dias faltan para el dia de inicio
				$resta=6-$atras; // fecha de inicio	
		//		echo 'faltan: '.$atras.' dias para el inicio <br>';	
		//		echo 'Dia hoy: '.$dia_hoy.' / Dia corte: '.$dia.'<br>';																
				$restar='P'.$resta.'D';													
				$fecha->sub(new DateInterval($restar)); // resta x dias a la fecha de hoy
				$fecha_inicio=$fecha->format('Y-m-d');
		//		echo 'Restar: '.$restar.'<br>';	
		//		echo 'fecha_inicio: '.$fecha_inicio.'<br>';
				$fecha2 = new DateTime($fecha_bd);
				$suma=($dia==0)? abs($dia_hoy-7) : abs($dia_hoy-$dia);																																			
				$sumar='P'.$suma.'D';											
				$fecha2->add(new DateInterval($sumar)); // suma x dias a la fecha
				$fecha_fin=$fecha2->format('Y-m-d');	
		//		echo 'Sumar: '.$sumar.'<br>';
		//		echo 'fecha fin: '.$fecha_fin.'<br>';							
			}
			
		}
//		echo 'fecha inicio: '.$fecha_inicio.'<br>';
//		echo 'fecha fin: '.$fecha_fin.'<br>';
		$periodo[0]=$fecha_inicio;
		$periodo[1]=$fecha_fin;
		return $periodo;
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
	function entre_horas($horaini,$horafin){
    	$f1 = new DateTime($horaini);
    	$f2 = new DateTime($horafin);
    	$d = $f1->diff($f2);
    	return $d->format('%H:%I:%S');
	}
}

?>