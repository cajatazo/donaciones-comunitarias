<?php
require_once __DIR__ . '/../../../backend/libs/Database.php';
require_once __DIR__ . '/../../../backend/models/Necesidad.php';
require_once __DIR__ . '/../../../backend/models/Donacion.php';
$necesidadModel = new Necesidad();
$donacionModel = new Donacion();
$necesidades = $necesidadModel->listarActivas();
if (isset($_GET['categoria']) && $_GET['categoria']) {
    $necesidades = array_filter($necesidades, function($n) {
        return $n['categoria'] === $_GET['categoria'];
    });
}
if (isset($_GET['organizacion']) && $_GET['organizacion']) {
    $necesidades = array_filter($necesidades, function($n) {
        return stripos($n['organizacion_nombre'], $_GET['organizacion']) !== false;
    });
}
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
    <h1 class="mb-4">Realizar Donación</h1>

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

    <div class="row">
        <div class="col-md-8">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Necesidades Disponibles</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($necesidades)): ?>
                        <div class="alert alert-info">No hay necesidades disponibles en este momento.</div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Organización</th>
                                        <th>Título</th>
                                        <th>Categoría</th>
                                        <th>Progreso</th>
                                        <th>Acción</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($necesidades as $necesidad): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($necesidad['organizacion_nombre']) ?></td>
                                            <td><?= htmlspecialchars($necesidad['titulo']) ?></td>
                                            <td>
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
                                            </td>
                                            <td>
                                                <div class="progress" style="height: 20px;">
                                                    <div class="progress-bar bg-success" 
                                                         role="progressbar" 
                                                         style="width: <?= min(100, ($necesidad['cantidad_actual'] / $necesidad['cantidad_necesaria']) * 100) ?>%" 
                                                         aria-valuenow="<?= $necesidad['cantidad_actual'] ?>" 
                                                         aria-valuemin="0" 
                                                         aria-valuemax="<?= $necesidad['cantidad_necesaria'] ?>">
                                                        <?= round(($necesidad['cantidad_actual'] / $necesidad['cantidad_necesaria']) * 100) ?>%
                                                    </div>
                                                </div>
                                                <small class="text-muted"><?= $necesidad['cantidad_actual'] ?> / <?= $necesidad['cantidad_necesaria'] ?></small>
                                            </td>
                                            <td>
                                                <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#donarModal<?= $necesidad['id'] ?>">
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
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Filtrar Necesidades</h5>
                </div>
                <div class="card-body">
                    <form method="GET" action="<?= BASE_URL ?>/donante/donaciones">
                        <div class="mb-3">
                            <label for="categoria" class="form-label">Categoría</label>
                            <select class="form-select" id="categoria" name="categoria">
                                <option value="">Todas las categorías</option>
                                <option value="alimentos" <?= isset($_GET['categoria']) && $_GET['categoria'] === 'alimentos' ? 'selected' : '' ?>>Alimentos</option>
                                <option value="ropa" <?= isset($_GET['categoria']) && $_GET['categoria'] === 'ropa' ? 'selected' : '' ?>>Ropa</option>
                                <option value="materiales_escolares" <?= isset($_GET['categoria']) && $_GET['categoria'] === 'materiales_escolares' ? 'selected' : '' ?>>Materiales Escolares</option>
                                <option value="medicinas" <?= isset($_GET['categoria']) && $_GET['categoria'] === 'medicinas' ? 'selected' : '' ?>>Medicinas</option>
                                <option value="otros" <?= isset($_GET['categoria']) && $_GET['categoria'] === 'otros' ? 'selected' : '' ?>>Otros</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="organizacion" class="form-label">Organización</label>
                            <input type="text" class="form-control" id="organizacion" name="organizacion" value="<?= $_GET['organizacion'] ?? '' ?>">
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">Filtrar</button>
                            <a href="<?= BASE_URL ?>/donante/donaciones" class="btn btn-outline-secondary">Limpiar Filtros</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/../../partials/footer.php'; ?>