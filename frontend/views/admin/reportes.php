<?php
$mes = isset($_GET['mes']) ? $_GET['mes'] : date('m');
$anio = isset($_GET['anio']) ? $_GET['anio'] : date('Y');
require_once __DIR__ . '/../../../backend/libs/Database.php';
require_once __DIR__ . '/../../../backend/models/Donacion.php';
$donaciones = (new Donacion())->listarTodas();
$donacionesFiltradas = array_filter($donaciones, function($d) use ($mes, $anio) {
    return date('m', strtotime($d['created_at'])) == $mes && date('Y', strtotime($d['created_at'])) == $anio;
});

require_once __DIR__ . '/../../partials/header.php'; ?>
<?php require_once __DIR__ . '/../../partials/navbar.php'; ?>

<main class="container my-5">
    <h1 class="mb-4">Reportes de Donaciones</h1>
    
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form class="row g-3">
                <div class="col-md-3">
                    <label for="mes" class="form-label">Mes</label>
                    <select id="mes" name="mes" class="form-select">
                        <?php for ($i = 1; $i <= 12; $i++): ?>
                            <option value="<?= str_pad($i, 2, '0', STR_PAD_LEFT) ?>" <?= $i == $mes ? 'selected' : '' ?>>
                                <?= DateTime::createFromFormat('!m', $i)->format('F') ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="anio" class="form-label">Año</label>
                    <select id="anio" name="anio" class="form-select">
                        <?php for ($i = date('Y'); $i >= date('Y') - 5; $i--): ?>
                            <option value="<?= $i ?>" <?= $i == $anio ? 'selected' : '' ?>><?= $i ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary">Generar Reporte</button>
                </div>
                <div class="col-md-3 d-flex align-items-end justify-content-end">
                    <a href="<?= BASE_URL ?>/admin/reportes/exportar?mes=<?= $mes ?>&anio=<?= $anio ?>" class="btn btn-success">
                        <i class="fas fa-file-pdf me-2"></i>Exportar a PDF
                    </a>
                </div>
            </form>
        </div>
    </div>
    
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Reporte de Donaciones - <?= DateTime::createFromFormat('!m', $mes)->format('F') ?> <?= $anio ?></h5>
        </div>
        <div class="card-body">
            <?php if (empty($donacionesFiltradas)): ?>
                <div class="alert alert-info">No hay donaciones registradas para este período.</div>
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
                            <?php foreach ($donacionesFiltradas as $donacion): ?>
                                <tr>
                                    <td><?= date('d/m/Y', strtotime($donacion['created_at'])) ?></td>
                                    <td><?= htmlspecialchars($donacion['donante_nombre']) ?></td>
                                    <td><?= htmlspecialchars($donacion['organizacion_nombre']) ?></td>
                                    <td><?= htmlspecialchars($donacion['necesidad_titulo']) ?></td>
                                    <td><?= $donacion['cantidad'] ?></td>
                                    <td>
                                        <span class="badge bg-<?= 
                                            $donacion['estado'] === 'pendiente' ? 'warning' : 
                                            ($donacion['estado'] === 'confirmada' ? 'info' : 
                                            ($donacion['estado'] === 'entregada' ? 'success' : 'secondary')) 
                                        ?>">
                                            <?= ucfirst($donacion['estado']) ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <div class="row mt-4">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header bg-info text-white">
                                <h6 class="mb-0">Resumen por Estado</h6>
                            </div>
                            <div class="card-body">
                                <canvas id="estadoChart" height="200"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header bg-info text-white">
                                <h6 class="mb-0">Resumen por Organización</h6>
                            </div>
                            <div class="card-body">
                                <canvas id="organizacionChart" height="200"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                
                <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        // Datos para los gráficos
                        const estados = {
                            pendiente: <?= count(array_filter($donacionesFiltradas, fn($d) => $d['estado'] === 'pendiente')) ?>,
                            confirmada: <?= count(array_filter($donacionesFiltradas, fn($d) => $d['estado'] === 'confirmada')) ?>,
                            entregada: <?= count(array_filter($donacionesFiltradas, fn($d) => $d['estado'] === 'entregada')) ?>,
                            cancelada: <?= count(array_filter($donacionesFiltradas, fn($d) => $d['estado'] === 'cancelada')) ?>
                        };
                        
                        const orgCounts = {};
                        <?php foreach ($donacionesFiltradas as $d): ?>
                            orgCounts['<?= $d['organizacion_nombre'] ?>'] = (orgCounts['<?= $d['organizacion_nombre'] ?>'] || 0) + 1;
                        <?php endforeach; ?>
                        
                        const orgLabels = Object.keys(orgCounts);
                        const orgData = Object.values(orgCounts);
                        
                        // Gráfico de estados
                        const estadoCtx = document.getElementById('estadoChart').getContext('2d');
                        new Chart(estadoCtx, {
                            type: 'doughnut',
                            data: {
                                labels: ['Pendientes', 'Confirmadas', 'Entregadas', 'Canceladas'],
                                datasets: [{
                                    data: Object.values(estados),
                                    backgroundColor: [
                                        'rgba(255, 206, 86, 0.7)',
                                        'rgba(54, 162, 235, 0.7)',
                                        'rgba(75, 192, 192, 0.7)',
                                        'rgba(153, 102, 255, 0.7)'
                                    ],
                                    borderWidth: 1
                                }]
                            }
                        });
                        
                        // Gráfico de organizaciones
                        const orgCtx = document.getElementById('organizacionChart').getContext('2d');
                        new Chart(orgCtx, {
                            type: 'bar',
                            data: {
                                labels: orgLabels,
                                datasets: [{
                                    label: 'Donaciones',
                                    data: orgData,
                                    backgroundColor: 'rgba(75, 192, 192, 0.7)',
                                    borderColor: 'rgba(75, 192, 192, 1)',
                                    borderWidth: 1
                                }]
                            },
                            options: {
                                scales: {
                                    y: {
                                        beginAtZero: true,
                                        ticks: {
                                            stepSize: 1
                                        }
                                    }
                                }
                            }
                        });
                    });
                </script>
            <?php endif; ?>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/../../partials/footer.php'; ?>