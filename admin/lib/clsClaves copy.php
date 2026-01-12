<?PHP
class claves{
	public function generar_clave(){ 
		$str = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890#*-|})({@";
        $pwd = "";
        if (!preg_match('/(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{5,}/',$pwd)){
            $cad = "";
            for($i=0;$i<12;$i++) {
                $cad .= substr($str,rand(0,62),1);	
            }	

            $clave[]=$cad;

       //     $salt = substr($mail, 0, 2);
        //    $clave[] = crypt($cad, $salt);
            $pwd=$clave[0];
        }        	   	
		
		return $cad;
	}
	function codificaPwd($usr,$pwd){
		$salt = substr ($usr, 0, 2);
		$clave_crypt = crypt ($pwd, $salt);
		return $clave_crypt;
	}
}
/*
$clsClaves=new claves();
$correo="admin@dydasoft.net";
$claves=$clsClaves->generar_clave($correo);
echo $claves[0].'<br>';
echo $claves[1].'<br>';
*/

    ?>

