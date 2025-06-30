<?php
require_once __DIR__ . '/../../../backend/libs/Database.php';
require_once __DIR__ . '/../../../backend/models/Organizacion.php';
require_once __DIR__ . '/../../../backend/models/Usuario.php';
require_once __DIR__ . '/../../../backend/models/Necesidad.php';
require_once __DIR__ . '/../../../backend/models/Donacion.php';
require_once __DIR__ . '/../../partials/header.php';
require_once __DIR__ . '/../../partials/navbar.php';

if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'organizacion') {
    header('Location: ' . BASE_URL . '/frontend/views/auth/login.php');
    exit();
}

$usuarioModel = new Usuario();
$orgModel = new Organizacion();
$necesidadModel = new Necesidad();
$donacionModel = new Donacion();

$usuario = $usuarioModel->obtenerPorId($_SESSION['user_id']);
$org = $orgModel->obtenerPorUsuarioId($_SESSION['user_id']);
if (!is_array($usuario) || !$usuario) {
    echo '<div class="alert alert-danger">Error: No se encontró el usuario en la base de datos.</div>';
    require_once __DIR__ . '/../../partials/footer.php';
    exit;
}
if (!is_array($org) || !$org) {
    // Crear organización automáticamente si no existe
    $orgModel->registrar(
        $usuario['id'],
        $usuario['nombre'] . ' (Org)',
        '20123456789',
        'Dirección por defecto',
        '999999999',
        'Organización creada automáticamente (usted no ha creado una organizacion corectamente por favor contacte con el administrador de la plataforma para que se le asigne una organizacion y este mensaje fue creado por el sistema). Puedes actualizar tambien tus datos de perfil ahi es lo que se va actualizar tu organizacion.',
        1
    );
    // Recargar para tomar los datos nuevos
    header('Location: ' . $_SERVER['REQUEST_URI']);
    exit;
}
$necesidades = $necesidadModel->listarPorOrganizacion($org['id']);
$donaciones = $donacionModel->listarPorOrganizacion($org['id']);

