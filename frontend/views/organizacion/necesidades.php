<?php
require_once __DIR__ . '/../../../backend/libs/Database.php';
require_once __DIR__ . '/../../../backend/models/Necesidad.php';
require_once __DIR__ . '/../../../backend/models/Organizacion.php';
$mensaje = '';
$mensaje_tipo = '';
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'organizacion') {
    header('Location: ' . BASE_URL . '/frontend/views/auth/login.php');
    exit();
}
$necesidades = [];
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
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $necesidadModel = new Necesidad();
    // Editar
    if (isset($_POST['editar_necesidad'])) {
        $ok = $necesidadModel->actualizar(
            $_POST['id'],
            $_POST['titulo'],
            $_POST['descripcion'],
            $_POST['categoria'],
            $_POST['cantidad_necesaria'],
            $_POST['fecha_limite'],
            $_POST['estado']
        );
        if ($ok) {
            $_SESSION['success'] = 'Necesidad actualizada correctamente.';
        } else {
            $_SESSION['error'] = 'Error al actualizar la necesidad.';
        }
        header('Location: ' . $_SERVER['REQUEST_URI']);
        exit();
    }
    // Eliminar (redirige a necesidades_eliminar.php)
}
$org = (new Organizacion())->obtenerPorUsuarioId($_SESSION['user_id']);
if ($org && isset($org['id'])) {
    $necesidades = (new Necesidad())->listarPorOrganizacion($org['id']);
}
?>
<?php require_once __DIR__ . '/../../partials/header.php'; ?>
<?php require_once __DIR__ . '/../../partials/navbar.php'; ?>

