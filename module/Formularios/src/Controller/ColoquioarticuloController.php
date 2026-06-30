<?php

declare(strict_types=1);

namespace Formularios\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\Authentication\AuthenticationService;
use Laminas\View\Model\ViewModel;
use Laminas\View\Model\JsonModel;
use Formularios\Modelo\DAO\ColoquioarticuloDAO;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

class ColoquioarticuloController extends AbstractActionController
{

    private $DAO;
    //------------------------------------------------------------------------------

    public function __construct(ColoquioarticuloDAO $dao)
    {
        $this->DAO = $dao;
    }

    //------------------------------------------------------------------------------

    public function getInfoSesion()
    {
        $infoSesion = [
            'idEmpleadoCliente ' => 0,
            'login' => 'SIN INICIO DE SESION'
        ];
        $auth = new AuthenticationService();
        if ($auth->hasIdentity()) {
            $infoSesion['login'] = $auth->getIdentity()->login;
            $infoSesion['idEmpleadoCliente'] = $auth->getIdentity()->idEmpleadoCliente;
        }
        return $infoSesion;
    }

    //------------------------------------------------------------------------------
    public function indexAction()
    {
        // Obtener valores para filtros
        $estados = $this->DAO->getDistinctValues('estado');
        $fechasRango = $this->DAO->getMinMaxFechas();

        // Obtener filtros de la solicitud
        $filters = [];
        $filters['estado'] = $this->params()->fromQuery('estado', '');
        $filters['fecha_desde'] = $this->params()->fromQuery('fecha_desde', '');
        $filters['fecha_hasta'] = $this->params()->fromQuery('fecha_hasta', '');
        $filters['titulo'] = $this->params()->fromQuery('titulo', '');

        // Verificar si hay filtros activos
        $hasFilters = !empty($filters['estado']) ||
            (!empty($filters['fecha_desde']) && !empty($filters['fecha_hasta'])) ||
            !empty($filters['titulo']);

        // Obtener datos
        if ($hasFilters) {
            $datos = $this->DAO->fetchAllWithFilters($filters);
        } else {
            $datos = $this->DAO->fetchAll('');
        }

        return new ViewModel([
            'fetchAll' => $datos,
            'estados' => $estados,
            'filters' => $filters,
            'fechasRango' => $fechasRango,
            'hasFilters' => $hasFilters
        ]);
    }

    //------------------------------------------------------------------------------  
    public function detalleAction()
    {
        $id = (int) $this->params()->fromQuery('id', 0);
        $infoEmpleado = $this->DAO->getFormDetalle($id);
        $view = new ViewModel(['form' => $infoEmpleado]);
        $view->setTerminal(true);
        return $view;
    }

    //------------------------------------------------------------------------------
    public function limpiarFiltrosAction()
    {
        return $this->redirect()->toRoute('formularios/coloquioarticulo', ['action' => 'index']);
    }

    //------------------------------------------------------------------------------
    public function exportarExcelAction()
    {
        // Obtener filtros de la solicitud
        $filters = [];
        $filters['estado'] = $this->params()->fromQuery('estado', '');
        $filters['fecha_desde'] = $this->params()->fromQuery('fecha_desde', '');
        $filters['fecha_hasta'] = $this->params()->fromQuery('fecha_hasta', '');
        $filters['titulo'] = $this->params()->fromQuery('titulo', '');

        // Obtener datos filtrados
        $datos = $this->DAO->fetchAllForExport($filters);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Coloquio_Articulos');

        // Encabezados
        $headers = [
            'A1' => 'ID',
            'B1' => 'TÍTULO',
            'C1' => 'RESUMEN',
            'D1' => 'PALABRAS CLAVE',
            'E1' => 'AUTORES',
            'F1' => 'AFILIACIONES',
            'G1' => 'SUGERENCIA EVALUADORES',
            'H1' => 'CORREO',
            'I1' => 'TELÉFONO',
            'J1' => 'ESTADO',
            'K1' => 'REGISTRADO POR',
            'L1' => 'FECHA REGISTRO'
        ];

        foreach ($headers as $celda => $titulo) {
            $sheet->setCellValue($celda, $titulo);
            $sheet->getStyle($celda)->getFont()->setBold(true);
            $sheet->getStyle($celda)->getFill()->setFillType(Fill::FILL_SOLID);
            $sheet->getStyle($celda)->getFill()->getStartColor()->setRGB('000066');
            $sheet->getStyle($celda)->getFont()->getColor()->setRGB('FFFFFF');
            $sheet->getStyle($celda)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }

        // Llenar datos
        $fila = 2;
        foreach ($datos as $row) {
            $sheet->setCellValue('A' . $fila, $row['idForm'] ?? '');
            $sheet->setCellValue('B' . $fila, $row['titulo'] ?? '');
            $sheet->setCellValue('C' . $fila, $row['resumen'] ?? '');
            $sheet->setCellValue('D' . $fila, $row['palabras_clave'] ?? '');
            $sheet->setCellValue('E' . $fila, $row['autores'] ?? '');
            $sheet->setCellValue('F' . $fila, $row['afiliaciones'] ?? '');
            $sheet->setCellValue('G' . $fila, $row['sugerencia_evaluadores'] ?? '');
            $sheet->setCellValue('H' . $fila, $row['correo'] ?? '');
            $sheet->setCellValue('I' . $fila, $row['telefono'] ?? '');
            $sheet->setCellValue('J' . $fila, $row['estado'] ?? '');
            $sheet->setCellValue('K' . $fila, $row['registradopor'] ?? '');
            $sheet->setCellValue('L' . $fila, $row['fechahorareg'] ?? '');
            $fila++;
        }

        // Autoajustar columnas
        foreach (range('A', 'L') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Bordes
        $styleArray = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000']
                ]
            ]
        ];
        $sheet->getStyle('A1:L' . ($fila - 1))->applyFromArray($styleArray);

        // Centrar contenido
        $sheet->getStyle('A2:L' . ($fila - 1))->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER);

        // Configurar descarga
        $writer = new Xlsx($spreadsheet);
        $fecha = date('Y-m-d_H-i-s');
        $nombreArchivo = "Coloquio_Articulos_{$fecha}.xlsx";

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $nombreArchivo . '"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
    }
    //------------------------------------------------------------------------------
}