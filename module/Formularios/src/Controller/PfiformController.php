<?php

declare(strict_types=1);

namespace Formularios\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\Authentication\AuthenticationService;
use Laminas\View\Model\ViewModel;
use Laminas\View\Model\JsonModel;
use Formularios\Modelo\DAO\PfiformDAO;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

class PfiformController extends AbstractActionController
{

    private $DAO;
    //------------------------------------------------------------------------------

    public function __construct(PfiformDAO $dao)
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
        // Obtener valores únicos para los filtros
        $tiposPsi = $this->DAO->getDistinctValues('tipo_psi');
        $inscritosPor = $this->DAO->getDistinctValues('inscrito_por');
        $periodosIngreso = $this->DAO->getDistinctValues('periodo_ingreso_universidad');
        $semestresActuales = $this->DAO->getDistinctValues('semestre_actual');
        $fechasRango = $this->DAO->getMinMaxFechas();

        // Obtener convocatorias
        $convocatorias = $this->DAO->getConvocatorias(false);

        // Crear un mapa de id_convocatoria => nombre_convocatoria para usar en la vista
        $mapaConvocatorias = [];
        foreach ($convocatorias as $conv) {
            $mapaConvocatorias[$conv['id_convocatoria']] = $conv['nombre_convocatoria'] . ' (' . $conv['periodo'] . ')';
        }

        // Obtener filtros de la solicitud
        $filters = [];
        $filters['tipo_psi'] = $this->params()->fromQuery('tipo_psi', '');
        $filters['inscrito_por'] = $this->params()->fromQuery('inscrito_por', '');
        $filters['periodo_ingreso_universidad'] = $this->params()->fromQuery('periodo_ingreso_universidad', '');
        $filters['semestre_actual'] = $this->params()->fromQuery('semestre_actual', '');
        $filters['id_convocatoria'] = $this->params()->fromQuery('id_convocatoria', '');
        $filters['fecha_desde'] = $this->params()->fromQuery('fecha_desde', '');
        $filters['fecha_hasta'] = $this->params()->fromQuery('fecha_hasta', '');

        // Aplicar filtros
        $hasFilters = !empty($filters['tipo_psi']) || !empty($filters['inscrito_por']) ||
            !empty($filters['periodo_ingreso_universidad']) || !empty($filters['semestre_actual']) ||
            !empty($filters['id_convocatoria']) ||
            (!empty($filters['fecha_desde']) && !empty($filters['fecha_hasta']));

        if ($hasFilters) {
            $datos = $this->DAO->fetchAllWithFilters($filters);
        } else {
            $datos = $this->DAO->fetchAll('');
        }

