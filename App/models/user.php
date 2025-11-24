<?php
namespace App\Natys\Models;

use App\Natys\config\connect\Conexion;
use PDO;
use PDOException;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class user extends Conexion {
    public $id;
    public $correo_usuario;
    public $usuario;
    public $clave;
    public $rol;
    private $lastError;
    protected $conn;

    public function __construct() {
        parent::__construct();
        $this->conn = $this->getConnection();
    }

    public function getLastError() {
        return $this->lastError;
    }
    
    private function hashPassword($password) {
        return password_hash($password, PASSWORD_DEFAULT);
    }
    
    public function verificarClave($claveAlmacenada, $claveIngresada) {
        if (password_verify($claveIngresada, $claveAlmacenada)) {
            return true;
        }
        
        if ($claveAlmacenada === $claveIngresada) {
            $this->actualizarClaveAHash($claveIngresada);
            return true;
        }
        
        return false;
    }
    
    private function actualizarClaveAHash($clavePlana) {
        try {
            if (empty($this->id)) {
                return false;
            }
            
            $claveHash = $this->hashPassword($clavePlana);
            $query = "UPDATE usuario SET clave = :clave WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":clave", $claveHash, PDO::PARAM_STR);
            $stmt->bindParam(":id", $this->id, PDO::PARAM_INT);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error al actualizar contraseña a hash: " . $e->getMessage());
            return false;
        }
    }
    
    public function actualizarImagenPerfil($usuarioId, $rutaImagen) {
        try {
            $query = "UPDATE usuario SET imagen_perfil = :ruta_imagen WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":ruta_imagen", $rutaImagen, PDO::PARAM_STR);
            $stmt->bindParam(":id", $usuarioId, PDO::PARAM_INT);
            
            $result = $stmt->execute();
            
            if ($result && isset($_SESSION['usuario']) && $_SESSION['usuario']['id'] == $usuarioId) {
                $_SESSION['usuario']['imagen_perfil'] = $rutaImagen;
            }
            
            return $result;
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            error_log("Error la actualizar la imagen del perfil: " . $e->getMessage());
            return false;
        }
    }

    public function obtenerUsuarioPorId($id) {
        try {
            $query = "SELECT * FROM usuario WHERE id = :id LIMIT 1";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":id", $id, PDO::PARAM_INT);
            
            if ($stmt->execute()) {
                return $stmt->fetch(PDO::FETCH_ASSOC);
            }
            
            return false;
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            error_log("Error en obtenerUsuarioPorId: " . $e->getMessage());
            return false;
        }
    }

    public function validarUsuario() {
        try {
            $query = "SELECT id, usuario, clave, rol, correo_usuario, imagen_perfil 
                     FROM usuario 
                     WHERE usuario = :usuario AND estado = 1 
                     LIMIT 1";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":usuario", $this->usuario, PDO::PARAM_STR);
            
            if (!$stmt->execute()) {
                $this->lastError = implode(", ", $stmt->errorInfo());
                return false;
            }
            
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($usuario) {
                $this->id = $usuario['id'];
            }
            
            return $usuario;
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            error_log("Error en validarUsuario: " . $e->getMessage());
            return false;
        }
    }
    
    public function verificarSuperAdmin($password) {
        try {
            $query = "SELECT id, usuario, clave, rol FROM usuario WHERE rol = 'superadmin' LIMIT 1";
            $stmt = $this->conn->prepare($query);
            
            if (!$stmt->execute()) {
                $errorInfo = $stmt->errorInfo();
                $this->lastError = "Error en la consulta: " . ($errorInfo[2] ?? 'Error desconocido');
                return false;
            }
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$result) {
                $this->lastError = "No se encontró ningún usuario con rol 'superadmin'";
                return false;
            }
            
            $this->id = $result['id'];
            
            return $this->verificarClave($result['clave'], $password);
            
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            error_log("Error en verificarSuperAdmin: " . $e->getMessage());
            return false;
        }
    }
    
    public function obtenerClaveUsuario($userId, $superAdminPassword) {
        try {
            if (!$this->verificarSuperAdmin($superAdminPassword)) {
                $this->lastError = "Contraseña de super administrador incorrecta";
                return false;
            }
            
            $query = "SELECT id, usuario, clave, correo_usuario, rol FROM usuario WHERE id = :id LIMIT 1";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":id", $userId, PDO::PARAM_INT);
            
            if (!$stmt->execute()) {
                $this->lastError = "Error al obtener la información del usuario";
                return false;
            }
            
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$usuario) {
                $this->lastError = "Usuario no encontrado";
                return false;
            }
            
            return [
                'id' => $usuario['id'],
                'usuario' => $usuario['usuario'],
                'clave' => $usuario['clave'], 
                'correo' => $usuario['correo_usuario'],
                'rol' => $usuario['rol']
            ];
            
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            error_log("Error en obtenerClaveUsuario: " . $e->getMessage());
            return false;
        }
    }
    
    public function actualizarUltimoAcceso($usuarioId) {
        try {
            if (date_default_timezone_get() !== 'America/Caracas') {
                date_default_timezone_set('America/Caracas');
            }
            
            $fechaActual = date('Y-m-d H:i:s');
            $query = "UPDATE usuario SET ultimo_acceso = :fecha WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":fecha", $fechaActual, PDO::PARAM_STR);
            $stmt->bindParam(":id", $usuarioId, PDO::PARAM_INT);
            
            $result = $stmt->execute();
            
            if ($result && isset($_SESSION['usuario'])) {
                $_SESSION['usuario']['ultimo_acceso'] = $fechaActual;
            }
            
            return $result;
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            error_log("Error en actualizarUltimoAcceso: " . $e->getMessage());
            return false;
        }
    }

    public function obtenerUsuarioPorCorreo() {
        try {
            $query = "SELECT id, usuario, correo_usuario 
                     FROM usuario 
                     WHERE correo_usuario = :correo AND estado = 1 
                     LIMIT 1";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":correo", $this->correo_usuario, PDO::PARAM_STR);
            
            if (!$stmt->execute()) {
                $this->lastError = implode(", ", $stmt->errorInfo());
                return false;
            }
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            error_log("Error en obtenerUsuarioPorCorreo: " . $e->getMessage());
            return false;
        }
    }

    public function editarClave($nuevaClave) {
        try {
            $claveHash = $this->hashPassword($nuevaClave);
            
            $query = "UPDATE usuario 
                     SET clave = :clave 
                     WHERE correo_usuario = :correo AND estado = 1";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":clave", $claveHash, PDO::PARAM_STR);
            $stmt->bindParam(":correo", $this->correo_usuario, PDO::PARAM_STR);
            
            $result = $stmt->execute();
            
            if (!$result) {
                $this->lastError = implode(", ", $stmt->errorInfo());
            }
            
            return $result;
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            error_log("Error en editarClave: " . $e->getMessage());
            return false;
        }
    }

    public function listar($incluirInactivos = false) {
        try {
            $query = "SELECT id, correo_usuario, usuario, rol, imagen_perfil, estado FROM usuario";
            
            $conditions = [];
            if (!$incluirInactivos) {
                $conditions[] = "estado = 1";
            }
            
            if (isset($_SESSION['usuario']['rol']) && $_SESSION['usuario']['rol'] !== 'superadmin') {
                $conditions[] = "rol != 'superadmin'";
            }
            
            if (!empty($conditions)) {
                $query .= " WHERE " . implode(" AND ", $conditions);
            }
            
            $stmt = $this->conn->prepare($query);
            
            if (!$stmt->execute()) {
                $this->lastError = implode(", ", $stmt->errorInfo());
                return false;
            }
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            error_log("Error en listar: " . $e->getMessage());
            return false;
        }
    }

    public function obtenerPerfil($id) {
        try {
            $query = "SELECT id, correo_usuario, usuario, rol, imagen_perfil FROM usuario WHERE id = :id AND estado = 1 LIMIT 1";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":id", $id, PDO::PARAM_INT);
            
            if (!$stmt->execute()) {
                $this->lastError = implode(", ", $stmt->errorInfo());
                return false;
            }
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            error_log("Error en obtenerPerfil: " . $e->getMessage());
            return false;
        }
    }

    public function editar() {
        try {
            if (empty($this->id) || empty($this->correo_usuario) || empty($this->usuario) || empty($this->rol)) {
                $this->lastError = "Campos requeridos vacíos";
                return false;
            }

            if (!empty($this->clave)) {
                $claveHash = $this->hashPassword($this->clave);
                
                $query = "UPDATE usuario SET  
                          correo_usuario = :correo, 
                          usuario = :usuario, 
                          clave = :clave, 
                          rol = :rol 
                          WHERE id = :id AND estado = 1";
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(":clave", $claveHash, PDO::PARAM_STR);
            } else {
                $query = "UPDATE usuario SET 
                          correo_usuario = :correo, 
                          usuario = :usuario, 
                          rol = :rol 
                          WHERE id = :id AND estado = 1";
                $stmt = $this->conn->prepare($query);
            }

            $stmt->bindParam(":correo", $this->correo_usuario, PDO::PARAM_STR);
            $stmt->bindParam(":usuario", $this->usuario, PDO::PARAM_STR);
            $stmt->bindParam(":rol", $this->rol, PDO::PARAM_STR);
            $stmt->bindParam(":id", $this->id, PDO::PARAM_INT);
            
            $result = $stmt->execute();
            
            if (!$result) {
                $this->lastError = implode(", ", $stmt->errorInfo());
            }
            
            return $result;
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            error_log("Error en editar: " . $e->getMessage());
            return false;
        }
    }

    public function registrar() {
        try {
            if (empty($this->correo_usuario) || empty($this->usuario) || empty($this->clave) || empty($this->rol)) {
                $this->lastError = "Campos requeridos vacíos";
                return false;
            }

            $checkQuery = "SELECT id FROM usuario WHERE usuario = :usuario OR correo_usuario = :correo LIMIT 1";
            $checkStmt = $this->conn->prepare($checkQuery);
            $checkStmt->bindParam(":usuario", $this->usuario, PDO::PARAM_STR);
            $checkStmt->bindParam(":correo", $this->correo_usuario, PDO::PARAM_STR);
            
            if (!$checkStmt->execute()) {
                $this->lastError = implode(", ", $checkStmt->errorInfo());
                return false;
            }
            
            if ($checkStmt->fetch()) {
                $this->lastError = "Usuario o correo ya existen";
                return false;
            }

            $claveHash = $this->hashPassword($this->clave);

            $query = "INSERT INTO usuario (correo_usuario, usuario, clave, rol, estado, imagen_perfil) 
                     VALUES (:correo, :usuario, :clave, :rol, 1, '/Natys/Assets/img/defaultAvatar.jpg')";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":correo", $this->correo_usuario, PDO::PARAM_STR);
            $stmt->bindParam(":usuario", $this->usuario, PDO::PARAM_STR);
            $stmt->bindParam(":clave", $claveHash, PDO::PARAM_STR);
            $stmt->bindParam(":rol", $this->rol, PDO::PARAM_STR);
            
            $result = $stmt->execute();
            
            if (!$result) {
                $this->lastError = implode(", ", $stmt->errorInfo());
            }
            
            return $result;
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            error_log("Error en registrar: " . $e->getMessage());
            return false;
        }
    }

    public function eliminar($id) {
        try {
            if (empty($id)) {
                $this->lastError = "ID vacío";
                return false;
            }

            $query = "UPDATE usuario SET estado = 0 WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":id", $id, PDO::PARAM_INT);
            
            $result = $stmt->execute();
            
            if (!$result) {
                $this->lastError = implode(", ", $stmt->errorInfo());
            }
            
            return $result;
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            error_log("Error en eliminar: " . $e->getMessage());
            return false;
        }
    }

    public function cambiarClave($id, $claveActual, $nuevaClave) {
        try {
            $query = "SELECT id, clave FROM usuario WHERE id = :id AND estado = 1 LIMIT 1";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":id", $id, PDO::PARAM_INT);
            
            if (!$stmt->execute()) {
                $this->lastError = implode(", ", $stmt->errorInfo());
                return false;
            }
            
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$usuario) {
                $this->lastError = "Usuario no encontrado";
                return false;
            }
            
            $this->id = $usuario['id'];
            
            if (!$this->verificarClave($usuario['clave'], $claveActual)) {
                $this->lastError = "Contraseña actual incorrecta";
                return false;
            }

            $nuevaClaveHash = $this->hashPassword($nuevaClave);
            
            $query = "UPDATE usuario SET clave = :clave WHERE id = :id AND estado = 1";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":clave", $nuevaClaveHash, PDO::PARAM_STR);
            $stmt->bindParam(":id", $id, PDO::PARAM_INT);
            
            $result = $stmt->execute();
            
            if (!$result) {
                $this->lastError = implode(", ", $stmt->errorInfo());
            }
            
            return $result;
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            error_log("Error en cambiarClave: " . $e->getMessage());
            return false;
        }
    }
    
    public function generarCodigo() {
        $caracteres = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $codigo = '';
        for ($i = 0; $i < 6; $i++) {
            $codigo .= $caracteres[rand(0, strlen($caracteres) - 1)];
        }
        return $codigo;
    }
    
    public function enviarCodigo($correo, $codigo) {
        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'sluzzentesting@gmail.com';
            $mail->Password = 'xviv eiya gecm kbpn';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;
            $mail->CharSet = 'UTF-8';
            
            $mail->SMTPDebug = 0; 

            $mail->setFrom('sluzzentesting@gmail.com', 'Soporte Natys');
            $mail->addAddress($correo);
            $mail->Subject = 'Código de verificación Natys';
            $mail->isHTML(true);
            $mail->Body = '
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperación de Contraseña - Natys</title>
    <style>
        @import url("https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap");
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            margin: 0;
            padding: 0;
            font-family: "Inter", Arial, Helvetica, sans-serif;
            background-color: #f8f9fa;
            color: #333333;
            line-height: 1.6;
        }
        
        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        }
        
        .email-header {
            background: linear-gradient(rgba(204, 29, 29, 0.99), rgba(204, 29, 29, 0.65)), 
                        url("https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTdD8TwPCvDHGhQiVwENfLQ1Z7JFZn_SsKjlS0J60pHI_mhfO5VE88uoi_YFU2vi0rcAkc&usqp=CAU");
            background-size: cover;
            background-position: center;
            padding: 50px 30px;
            text-align: center;
            color: white;
            position: relative;
        }
        
        .company-logo {
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 8px;
            letter-spacing: -0.5px;
        }
        
        .company-tagline {
            font-size: 16px;
            font-weight: 300;
            opacity: 0.95;
            letter-spacing: 0.5px;
        }
        
        .email-body {
            padding: 40px 35px;
        }
        
        .section-title {
            font-size: 24px;
            font-weight: 600;
            color: #1a1a1a;
            margin-bottom: 20px;
            line-height: 1.3;
        }
        
        .description {
            color: #555555;
            font-size: 15px;
            margin-bottom: 30px;
        }
        
        .verification-code-container {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-left: 4px solid #cc1d1d;
            border-radius: 8px;
            padding: 25px;
            margin: 30px 0;
            text-align: center;
        }
        
        .code-label {
            font-size: 13px;
            color: #666666;
            text-transform: uppercase;
            letter-spacing: 1.2px;
            font-weight: 500;
            margin-bottom: 12px;
        }
        
        .verification-code {
            font-size: 38px;
            font-weight: 700;
            color: #cc1d1d;
            letter-spacing: 6px;
            font-family: "Courier New", monospace;
            padding: 5px;
            background: rgba(255, 255, 255, 0.7);
            border-radius: 6px;
            display: inline-block;
            min-width: 220px;
        }
        
        .info-box {
            background: #fff8f8;
            border-radius: 8px;
            padding: 22px;
            margin: 25px 0;
            border: 1px solid #ffeaea;
        }
        
        .info-title {
            color: #cc1d1d;
            font-size: 15px;
            font-weight: 600;
            margin-bottom: 12px;
        }
        
        .info-list {
            list-style: none;
            padding: 0;
        }
        
        .info-list li {
            padding: 6px 0;
            color: #666666;
            font-size: 14px;
            position: relative;
            padding-left: 20px;
        }
        
        .info-list li:before {
            content: "•";
            color: #cc1d1d;
            font-weight: bold;
            position: absolute;
            left: 0;
        }
        
        .footer-note {
            color: #777777;
            font-size: 14px;
            margin-top: 25px;
            line-height: 1.5;
        }
        
        .email-footer {
            background: #f8f9fa;
            padding: 30px;
            text-align: center;
            border-top: 1px solid #e9ecef;
        }
        
        .footer-text {
            color: #999999;
            font-size: 13px;
            margin-bottom: 8px;
        }
        
        .copyright {
            color: #999999;
            font-size: 12px;
        }
        
        @media (max-width: 600px) {
            .email-body {
                padding: 30px 25px;
            }
            
            .email-header {
                padding: 40px 25px;
            }
            
            .verification-code {
                font-size: 32px;
                letter-spacing: 4px;
                min-width: 200px;
            }
            
            .section-title {
                font-size: 22px;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="email-header">
            <div class="company-logo">NATYS</div>
            <div class="company-tagline">Sistema de Gestión Empresarial</div>
        </div>
        
        <div class="email-body">
            <h2 class="section-title">Recuperación de Contraseña</h2>
            
            <p class="description">
                Has solicitado restablecer tu contraseña en nuestro sistema. 
                Utiliza el siguiente código de verificación para continuar con el proceso de recuperación:
            </p>
            
            <div class="verification-code-container">
                <div class="code-label">Código de Verificación</div>
                <div class="verification-code">'.$codigo.'</div>
            </div>
            
            <div class="info-box">
                <div class="info-title">Información Importante</div>
                <ul class="info-list">
                    <li>Este código es válido por <strong>15 minutos</strong></li>
                    <li>No compartas este código con nadie por seguridad</li>
                    <li>Si no solicitaste este cambio, ignora este mensaje</li>
                    <li>Para mayor seguridad, recomendamos cambiar tu contraseña regularmente</li>
                </ul>
            </div>
            
            <p class="footer-note">
                Si tienes alguna pregunta o necesitas asistencia adicional, 
                no dudes en contactar a nuestro equipo de soporte.
            </p>
        </div>
        
        <div class="email-footer">
            <p class="footer-text">
                Este es un correo automático, por favor no responder directamente.
            </p>
            <p class="copyright">
                © '.date('Y').' Natys - Todos los derechos reservados.
            </p>
        </div>
    </div>
</body>
</html>
';
            $mail->AltBody = "Recuperación de contraseña - Natys\n\n"
                           . "Tu código de verificación es: " . $codigo . "\n\n"
                           . "Este código es válido por 15 minutos.\n"
                           . "No lo compartas con nadie.\n\n"
                           . "Si no solicitaste este cambio, ignora este mensaje.";

            $resultado = $mail->send();
            
            if ($resultado) {
                error_log("Correo enviado exitosamente a: {$correo}");
            } else {
                error_log("Fallo al enviar correo a: {$correo}");
            }
            
            return $resultado;
        } catch (Exception $e) {
            error_log("Error PHPMailer al enviar a {$correo}: {$e->getMessage()}");
            return false;
        }
    }
}