<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
?>
<?php require_once __DIR__ . '/../../../config.php'; ?>
<?php
require_once __DIR__ . '/../../../backend/models/Usuario.php';
require_once __DIR__ . '/../../../backend/models/Organizacion.php';
require_once __DIR__ . '/../../../backend/libs/Database.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }

$mensaje = '';
$mensaje_tipo = '';
$registro_exitoso = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];
    $tipo = $_POST['tipo'];

    $usuarioModel = new Usuario();
    // Validar si el email ya existe
    $usuarios = $usuarioModel->listar();
    $emailExiste = false;
    foreach ($usuarios as $u) {
        if ($u['email'] === $email) {
            $emailExiste = true;
            break;
        }
    }

    if ($emailExiste) {
        $mensaje = 'El correo electrónico ya está registrado.';
        $mensaje_tipo = 'danger';
    } elseif ($password !== $confirmPassword) {
        $mensaje = 'Las contraseñas no coinciden.';
        $mensaje_tipo = 'danger';
    } else {
        // Registrar usuario
        if ($usuarioModel->registrar($nombre, $email, $password, $tipo)) {
            // Si es organización, registrar también en la tabla organizaciones
            if ($tipo === 'organizacion') {
                $orgModel = new Organizacion();
                $db = Database::getConnectionStatic();
                $userId = $db->lastInsertId();
                $orgModel->registrar(
                    $userId,
                    $_POST['org_nombre'],
                    $_POST['org_ruc'],
                    $_POST['org_direccion'],
                    $_POST['org_telefono'],
                    $_POST['org_descripcion']
                );
            }
            $mensaje = '¡Registro exitoso! Ahora puedes iniciar sesión.';
            $mensaje_tipo = 'success';
            $registro_exitoso = true;
        } else {
            $mensaje = 'Error al registrar el usuario.';
            $mensaje_tipo = 'danger';
        }
    }
}
?>
<?php require_once __DIR__ . '/../../partials/header.php'; ?>

<main class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h2 class="mb-0">Registrarse</h2>
                </div>
                <div class="card-body">
                    <?php if (!empty($mensaje)): ?>
                        <div class="alert alert-<?= $mensaje_tipo ?>"> <?= $mensaje ?> </div>
                    <?php endif; ?>
                    <?php if ($registro_exitoso): ?>
                        <div class="text-center mt-4">
                            <a href="<?= BASE_URL ?>/frontend/views/auth/login.php" class="btn btn-success">Ir a Iniciar Sesión</a>
                        </div>
                    <?php else: ?>
                    <form action="" method="POST">
                        <div class="mb-3">
                            <label for="nombre" class="form-label">Nombre Completo</label>
                            <input type="text" class="form-control" id="nombre" name="nombre" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Correo Electrónico</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Contraseña</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">Confirmar Contraseña</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                        </div>
                        <div class="mb-3">
                            <label for="tipo" class="form-label">Tipo de Usuario</label>
                            <select class="form-select" id="tipo" name="tipo" required>
                                <option value="">Seleccionar...</option>
                                <option value="donante">Donante</option>
                                <option value="organizacion">Organización</option>
                            </select>
                        </div>
                        <!-- Campos específicos para organizaciones -->
                        <div id="orgFields" style="display: none;">
                            <h5 class="mt-4 mb-3">Información de la Organización</h5>
                            <div class="mb-3">
                                <label for="org_nombre" class="form-label">Nombre de la Organización</label>
                                <input type="text" class="form-control" id="org_nombre" name="org_nombre">
                            </div>
                            <div class="mb-3">
                                <label for="org_ruc" class="form-label">RUC</label>
                                <input type="text" class="form-control" id="org_ruc" name="org_ruc">
                            </div>
                            <div class="mb-3">
                                <label for="org_direccion" class="form-label">Dirección</label>
                                <input type="text" class="form-control" id="org_direccion" name="org_direccion">
                            </div>
                            <div class="mb-3">
                                <label for="org_telefono" class="form-label">Teléfono</label>
                                <input type="text" class="form-control" id="org_telefono" name="org_telefono">
                            </div>
                            <div class="mb-3">
                                <label for="org_descripcion" class="form-label">Descripción</label>
                                <textarea class="form-control" id="org_descripcion" name="org_descripcion" rows="3"></textarea>
                            </div>
                        </div>
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">Registrarse</button>
                        </div>
                    </form>
                    <hr class="my-4">
                    <div class="text-center">
                        <p>¿Ya tienes una cuenta? <a href="<?= BASE_URL ?>/frontend/views/auth/login.php">Inicia sesión aquí</a></p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const tipoSelect = document.getElementById('tipo');
        const orgFields = document.getElementById('orgFields');
        tipoSelect.addEventListener('change', function() {
            if (this.value === 'organizacion') {
                orgFields.style.display = 'block';
                document.getElementById('org_nombre').required = true;
                document.getElementById('org_ruc').required = true;
                document.getElementById('org_direccion').required = true;
            } else {
                orgFields.style.display = 'none';
                document.getElementById('org_nombre').required = false;
                document.getElementById('org_ruc').required = false;
                document.getElementById('org_direccion').required = false;
            }
        });
    });
</script>

<?php require_once __DIR__ . '/../../partials/footer.php'; ?>