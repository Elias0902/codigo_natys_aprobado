<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);


$usuarioId = (int)($_GET['id'] ?? 0);
$esMiniatura = isset($_GET['thumb']);

if (!$usuarioId) {
    header('HTTP/1.0 400 Bad Request');
    exit('ID de usuario no proporcionado');
}


try {
    $db = new PDO('mysql:host=localhost;dbname=natys;charset=utf8mb4', 'root', '');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    
    $query = "SELECT id, imagen_perfil FROM usuario WHERE id = :id";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $usuarioId, PDO::PARAM_INT);
    $stmt->execute();
    
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$usuario) {
        error_log("Usuario no encontrado con ID: $usuarioId");
        header('HTTP/1.0 404 Not Found');
        exit('Usuario no encontrado');
    }
    
    
    $rutaDefault = $_SERVER['DOCUMENT_ROOT'] . '/Natys/Assets/img/defaultAvatar.jpg';
    
    
    if (empty($usuario['imagen_perfil']) || !file_exists($_SERVER['DOCUMENT_ROOT'] . $usuario['imagen_perfil'])) {
        if (file_exists($rutaDefault)) {
            header('Content-Type: image/jpeg');
            header('Content-Length: ' . filesize($rutaDefault));
            readfile($rutaDefault);
        } else {
            header('HTTP/1.0 404 Not Found');
            exit('Imagen por defecto no encontrada');
        }
        exit;
    }
    
    
    $rutaImagen = $_SERVER['DOCUMENT_ROOT'] . $usuario['imagen_perfil'];
    
    
    if (!file_exists($rutaImagen) || !is_readable($rutaImagen)) {
        error_log("La imagen no existe o no se puede leer: $rutaImagen");
        
        if (file_exists($rutaDefault)) {
            header('Content-Type: image/jpeg');
            header('Content-Length: ' . filesize($rutaDefault));
            readfile($rutaDefault);
        } else {
            header('HTTP/1.0 404 Not Found');
            exit('Imagen no encontrada');
        }
        exit;
    }
    
    
    $tamanoArchivo = filesize($rutaImagen);
    $extension = strtolower(pathinfo($rutaImagen, PATHINFO_EXTENSION));
    
    
    $mimeTypes = [
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'gif' => 'image/gif'
    ];
    
    $contentType = $mimeTypes[$extension] ?? 'application/octet-stream';
    
    
    if ($esMiniatura) {
        
        switch ($extension) {
            case 'jpg':
            case 'jpeg':
                $imagen = imagecreatefromjpeg($rutaImagen);
                break;
            case 'png':
                $imagen = imagecreatefrompng($rutaImagen);
                break;
            case 'gif':
                $imagen = imagecreatefromgif($rutaImagen);
                break;
            default:
                
                header('Content-Type: ' . $contentType);
                header('Content-Length: ' . $tamanoArchivo);
                readfile($rutaImagen);
                exit;
        }
        
        if ($imagen === false) {
            error_log("No se pudo cargar la imagen: $rutaImagen");
            throw new Exception("No se pudo cargar la imagen");
        }
        
        
        $ancho = 100;
        $alto = 100;
        
        
        $ancho_original = imagesx($imagen);
        $alto_original = imagesy($imagen);
        
        
        $ratio_original = $ancho_original / $alto_original;
        
        if (($ancho / $alto) > $ratio_original) {
            $ancho = $alto * $ratio_original;
        } else {
            $alto = $ancho / $ratio_original;
        }
        
        
        $miniatura = imagecreatetruecolor($ancho, $alto);
        
        
        if ($extension === 'png') {
            imagealphablending($miniatura, false);
            imagesavealpha($miniatura, true);
        }
        
        
        imagecopyresampled($miniatura, $imagen, 0, 0, 0, 0, $ancho, $alto, $ancho_original, $alto_original);
        
        
        imagedestroy($imagen);
        
        
        header('Content-Type: ' . $contentType);
        
        switch ($extension) {
            case 'jpg':
            case 'jpeg':
                imagejpeg($miniatura, null, 90);
                break;
            case 'png':
                imagepng($miniatura, null, 9);
                break;
            case 'gif':
                imagegif($miniatura);
                break;
        }
        
        
        imagedestroy($miniatura);
        exit;
    } else {
        
        header('Content-Type: ' . $contentType);
        header('Content-Length: ' . $tamanoArchivo);
        readfile($rutaImagen);
        exit;
    }
    
} catch (Exception $e) {
    error_log('Error al procesar la imagen: ' . $e->getMessage());
    
    
    $rutaDefault = $_SERVER['DOCUMENT_ROOT'] . '/Natys/Assets/img/defaultAvatar.jpg';
    if (file_exists($rutaDefault)) {
        header('Content-Type: image/jpeg');
        header('Content-Length: ' . filesize($rutaDefault));
        readfile($rutaDefault);
    } else {
        header('HTTP/1.0 500 Internal Server Error');
        echo 'Error al procesar la imagen: ' . $e->getMessage();
    }
    exit;
}