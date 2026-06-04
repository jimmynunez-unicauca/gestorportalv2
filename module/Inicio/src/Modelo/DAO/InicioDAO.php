<?php

namespace Inicio\Modelo\DAO;

use Laminas\Db\TableGateway\AbstractTableGateway;
use Laminas\Db\Adapter\Adapter;
use Laminas\Db\Sql\Expression;
use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\Insert;
use Laminas\Db\Sql\Update;
use Laminas\Db\Sql\Sql;

class InicioDAO extends AbstractTableGateway
{
    private $tablas = [
        'form_academica',
        'form_agraria',
        'form_artes',
        'form_cecav',
        'form_conflicto_interes',
        'form_contables',
        'form_cp',
        'form_cultura',
        'form_dae_empre',
        'form_dae_propiedad',
        'form_egresados',
        'form_emisora',
        'form_fchs',
        'form_fic',
        'form_fiet',
        'form_ocdi',
        'form_orii',
        'form_pqrsf',
        'form_rectoria',
        'form_rendicion_cuentas',
        'form_viceadmin',
        'form_viceinvest',
        'form_facned',
        'form_fderecho',
        'form_fsalud',
        'form_comarca',
        'form_secretariageneral',
        'form_unisalud',
        'form_unisalud_rendicion_cuentas',
    ];

