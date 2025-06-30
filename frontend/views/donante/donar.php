<?php
require_once __DIR__ . '/../../../backend/libs/Database.php';
require_once __DIR__ . '/../../../backend/models/Necesidad.php';
require_once __DIR__ . '/../../../backend/models/Donacion.php';
require_once __DIR__ . '/../../partials/header.php';
require_once __DIR__ . '/../../partials/navbar.php';

if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'donante') {
    header('Location: ' . BASE_URL . '/frontend/views/auth/login.php');
    exit();
}

$necesidadModel = new Necesidad();
$necesidades = $necesidadModel->listarActivas();
$mensaje = '';
$mensaje_tipo = '';
$necesidadSeleccionada = null;
if (isset($_GET['necesidad_id'])) {
    $necesidadSeleccionada = $necesidadModel->buscarPorId($_GET['necesidad_id']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $necesidadId = $_POST['necesidad_id'];
    $cantidad = $_POST['cantidad'];
    $comentario = $_POST['comentario'] ?? null;
    $donacionModel = new Donacion();
    if ($donacionModel->crear($necesidadId, $_SESSION['user_id'], $cantidad, $comentario)) {
        $necesidadModel->actualizarCantidad($necesidadId, $cantidad);
        $_SESSION['mensaje'] = 'Donación registrada con éxito';
        $_SESSION['mensaje_tipo'] = 'success';
    } else {
        $_SESSION['mensaje'] = 'Error al registrar la donación';
        $_SESSION['mensaje_tipo'] = 'danger';
    }
    $redirigir = $_SERVER['PHP_SELF'];
    if (isset($_GET['necesidad_id'])) {
        $redirigir .= '?necesidad_id=' . urlencode($_GET['necesidad_id']);
    }
    header('Location: ' . $redirigir);
    exit();
}
if (isset($_SESSION['mensaje'])) {
    $mensaje = $_SESSION['mensaje'];
    $mensaje_tipo = $_SESSION['mensaje_tipo'];
    unset($_SESSION['mensaje'], $_SESSION['mensaje_tipo']);
}
?>
<?php if ($mensaje): ?>
<style>
.vip-alert-float {
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%) scale(1);
    z-index: 2000;
    min-width: 350px;
    max-width: 90vw;
    box-shadow: 0 8px 32px rgba(0,0,0,0.25);
    border-radius: 1.5rem;
    animation: vipfadein 0.5s cubic-bezier(.68,-0.55,.27,1.55);
}
@keyframes vipfadein {
    0% { opacity: 0; transform: translate(-50%, -60%) scale(0.8); }
    100% { opacity: 1; transform: translate(-50%, -50%) scale(1); }
}
.vip-alert-float .fa-crown {
    font-size: 2.5rem;
    margin-bottom: 0.5rem;
}
</style>
<div class="vip-alert-float alert alert-<?= $mensaje_tipo ?> alert-dismissible fade show text-center py-5 fs-4" role="alert">
    <i class="fas fa-crown text-warning"></i><br>
    <strong><?= $mensaje ?></strong>
    <button type="button" class="btn-close position-absolute top-0 end-0 m-3" data-bs-dismiss="alert" aria-label="Cerrar"></button>
</div>
<?php endif; ?>
<main class="container my-5">
    <h1 class="mb-4">Realizar Donación</h1>
    <div class="card shadow-sm">
        <div class="card-body">
            <form action="" method="POST">
                <div class="mb-3">
                    <label for="necesidad_id" class="form-label">Selecciona una necesidad</label>
                    <select class="form-select" id="necesidad_id" name="necesidad_id" required <?= $necesidadSeleccionada ? 'readonly disabled' : '' ?>>
                        <option value="">Seleccionar...</option>
                        <?php foreach ($necesidades as $n): ?>
                            <option value="<?= $n['id'] ?>" <?= $necesidadSeleccionada && $necesidadSeleccionada['id'] == $n['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($n['titulo']) ?> (<?= htmlspecialchars($n['organizacion_nombre']) ?>) - <?= $n['cantidad_actual'] ?> / <?= $n['cantidad_necesaria'] ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if ($necesidadSeleccionada): ?>
                        <input type="hidden" name="necesidad_id" value="<?= $necesidadSeleccionada['id'] ?>">
                    <?php endif; ?>
                </div>
                <div class="mb-3">
                    <label for="cantidad" class="form-label">Cantidad a donar</label>
                    <input type="number" class="form-control" id="cantidad" name="cantidad" min="1" required>
                </div>
                <div class="mb-3">
                    <label for="comentario" class="form-label">Comentario (opcional)</label>
                    <textarea class="form-control" id="comentario" name="comentario" rows="3"></textarea>
                </div>
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary">Donar</button>
                    <a href="<?= BASE_URL ?>/frontend/views/necesidades.php" class="btn btn-outline-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</main>
<?php require_once __DIR__ . '/../../partials/footer.php'; ?> 