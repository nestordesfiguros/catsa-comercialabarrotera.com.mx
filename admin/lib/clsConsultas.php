<?PHP
class Consultas
{
	public $numrows;
	public $ultimoid;
	public $campos;
	public $campoValor;
	public $consulta;
	public $fechas;
	private $mysqli;

	public function __construct()
	{
		$this->mysqli = $this->getConexion();
	}

	public function getConexion()
	{
		if (!isset($this->mysqli)) {
			include 'config.php';
			if ($mysqli->connect_errno) {
				throw new Exception("Error de conexión: " . $mysqli->connect_error);
			}
			$this->mysqli = $mysqli;
			$this->mysqli->set_charset("utf8mb4");
		}
		return $this->mysqli;
	}

	/* #################  C O N S U L T A S */


	public function consultaGeneral($con)
	{		// Todo el contenido de la tabla						
		$rs = $this->aplicaQuery($con);
		//	echo $con.'<br>';
		$this->numrows = mysqli_num_rows($rs);
		$campos = $this->presentaCampos($rs);
		$valor = $this->ejecutaConsulta($campos, $rs);
		return $valor;
	}

	public function QueryGeneral($con)
	{		// Todo el contenido de la tabla						
		$rs = $this->aplicaQuery($con);
		//	echo $con.'<br>';		
		//	$campos=$this->presentaCampos($rs);
		//	$valor=$this->ejecutaConsulta($campos,$rs);
		//	return $rs;
	}
	function consultaIndividual($tabla, $id)
	{		// Todo el contenido de la tabla
		$con = "SELECT * FROM " . $tabla . " WHERE id=" . $id;
		$rs = $this->aplicaQuery($con);
		$this->numrows = mysqli_num_rows($rs);
		$campos = $this->presentaCampos($rs);
		$valor = $this->ejecutaConsulta($campos, $rs);
		//	echo var_dump($valor);
		return $valor;
	}
	function presenta_todo($tabla)
	{		// Todo el contenido de la tabla
		$con = "SELECT * FROM " . $tabla . "";
		$rs = $this->aplicaQuery($con);
		$this->numrows = mysqli_num_rows($rs);
		$campos = $this->presentaCampos($rs);
		$valor = $this->ejecutaConsulta($campos, $rs);
		return $valor;
	}
	function presentaCliente($tabla, $id)
	{
		$con = "SELECT * FROM " . $tabla . " WHERE id=" . $id;
		$rs = $this->aplicaQuery($con);
		$this->numrows = mysqli_num_rows($rs);
		$campos = $this->presentaCampos($rs);
		$valor = $this->ejecutaConsulta($campos, $rs);
		return $valor;
	}
	function presentaDetalleid($tabla, $id_detalle, $id)
	{   // El conenido de un id especifico
		$con = "SELECT * FROM " . $tabla . " WHERE " . $id_detalle . "=" . $id;
		$rs = $this->aplicaQuery($con);
		$this->numrows = mysqli_num_rows($rs);
		$campos = $this->presentaCampos($rs);
		$valor = $this->ejecutaConsulta($campos, $rs);
		return $valor;
	}
	function presentaDetalle($tabla, $id)
	{   // El conenido de un id especifico
		$con = "SELECT * FROM " . $tabla . " WHERE id=" . $id;
		$rs = $this->aplicaQuery($con);
		$this->numrows = mysqli_num_rows($rs);
		$campos = $this->presentaCampos($rs);
		$valor = $this->ejecutaConsulta($campos, $rs);
		return $valor;
	}
	function ejecutaConsulta($campos, $rs)
	{
		if ($this->numrows > 0) {
			$d = 1;
			while ($val = $rs->fetch_assoc()) {
				$c = 1;
				foreach ($campos as $campo) {
					$valor[$d][$campo] = $val[$campo];
					$c++;
				}
				$d++;
			}
			$this->campoValor = $valor;
			return $valor;
		} else {
			return NULL;
		}
	}
	public function presentaCampos($rs)
	{
		$info_campo = mysqli_fetch_fields($rs);
		$this->campos = $info_campo;
		foreach ($info_campo as $valor) {
			$campo[] = $valor->name;
		}
		return $campo;
	}

