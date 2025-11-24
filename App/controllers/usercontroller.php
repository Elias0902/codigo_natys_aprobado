<?php
use App\Natys\Models\User;
use App\Natys\Helpers\FileUploadTrait;
use App\Natys\Helpers\RegexValidationTrait;
use App\Natys\Helpers\ReportePDF;

require 'vendor/autoload.php';

session_start();

$user = new User();

class UserControllerHelper {
    use FileUploadTrait, RegexValidationTrait;
}

$uploadHelper = new UserControllerHelper();
$validationHelper = new UserControllerHelper();

if (isset($_GET['type'])) {
    if ($_GET['type'] == 'login') {
        function verificarAutenticacion() {
            if (!isset($_SESSION['usuario'])) {
                if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                    header('Content-Type: application/json');
                    echo json_encode([
                        'success' => false, 
                        'message' => 'Debes iniciar sesión para acceder a esta página',
                        'redirect' => 'index.php?url=user&type=login'
                    ]);
                    exit;
                } else {
                    $_SESSION['error_login'] = 'Debes iniciar sesión para acceder a esta página';
                    header('Location: index.php?url=user&type=login');
                    exit;
                }
            }
        }

        $action = $_REQUEST['action'] ?? 'mostrarFormulario';

        switch ($action) {
            case 'autenticar':
                if (empty($_POST['usuario']) || empty($_POST['clave'])) {
                    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                        header('Content-Type: application/json');
                        echo json_encode([
                            'success' => false, 
                            'message' => 'Usuario y contraseña son requeridos'
                        ]);
                        exit;
                    } else {
                        $_SESSION['error_login'] = 'Usuario y contraseña son requeridos';
                        header('Location: index.php?url=user&type=login');
                        exit;
                    }
                }

                $user->usuario = $_POST['usuario'];
                $user->clave = $_POST['clave'];
                $usuario = $user->validarUsuario();

                if ($usuario && $user->verificarClave($usuario['clave'], $user->clave)) {
                    $user->actualizarUltimoAcceso($usuario['id']);
                    
                    $_SESSION['usuario'] = [
                        'id' => $usuario['id'],
                        'usuario' => $usuario['usuario'],
                        'correo' => $usuario['correo_usuario'],
                        'rol' => $usuario['rol'],
                        'imagen_perfil' => $usuario['imagen_perfil'] ?? '/Natys/Assets/img/defaultAvatar.jpg',
                        'ultimo_acceso' => date('Y-m-d H:i:s')
                    ];
                    $_SESSION['last_activity'] = time();

                    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                        header('Content-Type: application/json');
                        echo json_encode([
                            'success' => true, 
                            'message' => 'Inicio de sesión exitoso',
                            'redirect' => 'index.php?url=home'
                        ]);
                    } else {
                        header('Location: index.php?url=home');
                    }
                    exit;
                } else {
                    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                        header('Content-Type: application/json');
                        echo json_encode([
                            'success' => false, 
                            'message' => 'Usuario o contraseña incorrectos'
                        ]);
                    } else {
                        $_SESSION['error_login'] = 'Usuario o contraseña incorrectos';
                        header('Location: index.php?url=user&type=login&action=mostrarFormulario');
                    }
                    exit;
                }
                break;

            case 'subirImagen':
                header('Content-Type: application/json');
                
                try {
                    if (empty($_FILES['imagen_perfil'])) {
                        throw new Exception('No se ha seleccionado ninguna imagen');
                    }
                    
                    $imagen = $_FILES['imagen_perfil'];
                    $resultado = $uploadHelper->uploadImageWithValidation($imagen, 'profile');
                    
                    if (!$resultado['success']) {
                        throw new Exception($resultado['message']);
                    }
                    
                    $usuarioId = $_SESSION['usuario']['id'];
                    
                    if ($user->actualizarImagenPerfil($usuarioId, $resultado['ruta'])) {
                        $_SESSION['usuario']['imagen_perfil'] = $resultado['ruta'];
                        
                        echo json_encode([
                            'success' => true,
                            'message' => 'Imagen de perfil actualizada correctamente',
                            'imagen_url' => $resultado['ruta'] . '?t=' . time()
                        ]);
                    } else {
                        throw new Exception('Error al actualizar la imagen en la base de datos');
                    }
                    
                } catch (Exception $e) {
                    error_log("Error en subirImagen: " . $e->getMessage());
                    http_response_code(400);
                    echo json_encode([
                        'success' => false,
                        'message' => $e->getMessage()
                    ]);
                }
                break;

            case 'eliminarImagen':
                header('Content-Type: application/json');
                
                try {
                    $usuarioId = $_SESSION['usuario']['id'];
                    $imagenDefault = '/Natys/Assets/img/defaultAvatar.jpg';
                    
                    $usuarioActual = $user->obtenerUsuarioPorId($usuarioId);
                    $imagenActual = $usuarioActual['imagen_perfil'] ?? '';
                    
                    if (!empty($imagenActual) && $imagenActual !== $imagenDefault) {
                        $uploadHelper->deleteFile($imagenActual);
                    }
                    
                    $resultado = $user->actualizarImagenPerfil($usuarioId, $imagenDefault);
                    
                    if ($resultado === false) {
                        throw new Exception($user->getLastError() ?: 'Error al eliminar la imagen de perfil');
                    }
                    
                    $_SESSION['usuario']['imagen_perfil'] = $imagenDefault;
                    
                    echo json_encode([
                        'success' => true,
                        'message' => 'Imagen de perfil eliminada correctamente',
                        'imagen_url' => $imagenDefault . '?t=' . time()
                    ]);
                    
                } catch (Exception $e) {
                    error_log("Error en eliminarImagen: " . $e->getMessage());
                    http_response_code(400);
                    echo json_encode([
                        'success' => false,
                        'message' => $e->getMessage()
                    ]);
                }
                break;

            case 'mostrarFormulario':
                include 'app/views/login/formulario.php';
                break;

            case 'mostrarRecuperar':
                include 'app/views/login/recuperar.php';
                break;

            case 'solicitarRecuperacion':
                header('Content-Type: application/json');
                
                if (empty($_POST['correo'])) {
                    echo json_encode([
                        'success' => false, 
                        'message' => 'El correo electrónico es requerido'
                    ]);
                    exit;
                }

                if (!$validationHelper->validarEmail($_POST['correo'])) {
                    echo json_encode([
                        'success' => false, 
                        'message' => 'El correo electrónico no tiene un formato válido'
                    ]);
                    exit;
                }

                $user->correo_usuario = $_POST['correo'];
                $usuario = $user->obtenerUsuarioPorCorreo();

                if ($usuario) {
                    $codigo = $user->generarCodigo();
                    
                    $_SESSION['codigo_recuperacion'] = [
                        'codigo' => $codigo,
                        'correo' => $user->correo_usuario,
                        'timestamp' => time(),
                        'intentos' => 0
                    ];
                    
                    if ($user->enviarCodigo($user->correo_usuario, $codigo)) {
                        echo json_encode([
                            'success' => true, 
                            'message' => 'Código enviado. Revisa tu correo electrónico.'
                        ]);
                    } else {
                        echo json_encode([
                            'success' => false, 
                            'message' => 'Error al enviar el código. Contacta al soporte técnico.'
                        ]);
                    }
                } else {
                    echo json_encode([
                        'success' => false, 
                        'message' => 'Correo electrónico no registrado'
                    ]);
                }
                break;

            case 'verificarCodigo':
                header('Content-Type: application/json');
                
                if (empty($_POST['correo']) || empty($_POST['codigo'])) {
                    echo json_encode([
                        'success' => false, 
                        'message' => 'Todos los campos son requeridos'
                    ]);
                    exit;
                }

                if (!isset($_SESSION['codigo_recuperacion'])) {
                    echo json_encode([
                        'success' => false, 
                        'message' => 'No hay una solicitud de recuperación activa'
                    ]);
                    exit;
                }

                $codigoSesion = $_SESSION['codigo_recuperacion'];
                
                if ($codigoSesion['intentos'] >= 3) {
                    unset($_SESSION['codigo_recuperacion']);
                    echo json_encode([
                        'success' => false, 
                        'message' => 'Demasiados intentos fallidos. Solicita un nuevo código.'
                    ]);
                    exit;
                }

                if (time() - $codigoSesion['timestamp'] > 900) {
                    unset($_SESSION['codigo_recuperacion']);
                    echo json_encode([
                        'success' => false, 
                        'message' => 'El código ha expirado. Solicita uno nuevo.'
                    ]);
                    exit;
                }

                if ($codigoSesion['correo'] !== $_POST['correo']) {
                    echo json_encode([
                        'success' => false, 
                        'message' => 'Correo electrónico no coincide'
                    ]);
                    exit;
                }

                if ($codigoSesion['codigo'] !== $_POST['codigo']) {
                    $_SESSION['codigo_recuperacion']['intentos']++;
                    echo json_encode([
                        'success' => false, 
                        'message' => 'Código incorrecto. Intentos restantes: '.(3 - $_SESSION['codigo_recuperacion']['intentos'])
                    ]);
                    exit;
                }

                $_SESSION['codigo_recuperacion']['verificado'] = true;
                
                echo json_encode([
                    'success' => true, 
                    'message' => 'Código verificado. Ahora puedes cambiar tu contraseña.'
                ]);
                break;

            case 'reenviarCodigo':
                header('Content-Type: application/json');
                
                if (empty($_POST['correo'])) {
                    echo json_encode([
                        'success' => false, 
                        'message' => 'El correo electrónico es requerido'
                    ]);
                    exit;
                }

                if (!isset($_SESSION['codigo_recuperacion']) || $_SESSION['codigo_recuperacion']['correo'] !== $_POST['correo']) {
                    echo json_encode([
                        'success' => false, 
                        'message' => 'No hay una solicitud de recuperación activa para este correo'
                    ]);
                    exit;
                }

                $nuevoCodigo = $user->generarCodigo();
                
                $_SESSION['codigo_recuperacion'] = [
                    'codigo' => $nuevoCodigo,
                    'correo' => $_POST['correo'],
                    'timestamp' => time(),
                    'intentos' => 0
                ];
                
                if ($user->enviarCodigo($_POST['correo'], $nuevoCodigo)) {
                    echo json_encode([
                        'success' => true, 
                        'message' => 'Nuevo código enviado a tu correo.'
                    ]);
                } else {
                    echo json_encode([
                        'success' => false, 
                        'message' => 'Error al reenviar el código. Intenta más tarde.'
                    ]);
                }
                break;

            case 'cambiarClave':
                header('Content-Type: application/json');
                
                if (empty($_POST['correo']) || empty($_POST['clave']) || empty($_POST['confirmar_clave'])) {
                    echo json_encode([
                        'success' => false, 
                        'message' => 'Todos los campos son requeridos'
                    ]);
                    exit;
                }

                if (!isset($_SESSION['codigo_recuperacion']['verificado']) || !$_SESSION['codigo_recuperacion']['verificado'] || 
                    $_SESSION['codigo_recuperacion']['correo'] !== $_POST['correo']) {
                    echo json_encode([
                        'success' => false, 
                        'message' => 'Debes verificar tu identidad primero'
                    ]);
                    exit;
                }

                if ($_POST['clave'] !== $_POST['confirmar_clave']) {
                    echo json_encode([
                        'success' => false, 
                        'message' => 'Las contraseñas no coinciden'
                    ]);
                    exit;
                }

                if (strlen($_POST['clave']) < 8) {
                    echo json_encode([
                        'success' => false, 
                        'message' => 'La contraseña debe tener al menos 8 caracteres'
                    ]);
                    exit;
                }

                $user->correo_usuario = $_POST['correo'];
                $resultado = $user->editarClave($_POST['clave']);

                if ($resultado) {
                    unset($_SESSION['codigo_recuperacion']);
                    
                    echo json_encode([
                        'success' => true, 
                        'message' => 'Contraseña actualizada correctamente',
                        'redirect' => 'index.php?url=user&type=login'
                    ]);
                } else {
                    echo json_encode([
                        'success' => false, 
                        'message' => 'Error al editar la contraseña: ' . $user->getLastError()
                    ]);
                }
                break;

            case 'cerrarSesion':
                session_destroy();
                header('Location: index.php?url=user&type=login');
                exit;
                break;

            default:
                verificarAutenticacion();
                include 'app/views/login/formulario.php';
                break;
        }
    }
}

