<?php

namespace App\Natys\Helpers;

trait FileUploadTrait 
{
    private $uploadPath = "";
    private $fileName = "";

    
    public function uploadProfileImage($file)
    {
        $uploadDir = "Assets/img/usuarios/";
        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
        $maxSize = 3 * 1024 * 1024; 

        return $this->uploadImage($file, $uploadDir, $allowedTypes, $maxSize);
    }

    
    public function uploadProductImage($file)
    {
        $uploadDir = "Assets/img/producto/";
        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
        $maxSize = 3 * 1024 * 1024; 

        return $this->uploadImage($file, $uploadDir, $allowedTypes, $maxSize);
    }

    
    private function uploadImage($file, $uploadDir, $allowedTypes, $maxSize)
    {
        if (!$this->validateImageFile($file, $allowedTypes, $maxSize)) {
            return false;
        }

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $this->uploadPath = $uploadDir;
        $this->fileName = $this->generateImageFileName($file['name']);
        $fullPath = $this->uploadPath . $this->fileName;

        if (move_uploaded_file($file['tmp_name'], $fullPath)) {
            return [
                'path' => $fullPath,
                'name' => $this->fileName,
                'original_name' => $file['name'],
                'size' => $file['size'],
                'type' => $file['type']
            ];
        }

        return false;
    }

    
    private function validateImageFile($file, $allowedTypes, $maxSize)
    {
        if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
            return false;
        }

        
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, $allowedTypes)) {
            return false;
        }

        
        $allowedMimeTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        $fileType = mime_content_type($file['tmp_name']);
        
        if (!in_array($fileType, $allowedMimeTypes)) {
            return false;
        }

        
        $imageInfo = getimagesize($file['tmp_name']);
        if ($imageInfo === false) {
            return false;
        }

        
        if ($file['size'] > $maxSize) {
            return false;
        }

        return true;
    }

    
    private function generateImageFileName($originalName)
    {
        date_default_timezone_set("America/Caracas");
        $timestamp = date('Ymd_His');
        $random = rand(10000, 99999);
        $hash = md5($timestamp . $random . $originalName);
        $extension = pathinfo($originalName, PATHINFO_EXTENSION);
        
        return 'image_' . $hash . '.' . $extension;
    }

    
    public function deleteFile($filePath)
    {
        
        $physicalPath = str_replace('/Natys/', '', $filePath);
        $physicalPath = ltrim($physicalPath, '/');
        
        if (file_exists($physicalPath) && is_file($physicalPath)) {
            return unlink($physicalPath);
        }
        return false;
    }

    
    public function uploadImageWithValidation($file, $type = 'profile')
    {
        $maxSize = 3 * 1024 * 1024; 
        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
        
        
        if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
            return ['success' => false, 'message' => 'Error en la carga del archivo.'];
        }

        
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, $allowedTypes)) {
            return ['success' => false, 'message' => 'Solo se permiten archivos JPG, JPEG, PNG y GIF.'];
        }

        
        if ($file['size'] > $maxSize) {
            return ['success' => false, 'message' => 'La imagen no debe pesar más de 3MB.'];
        }

        
        $imageInfo = getimagesize($file['tmp_name']);
        if ($imageInfo === false) {
            return ['success' => false, 'message' => 'El archivo no es una imagen válida.'];
        }

        
        if ($type === 'profile') {
            $result = $this->uploadProfileImage($file);
        } else {
            $result = $this->uploadProductImage($file);
        }

        if ($result) {
            return [
                'success' => true,
                'ruta' => '/Natys/' . $result['path'], 
                'message' => 'Imagen subida correctamente.'
            ];
        }

        return ['success' => false, 'message' => 'Error al subir la imagen.'];
    }
}