	function tomaFolio($campo)
	{
		$con = "SELECT " . $campo . " FROM folios WHERE id=1";
		//   echo $con;
		$rs = $this->aplicaQuery($con);
		foreach ($rs as $v => $val) {
			$valor = $val[$campo];
		}
		return $valor;
	}

	//  ***************************************  G U A R D A R  	
	function guardar($tabla, $post)
	{
		//	$clsCadena=new cadenas();  
		include 'config.php';
		$campos = '';
		$valores = '';
		foreach ($_POST as $nombre_campo => $valor) {
			if ($nombre_campo == 'fin') {
				break;
			}
			$campos .= $nombre_campo . ", ";
			//	   echo $nombre_campo.' ==> '.$valor.'<br>';
			if ($valor == 'ON' || $valor == 'on') {
				$valor = 1;
			}
			//   $valor =$clsCadena->normaliza($valor);
			$valores .= "'" . strtoupper($valor) . "', ";
		}
		$campos = substr($campos, 0, -2);
		$valores = substr($valores, 0, -2);

		$con = "INSERT INTO " . $tabla . " (" . $campos . ") VALUES (" . $valores . ")";
		//	echo $con.'<br>';
		$this->consulta = $con;
		if ($rs = $mysqli->query($con)) {
			$this->ultimoid = $mysqli->insert_id;
		}
	}

	/* *********************************************  MODIFICAR */
	function modificar($post, $tabla, $id)
	{
		//	$clsCadena=new cadenas();
		include 'config.php';
		$guardar = '';
		foreach ($post as $nombre_campo => $valor) {
			if ($nombre_campo == 'fin') {
				break;
			}
			//	if($valor=='ON' || $valor=='on'){ $valor=1;}
			//	$valor =$clsCadena->normaliza($valor);

			$guardar .= $nombre_campo . "='" . strtoupper($valor) . "', ";
		}
		$guardar = substr($guardar, 0, -2);
		$con = "UPDATE " . $tabla . " SET " . $guardar . " WHERE id=" . $id;
		//	echo $con.'<br>';
		$rs = $this->aplicaQuery($con);
		//	$campos=$clsConsulta->presentaCampos($rs);
		$this->consulta = $con;
	}

	/* *********************************************  BAJA */
	function baja($tabla, $id)
	{
		//	$clsCadena=new cadenas();
		include 'config.php';
		$con = "DELETE FROM " . $tabla . " WHERE id=" . $id;
		//	echo $con.'<br>';
		$rs = $this->aplicaQuery($con);
		$this->consulta = $con;
	}
	/* *********************************************  BORRAR */
	function borrar($tabla, $id)
	{
		include 'config.php';
		$con = "DELETE FROM " . $tabla . " WHERE id=" . $id;
		//	echo $con.'<br>';
		//	$this->aplicaQuery($con);
		if ($rs = $mysqli->query($con)) {
		}
	}

	/* *********************************************  GUARDAR */
	public function guardarGeneral($con)
	{
		include 'config.php';
		if ($rs = $mysqli->query($con)) {
			$this->ultimoid = $mysqli->insert_id;
		}
		//	$this->rs=$rs;
		return $rs;
	}

	function accesos($post, $tabla, $id)
	{ // Funcion Accesos ****************
		include 'config.php';
		$cambiar = '';
		$con = "SELECT * FROM accesos WHERE id=" . $id;
		//    echo $con.'<br>';
		$rs = $this->aplicaQuery($con);
		$campos = $this->presentaCampos($rs);
		foreach ($campos as $valor) {
			if ($valor == 'id' || $valor == 'id_personal') {
			} else {
				$cambiar .= $valor . "=0, ";
			}
		}
		$cambiar = substr($cambiar, 0, -2);
		$con = "UPDATE " . $tabla . " SET " . $cambiar . " WHERE id=" . $id;
		//	echo $con.'<br>';
		$rs = $this->aplicaQuery($con);

		$guardar = '';
		foreach ($_POST as $nombre_campo => $valor) {
			if ($nombre_campo == 'fin') {
				break;
			}
			$guardar .= $nombre_campo . "=1, ";
		}
		$guardar = substr($guardar, 0, -2);
		$cong = "UPDATE " . $tabla . " SET " . $guardar . " WHERE id=" . $id;
		$rs = $this->aplicaQuery($cong);
		//	echo $cong.'<br>';	
	}

