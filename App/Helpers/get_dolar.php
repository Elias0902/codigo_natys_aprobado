<?php
// Habilitar CORS si es necesario
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

// Incluir el helper del dólar
require_once __DIR__ . '/dolar.php';

// Obtener el valor actual del dólar
$dolarData = obtenerValorDolar();

// Si hay un error, devolver el valor por defecto
if (!is_array($dolarData) || empty($dolarData['valor'])) {
    $dolarData = [
        'valor' => '227,00',
        'fecha' => date('d/m/Y H:i'),
        'actualizado' => false,
        'success' => false,
        'message' => 'No se pudo obtener el valor actualizado del dólar'
    ];
} else {
    $dolarData['success'] = true;
}

// Devolver la respuesta en formato JSON
echo json_encode($dolarData);
