<?PHP
	require_once('..\lib\clsMunicipios.php');
	$objmunicipio=new Municipio();
	$estado_id = $_GET['id'];
	$objmunicipio->consulta_municipio($estado_id);
?>

		<select class="form-control" name="municipio_id" id="municipio_id">
			<?php
				echo $objmunicipio->get_municipios();
			?>
		</select>


