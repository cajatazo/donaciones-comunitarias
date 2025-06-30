<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../../../../backend/libs/Database.php';
require_once __DIR__ . '/../../../../backend/models/Donacion.php';
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'organizacion') {
    header('Location: ' . BASE_URL . '/frontend/views/auth/login.php');
    exit();
}
if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['donacion_id'], $_POST['estado'])
) {
    $donacionModel = new Donacion();
    $id = $_POST['donacion_id'];
    $estado = $_POST['estado'];
    $observacion = isset($_POST['observacion']) ? trim($_POST['observacion']) : null;
    $fechaEntrega = isset($_POST['fecha_entrega']) && $_POST['fecha_entrega'] ? $_POST['fecha_entrega'] : null;
    $ok = $donacionModel->actualizarGestion($id, $estado, $observacion, $fechaEntrega);
    if ($ok) {
        $_SESSION['success'] = 'Donación gestionada correctamente.';
    } else {
        $_SESSION['error'] = 'Error al gestionar la donación.';
    }
    header('Location: ' . BASE_URL . '/frontend/views/organizacion/DonacionesRecibidas.php');
    exit();
} else {
    $_SESSION['error'] = 'Solicitud inválida.';
    header('Location: ' . BASE_URL . '/frontend/views/organizacion/DonacionesRecibidas.php');
    exit();
} 