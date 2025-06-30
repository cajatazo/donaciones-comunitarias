<?php
require_once __DIR__ . '/../../../backend/libs/Database.php';
require_once __DIR__ . '/../../../backend/models/Organizacion.php';
require_once __DIR__ . '/../../../backend/models/Usuario.php';
$organizaciones = [];
$mensaje = '';
$mensaje_tipo = '';
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: ' . BASE_URL . '/frontend/views/auth/login.php');
    exit();
}
// CRUD acciones
$orgModel = new Organizacion();
$usuarioModel = new Usuario();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Crear
    if (isset($_POST['crear_org'])) {
        $email = trim($_POST['email']);
        $usuario = $usuarioModel->obtenerPorEmail($email);
        if (!$usuario) {
            // Crear usuario nuevo
            if (!empty($_POST['nuevo_nombre']) && !empty($_POST['nuevo_password'])) {
                $okUser = $usuarioModel->registrar($_POST['nuevo_nombre'], $email, $_POST['nuevo_password'], 'organizacion');
                if ($okUser) {
                    $usuario = $usuarioModel->obtenerPorEmail($email);
                } else {
                    $mensaje = 'No se pudo crear el usuario.';
                    $mensaje_tipo = 'danger';
                }
            } else {
                $mensaje = 'Usuario no encontrado. Complete nombre y contraseña para crear uno nuevo.';
                $mensaje_tipo = 'danger';
            }
        }
        if ($usuario) {
            $ok = $orgModel->registrar($usuario['id'], $_POST['nombre'], $_POST['ruc'], $_POST['direccion'], $_POST['telefono'], $_POST['descripcion']);
            if ($ok) {
                $mensaje = 'Organización creada correctamente.';
                $mensaje_tipo = 'success';
            } else {
                $mensaje = 'Error al crear la organización.';
                $mensaje_tipo = 'danger';
            }
        }
    }
    // Editar
    if (isset($_POST['editar_org'])) {
        $ok = $orgModel->actualizar($_POST['org_id'], $_POST['nombre'], $_POST['ruc'], $_POST['direccion'], $_POST['telefono'], $_POST['descripcion']);
        if ($ok) {
            $mensaje = 'Organización actualizada correctamente.';
            $mensaje_tipo = 'success';
        } else {
            $mensaje = 'Error al actualizar la organización.';
            $mensaje_tipo = 'danger';
        }
    }
    // Eliminar
    if (isset($_POST['eliminar_org'])) {
        $db = Database::getConnectionStatic();
        $stmt = $db->prepare('DELETE FROM organizaciones WHERE id = ?');
        if ($stmt->execute([$_POST['org_id']])) {
            $mensaje = 'Organización eliminada correctamente.';
            $mensaje_tipo = 'success';
        } else {
            $mensaje = 'Error al eliminar la organización.';
            $mensaje_tipo = 'danger';
        }
    }
    // Verificar/Desverificar
    if (isset($_POST['toggle_verificada'])) {
        $ok = $orgModel->verificar($_POST['org_id'], $_POST['nuevo_estado']);
        if ($ok) {
            $mensaje = $_POST['nuevo_estado'] ? 'Organización verificada.' : 'Organización marcada como "Por Verificar".';
            $mensaje_tipo = 'success';
        } else {
            $mensaje = 'Error al cambiar el estado de verificación.';
            $mensaje_tipo = 'danger';
        }
    }
    // PRG
    $_SESSION['success'] = $mensaje_tipo === 'success' ? $mensaje : null;
    $_SESSION['error'] = $mensaje_tipo === 'danger' ? $mensaje : null;
    header('Location: ' . $_SERVER['REQUEST_URI']);
    exit();
}
if (isset($_GET['estado'])) {
    $organizaciones = $orgModel->listar($_GET['estado']);
} else {
    $organizaciones = $orgModel->listar();
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
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Organizaciones Registradas</h1>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#crearOrgModal"><i class="fas fa-plus me-2"></i>Crear Organización</button>
        <div class="dropdown ms-2">
            <button class="btn btn-outline-secondary dropdown-toggle" type="button" id="filterOrgDropdown" data-bs-toggle="dropdown">
                <?= isset($_GET['estado']) ? ($_GET['estado'] == 1 ? 'Verificadas' : 'Por Verificar') : 'Todas' ?>
            </button>
            <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="<?= BASE_URL ?>/frontend/views/admin/organizaciones.php">Todas</a></li>
                <li><a class="dropdown-item" href="<?= BASE_URL ?>/frontend/views/admin/organizaciones.php?estado=1">Verificadas</a></li>
                <li><a class="dropdown-item" href="<?= BASE_URL ?>/frontend/views/admin/organizaciones.php?estado=0">Por Verificar</a></li>
            </ul>
        </div>
    </div>

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
    .vip-alert-float-admin .fa-shield-alt {
        font-size: 2.2rem;
        color: #8e24aa;
        margin-left: 0.5rem;
        animation: shieldpop 1s cubic-bezier(.68,-0.55,.27,1.55);
    }
    @keyframes crownpopadmin {
        0% { transform: scale(0.5) rotate(-20deg); }
        80% { transform: scale(1.3) rotate(10deg); }
        100% { transform: scale(1) rotate(0deg); }
    }
    @keyframes shieldpop {
        0% { transform: scale(0.5) rotate(10deg); }
        80% { transform: scale(1.2) rotate(-10deg); }
        100% { transform: scale(1) rotate(0deg); }
    }
    .vip-alert-float-admin .btn-close {
        filter: invert(1) grayscale(1);
    }
    </style>
    <div class="vip-alert-float-admin alert alert-<?= $mensaje_tipo ?> alert-dismissible fade show text-center py-5 fs-2 fw-bold" role="alert">
        <i class="fas fa-crown"></i><i class="fas fa-shield-alt"></i><br>
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
                            <th>Nombre</th>
                            <th>RUC</th>
                            <th>Email</th>
                            <th>Teléfono</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($organizaciones)): ?>
                            <tr>
                                <td colspan="6" class="text-center">No hay organizaciones registradas</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($organizaciones as $org): ?>
                                <tr>
                                    <td><?= htmlspecialchars($org['nombre']) ?></td>
                                    <td><?= htmlspecialchars($org['ruc']) ?></td>
                                    <td><?= htmlspecialchars($org['email']) ?></td>
                                    <td><?= htmlspecialchars($org['telefono']) ?></td>
                                    <td>
                                        <span class="badge bg-<?= $org['verificada'] ? 'success' : 'warning' ?>">
                                            <?= $org['verificada'] ? 'Verificada' : 'Por Verificar' ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="d-flex gap-2 flex-wrap">
                                            <form method="POST" style="display:inline-block">
                                                <input type="hidden" name="org_id" value="<?= $org['id'] ?>">
                                                <input type="hidden" name="nuevo_estado" value="<?= $org['verificada'] ? 0 : 1 ?>">
                                                <button type="submit" name="toggle_verificada" class="btn btn-sm btn-<?= $org['verificada'] ? 'warning' : 'success' ?>">
                                                    <?= $org['verificada'] ? 'Desmarcar' : 'Verificar' ?>
                                                </button>
                                            </form>
                                            <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editarOrgModal<?= $org['id'] ?>">Editar</button>
                                            <form method="POST" style="display:inline-block" onsubmit="return confirm('¿Seguro que deseas eliminar esta organización?');">
                                                    <input type="hidden" name="org_id" value="<?= $org['id'] ?>">
                                                <button type="submit" name="eliminar_org" class="btn btn-sm btn-danger">Eliminar</button>
                                                </form>
                                            <button class="btn btn-sm btn-outline-info" data-bs-toggle="modal" data-bs-target="#orgModal<?= $org['id'] ?>">Detalles</button>
                                        </div>
                                        
                                        <!-- Modal Editar -->
                                        <div class="modal fade" id="editarOrgModal<?= $org['id'] ?>" tabindex="-1">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <form method="POST">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Editar Organización</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <input type="hidden" name="org_id" value="<?= $org['id'] ?>">
                                                            <div class="mb-3"><label class="form-label">Nombre</label><input type="text" class="form-control" name="nombre" value="<?= htmlspecialchars($org['nombre']) ?>" required></div>
                                                            <div class="mb-3"><label class="form-label">RUC</label><input type="text" class="form-control" name="ruc" value="<?= htmlspecialchars($org['ruc']) ?>" required></div>
                                                            <div class="mb-3"><label class="form-label">Dirección</label><input type="text" class="form-control" name="direccion" value="<?= htmlspecialchars($org['direccion']) ?>" required></div>
                                                            <div class="mb-3"><label class="form-label">Teléfono</label><input type="text" class="form-control" name="telefono" value="<?= htmlspecialchars($org['telefono']) ?>" required></div>
                                                            <div class="mb-3"><label class="form-label">Descripción</label><textarea class="form-control" name="descripcion" required><?= htmlspecialchars($org['descripcion']) ?></textarea></div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                                            <button type="submit" name="editar_org" class="btn btn-primary">Guardar Cambios</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- Modal Detalles -->
                                        <div class="modal fade" id="orgModal<?= $org['id'] ?>" tabindex="-1">
                                            <div class="modal-dialog modal-lg">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title"><?= htmlspecialchars($org['nombre']) ?></h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="row">
                                                            <div class="col-md-6">
                                                                <p><strong>RUC:</strong> <?= htmlspecialchars($org['ruc']) ?></p>
                                                                <p><strong>Email:</strong> <?= htmlspecialchars($org['email']) ?></p>
                                                                <p><strong>Teléfono:</strong> <?= htmlspecialchars($org['telefono']) ?></p>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <p><strong>Dirección:</strong></p>
                                                                <p><?= htmlspecialchars($org['direccion']) ?></p>
                                                            </div>
                                                        </div>
                                                        <hr>
                                                        <h6>Descripción:</h6>
                                                        <p><?= htmlspecialchars($org['descripcion']) ?></p>
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
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <!-- Modal Crear -->
    <div class="modal fade" id="crearOrgModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title">Crear Organización</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3"><label class="form-label">Nombre</label><input type="text" class="form-control" name="nombre" required></div>
                        <div class="mb-3"><label class="form-label">RUC</label><input type="text" class="form-control" name="ruc" required></div>
                        <div class="mb-3"><label class="form-label">Dirección</label><input type="text" class="form-control" name="direccion" required></div>
                        <div class="mb-3"><label class="form-label">Teléfono</label><input type="text" class="form-control" name="telefono" required></div>
                        <div class="mb-3"><label class="form-label">Email usuario (asociar o crear)</label><input type="email" class="form-control" name="email" required></div>
                        <div class="mb-3"><label class="form-label">Nombre usuario (si es nuevo)</label><input type="text" class="form-control" name="nuevo_nombre"></div>
                        <div class="mb-3"><label class="form-label">Contraseña usuario (si es nuevo)</label><input type="password" class="form-control" name="nuevo_password"></div>
                        <div class="mb-3"><label class="form-label">Descripción</label><textarea class="form-control" name="descripcion" required></textarea></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" name="crear_org" class="btn btn-primary">Crear</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/../../partials/footer.php'; ?>