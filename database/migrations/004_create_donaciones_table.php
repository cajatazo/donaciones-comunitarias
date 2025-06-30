<?php
function up() {
    $sql = "CREATE TABLE donaciones (
        id INT AUTO_INCREMENT PRIMARY KEY,
        necesidad_id INT NOT NULL,
        donante_id INT NOT NULL,
        cantidad INT NOT NULL,
        estado ENUM('pendiente', 'confirmada', 'entregada', 'cancelada') DEFAULT 'pendiente',
        comentario TEXT,
        fecha_entrega DATE,
        FOREIGN KEY (necesidad_id) REFERENCES necesidades(id) ON DELETE CASCADE,
        FOREIGN KEY (donante_id) REFERENCES usuarios(id) ON DELETE CASCADE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    
    return $sql;
}

function down() {
    return "DROP TABLE donaciones";
}
?>