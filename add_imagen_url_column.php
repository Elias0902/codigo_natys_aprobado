<?php
// Archivo para agregar la columna imagen_url a la tabla usuario

try {
    // Configuración de conexión a la base de datos
    $host = 'localhost';
    $dbname = 'natys';
    $username = 'root';
    $password = '';
    
    // Crear conexión PDO
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Verificar si la columna ya existe
    $stmt = $pdo->query("SHOW COLUMNS FROM usuario LIKE 'imagen_url'");
    $columnExists = $stmt->rowCount() > 0;
    
    if (!$columnExists) {
        // Agregar la columna imagen_url
        $sql = "ALTER TABLE usuario ADD COLUMN imagen_url VARCHAR(255) DEFAULT '/Natys/Assets/img/defaultAvatar.jpg' AFTER estado";
        $pdo->exec($sql);
        echo "Columna 'imagen_url' agregada correctamente a la tabla 'usuario'.\n";
        
        // Actualizar los registros existentes con la ruta de la imagen por defecto
        $updateSql = "UPDATE usuario SET imagen_url = :ruta_imagen";
        $stmt = $pdo->prepare($updateSql);
        $ruta_imagen = '/Natys/Assets/img/defaultAvatar.jpg';
        $stmt->bindParam(':ruta_imagen', $ruta_imagen, PDO::PARAM_STR);
        $stmt->execute();
        
        echo "Registros actualizados con la ruta de imagen por defecto.\n";
    } else {
        echo "La columna 'imagen_url' ya existe en la tabla 'usuario'.\n";
    }
    
    echo "Proceso completado.\n";
    
} catch (PDOException $e) {
    die("Error: " . $e->getMessage() . "\n");
}
?>
