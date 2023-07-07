<?php

function resizeImage($imagePath, $newWidth, $newHeight)
{
    // Obtener el tipo de imagen
    $imageType = exif_imagetype($imagePath);

    // Crear una imagen en base al tipo
    switch ($imageType)
    {
        case IMAGETYPE_JPEG:
            $image = imagecreatefromjpeg($imagePath);
            break;
        case IMAGETYPE_PNG:
            $image = imagecreatefrompng($imagePath);
            break;
        default:
            return false; // Tipo de imagen no compatible
    }

    // Obtener las dimensiones actuales de la imagen
    $width = imagesx($image);
    $height = imagesy($image);

    // Calcular las nuevas dimensiones manteniendo la proporción
    $ratio = $width / $height;
    if($newWidth / $newHeight > $ratio)
    {
        $newWidth = $newHeight * $ratio;
    }
    else
    {
        $newHeight = $newWidth / $ratio;
    }

    // Crear una nueva imagen con las dimensiones redimensionadas
    $newImage = imagecreatetruecolor($newWidth, $newHeight);
    if ($imageType === IMAGETYPE_PNG)
    {
        // Preservar la transparencia para imágenes PNG
        imagealphablending($newImage, false);
        imagesavealpha($newImage, true);
        $transparent = imagecolorallocatealpha($newImage, 0, 0, 0, 127);
        imagefill($newImage, 0, 0, $transparent);
    }
    imagecopyresampled($newImage, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

    // // Guardar la imagen redimensionada en el mismo formato original
    // $outputPath = 'ruta/donde/guardar/' . basename($imagePath);
    // switch ($imageType) {
    //     case IMAGETYPE_JPEG:
    //         imagejpeg($newImage, $outputPath);
    //         break;
    //     case IMAGETYPE_PNG:
    //         imagepng($newImage, $outputPath);
    //         break;
    // }

    // Guardar la imagen redimensionada en el mismo formato original
    $outputPath = dirname($imagePath) . '/' . basename($imagePath);
    switch($imageType)
    {
        case IMAGETYPE_JPEG:
            imagejpeg($newImage, $outputPath);
            break;
        case IMAGETYPE_PNG:
            imagepng($newImage, $outputPath);
            break;
    }

    // Liberar memoria
    imagedestroy($image);
    imagedestroy($newImage);

    // Eliminar la imagen original
    // unlink($imagePath);

    return true;
}

function resizeImagesInFolder($folderPath, $newWidth, $newHeight)
{
    $fileTypes = ['jpg', 'jpeg', 'png']; // Tipos de archivo permitidos

    // Recorrer el directorio
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($folderPath));
    foreach($iterator as $file)
    {
        if($file->isFile() && in_array(strtolower($file->getExtension()), $fileTypes))
        {
            resizeImage($file->getPathname(), $newWidth, $newHeight);
        }
    }
}

// Llamar a la función para redimensionar imágenes en una carpeta y subcarpetas
$folderPath = 'ruta_de_la_carpeta_de_imagenes';
$newWidth = 100; // Nuevo ancho deseado
$newHeight = 100; // Nuevo alto deseado
resizeImagesInFolder($folderPath, $newWidth, $newHeight);