<?php require_once __DIR__ . '/../partials/header.php'; ?>
<?php require_once __DIR__ . '/../partials/navbar.php'; ?>
<main class="container my-5 text-center">
    <h1 class="display-1 text-danger">404</h1>
    <h2 class="mb-4">Página no encontrada</h2>
    <p class="lead">La URL que solicitaste no existe o la acción no está especificada.<br>Por favor, revisa la dirección o vuelve al <a href="<?= BASE_URL ?>/">inicio</a>.</p>
    <a href="<?= BASE_URL ?>/" class="btn btn-primary mt-3">Ir al inicio</a>
</main>
<?php require_once __DIR__ . '/../partials/footer.php'; ?> 