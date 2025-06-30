<?php
require_once __DIR__ . '/../../../backend/libs/Database.php';
require_once __DIR__ . '/../../../backend/models/Donacion.php';
require_once __DIR__ . '/../../../backend/models/Necesidad.php';
$donaciones = isset($_SESSION['user_id']) ? (new Donacion())->listarPorDonante($_SESSION['user_id']) : [];
$necesidades = method_exists('Necesidad', 'listarActivas') ? (new Necesidad())->listarActivas() : [];
?>
<?php require_once __DIR__ . '/../../partials/header.php'; ?>
<?php require_once __DIR__ . '/../../partials/navbar.php'; ?>

<main class="container my-5">
    <h1 class="mb-4">Bienvenido, <?= isset($_SESSION['user_name']) ? htmlspecialchars($_SESSION['user_name']) : '' ?></h1>
    
    <div class="row">
        <div class="col-md-6 mb-4">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title">Donaciones Realizadas</h5>
                            <h2 class="mb-0"><?= count($donaciones) ?></h2>
                        </div>
                        <i class="fas fa-hand-holding-heart fa-3x opacity-50"></i>
                    </div>
                    <a href="<?= BASE_URL ?>/frontend/views/donante/historial.php" class="stretched-link"></a>
                </div>
            </div>
        </div>
        
        <div class="col-md-6 mb-4">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title">Necesidades Activas</h5>
                            <h2 class="mb-0"><?= count($necesidades) ?></h2>
                        </div>
                        <i class="fas fa-list-alt fa-3x opacity-50"></i>
                    </div>
                    <a href="<?= BASE_URL ?>/frontend/views/necesidades.php" class="stretched-link"></a>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Últimas Donaciones</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($donaciones)): ?>
                        <p class="text-muted">No has realizado donaciones aún.</p>
                    <?php else: ?>
                        <div class="list-group">
                            <?php foreach (array_slice($donaciones, 0, 5) as $donacion): ?>
                                <div class="list-group-item">
                                    <div class="d-flex justify-content-between">
                                        <strong><?= isset($donacion['necesidad_titulo']) ? htmlspecialchars($donacion['necesidad_titulo']) : 'N/A' ?></strong>
                                        <span class="badge bg-<?= isset($donacion['estado']) && $donacion['estado'] === 'pendiente' ? 'warning' : (isset($donacion['estado']) && $donacion['estado'] === 'confirmada' ? 'info' : 'success') ?>">
                                            <?= isset($donacion['estado']) ? ucfirst($donacion['estado']) : 'N/A' ?>
                                        </span>
                                    </div>
                                    <small class="text-muted">Organización: <?= isset($donacion['organizacion_nombre']) ? htmlspecialchars($donacion['organizacion_nombre']) : 'N/A' ?></small>
                                    <small class="text-muted d-block">Cantidad: <?= isset($donacion['cantidad']) ? $donacion['cantidad'] : 'N/A' ?></small>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Necesidades Recientes</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($necesidades)): ?>
                        <p class="text-muted">No hay necesidades publicadas en este momento.</p>
                    <?php else: ?>
                        <div class="list-group">
                            <?php foreach (array_slice($necesidades, 0, 5) as $necesidad): ?>
                                <a href="<?= BASE_URL ?>/frontend/views/necesidades.php" class="list-group-item list-group-item-action">
                                    <div class="d-flex justify-content-between">
                                        <strong><?= isset($necesidad['titulo']) ? htmlspecialchars($necesidad['titulo']) : 'N/A' ?></strong>
                                        <span class="badge bg-<?= isset($necesidad['estado']) && $necesidad['estado'] === 'activa' ? 'success' : 'primary' ?>">
                                            <?= isset($necesidad['estado']) ? ucfirst($necesidad['estado']) : 'N/A' ?>
                                        </span>
                                    </div>
                                    <small class="text-muted">Organización: <?= isset($necesidad['organizacion_nombre']) ? htmlspecialchars($necesidad['organizacion_nombre']) : 'N/A' ?></small>
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