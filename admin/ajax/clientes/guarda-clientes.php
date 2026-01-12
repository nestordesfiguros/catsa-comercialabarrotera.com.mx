<?php
// admin/ajax/clientes/guarda-clientes.php

session_start();
include '../../lib/clsConsultas.php';
$clsConsulta = new Consultas();

include '../../lib/clsCadenas.php';
$clsCadenas = new Cadenas();

date_default_timezone_set('America/Mexico_City');
$fechaActual = date('Y-m-d H:i:s');

// Empresa actual
$idEmpresa = 0;
if (isset($_SESSION['id_empresa'])) $idEmpresa = (int)$_SESSION['id_empresa'];
elseif (isset($_SESSION['empresa'])) $idEmpresa = (int)$_SESSION['empresa'];

// Inputs
$no_cliente = $_POST['no_cliente'] ?? '';

$razon_social = isset($_POST['razon_social']) ? trim($_POST['razon_social']) : '';
$nombre_comercial = isset($_POST['nombre_comercial']) ? trim($_POST['nombre_comercial']) : '';

$rfc = $_POST['rfc'] ?? '';
$correo = $_POST['correo'] ?? '';
$correo_factura = $_POST['correo_factura'] ?? '';

$calle = $_POST['calle'] ?? '';
$num_ext = $_POST['num_ext'] ?? '';
$num_int = isset($_POST['num_int']) && $_POST['num_int'] !== '' ? $_POST['num_int'] : NULL;

$colonia = $_POST['colonia'] ?? '';
$cp = (isset($_POST['cp']) && $_POST['cp'] != '') ? $_POST['cp'] : NULL;

$id_municipio = isset($_POST['id_municipio']) ? (int)$_POST['id_municipio'] : 0;
$id_estado = isset($_POST['id_estado']) ? (int)$_POST['id_estado'] : 0;

$localidad = (isset($_POST['localidad']) && $_POST['localidad'] != '') ? $_POST['localidad'] : NULL;

$contacto = $_POST['contacto'] ?? '';
$tel1 = (isset($_POST['tel1']) && $_POST['tel1'] != '') ? $_POST['tel1'] : NULL;
$tel2 = (isset($_POST['tel2']) && $_POST['tel2'] != '') ? $_POST['tel2'] : NULL;

$credito_dias = (isset($_POST['credito_dias']) && $_POST['credito_dias'] != '') ? (int)$_POST['credito_dias'] : 0;
$credito_limite = (isset($_POST['credito_limite']) && $_POST['credito_limite'] != '') ? str_replace(',', '', $_POST['credito_limite']) : 0;

$mapa = $_POST['mapa'] ?? '';

// Lista de precios (opcional)
$lista_precios = isset($_POST['lista_precios']) && $_POST['lista_precios'] !== '' ? (int)$_POST['lista_precios'] : NULL;

// Validaciones mínimas alineadas a lo que pediste
if (trim($no_cliente) === '' || trim($nombre_comercial) === '' || trim($calle) === '' || $id_estado <= 0 || $id_municipio <= 0) {
        echo "error_datos";
        exit;
}

// Normalización de acentos (como tu flujo actual)
$no_cliente = $clsCadenas->quitarAcentos($no_cliente);

$razon_social = $razon_social !== '' ? $clsCadenas->quitarAcentos($razon_social) : NULL;
$nombre_comercial = $clsCadenas->quitarAcentos($nombre_comercial);

$calle = $clsCadenas->quitarAcentos($calle);
$colonia = $clsCadenas->quitarAcentos($colonia);

if ($localidad !== NULL) $localidad = $clsCadenas->quitarAcentos($localidad);
if ($contacto !== '') $contacto = $clsCadenas->quitarAcentos($contacto);

// INSERT (usa NULL real donde aplica)
$con = "INSERT INTO cat_clientes (
            id_empresa,
            no_cliente,
            razon_social,
            nombre_comercial,
            rfc,
            correo,
            correo_factura,
            calle,
            num_ext,
            num_int,
            colonia,
            cp,
            id_municipio,
            id_estado,
            localidad,
            contacto,
            tel1,
            tel2,
            fecha_alta,
            credito_dias,
            credito_limite,
            mapa,
            lista_precios
        ) VALUES (
            " . ($idEmpresa > 0 ? $idEmpresa : "NULL") . ",
            '" . $clsConsulta->real_escape_string($no_cliente) . "',
            " . ($razon_social !== NULL ? "'" . $clsConsulta->real_escape_string($razon_social) . "'" : "NULL") . ",
            '" . $clsConsulta->real_escape_string($nombre_comercial) . "',
            '" . $clsConsulta->real_escape_string($rfc) . "',
            '" . $clsConsulta->real_escape_string($correo) . "',
            '" . $clsConsulta->real_escape_string($correo_factura) . "',
            '" . $clsConsulta->real_escape_string($calle) . "',
            '" . $clsConsulta->real_escape_string($num_ext) . "',
            " . ($num_int !== NULL ? "'" . $clsConsulta->real_escape_string($num_int) . "'" : "NULL") . ",
            '" . $clsConsulta->real_escape_string($colonia) . "',
            " . ($cp !== NULL ? "'" . $clsConsulta->real_escape_string($cp) . "'" : "NULL") . ",
            " . $id_municipio . ",
            " . $id_estado . ",
            " . ($localidad !== NULL ? "'" . $clsConsulta->real_escape_string($localidad) . "'" : "NULL") . ",
            '" . $clsConsulta->real_escape_string($contacto) . "',
            " . ($tel1 !== NULL ? "'" . $clsConsulta->real_escape_string($tel1) . "'" : "NULL") . ",
            " . ($tel2 !== NULL ? "'" . $clsConsulta->real_escape_string($tel2) . "'" : "NULL") . ",
            '" . $fechaActual . "',
            " . (int)$credito_dias . ",
            " . (float)$credito_limite . ",
            '" . $clsConsulta->real_escape_string($mapa) . "',
            " . ($lista_precios !== NULL ? (int)$lista_precios : "NULL") . "
        )";

try {
        $ok = $clsConsulta->guardarGeneral($con);
        echo $ok ? "success" : "error_sql";
} catch (Exception $e) {
        error_log("Error guarda-clientes.php: " . $e->getMessage());
        echo "error_sql";
}
