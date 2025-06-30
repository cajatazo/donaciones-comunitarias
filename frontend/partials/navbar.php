<?php require_once dirname(__DIR__, 2) . '/config.php'; ?>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center gap-2" href="<?= BASE_URL ?>/frontend/index.php">
            <img src="<?= BASE_URL ?>/frontend/assets/img/logo.png" alt="Logo" height="40">
            <span class="fw-bold fs-4">Red de Donaciones</span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center gap-2 <?php if (!isset(
                        $_SESSION['user_id']) && basename($_SERVER['PHP_SELF']) === 'necesidades.php') echo 'active'; ?>" href="<?= BASE_URL ?>/frontend/views/necesidades.php">
                        <i class="fas fa-list-alt me-2"></i>Necesidades
                    </a>
                </li>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <?php if ($_SESSION['user_type'] === 'donante'): ?>
                        <li class="nav-item">
                            <a class="nav-link d-flex align-items-center gap-2 <?php if (basename($_SERVER['PHP_SELF']) === 'dashboard.php') echo 'active'; ?>" href="<?= BASE_URL ?>/frontend/views/donante/dashboard.php">
                                <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link d-flex align-items-center gap-2 <?php if (basename($_SERVER['PHP_SELF']) === 'donaciones.php') echo 'active'; ?>" href="<?= BASE_URL ?>/frontend/views/donante/donaciones.php">
                                <i class="fas fa-hand-holding-heart me-2"></i>Donar
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link d-flex align-items-center gap-2 <?php if (basename($_SERVER['PHP_SELF']) === 'historial.php') echo 'active'; ?>" href="<?= BASE_URL ?>/frontend/views/donante/historial.php">
                                <i class="fas fa-history me-2"></i>Historial
                            </a>
                        </li>
                    <?php elseif ($_SESSION['user_type'] === 'organizacion'): ?>
                        <li class="nav-item">
                            <a class="nav-link d-flex align-items-center gap-2 <?php if (basename($_SERVER['PHP_SELF']) === 'dashboard.php') echo 'active'; ?>" href="<?= BASE_URL ?>/frontend/views/organizacion/dashboard.php">
                                <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link d-flex align-items-center gap-2 <?php if (basename($_SERVER['PHP_SELF']) === 'necesidades.php') echo 'active'; ?>" href="<?= BASE_URL ?>/frontend/views/organizacion/necesidades.php">
                                <i class="fas fa-list-alt me-2"></i>Mis Necesidades
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link d-flex align-items-center gap-2 <?php if (basename($_SERVER['PHP_SELF']) === 'DonacionesRecibidas.php') echo 'active'; ?>" href="<?= BASE_URL ?>/frontend/views/organizacion/DonacionesRecibidas.php">
                                <i class="fas fa-hand-holding-heart me-2"></i>Donaciones Recibidas
                            </a>
                        </li>
                    <?php elseif ($_SESSION['user_type'] === 'admin'): ?>
                        <li class="nav-item">
                            <a class="nav-link d-flex align-items-center gap-2 <?php if (basename($_SERVER['PHP_SELF']) === 'dashboard.php') echo 'active'; ?>" href="<?= BASE_URL ?>/frontend/views/admin/dashboard.php">
                                <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link d-flex align-items-center gap-2 <?php if (basename($_SERVER['PHP_SELF']) === 'organizaciones.php') echo 'active'; ?>" href="<?= BASE_URL ?>/frontend/views/admin/organizaciones.php">
                                <i class="fas fa-building me-2"></i>Organizaciones
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link d-flex align-items-center gap-2 <?php if (basename($_SERVER['PHP_SELF']) === 'donaciones.php') echo 'active'; ?>" href="<?= BASE_URL ?>/frontend/views/admin/donaciones.php">
                                <i class="fas fa-hand-holding-heart me-2"></i>Donaciones
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link d-flex align-items-center gap-2 <?php if (basename($_SERVER['PHP_SELF']) === 'reportes.php') echo 'active'; ?>" href="<?= BASE_URL ?>/frontend/views/admin/reportes.php">
                                <i class="fas fa-chart-bar me-2"></i>Reportes
                            </a>
                        </li>
                    <?php endif; ?>
                <?php endif; ?>
            </ul>
            <ul class="navbar-nav">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center gap-2" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <span class="bg-white bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                                <i class="fas fa-user fa-lg text-light"></i>
                            </span>
                            <span class="fw-bold fs-5 text-white text-shadow-sm"><?= htmlspecialchars($_SESSION['user_name'] ?? 'Usuario') ?></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow-lg animate__animated animate__fadeInTopRight" aria-labelledby="navbarDropdown" style="min-width: 220px;">
                            <li class="text-center py-2">
                                <span class="d-block fw-bold fs-5 text-primary"><i class="fas fa-user-circle fa-2x mb-1"></i><br><?= htmlspecialchars($_SESSION['user_name'] ?? 'Usuario') ?></span>
                                <span class="badge bg-info text-dark mt-1 mb-2 text-capitalize"><?= htmlspecialchars($_SESSION['user_type']) ?></span>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <?php if ($_SESSION['user_type'] === 'organizacion'): ?>
                                <li><a class="dropdown-item" href="<?= BASE_URL ?>/frontend/views/organizacion/perfil.php"><i class="fas fa-id-badge me-2"></i>Perfil</a></li>
                                <li><a class="dropdown-item" href="<?= BASE_URL ?>/frontend/views/organizacion/necesidades.php"><i class="fas fa-list-alt me-2"></i>Mis Necesidades</a></li>
                                <li><a class="dropdown-item" href="<?= BASE_URL ?>/frontend/views/organizacion/historial.php"><i class="fas fa-history me-2"></i>Historial</a></li>
                                
                            <?php elseif ($_SESSION['user_type'] === 'admin'): ?>
                                <li><a class="dropdown-item" href="<?= BASE_URL ?>/frontend/views/admin/perfil.php"><i class="fas fa-id-badge me-2"></i>Perfil</a></li>
                            <?php elseif ($_SESSION['user_type'] === 'donante'): ?>
                                <li><a class="dropdown-item" href="<?= BASE_URL ?>/frontend/views/donante/perfil.php"><i class="fas fa-id-badge me-2"></i>Perfil</a></li>
                            <?php else: ?>
                                <li><span class="dropdown-item disabled">Perfil no disponible</span></li>
                            <?php endif; ?>
                            <li><a class="dropdown-item" href="<?= BASE_URL ?>/frontend/views/auth/logout.php"><i class="fas fa-sign-out-alt me-2"></i>Cerrar Sesión</a></li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>/frontend/views/auth/login.php">Iniciar Sesión</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>/frontend/views/auth/register.php">Registrarse</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>