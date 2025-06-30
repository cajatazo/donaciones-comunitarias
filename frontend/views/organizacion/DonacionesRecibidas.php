<?php require_once __DIR__ . '/../../partials/header.php'; ?>
<?php require_once __DIR__ . '/../../partials/navbar.php'; ?>

<?php
require_once __DIR__ . '/../../../backend/libs/Database.php';
require_once __DIR__ . '/../../../backend/models/Donacion.php';
require_once __DIR__ . '/../../../backend/models/Organizacion.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'organizacion') {
    header('Location: ' . BASE_URL . '/auth/login');
    exit();
}
$org = (new Organizacion())->obtenerPorUsuarioId($_SESSION['user_id']);
$donacionModel = new Donacion();
$donacionesTodas = [];
if ($org && isset($org['id'])) {
    $donacionesTodas = $donacionModel->listarPorOrganizacion($org['id']);
}

// Filtrado por estado
if (isset($_GET['estado']) && in_array($_GET['estado'], ['pendiente', 'confirmada', 'entregada', 'cancelada'])) {
    $donacionesFiltradas = array_filter($donacionesTodas, function($d) {
        return $d['estado'] === $_GET['estado'];
    });
} else {
    $donacionesFiltradas = $donacionesTodas;
}

// Paginación
$porPagina = 10;
$totalDonaciones = count($donacionesFiltradas);
$totalPaginas = max(1, ceil($totalDonaciones / $porPagina));
$paginaActual = isset($_GET['pagina']) && is_numeric($_GET['pagina']) && $_GET['pagina'] > 0 ? (int)$_GET['pagina'] : 1;
$paginaActual = min($paginaActual, $totalPaginas);
$inicio = ($paginaActual - 1) * $porPagina;
$donaciones = array_slice($donacionesFiltradas, $inicio, $porPagina);
?>

