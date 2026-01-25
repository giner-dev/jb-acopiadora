<?php
require_once __DIR__ . '/../../core/Controller.php';
require_once __DIR__ . '/../services/AuthService.php';

class AuthController extends Controller {
    private $authService;
    
    public function __construct() {
        parent::__construct();
        $this->authService = new AuthService();
    }
    
    public function showLogin() {
        if (isAuthenticated()) {
            $this->redirect(url('dashboard'));
        }
        
        $expired = isset($_GET['expired']) && $_GET['expired'] == '1';
        
        $this->view->setLayout(null);
        $this->render('auth/login', [
            'expired' => $expired
        ]);
    }
    
    public function doLogin() {
        $this->validateMethod('POST');
        
        $username = $this->getPost('username');
        $password = $_POST['password'] ?? '';
        
        $result = $this->authService->login($username, $password);
        
        if ($result['success']) {
            $this->setSuccess($result['message']);
            
            if (isset($_SESSION['redirect_after_login'])) {
                $redirect = $_SESSION['redirect_after_login'];
                unset($_SESSION['redirect_after_login']);
                $this->redirect($redirect);
            }
            
            $this->redirect(url('dashboard'));
        } else {
            $this->setError($result['message']);
            $this->redirect(url('login'));
        }
    }
    
    public function logout() {
        $this->authService->logout();
        $this->redirect(url('login'));
    }
    
    public function showChangePassword() {
        $this->requireAuth();
        
        $this->render('auth/cambiar-password', [
            'title' => 'Cambiar Contraseña'
        ]);
    }
    
    public function doChangePassword() {
        $this->requireAuth();
        $this->validateMethod('POST');
        
        $token = $this->getPost('csrf_token');
        if (!verifyCsrfToken($token)) {
            $this->setError('Token de seguridad inválido');
            $this->redirect(url('cambiar-password'));
        }
        
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        $result = $this->authService->changePassword(
            authUserId(),
            $currentPassword,
            $newPassword,
            $confirmPassword
        );
        
        if ($result['success']) {
            $this->setSuccess($result['message']);
            $this->redirect(url('dashboard'));
        } else {
            $this->setError($result['message']);
            $this->redirect(url('cambiar-password'));
        }
    }
}