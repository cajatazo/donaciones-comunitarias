<?php
require_once __DIR__ . '/../libs/Database.php';

class Organizacion {
    private $db;
    
    public function __construct() {
        $this->db = Database::getConnectionStatic();
    }
    
    public function registrar($usuarioId, $nombre, $ruc, $direccion, $telefono, $descripcion) {
        $stmt = $this->db->prepare("INSERT INTO organizaciones (usuario_id, nombre, ruc, direccion, telefono, descripcion) VALUES (?, ?, ?, ?, ?, ?)");
        return $stmt->execute([$usuarioId, $nombre, $ruc, $direccion, $telefono, $descripcion]);
    }
    
    public function obtenerPorUsuarioId($usuarioId) {
        $stmt = $this->db->prepare("SELECT * FROM organizaciones WHERE usuario_id = ?");
        $stmt->execute([$usuarioId]);
        return $stmt->fetch();
    }
    
    public function obtenerPorId($id) {
        $stmt = $this->db->prepare("SELECT o.*, u.email FROM organizaciones o JOIN usuarios u ON o.usuario_id = u.id WHERE o.id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    public function listar($verificadas = null) {
        if ($verificadas !== null) {
            $stmt = $this->db->prepare("SELECT o.*, u.email FROM organizaciones o JOIN usuarios u ON o.usuario_id = u.id WHERE o.verificada = ?");
            $stmt->execute([$verificadas]);
        } else {
            $stmt = $this->db->query("SELECT o.*, u.email FROM organizaciones o JOIN usuarios u ON o.usuario_id = u.id");
        }
        return $stmt->fetchAll();
    }
    
    public function verificar($id, $estado) {
        $stmt = $this->db->prepare("UPDATE organizaciones SET verificada = ? WHERE id = ?");
        return $stmt->execute([$estado, $id]);
    }
    
    public function actualizar($id, $nombre, $ruc, $direccion, $telefono, $descripcion) {
        $stmt = $this->db->prepare("UPDATE organizaciones SET nombre = ?, ruc = ?, direccion = ?, telefono = ?, descripcion = ? WHERE id = ?");
        return $stmt->execute([$nombre, $ruc, $direccion, $telefono, $descripcion, $id]);
    }
}
?>