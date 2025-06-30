<?php
require_once __DIR__ . '/../../../backend/libs/Database.php';
require_once __DIR__ . '/../../../backend/models/Donacion.php';
$donaciones = (new Donacion())->listarTodas();
$mensaje = '';
$mensaje_tipo = '';
if (isset($_SESSION['success'])) {
    $mensaje = $_SESSION['success'];
    $mensaje_tipo = 'success';
    unset($_SESSION['success']);
}
if (isset($_SESSION['error'])) {
    $mensaje = $_SESSION['error'];
    $mensaje_tipo = 'danger';
    unset($_SESSION['error']);
}
?>
<?php require_once __DIR__ . '/../../partials/header.php'; ?>
<?php require_once __DIR__ . '/../../partials/navbar.php'; ?>

<main class="container my-5">
    <h1 class="mb-4">Donaciones Registradas</h1>

    <?php if ($mensaje): ?>
    <style>
    .vip-alert-float-admin {
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%) scale(1);
        z-index: 5000;
        min-width: 370px;
        max-width: 97vw;
        box-shadow: 0 24px 96px rgba(80,0,180,0.40);
        border-radius: 3rem;
        animation: vipfadeinadmin 0.8s cubic-bezier(.68,-0.55,.27,1.55);
        background: linear-gradient(135deg, #f3e6fd 0%, #e6eaff 100%);
        border: 4px solid #8e24aa;
        color: #4a148c;
        font-family: 'Poppins', 'Roboto', sans-serif;
        letter-spacing: 1.3px;
    }
    @keyframes vipfadeinadmin {
        0% { opacity: 0; transform: translate(-50%, -60%) scale(0.7); }
        100% { opacity: 1; transform: translate(-50%, -50%) scale(1); }
    }
    .vip-alert-float-admin .fa-crown {
        font-size: 3.2rem;
        margin-bottom: 0.5rem;
        color: #ffc107;
        text-shadow: 0 2px 12px #fff176, 0 0 24px #8e24aa;
        animation: crownpopadmin 0.9s cubic-bezier(.68,-0.55,.27,1.55);
    }
    .vip-alert-float-admin .fa-hand-holding-heart {
        font-size: 2.2rem;
        color: #e91e63;
        margin-left: 0.5rem;
        animation: heartpop 1s cubic-bezier(.68,-0.55,.27,1.55);
    }
    @keyframes crownpopadmin {
        0% { transform: scale(0.5) rotate(-20deg); }
        80% { transform: scale(1.3) rotate(10deg); }
        100% { transform: scale(1) rotate(0deg); }
    }
    @keyframes heartpop {
        0% { transform: scale(0.5) rotate(10deg); }
        80% { transform: scale(1.2) rotate(-10deg); }
        100% { transform: scale(1) rotate(0deg); }
    }
    .vip-alert-float-admin .btn-close {
        filter: invert(1) grayscale(1);
    }
    </style>
    <div class="vip-alert-float-admin alert alert-<?= $mensaje_tipo ?> alert-dismissible fade show text-center py-5 fs-2 fw-bold" role="alert">
        <i class="fas fa-crown"></i><i class="fas fa-hand-holding-heart"></i><br>
        <?= $mensaje ?>
        <button type="button" class="btn-close position-absolute top-0 end-0 m-3" data-bs-dismiss="alert" aria-label="Cerrar"></button>
    </div>
    <?php endif; ?>

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Donante</th>
                            <th>Necesidad</th>
                            <th>Organización</th>
                            <th>Cantidad</th>
                            <th>Fecha</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($donaciones)): ?>
                            <tr>
                                <td colspan="7" class="text-center">No hay donaciones registradas</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($donaciones as $donacion): ?>
                                <tr>
                                    <td><?= htmlspecialchars($donacion['donante_nombre']) ?></td>
                                    <td><?= htmlspecialchars($donacion['necesidad_titulo']) ?></td>
                                    <td><?= htmlspecialchars($donacion['organizacion_nombre']) ?></td>
                                    <td><?= $donacion['cantidad'] ?></td>
                                    <td><?= date('d/m/Y', strtotime($donacion['created_at'])) ?></td>
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
                                            Gestionar
                                        </button>
                                        
                                        <!-- Modal de gestión -->
                                        <div class="modal fade" id="donacionModal<?= $donacion['id'] ?>" tabindex="-1">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Gestionar Donación</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <form action="<?= BASE_URL ?>/frontend/views/admin/donaciones_estado.php" method="POST">
                                                        <input type="hidden" name="donacion_id" value="<?= $donacion['id'] ?>">
                                                        <div class="modal-body">
                                                            <div class="mb-3">
                                                                <label class="form-label">Donante</label>
                                                                <input type="text" class="form-control" value="<?= htmlspecialchars($donacion['donante_nombre']) ?>" readonly>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label">Necesidad</label>
                                                                <input type="text" class="form-control" value="<?= htmlspecialchars($donacion['necesidad_titulo']) ?>" readonly>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label">Cantidad</label>
                                                                <input type="text" class="form-control" value="<?= $donacion['cantidad'] ?>" readonly>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label">Estado</label>
                                                                <select class="form-select" name="estado" required>
                                                                    <option value="pendiente" <?= $donacion['estado'] === 'pendiente' ? 'selected' : '' ?>>Pendiente</option>
                                                                    <option value="confirmada" <?= $donacion['estado'] === 'confirmada' ? 'selected' : '' ?>>Confirmada</option>
                                                                    <option value="entregada" <?= $donacion['estado'] === 'entregada' ? 'selected' : '' ?>>Entregada</option>
                                                                    <option value="cancelada" <?= $donacion['estado'] === 'cancelada' ? 'selected' : '' ?>>Cancelada</option>
                                                                </select>
                                                            </div>
                                                            <div class="mb-3" id="fechaEntregaContainer<?= $donacion['id'] ?>" style="display: none;">
                                                                <label for="fechaEntrega<?= $donacion['id'] ?>" class="form-label">Fecha de Entrega</label>
                                                                <input type="date" class="form-control" id="fechaEntrega<?= $donacion['id'] ?>" name="fecha_entrega">
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                                            <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <script>
                                            document.addEventListener('DOMContentLoaded', function() {
                                                const estadoSelect = document.querySelector('#donacionModal<?= $donacion['id'] ?> select[name="estado"]');
                                                const fechaContainer = document.querySelector('#fechaEntregaContainer<?= $donacion['id'] ?>');
                                                
                                                estadoSelect.addEventListener('change', function() {
                                                    if (this.value === 'entregada') {
                                                        fechaContainer.style.display = 'block';
                                                    } else {
                                                        fechaContainer.style.display = 'none';
                                                    }
                                                });
                                            });
                                        </script>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/../../partials/footer.php'; ?>