	public function bitacora($folio, $id_usuario, $accion, $comentario, $seccion)
	{
		$con = "INSERT INTO mov_bitacora (folio, id_usuario, accion, comentario, seccion, fecha_mov) VALUES ('" . $folio . "', " . $id_usuario . ", '" . $accion . "', '" . $comentario . "', '" . $seccion . "', NOW())";

		$this->aplicaQuery($con);
	}
	/*
	public function camposTablas($tabla){
		$resultado = mysql_query("SHOW COLUMNS FROM alguna_tabla");
		if (!$resultado) {
		//	echo 'No se pudo ejecutar la consulta: ' . mysql_error();
			exit;
		}
	}
	*/

	function borrarDetalle($tabla, $id, $detalle)
	{
		include 'config.php';
		$con = "DELETE FROM " . $tabla . " WHERE " . $detalle . "=" . $id;
		//	echo $con.'<br>';
		//	$this->aplicaQuery($con);
		if ($rs = $mysqli->query($con)) {
		}
	}

	function insertaCamposDetalle($tabla, $campos, $id)
	{
		include 'config.php';
		$con = "INSERT INTO " . $tabla . " (" . $campos . ") VALUES (" . $valores . ")";
		if ($rs = $mysqli->query($con)) {
		}
	}

	public function aplicaQuery($con)
	{
		include 'config.php';
		//    echo $con.'<br>';
		if ($rs = $mysqli->query($con)) {
		}
		//	$this->rs=$rs;		
		return $rs;
	}



	public function codificaPwd($usr, $pwd)
	{
		$salt = substr($usr, 0, 2);
		$clave_crypt = crypt($pwd, $salt);
		return $clave_crypt;
	}

	/* Roles Accesos   */

	function tomaRol($rol_id, $permiso_id)
	{
		$con = "SELECT * FROM roles_permisos WHERE rol_id=" . $rol_id . " AND permiso_id=" . $permiso_id;
		$this->consultaGeneral($con);

		if ($this->numrows > 0) {
			$res = 'true';
		} else {
			$res = 'false';
		}
		return $res;
	}


	function encode64($input)
	{
		return strtr(base64_encode($input), '+/=', '._-');
	}

	function decode64($input)
	{
		return base64_decode(strtr($input, '._-', '+/='));
	}

	public function escape($string)
	{
		include 'config.php';
		return $mysqli->real_escape_string($string);
	}


	function obtenerFechaYHoraNumerica()
	{
		// Obtener la fecha y hora actual en formato YYYYMMDDHHMMSS
		$fechaHora = date('YmdHis');

		// Devolver la fecha y hora como una cadena de números
		return $fechaHora;
	}

