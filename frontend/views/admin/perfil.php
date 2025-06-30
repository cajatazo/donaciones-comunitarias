<?php
require_once __DIR__ . '/../../../backend/models/Usuario.php';
require_once __DIR__ . '/../../../backend/models/Organizacion.php';
require_once __DIR__ . '/../../../backend/models/Donacion.php';
require_once __DIR__ . '/../../../backend/models/Necesidad.php';
require_once __DIR__ . '/../../partials/header.php';
require_once __DIR__ . '/../../partials/navbar.php';

if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: ' . BASE_URL . '/frontend/views/auth/login.php');
    exit();
}

$usuarioModel = new Usuario();
$admin = $usuarioModel->obtenerPorId($_SESSION['user_id']);
$organizaciones = (new Organizacion())->listar();
$donaciones = (new Donacion())->listarTodas();
$necesidades = (new Necesidad())->listarActivas();

// Actualización de datos personales
$mensaje = '';
$mensaje_tipo = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['actualizar_perfil'])) {
    $nombre = $_POST['nombre'];
    $email = $_POST['email'];
    if ($usuarioModel->actualizar($admin['id'], $nombre, $email)) {
        $_SESSION['mensaje'] = 'Datos actualizados correctamente.';
        $_SESSION['mensaje_tipo'] = 'success';
        $_SESSION['user_name'] = $nombre;
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
        if ($stmt->execute([$hashed, $admin['id']])) {
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
                        <li class="nav-item"><button class="nav-link w-100 text-start" id="btnDonaciones" onclick="showSection('donaciones')"><i class="fas fa-hand-holding-heart me-2"></i>Últimas Donaciones</button></li>
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
                                    <h2 class="mb-4">Estadísticas Globales</h2>
                                    <div class="row text-center">
                                        <div class="col-6 col-md-4 mb-2">
                                            <div class="card bg-primary text-white h-100"><div class="card-body p-2"><i class="fas fa-users fa-2x mb-2"></i><div class="fw-bold fs-4"><?= count($organizaciones) ?></div><div>Organizaciones</div></div></div>
                                        </div>
                                        <div class="col-6 col-md-4 mb-2">
                                            <div class="card bg-success text-white h-100"><div class="card-body p-2"><i class="fas fa-hand-holding-heart fa-2x mb-2"></i><div class="fw-bold fs-4"><?= count($donaciones) ?></div><div>Donaciones</div></div></div>
                                        </div>
                                        <div class="col-6 col-md-4 mb-2">
                                            <div class="card bg-info text-white h-100"><div class="card-body p-2"><i class="fas fa-list-alt fa-2x mb-2"></i><div class="fw-bold fs-4"><?= count($necesidades) ?></div><div>Necesidades Activas</div></div></div>
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
                            <div class="card-header bg-primary text-white">Bienvenido, <?= htmlspecialchars($admin['nombre']) ?></div>
                            <div class="card-body">
                                <p class="mb-0">Desde este panel puedes gestionar toda la plataforma, revisar estadísticas, acceder a reportes y administrar usuarios, organizaciones y donaciones.</p>
                            </div>
                        </div>
                    </section>
                    <!-- SECCIÓN ACCESOS RÁPIDOS -->
                    <section id="section-accesos" class="dashboard-section d-none">
                        <h2 class="mb-4">Accesos Rápidos</h2>
                        <div class="card"><div class="card-header bg-success text-white">Accesos Rápidos</div><div class="card-body d-flex flex-wrap gap-2 justify-content-center">
                            <a href="<?= BASE_URL ?>/frontend/views/admin/organizaciones.php" class="btn btn-outline-primary">Gestionar Organizaciones</a>
                            <a href="<?= BASE_URL ?>/frontend/views/admin/donaciones.php" class="btn btn-outline-success">Gestionar Donaciones</a>
                            <a href="<?= BASE_URL ?>/frontend/views/admin/reportes.php" class="btn btn-outline-info">Ver Reportes</a>
                            <a href="<?= BASE_URL ?>/frontend/views/necesidades.php" class="btn btn-outline-secondary">Ver Necesidades</a>
                        </div></div>
                    </section>
                    <!-- SECCIÓN ÚLTIMAS DONACIONES -->
                    <section id="section-donaciones" class="dashboard-section d-none">
                        <h2 class="mb-4">Últimas Donaciones</h2>
                        <div class="card"><div class="card-header bg-dark text-white">Donaciones</div><div class="card-body">
                            <?php if (empty($donaciones)): ?>
                                <p class="text-muted">No hay donaciones recientes.</p>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Fecha</th>
                                                <th>Donante</th>
                                                <th>Organización</th>
                                                <th>Necesidad</th>
                                                <th>Cantidad</th>
                                                <th>Estado</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach (array_slice($donaciones, 0, 10) as $d): ?>
                                                <tr>
                                                    <td><?= date('d/m/Y H:i', strtotime($d['created_at'])) ?></td>
                                                    <td><?= htmlspecialchars($d['donante_nombre']) ?></td>
                                                    <td><?= htmlspecialchars($d['organizacion_nombre']) ?></td>
                                                    <td><?= htmlspecialchars($d['necesidad_titulo']) ?></td>
                                                    <td><?= $d['cantidad'] ?></td>
                                                    <td><span class="badge bg-<?= $d['estado'] === 'pendiente' ? 'warning' : ($d['estado'] === 'confirmada' ? 'info' : 'success') ?>"><?= ucfirst($d['estado']) ?></span></td>
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
                        <h2 class="mb-4">Editar Datos de Administrador</h2>
                        <div class="card"><div class="card-header bg-secondary text-white">Editar Perfil</div><div class="card-body">
                            <form method="POST">
                                <div class="mb-3"><label class="form-label">Nombre</label><input type="text" class="form-control" name="nombre" value="<?= htmlspecialchars($admin['nombre']) ?>" required></div>
                                <div class="mb-3"><label class="form-label">Correo electrónico</label><input type="email" class="form-control" name="email" value="<?= htmlspecialchars($admin['email']) ?>" required></div>
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
                                <li><strong>¿Cómo gestiono organizaciones?</strong> Usa el acceso rápido correspondiente.</li>
                                <li><strong>¿Cómo veo reportes?</strong> Usa el acceso rápido de reportes.</li>
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
<?php require_once __DIR__ . '/../../partials/footer.php'; ?> 