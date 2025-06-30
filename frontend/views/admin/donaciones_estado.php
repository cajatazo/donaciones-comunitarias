<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/donaciones-comunitarias/backend/models/Donacion.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: ' . BASE_URL . '/frontend/views/auth/login.php');
    exit();
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['donacion_id'], $_POST['estado'])) {
    $donacionModel = new Donacion();
    $ok = $donacionModel->cambiarEstado($_POST['donacion_id'], $_POST['estado']);
    $fechaOk = true;
    if ($ok && $_POST['estado'] === 'entregada' && !empty($_POST['fecha_entrega'])) {
        $fechaOk = $donacionModel->actualizarFechaEntrega($_POST['donacion_id'], $_POST['fecha_entrega']);
    }
    if ($ok && $fechaOk) {
        $_SESSION['success'] = 'Estado de la donación actualizado correctamente.';
    } else {
        $_SESSION['error'] = 'Error al actualizar el estado de la donación.';
    }
    header('Location: ' . BASE_URL . '/frontend/views/admin/donaciones.php');
    exit();
} else {
    $_SESSION['error'] = 'Solicitud inválida.';
    header('Location: ' . BASE_URL . '/frontend/views/admin/donaciones.php');
    exit();
}
// No debe llegar aquí
?> 