	public function cantidadEnTexto($cantidad)
	{
		// Separar la parte entera de los decimales
		$partes = explode('.', number_format($cantidad, 2, '.', ''));
		$entero = $partes[0];
		$decimales = isset($partes[1]) ? $partes[1] : '00';

		$decimales = str_pad(substr($decimales, 0, 2), 2, '0');

		$unidades = array('', 'UN', 'DOS', 'TRES', 'CUATRO', 'CINCO', 'SEIS', 'SIETE', 'OCHO', 'NUEVE');
		$decenas = array('DIEZ', 'ONCE', 'DOCE', 'TRECE', 'CATORCE', 'QUINCE', 'DIECISEIS', 'DIECISIETE', 'DIECIOCHO', 'DIECINUEVE');
		$veintenas = array('VEINTE', 'VEINTIUN', 'VEINTIDOS', 'VEINTITRES', 'VEINTICUATRO', 'VEINTICINCO', 'VEINTISEIS', 'VEINTISIETE', 'VEINTIOCHO', 'VEINTINUEVE');
		$decenasCompletas = array('', 'DIEZ', 'VEINTE', 'TREINTA', 'CUARENTA', 'CINCUENTA', 'SESENTA', 'SETENTA', 'OCHENTA', 'NOVENTA');
		$centenas = array('', 'CIENTO', 'DOSCIENTOS', 'TRESCIENTOS', 'CUATROCIENTOS', 'QUINIENTOS', 'SEISCIENTOS', 'SETECIENTOS', 'OCHOCIENTOS', 'NOVECIENTOS');

		$texto = '';
		$entero = (int)$entero;

		if ($entero == 0) {
			$texto = 'CERO';
		} else {
			$millones = floor($entero / 1000000);
			if ($millones > 0) {
				$texto .= ($millones == 1) ? 'UN MILLON ' : $this->convertirGrupo($millones, $unidades, $decenas, $veintenas, $decenasCompletas, $centenas) . ' MILLONES ';
				$entero %= 1000000;
			}

			$miles = floor($entero / 1000);
			if ($miles > 0) {
				$texto .= ($miles == 1) ? 'MIL ' : $this->convertirGrupo($miles, $unidades, $decenas, $veintenas, $decenasCompletas, $centenas) . ' MIL ';
				$entero %= 1000;
			}

			if ($entero > 0) {
				$texto .= $this->convertirGrupo($entero, $unidades, $decenas, $veintenas, $decenasCompletas, $centenas);
			}
		}

		return trim(preg_replace('/\s+/', ' ', $texto)) . ' ' . $decimales . '/100 MN';
	}

	private function convertirGrupo($numero, $unidades, $decenas, $veintenas, $decenasCompletas, $centenas)
	{
		$texto = '';
		$centena = floor($numero / 100);
		$decena = floor(($numero % 100) / 10);
		$unidad = $numero % 10;

		if ($centena > 0) {
			if ($numero == 100) return 'CIEN';
			$texto .= $centenas[$centena] . ' ';
		}

		if ($decena == 1) {
			$texto .= $decenas[$unidad] . ' ';
		} elseif ($decena == 2) {
			$texto .= $veintenas[$unidad] . ' ';
		} elseif ($decena > 2) {
			$texto .= $decenasCompletas[$decena];
			if ($unidad > 0) $texto .= ' Y ' . $unidades[$unidad];
			$texto .= ' ';
		} elseif ($unidad > 0) {
			$texto .= $unidades[$unidad] . ' ';
		}

		return trim($texto);
	}

	/**
	 * Sanitiza un valor para uso en consultas SQL
	 * @param mixed $valor El valor a sanitizar
	 * @param bool $esNumero Indica si el valor debe tratarse como número
	 * @param bool $aceptarNulo Si permite valores NULL
	 * @param bool $agregarComillas Si debe incluir las comillas en el resultado
	 * @return string Valor sanitizado
	 */
	public function sanitizar($valor, $esNumero = false, $aceptarNulo = true, $agregarComillas = true)
	{
		// Si el valor es nulo o está vacío
		if ($valor === null || $valor === '' || $valor === false) {
			return $aceptarNulo ? 'NULL' : ($esNumero ? '0' : ($agregarComillas ? "''" : ''));
		}

		// Si es número y no debe escaparse
		if ($esNumero && is_numeric($valor)) {
			return $valor;
		}

		// Asegurar conexión
		if (!isset($this->mysqli)) {
			$this->getConexion();
		}

		// Escapar caracteres especiales
		$valorSanitizado = $this->mysqli->real_escape_string(strval($valor));

		// Devolver con o sin comillas según parámetro
		return $agregarComillas ? "'$valorSanitizado'" : $valorSanitizado;
	}

	/**
	 * Versión mejorada para sanitizar arrays completos
	 * @param array $datos Array clave-valor con los datos a sanitizar
	 * @return array Array con los valores sanitizados
	 */
	public function sanitizarArray($datos)
	{
		$sanitizados = [];
		foreach ($datos as $clave => $valor) {
			// Determinar si es número (para campos conocidos)
			$esNumero = is_numeric($valor) &&
				(stripos($clave, 'id_') === 0 ||
					stripos($clave, 'num') !== false ||
					stripos($clave, 'cantidad') !== false);

			$sanitizados[$clave] = $this->sanitizar($valor, $esNumero);
		}
		return $sanitizados;
	}

