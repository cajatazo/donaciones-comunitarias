<?php
function run() {
    $sql = "INSERT INTO organizaciones (usuario_id, nombre, ruc, direccion, telefono, descripcion, verificada) VALUES 
        (3, 'Comedor Popular Los Olivos', '20123456789', 'Av. Los Olivos 123', '987654321', 'Comedor que atiende a 50 niños diariamente', 1)";
    
    return $sql;
}
?>