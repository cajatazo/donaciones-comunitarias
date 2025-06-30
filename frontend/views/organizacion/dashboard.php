<?php
require_once __DIR__ . '/../../../backend/libs/Database.php';
require_once __DIR__ . '/../../../backend/models/Necesidad.php';
require_once __DIR__ . '/../../../backend/models/Donacion.php';
require_once __DIR__ . '/../../../backend/models/Organizacion.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'organizacion') {
    header('Location: ' . BASE_URL . '/frontend/views/auth/login.php');
    exit();
}
$org = (new Organizacion())->obtenerPorUsuarioId($_SESSION['user_id']);
$necesidades = [];
$donaciones = [];
if ($org && isset($org['id'])) {
    $necesidades = (new Necesidad())->listarPorOrganizacion($org['id']);
    $donaciones = (new Donacion())->listarPorOrganizacion($org['id']);
}
?>
<?php require_once __DIR__ . '/../../partials/header.php'; ?>
<?php require_once __DIR__ . '/../../partials/navbar.php'; ?>

<main class="container my-5">
    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title">Necesidades Activas</h5>
                            <h2 class="mb-0"><?= count(array_filter($necesidades, fn($n) => $n['estado'] === 'activa')) ?></h2>
                        </div>
                        <i class="fas fa-list-alt fa-3x opacity-50"></i>
                    </div>
                    <a href="<?= BASE_URL ?>/frontend/views/organizacion/necesidades.php" class="stretched-link"></a>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 mb-4">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title">Donaciones Recibidas</h5>
                            <h2 class="mb-0"><?= count($donaciones) ?></h2>
                        </div>
                        <i class="fas fa-hand-holding-heart fa-3x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 mb-4">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title">Necesidades Completadas</h5>
                            <h2 class="mb-0"><?= count(array_filter($necesidades, fn($n) => $n['estado'] === 'completada')) ?></h2>
                        </div>
                        <i class="fas fa-check-circle fa-3x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Últimas Necesidades</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($necesidades)): ?>
                        <p class="text-muted">No hay necesidades publicadas.</p>
                    <?php else: ?>
                        <div class="list-group">
                            <?php foreach (array_slice($necesidades, 0, 5) as $necesidad): ?>
                                <a href="<?= BASE_URL ?>/frontend/views/organizacion/necesidades.php" class="list-group-item list-group-item-action">
                                    <div class="d-flex justify-content-between">
                                        <strong><?= htmlspecialchars($necesidad['titulo']) ?></strong>
                                        <span class="badge bg-<?= $necesidad['estado'] === 'activa' ? 'success' : ($necesidad['estado'] === 'completada' ? 'primary' : 'secondary') ?>">
                                            <?= ucfirst($necesidad['estado']) ?>
                                        </span>
                                    </div>
                                    <small class="text-muted">Publicado: <?= date('d/m/Y', strtotime($necesidad['created_at'])) ?></small>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Últimas Donaciones</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($donaciones)): ?>
                        <p class="text-muted">No hay donaciones recientes.</p>
                    <?php else: ?>
                        <div class="list-group">
                            <?php foreach (array_slice($donaciones, 0, 5) as $donacion): ?>
                                <a href="<?= BASE_URL ?>/frontend/views/organizacion/DonacionesRecibidas.php" class="list-group-item list-group-item-action">
                                    <div class="d-flex justify-content-between">
                                        <strong><?= isset($donacion['donante_nombre']) && $donacion['donante_nombre'] ? htmlspecialchars($donacion['donante_nombre']) : (isset($donacion['nombre']) ? htmlspecialchars($donacion['nombre']) : 'Donante') ?></strong>
                                        <span class="badge bg-<?= $donacion['estado'] === 'pendiente' ? 'warning' : ($donacion['estado'] === 'confirmada' ? 'info' : 'success') ?>">
                                            <?= ucfirst($donacion['estado']) ?>
                                        </span>
                                    </div>
                                    <small class="text-muted">Para: <?= htmlspecialchars($donacion['necesidad_titulo']) ?></small>
                                    <small class="text-muted d-block">Cantidad: <?= $donacion['cantidad'] ?></small>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/../../partials/footer.php'; ?>