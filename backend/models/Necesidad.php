<?php
require_once __DIR__ . '/../libs/Database.php';

class Necesidad {
    private $db;
    
    public function __construct() {
        $this->db = Database::getConnectionStatic();
    }
    
    public function crear($organizacionId, $titulo, $descripcion, $categoria, $cantidadNecesaria, $fechaLimite) {
        $stmt = $this->db->prepare("INSERT INTO necesidades (organizacion_id, titulo, descripcion, categoria, cantidad_necesaria, fecha_limite) VALUES (?, ?, ?, ?, ?, ?)");
        return $stmt->execute([$organizacionId, $titulo, $descripcion, $categoria, $cantidadNecesaria, $fechaLimite]);
    }
    
    public function obtenerPorId($id) {
        $stmt = $this->db->prepare("SELECT n.*, o.nombre as organizacion_nombre FROM necesidades n JOIN organizaciones o ON n.organizacion_id = o.id WHERE n.id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    public function listarPorOrganizacion($organizacionId) {
        $stmt = $this->db->prepare("SELECT * FROM necesidades WHERE organizacion_id = ? ORDER BY created_at DESC");
        $stmt->execute([$organizacionId]);
        return $stmt->fetchAll();
    }
    
    public function listarActivas($categoria = null) {
        $sql = "SELECT n.*, o.nombre as organizacion_nombre FROM necesidades n JOIN organizaciones o ON n.organizacion_id = o.id WHERE n.estado = 'activa' AND o.verificada = 1";
        
        if ($categoria) {
            $sql .= " AND n.categoria = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$categoria]);
        } else {
            $stmt = $this->db->query($sql);
        }
        
        return $stmt->fetchAll();
    }
    
    public function actualizar($id, $titulo, $descripcion, $categoria, $cantidadNecesaria, $fechaLimite, $estado) {
        $stmt = $this->db->prepare("UPDATE necesidades SET titulo = ?, descripcion = ?, categoria = ?, cantidad_necesaria = ?, fecha_limite = ?, estado = ? WHERE id = ?");
        return $stmt->execute([$titulo, $descripcion, $categoria, $cantidadNecesaria, $fechaLimite, $estado, $id]);
    }
    
    public function eliminar($id) {
        $stmt = $this->db->prepare("DELETE FROM necesidades WHERE id = ?");
        return $stmt->execute([$id]);
    }
    
    public function actualizarCantidad($id, $cantidad) {
        $stmt = $this->db->prepare("UPDATE necesidades SET cantidad_actual = cantidad_actual + ? WHERE id = ?");
        return $stmt->execute([$cantidad, $id]);
    }
    
    public function cambiarEstado($id, $estado) {
        $stmt = $this->db->prepare("UPDATE necesidades SET estado = ? WHERE id = ?");
        return $stmt->execute([$estado, $id]);
    }
    
    public function buscarPorId($id) {
        return $this->obtenerPorId($id);
    }
}
?>