	public function obtenerError()
	{
		if (isset($this->mysqli) && $this->mysqli->connect_errno) {
			return $this->mysqli->connect_error;
		} elseif (isset($this->mysqli) && $this->mysqli->error) {
			return $this->mysqli->error;
		}
		return 'Error desconocido';
	}

	/**
	 * Escapa una cadena para usarla en consultas SQL
	 * @param string $string Cadena a escapar
	 * @return string Cadena escapada
	 */
	public function real_escape_string($string)
	{
		$conn = $this->getConexion();
		return $conn->real_escape_string($string);
	}
	/**
	 * Ejecuta una consulta preparada con parámetros
	 * @param string $sql Consulta SQL con placeholders (?)
	 * @param array $params Array de parámetros para bindear
	 * @param string $tipos Cadena de tipos de parámetros (i=entero, d=doble, s=string, b=blob)
	 * @return array|bool Array con resultados o false en caso de error
	 */
	public function consultaPreparada($sql, $params = [], $tipos = '')
	{
		if (!isset($this->mysqli)) {
			$this->getConexion();
		}

		$stmt = $this->mysqli->prepare($sql);
		if (!$stmt) {
			error_log("Error SQL: " . $this->mysqli->error . "\nConsulta: " . $sql);
			throw new Exception("Error al preparar consulta: " . $this->mysqli->error);
		}

		if (!empty($params)) {
			if (empty($tipos)) {
				$tipos = '';
				foreach ($params as $param) {
					if (is_int($param)) {
						$tipos .= 'i';
					} elseif (is_float($param)) {
						$tipos .= 'd';
					} elseif (is_string($param)) {
						$tipos .= 's';
					} else {
						$tipos .= 'b';
					}
				}
			}

			$bindParams = [$tipos];
			foreach ($params as &$param) {
				$bindParams[] = &$param;
			}

			if (!call_user_func_array([$stmt, 'bind_param'], $bindParams)) {
				$stmt->close();
				throw new Exception("Error al bindear parámetros: " . $stmt->error);
			}
		}

		if (!$stmt->execute()) {
			$error = $stmt->error;
			$stmt->close();
			throw new Exception("Error al ejecutar consulta: " . $error);
		}

		$stmt->store_result();
		$meta = $stmt->result_metadata();

		if ($meta) {
			$row = [];
			$bindResult = [];

			while ($field = $meta->fetch_field()) {
				$bindResult[$field->name] = null;
				$row[] = &$bindResult[$field->name];
			}

			call_user_func_array([$stmt, 'bind_result'], $row);

			$data = [];
			while ($stmt->fetch()) {
				$registro = [];
				foreach ($bindResult as $key => $val) {
					$registro[$key] = $val;
				}
				$data[] = $registro;
			}

			$stmt->close();
			return $data;
		}

		$affectedRows = $stmt->affected_rows;
		$this->ultimoid = $stmt->insert_id;
		$stmt->close();
		return $affectedRows;
	}