    //------------------------------------------------------------------------------

    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
    }

    //------------------------------------------------------------------------------
    public function getCountForm($tabla)
    {
        $this->table = $tabla;
        $select = new Select($this->table);
        $select->columns(array(
            'total' => new Expression('count(*)'),
        ));
        $datos = $this->selectWith($select)->toArray();
        return $datos[0]['total'];
    }
    //------------------------------------------------------------------------------
    public function getTotalByFechas($tabla, $fechaini = '0000-00-00', $fechafin = '0000-00-00')
    {
        $this->table = $tabla;
        $select = [];
        $select = new Select($this->table);
        $select->columns([
            'total' => new Expression('count(*)'),
        ])->where("DATE(fechahorareg) >= '$fechaini' AND DATE(fechahorareg) < '$fechafin'");
        $datos = $this->selectWith($select)->toArray();
        return $datos;
    }
    //------------------------------------------------------------------------------
    public function obtenerAniosDistinct()
    {
        $sql = new Sql($this->adapter);
        $anios = [];

        foreach ($this->tablas as $tabla) {
            $select = $sql->select();
            $select->from($tabla);
            $select->columns([new Expression('DISTINCT YEAR(fechahorareg) AS anio')]);

            $statement = $sql->prepareStatementForSqlObject($select);
            $result = $statement->execute();

            foreach ($result as $row) {
                if (!in_array($row['anio'], $anios)) {
                    $anios[] = $row['anio'];
                }
            }
        }

        sort($anios); // Ordena los años de forma ascendente
        return $anios;
    }

    //------------------------------------------------------------------------------

    // NUEVO: Obtener últimas actividades de todos los formularios
    public function getUltimasActividades($limite = 10)
    {
        $actividades = [];

        foreach ($this->tablas as $tabla) {
            $this->table = $tabla;
            $select = new Select($this->table);
            $select->columns([
                'fechahorareg',
                'nombre'
            ])
                ->order('fechahorareg DESC')
                ->limit(2); // Solo 2 por tabla para no sobrecargar

            $resultados = $this->selectWith($select)->toArray();

            foreach ($resultados as $row) {
                $actividades[] = [
                    'tipo' => $this->getNombreFormulario($tabla),
                    'id' => 'N/A',
                    'fecha' => $row['fechahorareg'],
                    'usuario' => $row['nombre'],
                    'estado' => 'Activo', // Default
                    'icono' => $this->getIconoFormulario($tabla),
                    'color' => $this->getColorFormulario($tabla)
                ];
            }
        }

        // Ordenar por fecha descendente y limitar
        usort($actividades, function ($a, $b) {
            return strtotime($b['fecha']) - strtotime($a['fecha']);
        });

        return array_slice($actividades, 0, $limite);
    }

    // NUEVO: Obtener eventos próximos
    public function getEventosProximos($limite = 5)
    {
        try {
            $this->table = 'evento';
            $select = new Select($this->table);
            $select->columns(['titulo', 'start', 'end', 'lugar', 'color'])
                ->where("start >= '" . date('Y-m-d') . "'")
                ->order('start ASC')
                ->limit($limite);

            return $this->selectWith($select)->toArray();
        } catch (\Exception $e) {
            return [];
        }
    }

    // NUEVO: Obtener documentos recientes
    public function getDocumentosRecientes($limite = 5)
    {
        try {
            $this->table = 'archivos';
            $select = new Select($this->table);
            $select->columns(['nombre', 'tipo', 'publicacion', 'archivo'])
                ->order('publicacion DESC')
                ->limit($limite);

            return $this->selectWith($select)->toArray();
        } catch (\Exception $e) {
            return [];
        }
    }

    // NUEVO: Estadísticas semanales
    public function getEstadisticasSemanales($anio, $mes)
    {
        $semanas = [];
        $diasEnMes = cal_days_in_month(CAL_GREGORIAN, $mes, $anio);

        for ($semana = 1; $semana <= 5; $semana++) {
            $inicioSemana = date('Y-m-d', strtotime("$anio-$mes-01 + " . (($semana - 1) * 7) . " days"));
            $finSemana = date('Y-m-d', strtotime("$inicioSemana + 6 days"));

            if (strtotime($finSemana) > strtotime("$anio-$mes-$diasEnMes")) {
                $finSemana = "$anio-$mes-$diasEnMes";
            }

            $total = 0;
            foreach ($this->tablas as $tabla) {
                $this->table = $tabla;
                $select = new Select($this->table);
                $select->columns([new Expression('COUNT(*) as count')])
                    ->where("DATE(fechahorareg) BETWEEN '$inicioSemana' AND '$finSemana'");
                $result = $this->selectWith($select)->toArray();
                $total += $result[0]['count'];
            }

            $semanas[] = [
                'semana' => $semana,
                'inicio' => $inicioSemana,
                'fin' => $finSemana,
                'total' => $total
            ];
        }

        return $semanas;
    }

    // Helpers para nombres, iconos y colores
    private function getNombreFormulario($tabla)
    {
        $nombres = [
            'form_academica' => 'Académica',
            'form_agraria' => 'Agrarias',
            'form_artes' => 'Artes',
            'form_cecav' => 'CECAV',
            'form_conflicto_interes' => 'Conflicto de Interés',
            'form_contables' => 'Contables',
            'form_cp' => 'Posgrados',
            'form_cultura' => 'Cultura',
            'form_dae_empre' => 'Emprendimiento',
            'form_dae_propiedad' => 'Propiedad Intelectual',
            'form_egresados' => 'Egresados',
            'form_emisora' => 'Emisora',
            'form_fchs' => 'FCHS',
            'form_fic' => 'FIC',
            'form_fiet' => 'FIET',
            'form_ocdi' => 'OCDI',
            'form_orii' => 'ORII',
            'form_pqrsf' => 'PQRSF',
            'form_rectoria' => 'Rectoría',
            'form_rendicion_cuentas' => 'Rendición de Cuentas',
            'form_viceadmin' => 'Vic. Administrativa',
            'form_viceinvest' => 'Vic. Investigaciones',
            'form_facned' => 'FACNED',
            'form_fderecho' => 'Fac. Derecho',
            'form_fsalud' => 'Fac. Salud',
            'form_comarca' => 'CoMarca',
            'form_secretariageneral' => 'Secretaría General',
        ];
        return $nombres[$tabla] ?? $tabla;
    }

    private function getIconoFormulario($tabla)
    {
        $iconos = [
            'form_academica' => 'fa-graduation-cap',
            'form_agraria' => 'fa-leaf',
            'form_artes' => 'fa-palette',
            'form_cecav' => 'fa-video',
            'form_conflicto_interes' => 'fa-balance-scale',
            'form_contables' => 'fa-calculator',
            'form_cp' => 'fa-user-graduate',
            'form_cultura' => 'fa-theater-masks',
            'form_dae_empre' => 'fa-lightbulb',
            'form_dae_propiedad' => 'fa-copyright',
            'form_egresados' => 'fa-user-tie',
            'form_emisora' => 'fa-broadcast-tower',
            'form_fchs' => 'fa-brain',
            'form_fic' => 'fa-hard-hat',
            'form_fiet' => 'fa-microchip',
            'form_ocdi' => 'fa-gavel',
            'form_orii' => 'fa-globe',
            'form_pqrsf' => 'fa-comment-dots',
            'form_rectoria' => 'fa-landmark',
            'form_rendicion_cuentas' => 'fa-file-invoice-dollar',
            'form_viceadmin' => 'fa-clipboard-list',
            'form_viceinvest' => 'fa-flask',
            'form_facned' => 'fa-square-root-alt',
            'form_fderecho' => 'fa-gavel',
            'form_fsalud' => 'fa-heartbeat',
            'form_comarca' => 'fa-broadcast-tower',
            'form_secretariageneral' => 'fa-landmark',
        ];
        return $iconos[$tabla] ?? 'fa-file';
    }

    private function getColorFormulario($tabla)
    {
        $colores = [
            'form_academica' => '#4361ee',
            'form_agraria' => '#4cc9f0',
            'form_artes' => '#f72585',
            'form_cecav' => '#7209b7',
            'form_conflicto_interes' => '#f8961e',
            'form_contables' => '#43aa8b',
            'form_cp' => '#577590',
            'form_cultura' => '#f94144',
            'form_dae_empre' => '#f9c74f',
            'form_dae_propiedad' => '#90be6d',
            'form_egresados' => '#4d908e',
            'form_emisora' => '#277da1',
            'form_fchs' => '#b5838d',
            'form_fic' => '#e76f51',
            'form_fiet' => '#2a9d8f',
            'form_ocdi' => '#e9c46a',
            'form_orii' => '#f4a261',
            'form_pqrsf' => '#e63946',
            'form_rectoria' => '#1e6091',
            'form_rendicion_cuentas' => '#76c893',
            'form_viceadmin' => '#34a0a4',
            'form_viceinvest' => '#b56576',
            'form_facned' => '#6c757d',
            'form_fderecho' => '#adb5bd',
            'form_fsalud' => '#d62828',
            'form_comarca' => '#003049',
            'form_secretariageneral' => '#fcbf49',
        ];
        return $colores[$tabla] ?? '#6c757d';
    }

    //------------------------------------------------------------------------------
}