<main class="container my-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Donaciones Recibidas</h1>
        
        <div class="dropdown">
            <button class="btn btn-outline-primary dropdown-toggle" type="button" id="filterDropdown" data-bs-toggle="dropdown">
                <i class="fas fa-filter me-2"></i>
                <?= isset($_GET['estado']) ? ucfirst($_GET['estado']) : 'Todos los estados' ?>
            </button>
            <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="<?= BASE_URL ?>/frontend/views/organizacion/DonacionesRecibidas.php">Todos</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="<?= BASE_URL ?>/frontend/views/organizacion/DonacionesRecibidas.php?estado=pendiente">Pendientes</a></li>
                <li><a class="dropdown-item" href="<?= BASE_URL ?>/frontend/views/organizacion/DonacionesRecibidas.php?estado=confirmada">Confirmadas</a></li>
                <li><a class="dropdown-item" href="<?= BASE_URL ?>/frontend/views/organizacion/DonacionesRecibidas.php?estado=entregada">Entregadas</a></li>
                <li><a class="dropdown-item" href="<?= BASE_URL ?>/frontend/views/organizacion/DonacionesRecibidas.php?estado=cancelada">Canceladas</a></li>
            </ul>
        </div>
    </div>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?= $_SESSION['error'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?= $_SESSION['success'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <div class="card shadow-sm">
        <div class="card-body">
            <?php if (empty($donaciones)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-box-open fa-4x text-muted mb-4"></i>
                    <h4 class="text-muted">No hay donaciones registradas</h4>
                    <p class="text-muted">Cuando recibas donaciones, aparecerán listadas aquí</p>
                    <a href="<?= BASE_URL ?>/frontend/views/organizacion/necesidades.php" class="btn btn-primary mt-3">
                        <i class="fas fa-plus me-2"></i>Publicar nueva necesidad
                    </a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th width="120">Fecha</th>
                                <th>Donante</th>
                                <th>Necesidad</th>
                                <th class="text-center">Cantidad</th>
                                <th class="text-center">Estado</th>
                                <th class="text-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($donaciones as $donacion): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <small class="text-muted"><?= date('d/m/Y', strtotime($donacion['created_at'])) ?></small>
                                            <small class="text-muted"><?= date('H:i', strtotime($donacion['created_at'])) ?></small>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                                <i class="fas fa-user"></i>
                                            </div>
                                            <div>
                                                <h6 class="mb-0"><?= htmlspecialchars($donacion['donante_nombre']) ?></h6>
                                                <?php if ($donacion['comentario']): ?>
                                                    <small class="text-muted" data-bs-toggle="tooltip" title="<?= htmlspecialchars($donacion['comentario']) ?>">
                                                        <i class="fas fa-comment me-1"></i> Tiene comentario
                                                    </small>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <h6 class="mb-0"><?= htmlspecialchars($donacion['necesidad_titulo']) ?></h6>
                                        <small class="text-muted"><?= ucfirst($donacion['categoria']) ?></small>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-primary rounded-pill px-3 py-2">
                                            <?= $donacion['cantidad'] ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-<?= 
                                            $donacion['estado'] === 'pendiente' ? 'warning' : 
                                            ($donacion['estado'] === 'confirmada' ? 'info' : 
                                            ($donacion['estado'] === 'entregada' ? 'success' : 'secondary')) 
                                        ?> rounded-pill px-3 py-2">
                                            <?= ucfirst($donacion['estado']) ?>
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <div class="d-flex justify-content-end gap-2">
                                            <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#detalleModal<?= $donacion['id'] ?>">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            
                                            <?php if ($donacion['estado'] === 'pendiente' || $donacion['estado'] === 'confirmada'): ?>
                                                <button class="btn btn-sm btn-outline-success" data-bs-toggle="modal" data-bs-target="#gestionModal<?= $donacion['id'] ?>">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <!-- Modal de detalle -->
                                        <div class="modal fade" id="detalleModal<?= $donacion['id'] ?>" tabindex="-1" aria-labelledby="detalleModalLabel" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered">
                                                <div class="modal-content">
                                                    <div class="modal-header bg-primary text-white">
                                                        <h5 class="modal-title" id="detalleModalLabel">Detalle de Donación</h5>
                                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="row mb-3">
                                                            <div class="col-md-6">
                                                                <h6 class="text-muted mb-1">Donante</h6>
                                                                <p><?= htmlspecialchars($donacion['donante_nombre']) ?></p>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <h6 class="text-muted mb-1">Fecha</h6>
                                                                <p><?= date('d/m/Y H:i', strtotime($donacion['created_at'])) ?></p>
                                                            </div>
                                                        </div>
                                                        
                                                        <div class="row mb-3">
                                                            <div class="col-md-6">
                                                                <h6 class="text-muted mb-1">Necesidad</h6>
                                                                <p><?= htmlspecialchars($donacion['necesidad_titulo']) ?></p>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <h6 class="text-muted mb-1">Categoría</h6>
                                                                <p><?= ucfirst($donacion['categoria']) ?></p>
                                                            </div>
                                                        </div>
                                                        
                                                        <div class="row mb-3">
                                                            <div class="col-md-6">
                                                                <h6 class="text-muted mb-1">Cantidad</h6>
                                                                <p><?= $donacion['cantidad'] ?></p>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <h6 class="text-muted mb-1">Estado</h6>
                                                                <p>
                                                                    <span class="badge bg-<?= 
                                                                        $donacion['estado'] === 'pendiente' ? 'warning' : 
                                                                        ($donacion['estado'] === 'confirmada' ? 'info' : 
                                                                        ($donacion['estado'] === 'entregada' ? 'success' : 'secondary')) 
                                                                    ?>">
                                                                        <?= ucfirst($donacion['estado']) ?>
                                                                    </span>
                                                                </p>
                                                            </div>
                                                        </div>
                                                        
                                                        <?php if ($donacion['comentario']): ?>
                                                            <div class="mb-3">
                                                                <h6 class="text-muted mb-1">Comentario del Donante</h6>
                                                                <div class="bg-light p-3 rounded">
                                                                    <p class="mb-0"><?= htmlspecialchars($donacion['comentario']) ?></p>
                                                                </div>
                                                            </div>
                                                        <?php endif; ?>
                                                        
                                                        <?php if ($donacion['observacion']): ?>
                                                            <div class="mb-3">
                                                                <h6 class="text-muted mb-1">Observación de Gestión</h6>
                                                                <div class="bg-light p-3 rounded">
                                                                    <p class="mb-0"><?= htmlspecialchars($donacion['observacion']) ?></p>
                                                                </div>
                                                            </div>
                                                        <?php endif; ?>
                                                        
                                                        <?php if ($donacion['fecha_entrega']): ?>
                                                            <div class="mb-3">
                                                                <h6 class="text-muted mb-1">Fecha de Entrega</h6>
                                                                <p><?= date('d/m/Y', strtotime($donacion['fecha_entrega'])) ?></p>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Modal de gestión -->
                                        <div class="modal fade" id="gestionModal<?= $donacion['id'] ?>" tabindex="-1" aria-labelledby="gestionModalLabel" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered">
                                                <div class="modal-content">
                                                    <div class="modal-header bg-primary text-white">
                                                        <h5 class="modal-title" id="gestionModalLabel">Gestionar Donación</h5>
                                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <form action="<?= BASE_URL ?>/frontend/views/organizacion/donaciones/actualizar.php" method="POST">
                                                        <input type="hidden" name="donacion_id" value="<?= $donacion['id'] ?>">
                                                        <div class="modal-body">
                                                            <div class="mb-3">
                                                                <label class="form-label">Estado</label>
                                                                <select class="form-select" name="estado" required>
                                                                    <option value="pendiente" <?= $donacion['estado'] === 'pendiente' ? 'selected' : '' ?>>Pendiente</option>
                                                                    <option value="confirmada" <?= $donacion['estado'] === 'confirmada' ? 'selected' : '' ?>>Confirmada</option>
                                                                    <option value="entregada" <?= $donacion['estado'] === 'entregada' ? 'selected' : '' ?>>Entregada</option>
                                                                    <option value="cancelada" <?= $donacion['estado'] === 'cancelada' ? 'selected' : '' ?>>Cancelada</option>
                                                                </select>
                                                            </div>
                                                            
                                                            <div class="mb-3" id="fechaEntregaContainer<?= $donacion['id'] ?>" style="<?= $donacion['estado'] === 'entregada' ? '' : 'display: none;' ?>">
                                                                <label for="fecha_entrega<?= $donacion['id'] ?>" class="form-label">Fecha de Entrega</label>
                                                                <input type="date" class="form-control" id="fecha_entrega<?= $donacion['id'] ?>" name="fecha_entrega" value="<?= $donacion['fecha_entrega'] ?>" min="<?= date('Y-m-d') ?>">
                                                            </div>
                                                            
                                                            <div class="mb-3">
                                                                <label for="observacion<?= $donacion['id'] ?>" class="form-label">Observación (opcional)</label>
                                                                <textarea class="form-control" id="observacion<?= $donacion['id'] ?>" name="observacion" rows="2"><?= htmlspecialchars($donacion['observacion'] ?? '') ?></textarea>
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
                                                // Mostrar/ocultar campo de fecha según estado
                                                const estadoSelect<?= $donacion['id'] ?> = document.querySelector('#gestionModal<?= $donacion['id'] ?> select[name="estado"]');
                                                const fechaContainer<?= $donacion['id'] ?> = document.querySelector('#fechaEntregaContainer<?= $donacion['id'] ?>');
                                                
                                                estadoSelect<?= $donacion['id'] ?>.addEventListener('change', function() {
                                                    if (this.value === 'entregada') {
                                                        fechaContainer<?= $donacion['id'] ?>.style.display = 'block';
                                                    } else {
                                                        fechaContainer<?= $donacion['id'] ?>.style.display = 'none';
                                                    }
                                                });
                                            });
                                        </script>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Paginación -->
                <nav aria-label="Page navigation" class="mt-4">
                    <ul class="pagination justify-content-center">
                        <li class="page-item <?= $paginaActual <= 1 ? 'disabled' : '' ?>">
                            <a class="page-link" href="<?= BASE_URL ?>/organizacion/donaciones?pagina=<?= $paginaActual - 1 ?><?= isset($_GET['estado']) ? '&estado='.$_GET['estado'] : '' ?>" aria-label="Previous">
                                <span aria-hidden="true">&laquo;</span>
                            </a>
                        </li>
                        
                        <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
                            <li class="page-item <?= $i == $paginaActual ? 'active' : '' ?>">
                                <a class="page-link" href="<?= BASE_URL ?>/organizacion/donaciones?pagina=<?= $i ?><?= isset($_GET['estado']) ? '&estado='.$_GET['estado'] : '' ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>
                        
                        <li class="page-item <?= $paginaActual >= $totalPaginas ? 'disabled' : '' ?>">
                            <a class="page-link" href="<?= BASE_URL ?>/organizacion/donaciones?pagina=<?= $paginaActual + 1 ?><?= isset($_GET['estado']) ? '&estado='.$_GET['estado'] : '' ?>" aria-label="Next">
                                <span aria-hidden="true">&raquo;</span>
                            </a>
                        </li>
                    </ul>
                </nav>
            <?php endif; ?>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/../../partials/footer.php'; ?>