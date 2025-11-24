<?php
class TasaCambioController {
    // URL oficial del BCV
    private $bcv_url = 'https://www.bcv.org.ve/';
    
    // Archivo de caché
    private $cache_file = __DIR__ . '/../../cache/tasa_dolar.json';
    private $cache_time = 3600; // 1 hora de caché
    
    // Patrones para buscar el valor en el HTML del BCV
    private $patrones_bcv = [
        '/<div class="col-sm-6 col-xs-6 centrado">\s*<strong>([\d.,]+)<\/strong>/',
        '/<div class="col-sm-6 col-xs-6 centrado">\s*([\d.,]+)\s*<\/div>/',
        '/USD[^\d]*([\d.,]+)/i',
        '/<div[^>]*id="dolar"[^>]*>\s*<div[^>]*>[^<]*<strong>([\d.,]+)<\/strong>/i'
    ];

    public function __construct() {
        // Crear directorio de caché si no existe
        if (!file_exists(dirname($this->cache_file))) {
            mkdir(dirname($this->cache_file), 0755, true);
        }
    }

    public function obtenerTasaDolar() {
        // Verificar si hay caché válido
        if ($this->cacheValido()) {
            $datos = json_decode(file_get_contents($this->cache_file), true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $datos['actualizado'] = false; // Indica que es un valor en caché
                return $datos;
            }
        }

        // Obtener el valor del dólar del BCV
        $valorDolar = $this->obtenerValorDolar();
        
        // Si no se pudo obtener, intentar con el valor en caché aunque esté vencido
        if ($valorDolar === null && file_exists($this->cache_file)) {
            $datos = json_decode(file_get_contents($this->cache_file), true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $datos['actualizado'] = false;
                $datos['error'] = 'Usando valor en caché';
                return $datos;
            }
        }
        
        // Si no hay valor y no hay caché, usar un valor por defecto
        if ($valorDolar === null) {
            $valorDolar = 36.20; // Valor por defecto
            $actualizado = false;
            $error = 'No se pudo obtener el valor del dólar';
        } else {
            $actualizado = true;
            $error = null;
        }
        
        // Preparar los datos para guardar en caché
        $datos = [
            'valor' => $valorDolar,
            'fecha' => date('Y-m-d H:i:s'),
            'fecha_formateada' => date('d/m/Y H:i'),
            'actualizado' => $actualizado
        ];
        
        if (isset($error)) {
            $datos['error'] = $error;
        }
        
        // Guardar en caché
        @file_put_contents($this->cache_file, json_encode($datos));
        
        return $datos;
    }

    private function obtenerValorDolar() {
        $valor = $this->obtenerDeBCV();
        if ($valor !== false) {
            return $valor;
        }
        
        // Si falla, intentar con el valor en caché
        if (file_exists($this->cache_file)) {
            $cache = json_decode(file_get_contents($this->cache_file), true);
            if (isset($cache['valor'])) {
                return $cache['valor'];
            }
        }
        
        // Si todo falla, devolver null
        return null;
    }
    
    private function obtenerDeBCV() {
        try {
            // Configuración del contexto para la petición HTTP
            $context = stream_context_create([
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'verify_host' => false
                ],
                'http' => [
                    'method' => 'GET',
                    'header' => "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36\r\n" .
                               "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8\r\n" .
                               "Accept-Language: es-ES,es;q=0.8,en-US;q=0.5,en;q=0.3\r\n" .
                               "Connection: keep-alive\r\n" .
                               "Upgrade-Insecure-Requests: 1\r\n",
                    'timeout' => 15,
                    'follow_location' => true,
                    'max_redirects' => 5
                ]
            ]);

            // Realizar la petición al BCV
            $html = @file_get_contents($this->bcv_url, false, $context);

            if ($html === false) {
                error_log('Error al obtener el HTML del BCV');
                return false;
            }

            // Log the HTML for debugging
            $debugFile = __DIR__ . '/../../debug_bcv.html';
            $result = file_put_contents($debugFile, $html);
            error_log('Debug file write result: ' . ($result === false ? 'FAILED' : 'SUCCESS') . ' - Size: ' . strlen($html) . ' - Path: ' . $debugFile);
            
            // Intentar con varios patrones para encontrar el valor
            foreach ($this->patrones_bcv as $patron) {
                if (preg_match($patron, $html, $matches)) {
                    $valor = trim($matches[1]);
                    
                    // Limpiar y formatear el valor
                    $valor = str_replace('.', '', $valor);
                    $valor = str_replace(',', '.', $valor);
                    
                    if (is_numeric($valor)) {
                        return (float)$valor;
                    }
                }
            }
            
            error_log('No se pudo extraer el valor del dólar del HTML del BCV');
            return false;
            
        } catch (Exception $e) {
            error_log('Excepción al obtener el valor del dólar: ' . $e->getMessage());
            return false;
        }
    }

    private function cacheValido() {
        if (!file_exists($this->cache_file)) {
            return false;
        }
        $cache_time = filemtime($this->cache_file);
        return (time() - $cache_time) < $this->cache_time;
    }
}
?>
