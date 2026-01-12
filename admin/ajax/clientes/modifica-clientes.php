<?php
// admin/ajax/clientes/modifica-clientes.php
include '../../lib/clsConsultas.php';
$clsConsulta = new Consultas();
include '../../lib/clsCadenas.php';
$clsCadenas = new Cadenas();

date_default_timezone_set('America/Mexico_City');
$fechaActual = date('Y-m-d H:i:s');

// Función para manejar NULL y comillas
function sqlValue($valor)
{
    return is_null($valor) ? "NULL" : "'" . addslashes($valor) . "'";
}

$id_cliente = isset($_POST['id']) ? (int)$_POST['id'] : 0;

$no_cliente = isset($_POST['no_cliente']) ? trim($_POST['no_cliente']) : '';
$razon_social = (isset($_POST['razon_social']) && trim($_POST['razon_social']) !== '')
    ? $clsCadenas->quitarAcentos($_POST['razon_social'])
    : null;

$nombre_comercial = (isset($_POST['nombre_comercial']) && trim($_POST['nombre_comercial']) !== '')
    ? $clsCadenas->quitarAcentos($_POST['nombre_comercial'])
    : '';

$rfc = isset($_POST['rfc']) ? trim($_POST['rfc']) : '';
$correo = isset($_POST['correo']) ? trim($_POST['correo']) : '';
$correo_factura = isset($_POST['correo_factura']) ? trim($_POST['correo_factura']) : '';

$calle = (isset($_POST['calle']) && trim($_POST['calle']) !== '')
    ? $clsCadenas->quitarAcentos($_POST['calle'])
    : '';

$num_ext = isset($_POST['num_ext']) ? trim($_POST['num_ext']) : '';
$num_int = (isset($_POST['num_int']) && trim($_POST['num_int']) !== '') ? trim($_POST['num_int']) : null;

$colonia = (isset($_POST['colonia']) && trim($_POST['colonia']) !== '')
    ? $clsCadenas->quitarAcentos($_POST['colonia'])
    : '';

$cp = (isset($_POST['cp']) && trim($_POST['cp']) !== '') ? trim($_POST['cp']) : null;

$id_municipio = (isset($_POST['id_municipio']) && $_POST['id_municipio'] !== '') ? (int)$_POST['id_municipio'] : null;
$id_estado = (isset($_POST['id_estado']) && $_POST['id_estado'] !== '') ? (int)$_POST['id_estado'] : null;

$localidad = (isset($_POST['localidad']) && trim($_POST['localidad']) !== '') ? trim($_POST['localidad']) : null;

$contacto = isset($_POST['contacto']) ? trim($_POST['contacto']) : '';

$tel1 = (isset($_POST['tel1']) && trim($_POST['tel1']) !== '') ? trim($_POST['tel1']) : null;
$tel2 = (isset($_POST['tel2']) && trim($_POST['tel2']) !== '') ? trim($_POST['tel2']) : null;

$credito_dias = (isset($_POST['credito_dias']) && $_POST['credito_dias'] !== '') ? (int)$_POST['credito_dias'] : 0;
$credito_limite = (isset($_POST['credito_limite']) && $_POST['credito_limite'] !== '') ? str_replace(',', '', $_POST['credito_limite']) : 0;

$mapa = $_POST['mapa'] ?? '';

// Lista de precios (opcional)
$lista_precios = null;
if (isset($_POST['lista_precios']) && trim($_POST['lista_precios']) !== '') {
    $lp = (int)$_POST['lista_precios'];
    $lista_precios = ($lp >= 1 && $lp <= 5) ? $lp : null;
}

// Validaciones mínimas (UX/consistencia)
if ($id_cliente <= 0 || $no_cliente === '' || $nombre_comercial === '' || $calle === '' || !$id_estado || !$id_municipio) {
    echo "error_datos";
    exit;
}

$con = "UPDATE cat_clientes SET 
    no_cliente = " . sqlValue($no_cliente) . ", 
    razon_social = " . sqlValue($razon_social) . ", 
    nombre_comercial = " . sqlValue($nombre_comercial) . ", 
    rfc = " . sqlValue($rfc) . ", 
    correo = " . sqlValue($correo) . ", 
    correo_factura = " . sqlValue($correo_factura) . ", 
    calle = " . sqlValue($calle) . ", 
    num_ext = " . sqlValue($num_ext) . ", 
    num_int = " . sqlValue($num_int) . ", 
    colonia = " . sqlValue($colonia) . ", 
    cp = " . ($cp !== null ? (int)$cp : "NULL") . ", 
    id_municipio = " . ($id_municipio !== null ? (int)$id_municipio : "NULL") . ", 
    id_estado = " . ($id_estado !== null ? (int)$id_estado : "NULL") . ", 
    localidad = " . sqlValue($localidad) . ", 
    contacto = " . sqlValue($contacto) . ", 
    tel1 = " . sqlValue($tel1) . ", 
    tel2 = " . sqlValue($tel2) . ", 
    mapa = " . sqlValue($mapa) . ", 
    lista_precios = " . ($lista_precios !== null ? (int)$lista_precios : "NULL") . ",
    updated_at = '$fechaActual', 
    credito_dias = $credito_dias, 
    credito_limite = $credito_limite 
WHERE id = $id_cliente";

$ok = $clsConsulta->guardarGeneral($con);
echo $ok ? "success" : "error_sql";
