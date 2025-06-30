<?php
require_once __DIR__ . '/../libs/Database.php';

class Donacion {
    private $db;
    
    public function __construct() {
        $this->db = Database::getConnectionStatic();
    }
    
    public function crear($necesidadId, $donanteId, $cantidad, $comentario = null) {
        $stmt = $this->db->prepare("INSERT INTO donaciones (necesidad_id, donante_id, cantidad, comentario) VALUES (?, ?, ?, ?)");
        return $stmt->execute([$necesidadId, $donanteId, $cantidad, $comentario]);
    }
    
    public function obtenerPorId($id) {
        $stmt = $this->db->prepare("SELECT d.*, n.titulo as necesidad_titulo, u.nombre as donante_nombre FROM donaciones d JOIN necesidades n ON d.necesidad_id = n.id JOIN usuarios u ON d.donante_id = u.id WHERE d.id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    public function listarPorDonante($donanteId) {
        $stmt = $this->db->prepare("SELECT d.*, n.titulo as necesidad_titulo, o.nombre as organizacion_nombre FROM donaciones d JOIN necesidades n ON d.necesidad_id = n.id JOIN organizaciones o ON n.organizacion_id = o.id WHERE d.donante_id = ? ORDER BY d.created_at DESC");
        $stmt->execute([$donanteId]);
        return $stmt->fetchAll();
    }
    
    public function listarPorNecesidad($necesidadId) {
        $stmt = $this->db->prepare("SELECT d.*, u.nombre as donante_nombre FROM donaciones d JOIN usuarios u ON d.donante_id = u.id WHERE d.necesidad_id = ? ORDER BY d.created_at DESC");
        $stmt->execute([$necesidadId]);
        return $stmt->fetchAll();
    }
    
    public function listarTodas() {
        $stmt = $this->db->query("SELECT d.*, n.titulo as necesidad_titulo, o.nombre as organizacion_nombre, u.nombre as donante_nombre FROM donaciones d JOIN necesidades n ON d.necesidad_id = n.id JOIN organizaciones o ON n.organizacion_id = o.id JOIN usuarios u ON d.donante_id = u.id ORDER BY d.created_at DESC");
        return $stmt->fetchAll();
    }
    
    public function cambiarEstado($id, $estado) {
        $stmt = $this->db->prepare("UPDATE donaciones SET estado = ? WHERE id = ?");
        return $stmt->execute([$estado, $id]);
    }
    
    public function actualizarFechaEntrega($id, $fecha) {
        $stmt = $this->db->prepare("UPDATE donaciones SET fecha_entrega = ? WHERE id = ?");
        return $stmt->execute([$fecha, $id]);
    }
    
    public function listarPorOrganizacion($organizacionId) {
        $stmt = $this->db->prepare("SELECT d.*, n.titulo as necesidad_titulo, n.categoria, u.nombre as donante_nombre FROM donaciones d JOIN necesidades n ON d.necesidad_id = n.id JOIN usuarios u ON d.donante_id = u.id WHERE n.organizacion_id = ? ORDER BY d.created_at DESC");
        $stmt->execute([$organizacionId]);
        return $stmt->fetchAll();
    }
    
    public function actualizarGestion($id, $estado, $observacion = null, $fechaEntrega = null) {
        $sql = "UPDATE donaciones SET estado = ?, observacion = ?";
        $params = [$estado, $observacion, $id];
        if ($fechaEntrega) {
            $sql .= ", fecha_entrega = ?";
            $params = [$estado, $observacion, $fechaEntrega, $id];
        }
        $sql .= " WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }
}
?>