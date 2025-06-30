<?php
require_once __DIR__ . '/../../../backend/libs/Database.php';
require_once __DIR__ . '/../../../backend/models/Necesidad.php';
require_once __DIR__ . '/../../../backend/models/Donacion.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'organizacion') {
    header('Location: ' . BASE_URL . '/frontend/views/auth/login.php');
    exit();
}
$mensaje = '';
$mensaje_tipo = '';
$id = isset($_GET['id']) ? $_GET['id'] : (isset($_POST['id']) ? $_POST['id'] : null);
$necesidadModel = new Necesidad();
$necesidad = $id ? $necesidadModel->obtenerPorId($id) : [];
$donaciones = $id ? (new Donacion())->listarPorNecesidad($id) : [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
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
    header('Location: ' . BASE_URL . '/frontend/views/organizacion/necesidades.php');
    exit();
}
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
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h2 class="mb-0">Editar Necesidad</h2>
                </div>
                <div class="card-body">
                    <?php if ($mensaje): ?>
                    <div class="alert alert-<?= $mensaje_tipo ?>"> <?= $mensaje ?> </div>
                    <?php endif; ?>
                    <form action="" method="POST">
                        <input type="hidden" name="id" value="<?= $necesidad['id'] ?>">
                        
                        <div class="mb-3">
                            <label for="titulo" class="form-label">Título</label>
                            <input type="text" class="form-control" id="titulo" name="titulo" value="<?= htmlspecialchars($necesidad['titulo']) ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="descripcion" class="form-label">Descripción</label>
                            <textarea class="form-control" id="descripcion" name="descripcion" rows="3" required><?= htmlspecialchars($necesidad['descripcion']) ?></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="categoria" class="form-label">Categoría</label>
                                <select class="form-select" id="categoria" name="categoria" required>
                                    <option value="alimentos" <?= $necesidad['categoria'] === 'alimentos' ? 'selected' : '' ?>>Alimentos</option>
                                    <option value="ropa" <?= $necesidad['categoria'] === 'ropa' ? 'selected' : '' ?>>Ropa</option>
                                    <option value="materiales_escolares" <?= $necesidad['categoria'] === 'materiales_escolares' ? 'selected' : '' ?>>Materiales Escolares</option>
                                    <option value="medicinas" <?= $necesidad['categoria'] === 'medicinas' ? 'selected' : '' ?>>Medicinas</option>
                                    <option value="otros" <?= $necesidad['categoria'] === 'otros' ? 'selected' : '' ?>>Otros</option>
                                </select>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="cantidad_necesaria" class="form-label">Cantidad Necesaria</label>
                                <input type="number" class="form-control" id="cantidad_necesaria" name="cantidad_necesaria" min="1" value="<?= $necesidad['cantidad_necesaria'] ?>" required>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="fecha_limite" class="form-label">Fecha Límite</label>
                                <input type="date" class="form-control" id="fecha_limite" name="fecha_limite" value="<?= $necesidad['fecha_limite'] ?>" required>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="estado" class="form-label">Estado</label>
                                <select class="form-select" id="estado" name="estado" required>
                                    <option value="activa" <?= $necesidad['estado'] === 'activa' ? 'selected' : '' ?>>Activa</option>
                                    <option value="completada" <?= $necesidad['estado'] === 'completada' ? 'selected' : '' ?>>Completada</option>
                                    <option value="cancelada" <?= $necesidad['estado'] === 'cancelada' ? 'selected' : '' ?>>Cancelada</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                            <a href="<?= BASE_URL ?>/organizacion/necesidades" class="btn btn-outline-secondary">Cancelar</a>
                        </div>
                    </form>
                    
                    <hr class="my-4">
                    
                    <h5 class="mb-3">Donaciones Recibidas</h5>
                    <?php if (empty($donaciones)): ?>
                        <div class="alert alert-info">No hay donaciones registradas para esta necesidad.</div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Donante</th>
                                        <th>Cantidad</th>
                                        <th>Fecha</th>
                                        <th>Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($donaciones as $donacion): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($donacion['donante_nombre']) ?></td>
                                            <td><?= $donacion['cantidad'] ?></td>
                                            <td><?= date('d/m/Y', strtotime($donacion['created_at'])) ?></td>
                                            <td>
                                                <span class="badge bg-<?= $donacion['estado'] === 'pendiente' ? 'warning' : ($donacion['estado'] === 'confirmada' ? 'info' : 'success') ?>">
                                                    <?= ucfirst($donacion['estado']) ?>
                                                </span>
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
    </div>
</main>

<?php require_once __DIR__ . '/../../partials/footer.php'; ?>