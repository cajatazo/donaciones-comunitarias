<?php
require_once __DIR__ . '/../../../backend/libs/Database.php';
require_once __DIR__ . '/../../../backend/models/Necesidad.php';
require_once __DIR__ . '/../../../backend/models/Organizacion.php';
require_once __DIR__ . '/../../../backend/models/Donacion.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'organizacion') {
    header('Location: ' . BASE_URL . '/frontend/views/auth/login.php');
    exit();
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
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $necesidadModel = new Necesidad();
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
    if (isset($_POST['cambiar_estado'])) {
        $ok = $necesidadModel->cambiarEstado($_POST['id'], $_POST['nuevo_estado']);
        if ($ok) {
            $_SESSION['success'] = 'Estado de la necesidad actualizado correctamente.';
        } else {
            $_SESSION['error'] = 'Error al cambiar el estado de la necesidad.';
        }
        header('Location: ' . $_SERVER['REQUEST_URI']);
        exit();
    }
}
$org = (new Organizacion())->obtenerPorUsuarioId($_SESSION['user_id']);
$necesidades = [];
if ($org && isset($org['id'])) {
    $necesidades = (new Necesidad())->listarPorOrganizacion($org['id']);
    // Cargar donaciones para cada necesidad
    $donacionModel = new Donacion();
    foreach ($necesidades as &$nec) {
        $nec['donaciones'] = $donacionModel->listarPorNecesidad($nec['id']);
    }
    unset($nec);
}
if (isset($_GET['estado']) && $_GET['estado']) {
    $necesidades = array_filter($necesidades, function($n) {
        return $n['estado'] === $_GET['estado'];
    });
}
if (isset($_GET['categoria']) && $_GET['categoria']) {
    $necesidades = array_filter($necesidades, function($n) {
        return $n['categoria'] === $_GET['categoria'];
    });
}
$pagina_actual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$por_pagina = 10;
$total_necesidades = count($necesidades);
$total_paginas = max(1, ceil($total_necesidades / $por_pagina));
$necesidades = array_slice($necesidades, ($pagina_actual - 1) * $por_pagina, $por_pagina);
?>
<?php require_once __DIR__ . '/../../partials/header.php'; ?>
<?php require_once __DIR__ . '/../../partials/navbar.php'; ?>

