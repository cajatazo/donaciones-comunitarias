<?php
require_once __DIR__ . '/../models/Donacion.php';
require_once __DIR__ . '/../models/Necesidad.php';
require_once __DIR__ . '/../models/Usuario.php';

class DonacionController {
    public function crear() {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'donante') {
            header('Location: ' . BASE_URL . '/auth/login');
            exit();
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $necesidadId = $_POST['necesidad_id'];
            $cantidad = $_POST['cantidad'];
            $comentario = $_POST['comentario'] ?? null;
            
            $donacionModel = new Donacion();
            $necesidadModel = new Necesidad();
            
            // Crear la donación
            if ($donacionModel->crear($necesidadId, $_SESSION['user_id'], $cantidad, $comentario)) {
                // Actualizar la cantidad actual en la necesidad
                $necesidadModel->actualizarCantidad($necesidadId, $cantidad);
                
                $_SESSION['success'] = 'Donación registrada con éxito';
                header('Location: ' . BASE_URL . '/donante/donaciones');
                exit();
            } else {
                $_SESSION['error'] = 'Error al registrar la donación';
                header('Location: ' . BASE_URL . '/necesidades');
                exit();
            }
        }
    }
    
    public function listarDonante() {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'donante') {
            header('Location: ' . BASE_URL . '/auth/login');
            exit();
        }
        
        $donacionModel = new Donacion();
        $donaciones = $donacionModel->listarPorDonante($_SESSION['user_id']);
        
        require_once __DIR__ . '/../../frontend/views/donante/historial.php';
    }
    
    public function cambiarEstado() {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
            header('Location: ' . BASE_URL . '/auth/login');
            exit();
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $donacionId = $_POST['donacion_id'];
            $estado = $_POST['estado'];
            
            $donacionModel = new Donacion();
            
            if ($estado === 'entregada') {
                $fecha = $_POST['fecha_entrega'];
                $donacionModel->actualizarFechaEntrega($donacionId, $fecha);
            }
            
            if ($donacionModel->cambiarEstado($donacionId, $estado)) {
                $_SESSION['success'] = 'Estado de donación actualizado';
            } else {
                $_SESSION['error'] = 'Error al actualizar el estado';
            }
            
            header('Location: ' . BASE_URL . '/admin/donaciones');
            exit();
        }
    }
}
?>