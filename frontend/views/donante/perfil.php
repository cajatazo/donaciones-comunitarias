<?php
require_once __DIR__ . '/../../../backend/models/Usuario.php';
require_once __DIR__ . '/../../../backend/models/Donacion.php';
require_once __DIR__ . '/../../partials/header.php';
require_once __DIR__ . '/../../partials/navbar.php';

if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'donante') {
    header('Location: ' . BASE_URL . '/frontend/views/auth/login.php');
    exit();
}

$usuarioModel = new Usuario();
$donante = $usuarioModel->obtenerPorId($_SESSION['user_id']);
$donaciones = (new Donacion())->listarPorDonante($donante['id']);

// Actualización de datos personales
$mensaje = '';
$mensaje_tipo = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['actualizar_perfil'])) {
    $nombre = $_POST['nombre'];
    $email = $_POST['email'];
    if ($usuarioModel->actualizar($donante['id'], $nombre, $email)) {
        $_SESSION['mensaje'] = 'Datos actualizados correctamente.';
        $_SESSION['mensaje_tipo'] = 'success';
        $_SESSION['user_name'] = $nombre;
        // Redirigir para evitar doble envío
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit();
    } else {
        $_SESSION['mensaje'] = 'Error al actualizar los datos.';
        $_SESSION['mensaje_tipo'] = 'danger';
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit();
    }
}
// Cambio de contraseña
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cambiar_password'])) {
    $password = $_POST['password'];
    $confirm = $_POST['confirm_password'];
    if ($password !== $confirm) {
        $_SESSION['mensaje'] = 'Las contraseñas no coinciden.';
        $_SESSION['mensaje_tipo'] = 'danger';
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit();
    } else {
        $hashed = password_hash($password, PASSWORD_BCRYPT);
        $db = Database::getConnectionStatic();
        $stmt = $db->prepare('UPDATE usuarios SET password = ? WHERE id = ?');
        if ($stmt->execute([$hashed, $donante['id']])) {
            $_SESSION['mensaje'] = 'Contraseña actualizada correctamente.';
            $_SESSION['mensaje_tipo'] = 'success';
            header('Location: ' . $_SERVER['PHP_SELF']);
            exit();
        } else {
            $_SESSION['mensaje'] = 'Error al actualizar la contraseña.';
            $_SESSION['mensaje_tipo'] = 'danger';
            header('Location: ' . $_SERVER['PHP_SELF']);
            exit();
        }
    }
}
if (isset($_SESSION['mensaje'])) {
    $mensaje = $_SESSION['mensaje'];
    $mensaje_tipo = $_SESSION['mensaje_tipo'];
    unset($_SESSION['mensaje'], $_SESSION['mensaje_tipo']);
}
?>
<main class="container-fluid my-4">
    <div class="row justify-content-center">
        <!-- SIDEBAR -->
        <div class="col-12 col-md-3 col-lg-2 mb-3 mb-md-0">
            <nav class="bg-light sidebar py-4 rounded shadow-sm h-100">
                <div class="position-sticky">
                    <ul class="nav flex-column gap-2">
                        <li class="nav-item"><button class="nav-link active w-100 text-start" id="btnResumen" onclick="showSection('resumen')"><i class="fas fa-home me-2"></i>Resumen</button></li>
                        <li class="nav-item"><button class="nav-link w-100 text-start" id="btnEstadisticas" onclick="showSection('estadisticas')"><i class="fas fa-chart-bar me-2"></i>Estadísticas</button></li>
                        <li class="nav-item"><button class="nav-link w-100 text-start" id="btnAccesos" onclick="showSection('accesos')"><i class="fas fa-bolt me-2"></i>Accesos Rápidos</button></li>
                        <li class="nav-item"><button class="nav-link w-100 text-start" id="btnHistorial" onclick="showSection('historial')"><i class="fas fa-history me-2"></i>Historial de Donaciones</button></li>
                        <li class="nav-item"><button class="nav-link w-100 text-start" id="btnEditar" onclick="showSection('editar')"><i class="fas fa-user-edit me-2"></i>Editar Perfil</button></li>
                        <li class="nav-item"><button class="nav-link w-100 text-start" id="btnPassword" onclick="showSection('password')"><i class="fas fa-key me-2"></i>Cambiar Contraseña</button></li>
                        <li class="nav-item"><button class="nav-link w-100 text-start" id="btnAyuda" onclick="showSection('ayuda')"><i class="fas fa-question-circle me-2"></i>Ayuda/FAQ</button></li>
                    </ul>
                </div>
            </nav>
        </div>
        <!-- PANEL PRINCIPAL -->
        <div class="col-12 col-md-9 col-lg-10">
            <div class="row">
                <!-- Panel de Estadísticas SIEMPRE ARRIBA -->
                <div class="col-12">
                    <div id="estadisticas-panel" class="mb-4">
                        <section class="h-100">
                            <div class="card shadow-sm h-100">
                                <div class="card-body">
                                    <h2 class="mb-4">Estadísticas de Donaciones</h2>
                                    <div class="row text-center">
                                        <div class="col-6 col-md-4 mb-2">
                                            <div class="card bg-primary text-white h-100"><div class="card-body p-2"><i class="fas fa-hand-holding-heart fa-2x mb-2"></i><div class="fw-bold fs-4"><?= count($donaciones) ?></div><div>Donaciones Realizadas</div></div></div>
                                        </div>
                                        <div class="col-6 col-md-4 mb-2">
                                            <div class="card bg-success text-white h-100"><div class="card-body p-2"><i class="fas fa-gift fa-2x mb-2"></i><div class="fw-bold fs-4"><?= count(array_filter($donaciones, fn($d) => $d['estado'] === 'entregada')) ?></div><div>Entregadas</div></div></div>
                                        </div>
                                        <div class="col-6 col-md-4 mb-2">
                                            <div class="card bg-warning text-dark h-100"><div class="card-body p-2"><i class="fas fa-clock fa-2x mb-2"></i><div class="fw-bold fs-4"><?= count(array_filter($donaciones, fn($d) => $d['estado'] === 'pendiente')) ?></div><div>Pendientes</div></div></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </section>
                    </div>
                </div>
                <!-- Panel de control y secciones debajo de estadísticas -->
                <div class="col-12" id="main-panel">
                    <?php if ($mensaje): ?>
                        <div class="alert alert-<?= $mensaje_tipo ?>"> <?= $mensaje ?> </div>
                    <?php endif; ?>
                    <!-- SECCIÓN RESUMEN -->
                    <section id="section-resumen" class="dashboard-section">
                        <h2 class="mb-4">Resumen</h2>
                        <div class="card mb-4">
                            <div class="card-header bg-primary text-white">Bienvenido, <?= htmlspecialchars($donante['nombre']) ?></div>
                            <div class="card-body">
                                <p class="mb-0">Desde este panel puedes gestionar tus donaciones, ver tu historial, actualizar tus datos y acceder a necesidades de la comunidad.</p>
                            </div>
                        </div>
                    </section>
                    <!-- SECCIÓN ACCESOS RÁPIDOS -->
                    <section id="section-accesos" class="dashboard-section d-none">
                        <h2 class="mb-4">Accesos Rápidos</h2>
                        <div class="card"><div class="card-header bg-success text-white">Accesos Rápidos</div><div class="card-body d-flex flex-wrap gap-2 justify-content-center">
                            <a href="<?= BASE_URL ?>/frontend/views/donante/historial.php" class="btn btn-outline-primary">Ver Historial de Donaciones</a>
                            <a href="<?= BASE_URL ?>/frontend/views/donante/donaciones.php" class="btn btn-outline-success">Realizar Nueva Donación</a>
                            <a href="<?= BASE_URL ?>/frontend/views/necesidades.php" class="btn btn-outline-info">Ver Necesidades</a>
                        </div></div>
                    </section>
                    <!-- SECCIÓN HISTORIAL DE DONACIONES -->
                    <section id="section-historial" class="dashboard-section d-none">
                        <h2 class="mb-4">Historial Reciente de Donaciones</h2>
                        <div class="card"><div class="card-header bg-dark text-white">Donaciones</div><div class="card-body">
                            <?php if (empty($donaciones)): ?>
                                <p class="text-muted">No has realizado donaciones aún.</p>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Fecha</th>
                                                <th>Necesidad</th>
                                                <th>Organización</th>
                                                <th>Cantidad</th>
                                                <th>Estado</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach (array_slice($donaciones, 0, 10) as $d): ?>
                                                <tr>
                                                    <td><?= date('d/m/Y', strtotime($d['created_at'])) ?></td>
                                                    <td><?= htmlspecialchars($d['necesidad_titulo']) ?></td>
                                                    <td><?= htmlspecialchars($d['organizacion_nombre']) ?></td>
                                                    <td><?= $d['cantidad'] ?></td>
                                                    <td><span class="badge bg-<?= $d['estado'] === 'pendiente' ? 'warning' : ($d['estado'] === 'confirmada' ? 'info' : ($d['estado'] === 'entregada' ? 'success' : 'secondary')) ?>"><?= ucfirst($d['estado']) ?></span></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div></div>
                    </section>
                    <!-- SECCIÓN EDITAR PERFIL -->
                    <section id="section-editar" class="dashboard-section d-none">
                        <h2 class="mb-4">Editar Datos de Donante</h2>
                        <div class="card"><div class="card-header bg-secondary text-white">Editar Perfil</div><div class="card-body">
                            <form method="POST">
                                <div class="mb-3"><label class="form-label">Nombre</label><input type="text" class="form-control" name="nombre" value="<?= htmlspecialchars($donante['nombre']) ?>" required></div>
                                <div class="mb-3"><label class="form-label">Correo electrónico</label><input type="email" class="form-control" name="email" value="<?= htmlspecialchars($donante['email']) ?>" required></div>
                                <button type="submit" name="actualizar_perfil" class="btn btn-primary">Actualizar Datos</button>
                            </form>
                        </div></div>
                    </section>
                    <!-- SECCIÓN CAMBIAR CONTRASEÑA -->
                    <section id="section-password" class="dashboard-section d-none">
                        <h2 class="mb-4">Cambiar Contraseña</h2>
                        <div class="card"><div class="card-header bg-secondary text-white">Cambiar Contraseña</div><div class="card-body">
                            <form method="POST">
                                <div class="mb-3"><label class="form-label">Nueva Contraseña</label><input type="password" class="form-control" name="password" required></div>
                                <div class="mb-3"><label class="form-label">Confirmar Contraseña</label><input type="password" class="form-control" name="confirm_password" required></div>
                                <button type="submit" name="cambiar_password" class="btn btn-secondary">Cambiar Contraseña</button>
                            </form>
                        </div></div>
                    </section>
                    <!-- SECCIÓN AYUDA/FAQ -->
                    <section id="section-ayuda" class="dashboard-section d-none">
                        <h2 class="mb-4">Ayuda y Preguntas Frecuentes</h2>
                        <div class="card"><div class="card-header bg-dark text-white">Ayuda/FAQ</div><div class="card-body">
                            <ul>
                                <li><strong>¿Cómo realizo una donación?</strong> Usa el acceso rápido correspondiente.</li>
                                <li><strong>¿Cómo veo mi historial?</strong> Usa el acceso rápido de historial.</li>
                                <li><strong>¿Cómo contacto soporte?</strong> Escribe a <a href="mailto:soporte@donacionesperu.com">soporte@donacionesperu.com</a></li>
                            </ul>
                        </div></div>
                    </section>
                </div>
            </div>
        </div>
    </div>