<main class="container my-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Historial de Necesidades</h1>
        <div class="dropdown">
            <button class="btn btn-outline-primary dropdown-toggle" type="button" id="filterDropdown" data-bs-toggle="dropdown">
                <i class="fas fa-filter me-2"></i>Filtrar
            </button>
            <ul class="dropdown-menu">
                <li><h6 class="dropdown-header">Por Estado</h6></li>
                <li><a class="dropdown-item" href="<?= BASE_URL ?>/organizacion/historial">Todos</a></li>
                <li><a class="dropdown-item" href="<?= BASE_URL ?>/organizacion/historial?estado=activa">Activas</a></li>
                <li><a class="dropdown-item" href="<?= BASE_URL ?>/organizacion/historial?estado=completada">Completadas</a></li>
                <li><a class="dropdown-item" href="<?= BASE_URL ?>/organizacion/historial?estado=cancelada">Canceladas</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><h6 class="dropdown-header">Por Categoría</h6></li>
                <li><a class="dropdown-item" href="<?= BASE_URL ?>/organizacion/historial?categoria=alimentos">Alimentos</a></li>
                <li><a class="dropdown-item" href="<?= BASE_URL ?>/organizacion/historial?categoria=ropa">Ropa</a></li>
                <li><a class="dropdown-item" href="<?= BASE_URL ?>/organizacion/historial?categoria=materiales_escolares">Materiales Escolares</a></li>
                <li><a class="dropdown-item" href="<?= BASE_URL ?>/organizacion/historial?categoria=medicinas">Medicinas</a></li>
                <li><a class="dropdown-item" href="<?= BASE_URL ?>/organizacion/historial?categoria=otros">Otros</a></li>
            </ul>
        </div>
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
            <?php if (empty($necesidades)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-inbox fa-4x text-muted mb-4"></i>
                    <h4 class="text-muted">No hay necesidades registradas</h4>
                    <p class="text-muted">Puedes publicar nuevas necesidades desde el panel principal</p>
                    <a href="<?= BASE_URL ?>/frontend/views/organizacion/necesidades_crear.php" class="btn btn-primary mt-3">
                        <i class="fas fa-plus me-2"></i>Crear Nueva Necesidad
                    </a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 25%">Necesidad</th>
                                <th style="width: 15%">Categoría</th>
                                <th style="width: 15%">Progreso</th>
                                <th style="width: 15%">Estado</th>
                                <th style="width: 15%">Fecha</th>
                                <th style="width: 15%">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($necesidades as $necesidad): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="flex-shrink-0 bg-primary bg-opacity-10 text-primary rounded-circle p-2 me-3">
                                                <i class="fas <?= 
                                                    $necesidad['categoria'] === 'alimentos' ? 'fa-utensils' : 
                                                    ($necesidad['categoria'] === 'ropa' ? 'fa-tshirt' : 
                                                    ($necesidad['categoria'] === 'materiales_escolares' ? 'fa-book' : 
                                                    ($necesidad['categoria'] === 'medicinas' ? 'fa-pills' : 'fa-hands-helping')))
                                                ?> fa-lg"></i>
                                            </div>
                                            <div class="flex-grow-1">
                                                <h6 class="mb-1"><?= htmlspecialchars($necesidad['titulo']) ?></h6>
                                                <small class="text-muted"><?= substr(htmlspecialchars($necesidad['descripcion']), 0, 50) ?>...</small>
                                            </div>
                                        </div>
                                    </td>
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
                                        <div class="d-flex align-items-center">
                                            <div class="progress flex-grow-1 me-2" style="height: 8px;">
                                                <div class="progress-bar bg-<?php
                                                    $ratio = $necesidad['cantidad_actual'] / $necesidad['cantidad_necesaria'];
                                                    echo ($ratio >= 1) ? 'success' : (($ratio >= 0.5) ? 'info' : 'warning');
                                                ?>" 
                                                role="progressbar" 
                                                style="width: <?= min(100, ($necesidad['cantidad_actual'] / $necesidad['cantidad_necesaria']) * 100) ?>%" 
                                                aria-valuenow="<?= $necesidad['cantidad_actual'] ?>" 
                                                aria-valuemin="0" 
                                                aria-valuemax="<?= $necesidad['cantidad_necesaria'] ?>">
                                                </div>
                                            </div>
                                            <small class="text-nowrap"><?= round(($necesidad['cantidad_actual'] / $necesidad['cantidad_necesaria']) * 100) ?>%</small>
                                        </div>
                                        <small class="text-muted"><?= $necesidad['cantidad_actual'] ?> de <?= $necesidad['cantidad_necesaria'] ?></small>
                                    </td>
                                    <td>
                                        <span class="badge rounded-pill bg-<?php
                                            echo ($necesidad['estado'] === 'activa') ? 'success' : (($necesidad['estado'] === 'completada') ? 'primary' : 'secondary');
                                        ?>">
                                            <?= ucfirst($necesidad['estado']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <small class="text-muted">Publicado:</small>
                                        <div><?= date('d/m/Y', strtotime($necesidad['created_at'])) ?></div>
                                        <small class="text-muted">Límite:</small>
                                        <div><?= date('d/m/Y', strtotime($necesidad['fecha_limite'])) ?></div>
                                    </td>
                                    <td>
                                        <div class="d-flex gap-2">
                                            <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#detalleModal<?= $necesidad['id'] ?>">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#editarModal<?= $necesidad['id'] ?>">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <?php if ($necesidad['estado'] === 'activa'): ?>
                                                <button class="btn btn-sm btn-outline-success" data-bs-toggle="modal" data-bs-target="#confirmarCompletar<?= $necesidad['id'] ?>" title="Marcar como completada">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                <button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#confirmarCancelar<?= $necesidad['id'] ?>" title="Cancelar necesidad">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>

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

                                <!-- Modal de Detalles -->
                                <div class="modal fade" id="detalleModal<?= $necesidad['id'] ?>" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Detalles de Necesidad</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="row mb-4">
                                                    <div class="col-md-8">
                                                        <h4><?= htmlspecialchars($necesidad['titulo']) ?></h4>
                                                        <p class="text-muted"><?= htmlspecialchars($necesidad['descripcion']) ?></p>
                                                        
                                                        <div class="d-flex align-items-center mb-3">
                                                            <span class="badge bg-<?php
                                                                echo ($necesidad['estado'] === 'activa') ? 'success' : (($necesidad['estado'] === 'completada') ? 'primary' : 'secondary');
                                                            ?> me-2">
                                                                <?= ucfirst($necesidad['estado']) ?>
                                                            </span>
                                                            <span class="badge bg-info">
                                                                <?= $categorias[$necesidad['categoria']] ?>
                                                            </span>
                                                        </div>
                                                        
                                                        <div class="row">
                                                            <div class="col-md-6">
                                                                <div class="mb-3">
                                                                    <h6 class="text-muted mb-1">Cantidad Requerida</h6>
                                                                    <p><?= $necesidad['cantidad_necesaria'] ?></p>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <div class="mb-3">
                                                                    <h6 class="text-muted mb-1">Cantidad Recibida</h6>
                                                                    <p><?= $necesidad['cantidad_actual'] ?></p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        
                                                        <div class="row">
                                                            <div class="col-md-6">
                                                                <div class="mb-3">
                                                                    <h6 class="text-muted mb-1">Fecha de Publicación</h6>
                                                                    <p><?= date('d/m/Y H:i', strtotime($necesidad['created_at'])) ?></p>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <div class="mb-3">
                                                                    <h6 class="text-muted mb-1">Fecha Límite</h6>
                                                                    <p><?= date('d/m/Y', strtotime($necesidad['fecha_limite'])) ?></p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="card bg-light">
                                                            <div class="card-body text-center">
                                                                <div class="mb-3">
                                                                    <div class="display-4 fw-bold text-primary">
                                                                        <?= round(($necesidad['cantidad_actual'] / $necesidad['cantidad_necesaria']) * 100) ?>%
                                                                    </div>
                                                                    <p class="text-muted">Completado</p>
                                                                </div>
                                                                <div class="progress" style="height: 10px;">
                                                                    <div class="progress-bar bg-primary" 
                                                                         role="progressbar" 
                                                                         style="width: <?= min(100, ($necesidad['cantidad_actual'] / $necesidad['cantidad_necesaria']) * 100) ?>%" 
                                                                         aria-valuenow="<?= $necesidad['cantidad_actual'] ?>" 
                                                                         aria-valuemin="0" 
                                                                         aria-valuemax="<?= $necesidad['cantidad_necesaria'] ?>">
                                                                    </div>
                                                                </div>
                                                                <small class="text-muted"><?= $necesidad['cantidad_actual'] ?> de <?= $necesidad['cantidad_necesaria'] ?></small>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <hr>
                                                
                                                <h5 class="mb-3">Donaciones Recibidas</h5>
                                                <?php if (empty($necesidad['donaciones'])): ?>
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
                                                                    <th>Comentario</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <?php foreach ($necesidad['donaciones'] as $donacion): ?>
                                                                    <tr>
                                                                        <td><?= htmlspecialchars($donacion['donante_nombre']) ?></td>
                                                                        <td><?= $donacion['cantidad'] ?></td>
                                                                        <td><?= date('d/m/Y', strtotime($donacion['created_at'])) ?></td>
                                                                        <td>
                                                                            <span class="badge bg-<?php
                                                                                echo ($donacion['estado'] === 'pendiente') ? 'warning' : (($donacion['estado'] === 'confirmada') ? 'info' : 'success');
                                                                            ?>">
                                                                                <?= ucfirst($donacion['estado']) ?>
                                                                            </span>
                                                                        </td>
                                                                        <td>
                                                                            <?php if ($donacion['comentario']): ?>
                                                                                <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="tooltip" data-bs-placement="top" title="<?= htmlspecialchars($donacion['comentario']) ?>">
                                                                                    <i class="fas fa-comment"></i>
                                                                                </button>
                                                                            <?php else: ?>
                                                                                <span class="text-muted">Sin comentario</span>
                                                                            <?php endif; ?>
                                                                        </td>
                                                                    </tr>
                                                                <?php endforeach; ?>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Modal Confirmar Completar -->
                                <div class="modal fade" id="confirmarCompletar<?= $necesidad['id'] ?>" tabindex="-1">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content">
                                            <form method="POST">
                                                <input type="hidden" name="id" value="<?= $necesidad['id'] ?>">
                                                <input type="hidden" name="nuevo_estado" value="completada">
                                                <div class="modal-header bg-success bg-opacity-10">
                                                    <h5 class="modal-title text-success"><i class="fas fa-crown me-2"></i>Confirmar acción VIP</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body text-center">
                                                    <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                                                    <p class="fs-5">¿Estás seguro que deseas marcar la necesidad <b><?= htmlspecialchars($necesidad['titulo']) ?></b> como <span class="text-success">completada</span>?</p>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                                    <button type="submit" name="cambiar_estado" class="btn btn-success">Sí, marcar como completada</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                <!-- Modal Confirmar Cancelar -->
                                <div class="modal fade" id="confirmarCancelar<?= $necesidad['id'] ?>" tabindex="-1">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content">
                                            <form method="POST">
                                                <input type="hidden" name="id" value="<?= $necesidad['id'] ?>">
                                                <input type="hidden" name="nuevo_estado" value="cancelada">
                                                <div class="modal-header bg-danger bg-opacity-10">
                                                    <h5 class="modal-title text-danger"><i class="fas fa-crown me-2"></i>Confirmar acción VIP</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body text-center">
                                                    <i class="fas fa-times-circle fa-3x text-danger mb-3"></i>
                                                    <p class="fs-5">¿Estás seguro que deseas <b>cancelar</b> la necesidad <b><?= htmlspecialchars($necesidad['titulo']) ?></b>?</p>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">No, volver</button>
                                                    <button type="submit" name="cambiar_estado" class="btn btn-danger">Sí, cancelar necesidad</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Paginación -->
                <nav aria-label="Page navigation" class="mt-4">
                    <ul class="pagination justify-content-center">
                        <li class="page-item <?= $pagina_actual <= 1 ? 'disabled' : '' ?>">
                            <a class="page-link" href="<?= BASE_URL ?>/organizacion/historial?pagina=<?= $pagina_actual - 1 ?><?= isset($_GET['estado']) ? '&estado='.$_GET['estado'] : '' ?><?= isset($_GET['categoria']) ? '&categoria='.$_GET['categoria'] : '' ?>">
                                <i class="fas fa-chevron-left"></i>
                            </a>
                        </li>
                        
                        <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                            <li class="page-item <?= $i == $pagina_actual ? 'active' : '' ?>">
                                <a class="page-link" href="<?= BASE_URL ?>/organizacion/historial?pagina=<?= $i ?><?= isset($_GET['estado']) ? '&estado='.$_GET['estado'] : '' ?><?= isset($_GET['categoria']) ? '&categoria='.$_GET['categoria'] : '' ?>">
                                    <?= $i ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                        
                        <li class="page-item <?= $pagina_actual >= $total_paginas ? 'disabled' : '' ?>">
                            <a class="page-link" href="<?= BASE_URL ?>/organizacion/historial?pagina=<?= $pagina_actual + 1 ?><?= isset($_GET['estado']) ? '&estado='.$_GET['estado'] : '' ?><?= isset($_GET['categoria']) ? '&categoria='.$_GET['categoria'] : '' ?>">
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        </li>
                    </ul>
                </nav>
            <?php endif; ?>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/../../partials/footer.php'; ?>