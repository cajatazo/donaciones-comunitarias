<?php
function up() {
    $sql = "CREATE TABLE necesidades (
        id INT AUTO_INCREMENT PRIMARY KEY,
        organizacion_id INT NOT NULL,
        titulo VARCHAR(100) NOT NULL,
        descripcion TEXT NOT NULL,
        categoria ENUM('alimentos', 'ropa', 'materiales_escolares', 'medicinas', 'otros') NOT NULL,
        cantidad_necesaria INT NOT NULL,
        cantidad_actual INT DEFAULT 0,
        fecha_limite DATE,
        estado ENUM('activa', 'completada', 'cancelada') DEFAULT 'activa',
        FOREIGN KEY (organizacion_id) REFERENCES organizaciones(id) ON DELETE CASCADE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    
    return $sql;
}

function down() {
    return "DROP TABLE necesidades";
}
?>