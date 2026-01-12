<?PHP
class Cadenas{

	function quitarAcentos($cadena) {
		$acentos = array(
			'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u',
			'Á' => 'A', 'É' => 'E', 'Í' => 'I', 'Ó' => 'O', 'Ú' => 'U',
			'ñ' => 'n', 'Ñ' => 'N',
			'ä' => 'a', 'ë' => 'e', 'ï' => 'i', 'ö' => 'o', 'ü' => 'u',
			'Ä' => 'A', 'Ë' => 'E', 'Ï' => 'I', 'Ö' => 'O', 'Ü' => 'U'
		);
	
		return strtr($cadena, $acentos);
	}

	function getSubString($string, $length=NULL){
		//Si no se especifica la longitud por defecto es 50
		if ($length == NULL)
			$length = 50;
		//Primero eliminamos las etiquetas html y luego cortamos el string
		$stringDisplay = substr(strip_tags($string), 0, $length);
		//Si el texto es mayor que la longitud se agrega puntos suspensivos
		if (strlen(strip_tags($string)) > $length)
			$stringDisplay .= ' ...';
		return $stringDisplay;
	}
		
	function normaliza($string)
	{
	
		$string = trim($string);
	
		$string = str_replace(
			array('á', 'à', 'ä', 'â', 'ª', 'Á', 'À', 'Â', 'Ä'),
			array('a', 'a', 'a', 'a', 'a', 'A', 'A', 'A', 'A'),
			$string
		);
	
		$string = str_replace(
			array('é', 'è', 'ë', 'ê', 'É', 'È', 'Ê', 'Ë'),
			array('e', 'e', 'e', 'e', 'E', 'E', 'E', 'E'),
			$string
		);
	
		$string = str_replace(
			array('í', 'ì', 'ï', 'î', 'Í', 'Ì', 'Ï', 'Î'),
			array('i', 'i', 'i', 'i', 'I', 'I', 'I', 'I'),
			$string
		);
	
		$string = str_replace(
			array('ó', 'ò', 'ö', 'ô', 'Ó', 'Ò', 'Ö', 'Ô'),
			array('o', 'o', 'o', 'o', 'O', 'O', 'O', 'O'),
			$string
		);
	
		$string = str_replace(
			array('ú', 'ù', 'ü', 'û', 'Ú', 'Ù', 'Û', 'Ü'),
			array('u', 'u', 'u', 'u', 'U', 'U', 'U', 'U'),
			$string
		);
	
		$string = str_replace(
			array('ñ', 'Ñ', 'ç', 'Ç'),
			array('n', 'N', 'c', 'C',),
			$string
		);
	
		//Esta parte se encarga de eliminar cualquier caracter extraño
		$string = str_replace(
			array("\\", "¨", "º", "-", "~",
				 "#", "@", "|", "!", "\"",
				 "·", "$", "%", "&", "/",
				 "(", ")", "?", "'", "¡",
				 "¿", "[", "^", "`", "]",
				 "+", "}", "{", "¨", "´",
				 ">", "< ", ";", ",", ":",
				 ".", "*"),
			'',
			$string
		);
	
		$string=str_replace(" ","-",$string);
		return $string;
	}
	
	function cfDecodeEmail($encodedString){  // Decodifica el correo
	  $k = hexdec(substr($encodedString,0,2));
	  for($i=2,$email='';$i<strlen($encodedString)-1;$i+=2){
		$email.=chr(hexdec(substr($encodedString,$i,2))^$k);
	  }
	  return $email;
			// echo cfDecodeEmail('3e575058517e5d5f50485f4d105d5153'); // usage
	}
	
	function codifica($dato){
		//Configuración del algoritmo de encriptación
		//Debes cambiar esta cadena, debe ser larga y unica
		//nadie mas debe conocerla
		$clave  = 'Mahad';
		//Metodo de encriptación
		$method = 'aes-256-cbc';
		// Puedes generar una diferente usando la funcion $getIV()
		$iv = base64_decode("C9fBxl1EWtYTL1/M8jfstw==");
		 /*
		 Encripta el contenido de la variable, enviada como parametro.
		  */
		 $encriptar = function ($valor) use ($method, $clave, $iv) {
			 return openssl_encrypt ($valor, $method, $clave, false, $iv);
		 };		
		 /*
		 Genera un valor para IV
		 */
		 $getIV = function () use ($method) {
			 return base64_encode(openssl_random_pseudo_bytes(openssl_cipher_iv_length($method)));
		 };
		
		$dato_encriptado=$encriptar($dato);
		return $dato_encriptado;
	}
	function decodifica($dato){
		//Configuración del algoritmo de encriptación
		//Debes cambiar esta cadena, debe ser larga y unica
		//nadie mas debe conocerla
		$clave  = 'Mahad';
		//Metodo de encriptación
		$method = 'aes-256-cbc';
		// Puedes generar una diferente usando la funcion $getIV()
		$iv = base64_decode("C9fBxl1EWtYTL1/M8jfstw==");		 
		 /*
		 Desencripta el texto recibido
		 */
		 $desencriptar = function ($valor) use ($method, $clave, $iv) {
			 $encrypted_data = base64_decode($valor);
			 return openssl_decrypt($valor, $method, $clave, false, $iv);
		 };
		 /*
		 Genera un valor para IV
		 */
		 $getIV = function () use ($method) {
			 return base64_encode(openssl_random_pseudo_bytes(openssl_cipher_iv_length($method)));
		 };
		$dato_desencriptado = $desencriptar($dato);
		return $dato_desencriptado;
	}

}

?>