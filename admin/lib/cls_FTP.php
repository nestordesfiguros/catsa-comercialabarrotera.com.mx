<?PHP
class FTP
{
	private $ruta;
	private $cid;
	private $usuario;
	private $tamano;
	private $resultado;
	private $archivos;
	private $archivo;
	private $rutaDestino;
	private $extencion;
	
	public function setRutaDestino($strRutaDestino)
	{
		$this->rutaDestino = $strRutaDestino;
	}
	
	public function setRuta($strRuta)
	{
		$this->ruta = $strRuta;	
	}
	
	public function setMaximoTamano($intTamano)
	{
		$this->tamano = $intTamano;	
	}	
	public function mostrarTMP($strArchivos)		
	{
		$this->archivos = $strArcnivos;
	}	
	public function setNombreArchivo($strArchivo)		
	{
		$this->archivo = $strArchivo;
	}	
		
	public function adjuntarArchivo($nombre_archivo, $temporal_archivo, $tamano_archivo)
	{
		if(file_exists($this->ruta.$nombre_archivo))
		{
			return "Nombre de Archivo ya existe";
		}
	/*	elseif($tamano_archivo > $this->tamano)
		{	
			return "El archivo sobrepasa el tama�o maximo permitido";
		}
*/		try{
			move_uploaded_file($temporal_archivo, $this->ruta.$nombre_archivo);
			return 1;
		}
		catch (Exception $e){
			$problema = "Fallo Al subir el archivo : ".$e->getMessage();
			return $problema;
		}		
		
	}
	
	public function eliminarTemporales()
	{
		foreach(glob($this->ruta) as $archivo) 
		{
			unlink($archivo);
		}
	}
	
	public function eliminarArchivo()
	{
		unlink($this->ruta);		
	}	
}
?>