<main class="container my-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Mis Necesidades</h1>
        <a href="<?= BASE_URL ?>/frontend/views/organizacion/necesidades_crear.php" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Nueva Necesidad
        </a>
    </div>

    <?php if ($mensaje): ?>
    <style>
    .vip-alert-float-org {
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%) scale(1);
        z-index: 5000;
        min-width: 370px;
        max-width: 97vw;
        box-shadow: 0 24px 96px rgba(0,80,180,0.40);
        border-radius: 3rem;
        animation: vipfadeinorg 0.8s cubic-bezier(.68,-0.55,.27,1.55);
        background: linear-gradient(135deg, #e3f6fd 0%, #d0f8ce 100%);
        border: 4px solid #2196f3;
        color: #01579b;
        font-family: 'Poppins', 'Roboto', sans-serif;
        letter-spacing: 1.2px;
    }
    @keyframes vipfadeinorg {
        0% { opacity: 0; transform: translate(-50%, -60%) scale(0.7); }
        100% { opacity: 1; transform: translate(-50%, -50%) scale(1); }
    }
    .vip-alert-float-org .fa-crown {
        font-size: 3.2rem;
        margin-bottom: 0.5rem;
        color: #ffc107;
        text-shadow: 0 2px 12px #fff176, 0 0 24px #2196f3;
        animation: crownpoporg 0.9s cubic-bezier(.68,-0.55,.27,1.55);
    }
    .vip-alert-float-org .fa-list-alt {
        font-size: 2.2rem;
        color: #2196f3;
        margin-left: 0.5rem;
        animation: listpop 1s cubic-bezier(.68,-0.55,.27,1.55);
    }
    @keyframes crownpoporg {
        0% { transform: scale(0.5) rotate(-20deg); }
        80% { transform: scale(1.3) rotate(10deg); }
        100% { transform: scale(1) rotate(0deg); }
    }
    @keyframes listpop {
        0% { transform: scale(0.5) rotate(10deg); }
        80% { transform: scale(1.2) rotate(-10deg); }
        100% { transform: scale(1) rotate(0deg); }
    }
    .vip-alert-float-org .btn-close {
        filter: invert(1) grayscale(1);
    }
    </style>
    <div class="vip-alert-float-org alert alert-<?= $mensaje_tipo ?> alert-dismissible fade show text-center py-5 fs-2 fw-bold" role="alert">
        <i class="fas fa-crown"></i><i class="fas fa-list-alt"></i><br>
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
                            <th>Título</th>
                            <th>Categoría</th>
                            <th>Progreso</th>
                            <th>Fecha Límite</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($necesidades)): ?>
                            <tr>
                                <td colspan="6" class="text-center">No hay necesidades publicadas</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($necesidades as $necesidad): ?>
                                <tr>
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
                                    <td><?= date('d/m/Y', strtotime($necesidad['fecha_limite'])) ?></td>
                                    <td>
                                        <span class="badge bg-<?= $necesidad['estado'] === 'activa' ? 'success' : ($necesidad['estado'] === 'completada' ? 'primary' : 'secondary') ?>">
                                            <?= ucfirst($necesidad['estado']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="d-flex gap-2">
                                            <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editarModal<?= $necesidad['id'] ?>">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <form action="<?= BASE_URL ?>/frontend/views/organizacion/necesidades_eliminar.php?id=<?= $necesidad['id'] ?>" method="POST" onsubmit="return confirm('¿Estás seguro de eliminar esta necesidad?');">
                                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                        <!-- Modal Editar -->
                                        <div class="modal fade" id="editarModal<?= $necesidad['id'] ?>" tabindex="-1">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <form method="POST">
                                                        <input type="hidden" name="id" value="<?= $necesidad['id'] ?>">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Editar Necesidad</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <div class="mb-3">
                                                                <label for="titulo<?= $necesidad['id'] ?>" class="form-label">Título</label>
                                                                <input type="text" class="form-control" id="titulo<?= $necesidad['id'] ?>" name="titulo" value="<?= htmlspecialchars($necesidad['titulo']) ?>" required>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label for="descripcion<?= $necesidad['id'] ?>" class="form-label">Descripción</label>
                                                                <textarea class="form-control" id="descripcion<?= $necesidad['id'] ?>" name="descripcion" rows="3" required><?= htmlspecialchars($necesidad['descripcion']) ?></textarea>
                                                            </div>
                                                            <div class="row">
                                                                <div class="col-md-6 mb-3">
                                                                    <label for="categoria<?= $necesidad['id'] ?>" class="form-label">Categoría</label>
                                                                    <select class="form-select" id="categoria<?= $necesidad['id'] ?>" name="categoria" required>
                                                                        <option value="alimentos" <?= $necesidad['categoria'] === 'alimentos' ? 'selected' : '' ?>>Alimentos</option>
                                                                        <option value="ropa" <?= $necesidad['categoria'] === 'ropa' ? 'selected' : '' ?>>Ropa</option>
                                                                        <option value="materiales_escolares" <?= $necesidad['categoria'] === 'materiales_escolares' ? 'selected' : '' ?>>Materiales Escolares</option>
                                                                        <option value="medicinas" <?= $necesidad['categoria'] === 'medicinas' ? 'selected' : '' ?>>Medicinas</option>
                                                                        <option value="otros" <?= $necesidad['categoria'] === 'otros' ? 'selected' : '' ?>>Otros</option>
                                                                    </select>
                                                                </div>
                                                                <div class="col-md-6 mb-3">
                                                                    <label for="cantidad_necesaria<?= $necesidad['id'] ?>" class="form-label">Cantidad Necesaria</label>
                                                                    <input type="number" class="form-control" id="cantidad_necesaria<?= $necesidad['id'] ?>" name="cantidad_necesaria" min="1" value="<?= $necesidad['cantidad_necesaria'] ?>" required>
                                                                </div>
                                                            </div>
                                                            <div class="row">
                                                                <div class="col-md-6 mb-3">
                                                                    <label for="fecha_limite<?= $necesidad['id'] ?>" class="form-label">Fecha Límite</label>
                                                                    <input type="date" class="form-control" id="fecha_limite<?= $necesidad['id'] ?>" name="fecha_limite" value="<?= $necesidad['fecha_limite'] ?>" required>
                                                                </div>
                                                                <div class="col-md-6 mb-3">
                                                                    <label for="estado<?= $necesidad['id'] ?>" class="form-label">Estado</label>
                                                                    <select class="form-select" id="estado<?= $necesidad['id'] ?>" name="estado" required>
                                                                        <option value="activa" <?= $necesidad['estado'] === 'activa' ? 'selected' : '' ?>>Activa</option>
                                                                        <option value="completada" <?= $necesidad['estado'] === 'completada' ? 'selected' : '' ?>>Completada</option>
                                                                        <option value="cancelada" <?= $necesidad['estado'] === 'cancelada' ? 'selected' : '' ?>>Cancelada</option>
                                                                    </select>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                                            <button type="submit" name="editar_necesidad" class="btn btn-primary">Guardar Cambios</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
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