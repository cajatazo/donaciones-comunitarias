<?php
require_once __DIR__ . '/../libs/Database.php';

class Usuario {
    private $db;
    
    public function __construct() {
        $this->db = Database::getConnectionStatic();
    }
    
    public function registrar($nombre, $email, $password, $tipo) {
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $this->db->prepare("INSERT INTO usuarios (nombre, email, password, tipo) VALUES (?, ?, ?, ?)");
        return $stmt->execute([$nombre, $email, $hashedPassword, $tipo]);
    }
    
    public function login($email, $password) {
        $stmt = $this->db->prepare("SELECT * FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            return $user;
        }
        return false;
    }
    
    public function obtenerPorId($id) {
        $stmt = $this->db->prepare("SELECT * FROM usuarios WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    public function actualizar($id, $nombre, $email) {
        $stmt = $this->db->prepare("UPDATE usuarios SET nombre = ?, email = ? WHERE id = ?");
        return $stmt->execute([$nombre, $email, $id]);
    }
    
    public function cambiarEstado($id, $estado) {
        $stmt = $this->db->prepare("UPDATE usuarios SET estado = ? WHERE id = ?");
        return $stmt->execute([$estado, $id]);
    }
    
    public function listar($tipo = null) {
        if ($tipo) {
            $stmt = $this->db->prepare("SELECT * FROM usuarios WHERE tipo = ?");
            $stmt->execute([$tipo]);
        } else {
            $stmt = $this->db->query("SELECT * FROM usuarios");
        }
        return $stmt->fetchAll();
    }
    
    public function obtenerPorEmail($email) {
        $stmt = $this->db->prepare("SELECT * FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch();
    }
}
?>