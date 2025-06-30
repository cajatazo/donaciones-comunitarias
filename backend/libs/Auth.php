<?php
require_once __DIR__ . '/../models/Usuario.php';
require_once __DIR__ . '/../models/Organizacion.php';

class Auth {
    public static function check() {
        return isset($_SESSION['user_id']);
    }
    
    public static function user() {
        if (self::check()) {
            $usuarioModel = new Usuario();
            return $usuarioModel->obtenerPorId($_SESSION['user_id']);
        }
        return null;
    }
    
    public static function isAdmin() {
        return self::check() && $_SESSION['user_type'] === 'admin';
    }
    
    public static function isDonante() {
        return self::check() && $_SESSION['user_type'] === 'donante';
    }
    
    public static function isOrganizacion() {
        return self::check() && $_SESSION['user_type'] === 'organizacion';
    }
    
    public static function organizacion() {
        if (self::isOrganizacion()) {
            $orgModel = new Organizacion();
            return $orgModel->obtenerPorUsuarioId($_SESSION['user_id']);
        }
        return null;
    }
    
    public static function redirectIfNotAuthenticated($type = null) {
        if (!self::check()) {
            header('Location: ' . BASE_URL . '/auth/login');
            exit();
        }
        
        if ($type === 'admin' && !self::isAdmin()) {
            header('Location: ' . BASE_URL . '/');
            exit();
        }
        
        if ($type === 'donante' && !self::isDonante()) {
            header('Location: ' . BASE_URL . '/');
            exit();
        }
        
        if ($type === 'organizacion' && !self::isOrganizacion()) {
            header('Location: ' . BASE_URL . '/');
            exit();
        }
    }
}
?>