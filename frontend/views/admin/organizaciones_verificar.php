<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/donaciones-comunitarias/backend/models/Organizacion.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: ' . BASE_URL . '/frontend/views/auth/login.php');
    exit();
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['org_id'], $_POST['estado'])) {
    $orgId = $_POST['org_id'];
    $estado = $_POST['estado'];
    $orgModel = new Organizacion();
    $org = $orgModel->obtenerPorId($orgId);
    if (!$org) {
        $_SESSION['mensaje'] = 'Organización no encontrada.';
        $_SESSION['mensaje_tipo'] = 'danger';
    } else {
        if ($orgModel->verificar($orgId, $estado)) {
            $_SESSION['mensaje'] = 'Organización verificada correctamente.';
            $_SESSION['mensaje_tipo'] = 'success';
        } else {
            $_SESSION['mensaje'] = 'Error al verificar la organización.';
            $_SESSION['mensaje_tipo'] = 'danger';
        }
    }
    header('Location: ' . BASE_URL . '/frontend/views/admin/organizaciones.php');
    exit();
} else {
    $_SESSION['mensaje'] = 'Solicitud inválida.';
    $_SESSION['mensaje_tipo'] = 'danger';
    header('Location: ' . BASE_URL . '/frontend/views/admin/organizaciones.php');
    exit();
}
// No debe llegar aquí
?> 