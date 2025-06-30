<?php require_once __DIR__ . '/../partials/header.php'; ?>
<?php require_once __DIR__ . '/../partials/navbar.php'; ?>

<main class="container my-5">
    <section class="hero-section mb-5">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h1 class="display-4 fw-bold mb-4">Conectando solidaridad con necesidad</h1>
                <p class="lead mb-4">La Red Peruana de Donaciones Comunitarias facilita la conexión entre organizaciones sociales y donantes para hacer llegar ayuda donde más se necesita.</p>
                <div class="d-flex gap-3">
                    <a href="<?= BASE_URL ?>/necesidades" class="btn btn-primary btn-lg">Ver Necesidades</a>
                    <a href="<?= BASE_URL ?>/auth/register" class="btn btn-outline-primary btn-lg">Registrarse</a>
                </div>
            </div>
            <div class="col-md-6">
                <img src="<?= BASE_URL ?>/frontend/assets/img/hero-bg.jpg" alt="Personas ayudando" class="img-fluid rounded shadow">
            </div>
        </div>
    </section>

    <section class="stats-section mb-5 py-4 bg-light rounded">
        <div class="row text-center">
            <div class="col-md-4">
                <div class="display-4 fw-bold text-primary">150+</div>
                <p class="fs-5">Organizaciones registradas</p>
            </div>
            <div class="col-md-4">
                <div class="display-4 fw-bold text-primary">2,500+</div>
                <p class="fs-5">Donaciones realizadas</p>
            </div>
            <div class="col-md-4">
                <div class="display-4 fw-bold text-primary">50+</div>
                <p class="fs-5">Ciudades beneficiadas</p>
            </div>
        </div>
    </section>

    <section class="how-it-works mb-5">
        <h2 class="text-center mb-5">¿Cómo funciona?</h2>
        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body text-center">
                        <div class="bg-primary bg-opacity-10 text-primary rounded-circle p-3 d-inline-block mb-3">
                            <i class="fas fa-search fa-2x"></i>
                        </div>
                        <h4 class="card-title">1. Encuentra una necesidad</h4>
                        <p class="card-text">Las organizaciones publican sus necesidades de donaciones en nuestra plataforma.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body text-center">
                        <div class="bg-primary bg-opacity-10 text-primary rounded-circle p-3 d-inline-block mb-3">
                            <i class="fas fa-hand-holding-heart fa-2x"></i>
                        </div>
                        <h4 class="card-title">2. Realiza tu donación</h4>
                        <p class="card-text">Puedes reservar una donación y coordinar la entrega directamente con la organización.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body text-center">
                        <div class="bg-primary bg-opacity-10 text-primary rounded-circle p-3 d-inline-block mb-3">
                            <i class="fas fa-smile fa-2x"></i>
                        </div>
                        <h4 class="card-title">3. Haz la diferencia</h4>
                        <p class="card-text">Tu contribución llega directamente a quienes más lo necesitan.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>