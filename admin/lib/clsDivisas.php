<?PHP
/*    Toma valor al dia del dolar  Diario Oficial de la Fedederación  */

class Divisas{
        
    function pesoAdolar($peso){
        $url='https://sidofqa.segob.gob.mx/dof/sidof/indicadores/';
        $data = json_decode( file_get_contents($url), true );           
        return $data;
    }    
}
/*
$divisa=new clsDivisas();

$dolar=1;
$divisa=$divisa->pesoAdolar($dolar);

echo $divisa['ListaIndicadores'][0]['fecha'].'<br>';
echo $divisa['ListaIndicadores'][0]['valor'];
*/

?>