<?php
require_once dirname(__DIR__, 2) . '/config.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Red Peruana de Donaciones Comunitarias</title>
    <link rel="icon" type="image/png" href="<?= BASE_URL ?>/frontend/assets/img/logo.png">
    <link href="<?= BASE_URL ?>/frontend/assets/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>