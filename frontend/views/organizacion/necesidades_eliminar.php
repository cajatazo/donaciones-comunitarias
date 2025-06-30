<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/donaciones-comunitarias/backend/models/Necesidad.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'organizacion') {
    header('Location: ' . BASE_URL . '/frontend/views/auth/login.php');
    exit();
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['id'])) {
    $necesidadModel = new Necesidad();
    $ok = $necesidadModel->eliminar($_GET['id']);
    if ($ok) {
        $_SESSION['success'] = 'Necesidad eliminada correctamente.';
    } else {
        $_SESSION['error'] = 'Error al eliminar la necesidad.';
    }
    header('Location: ' . BASE_URL . '/frontend/views/organizacion/necesidades.php');
    exit();
} else {
    $_SESSION['error'] = 'Solicitud inválida.';
    header('Location: ' . BASE_URL . '/frontend/views/organizacion/necesidades.php');
    exit();
}
// No debe llegar aquí
?> 