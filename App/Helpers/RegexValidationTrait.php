<?php

namespace App\Natys\Helpers;

trait RegexValidationTrait
{
    
    private $patrones = [
        'email' => '/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/',
        'usuario' => '/^[a-zA-Z0-9_]{3,20}$/',
        'clave_fuerte' => '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/',
        'clave_media' => '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{6,}$/',
        'solo_letras' => '/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/',
        'solo_numeros' => '/^[0-9]+$/',
        'alfanumerico' => '/^[a-zA-Z0-9\s]+$/',
        'telefono' => '/^(\+?[0-9]{1,3})?[-\s]?[0-9]{3,4}[-\s]?[0-9]{3,4}$/',
        'codigo_producto' => '/^[a-zA-Z0-9\-_]{1,20}$/',
        'precio' => '/^\d+(\.\d{1,2})?$/',
        'nombre_archivo' => '/^[a-zA-Z0-9_\-\.\s]+$/'
    ];

    
    public function validarPatron($valor, $patron)
    {
        if (!isset($this->patrones[$patron])) {
            throw new \Exception("Patrón de validación no definido: $patron");
        }

        return preg_match($this->patrones[$patron], $valor) === 1;
    }

    
    public function validarEmail($email)
    {
        return $this->validarPatron($email, 'email');
    }

    
    public function validarUsuario($usuario)
    {
        return $this->validarPatron($usuario, 'usuario');
    }

    
    public function validarClaveFuerte($clave)
    {
        return $this->validarPatron($clave, 'clave_fuerte');
    }

    
    public function validarClaveMedia($clave)
    {
        return $this->validarPatron($clave, 'clave_media');
    }

    
    public function validarSoloLetras($texto)
    {
        return $this->validarPatron($texto, 'solo_letras');
    }

    
    public function validarSoloNumeros($numero)
    {
        return $this->validarPatron($numero, 'solo_numeros');
    }

    
    public function validarAlfanumerico($texto)
    {
        return $this->validarPatron($texto, 'alfanumerico');
    }

    
    public function validarTelefono($telefono)
    {
        return $this->validarPatron($telefono, 'telefono');
    }

    
    public function validarCodigoProducto($codigo)
    {
        return $this->validarPatron($codigo, 'codigo_producto');
    }

    
    public function validarPrecio($precio)
    {
        return $this->validarPatron($precio, 'precio');
    }

    
    public function validarNombreArchivo($nombre)
    {
        return $this->validarPatron($nombre, 'nombre_archivo');
    }

    
    public function sanitizarString($string)
    {
        return htmlspecialchars(strip_tags(trim($string)), ENT_QUOTES, 'UTF-8');
    }

    
    public function sanitizarNumero($numero)
    {
        return filter_var($numero, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    }

    
    public function sanitizarEmail($email)
    {
        return filter_var(trim($email), FILTER_SANITIZE_EMAIL);
    }
}