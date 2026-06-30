<?php

declare(strict_types=1);

namespace Formularios\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\Authentication\AuthenticationService;
use Laminas\View\Model\ViewModel;
use Laminas\View\Model\JsonModel;
use Formularios\Modelo\DAO\ColoquioinscripcionDAO;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

class ColoquioinscripcionController extends AbstractActionController
{

    private $DAO;
    //------------------------------------------------------------------------------

    public function __construct(ColoquioinscripcionDAO $dao)
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
        $paises = $this->DAO->getPaises();
        $estados = $this->DAO->getDistinctValues('estado');
        $roles = $this->DAO->getDistinctValues('rol_academico');
        $fechasRango = $this->DAO->getMinMaxFechas();

        // Obtener filtros de la solicitud
        $filters = [];
        $filters['idCountries'] = $this->params()->fromQuery('idCountries', '');
        $filters['estado'] = $this->params()->fromQuery('estado', '');
        $filters['rol_academico'] = $this->params()->fromQuery('rol_academico', '');
        $filters['fecha_desde'] = $this->params()->fromQuery('fecha_desde', '');
        $filters['fecha_hasta'] = $this->params()->fromQuery('fecha_hasta', '');

        // Verificar si hay filtros activos
        $hasFilters = !empty($filters['idCountries']) || !empty($filters['estado']) ||
            !empty($filters['rol_academico']) ||
            (!empty($filters['fecha_desde']) && !empty($filters['fecha_hasta']));

        // Obtener datos
        if ($hasFilters) {
            $datos = $this->DAO->fetchAllWithFilters($filters);
        } else {
            $datos = $this->DAO->fetchAll('');
        }

        return new ViewModel([
            'fetchAll' => $datos,
            'paises' => $paises,
            'estados' => $estados,
            'roles' => $roles,
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
        return $this->redirect()->toRoute('formularios/coloquioinscripcion', ['action' => 'index']);
    }

    //------------------------------------------------------------------------------
    public function exportarExcelAction()
    {
        // Obtener filtros de la solicitud
        $filters = [];
        $filters['idCountries'] = $this->params()->fromQuery('idCountries', '');
        $filters['estado'] = $this->params()->fromQuery('estado', '');
        $filters['rol_academico'] = $this->params()->fromQuery('rol_academico', '');
        $filters['fecha_desde'] = $this->params()->fromQuery('fecha_desde', '');
        $filters['fecha_hasta'] = $this->params()->fromQuery('fecha_hasta', '');

        // Obtener datos filtrados
        $datos = $this->DAO->fetchAllForExport($filters);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Coloquio_Inscripciones');

        // Encabezados
        $headers = [
            'A1' => 'ID',
            'B1' => 'NOMBRE',
            'C1' => 'CORREO',
            'D1' => 'INSTITUCIÓN',
            'E1' => 'ROL ACADÉMICO',
            'F1' => 'TELÉFONO',
            'G1' => 'PAÍS',
            'H1' => 'ESTADO',
            'I1' => 'REGISTRADO POR',
            'J1' => 'FECHA REGISTRO'
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
            $sheet->setCellValue('B' . $fila, $row['nombre'] ?? '');
            $sheet->setCellValue('C' . $fila, $row['correo'] ?? '');
            $sheet->setCellValue('D' . $fila, $row['institucion'] ?? '');
            $sheet->setCellValue('E' . $fila, $row['rol_academico'] ?? '');
            $sheet->setCellValue('F' . $fila, $row['telefono'] ?? '');
            $sheet->setCellValue('G' . $fila, $row['pais'] ?? '');
            $sheet->setCellValue('H' . $fila, $row['estado'] ?? '');
            $sheet->setCellValue('I' . $fila, $row['registradopor'] ?? '');
            $sheet->setCellValue('J' . $fila, $row['fechahorareg'] ?? '');
            $fila++;
        }

        // Autoajustar columnas
        foreach (range('A', 'J') as $col) {
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
        $sheet->getStyle('A1:J' . ($fila - 1))->applyFromArray($styleArray);

        // Centrar contenido
        $sheet->getStyle('A2:J' . ($fila - 1))->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER);

        // Configurar descarga
        $writer = new Xlsx($spreadsheet);
        $fecha = date('Y-m-d_H-i-s');
        $nombreArchivo = "Coloquio_Inscripciones_{$fecha}.xlsx";

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $nombreArchivo . '"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
    }
    //------------------------------------------------------------------------------
}