if (isset($_GET['type']) && $_GET['type'] == 'verificarSuperAdmin') {
    header('Content-Type: application/json');
    
    try {
        if (!isset($_POST['password']) || empty(trim($_POST['password']))) {
            throw new Exception('No se proporcionó la contraseña');
        }
        
        $password = trim($_POST['password']);
        $user = new User();
        
        error_log("Iniciando verificación de superadmin");
        
        if ($user->verificarSuperAdmin($password)) {
            error_log("Verificación exitosa de superadmin");
            echo json_encode([
                'success' => true,
                'message' => 'Verificación exitosa'
            ]);
        } else {
            $errorMsg = $user->getLastError() ?: 'La contraseña proporcionada no es correcta';
            error_log("Error en verificación de superadmin: " . $errorMsg);
            
            echo json_encode([
                'success' => false, 
                'message' => $errorMsg
            ]);
        }
    } catch (Exception $e) {
        $errorMsg = 'Error en el servidor: ' . $e->getMessage();
        error_log("Excepción en verificarSuperAdmin: " . $e->getMessage());
        
        echo json_encode([
            'success' => false, 
            'message' => $errorMsg
        ]);
    }
    exit;
}

if (isset($_GET['type']) && $_GET['type'] == 'obtenerClave') {
    header('Content-Type: application/json');
    
    try {
        $userId = $_POST['id'] ?? 0;
        $superAdminPassword = $_POST['superadmin_password'] ?? '';
        
        if (!$userId || !$superAdminPassword) {
            throw new Exception('Parámetros incompletos');
        }
        
        $user = new User();
        $result = $user->obtenerClaveUsuario($userId, $superAdminPassword);
        
        if ($result === false) {
            echo json_encode([
                'success' => false,
                'message' => $user->getLastError() ?: 'Error al obtener la clave del usuario'
            ]);
        } else {
            echo json_encode([
                'success' => true,
                'clave' => $result['clave'],
                'usuario' => $result['usuario']
            ]);
        }
    } catch (Exception $e) {
        error_log("Error en obtenerClave: " . $e->getMessage());
        echo json_encode([
            'success' => false, 
            'message' => 'Error al obtener la clave del usuario: ' . $e->getMessage()
        ]);
    }
    exit;
}

