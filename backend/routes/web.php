<?php
require_once __DIR__ . '/../controllers/AuthController.php';
require_once __DIR__ . '/../controllers/DonacionController.php';
require_once __DIR__ . '/../controllers/NecesidadController.php';
require_once __DIR__ . '/../controllers/OrganizacionController.php';
require_once __DIR__ . '/../controllers/ReporteController.php';

// Rutas públicas
$routes = [
    '/' => function() {
        require_once __DIR__ . '/../../frontend/views/home.php';
    },
    '/auth/login' => function() {
        $authController = new AuthController();
        $authController->login();
    },
    '/auth/register' => function() {
        $authController = new AuthController();
        $authController->register();
    },
    '/auth/logout' => function() {
        $authController = new AuthController();
        $authController->logout();
    },
    '/necesidades' => function() {
        $necesidadController = new NecesidadController();
        $necesidadController->listarPublicas();
    },
    
    // Rutas de donante
    '/donante/dashboard' => function() {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'donante') {
            header('Location: ' . BASE_URL . '/auth/login');
            exit();
        }
        require_once __DIR__ . '/../../frontend/views/donante/dashboard.php';
    },
    '/donante/donaciones' => function() {
        $donacionController = new DonacionController();
        $donacionController->listarDonante();
    },
    '/donante/donar' => function() {
        $donacionController = new DonacionController();
        $donacionController->crear();
    },
    
    // Rutas de organización
    '/organizacion/dashboard' => function() {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'organizacion') {
            header('Location: ' . BASE_URL . '/auth/login');
            exit();
        }
        require_once __DIR__ . '/../../frontend/views/organizacion/dashboard.php';
    },
    '/organizacion/necesidades' => function() {
        $necesidadController = new NecesidadController();
        $necesidadController->listarOrganizacion();
    },
    '/organizacion/necesidades/crear' => function() {
        $necesidadController = new NecesidadController();
        $necesidadController->crear();
    },
    '/organizacion/necesidades/editar' => function() {
        $necesidadController = new NecesidadController();
        $necesidadController->actualizar();
    },
    '/organizacion/necesidades/eliminar' => function() {
        $necesidadController = new NecesidadController();
        $necesidadController->eliminar();
    },
    '/organizacion/perfil' => function() {
        $orgController = new OrganizacionController();
        $orgController->perfil();
    },
    
    // Rutas de administrador
    '/admin/dashboard' => function() {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
            header('Location: ' . BASE_URL . '/auth/login');
            exit();
        }
        require_once __DIR__ . '/../../frontend/views/admin/dashboard.php';
    },
    '/admin/organizaciones' => function() {
        require_once __DIR__ . '/../frontend/views/admin/organizaciones.php';
    },
    '/admin/organizaciones/verificar' => function() {
        require_once __DIR__ . '/../frontend/views/admin/organizaciones_verificar.php';
    },
    '/admin/donaciones' => function() {
        $donacionController = new DonacionController();
        $donacionController->listarTodas();
    },
    '/admin/donaciones/estado' => function() {
        $donacionController = new DonacionController();
        $donacionController->cambiarEstado();
    },
    '/admin/reportes' => function() {
        $reporteController = new ReporteController();
        $reporteController->generar();
    },
    '/admin/reportes/exportar' => function() {
        $reporteController = new ReporteController();
        $reporteController->exportarPDF();
    }
];

// Manejo de rutas
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path = str_replace(BASE_URL, '', $path);
$path = rtrim($path, '/');

// Si no existe, prueba agregando o quitando el slash final
if (!array_key_exists($path, $routes)) {
    $altPath = $path === '' ? '/' : $path . '/';
    if (array_key_exists($altPath, $routes)) {
        $path = $altPath;
    }
}

if (array_key_exists($path, $routes)) {
    $routes[$path]();
} else {
    http_response_code(404);
    require_once __DIR__ . '/../../frontend/views/404.php';
}
?>