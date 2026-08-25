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
        'form_cegeco',
        /* 'form_coloquio_articulo', */
        'form_coloquio_inscripcion',
        'form_comarca',
        'form_conflicto_interes',
        'form_contables',
        'form_contables_feria',
        'form_cp',
        'form_cultura',
        'form_dae_empre',
        'form_dae_propiedad',
        'form_egresados',
        'form_emisora',
        'form_facned',
        'form_fchs',
        'form_fderecho',
        'form_fic',
        'form_fiet',
        'form_fsalud',
        'form_ocdi',
        'form_orii',
        'form_pqrsf',
        'form_psi',
        'form_rectoria',
        'form_rendicion_cuentas',
        'form_secretariageneral',
        'form_unicauca_virtual',
        'form_unisalud',
        'form_unisalud_afiliacion',
        'form_unisalud_rendicion_cuentas',
        'form_viceadmin',
        'form_viceinvest',
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
            'form_cegeco' => 'CEGECO',
            'form_coloquio_articulo' => 'Coloquio Artículo',
            'form_coloquio_inscripcion' => 'Coloquio Inscripción',
            'form_comarca' => 'CoMarca',
            'form_conflicto_interes' => 'Conflicto de Interés',
            'form_contables' => 'Contables',
            'form_contables_feria' => 'Contables Feria',
            'form_cp' => 'Posgrados',
            'form_cultura' => 'Cultura',
            'form_dae_empre' => 'Emprendimiento',
            'form_dae_propiedad' => 'Propiedad Intelectual',
            'form_egresados' => 'Egresados',
            'form_emisora' => 'Emisora',
            'form_facned' => 'FACNED',
            'form_fchs' => 'FCHS',
            'form_fderecho' => 'Fac. Derecho',
            'form_fic' => 'FIC',
            'form_fiet' => 'FIET',
            'form_fsalud' => 'Fac. Salud',
            'form_ocdi' => 'OCDI',
            'form_orii' => 'ORII',
            'form_pqrsf' => 'PQRSF',
            'form_psi' => 'PSI',
            'form_rectoria' => 'Rectoría',
            'form_rendicion_cuentas' => 'Rendición de Cuentas',
            'form_secretariageneral' => 'Secretaría General',
            'form_unicauca_virtual' => 'Unicauca Virtual',
            'form_unisalud' => 'Unisalud',
            'form_unisalud_afiliacion' => 'Unisalud Afiliación',
            'form_unisalud_rendicion_cuentas' => 'Unisalud RC',
            'form_viceadmin' => 'Vic. Administrativa',
            'form_viceinvest' => 'Vic. Investigaciones',
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
            'form_cegeco' => 'fa-headset',
            'form_coloquio_articulo' => 'fa-file-alt',
            'form_coloquio_inscripcion' => 'fa-pencil-alt',
            'form_comarca' => 'fa-broadcast-tower',
            'form_conflicto_interes' => 'fa-balance-scale',
            'form_contables' => 'fa-calculator',
            'form_contables_feria' => 'fa-chart-bar',
            'form_cp' => 'fa-user-graduate',
            'form_cultura' => 'fa-theater-masks',
            'form_dae_empre' => 'fa-lightbulb',
            'form_dae_propiedad' => 'fa-copyright',
            'form_egresados' => 'fa-user-tie',
            'form_emisora' => 'fa-broadcast-tower',
            'form_facned' => 'fa-square-root-alt',
            'form_fchs' => 'fa-brain',
            'form_fderecho' => 'fa-gavel',
            'form_fic' => 'fa-hard-hat',
            'form_fiet' => 'fa-microchip',
            'form_fsalud' => 'fa-heartbeat',
            'form_ocdi' => 'fa-gavel',
            'form_orii' => 'fa-globe',
            'form_pqrsf' => 'fa-comment-dots',
            'form_psi' => 'fa-brain',
            'form_rectoria' => 'fa-landmark',
            'form_rendicion_cuentas' => 'fa-file-invoice-dollar',
            'form_secretariageneral' => 'fa-landmark',
            'form_unicauca_virtual' => 'fa-laptop',
            'form_unisalud' => 'fa-hospital-alt',
            'form_unisalud_afiliacion' => 'fa-user-md',
            'form_unisalud_rendicion_cuentas' => 'fa-hospital',
            'form_viceadmin' => 'fa-clipboard-list',
            'form_viceinvest' => 'fa-flask',
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
            'form_cegeco' => '#4361ee',
            'form_coloquio_articulo' => '#6a4c93',
            'form_coloquio_inscripcion' => '#1982c4',
            'form_comarca' => '#003049',
            'form_conflicto_interes' => '#f8961e',
            'form_contables' => '#43aa8b',
            'form_contables_feria' => '#ff9f1c',
            'form_cp' => '#577590',
            'form_cultura' => '#f94144',
            'form_dae_empre' => '#f9c74f',
            'form_dae_propiedad' => '#90be6d',
            'form_egresados' => '#4d908e',
            'form_emisora' => '#277da1',
            'form_facned' => '#6c757d',
            'form_fchs' => '#b5838d',
            'form_fderecho' => '#adb5bd',
            'form_fic' => '#e76f51',
            'form_fiet' => '#2a9d8f',
            'form_fsalud' => '#d62828',
            'form_ocdi' => '#e9c46a',
            'form_orii' => '#f4a261',
            'form_pqrsf' => '#e63946',
            'form_psi' => '#2ec4b6',
            'form_rectoria' => '#1e6091',
            'form_rendicion_cuentas' => '#76c893',
            'form_secretariageneral' => '#fcbf49',
            'form_unicauca_virtual' => '#f72585',
            'form_unisalud' => '#9d4edd',
            'form_unisalud_afiliacion' => '#e71d36',
            'form_unisalud_rendicion_cuentas' => '#6a057f',
            'form_viceadmin' => '#34a0a4',
            'form_viceinvest' => '#b56576',
        ];
        return $colores[$tabla] ?? '#6c757d';
    }

    //------------------------------------------------------------------------------
}
