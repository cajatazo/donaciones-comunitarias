<?php
require_once __DIR__ . '/../models/Organizacion.php';
require_once __DIR__ . '/../models/Usuario.php';

class OrganizacionController {
    public function listar() {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
            header('Location: ' . BASE_URL . '/auth/login');
            exit();
        }
        
        $orgModel = new Organizacion();
        $estado = isset($_GET['estado']) ? $_GET['estado'] : null;
        $organizaciones = $orgModel->listar($estado);
        
        require_once __DIR__ . '/../../frontend/views/admin/organizaciones.php';
        header('Location: ' . BASE_URL . '/frontend/views/admin/organizaciones.php');
    }
    
    public function verificar() {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
            header('Location: ' . BASE_URL . '/auth/login');
            exit();
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $orgId = $_POST['org_id'];
            $estado = $_POST['estado'];
            
            $orgModel = new Organizacion();
            
            if ($orgModel->verificar($orgId, $estado)) {
                // Si se verifica, también activar el usuario
                $org = $orgModel->obtenerPorId($orgId);
                $usuarioModel = new Usuario();
                $usuarioModel->cambiarEstado($org['usuario_id'], 1);
                
                $_SESSION['success'] = 'Estado de organización actualizado';
            } else {
                $_SESSION['error'] = 'Error al actualizar el estado';
            }
            
            header('Location: ' . BASE_URL . '/admin/organizaciones');
            exit();
        }
    }
    
    public function perfil() {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'organizacion') {
            header('Location: ' . BASE_URL . '/auth/login');
            exit();
        }
        
        $orgModel = new Organizacion();
        $org = $orgModel->obtenerPorUsuarioId($_SESSION['user_id']);
        
        if (!$org) {
            $_SESSION['error'] = 'Organización no encontrada';
            header('Location: ' . BASE_URL . '/organizacion/dashboard');
            exit();
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nombre = $_POST['nombre'];
            $ruc = $_POST['ruc'];
            $direccion = $_POST['direccion'];
            $telefono = $_POST['telefono'];
            $descripcion = $_POST['descripcion'];
            
            if ($orgModel->actualizar($org['id'], $nombre, $ruc, $direccion, $telefono, $descripcion)) {
                $_SESSION['success'] = 'Perfil actualizado con éxito';
                header('Location: ' . BASE_URL . '/organizacion/perfil');
                exit();
            } else {
                $_SESSION['error'] = 'Error al actualizar el perfil';
            }
        }
        
        require_once __DIR__ . '/../../frontend/views/organizacion/perfil.php';
    }
}
?>