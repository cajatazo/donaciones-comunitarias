<?php require_once dirname(__DIR__, 2) . '/config.php'; ?>

<footer class="bg-dark text-white py-4 mt-5">
    <div class="container">
        <div class="row">
            <div class="col-md-4">
                <h5>Red Peruana de Donaciones Comunitarias</h5>
                <p>Conectando organizaciones con donantes para un Perú más solidario.</p>
            </div>
            <div class="col-md-4">
                <h5>Enlaces Rápidos</h5>
                <ul class="list-unstyled">
                    <li><a href="<?= BASE_URL ?>/frontend/views/necesidades.php" class="text-white">Necesidades</a></li>
                    <li><a href="<?= BASE_URL ?>/frontend/views/auth/register.php" class="text-white">Registrarse</a></li>
                    <li><a href="#" class="text-white">Contacto</a></li>
                </ul>
            </div>
            <div class="col-md-4">
                <h5>Contacto</h5>
                <p><i class="fas fa-envelope me-2"></i> contacto@donacionesperu.com</p>
                <p><i class="fas fa-phone me-2"></i> +51 987 654 321</p>
            </div>
        </div>
        <hr>
        <div class="text-center">
            <p class="mb-0">&copy; <?= date('Y') ?> Red Peruana de Donaciones Comunitarias. Todos los derechos reservados.</p>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= BASE_URL ?>/frontend/assets/js/main.js"></script>
</body>
</html>