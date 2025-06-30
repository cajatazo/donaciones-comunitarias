<?php
require_once __DIR__ . '/../models/Necesidad.php';
require_once __DIR__ . '/../models/Organizacion.php';

class NecesidadController {
    public function listarPublicas() {
        $necesidadModel = new Necesidad();
        $categoria = isset($_GET['categoria']) ? $_GET['categoria'] : null;
        $necesidades = $necesidadModel->listarActivas($categoria);
        
        require_once __DIR__ . '/../../frontend/views/necesidades.php';
    }
    
    public function listarOrganizacion() {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'organizacion') {
            header('Location: ' . BASE_URL . '/auth/login');
            exit();
        }
        
        $orgModel = new Organizacion();
        $org = $orgModel->obtenerPorUsuarioId($_SESSION['user_id']);
        
        if (!$org || !$org['verificada']) {
            $_SESSION['error'] = 'Su organización no está verificada o no existe';
            header('Location: ' . BASE_URL . '/organizacion/dashboard');
            exit();
        }
        
        $necesidadModel = new Necesidad();
        $necesidades = $necesidadModel->listarPorOrganizacion($org['id']);
        
        require_once __DIR__ . '/../../frontend/views/organizacion/necesidades.php';
    }
    
    public function crear() {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'organizacion') {
            header('Location: ' . BASE_URL . '/auth/login');
            exit();
        }
        
        $orgModel = new Organizacion();
        $org = $orgModel->obtenerPorUsuarioId($_SESSION['user_id']);
        
        if (!$org || !$org['verificada']) {
            $_SESSION['error'] = 'Su organización no está verificada o no existe';
            header('Location: ' . BASE_URL . '/organizacion/dashboard');
            exit();
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $titulo = $_POST['titulo'];
            $descripcion = $_POST['descripcion'];
            $categoria = $_POST['categoria'];
            $cantidadNecesaria = $_POST['cantidad_necesaria'];
            $fechaLimite = $_POST['fecha_limite'];
            
            $necesidadModel = new Necesidad();
            
            if ($necesidadModel->crear($org['id'], $titulo, $descripcion, $categoria, $cantidadNecesaria, $fechaLimite)) {
                $_SESSION['success'] = 'Necesidad publicada con éxito';
                header('Location: ' . BASE_URL . '/organizacion/necesidades');
                exit();
            } else {
                $_SESSION['error'] = 'Error al publicar la necesidad';
                header('Location: ' . BASE_URL . '/organizacion/necesidades/crear');
                exit();
            }
        }
        
        require_once __DIR__ . '/../../frontend/views/organizacion/necesidades_crear.php';
    }
    
    public function actualizar() {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'organizacion') {
            header('Location: ' . BASE_URL . '/auth/login');
            exit();
        }
        
        $orgModel = new Organizacion();
        $org = $orgModel->obtenerPorUsuarioId($_SESSION['user_id']);
        
        if (!$org || !$org['verificada']) {
            $_SESSION['error'] = 'Su organización no está verificada o no existe';
            header('Location: ' . BASE_URL . '/organizacion/dashboard');
            exit();
        }
        
        $necesidadId = $_GET['id'] ?? $_POST['id'];
        $necesidadModel = new Necesidad();
        $necesidad = $necesidadModel->obtenerPorId($necesidadId);
        
        if (!$necesidad || $necesidad['organizacion_id'] != $org['id']) {
            $_SESSION['error'] = 'Necesidad no encontrada o no pertenece a su organización';
            header('Location: ' . BASE_URL . '/organizacion/necesidades');
            exit();
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $titulo = $_POST['titulo'];
            $descripcion = $_POST['descripcion'];
            $categoria = $_POST['categoria'];
            $cantidadNecesaria = $_POST['cantidad_necesaria'];
            $fechaLimite = $_POST['fecha_limite'];
            $estado = $_POST['estado'];
            
            if ($necesidadModel->actualizar($necesidadId, $titulo, $descripcion, $categoria, $cantidadNecesaria, $fechaLimite, $estado)) {
                $_SESSION['success'] = 'Necesidad actualizada con éxito';
                header('Location: ' . BASE_URL . '/organizacion/necesidades');
                exit();
            } else {
                $_SESSION['error'] = 'Error al actualizar la necesidad';
                header('Location: ' . BASE_URL . '/organizacion/necesidades/editar?id=' . $necesidadId);
                exit();
            }
        }
        
        require_once __DIR__ . '/../../frontend/views/organizacion/necesidades_editar.php';
    }
    
    public function eliminar() {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'organizacion') {
            header('Location: ' . BASE_URL . '/auth/login');
            exit();
        }
        
        $orgModel = new Organizacion();
        $org = $orgModel->obtenerPorUsuarioId($_SESSION['user_id']);
        
        if (!$org || !$org['verificada']) {
            $_SESSION['error'] = 'Su organización no está verificada o no existe';
            header('Location: ' . BASE_URL . '/organizacion/dashboard');
            exit();
        }
        
        $necesidadId = $_GET['id'];
        $necesidadModel = new Necesidad();
        $necesidad = $necesidadModel->obtenerPorId($necesidadId);
        
        if (!$necesidad || $necesidad['organizacion_id'] != $org['id']) {
            $_SESSION['error'] = 'Necesidad no encontrada o no pertenece a su organización';
            header('Location: ' . BASE_URL . '/organizacion/necesidades');
            exit();
        }
        
        if ($necesidadModel->eliminar($necesidadId)) {
            $_SESSION['success'] = 'Necesidad eliminada con éxito';
        } else {
            $_SESSION['error'] = 'Error al eliminar la necesidad';
        }
        
        header('Location: ' . BASE_URL . '/organizacion/necesidades');
        exit();
    }
}
?>