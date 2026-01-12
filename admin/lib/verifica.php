<?php
// admin/lib/verifica.php
session_start();
include 'clsConsultas.php';
include 'clsClaves.php';

$clsConsulta = new Consultas();
$claves = new Claves();

date_default_timezone_set('Etc/GMT+6');


$usr = $_POST['usr'];
$pwd = $_POST['pwd'];

$con = "SELECT
    usuarios.usr,
    usuarios.pwd,
	usuarios.nombre,
    usuarios.rol,
    usuarios.id AS idUsr
FROM usuarios
WHERE usuarios.usr = '" . $usr . "'";

$rs = $clsConsulta->consultaGeneral($con);

if ($clsConsulta->numrows > 0) {
	foreach ($rs as $rowEmp) {
		$hash_guardado = $rowEmp['pwd'];
		$id_user = $rowEmp['idUsr'] ?? '';
		$nombre = $rowEmp['nombre']; // . ' ' . $rowEmp['apellido1p'] . ' ' . $rowEmp['apellido2p'];
		//$id_puesto = $rowEmp['idPuesto'];
		$rol = $rowEmp['rol'];
		//$idEmpresa = $rowEmp['idEmpresa'];

		// echo $pwd . '<br>';
		// echo $hash_guardado . '<br>';
		// echo $rol . '<br>';
		// exit;
		$hash_guardado = isset($rowEmp['pwd']) ? trim($rowEmp['pwd']) : '';
		$resultado_verificacion = $claves->verificaPwd($pwd, $hash_guardado);

		// var_dump($resultado_verificacion);
		// exit;

		if ($resultado_verificacion) {
			// Guardar sesión básica
			$_SESSION["id_user"] = $id_user;
			$_SESSION["rol"] = $rol;
			$_SESSION['nombre'] = $nombre;
			//$_SESSION['id_puesto'] = $id_puesto;
			$_SESSION['time'] = strtotime(date("Y-m-d H:i:s"));


			// Cargar lista de empresas disponibles
			if ($rol == 1) {
				$sqlEmpresas = "SELECT id, razon_social FROM cat_empresas";
			} else {
				$sqlEmpresas = "SELECT e.id, e.razon_social 
                    FROM cat_empresas e
                    JOIN usuarios_empresas ue ON ue.id_empresa = e.id
                    WHERE ue.id_usuario = $id_user";
			}

			$rsEmpresas = $clsConsulta->consultaGeneral($sqlEmpresas);
			$_SESSION['empresas_disponibles'] = [];

			foreach ($rsEmpresas as $emp) {
				$_SESSION['empresas_disponibles'][] = [
					'id' => $emp['id'],
					'razon_social' => $emp['razon_social']
				];
			}

			// **Nuevo bloque: Validar que tenga al menos una empresa asignada**
			if (count($_SESSION['empresas_disponibles']) === 0) {
				ob_end_clean();
				echo '{"existe":"no_empresa"}';
				exit;
			}


			// Si solo tiene una empresa, asignarla directamente
			if (count($_SESSION['empresas_disponibles']) === 1) {
				$_SESSION['id_empresa'] = $_SESSION['empresas_disponibles'][0]['id'];
				$_SESSION['razon_social'] = $_SESSION['empresas_disponibles'][0]['razon_social'];
			}

			// Cargar permisos por módulo
			$_SESSION['permisos'] = [];

			$sqlPermisos = "SELECT modulo, puede_ver, puede_crear, puede_editar, puede_eliminar
                FROM permisos_rol_modulo
                WHERE rol = $rol";



			$rsPermisos = $clsConsulta->consultaGeneral($sqlPermisos);

			foreach ($rsPermisos as $perm) {
				$modulo = (int)$perm['modulo'];
				$_SESSION['permisos'][$modulo] = [
					'ver' => (int)$perm['puede_ver'],
					'crear' => (int)$perm['puede_crear'],
					'editar' => (int)$perm['puede_editar'],
					'eliminar' => (int)$perm['puede_eliminar']
				];
			}

			// Cargar lista de módulos disponibles
			$_SESSION['modulos'] = [];

			$sqlModulos = "SELECT m.id, m.nombre, m.archivo, m.icono, m.modulo_padre
                FROM permisos_rol_modulo prm
                INNER JOIN modulos m ON prm.modulo = m.id
                WHERE prm.rol = $rol AND prm.puede_ver = 1 ORDER BY id ASC";

			$rsModulos = $clsConsulta->consultaGeneral($sqlModulos);

			if ($clsConsulta->numrows > 0) {
				foreach ($rsModulos as $mod) {
					$idModulo = (int)$mod['id'];
					$_SESSION['modulos'][$idModulo] = [
						'nombre'        => $mod['nombre'],
						'archivo'       => $mod['archivo'],
						'icono'         => $mod['icono'], // puede estar vacío o null
						'modulo_padre'  => $mod['modulo_padre'] // null o int
					];
				}
			}


			ob_end_clean();
			echo '{"existe":"true"}';
			exit;
		} else {
			ob_end_clean();
			echo '{"existe":"false"}';
			exit;
		}
	}
} else {
	ob_end_clean();
	echo '{"existe":"false"}';
	exit;
}
