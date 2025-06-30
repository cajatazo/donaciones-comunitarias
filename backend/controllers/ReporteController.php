<?php
require_once __DIR__ . '/../models/Donacion.php';
require_once __DIR__ . '/../models/Necesidad.php';
require_once __DIR__ . '/../models/Organizacion.php';

class ReporteController {
    public function generar() {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
            header('Location: ' . BASE_URL . '/auth/login');
            exit();
        }
        
        $mes = $_GET['mes'] ?? date('m');
        $anio = $_GET['anio'] ?? date('Y');
        
        $donacionModel = new Donacion();
        $necesidadModel = new Necesidad();
        $orgModel = new Organizacion();
        
        $donaciones = $donacionModel->listarTodas();
        $necesidades = $necesidadModel->listarActivas();
        $organizaciones = $orgModel->listar(1); // Solo verificadas
        
        // Filtrar por mes y año si es necesario
        $donacionesFiltradas = array_filter($donaciones, function($d) use ($mes, $anio) {
            $fecha = new DateTime($d['created_at']);
            return $fecha->format('m') == $mes && $fecha->format('Y') == $anio;
        });
        
        require_once __DIR__ . '/../../frontend/views/admin/reportes.php';
    }
    
    public function exportarPDF() {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
            header('Location: ' . BASE_URL . '/auth/login');
            exit();
        }
        
        $mes = $_GET['mes'] ?? date('m');
        $anio = $_GET['anio'] ?? date('Y');
        
        $donacionModel = new Donacion();
        $donaciones = $donacionModel->listarTodas();
        
        // Filtrar por mes y año
        $donacionesFiltradas = array_filter($donaciones, function($d) use ($mes, $anio) {
            $fecha = new DateTime($d['created_at']);
            return $fecha->format('m') == $mes && $fecha->format('Y') == $anio;
        });
        
        // Generar HTML para el PDF
        ob_start();
        include __DIR__ . '/../../frontend/views/admin/reporte_pdf_template.php';
        $html = ob_get_clean();
        
        // Usar una librería como Dompdf o TCPDF para generar el PDF
        // Esto es un ejemplo con Dompdf (necesitarías instalarlo via composer)
        require_once __DIR__ . '/../../vendor/autoload.php';
        
        $dompdf = new Dompdf\Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        
        $dompdf->stream("reporte-donaciones-{$mes}-{$anio}.pdf", ["Attachment" => true]);
        exit();
    }
}
?>