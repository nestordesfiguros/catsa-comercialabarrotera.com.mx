<?php
session_start();
include '../../lib/clsConsultas.php';
$clsConsulta = new Consultas();
// Verificamos si el formulario fue enviado y si el archivo fue cargado correctamente
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['formFile'])) {
    // Configuración para la carga de archivos
    $id=$_POST['id'];
    $targetDir = "../../../img/productos/"; // Directorio donde se guardarán las imágenes
    $fileName = basename($_FILES["formFile"]["name"]);
    $targetFile = $targetDir . $fileName;
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

    // Verificamos si el archivo es una imagen real
    if (isset($_POST["submit"])) {
        $check = getimagesize($_FILES["formFile"]["tmp_name"]);
        if ($check !== false) {
            $uploadOk = 1;
        } else {
            echo "El archivo no es una imagen.";
            $uploadOk = 0;
        }
    }

    // Limitar el tamaño del archivo (5MB máximo)
    if ($_FILES["formFile"]["size"] > 5000000) {
        echo "Lo sentimos, el archivo es demasiado grande.";
        $uploadOk = 0;
    }

    // Permitir ciertos formatos de imagen (ahora incluimos webp)
    if ($imageFileType != "jpg" && $imageFileType != "jpeg" && $imageFileType != "png" && $imageFileType != "webp") {
        echo "Lo sentimos, solo se permiten archivos JPG, JPEG, PNG y WebP.";
        $uploadOk = 0;
    }

    // Verificamos si $uploadOk está en 0 (indica que hubo un error)
    if ($uploadOk == 0) {
        echo "Lo sentimos, tu archivo no se pudo subir.";
    } else {
        // Si todo está bien, intentamos subir el archivo
        if (isset($_FILES["formFile"])) {
            // Toma el nombre del producto desde la base de datos
            $con = "SELECT nombre FROM cat_productos WHERE id_producto=" . $id;
            $rs = $clsConsulta->consultaGeneral($con);
            $nombre = $rs[1]['nombre'];
        
            // Obtener la extensión del archivo original
            $fileExtension = pathinfo($_FILES["formFile"]["name"], PATHINFO_EXTENSION);
        
            // Limpiar el nombre del producto (quitar caracteres especiales y cambiar espacios por guiones)
            $nombre = strtolower($nombre);  // Opcional: convertir todo a minúsculas
            $nombre = preg_replace('/[^a-zA-Z0-9\s]/', '', $nombre); // Eliminar caracteres especiales
            $nombre = str_replace(' ', '-', $nombre);  // Reemplazar los espacios por guiones
        
            // Crear el nombre final del archivo usando el nombre del producto y la extensión
            $nombreConExtension = $nombre . '.' . $fileExtension;
        
            // Crear el path de destino con el nuevo nombre
            $targetFile = $targetDir . $nombreConExtension;
        
            // Mover el archivo subido a la carpeta con el nuevo nombre
            if (move_uploaded_file($_FILES["formFile"]["tmp_name"], $targetFile)) {
                // Crear la miniatura
                createThumbnail($targetFile, $fileExtension);
        
                // Guarda en la base de datos el nombre del producto con la extensión
                $con = "UPDATE cat_productos SET imagen='" . $nombreConExtension . "' WHERE id_producto=" . $id;
                $clsConsulta->aplicaQuery($con);
        
                // Redirigir a la página de productos con el id correspondiente
                header("Location: ../../productos-fotos/" . $id); // Aquí debes asegurarte de tener el id del producto
                exit; // Es importante llamar a exit después de header para evitar que el código posterior se ejecute
            } else {
                echo "Lo sentimos, hubo un error al subir tu archivo.";
            }
        } else {
            echo "No se ha enviado ningún archivo.";
        }
        
        
    }
} else {
    echo "No se ha enviado ningún archivo.";
}

// Función para crear la miniatura
// Función para crear la miniatura
function createThumbnail($filePath, $imageType) {
    // Obtener el tamaño original de la imagen
    list($width, $height) = getimagesize($filePath);
    
    // Define el tamaño máximo de la miniatura (proporcional)
    $maxWidth = 150; // Ancho máximo de la miniatura
    $maxHeight = 150; // Alto máximo de la miniatura
    
    // Calculamos la relación de aspecto (proporción de la imagen original)
    $aspectRatio = $width / $height;

    // Calculamos el nuevo tamaño proporcional de la imagen
    if ($width > $height) {
        $newWidth = $maxWidth;
        $newHeight = round($maxWidth / $aspectRatio);
    } else {
        $newHeight = $maxHeight;
        $newWidth = round($maxHeight * $aspectRatio);
    }

    // Crear la nueva imagen en miniatura con las dimensiones calculadas
    $thumb = imagecreatetruecolor($newWidth, $newHeight);
    
    // Dependiendo del tipo de archivo, usamos la función adecuada para cargar la imagen
    switch ($imageType) {
        case 'jpg':
        case 'jpeg':
            $source = imagecreatefromjpeg($filePath);
            break;
        case 'png':
            $source = imagecreatefrompng($filePath);
            break;
        case 'webp':
            $source = imagecreatefromwebp($filePath); // Usamos imagecreatefromwebp para archivos webp
            break;
        default:
            return; // Si no es un tipo de archivo permitido, no hacer nada
    }

    // Redimensionar la imagen original y copiarla en la miniatura
    imagecopyresampled($thumb, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
    
    // Crear el nuevo nombre para la miniatura (con el prefijo "mini-" antes del nombre)
    $thumbFilePath = pathinfo($filePath, PATHINFO_DIRNAME) . '/mini-' . pathinfo($filePath, PATHINFO_FILENAME) . '.' . $imageType;
    
    // Guardar la miniatura con el nombre modificado
    switch ($imageType) {
        case 'jpg':
        case 'jpeg':
            imagejpeg($thumb, $thumbFilePath);
            break;
        case 'png':
            imagepng($thumb, $thumbFilePath);
            break;
        case 'webp':
            imagewebp($thumb, $thumbFilePath); // Usamos imagewebp para guardar archivos webp
            break;
    }
    
    // Liberar memoria
    imagedestroy($thumb);
    imagedestroy($source);
}

?>
