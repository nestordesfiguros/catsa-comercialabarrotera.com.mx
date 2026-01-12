<?php 
include '../../lib/clsConsultas.php';
$clsConsulta=new Consultas();
include '../../lib/clsFechas.php';
$clsFecha=new Fechas();

$valores='';
$seleccionado='';
$archivos=$_POST['archivo'];
$ruta='../../expedientes/post_venta/';
$archivo = explode("|",$_POST['archivo']);
foreach($archivo as $file){
    if($file!=''){
        $nombreFile = new SplFileInfo($file);
        $ext=$nombreFile->getExtension();
     //   $arch=explode(".",$file);        
        if($ext=='xml'){            
            $seleccionado=$ruta.$file;
        }else{
            $seleccionado='';
        }
      
    }            
}

if($ext=='pdf'){
    $valores='{"contenido":"nada"}';
}else{

    // Cargar el archivo XML
    $xml = simplexml_load_file($seleccionado);

    // Obtener el valor de TipoDeComprobante
    $tipoDeComprobante = (string) $xml->xpath('//cfdi:Comprobante/@TipoDeComprobante')[0];

    // Imprimir el valor obtenido
    //echo "Tipo de Comprobante: " . $tipoDeComprobante;
    if($seleccionado!=''){
        if($tipoDeComprobante=='I'){ 
            $xml = simplexml_load_file( $seleccionado, 'SimpleXMLElement', 0, 'cfdi', true );
            
            $valores='{"contenido":"xml",';        
            foreach($xml->xpath('/cfdi:Comprobante') as $emisor){
                $fecha1=explode("T",$emisor['Fecha']);
                $year=substr($fecha1[0],0,4);
                $month=substr($fecha1[0],5,2);
                $day=substr($fecha1[0],8,2); 
                $fecha=$day.'-'.$month.'-'.$year;
            //   $fecha='${'.$year.'}-${'.$mont.'}-${'.$day.'}';
            //  $fecha=$year.'/'.$month.'/'.$day;

            //   $fecha=$clsFecha->fechaEntoEs($fecha1[0]);
                $valores.='"anio":"'.$year.'",';
                $valores.='"mes":"'.$month.'",';
                $valores.='"dia":"'.$day.'",';
                $valores.='"serie":"'.$emisor['Serie'].'",';        
                $valores.='"folio":"'.$emisor['Folio'].'",';    
                $valores.='"total":"'.$emisor['Total'].'",'; 
                    
            }
            foreach($xml->xpath('/cfdi:Comprobante/cfdi:Conceptos/cfdi:Concepto') as $emisor){
                $valores.='"descripcion":"'.$emisor['Descripcion'].'",'; 
                $valores.='"importe":"'. $emisor['Importe'].'",'; 
            }
            foreach($xml->xpath('/cfdi:Comprobante/cfdi:Receptor') as $emisor){
            //   $razon_social=$emisor['Nombre'];
                $rfc=$emisor['Rfc'];
            //  $valores.='"razon_social":"'.$emisor['Nombre'].'",'; 
                $valores.='"rfc":"'. $emisor['Rfc'].'",'; 
            }
            //   $valores=trim($valores,",");    
                
            $con="SELECT * FROM cat_clientes WHERE rfc='".$rfc."'";         
            $rs=$clsConsulta->consultaGeneral($con);
            if($clsConsulta->numrows>0){
                $valores.='"razon_social":"'.$rs[1]['razon_social'].'",';
                $valores.='"idCliente":"'.$rs[1]['id'].'",'; 
                $valores.='"contenido":"Valido"'; 
                $valores.='}';
            }else{
                $valores='{"contenido":"noCliente"}';
            }
        }else{
                $valores='{"contenido":"noValido"}';
        }    
            
    }else{
        $valores='{"contenido":"nada"}';
    }
}    
    echo  $valores;

?>