	/**
	 * Consulta segura con múltiples JOINs
	 * @param string $tablaPrincipal Tabla principal de la consulta
	 * @param array $joins Array de joins [alias => ['table'=>, 'field'=>, 'on_condition'=>]]
	 * @param string $condicion Condición WHERE (con ? para parámetros)
	 * @param array $params Valores para los parámetros en la condición
	 * @param string $camposSelect Campos a seleccionar de la tabla principal (por defecto *)
	 * @param string $orden Ordenamiento (ej: "ORDER BY fecha DESC")
	 * @return array|null Resultados de la consulta
	 */
	public function consultaConJoinsSegura($tablaPrincipal, $joins, $condicion = "", $params = [], $camposSelect = "*", $orden = "")
	{
		// Construir SELECT
		$query = "SELECT $tablaPrincipal.$camposSelect";

		// Agregar campos de JOINs
		foreach ($joins as $alias => $join) {
			if (!isset($join['table']) || !isset($join['field']) || !isset($join['on_condition'])) {
				throw new InvalidArgumentException("Cada JOIN debe tener 'table', 'field' y 'on_condition'");
			}
			$query .= ", {$join['table']}.{$join['field']} AS $alias";
		}

		// Construir FROM
		$query .= " FROM $tablaPrincipal";

		// Agregar JOINs
		foreach ($joins as $join) {
			$query .= " INNER JOIN {$join['table']} ON ({$join['on_condition']})";
		}

		// Agregar WHERE si existe
		if (!empty($condicion)) {
			$query .= " WHERE $condicion";
		}

		// Agregar ORDER BY si existe
		if (!empty($orden)) {
			$query .= " $orden";
		}

		// Preparar consulta
		$stmt = $this->mysqli->prepare($query);
		if (!$stmt) {
			throw new Exception("Error al preparar consulta: " . $this->mysqli->error);
		}

		// Bindear parámetros si existen
		if (!empty($params)) {
			$tipos = "";
			$valores = [];
			foreach ($params as $param) {
				if (is_int($param)) $tipos .= "i";
				elseif (is_float($param)) $tipos .= "d";
				else $tipos .= "s";
				$valores[] = $param;
			}
			$stmt->bind_param($tipos, ...$valores);
		}

		// Ejecutar
		$stmt->execute();
		$result = $stmt->get_result();

		// Procesar resultados
		$this->numrows = $result->num_rows;
		$campos = $this->presentaCampos($result);
		return $this->ejecutaConsulta($campos, $result);
	}

	public function consultaIndividualSegura($tabla, $id)
	{
		$stmt = $this->mysqli->prepare("SELECT * FROM $tabla WHERE id = ?");
		$stmt->bind_param("i", $id);
		$stmt->execute();
		$stmt->store_result();

		$meta = $stmt->result_metadata();
		$row = [];
		$bindResult = [];

		while ($field = $meta->fetch_field()) {
			$bindResult[$field->name] = null;
			$row[] = &$bindResult[$field->name];
		}

		call_user_func_array([$stmt, 'bind_result'], $row);

		$data = [];
		while ($stmt->fetch()) {
			$registro = [];
			foreach ($bindResult as $key => $val) {
				$registro[$key] = $val;
			}
			$data[] = $registro;
		}

		$stmt->close();
		$this->numrows = count($data);
		$this->campos = array_keys($bindResult);
		$this->campoValor = $data;
		return $data;
	}

	public function insertarSeguro($tabla, array $datos)
	{
		$campos = implode(", ", array_keys($datos));
		$placeholders = implode(", ", array_fill(0, count($datos), "?"));
		//print_r($placeholders) ;

		$stmt = $this->mysqli->prepare("INSERT INTO $tabla ($campos) VALUES ($placeholders)");

		$tipos = "";
		$valores = [];
		foreach ($datos as $valor) {
			if (is_int($valor)) $tipos .= "i";
			elseif (is_double($valor)) $tipos .= "d";
			else $tipos .= "s";
			$valores[] = $valor;
		}

		$stmt->bind_param($tipos, ...$valores);
		$stmt->execute();
		$this->ultimoid = $this->mysqli->insert_id;
		return $stmt->affected_rows;
	}

	public function actualizarSeguro($tabla, $id, array $datos)
	{
		$setParts = [];
		foreach ($datos as $campo => $valor) {
			$setParts[] = "$campo = ?";
		}
		$setClause = implode(", ", $setParts);

		$stmt = $this->mysqli->prepare("UPDATE $tabla SET $setClause WHERE id = ?");

		$tipos = "";
		$valores = [];
		foreach ($datos as $valor) {
			if (is_int($valor)) $tipos .= "i";
			elseif (is_double($valor)) $tipos .= "d";
			else $tipos .= "s";
			$valores[] = $valor;
		}
		$tipos .= "i";
		$valores[] = $id;

		$stmt->bind_param($tipos, ...$valores);
		$stmt->execute();
		return $stmt->affected_rows;
	}

	public function eliminarSeguro($tabla, $id)
	{
		$stmt = $this->mysqli->prepare("DELETE FROM $tabla WHERE id = ?");
		$stmt->bind_param("i", $id);
		$stmt->execute();
		return $stmt->affected_rows;
	}
}