if (isset($_GET['type']) && $_GET['type'] == 'perfil') {
    if (!isset($_SESSION['usuario'])) {
        $_SESSION['error_login'] = 'Debes iniciar sesión para acceder a esta página';
        header('Location: index.php?url=user&type=login');
        exit;
    }

    $action = $_REQUEST['action'] ?? 'miperfil';

    switch ($action) {
        case 'subirImagen':
            header('Content-Type: application/json');
            
            try {
                if (!isset($_FILES['imagen']) || $_FILES['imagen']['error'] !== UPLOAD_ERR_OK) {
                    throw new Exception('No se ha seleccionado ninguna imagen o hubo un error en la carga.');
                }
                
                $resultado = $uploadHelper->uploadImageWithValidation($_FILES['imagen'], 'profile');
                
                if (!$resultado['success']) {
                    throw new Exception($resultado['message']);
                }
                
                $usuarioId = $_SESSION['usuario']['id'];
                
                $usuarioActual = $user->obtenerUsuarioPorId($usuarioId);
                $imagenActual = $usuarioActual['imagen_perfil'] ?? '';
                $imagenDefault = '/Natys/Assets/img/defaultAvatar.jpg';
                
                if (!empty($imagenActual) && $imagenActual !== $imagenDefault) {
                    $uploadHelper->deleteFile($imagenActual);
                }
                
                if ($user->actualizarImagenPerfil($usuarioId, $resultado['ruta'])) {
                    $_SESSION['usuario']['imagen_perfil'] = $resultado['ruta'];
                    
                    echo json_encode([
                        'success' => true,
                        'message' => 'Imagen de perfil actualizada correctamente.',
                        'imagen_url' => $resultado['ruta'] . '?t=' . time()
                    ]);
                } else {
                    $uploadHelper->deleteFile($resultado['ruta']);
                    throw new Exception('Error al actualizar la imagen en la base de datos.');
                }
            } catch (Exception $e) {
                echo json_encode([
                    'success' => false,
                    'message' => $e->getMessage()
                ]);
            }
            exit;
            break;
            
        case 'eliminarImagenPerfil':
            header('Content-Type: application/json');
            
            try {
                $usuarioId = $_SESSION['usuario']['id'];
                $imagenDefault = '/Natys/Assets/img/defaultAvatar.jpg';
                
                $usuarioActual = $user->obtenerUsuarioPorId($usuarioId);
                $imagenActual = $usuarioActual['imagen_perfil'] ?? '';
                
                if (!empty($imagenActual) && $imagenActual !== $imagenDefault) {
                    $uploadHelper->deleteFile($imagenActual);
                }
                
                if ($user->actualizarImagenPerfil($usuarioId, $imagenDefault)) {
                    $_SESSION['usuario']['imagen_perfil'] = $imagenDefault;
                    
                    echo json_encode([
                        'success' => true,
                        'message' => 'Imagen de perfil restablecida correctamente.',
                        'imagen_url' => $imagenDefault . '?t=' . time()
                    ]);
                } else {
                    throw new Exception('No se pudo eliminar la imagen de perfil: ' . $user->getLastError());
                }
            } catch (Exception $e) {
                echo json_encode([
                    'success' => false,
                    'message' => $e->getMessage()
                ]);
            }
            exit;
            break;
            
        case 'miperfil':
            include 'app/views/perfil/perfil.php';
            break;
            
        case 'cambiarClave':
            header('Content-Type: application/json');
            
            if (empty($_POST['clave_actual']) || empty($_POST['nueva_clave']) || empty($_POST['confirmar_clave'])) {
                echo json_encode(['success' => false, 'message' => 'Todos los campos son requeridos']);
                exit;
            }
            
            if ($_POST['nueva_clave'] !== $_POST['confirmar_clave']) {
                echo json_encode(['success' => false, 'message' => 'Las nuevas contraseñas no coinciden']);
                exit;
            }
            
            $resultado = $user->cambiarClave(
                $_SESSION['usuario']['id'],
                $_POST['clave_actual'],
                $_POST['nueva_clave']
            );
            
            if ($resultado) {
                echo json_encode(['success' => true, 'message' => 'Contraseña actualizada correctamente']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error: ' . $user->getLastError()]);
            }
            break;
            
        case 'formEditar':
            header('Content-Type: application/json');
            
            try {
                if (empty($_GET['id'])) {
                    throw new Exception('Falta el ID del usuario');
                }
                
                $id = (int)$_GET['id'];
                $esSuperAdmin = ($_SESSION['usuario']['rol'] === 'superadmin');
                $esPropioUsuario = ($_SESSION['usuario']['id'] == $id);
                
                if (!$esSuperAdmin && !$esPropioUsuario) {
                    throw new Exception('No tienes permisos para ver este perfil');
                }
                
                $datos = $user->obtenerPerfil($id);
                
                if (!$datos) {
                    throw new Exception('Usuario no encontrado');
                }
                
                if (!$esSuperAdmin && $datos['rol'] === 'superadmin') {
                    throw new Exception('No tienes permisos para ver este perfil');
                }
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Datos del perfil cargados',
                    'data' => [
                        'id' => $datos['id'],
                        'correo_usuario' => $datos['correo_usuario'],
                        'usuario' => $datos['usuario'],
                        'rol' => $datos['rol']
                    ]
                ]);
                
            } catch (Exception $e) {
                error_log("Error en formEditar: " . $e->getMessage());
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => $e->getMessage()
                ]);
            }
            exit();
            break;

        case 'editar':
            header('Content-Type: application/json');
            
            try {
                $required = ['id', 'correo_usuario', 'usuario', 'rol'];
                $missing = [];
                
                foreach ($required as $field) {
                    if (empty($_POST[$field])) {
                        $missing[] = $field;
                    }
                }
                
                if (!empty($missing)) {
                    throw new Exception('Faltan los siguientes campos requeridos: ' . implode(', ', $missing));
                }
                
                if (!$validationHelper->validarEmail($_POST['correo_usuario'])) {
                    throw new Exception('El correo electrónico no tiene un formato válido');
                }
                
                if (!$validationHelper->validarUsuario($_POST['usuario'])) {
                    throw new Exception('El nombre de usuario solo puede contener letras, números y guiones bajos, y debe tener entre 3 y 20 caracteres');
                }
                
                $id = (int)$_POST['id'];
                $esSuperAdmin = $_SESSION['usuario']['rol'] === 'superadmin';
                $esPropioUsuario = ($_SESSION['usuario']['id'] == $id);
                
                if (!$esSuperAdmin && !$esPropioUsuario) {
                    throw new Exception('No tienes permisos para editar este usuario');
                }
                
                $usuarioActual = $user->obtenerPerfil($id);
                if (!$usuarioActual) {
                    throw new Exception('El usuario no existe');
                }
                
                $rolesPermitidos = ['vendedor', 'admin'];
                if ($esSuperAdmin) {
                    $rolesPermitidos[] = 'superadmin';
                }
                
                if (!in_array($_POST['rol'], $rolesPermitidos)) {
                    throw new Exception('Rol no válido');
                }
                
                if (!$esSuperAdmin && $usuarioActual['rol'] === 'superadmin') {
                    throw new Exception('No tienes permisos para editar a un superadministrador');
                }
                
                if ($esPropioUsuario && $_POST['rol'] !== $usuarioActual['rol']) {
                    throw new Exception('No puedes cambiar tu propio rol');
                }
                
                $user->id = $id;
                $user->correo_usuario = trim($_POST['correo_usuario']);
                $user->usuario = trim($_POST['usuario']);
                $user->rol = $_POST['rol'];
                
                if (!empty($_POST['clave'])) {
                    if (strlen($_POST['clave']) < 6) {
                        throw new Exception('La contraseña debe tener al menos 6 caracteres');
                    }
                    $user->clave = $_POST['clave'];
                }
                
                $resultado = $user->editar();
                
                if ($resultado === false) {
                    throw new Exception($user->getLastError() ?: 'Error al actualizar el usuario');
                }
                
                if ($esPropioUsuario) {
                    $_SESSION['usuario']['usuario'] = $user->usuario;
                    $_SESSION['usuario']['correo'] = $user->correo_usuario;
                    $_SESSION['usuario']['rol'] = $user->rol;
                }
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Usuario actualizado exitosamente',
                    'data' => [
                        'id' => $user->id,
                        'usuario' => $user->usuario,
                        'correo_usuario' => $user->correo_usuario,
                        'rol' => $user->rol
                    ]
                ]);
                
            } catch (Exception $e) {
                error_log("Error en editar: " . $e->getMessage());
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => $e->getMessage()
                ]);
            }
            break;

        case 'registrar':
            header('Content-Type: application/json');
            
            try {
                error_log("=== INICIO REGISTRO DE USUARIO ===");
                error_log("POST recibido: " . print_r($_POST, true));
                error_log("Rol del usuario en sesión: " . ($_SESSION['usuario']['rol'] ?? 'NO DEFINIDO'));
                
                if (!isset($_SESSION['usuario']['rol']) || $_SESSION['usuario']['rol'] !== 'superadmin') {
                    throw new Exception('No tienes permisos para registrar usuarios. Solo los super administradores pueden realizar esta acción.');
                }
                
                $required = ['correo_usuario', 'usuario', 'rol', 'clave'];
                $missing = [];
                
                foreach ($required as $field) {
                    if (empty($_POST[$field])) {
                        $missing[] = $field;
                    }
                }
                
                if (!empty($missing)) {
                    error_log("Campos faltantes: " . implode(', ', $missing));
                    throw new Exception('Faltan los siguientes campos requeridos: ' . implode(', ', $missing));
                }

                if (!$validationHelper->validarEmail($_POST['correo_usuario'])) {
                    throw new Exception('El correo electrónico no tiene un formato válido');
                }
                
                if (!$validationHelper->validarUsuario($_POST['usuario'])) {
                    throw new Exception('El nombre de usuario solo puede contener letras, números y guiones bajos, y debe tener entre 3 y 20 caracteres');
                }
                
                if (strlen($_POST['clave']) < 6) {
                    throw new Exception('La contraseña debe tener al menos 6 caracteres');
                }

                $rolesPermitidos = ['vendedor', 'admin'];
                if ($_SESSION['usuario']['rol'] === 'superadmin') {
                    $rolesPermitidos[] = 'superadmin';
                }
                
                if (!in_array($_POST['rol'], $rolesPermitidos)) {
                    throw new Exception('Rol no válido');
                }

                $user->correo_usuario = trim($_POST['correo_usuario']);
                $user->usuario = trim($_POST['usuario']);
                $user->rol = $_POST['rol'];
                $user->clave = $_POST['clave'];
                
                error_log("Datos a registrar - Usuario: " . $user->usuario . ", Correo: " . $user->correo_usuario . ", Rol: " . $user->rol);
                
                $resultado = $user->registrar();
                
                error_log("Resultado del registro: " . ($resultado ? 'EXITOSO' : 'FALLIDO'));
                
                if ($resultado === false) {
                    $errorMsg = $user->getLastError() ?: 'Error al registrar el usuario';
                    error_log("Error en registrar(): " . $errorMsg);
                    throw new Exception($errorMsg);
                }

                echo json_encode([
                    'success' => true,
                    'message' => 'Usuario registrado exitosamente',
                    'data' => [
                        'usuario' => $user->usuario,
                        'correo' => $user->correo_usuario,
                        'rol' => $user->rol
                    ]
                ]);
                exit;
            } catch (Exception $e) {
                error_log("Error en registrar: " . $e->getMessage());
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => $e->getMessage()
                ]);
                exit;
            }
            break;

        case 'eliminar':
            header('Content-Type: application/json');
            
            try {
                if (!in_array($_SESSION['usuario']['rol'], ['superadmin', 'admin'])) {
                    throw new Exception('No tienes permisos para realizar esta acción');
                }

                if (empty($_GET['id'])) {
                    throw new Exception('Falta el ID del usuario');
                }

                $id = (int)$_GET['id'];
                
                if ($_SESSION['usuario']['id'] == $id) {
                    throw new Exception('No puedes eliminar tu propio usuario');
                }
                
                $usuarioAEliminar = $user->obtenerPerfil($id);
                if (!$usuarioAEliminar) {
                    throw new Exception('El usuario no existe');
                }
                
                if ($usuarioAEliminar['rol'] === 'superadmin' && $_SESSION['usuario']['rol'] !== 'superadmin') {
                    throw new Exception('No tienes permisos para eliminar a un superadministrador');
                }

                $resultado = $user->eliminar($id);
                
                if ($resultado === false) {
                    throw new Exception($user->getLastError() ?: 'Error al eliminar el usuario');
                }

                echo json_encode([
                    'success' => true,
                    'message' => 'Usuario eliminado correctamente',
                    'data' => [
                        'id' => $id
                    ]
                ]);
                
            } catch (Exception $e) {
                error_log("Error en eliminar: " . $e->getMessage());
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => $e->getMessage()
                ]);
            }
            break;

        case 'listar':
            if (!in_array($_SESSION['usuario']['rol'], ['admin', 'superadmin'])) {
                if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                    header('Content-Type: application/json');
                    http_response_code(403);
                    echo json_encode([
                        'success' => false,
                        'message' => 'No tienes permisos para ver la lista de usuarios'
                    ]);
                } else {
                    header('Location: index.php?url=user&type=perfil');
                }
                exit;
            }
            
            try {
                $perfiles = $user->listar();
                
                if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                    header('Content-Type: application/json');
                    
                    if ($perfiles === false) {
                        throw new Exception('Error al cargar la lista de usuarios: ' . $user->getLastError());
                    }
                    
                    echo json_encode([
                        'success' => true,
                        'data' => $perfiles
                    ]);
                } else {
                    include 'app/views/perfil/perfilView.php';
                }
            } catch (Exception $e) {
                error_log("Error en listar: " . $e->getMessage());
                
                if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                    header('Content-Type: application/json');
                    http_response_code(500);
                    echo json_encode([
                        'success' => false,
                        'message' => $e->getMessage()
                    ]);
                } else {
                    $_SESSION['error'] = $e->getMessage();
                    include 'app/views/perfil/perfilView.php';
                }
            }
            break;
            
        default:
            header('Location: index.php?url=user&type=perfil');
            break;
    }
}

