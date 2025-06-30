<?php
require_once __DIR__ . '/../../config.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../models/Usuario.php';
require_once __DIR__ . '/../models/Organizacion.php';
require_once __DIR__ . '/../libs/Database.php';

class AuthController {
    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'];
            $password = $_POST['password'];
            
            $usuarioModel = new Usuario();
            $user = $usuarioModel->login($email, $password);
            
            if ($user) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_type'] = $user['tipo'];
                $_SESSION['user_name'] = $user['nombre'];
                
                // Redirigir según el tipo de usuario
                switch ($user['tipo']) {
                    case 'admin':
                        header('Location: ' . BASE_URL . '/frontend/views/admin/dashboard.php');
                        break;
                    case 'donante':
                        header('Location: ' . BASE_URL . '/frontend/views/donante/dashboard.php');
                        break;
                    case 'organizacion':
                        // Verificar si la organización está verificada
                        $orgModel = new Organizacion();
                        $org = $orgModel->obtenerPorUsuarioId($user['id']);
                        $_SESSION['org_verified'] = $org ? $org['verificada'] : 0;
                        
                        header('Location: ' . BASE_URL . '/frontend/views/organizacion/dashboard.php');
                        break;
                }
                exit();
            } else {
                $_SESSION['error'] = 'Credenciales incorrectas';
                header('Location: ' . BASE_URL . '/frontend/views/auth/login.php');
                exit();
            }
        }
        
        // Mostrar vista de login
        require_once __DIR__ . '/../../frontend/views/auth/login.php';
    }
    
    public function register() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nombre = $_POST['nombre'];
            $email = $_POST['email'];
            $password = $_POST['password'];
            $confirmPassword = $_POST['confirm_password'];
            $tipo = $_POST['tipo'];
            
            // Validaciones básicas
            if ($password !== $confirmPassword) {
                $_SESSION['error'] = 'Las contraseñas no coinciden';
                header('Location: ' . BASE_URL . '/frontend/views/auth/register.php');
                exit();
            }
            
            $usuarioModel = new Usuario();
            
            // Registrar usuario
            if ($usuarioModel->registrar($nombre, $email, $password, $tipo)) {
                // Si es organización, registrar también en la tabla organizaciones
                if ($tipo === 'organizacion') {
                    $orgModel = new Organizacion();
                    $userId = $usuarioModel->getConnection()->lastInsertId();
                    
                    $orgModel->registrar(
                        $userId,
                        $_POST['org_nombre'],
                        $_POST['org_ruc'],
                        $_POST['org_direccion'],
                        $_POST['org_telefono'],
                        $_POST['org_descripcion']
                    );
                }
                
                $_SESSION['success'] = 'Registro exitoso. Por favor inicia sesión.';
                header('Location: ' . BASE_URL . '/frontend/views/auth/login.php');
                exit();
            } else {
                $_SESSION['error'] = 'Error al registrar el usuario';
                header('Location: ' . BASE_URL . '/frontend/views/auth/register.php');
                exit();
            }
        }
        
        // Mostrar vista de registro
        require_once __DIR__ . '/../../frontend/views/auth/register.php';
    }
    
    public function logout() {
        session_unset();
        session_destroy();
        header('Location: ' . BASE_URL . '/');
        exit();
    }
}

if (isset($_GET['action'])) {
    $controller = new AuthController();
    switch ($_GET['action']) {
        case 'login':
            $controller->login();
            break;
        case 'register':
            $controller->register();
            break;
        case 'logout':
            $controller->logout();
            break;
        default:
            http_response_code(404);
            echo 'Acción no encontrada';
            break;
    }
} else {
    http_response_code(404);
    echo 'Acción no especificada';
}
?>