<?php
require_once __DIR__ . '/../../core/Controller.php';

class DashboardController extends Controller {
    
    public function index() {
        $this->requireAuth();
        
        $this->render('dashboard/index', [
            'title' => 'Dashboard',
            'user_name' => authUserFullName(),
            'user_role' => authUserRole()
        ]);
    }
}