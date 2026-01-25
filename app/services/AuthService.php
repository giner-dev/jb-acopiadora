<?php
require_once __DIR__ . '/../repositories/UsuarioRepository.php';

class AuthService {
    private $usuarioRepository;
    
    public function __construct() {
        $this->usuarioRepository = new UsuarioRepository();
    }
    
    public function login($username, $password) {
        if (empty($username) || empty($password)) {
            return [
                'success' => false,
                'message' => 'Usuario y contraseña son requeridos'
            ];
        }
        
        $usuario = $this->usuarioRepository->findByUsername($username);
        
        if (!$usuario) {
            logMessage("Intento de login fallido para usuario: {$username}", 'warning');
            return [
                'success' => false,
                'message' => 'Credenciales incorrectas'
            ];
        }
        
        if (!$usuario->verificarPassword($password)) {
            logMessage("Contraseña incorrecta para usuario: {$username}", 'warning');
            return [
                'success' => false,
                'message' => 'Credenciales incorrectas'
            ];
        }
        
        $this->usuarioRepository->updateLastAccess($usuario->id_usuario);
        
        $this->setUserSession($usuario);
        
        logMessage("Login exitoso para usuario: {$username}", 'info');
        
        return [
            'success' => true,
            'message' => 'Bienvenido ' . $usuario->nombres,
            'usuario' => $usuario
        ];
    }
    
    public function logout() {
        $userId = authUserId();
        
        if ($userId) {
            logMessage("Logout de usuario ID: {$userId}", 'info');
        }
        
        session_unset();
        session_destroy();
        
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
    
    public function changePassword($userId, $currentPassword, $newPassword, $confirmPassword) {
        if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
            return [
                'success' => false,
                'message' => 'Todos los campos son requeridos'
            ];
        }
        
        if ($newPassword !== $confirmPassword) {
            return [
                'success' => false,
                'message' => 'Las contraseñas nuevas no coinciden'
            ];
        }
        
        if (strlen($newPassword) < 6) {
            return [
                'success' => false,
                'message' => 'La contraseña debe tener al menos 6 caracteres'
            ];
        }
        
        $usuario = $this->usuarioRepository->findById($userId);
        
        if (!$usuario) {
            return [
                'success' => false,
                'message' => 'Usuario no encontrado'
            ];
        }
        
        if (!$usuario->verificarPassword($currentPassword)) {
            return [
                'success' => false,
                'message' => 'La contraseña actual es incorrecta'
            ];
        }
        
        $hashedPassword = Usuario::hashPassword($newPassword);
        
        $this->usuarioRepository->updatePassword($userId, $hashedPassword);
        
        logMessage("Cambio de contraseña exitoso para usuario ID: {$userId}", 'info');
        
        return [
            'success' => true,
            'message' => 'Contraseña cambiada exitosamente'
        ];
    }
    
    private function setUserSession($usuario) {
        $_SESSION['usuario_id'] = $usuario->id_usuario;
        $_SESSION['usuario_nombre'] = $usuario->nombre_usuario;
        $_SESSION['usuario_nombre_completo'] = $usuario->getNombreCompleto();
        $_SESSION['usuario_rol'] = $usuario->rol_nombre;
        $_SESSION['usuario_rol_id'] = $usuario->rol_id;
        $_SESSION['LAST_ACTIVITY'] = time();
        
        session_regenerate_id(true);
    }
}