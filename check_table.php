<?php
// Mostrar todos los errores
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Función para formatear la salida de arrays
function formatArray($data) {
    echo '<pre>';
    print_r($data);
    echo '</pre>';
}

echo "<h1>Verificación de la tabla de usuarios</h1>";

try {
    // 1. Conectar a la base de datos
    $db = new PDO('mysql:host=localhost;dbname=natys;charset=utf8mb4', 'root', '');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h2>1. Estructura de la tabla 'usuario':</h2>";
    
    // Obtener la estructura de la tabla usuario
    $stmt = $db->query("SHOW COLUMNS FROM usuario");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Mostrar estructura
    echo "<table border='1' cellpadding='5' cellspacing='0'>";
    echo "<tr><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Clave</th><th>Valor por defecto</th><th>Extra</th></tr>";
    foreach ($columns as $column) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($column['Field']) . "</td>";
        echo "<td>" . htmlspecialchars($column['Type']) . "</td>";
        echo "<td>" . htmlspecialchars($column['Null']) . "</td>";
        echo "<td>" . htmlspecialchars($column['Key']) . "</td>";
        echo "<td>" . htmlspecialchars($column['Default'] ?? 'NULL') . "</td>";
        echo "<td>" . htmlspecialchars($column['Extra']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // 2. Verificar usuarios existentes
    echo "<h2>2. Usuarios en la base de datos:</h2>";
    $stmt = $db->query("SELECT id, usuario, correo_usuario, rol, estado FROM usuario");
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($usuarios) > 0) {
        echo "<table border='1' cellpadding='5' cellspacing='0'>";
        echo "<tr><th>ID</th><th>Usuario</th><th>Correo</th><th>Rol</th><th>Estado</th></tr>";
        foreach ($usuarios as $usuario) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($usuario['id']) . "</td>";
            echo "<td>" . htmlspecialchars($usuario['usuario']) . "</td>";
            echo "<td>" . htmlspecialchars($usuario['correo_usuario']) . "</td>";
            echo "<td>" . htmlspecialchars($usuario['rol']) . "</td>";
            echo "<td>" . ($usuario['estado'] ? 'Activo' : 'Inactivo') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>No se encontraron usuarios en la base de datos.</p>";
    }
    
    // 3. Probar la función de verificación de contraseña
    echo "<h2>3. Prueba de verificación de contraseña:</h2>";
    
    // Obtener un superadmin para probar
    $stmt = $db->prepare("SELECT id, usuario, clave FROM usuario WHERE rol = 'superadmin' LIMIT 1");
    $stmt->execute();
    $superadmin = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($superadmin) {
        echo "<p>Probando con el superadmin: " . htmlspecialchars($superadmin['usuario']) . " (ID: " . $superadmin['id'] . ")</p>";
        
        // Probar con contraseña correcta
        $stmt = $db->prepare("SELECT clave FROM usuario WHERE id = ?");
        $stmt->execute([$superadmin['id']]);
        $claveAlmacenada = $stmt->fetchColumn();
        
        echo "<p>Clave almacenada (hash): " . substr($claveAlmacenada, 0, 10) . "...</p>";
        
        // Probar verificación directa
        $claveCorrecta = false;
        if (password_verify('admin', $claveAlmacenada)) {
            $claveCorrecta = true;
            echo "<p style='color:green;'>✓ La contraseña 'admin' es correcta (verificación con password_verify)</p>";
        } else {
            echo "<p style='color:red;'>✗ La contraseña 'admin' NO es correcta (verificación con password_verify)</p>";
        }
        
        // Verificar si la contraseña está en texto plano
        if ($claveAlmacenada === 'admin') {
            echo "<p style='color:orange;'>⚠ La contraseña está almacenada en texto plano.</p>";
            
            // Actualizar a hash si es necesario
            if (isset($_GET['update_hash']) && $_GET['update_hash'] === 'yes') {
                $hash = password_hash('admin', PASSWORD_DEFAULT);
                $updateStmt = $db->prepare("UPDATE usuario SET clave = ? WHERE id = ?");
                $updateStmt->execute([$hash, $superadmin['id']]);
                echo "<p style='color:green;'>✓ Contraseña actualizada a hash: " . substr($hash, 0, 10) . "...</p>";
                echo "<p><a href='check_table.php'>Recargar página</a></p>";
            } else {
                echo "<p><a href='check_table.php?update_hash=yes' style='color:red;'>Haz clic aquí para actualizar la contraseña a un hash seguro</a></p>";
            }
        }
        
    } else {
        echo "<p style='color:red;'>No se encontró ningún superadministrador en la base de datos.</p>";
    }
    
} catch (PDOException $e) {
    echo "<h2 style='color:red;'>Error de conexión:</h2>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<h3>Detalles del error:</h3>";
    echo "<pre>";
    var_dump($e);
    echo "</pre>";
}
?>
