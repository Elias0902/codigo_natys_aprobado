<?php
require_once __DIR__ . '/../controllers/TasaCambioController.php';

function obtenerValorDolar() {
    $tasaController = new TasaCambioController();
    $datos = $tasaController->obtenerTasaDolar();
    
    return [
        'valor' => number_format($datos['valor'], 2, ',', '.'),
        'fecha' => $datos['fecha_formateada'],
        'actualizado' => $datos['actualizado'] ?? true
    ];
}
?>
