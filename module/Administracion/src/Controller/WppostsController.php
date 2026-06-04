<?php

declare(strict_types=1);

namespace Administracion\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\Authentication\AuthenticationService;
use Laminas\View\Model\ViewModel;
use Laminas\View\Model\JsonModel;
use Administracion\Modelo\DAO\WppostsDAO;
use Administracion\Formularios\WppostsForm;
use Administracion\Modelo\Entidades\Wpposts;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

require_once __DIR__ . '/../../../../vendor/tecnickcom/tcpdf/tcpdf.php';

class WppostsController extends AbstractActionController
{

    private $DAO;

    //------------------------------------------------------------------------------

    public function __construct(WppostsDAO $dao)
    {
        $this->DAO = $dao;
    }

    //------------------------------------------------------------------------------

    public function indexAction()
    {
        $usuarios = $this->DAO->getUsuarios();
        $fechaInicio = $this->params()->fromQuery('fechaInicio', date('Y-m-01 00:00:00'));
        $fechaFin = $this->params()->fromQuery('fechaFin', date('Y-m-01 00:00:00', strtotime('+1 month')));
        $usuarioId = $this->params()->fromQuery('usuarioId', '');
        $filtros = [];
        // Filtro por fechas
        if (!empty($fechaInicio)) {
            $fechaInicio = date('Y-m-d 00:00:00', strtotime($fechaInicio));
            $filtros[] = "wp_posts.post_date >= '$fechaInicio'";
        }
        if (!empty($fechaFin)) {
            $fechaFin = date('Y-m-d 23:59:59', strtotime($fechaFin));
            $filtros[] = "wp_posts.post_date <= '$fechaFin'";
        }
        // Filtro por usuario
        if (!empty($usuarioId)) {
            $filtros[] = "wp_posts.post_author = $usuarioId";
        }
        // Combinar filtros
        $filtro = !empty($filtros) ? implode(' AND ', $filtros) : '';
        if ($this->getRequest()->isXmlHttpRequest()) {
            return new JsonModel([
                'fetchAll' => $this->DAO->fetchAll($filtro),
            ]);
        }
        return new ViewModel([
            'fetchAll' => $this->DAO->fetchAll($filtro),
            'usuarios' => $usuarios,
        ]);
    }

    //------------------------------------------------------------------------------  

    public function detalleAction()
    {
        $revision_id = (int) $this->params()->fromQuery('revision_id', 0);
        $view = new ViewModel([
            'detalle' => $this->DAO->getWppostsDetalle($revision_id),
        ]);
        $view->setTerminal(true);
        return $view;
    }

    //------------------------------------------------------------------------------  
    public function exportAction()
    {
        // Obtener parámetros de filtrado
        $fechaInicio = $this->params()->fromQuery('fechaInicio', '');
        $fechaFin = $this->params()->fromQuery('fechaFin', '');
        $usuarioId = $this->params()->fromQuery('usuarioId', '');
        $format = $this->params()->fromQuery('format', 'excel');

        // Aplicar mismos filtros que en indexAction
        $filtros = [];
        if (!empty($fechaInicio)) {
            $filtros[] = "wp_posts.post_date >= '" . date('Y-m-d 00:00:00', strtotime($fechaInicio)) . "'";
        }
        if (!empty($fechaFin)) {
            $filtros[] = "wp_posts.post_date <= '" . date('Y-m-d 23:59:59', strtotime($fechaFin)) . "'";
        }
        if (!empty($usuarioId)) {
            $filtros[] = "wp_posts.post_author = " . (int)$usuarioId;
        }
        $filtro = implode(' AND ', $filtros);

        // Obtener datos filtrados
        $datos = $this->DAO->fetchAll($filtro);

        // Preparar respuesta según formato
        switch ($format) {
            case 'pdf':
                return $this->exportToPdf($datos);
            case 'excel':
            default:
                return $this->exportToExcel($datos);
        }
    }

    private function exportToExcel($data)
    {
        // Crear contenido Excel
        $content = "ID Revision\tTitulo\tAutor\tCorreo\tpost_parent\tFecha\n";

        foreach ($data as $row) {
            $content .= "{$row['revision_id']}\t"
                . "{$row['post_title']}\t"
                . "{$row['author_name']}\t"
                . "{$row['author_email']}\t"
                . "{$row['post_parent']}\t"
                . "{$row['post_date']}\n";
        }

        // Configurar headers
        $response = $this->getResponse();
        $fechaHora = date('Ymd_His');
        $response->getHeaders()
            ->addHeaderLine('Content-Type', 'application/vnd.ms-excel')
            ->addHeaderLine('Content-Disposition', 'attachment; filename="reporte_historial_' . $fechaHora . '.xls"')
            ->addHeaderLine('Pragma', 'no-cache')
            ->addHeaderLine('Expires', '0');

        $response->setContent($content);
        return $response;
    }

    private function exportToPdf($data)
    {
        // Validación: si hay demasiados registros, se redirige con mensaje de error
        if (count($data) > 10000) {
            $this->flashMessenger()->addErrorMessage('No se puede generar el PDF: el número de registros supera el límite permitido (10.000). Intenta aplicar filtros.');
            return $this->redirect()->toUrl('index');
        }

        // Opcional: aumentar memoria y tiempo de ejecución si estás cerca del límite
        ini_set('memory_limit', '2048M');
        set_time_limit(0);

        // Requiere librería como TCPDF o mPDF
        $pdf = new \TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        $pdf->SetCreator('GestorPortal');
        $pdf->SetAuthor('Sistema');
        $pdf->SetTitle('Historial de Cambios');
        $pdf->AddPage();

        // Cabecera
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(0, 10, 'Historial de Cambios', 0, 1, 'C');

        // Filtros aplicados
        $pdf->SetFont('helvetica', '', 10);
        $filtros = [
            'Fecha inicio: ' . $this->params()->fromQuery('fechaInicio', 'N/A'),
            'Fecha fin: ' . $this->params()->fromQuery('fechaFin', 'N/A'),
            'Usuario ID: ' . $this->params()->fromQuery('usuarioId', 'Todos')
        ];
        $pdf->MultiCell(0, 10, implode(' | ', $filtros), 0, 'L');

        // Datos
        $pdf->SetFont('helvetica', '', 8);
        $html = '<table border="1" cellpadding="4">
        <tr>
            <th>ID</th><th>Título</th><th>Autor</th><th>Correo</th><th>post_parent</th><th>Fecha</th>
        </tr>';

        foreach ($data as $row) {
            $html .= '<tr>'
                . '<td>' . $row['revision_id'] . '</td>'
                . '<td>' . $row['post_title'] . '</td>'
                . '<td>' . $row['author_name'] . '</td>'
                . '<td>' . $row['author_email'] . '</td>'
                . '<td>' . $row['post_parent'] . '</td>'
                . '<td>' . $row['post_date'] . '</td>'
                . '</tr>';
        }

        $html .= '</table>';
        $pdf->writeHTML($html, true, false, true, false, '');

        // Salida
        $response = $this->getResponse();
        $fechaHora = date('Ymd_His');
        $response->getHeaders()
            ->addHeaderLine('Content-Type', 'application/pdf')
            ->addHeaderLine('Content-Disposition', 'attachment; filename="reporte_historial_' . $fechaHora . '.pdf"');

        $response->setContent($pdf->Output('reporte.pdf', 'S'));
        return $response;
    }

    //------------------------------------------------------------------------------   
}
