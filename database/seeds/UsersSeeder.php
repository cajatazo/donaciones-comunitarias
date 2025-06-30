<?php
function run() {
    $sql = "INSERT INTO usuarios (nombre, email, password, tipo, estado) VALUES 
        ('Admin', 'admin@donaciones.com', '" . password_hash('admin123', PASSWORD_BCRYPT) . "', 'admin', 1),
        ('Donante Ejemplo', 'donante@ejemplo.com', '" . password_hash('donante123', PASSWORD_BCRYPT) . "', 'donante', 1),
        ('Organización Ejemplo', 'org@ejemplo.com', '" . password_hash('org123', PASSWORD_BCRYPT) . "', 'organizacion', 1)";
    
    return $sql;
}
?>