        return new ViewModel([
            'fetchAll' => $datos,
            'tiposPsi' => $tiposPsi,
            'inscritosPor' => $inscritosPor,
            'periodosIngreso' => $periodosIngreso,
            'semestresActuales' => $semestresActuales,
            'convocatorias' => $convocatorias,
            'mapaConvocatorias' => $mapaConvocatorias,  // NUEVO: mapa para la vista
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
        return $this->redirect()->toRoute('pfiform', ['action' => 'index']);
    }
    //------------------------------------------------------------------------------
    public function exportarExcelAction()
    {
        // Obtener filtros de la solicitud
        $filters = [];
        $filters['tipo_psi'] = $this->params()->fromQuery('tipo_psi', '');
        $filters['inscrito_por'] = $this->params()->fromQuery('inscrito_por', '');
        $filters['periodo_ingreso_universidad'] = $this->params()->fromQuery('periodo_ingreso_universidad', '');
        $filters['semestre_actual'] = $this->params()->fromQuery('semestre_actual', '');
        $filters['id_convocatoria'] = $this->params()->fromQuery('id_convocatoria', '');
        $filters['fecha_desde'] = $this->params()->fromQuery('fecha_desde', '');
        $filters['fecha_hasta'] = $this->params()->fromQuery('fecha_hasta', '');

        // Obtener datos filtrados
        $datos = $this->DAO->fetchAllForExport($filters);

        // Obtener mapa de convocatorias para mostrar el nombre
        $convocatorias = $this->DAO->getConvocatorias(false);
        $mapaConvocatorias = [];
        foreach ($convocatorias as $conv) {
            $mapaConvocatorias[$conv['id_convocatoria']] = $conv['nombre_convocatoria'] . ' (' . $conv['periodo'] . ')';
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('PFI_Registros');

        // Encabezados - AGREGADA columna CONVOCATORIA
        $headers = [
            'A1' => 'ID',
            'B1' => 'TIPO IDENTIFICACIÓN',
            'C1' => 'IDENTIFICACIÓN',
            'D1' => 'APELLIDOS',
            'E1' => 'NOMBRES',
            'F1' => 'CÓDIGO ESTUDIANTE',
            'G1' => 'FACULTAD',
            'H1' => 'PROGRAMA',
            'I1' => 'PERIODO INGRESO',
            'J1' => 'SEMESTRE ACTUAL',
            'K1' => 'CORREO',
            'L1' => 'INSCRITO POR',
            'M1' => 'TIPO PSI',
            'N1' => 'ID CONFIG',
            'O1' => 'ID CONVOCATORIA',
            'P1' => 'CONVOCATORIA',
            'Q1' => 'ESTADO',
            'R1' => 'REGISTRADO POR',
            'S1' => 'FECHA HORA REG',
            'T1' => 'FECHA INSCRIPCIÓN'
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
            $nombreConvocatoria = $mapaConvocatorias[$row['id_convocatoria'] ?? 0] ?? 'Sin convocatoria';

            $sheet->setCellValue('A' . $fila, $row['idForm'] ?? '');
            $sheet->setCellValue('B' . $fila, $row['tipoIdentificacion'] ?? '');
            $sheet->setCellValue('C' . $fila, $row['identificacion'] ?? '');
            $sheet->setCellValue('D' . $fila, $row['apellidos'] ?? '');
            $sheet->setCellValue('E' . $fila, $row['nombre'] ?? '');
            $sheet->setCellValue('F' . $fila, $row['codigo_estudiante'] ?? '');
            $sheet->setCellValue('G' . $fila, $row['facultad'] ?? '');
            $sheet->setCellValue('H' . $fila, $row['programa'] ?? '');
            $sheet->setCellValue('I' . $fila, $row['periodo_ingreso_universidad'] ?? '');
            $sheet->setCellValue('J' . $fila, $row['semestre_actual'] ?? '');
            $sheet->setCellValue('K' . $fila, $row['correo'] ?? '');
            $sheet->setCellValue('L' . $fila, $row['inscrito_por'] ?? '');
            $sheet->setCellValue('M' . $fila, $row['tipo_psi'] ?? '');
            $sheet->setCellValue('N' . $fila, $row['id_config'] ?? '');
            $sheet->setCellValue('O' . $fila, $row['id_convocatoria'] ?? '');
            $sheet->setCellValue('P' . $fila, $nombreConvocatoria);
            $sheet->setCellValue('Q' . $fila, $row['estado'] ?? '');
            $sheet->setCellValue('R' . $fila, $row['registradopor'] ?? '');
            $sheet->setCellValue('S' . $fila, $row['fechahorareg'] ?? '');
            $sheet->setCellValue('T' . $fila, $row['fecha_inscripcion'] ?? '');
            $fila++;
        }

        // Autoajustar columnas (ahora hasta la T)
        foreach (range('A', 'T') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Agregar bordes a todas las celdas con datos (ahora hasta T)
        $styleArray = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000']
                ]
            ]
        ];
        $sheet->getStyle('A1:T' . ($fila - 1))->applyFromArray($styleArray);

        // Centrar contenido de las celdas de datos
        $sheet->getStyle('A2:T' . ($fila - 1))->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER);

        // Configurar descarga
        $writer = new Xlsx($spreadsheet);
        $fecha = date('Y-m-d_H-i-s');
        $nombreArchivo = "PFI_Registros_{$fecha}.xlsx";

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $nombreArchivo . '"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
    }
    //------------------------------------------------------------------------------
}
