<?php
require_once __DIR__ . '/../../../backend/libs/Database.php';
require_once __DIR__ . '/../../../backend/models/Organizacion.php';
require_once __DIR__ . '/../../../backend/models/Donacion.php';
require_once __DIR__ . '/../../../backend/models/Necesidad.php';
$organizaciones = (new Organizacion())->listar();
$donaciones = method_exists('Donacion', 'listarTodas') ? (new Donacion())->listarTodas() : [];
$necesidades = method_exists('Necesidad', 'listarActivas') ? (new Necesidad())->listarActivas() : [];
?>
<?php require_once __DIR__ . '/../../partials/header.php'; ?>
<?php require_once __DIR__ . '/../../partials/navbar.php'; ?>
<main class="container my-5">
    <h1 class="mb-4">Panel de Administración</h1>
    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title">Organizaciones</h5>
                            <h2 class="mb-0"><?= count($organizaciones) ?></h2>
                        </div>
                        <i class="fas fa-building fa-3x opacity-50"></i>
                    </div>
                    <a href="<?= BASE_URL ?>/frontend/views/admin/organizaciones.php" class="stretched-link"></a>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title">Donaciones</h5>
                            <h2 class="mb-0"><?= count($donaciones) ?></h2>
                        </div>
                        <i class="fas fa-hand-holding-heart fa-3x opacity-50"></i>
                    </div>
                    <a href="<?= BASE_URL ?>/frontend/views/admin/donaciones.php" class="stretched-link"></a>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="card bg-info text-white">
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
                        <p class="text-muted">No hay donaciones recientes.</p>
                    <?php else: ?>
                        <div class="list-group">
                            <?php foreach (array_slice($donaciones, 0, 5) as $donacion): ?>
                                <div class="list-group-item">
                                    <div class="d-flex justify-content-between">
                                        <strong><?= isset($donacion['donante_nombre']) ? htmlspecialchars($donacion['donante_nombre']) : 'N/A' ?></strong>
                                        <span class="badge bg-<?= isset($donacion['estado']) && $donacion['estado'] === 'pendiente' ? 'warning' : (isset($donacion['estado']) && $donacion['estado'] === 'confirmada' ? 'info' : 'success') ?>">
                                            <?= isset($donacion['estado']) ? ucfirst($donacion['estado']) : 'N/A' ?>
                                        </span>
                                    </div>
                                    <small class="text-muted">Para: <?= isset($donacion['necesidad_titulo']) ? htmlspecialchars($donacion['necesidad_titulo']) : 'N/A' ?></small>
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
                    <h5 class="mb-0">Organizaciones por Verificar</h5>
                </div>
                <div class="card-body">
                    <?php 
                    $orgsPorVerificar = array_filter($organizaciones, function($org) {
                        return isset($org['verificada']) && !$org['verificada'];
                    });
                    ?>
                    <?php if (empty($orgsPorVerificar)): ?>
                        <p class="text-muted">No hay organizaciones pendientes de verificación.</p>
                    <?php else: ?>
                        <div class="list-group">
                            <?php foreach (array_slice($orgsPorVerificar, 0, 5) as $org): ?>
                                <div class="list-group-item">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <strong><?= isset($org['nombre']) ? htmlspecialchars($org['nombre']) : 'N/A' ?></strong>
                                        <a href="<?= BASE_URL ?>/frontend/views/admin/organizaciones.php" class="btn btn-sm btn-outline-primary">Verificar</a>
                                    </div>
                                    <small class="text-muted"><?= isset($org['email']) ? htmlspecialchars($org['email']) : 'N/A' ?></small>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</main>
<?php require_once __DIR__ . '/../../partials/footer.php'; ?>