</main>
<script>
function showSection(section) {
    var activeBtn = document.getElementById('btn' + section.charAt(0).toUpperCase() + section.slice(1));
    if (activeBtn.classList.contains('active')) return; // Ya está activa, no hacer nada
    document.querySelectorAll('.sidebar .nav-link').forEach(btn => btn.disabled = true);
    setTimeout(() => { document.querySelectorAll('.sidebar .nav-link').forEach(btn => btn.disabled = false); }, 400);
    document.querySelectorAll('.dashboard-section').forEach(s => s.classList.add('d-none'));
    if(section !== 'estadisticas') {
        document.getElementById('main-panel').classList.remove('d-none');
        document.getElementById('section-' + section).classList.remove('d-none');
    } else {
        document.getElementById('main-panel').classList.add('d-none');
    }
    document.querySelectorAll('.sidebar .nav-link').forEach(btn => {
        btn.classList.remove('active', 'bg-primary', 'text-white');
    });
    activeBtn.classList.add('active', 'bg-primary', 'text-white');
}
document.addEventListener('DOMContentLoaded', function() {
    showSection('resumen');
});
</script>
<?php if ($mensaje): ?>
<style>
.vip-alert-float {
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%) scale(1);
    z-index: 4000;
    min-width: 350px;
    max-width: 95vw;
    box-shadow: 0 16px 64px rgba(0,0,0,0.45);
    border-radius: 2.5rem;
    animation: vipfadein 0.7s cubic-bezier(.68,-0.55,.27,1.55);
    background: linear-gradient(135deg, #fffbe6 0%, #e6fff9 100%);
    border: 4px solid #00e6b8;
    color: #4a148c;
    font-family: 'Poppins', 'Roboto', sans-serif;
    letter-spacing: 1px;
}
@keyframes vipfadein {
    0% { opacity: 0; transform: translate(-50%, -60%) scale(0.7); }
    100% { opacity: 1; transform: translate(-50%, -50%) scale(1); }
}
.vip-alert-float .fa-crown {
    font-size: 3.5rem;
    margin-bottom: 0.7rem;
    color: #ffc107;
    text-shadow: 0 2px 12px #fff176, 0 0 24px #00e6b8;
    animation: crownpop 0.8s cubic-bezier(.68,-0.55,.27,1.55);
}
@keyframes crownpop {
    0% { transform: scale(0.5) rotate(-20deg); }
    80% { transform: scale(1.3) rotate(10deg); }
    100% { transform: scale(1) rotate(0deg); }
}
.vip-alert-float .btn-close {
    filter: invert(1) grayscale(1);
}
</style>
<div class="vip-alert-float alert alert-<?= $mensaje_tipo ?> alert-dismissible fade show text-center py-5 fs-2 fw-bold" role="alert">
    <i class="fas fa-crown"></i><br>
    <?= $mensaje ?>
    <button type="button" class="btn-close position-absolute top-0 end-0 m-3" data-bs-dismiss="alert" aria-label="Cerrar"></button>
</div>
<?php endif; ?>
<?php require_once __DIR__ . '/../../partials/footer.php'; ?> 