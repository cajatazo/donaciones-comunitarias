<?php
function up() {
    $sql = "CREATE TABLE organizaciones (
        id INT AUTO_INCREMENT PRIMARY KEY,
        usuario_id INT NOT NULL,
        nombre VARCHAR(100) NOT NULL,
        ruc VARCHAR(11),
        direccion TEXT,
        telefono VARCHAR(15),
        descripcion TEXT,
        verificada TINYINT(1) DEFAULT 0,
        FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    
    return $sql;
}

function down() {
    return "DROP TABLE organizaciones";
}
?>