// Actualización de datos personales y de organización
$mensaje = '';
$mensaje_tipo = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['actualizar_perfil'])) {
    $nombre = $_POST['nombre'];
    $email = $_POST['email'];
    $org_nombre = $_POST['org_nombre'];
    $ruc = $_POST['ruc'];
    $direccion = $_POST['direccion'];
    $telefono = $_POST['telefono'];
    $descripcion = $_POST['descripcion'];
    $ok1 = $usuarioModel->actualizar($usuario['id'], $nombre, $email);
    $ok2 = $orgModel->actualizar($org['id'], $org_nombre, $ruc, $direccion, $telefono, $descripcion);
    if ($ok1 && $ok2) {
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
        if ($stmt->execute([$hashed, $usuario['id']])) {
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
// Exportar CSV
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['exportar_necesidades'])) {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="necesidades.csv"');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['Título', 'Categoría', 'Cantidad Actual', 'Cantidad Necesaria', 'Estado', 'Fecha Publicación']);
    foreach ($necesidades as $n) {
        fputcsv($out, [$n['titulo'], $n['categoria'], $n['cantidad_actual'], $n['cantidad_necesaria'], $n['estado'], $n['created_at']]);
    }
    fclose($out);
    exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['exportar_donaciones'])) {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="donaciones.csv"');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['Donante', 'Necesidad', 'Cantidad', 'Estado', 'Fecha']);
    foreach ($donaciones as $d) {
        fputcsv($out, [$d['donante_nombre'], $d['necesidad_titulo'], $d['cantidad'], $d['estado'], $d['created_at']]);
    }
    fclose($out);
    exit;
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
                        <li class="nav-item"><button class="nav-link w-100 text-start" id="btnGraficos" onclick="showSection('graficos')"><i class="fas fa-chart-pie me-2"></i>Gráficos</button></li>
                        <li class="nav-item"><button class="nav-link w-100 text-start" id="btnAccesos" onclick="showSection('accesos')"><i class="fas fa-bolt me-2"></i>Accesos Rápidos</button></li>
                        <li class="nav-item"><button class="nav-link w-100 text-start" id="btnRanking" onclick="showSection('ranking')"><i class="fas fa-trophy me-2"></i>Ranking</button></li>
                        <li class="nav-item"><button class="nav-link w-100 text-start" id="btnDonaciones" onclick="showSection('donaciones')"><i class="fas fa-hand-holding-heart me-2"></i>Últimas Donaciones</button></li>
                        <li class="nav-item"><button class="nav-link w-100 text-start" id="btnNecesidades" onclick="showSection('necesidades')"><i class="fas fa-list-alt me-2"></i>Últimas Necesidades</button></li>
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
                                    <h2 class="mb-4">Estadísticas</h2>
                                    <div class="row text-center">
                                        <div class="col-6 col-md-2 mb-2">
                                            <div class="card bg-primary text-white h-100"><div class="card-body p-2"><i class="fas fa-list-alt fa-2x mb-2"></i><div class="fw-bold fs-4"><?= count($necesidades) ?></div><div>Necesidades</div></div></div>
                                        </div>
                                        <div class="col-6 col-md-2 mb-2">
                                            <div class="card bg-success text-white h-100"><div class="card-body p-2"><i class="fas fa-check-circle fa-2x mb-2"></i><div class="fw-bold fs-4"><?= count(array_filter($necesidades, fn($n) => $n['estado'] === 'completada')) ?></div><div>Completadas</div></div></div>
                                        </div>
                                        <div class="col-6 col-md-2 mb-2">
                                            <div class="card bg-info text-white h-100"><div class="card-body p-2"><i class="fas fa-bullhorn fa-2x mb-2"></i><div class="fw-bold fs-4"><?= count(array_filter($necesidades, fn($n) => $n['estado'] === 'activa')) ?></div><div>Activas</div></div></div>
                                        </div>
                                        <div class="col-6 col-md-3 mb-2">
                                            <div class="card bg-warning text-dark h-100"><div class="card-body p-2"><i class="fas fa-hand-holding-heart fa-2x mb-2"></i><div class="fw-bold fs-4"><?= count($donaciones) ?></div><div>Donaciones Recibidas</div></div></div>
                                        </div>
                                        <div class="col-6 col-md-3 mb-2">
                                            <div class="card bg-dark text-white h-100"><div class="card-body p-2"><i class="fas fa-gift fa-2x mb-2"></i><div class="fw-bold fs-4"><?= count(array_filter($donaciones, fn($d) => $d['estado'] === 'entregada')) ?></div><div>Donaciones Entregadas</div></div></div>
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
                        <style>
                        .vip-alert-float-org {
                            position: fixed;
                            top: 50%;
                            left: 50%;
                            transform: translate(-50%, -50%) scale(1);
                            z-index: 5000;
                            min-width: 370px;
                            max-width: 97vw;
                            box-shadow: 0 20px 80px rgba(0,80,180,0.35);
                            border-radius: 2.7rem;
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
                        .vip-alert-float-org .fa-building {
                            font-size: 2.2rem;
                            color: #2196f3;
                            margin-left: 0.5rem;
                            animation: buildpop 1s cubic-bezier(.68,-0.55,.27,1.55);
                        }
                        @keyframes crownpoporg {
                            0% { transform: scale(0.5) rotate(-20deg); }
                            80% { transform: scale(1.3) rotate(10deg); }
                            100% { transform: scale(1) rotate(0deg); }
                        }
                        @keyframes buildpop {
                            0% { transform: scale(0.5) rotate(10deg); }
                            80% { transform: scale(1.2) rotate(-10deg); }
                            100% { transform: scale(1) rotate(0deg); }
                        }
                        .vip-alert-float-org .btn-close {
                            filter: invert(1) grayscale(1);
                        }
                        </style>
                        <div class="vip-alert-float-org alert alert-<?= $mensaje_tipo ?> alert-dismissible fade show text-center py-5 fs-2 fw-bold" role="alert">
                            <i class="fas fa-crown"></i><i class="fas fa-building"></i><br>
                            <?= $mensaje ?>
                            <button type="button" class="btn-close position-absolute top-0 end-0 m-3" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                        </div>
                    <?php endif; ?>
                    <section id="section-resumen" class="dashboard-section">
                        <h2 class="mb-4">Resumen</h2>
                        <div class="row align-items-center">
                            <div class="col-md-3 text-center">
                                <div class="bg-primary bg-opacity-10 rounded-circle mx-auto mb-2" style="width:100px;height:100px;display:flex;align-items:center;justify-content:center;">
                                    <i class="fas fa-building fa-3x text-primary"></i>
                                </div>
                                <span class="badge bg-<?= $org['verificada'] ? 'success' : 'warning' ?>"> <?= $org['verificada'] ? 'Verificada' : 'No verificada' ?> </span>
                                <?php $qr = urlencode("mailto:" . $usuario['email'] . "?subject=Contacto%20para%20Organización%20" . urlencode($org['nombre'])); ?>
                                <img src="https://api.qrserver.com/v1/create-qr-code/?size=100x100&data=<?= $qr ?>" alt="QR de contacto" class="mb-2 mt-2">
                                <div><small>Contacto rápido</small></div>
                            </div>
                            <div class="col-md-6">
                                <h3 class="mb-1"> <?= htmlspecialchars($org['nombre']) ?> </h3>
                                <div class="mb-1"><i class="fas fa-user me-2"></i><?= htmlspecialchars($usuario['nombre']) ?></div>
                                <div class="mb-1"><i class="fas fa-envelope me-2"></i><span id="emailOrg"><?= htmlspecialchars($usuario['email']) ?></span> <button class="btn btn-sm btn-outline-secondary ms-2" onclick="navigator.clipboard.writeText('<?= htmlspecialchars($usuario['email']) ?>')">Copiar</button></div>
                                <div class="mb-1"><i class="fas fa-phone me-2"></i><?= htmlspecialchars($org['telefono']) ?></div>
                                <div class="mb-1"><i class="fas fa-map-marker-alt me-2"></i><?= htmlspecialchars($org['direccion']) ?></div>
                                <div class="mb-1"><i class="fas fa-id-card me-2"></i>RUC: <?= htmlspecialchars($org['ruc']) ?></div>
                            </div>
                            <div class="col-md-3 text-center">
                                <form method="post">
                                    <button type="submit" name="exportar_necesidades" class="btn btn-outline-primary btn-sm mb-2 w-100">Exportar Necesidades CSV</button>
                                    <button type="submit" name="exportar_donaciones" class="btn btn-outline-success btn-sm w-100">Exportar Donaciones CSV</button>
                                </form>
                            </div>
                        </div>
                    </section>
                    <section id="section-graficos" class="dashboard-section d-none">
                        <h2 class="mb-4">Gráficos</h2>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card"><div class="card-header bg-primary text-white">Progreso de Necesidades</div><div class="card-body">
                                    <?php if (empty($necesidades)): ?><p class="text-muted">No hay necesidades publicadas.</p><?php else: ?>
                                        <?php foreach (array_slice($necesidades, 0, 5) as $n): ?>
                                            <div class="mb-2">
                                                <div class="d-flex justify-content-between"><span><?= htmlspecialchars($n['titulo']) ?></span><span><?= round(($n['cantidad_actual'] / max(1, $n['cantidad_necesaria'])) * 100) ?>%</span></div>
                                                <div class="progress" style="height: 10px;"><div class="progress-bar bg-success" role="progressbar" style="width: <?= min(100, ($n['cantidad_actual'] / max(1, $n['cantidad_necesaria'])) * 100) ?>%"></div></div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div></div>
                            </div>
                            <div class="col-md-6">
                                <div class="card"><div class="card-header bg-success text-white">Donaciones Recibidas por Estado</div><div class="card-body">
                                    <?php $estados = ['pendiente','confirmada','entregada','cancelada']; $colores = ['warning','info','success','secondary']; foreach ($estados as $i => $estado): $cant = count(array_filter($donaciones, fn($d) => $d['estado'] === $estado)); ?>
                                        <div class="mb-2">
                                            <div class="d-flex justify-content-between"><span><?= ucfirst($estado) ?></span><span><?= $cant ?></span></div>
                                            <div class="progress" style="height: 10px;"><div class="progress-bar bg-<?= $colores[$i] ?>" role="progressbar" style="width: <?= $cant > 0 ? min(100, ($cant / max(1, count($donaciones))) * 100) : 0 ?>%"></div></div>
                                </div>
                                    <?php endforeach; ?>
                                </div></div>
                            </div>
                        </div>
                    </section>
                    <section id="section-accesos" class="dashboard-section d-none">
                        <h2 class="mb-4">Accesos Rápidos</h2>
                        <div class="card"><div class="card-header bg-info text-white">Accesos Rápidos</div><div class="card-body d-flex flex-wrap gap-2 justify-content-center">
                            <a href="<?= BASE_URL ?>/frontend/views/organizacion/necesidades.php" class="btn btn-outline-primary"><i class="fas fa-tasks me-1"></i> Gestionar Necesidades</a>
                            <a href="<?= BASE_URL ?>/frontend/views/organizacion/DonacionesRecibidas.php" class="btn btn-outline-success"><i class="fas fa-hand-holding-heart me-1"></i> Donaciones Recibidas</a>
                            <a href="<?= BASE_URL ?>/frontend/views/organizacion/historial.php" class="btn btn-outline-info"><i class="fas fa-history me-1"></i> Historial de Necesidades</a>
                            <a href="<?= BASE_URL ?>/frontend/views/organizacion/necesidades_crear.php" class="btn btn-outline-secondary"><i class="fas fa-plus me-1"></i> Publicar Nueva Necesidad</a>
                        </div></div>
                    </section>
                    <section id="section-ranking" class="dashboard-section d-none">
                        <h2 class="mb-4">Ranking de Necesidades Más Apoyadas</h2>
                        <div class="card"><div class="card-header bg-warning text-dark">Ranking</div><div class="card-body">
                            <?php $ranking = $necesidades; usort($ranking, function($a, $b) { return ($b['cantidad_actual'] / max(1, $b['cantidad_necesaria'])) <=> ($a['cantidad_actual'] / max(1, $a['cantidad_necesaria'])); }); ?>
                            <?php if (empty($ranking)): ?><p class="text-muted">No hay datos para mostrar.</p><?php else: ?>
                                <ol class="mb-0">
                                    <?php foreach (array_slice($ranking, 0, 5) as $r): ?>
                                        <li><strong><?= htmlspecialchars($r['titulo']) ?></strong> - <?= round(($r['cantidad_actual'] / max(1, $r['cantidad_necesaria'])) * 100) ?>% completado</li>
                                    <?php endforeach; ?>
                                </ol>
                            <?php endif; ?>
                        </div></div>
                    </section>
                    <section id="section-donaciones" class="dashboard-section d-none">
                        <h2 class="mb-4">Últimas Donaciones Recibidas</h2>
                        <div class="card"><div class="card-header bg-dark text-white">Donaciones</div><div class="card-body">
                            <?php if (empty($donaciones)): ?><p class="text-muted">No hay donaciones recientes.</p><?php else: ?>
                                <div class="table-responsive"><table class="table table-sm"><thead><tr><th>Donante</th><th>Necesidad</th><th>Cantidad</th><th>Estado</th><th>Fecha</th></tr></thead><tbody>
                                    <?php foreach (array_slice($donaciones, 0, 10) as $d): ?>
                                        <tr><td><?= htmlspecialchars($d['donante_nombre']) ?></td><td><?= htmlspecialchars($d['necesidad_titulo']) ?></td><td><?= $d['cantidad'] ?></td><td><span class="badge bg-<?= $d['estado'] === 'pendiente' ? 'warning' : ($d['estado'] === 'confirmada' ? 'info' : ($d['estado'] === 'entregada' ? 'success' : 'secondary')) ?>"><?= ucfirst($d['estado']) ?></span></td><td><?= date('d/m/Y', strtotime($d['created_at'])) ?></td></tr>
                                    <?php endforeach; ?>
                                </tbody></table></div>
                            <?php endif; ?>
                        </div></div>
                    </section>
                    <section id="section-necesidades" class="dashboard-section d-none">
                        <h2 class="mb-4">Últimas Necesidades Publicadas</h2>
                        <div class="card"><div class="card-header bg-primary text-white">Necesidades</div><div class="card-body">
                            <?php if (empty($necesidades)): ?><p class="text-muted">No hay necesidades publicadas.</p><?php else: ?>
                                <div class="table-responsive"><table class="table table-sm"><thead><tr><th>Título</th><th>Categoría</th><th>Progreso</th><th>Estado</th><th>Fecha</th></tr></thead><tbody>
                                    <?php foreach (array_slice($necesidades, 0, 10) as $n): ?>
                                        <tr><td><?= htmlspecialchars($n['titulo']) ?></td><td><?= htmlspecialchars($n['categoria']) ?></td><td><?= $n['cantidad_actual'] ?> / <?= $n['cantidad_necesaria'] ?> (<?= round(($n['cantidad_actual'] / max(1, $n['cantidad_necesaria'])) * 100) ?>%)</td><td><span class="badge bg-<?= $n['estado'] === 'activa' ? 'success' : ($n['estado'] === 'completada' ? 'primary' : 'secondary') ?>"><?= ucfirst($n['estado']) ?></span></td><td><?= date('d/m/Y', strtotime($n['created_at'])) ?></td></tr>
                                    <?php endforeach; ?>
                                </tbody></table></div>
                            <?php endif; ?>
                        </div></div>
                    </section>
                    <section id="section-editar" class="dashboard-section d-none">
                        <h2 class="mb-4">Editar Datos de Usuario y Organización</h2>
                        <div class="card"><div class="card-header bg-secondary text-white">Editar Perfil</div><div class="card-body">
                            <form method="POST">
                                <div class="mb-3"><label class="form-label">Nombre de Usuario</label><input type="text" class="form-control" name="nombre" value="<?= htmlspecialchars($usuario['nombre']) ?>" required></div>
                                <div class="mb-3"><label class="form-label">Correo electrónico</label><input type="email" class="form-control" name="email" value="<?= htmlspecialchars($usuario['email']) ?>" required></div>
                                <div class="mb-3"><label class="form-label">Nombre de la Organización</label><input type="text" class="form-control" name="org_nombre" value="<?= htmlspecialchars($org['nombre']) ?>" required></div>
                                <div class="mb-3"><label class="form-label">RUC</label><input type="text" class="form-control" name="ruc" value="<?= htmlspecialchars($org['ruc']) ?>"></div>
                                <div class="mb-3"><label class="form-label">Dirección</label><input type="text" class="form-control" name="direccion" value="<?= htmlspecialchars($org['direccion']) ?>"></div>
                                <div class="mb-3"><label class="form-label">Teléfono</label><input type="text" class="form-control" name="telefono" value="<?= htmlspecialchars($org['telefono']) ?>"></div>
                                <div class="mb-3"><label class="form-label">Descripción</label><textarea class="form-control" name="descripcion" rows="3"><?= htmlspecialchars($org['descripcion']) ?></textarea></div>
                                <button type="submit" name="actualizar_perfil" class="btn btn-primary">Actualizar Perfil</button>
                            </form>
                        </div></div>
                    </section>
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
                    <section id="section-ayuda" class="dashboard-section d-none">
                        <h2 class="mb-4">Ayuda y Preguntas Frecuentes</h2>
                        <div class="card"><div class="card-header bg-dark text-white">Ayuda/FAQ</div><div class="card-body">
                            <ul>
                                <li><strong>¿Cómo publico una nueva necesidad?</strong> Usa el botón "Publicar Nueva Necesidad" en Accesos Rápidos.</li>
                                <li><strong>¿Cómo exporto mis datos?</strong> Usa los botones de exportación arriba para descargar tus necesidades o donaciones en formato CSV.</li>
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
<?php require_once __DIR__ . '/../../partials/footer.php'; ?>