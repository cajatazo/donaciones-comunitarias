<?php
require_once __DIR__ . '/../config.php';
// Si la URL no es la raíz ni un archivo físico, enruta con el router
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
$relative = '/' . ltrim(str_replace($base, '', $path), '/');
if ($relative !== '/' && !file_exists(__DIR__ . $relative)) {
    require_once __DIR__ . '/../backend/routes/web.php';
    exit;
}
require_once __DIR__ . '/../backend/libs/Database.php';

// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Conexión a la base de datos
$db = Database::getConnectionStatic();

// Obtener necesidades destacadas para la página principal
$necesidadesDestacadas = [];
try {
    $stmt = $db->query("
        SELECT n.*, o.nombre as organizacion_nombre 
        FROM necesidades n
        JOIN organizaciones o ON n.organizacion_id = o.id
        WHERE n.estado = 'activa' AND o.verificada = 1
        ORDER BY n.created_at DESC
        LIMIT 6
    ");
    $necesidadesDestacadas = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error al obtener necesidades destacadas: " . $e->getMessage());
}

// Obtener estadísticas para mostrar
$stats = [
    'organizaciones' => 0,
    'donaciones' => 0,
    'beneficiarios' => 0
];

try {
    // Total de organizaciones verificadas
    $stmt = $db->query("SELECT COUNT(*) as total FROM organizaciones WHERE verificada = 1");
    $stats['organizaciones'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Total de donaciones realizadas
    $stmt = $db->query("SELECT COUNT(*) as total FROM donaciones WHERE estado != 'cancelada'");
    $stats['donaciones'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Estimado de beneficiarios (asumimos 10 por donación)
    $stats['beneficiarios'] = $stats['donaciones'] * 10;
} catch (PDOException $e) {
    error_log("Error al obtener estadísticas: " . $e->getMessage());
}

// Determinar si mostrar el banner de alerta
$showAlertBanner = false;
if (!isset($_SESSION['user_id'])) {
    $showAlertBanner = true;
}
?>
<!DOCTYPE html>
<html lang="es" data-bs-theme="auto">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Red Peruana de Donaciones Comunitarias | Conectando solidaridad</title>
    <meta name="description" content="Plataforma que conecta organizaciones sociales con donantes para ayudar a comunidades necesitadas en todo el Perú">
    <link rel="icon" href="<?= BASE_URL ?>/frontend/assets/img/logo.png">
    
    <!-- Favicon -->
    <link rel="apple-touch-icon" sizes="180x180" href="<?= BASE_URL ?>/frontend/assets/img/favicon/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="<?= BASE_URL ?>/frontend/assets/img/favicon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="<?= BASE_URL ?>/frontend/assets/img/favicon/favicon-16x16.png">
    <link rel="manifest" href="<?= BASE_URL ?>/frontend/assets/img/favicon/site.webmanifest">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    
    <!-- AOS Animation -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link href="<?= BASE_URL ?>/frontend/assets/css/styles.css" rel="stylesheet">
    
    <!-- Theme color for mobile browsers -->
    <meta name="theme-color" content="#0d6efd">
    
    <!-- Open Graph / Social Media Meta Tags -->
    <meta property="og:title" content="Red Peruana de Donaciones Comunitarias">
    <meta property="og:description" content="Conectando organizaciones sociales con donantes para un Perú más solidario">
    <meta property="og:image" content="<?= BASE_URL ?>/frontend/assets/img/social-share.jpg">
    <meta property="og:url" content="<?= BASE_URL ?>">
    <meta property="og:type" content="website">
    
    <!-- Twitter Card Meta Tags -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Red Peruana de Donaciones Comunitarias">
    <meta name="twitter:description" content="Conectando organizaciones sociales con donantes para un Perú más solidario">
    <meta name="twitter:image" content="<?= BASE_URL ?>/frontend/assets/img/social-share.jpg">
</head>
<body class="d-flex flex-column min-vh-100">
    <!-- Alert Banner -->
    <?php if ($showAlertBanner): ?>
    <div class="alert alert-warning alert-dismissible fade show mb-0 text-center rounded-0" role="alert">
        <strong>¡Únete a nuestra comunidad solidaria!</strong> Regístrate ahora y comienza a hacer la diferencia.
        <a href="<?= BASE_URL ?>/frontend/views/auth/register.php" class="alert-link ms-2">Registrarse</a>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>
    
    <!-- Navbar VIP -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="<?= BASE_URL ?>/frontend/#">
                <img src="<?= BASE_URL ?>/frontend/assets/img/logo.png" alt="Logo" height="90" class="me-2">
                <span class="fw-bold">Red de Donaciones</span>
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarMain">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link d-flex align-items-center gap-2 <?php if (basename($_SERVER['PHP_SELF']) === 'index.php') echo 'active'; ?>" href="./">
                            <i class="fas fa-home me-2"></i>Inicio
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link d-flex align-items-center gap-2 <?php if (basename($_SERVER['PHP_SELF']) === 'necesidades.php') echo 'active'; ?>" href="views/necesidades.php">
                            <i class="fas fa-list-alt me-2"></i>Necesidades
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link d-flex align-items-center gap-2" href="#como-funciona">
                            <i class="fas fa-question-circle me-2"></i>Cómo funciona
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link d-flex align-items-center gap-2" href="#contacto">
                            <i class="fas fa-envelope me-2"></i>Contacto
                        </a>
                    </li>
                </ul>
                
                <div class="d-flex">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <div class="dropdown">
                            <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle" id="dropdownUser" data-bs-toggle="dropdown">
                                <i class="fas fa-user-circle fs-4 me-2"></i>
                                <span class="d-none d-sm-inline fw-bold text-shadow-sm"> <?= htmlspecialchars($_SESSION['user_name']) ?> </span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end shadow animate__animated animate__fadeInTopRight">
                                <li class="text-center py-2">
                                    <span class="d-block fw-bold fs-5 text-primary"><i class="fas fa-user-circle fa-2x mb-1"></i><br><?= htmlspecialchars($_SESSION['user_name'] ?? 'Usuario') ?></span>
                                    <span class="badge bg-info text-dark mt-1 mb-2 text-capitalize"> <?= htmlspecialchars($_SESSION['user_type']) ?> </span>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <?php if ($_SESSION['user_type'] === 'organizacion'): ?>
                                    <li><a class="dropdown-item d-flex align-items-center gap-2" href="<?= BASE_URL ?>/frontend/views/organizacion/dashboard.php"><i class="fas fa-tachometer-alt me-2"></i>Dashboard</a></li>
                                    <li><a class="dropdown-item d-flex align-items-center gap-2" href="<?= BASE_URL ?>/frontend/views/organizacion/perfil.php"><i class="fas fa-user me-2"></i>Perfil</a></li>
                                    <li><a class="dropdown-item d-flex align-items-center gap-2" href="<?= BASE_URL ?>/frontend/views/organizacion/necesidades.php"><i class="fas fa-list-alt me-2"></i>Mis Necesidades</a></li>
                                    <li><a class="dropdown-item d-flex align-items-center gap-2" href="<?= BASE_URL ?>/frontend/views/organizacion/historial.php"><i class="fas fa-history me-2"></i>Historial</a></li>
                                    <li><a class="dropdown-item d-flex align-items-center gap-2" href="<?= BASE_URL ?>/frontend/views/organizacion/DonacionesRecibidas.php"><i class="fas fa-hand-holding-heart me-2"></i>Donaciones Recibidas</a></li>
                                <?php elseif ($_SESSION['user_type'] === 'donante'): ?>
                                    <li><a class="dropdown-item d-flex align-items-center gap-2" href="<?= BASE_URL ?>/frontend/views/donante/dashboard.php"><i class="fas fa-tachometer-alt me-2"></i>Dashboard</a></li>
                                    <li><a class="dropdown-item d-flex align-items-center gap-2" href="<?= BASE_URL ?>/frontend/views/donante/donaciones.php"><i class="fas fa-hand-holding-heart me-2"></i>Donar</a></li>
                                    <li><a class="dropdown-item d-flex align-items-center gap-2" href="<?= BASE_URL ?>/frontend/views/donante/historial.php"><i class="fas fa-history me-2"></i>Historial</a></li>
                                    <li><a class="dropdown-item d-flex align-items-center gap-2" href="<?= BASE_URL ?>/frontend/views/donante/perfil.php"><i class="fas fa-user me-2"></i>Perfil</a></li>
                                <?php elseif ($_SESSION['user_type'] === 'admin'): ?>
                                    <li><a class="dropdown-item d-flex align-items-center gap-2" href="<?= BASE_URL ?>/frontend/views/admin/dashboard.php"><i class="fas fa-tachometer-alt me-2"></i>Dashboard</a></li>
                                    <li><a class="dropdown-item d-flex align-items-center gap-2" href="<?= BASE_URL ?>/frontend/views/admin/organizaciones.php"><i class="fas fa-building me-2"></i>Organizaciones</a></li>
                                    <li><a class="dropdown-item d-flex align-items-center gap-2" href="<?= BASE_URL ?>/frontend/views/admin/donaciones.php"><i class="fas fa-hand-holding-heart me-2"></i>Donaciones</a></li>
                                    <li><a class="dropdown-item d-flex align-items-center gap-2" href="<?= BASE_URL ?>/frontend/views/admin/reportes.php"><i class="fas fa-chart-bar me-2"></i>Reportes</a></li>
                                    <li><a class="dropdown-item d-flex align-items-center gap-2" href="<?= BASE_URL ?>/frontend/views/admin/perfil.php"><i class="fas fa-user me-2"></i>Perfil</a></li>
                                <?php endif; ?>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item d-flex align-items-center gap-2" href="<?= BASE_URL ?>/frontend/views/auth/logout.php"><i class="fas fa-sign-out-alt me-2"></i>Cerrar sesión</a></li>
                            </ul>
                        </div>
                    <?php else: ?>
                        <a href="<?= BASE_URL ?>/frontend/views/auth/login.php" class="btn btn-outline-light me-2">Iniciar sesión</a>
                        <a href="<?= BASE_URL ?>/frontend/views/auth/register.php" class="btn btn-light">Registrarse</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>
    
    <!-- Hero Section VIP -->
    <section class="hero-section position-relative overflow-hidden">
        <div class="container py-5">
            <div class="row align-items-center py-5">
                <div class="col-lg-6 py-5" data-aos="fade-right">
                    <h1 class="display-3 fw-bold mb-4 text-primary">Conectando <span class="text-warning">solidaridad</span> con <span class="text-success">necesidad</span></h1>
                    <p class="lead mb-4 fs-4">La plataforma que une a organizaciones sociales con donantes comprometidos para transformar vidas en todo el Perú.</p>
                    <div class="d-flex flex-wrap gap-3">
                        <a href="<?= BASE_URL ?>/frontend/views/necesidades.php" class="btn btn-primary btn-lg px-4 py-3 shadow">
                            <i class="fas fa-search me-2"></i> Ver necesidades
                        </a>
                        <?php if (!isset($_SESSION['user_id'])): ?>
                        <a href="<?= BASE_URL ?>/frontend/views/auth/register.php" class="btn btn-outline-primary btn-lg px-4 py-3">
                            <i class="fas fa-hand-holding-heart me-2"></i> Quiero ayudar
                        </a>
                        <?php endif; ?>
                    </div>
                    
                    <div class="d-flex flex-wrap gap-4 mt-5">
                        <div class="d-flex align-items-center">
                            <div class="bg-primary bg-opacity-10 text-primary rounded-circle p-3 me-3">
                                <i class="fas fa-check fa-2x"></i>
                            </div>
                            <div>
                                <h5 class="mb-0">+<?= number_format($stats['organizaciones'], 0, ',', '.') ?></h5>
                                <small class="text-muted">Organizaciones</small>
                            </div>
                        </div>
                        <div class="d-flex align-items-center">
                            <div class="bg-success bg-opacity-10 text-success rounded-circle p-3 me-3">
                                <i class="fas fa-heart fa-2x"></i>
                            </div>
                            <div>
                                <h5 class="mb-0">+<?= number_format($stats['donaciones'], 0, ',', '.') ?></h5>
                                <small class="text-muted">Donaciones</small>
                            </div>
                        </div>
                        <div class="d-flex align-items-center">
                            <div class="bg-warning bg-opacity-10 text-warning rounded-circle p-3 me-3">
                                <i class="fas fa-users fa-2x"></i>
                            </div>
                            <div>
                                <h5 class="mb-0">+<?= number_format($stats['beneficiarios'], 0, ',', '.') ?></h5>
                                <small class="text-muted">Beneficiarios</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6" data-aos="fade-left">
                    <div class="position-relative">
                        <img src="<?= BASE_URL ?>/frontend/assets/img/hero-image.png" alt="Personas ayudando" class="img-fluid rounded-4 shadow-lg">
                        <div class="position-absolute top-0 start-0 translate-middle bg-primary text-white rounded-circle p-3 d-none d-lg-block">
                            <i class="fas fa-heart fa-2x"></i>
                        </div>
                        <div class="position-absolute bottom-0 end-0 translate-middle bg-success text-white rounded-circle p-3 d-none d-lg-block">
                            <i class="fas fa-hands-helping fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="position-absolute top-0 end-0 w-50 h-100 bg-primary bg-opacity-10 rounded-start-5 z-n1 d-none d-lg-block"></div>
    </section>
    
    <!-- Necesidades Destacadas -->
    <section class="py-5 bg-light">
        <div class="container py-5">
            <div class="row mb-5" data-aos="fade-up">
                <div class="col-md-8 mx-auto text-center">
                    <h2 class="display-5 fw-bold mb-3">Necesidades <span class="text-primary">destacadas</span></h2>
                    <p class="lead text-muted">Estas son algunas de las necesidades más urgentes que las organizaciones han compartido.</p>
                </div>
            </div>
            
            <div class="row g-4">
                <?php if (empty($necesidadesDestacadas)): ?>
                    <div class="col-12 text-center py-5">
                        <div class="alert alert-info">No hay necesidades destacadas en este momento.</div>
                    </div>
                <?php else: ?>
                    <?php foreach ($necesidadesDestacadas as $idx => $necesidad): ?>
                    <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="<?= $idx * 100 ?>">
                        <div class="card h-100 border-0 shadow-sm overflow-hidden">
                            <div class="position-relative">
                                <?php 
                                $bgClass = [
                                    'alimentos' => 'bg-warning',
                                    'ropa' => 'bg-info',
                                    'materiales_escolares' => 'bg-primary',
                                    'medicinas' => 'bg-danger',
                                    'otros' => 'bg-secondary'
                                ];
                                ?>
                                <span class="badge <?= isset(
                                    $bgClass[$necesidad['categoria']]) ? $bgClass[$necesidad['categoria']] : 'bg-secondary' ?> position-absolute top-0 end-0 m-3">
                                    <?= ucfirst(str_replace('_', ' ', $necesidad['categoria'])) ?>
                                </span>
                                <div class="card-img-top bg-light" style="height: 150px; background-image: url('<?= BASE_URL ?>/frontend/assets/img/categories/<?= $necesidad['categoria'] ?>.jpg'); background-size: cover; background-position: center;"></div>
                            </div>
                            <div class="card-body">
                                <h5 class="card-title"><?= htmlspecialchars($necesidad['titulo']) ?></h5>
                                <p class="card-text text-muted"><?= htmlspecialchars(substr($necesidad['descripcion'], 0, 100)) ?><?= strlen($necesidad['descripcion']) > 100 ? '...' : '' ?></p>
                                
                                <div class="mb-3">
                                    <small class="text-muted d-block mb-1">Organización: <?= htmlspecialchars($necesidad['organizacion_nombre']) ?></small>
                                    <small class="text-muted">Fecha límite: <?= date('d/m/Y', strtotime($necesidad['fecha_limite'])) ?></small>
                                </div>
                                
                                <div class="progress mb-3" style="height: 10px;">
                                    <div class="progress-bar bg-success" 
                                         role="progressbar" 
                                         style="width: <?= min(100, ($necesidad['cantidad_actual'] / $necesidad['cantidad_necesaria']) * 100) ?>%" 
                                         aria-valuenow="<?= isset($necesidad['cantidad_actual']) ? $necesidad['cantidad_actual'] : 0 ?>" 
                                         aria-valuemin="0" 
                                         aria-valuemax="<?= isset($necesidad['cantidad_necesaria']) ? $necesidad['cantidad_necesaria'] : 0 ?>">
                                    </div>
                                </div>
                                <div class="d-flex justify-content-between text-muted small mb-3">
                                    <span><?= isset($necesidad['cantidad_actual']) ? $necesidad['cantidad_actual'] : 0 ?> de <?= isset($necesidad['cantidad_necesaria']) ? $necesidad['cantidad_necesaria'] : 0 ?></span>
                                    <span><?= isset($necesidad['cantidad_actual'], $necesidad['cantidad_necesaria']) && $necesidad['cantidad_necesaria'] > 0 ? round(($necesidad['cantidad_actual'] / $necesidad['cantidad_necesaria']) * 100) : 0 ?>%</span>
                                </div>
                            </div>
                            <div class="card-footer bg-white border-0">
                                <a href="<?= BASE_URL ?>/frontend/views/donante/donar.php?necesidad_id=<?= $necesidad['id'] ?>" class="btn btn-primary w-100 stretched-link">
                                    <i class="fas fa-hand-holding-heart me-2"></i> Colaborar
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            
            <div class="text-center mt-5" data-aos="fade-up">
                <a href="<?= BASE_URL ?>/frontend/views/necesidades.php" class="btn btn-outline-primary btn-lg px-5">
                    Ver todas las necesidades <i class="fas fa-arrow-right ms-2"></i>
                </a>
            </div>
        </div>
    </section>
    
    <!-- Cómo funciona -->
    <section id="como-funciona" class="py-5">
        <div class="container py-5">
            <div class="row mb-5" data-aos="fade-up">
                <div class="col-md-8 mx-auto text-center">
                    <h2 class="display-5 fw-bold mb-3">¿Cómo <span class="text-primary">funciona</span>?</h2>
                    <p class="lead text-muted">En solo 3 sencillos pasos puedes empezar a ayudar a quienes más lo necesitan.</p>
                </div>
            </div>
            
            <div class="row g-4">
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body text-center p-4">
                            <div class="bg-primary bg-opacity-10 text-primary rounded-circle p-4 d-inline-block mb-4">
                                <i class="fas fa-search fa-3x"></i>
                            </div>
                            <h3 class="h4 mb-3">1. Encuentra una necesidad</h3>
                            <p class="text-muted mb-0">Las organizaciones verificadas publican sus necesidades en nuestra plataforma. Puedes buscar por categoría, ubicación o urgencia.</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body text-center p-4">
                            <div class="bg-success bg-opacity-10 text-success rounded-circle p-4 d-inline-block mb-4">
                                <i class="fas fa-hand-holding-heart fa-3x"></i>
                            </div>
                            <h3 class="h4 mb-3">2. Realiza tu donación</h3>
                            <p class="text-muted mb-0">Selecciona la cantidad que deseas donar y coordina la entrega directamente con la organización.</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="300">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body text-center p-4">
                            <div class="bg-warning bg-opacity-10 text-warning rounded-circle p-4 d-inline-block mb-4">
                                <i class="fas fa-smile fa-3x"></i>
                            </div>
                            <h3 class="h4 mb-3">3. Haz la diferencia</h3>
                            <p class="text-muted mb-0">Tu contribución llega directamente a quienes más lo necesitan. Recibirás un reporte del impacto generado.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- CTA -->
    <section class="py-5 bg-primary text-white">
        <div class="container py-5">
            <div class="row align-items-center">
                <div class="col-lg-8 mb-4 mb-lg-0" data-aos="fade-right">
                    <h2 class="display-6 fw-bold mb-3">¿Listo para marcar la diferencia?</h2>
                    <p class="lead mb-0">Únete a nuestra comunidad de donantes y organizaciones que están transformando vidas en todo el Perú.</p>
                </div>
                <div class="col-lg-4 text-lg-end" data-aos="fade-left">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <a href="<?= BASE_URL ?>/frontend/views/necesidades.php" class="btn btn-light btn-lg px-5 py-3">
                            Ver necesidades <i class="fas fa-arrow-right ms-2"></i>
                        </a>
                    <?php else: ?>
                        <a href="<?= BASE_URL ?>/frontend/views/auth/register.php" class="btn btn-light btn-lg px-5 py-3 me-3">
                            Registrarme
                        </a>
                        <a href="<?= BASE_URL ?>/frontend/views/auth/login.php" class="btn btn-outline-light btn-lg px-5 py-3">
                            Iniciar sesión
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Contacto -->
    <section id="contacto" class="py-5">
        <div class="container py-5">
            <div class="row mb-5" data-aos="fade-up">
                <div class="col-md-8 mx-auto text-center">
                    <h2 class="display-5 fw-bold mb-3">Contáctanos</h2>
                    <p class="lead text-muted">¿Tienes preguntas o sugerencias? Escríbenos y te responderemos a la brevedad.</p>
                </div>
            </div>
            
            <div class="row g-4">
                <div class="col-lg-5" data-aos="fade-right">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body p-4">
                            <h3 class="h4 mb-4">Información de contacto</h3>
                            
                            <div class="d-flex mb-4">
                                <div class="bg-primary bg-opacity-10 text-primary rounded-circle p-3 me-3">
                                    <i class="fas fa-map-marker-alt"></i>
                                </div>
                                <div>
                                    <h5 class="h6 mb-1">Dirección</h5>
                                    <p class="text-muted mb-0">Av. Solidaridad 123, Lima, Perú</p>
                                </div>
                            </div>
                            
                            <div class="d-flex mb-4">
                                <div class="bg-primary bg-opacity-10 text-primary rounded-circle p-3 me-3">
                                    <i class="fas fa-envelope"></i>
                                </div>
                                <div>
                                    <h5 class="h6 mb-1">Correo electrónico</h5>
                                    <p class="text-muted mb-0">contacto@donacionesperu.org</p>
                                </div>
                            </div>
                            
                            <div class="d-flex mb-4">
                                <div class="bg-primary bg-opacity-10 text-primary rounded-circle p-3 me-3">
                                    <i class="fas fa-phone-alt"></i>
                                </div>
                                <div>
                                    <h5 class="h6 mb-1">Teléfono</h5>
                                    <p class="text-muted mb-0">+51 987 654 321</p>
                                </div>
                            </div>
                            
                            <div class="d-flex">
                                <div class="bg-primary bg-opacity-10 text-primary rounded-circle p-3 me-3">
                                    <i class="fas fa-clock"></i>
                                </div>
                                <div>
                                    <h5 class="h6 mb-1">Horario de atención</h5>
                                    <p class="text-muted mb-0">Lunes a Viernes: 9am - 6pm</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-7" data-aos="fade-left">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-4">
                            <h3 class="h4 mb-4">Envíanos un mensaje</h3>
                            
                            <form>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="nombre" class="form-label">Nombre completo</label>
                                        <input type="text" class="form-control" id="nombre" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="email" class="form-label">Correo electrónico</label>
                                        <input type="email" class="form-control" id="email" required>
                                    </div>
                                    <div class="col-12">
                                        <label for="asunto" class="form-label">Asunto</label>
                                        <input type="text" class="form-control" id="asunto" required>
                                    </div>
                                    <div class="col-12">
                                        <label for="mensaje" class="form-label">Mensaje</label>
                                        <textarea class="form-control" id="mensaje" rows="5" required></textarea>
                                    </div>
                                    <div class="col-12">
                                        <button type="submit" class="btn btn-primary px-4 py-2">
                                            <i class="fas fa-paper-plane me-2"></i> Enviar mensaje
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Footer VIP -->
    <footer class="bg-dark text-white pt-5 pb-4">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-4">
                    <a class="d-flex align-items-center mb-3 text-white text-decoration-none">
                        <img src="<?= BASE_URL ?>/frontend/assets/img/logo.png" alt="Logo" height="40" class="me-2">
                        <span class="fs-4 fw-bold">Red de Donaciones</span>
                    </a>
                    <p class="mb-3">Conectando organizaciones sociales con donantes para un Perú más solidario.</p>
                    <div class="d-flex gap-3">
                        <a href="#" class="text-white"><i class="fab fa-facebook-f fa-lg"></i></a>
                        <a href="#" class="text-white"><i class="fab fa-twitter fa-lg"></i></a>
                        <a href="#" class="text-white"><i class="fab fa-instagram fa-lg"></i></a>
                        <a href="#" class="text-white"><i class="fab fa-linkedin-in fa-lg"></i></a>
                    </div>
                </div>
                
                <div class="col-lg-2 col-md-4">
                    <h5 class="mb-3">Enlaces</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="./" class="text-white text-decoration-none">Inicio</a></li>
                        <li class="mb-2"><a href="views/necesidades.php" class="text-white text-decoration-none">Necesidades</a></li>
                        <li class="mb-2"><a href="#como-funciona" class="text-white text-decoration-none">Cómo funciona</a></li>
                        <li class="mb-2"><a href="#contacto" class="text-white text-decoration-none">Contacto</a></li>
                    </ul>
                </div>
                
                <div class="col-lg-2 col-md-4">
                    <h5 class="mb-3">Legal</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="#" class="text-white text-decoration-none">Términos</a></li>
                        <li class="mb-2"><a href="#" class="text-white text-decoration-none">Privacidad</a></li>
                        <li class="mb-2"><a href="#" class="text-white text-decoration-none">Cookies</a></li>
                        <li class="mb-2"><a href="#" class="text-white text-decoration-none">FAQ</a></li>
                    </ul>
                </div>
                
                <div class="col-lg-4 col-md-4">
                    <h5 class="mb-3">Suscríbete</h5>
                    <p class="mb-3">Recibe noticias y actualizaciones sobre nuestras actividades.</p>
                    <form class="mb-3">
                        <div class="input-group">
                            <input type="email" class="form-control" placeholder="Tu correo electrónico" required>
                            <button class="btn btn-primary" type="submit">
                                <i class="fas fa-paper-plane"></i>
                            </button>
                        </div>
                    </form>
                    <small class="text-muted">No compartiremos tu información con terceros.</small>
                </div>
            </div>
            
            <hr class="my-4">
            
            <div class="row">
                <div class="col-md-6 text-center text-md-start">
                    <p class="mb-0">&copy; <?= date('Y') ?> Red Peruana de Donaciones Comunitarias. Todos los derechos reservados.</p>
                </div>
                <div class="col-md-6 text-center text-md-end">
                    <p class="mb-0">Desarrollado con <i class="fas fa-heart text-danger"></i> para el Perú</p>
                </div>
            </div>
        </div>
    </footer>
    
    <!-- Botón flotante de WhatsApp -->
    <a href="https://wa.me/51987654321" 
       class="whatsapp-float position-fixed end-0 bottom-0 me-4 mb-4 z-3" 
       style="left: auto; right: 0; font-size: 2.5rem; width: 64px; height: 64px; display: flex; align-items: center; justify-content: center; border-radius: 50%; background: #25d366; color: #fff; border: none; box-shadow: none;" 
       target="_blank" rel="noopener noreferrer" 
       data-aos="fade-left" data-aos-delay="1000">
        <i class="fab fa-whatsapp"></i>
    </a>
    
    <!-- Botón de volver arriba -->
    <a href="#" 
       class="back-to-top position-fixed start-50 translate-middle-x bottom-0 mb-5 z-3" 
       style="left: 50%; right: auto; margin-bottom: 90px; font-size: 2.2rem; width: 56px; height: 56px; display: flex; align-items: center; justify-content: center; border-radius: 50%; background: #0d6efd; color: #fff; box-shadow: 0 2px 8px rgba(0,0,0,0.2); opacity:0; visibility:hidden; transition:all .3s; border: none;" 
       data-aos="fade-up" data-aos-delay="1200">
        <i class="fas fa-arrow-up"></i>
    </a>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- AOS Animation -->
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    
    <!-- Custom JS -->
    <script src="<?= BASE_URL ?>/frontend/assets/js/main.js"></script>
    
    <script>
        // Inicializar AOS Animation
        AOS.init({
            duration: 800,
            easing: 'ease-in-out',
            once: true
        });
        
        // Botón de volver arriba
        const backToTopButton = document.querySelector('.back-to-top');
        window.addEventListener('scroll', function() {
            if (window.pageYOffset > 300) {
                backToTopButton.style.opacity = '1';
                backToTopButton.style.visibility = 'visible';
            } else {
                backToTopButton.style.opacity = '0';
                backToTopButton.style.visibility = 'hidden';
            }
        });
        
        backToTopButton.addEventListener('click', function(e) {
            e.preventDefault();
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
        
        // Validación de formulario de contacto
        const contactForm = document.querySelector('#contacto form');
        if (contactForm) {
            contactForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                // Validación simple
                let isValid = true;
                const inputs = contactForm.querySelectorAll('input, textarea');
                
                inputs.forEach(input => {
                    if (!input.value.trim()) {
                        input.classList.add('is-invalid');
                        isValid = false;
                    } else {
                        input.classList.remove('is-invalid');
                    }
                });
                
                if (isValid) {
                    // Aquí iría la lógica para enviar el formulario
                    alert('Gracias por tu mensaje. Nos pondremos en contacto contigo pronto.');
                    contactForm.reset();
                }
            });
        }
    </script>
</body>
</html>