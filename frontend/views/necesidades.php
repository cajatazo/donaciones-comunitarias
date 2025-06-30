<?php
require_once __DIR__ . '/../../backend/libs/Database.php';
require_once __DIR__ . '/../../backend/models/Necesidad.php';
require_once __DIR__ . '/../../backend/models/Donacion.php';
$necesidadModel = new Necesidad();
$donacionModel = new Donacion();
$necesidades = $necesidadModel->listarActivas();
$mensaje = '';
$mensaje_tipo = '';

if (session_status() === PHP_SESSION_NONE) { session_start(); }
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['necesidad_id'], $_POST['cantidad'])) {
    if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'donante') {
        header('Location: ' . BASE_URL . '/frontend/views/auth/login.php');
        exit();
    }
    $necesidadId = $_POST['necesidad_id'];
    $cantidad = $_POST['cantidad'];
    $comentario = $_POST['comentario'] ?? null;
    if ($donacionModel->crear($necesidadId, $_SESSION['user_id'], $cantidad, $comentario)) {
        $necesidadModel->actualizarCantidad($necesidadId, $cantidad);
        $_SESSION['success'] = 'Donación registrada con éxito';
        header('Location: ' . $_SERVER['REQUEST_URI']);
        exit();
    } else {
        $_SESSION['error'] = 'Error al registrar la donación';
        header('Location: ' . $_SERVER['REQUEST_URI']);
        exit();
    }
}

// Mostrar mensajes de éxito/error
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
<?php if ($mensaje): ?>
<style>
.vip-alert-float {
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%) scale(1);
    z-index: 3000;
    min-width: 350px;
    max-width: 95vw;
    box-shadow: 0 12px 48px rgba(0,0,0,0.35);
    border-radius: 2rem;
    animation: vipfadein 0.6s cubic-bezier(.68,-0.55,.27,1.55);
    background: linear-gradient(135deg, #fffbe6 0%, #ffe6fa 100%);
    border: 3px solid #ffc107;
    color: #6c3483;
}
@keyframes vipfadein {
    0% { opacity: 0; transform: translate(-50%, -60%) scale(0.7); }
    100% { opacity: 1; transform: translate(-50%, -50%) scale(1); }
}
.vip-alert-float .fa-crown {
    font-size: 3rem;
    margin-bottom: 0.7rem;
    color: #ffc107;
    text-shadow: 0 2px 8px #fff176;
    animation: crownpop 0.7s cubic-bezier(.68,-0.55,.27,1.55);
}
@keyframes crownpop {
    0% { transform: scale(0.5) rotate(-20deg); }
    80% { transform: scale(1.2) rotate(10deg); }
    100% { transform: scale(1) rotate(0deg); }
}
.vip-alert-float .btn-close {
    filter: invert(1) grayscale(1);
}
</style>
<div class="vip-alert-float alert alert-<?= $mensaje_tipo ?> alert-dismissible fade show text-center py-5 fs-3 fw-bold" role="alert">
    <i class="fas fa-crown"></i><br>
    <?= $mensaje ?>
    <button type="button" class="btn-close position-absolute top-0 end-0 m-3" data-bs-dismiss="alert" aria-label="Cerrar"></button>
</div>
<?php endif; ?>
<?php require_once __DIR__ . '/../partials/header.php'; ?>
<?php require_once __DIR__ . '/../partials/navbar.php'; ?>