if (isset($_GET['type']) && $_GET['type'] == 'reporte') {
    if (!isset($_SESSION['usuario'])) {
        $_SESSION['error_login'] = 'Debes iniciar sesión para acceder a esta página';
        header('Location: index.php?url=user&type=login');
        exit;
    }

    if (!in_array($_SESSION['usuario']['rol'], ['admin', 'superadmin'])) {
        $_SESSION['error'] = 'No tienes permisos para generar reportes';
        header('Location: index.php?url=user&type=perfil');
        exit;
    }

    $action = $_REQUEST['action'] ?? 'gestion';

    switch ($action) {
        case 'gestion':
            $pdf = new ReportePDF();
            $pdf->setTitulo('GESTIÓN DE USUARIOS');
            $pdf->setSubtitulo('Informe detallado de usuarios del sistema');
            $pdf->AddPage();
            
            $usuarios = $user->listar();
            
            $totalUsuarios = count($usuarios);
    $administradores = 0;
    $superadministradores = 0; 
    $empleados = 0;
    $activos = 0;
    $inactivos = 0;
    
    foreach ($usuarios as $usuario) {
        if ($usuario['rol'] == 'admin' || $usuario['rol'] == 'superadmin') {
            $administradores++;
            
            if ($usuario['rol'] == 'superadmin') {
                $superadministradores++;
            }
        } else {
            $empleados++;
        }
        
        if ($usuario['estado'] == 1) {
            $activos++;
        } else {
            $inactivos++;
        }
    }
    
    $pdf->agregarResumen([
        'Total de Usuarios' => $totalUsuarios,
        'Administradores' => $administradores . ' (' . round(($administradores / $totalUsuarios) * 100, 1) . '%)',
        'Empleados' => $empleados . ' (' . round(($empleados / $totalUsuarios) * 100, 1) . '%)',
        'Usuarios Activos' => $activos . ' (' . round(($activos / $totalUsuarios) * 100, 1) . '%)',
        'Fecha del Reporte' => date('d/m/Y H:i:s')
    ]);
            
            $pdf->agregarSeccion('Detalle de Usuarios');
            
            $headers = ['ID', 'Usuario', 'Correo', 'Rol', 'Estado'];
            $widths = [15, 40, 80, 30, 25];
            $data = [];
            
            foreach ($usuarios as $usuario) {
                $data[] = [
                    $usuario['id'],
                    $usuario['usuario'],
                    $usuario['correo_usuario'],
                    ucfirst($usuario['rol']),
                    $usuario['estado'] == 1 ? 'Activo' : 'Inactivo'
                ];
            }
            
            $pdf->crearTabla($headers, $data, $widths);
            
            $pdf->agregarSeccion('Distribución por Rol');
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(0, 10, 'Administradores: ' . $administradores . ' usuarios (' . round(($administradores / $totalUsuarios) * 100, 1) . '%)', 0, 1);
    $pdf->Cell(0, 10, ' - Administradores: ' . ($administradores - $superadministradores) . ' usuarios (' . round((($administradores - $superadministradores) / $totalUsuarios) * 100, 1) . '%)', 0, 1);
    $pdf->Cell(0, 10, 'Empleados: ' . $empleados . ' usuarios (' . round(($empleados / $totalUsuarios) * 100, 1) . '%)', 0, 1);

            $pdf->Ln(5);
            $pdf->SetFont('Arial', 'I', 8);
            $pdf->Cell(0, 5, 'Este reporte fue generado el ' . date('d/m/Y H:i:s'), 0, 1, 'R');
            
            $pdf->Output('I', 'Gestion_Usuarios_' . date('Y-m-d') . '.pdf');
            break;
            
        case 'administradores':
            $pdf = new ReportePDF();
            $pdf->setTitulo('USUARIOS ADMINISTRADORES');
            $pdf->setSubtitulo('Listado de usuarios con rol de administrador');
            $pdf->AddPage();
            
            $usuarios = $user->listar();
            
            $administradores = array_filter($usuarios, function($u) {
                return $u['rol'] == 'admin' || $u['rol'] == 'superadmin';;
            });
            
            $pdf->agregarResumen([
                'Total de Administradores' => count($administradores)
            ]);
            
            if (count($administradores) > 0) {
                $headers = ['id', 'usuario', 'Correo Usario', 'rol', 'estado'];
                $widths = [35, 60, 60, 35];
                $data = [];
                
                foreach ($administradores as $admin) {
                    $data[] = [
                        $admin['id'],
                        $admin['usuario'],
                        $admin['correo_usuario'],
                        $admin['rol'],
                        $admin['estado'] == 1 ? 'Activo' : 'Inactivo'
                    ];
                }
                
                $pdf->crearTabla($headers, $data, $widths);
            } else {
                $pdf->SetFont('Arial', 'I', 10);
                $pdf->Cell(0, 10, 'No hay administradores registrados', 0, 1, 'C');
            }
            
            $pdf->Output('I', 'Administradores_' . date('Y-m-d') . '.pdf');
            break;
            
        case 'empleados':
            $pdf = new ReportePDF();
            $pdf->setTitulo('USUARIOS EMPLEADOS');
            $pdf->setSubtitulo('Listado de usuarios con rol de empleado');
            $pdf->AddPage();
            
            $usuarios = $user->listar();
            
            $empleados = array_filter($usuarios, function($u) {
                return $u['rol'] == 'vendedor';
            });
            
            $pdf->agregarResumen([
                'Total de Empleados' => count($empleados)
            ]);
            
            if (count($empleados) > 0) {
                $headers = ['id','usuario', 'correo_usuario', 'estado'];
                $widths = [35, 60, 60, 35];
                $data = [];
                
                foreach ($empleados as $empleado) {
                    $data[] = [
                        $empleado['id'],
                        $empleado['usuario'],
                        $empleado['correo_usuario'],
                        $empleado['estado'] == 1 ? 'Activo' : 'Inactivo'
                    ];
                }
                
                $pdf->crearTabla($headers, $data, $widths);
            } else {
                $pdf->SetFont('Arial', 'I', 10);
                $pdf->Cell(0, 10, 'No hay empleados registrados', 0, 1, 'C');
            }
            
            $pdf->Output('I', 'Empleados_' . date('Y-m-d') . '.pdf');
            break;
            
        default:
            header('HTTP/1.1 400 Bad Request');
            echo json_encode(['error' => 'Acción no válida']);
            break;
    }
}