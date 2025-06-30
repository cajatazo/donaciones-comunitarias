<?php
require_once __DIR__ . '/../../../backend/libs/Database.php';
require_once __DIR__ . '/../../../backend/models/Donacion.php';
$donaciones = isset($_SESSION['user_id']) ? (new Donacion())->listarPorDonante($_SESSION['user_id']) : [];
?>
<?php require_once __DIR__ . '/../../partials/header.php'; ?>
<?php require_once __DIR__ . '/../../partials/navbar.php'; ?>

<main class="container my-5">
    <h1 class="mb-4">Mis Donaciones</h1>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?= $_SESSION['error'] ?></div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success"><?= $_SESSION['success'] ?></div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <div class="card shadow-sm">
        <div class="card-body">
            <?php if (empty($donaciones)): ?>
                <div class="alert alert-info">No has realizado donaciones aún. <a href="<?= BASE_URL ?>/frontend/views/necesidades.php" class="alert-link">Ver necesidades</a></div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Necesidad</th>
                                <th>Organización</th>
                                <th>Cantidad</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($donaciones as $donacion): ?>
                                <tr>
                                    <td><?= date('d/m/Y', strtotime($donacion['created_at'])) ?></td>
                                    <td><?= htmlspecialchars($donacion['necesidad_titulo']) ?></td>
                                    <td><?= htmlspecialchars($donacion['organizacion_nombre']) ?></td>
                                    <td><?= $donacion['cantidad'] ?></td>
                                    <td>
                                        <span class="badge bg-<?= 
                                            $donacion['estado'] === 'pendiente' ? 'warning' : 
                                            ($donacion['estado'] === 'confirmada' ? 'info' : 
                                            ($donacion['estado'] === 'entregada' ? 'success' : 'secondary')) 
                                        ?>">
                                            <?= ucfirst($donacion['estado']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#donacionModal<?= $donacion['id'] ?>">
                                            Detalles
                                        </button>
                                        
                                        <!-- Modal de detalles -->
                                        <div class="modal fade" id="donacionModal<?= $donacion['id'] ?>" tabindex="-1">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Detalles de Donación</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="mb-3">
                                                            <label class="form-label">Necesidad</label>
                                                            <input type="text" class="form-control" value="<?= htmlspecialchars($donacion['necesidad_titulo']) ?>" readonly>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label">Organización</label>
                                                            <input type="text" class="form-control" value="<?= htmlspecialchars($donacion['organizacion_nombre']) ?>" readonly>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label">Cantidad</label>
                                                            <input type="text" class="form-control" value="<?= $donacion['cantidad'] ?>" readonly>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label">Estado</label>
                                                            <input type="text" class="form-control" value="<?= ucfirst($donacion['estado']) ?>" readonly>
                                                        </div>
                                                        <?php if ($donacion['comentario']): ?>
                                                            <div class="mb-3">
                                                                <label class="form-label">Tu Comentario</label>
                                                                <textarea class="form-control" readonly><?= htmlspecialchars($donacion['comentario']) ?></textarea>
                                                            </div>
                                                        <?php endif; ?>
                                                        <?php if ($donacion['fecha_entrega']): ?>
                                                            <div class="mb-3">
                                                                <label class="form-label">Fecha de Entrega</label>
                                                                <input type="text" class="form-control" value="<?= date('d/m/Y', strtotime($donacion['fecha_entrega'])) ?>" readonly>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/../../partials/footer.php'; ?>