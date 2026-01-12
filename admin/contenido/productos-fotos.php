<?php
$con = "SELECT imagen, clave, nombre  FROM cat_productos WHERE id_producto =" . $cat;
$rs = $clsConsulta->consultaGeneral($con);
foreach ($rs as $v => $val) {
    $id_producto = $cat;
    //$id_categoria = $val['id_categoria'];
    $clave = $val['clave'];
    $nombre = $val['nombre'];
    // $descripcion = $val['descripcion'];
    //$id_empaque = $val['id_empaque'];
    // $cantidad_empaque = $val['cantidad_empaque'];
    // $tipo_embalaje = $val['tipo_embalaje'];
    // $precio_compra = $val['precio_compra'];
    // $precio_venta = $val['precio_venta'];
    // $unidad_medida = $val['unidad_medida'];

    // $presentacion = $val['presentacion'];
    //  $id_almacen = $val['id_almacen'];
    //  $id_marca = $val['id_marca'];
    //  $cantidad_disponible = $val['cantidad_disponible'];
    //  $stock_minimo = $val['stock_minimo'];
    //  $stock_maximo = $val['stock_maximo'];
    //  $fecha_registro = $val['fecha_registro'];
    //  $id_proveedor = $val['id_proveedor'];
    //  $estado = $val['estado'];
    //  $codigo_barras = $val['codigo_barras'];
    //  $fecha_vencimiento = $val['fecha_vencimiento'];
    $imagen = $val['imagen'];
}
?>
<div class="ms-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="productos">Inicio</a></li>
            <li class="breadcrumb-item"><a href="productos">Productos</a></li>
            <li class="breadcrumb-item active" aria-current="page">Fotos </li>
        </ol>
    </nav>
</div>

<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5>Foto de <?php echo $nombre; ?></h5>
                    </div>
                    <div class="card-body">
                        <div class="row d-flex justify-content-center">
                            <div class="col-12 col-sm-4">
                                <div>
                                    <?php
                                    if ($imagen != '') {
                                        echo '<img src="../img/productos/' . $imagen . '" class="img-fluid">';
                                    } else {
                                        echo '<img src="../img/productos/no_img.png" class="img-fluid">';
                                    }
                                    ?>

                                </div>
                                <form action="ajax/fotos/subir.php" method="POST" enctype="multipart/form-data" onsubmit="return validateForm()">
                                    <div class="mt-5">
                                        <label for="formFile" class="form-label text-primary">Selecciona una foto para el producto</label>
                                        <input class="form-control" type="file" id="formFile" name="formFile" onchange="checkFile()">
                                    </div>
                                    <div class="mt-3">
                                        <button type="submit" class="btn btn-primary w-100" id="submitBtn" disabled>Modificar Foto</button>
                                        <input type="hidden" name="id" value="<?php echo $cat; ?>">
                                    </div>
                                </form>

                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
</section>

<script>
    // Función para verificar el archivo seleccionado
    function checkFile() {
        var fileInput = document.getElementById('formFile');
        var submitButton = document.getElementById('submitBtn');
        
        // Si hay un archivo seleccionado, habilitar el botón de submit
        if (fileInput.files.length > 0) {
            submitButton.disabled = false;
        } else {
            submitButton.disabled = true;
        }
    }

    // Validación del formulario al hacer submit
    function validateForm() {
        var fileInput = document.getElementById('formFile');
        var filePath = fileInput.value;
        
        // Verificar si el campo está vacío
        if (filePath === "") {
            alert("Por favor, selecciona una imagen.");
            return false;
        }

        // Obtener la extensión del archivo
        var allowedExtensions = /(\.jpg|\.jpeg|\.png|\.webp)$/i;

        // Verificar que el archivo tenga una extensión válida
        if (!allowedExtensions.exec(filePath)) {
            alert("Solo se permiten imágenes con formato JPG, JPEG, PNG o WebP.");
            fileInput.value = ""; // Limpiar el campo
            submitButton.disabled = true; // Deshabilitar el botón nuevamente
            return false;
        }

        // Si pasa todas las validaciones, permitir el envío del formulario
        return true;
    }
</script>