<main class="container my-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Necesidades de Donación</h1>
        <?php if (isset($_SESSION['user_id']) && $_SESSION['user_type'] === 'organizacion'): ?>
            <a href="<?= BASE_URL ?>/frontend/views/organizacion/necesidades_crear.php" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>Publicar Necesidad
            </a>
        <?php endif; ?>
    </div>

    <div class="row mb-4">
        <div class="col-md-6">
            <form class="d-flex">
                <input type="text" class="form-control me-2" placeholder="Buscar necesidades..." name="q">
                <button type="submit" class="btn btn-outline-primary">Buscar</button>
            </form>
        </div>
        <div class="col-md-6">
            <div class="dropdown float-end">
                <button class="btn btn-outline-secondary dropdown-toggle" type="button" id="filterDropdown" data-bs-toggle="dropdown">
                    Filtrar por categoría
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="<?= BASE_URL ?>/necesidades">Todas</a></li>
                    <li><a class="dropdown-item" href="<?= BASE_URL ?>/necesidades?categoria=alimentos">Alimentos</a></li>
                    <li><a class="dropdown-item" href="<?= BASE_URL ?>/necesidades?categoria=ropa">Ropa</a></li>
                    <li><a class="dropdown-item" href="<?= BASE_URL ?>/necesidades?categoria=materiales_escolares">Materiales Escolares</a></li>
                    <li><a class="dropdown-item" href="<?= BASE_URL ?>/necesidades?categoria=medicinas">Medicinas</a></li>
                    <li><a class="dropdown-item" href="<?= BASE_URL ?>/necesidades?categoria=otros">Otros</a></li>
                </ul>
            </div>
        </div>
    </div>

    <div class="row">
        <?php if (empty($necesidades)): ?>
            <div class="col-12">
                <div class="alert alert-info">No hay necesidades publicadas en este momento.</div>
            </div>
        <?php else: ?>
            <?php foreach ($necesidades as $necesidad): ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100 shadow-sm">
                        <div class="card-header bg-primary text-white">
                            <h5 class="card-title mb-0"><?= htmlspecialchars($necesidad['titulo']) ?></h5>
                        </div>
                        <div class="card-body">
                            <p class="card-text"><?= htmlspecialchars($necesidad['descripcion']) ?></p>
                            <ul class="list-group list-group-flush mb-3">
                                <li class="list-group-item">
                                    <strong>Organización:</strong> <?= htmlspecialchars($necesidad['organizacion_nombre']) ?>
                                </li>
                                <li class="list-group-item">
                                    <strong>Categoría:</strong> 
                                    <?php 
                                        $categorias = [
                                            'alimentos' => 'Alimentos',
                                            'ropa' => 'Ropa',
                                            'materiales_escolares' => 'Materiales Escolares',
                                            'medicinas' => 'Medicinas',
                                            'otros' => 'Otros'
                                        ];
                                        echo $categorias[$necesidad['categoria']];
                                    ?>
                                </li>
                                <li class="list-group-item">
                                    <strong>Cantidad:</strong> 
                                    <div class="progress mt-2">
                                        <div class="progress-bar bg-success" 
                                             role="progressbar" 
                                             style="width: <?= min(100, ($necesidad['cantidad_actual'] / $necesidad['cantidad_necesaria']) * 100) ?>%" 
                                             aria-valuenow="<?= $necesidad['cantidad_actual'] ?>" 
                                             aria-valuemin="0" 
                                             aria-valuemax="<?= $necesidad['cantidad_necesaria'] ?>">
                                        </div>
                                    </div>
                                    <small class="text-muted"><?= $necesidad['cantidad_actual'] ?> de <?= $necesidad['cantidad_necesaria'] ?></small>
                                </li>
                                <li class="list-group-item">
                                    <strong>Fecha límite:</strong> <?= date('d/m/Y', strtotime($necesidad['fecha_limite'])) ?>
                                </li>
                            </ul>
                        </div>
                        <div class="card-footer bg-white">
                            <?php if (isset($_SESSION['user_id']) && $_SESSION['user_type'] === 'donante'): ?>
                                <button class="btn btn-primary w-100" data-bs-toggle="modal" data-bs-target="#donarModal<?= $necesidad['id'] ?>">
                                    Donar
                                </button>
                                <!-- Modal para donar -->
                                <div class="modal fade" id="donarModal<?= $necesidad['id'] ?>" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Donar a: <?= htmlspecialchars($necesidad['titulo']) ?></h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <form action="" method="POST">
                                                <div class="modal-body">
                                                    <input type="hidden" name="necesidad_id" value="<?= $necesidad['id'] ?>">
                                                    <div class="mb-3">
                                                        <label for="cantidad<?= $necesidad['id'] ?>" class="form-label">Cantidad a donar</label>
                                                        <input type="number" class="form-control" id="cantidad<?= $necesidad['id'] ?>" name="cantidad" 
                                                               min="1" max="<?= $necesidad['cantidad_necesaria'] - $necesidad['cantidad_actual'] ?>" required>
                                                        <small class="text-muted">Máximo disponible: <?= $necesidad['cantidad_necesaria'] - $necesidad['cantidad_actual'] ?></small>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label for="comentario<?= $necesidad['id'] ?>" class="form-label">Comentario (opcional)</label>
                                                        <textarea class="form-control" id="comentario<?= $necesidad['id'] ?>" name="comentario" rows="3"></textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                                    <button type="submit" class="btn btn-primary">Confirmar Donación</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            <?php elseif (!isset($_SESSION['user_id'])): ?>
                                <a href="<?= BASE_URL ?>/auth/login" class="btn btn-primary w-100">Inicia sesión para donar</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</main>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>