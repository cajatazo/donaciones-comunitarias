<?php
require_once __DIR__ . '/../../../backend/libs/Database.php';
require_once __DIR__ . '/../../../backend/models/Necesidad.php';
require_once __DIR__ . '/../../../backend/models/Organizacion.php';
require_once __DIR__ . '/../../partials/header.php';
require_once __DIR__ . '/../../partials/navbar.php';

if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'organizacion') {
    header('Location: ' . BASE_URL . '/frontend/views/auth/login.php');
    exit();
}

$orgModel = new Organizacion();
$org = $orgModel->obtenerPorUsuarioId($_SESSION['user_id']);
if (!$org || !$org['verificada']) {
    echo '<div class="alert alert-danger">Su organización no está verificada o no existe.</div>';
    require_once __DIR__ . '/../../partials/footer.php';
    exit;
}

$mensaje = '';
$mensaje_tipo = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = $_POST['titulo'];
    $descripcion = $_POST['descripcion'];
    $categoria = $_POST['categoria'];
    $cantidadNecesaria = $_POST['cantidad_necesaria'];
    $fechaLimite = $_POST['fecha_limite'];
    $necesidadModel = new Necesidad();
    if ($necesidadModel->crear($org['id'], $titulo, $descripcion, $categoria, $cantidadNecesaria, $fechaLimite)) {
        $mensaje = 'Necesidad publicada con éxito';
        $mensaje_tipo = 'success';
    } else {
        $mensaje = 'Error al publicar la necesidad';
        $mensaje_tipo = 'danger';
    }
}
?>
<main class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h2 class="mb-0">Publicar Nueva Necesidad</h2>
                </div>
                <div class="card-body">
                    <?php if ($mensaje): ?>
                        <div class="alert alert-<?= $mensaje_tipo ?>"> <?= $mensaje ?> </div>
                    <?php endif; ?>
                    <form action="" method="POST">
                        <div class="mb-3">
                            <label for="titulo" class="form-label">Título</label>
                            <input type="text" class="form-control" id="titulo" name="titulo" required>
                        </div>
                        <div class="mb-3">
                            <label for="descripcion" class="form-label">Descripción</label>
                            <textarea class="form-control" id="descripcion" name="descripcion" rows="3" required></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="categoria" class="form-label">Categoría</label>
                                <select class="form-select" id="categoria" name="categoria" required>
                                    <option value="">Seleccionar...</option>
                                    <option value="alimentos">Alimentos</option>
                                    <option value="ropa">Ropa</option>
                                    <option value="materiales_escolares">Materiales Escolares</option>
                                    <option value="medicinas">Medicinas</option>
                                    <option value="otros">Otros</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="cantidad_necesaria" class="form-label">Cantidad Necesaria</label>
                                <input type="number" class="form-control" id="cantidad_necesaria" name="cantidad_necesaria" min="1" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="fecha_limite" class="form-label">Fecha Límite</label>
                            <input type="date" class="form-control" id="fecha_limite" name="fecha_limite" min="<?= date('Y-m-d') ?>" required>
                        </div>
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">Publicar Necesidad</button>
                            <a href="<?= BASE_URL ?>/organizacion/necesidades" class="btn btn-outline-secondary">Cancelar</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</main>
<?php require_once __DIR__ . '/../../partials/footer.php'; ?>