<?php

namespace Formularios\Modelo\DAO;

use Laminas\Db\TableGateway\AbstractTableGateway;
use Laminas\Db\Adapter\Adapter;
use Laminas\Db\Sql\Expression;
use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\Insert;
use Laminas\Db\Sql\Update;
use Laminas\Db\Sql\Where;

class PfiformDAO extends AbstractTableGateway
{

    protected $table = 'form_psi';

    //------------------------------------------------------------------------------

    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
    }

    //------------------------------------------------------------------------------
    public function fetchAll($filtro = '')
    {
        $this->table = 'form_psi';
        $select = new Select($this->table);
        $select->columns(['*']);
        if ($filtro != '') {
            $select->where($filtro);
        } else {
            $select->order("form_psi.idForm DESC");
        }
        return $this->selectWith($select)->toArray();
    }

    //------------------------------------------------------------------------------
    public function fetchAllWithFilters($filters = [])
    {
        $select = new Select('form_psi');
        $select->columns(['*']);

        $where = new Where();

        // Filtro por tipo_psi
        if (!empty($filters['tipo_psi'])) {
            $where->equalTo('tipo_psi', $filters['tipo_psi']);
        }

        // Filtro por inscrito_por
        if (!empty($filters['inscrito_por'])) {
            $where->equalTo('inscrito_por', $filters['inscrito_por']);
        }

        // Filtro por periodo_ingreso_universidad
        if (!empty($filters['periodo_ingreso_universidad'])) {
            $where->equalTo('periodo_ingreso_universidad', $filters['periodo_ingreso_universidad']);
        }

        // Filtro por semestre_actual
        if (!empty($filters['semestre_actual'])) {
            $where->equalTo('semestre_actual', $filters['semestre_actual']);
        }

        // NUEVO: Filtro por convocatoria
        if (!empty($filters['id_convocatoria'])) {
            $where->equalTo('id_convocatoria', $filters['id_convocatoria']);
        }

        // Filtro por rango de fechas (fecha_inscripcion)
        if (!empty($filters['fecha_desde']) && !empty($filters['fecha_hasta'])) {
            $where->between('fecha_inscripcion', $filters['fecha_desde'], $filters['fecha_hasta']);
        } elseif (!empty($filters['fecha_desde'])) {
            $where->greaterThanOrEqualTo('fecha_inscripcion', $filters['fecha_desde']);
        } elseif (!empty($filters['fecha_hasta'])) {
            $where->lessThanOrEqualTo('fecha_inscripcion', $filters['fecha_hasta']);
        }

        $select->where($where);
        $select->order("form_psi.idForm DESC");

        return $this->selectWith($select)->toArray();
    }

    //------------------------------------------------------------------------------
    public function getDistinctValues($campo)
    {
        $select = new Select('form_psi');
        $select->columns([$campo]);
        $select->group($campo);
        $select->order($campo . ' ASC');

        $resultados = $this->selectWith($select)->toArray();
        $valores = [];
        foreach ($resultados as $row) {
            if (!empty($row[$campo])) {
                $valores[] = $row[$campo];
            }
        }
        return $valores;
    }

    //------------------------------------------------------------------------------
    public function getFormDetalle($id = 0)
    {
        $select = new Select('form_psi');
        $select->columns(['*'])
            ->join('programas', 'programas.idPrograma = form_psi.idPrograma', [
                'programa',
            ])->join('facultades', 'facultades.idFacultad = programas.idFacultad', [
                'facultad',
            ])->where("form_psi.idForm = $id")->limit(1);
        $datos = $this->selectWith($select)->toArray();
        if (count($datos) > 0) {
            return $datos[0];
        } else {
            return null;
        }
    }

    //------------------------------------------------------------------------------
    public function getMinMaxFechas()
    {
        $select = new Select('form_psi');
        $select->columns([
            'fecha_min' => new Expression('MIN(fecha_inscripcion)'),
            'fecha_max' => new Expression('MAX(fecha_inscripcion)')
        ]);
        $resultado = $this->selectWith($select)->toArray();
        return [
            'min' => $resultado[0]['fecha_min'] ?? date('Y-m-d'),
            'max' => $resultado[0]['fecha_max'] ?? date('Y-m-d')
        ];
    }
    //------------------------------------------------------------------------------
    public function fetchAllForExport($filters = [])
    {
        $select = new Select('form_psi');
        $select->columns([
            '*',
            'programa' => new Expression("(SELECT programa FROM programas WHERE programas.idPrograma = form_psi.idPrograma)"),
            'facultad' => new Expression("(SELECT facultad FROM facultades WHERE facultades.idFacultad = (SELECT idFacultad FROM programas WHERE programas.idPrograma = form_psi.idPrograma))"),
        ]);

        $where = new Where();

        if (!empty($filters['tipo_psi'])) {
            $where->equalTo('tipo_psi', $filters['tipo_psi']);
        }

        if (!empty($filters['inscrito_por'])) {
            $where->equalTo('inscrito_por', $filters['inscrito_por']);
        }

        if (!empty($filters['periodo_ingreso_universidad'])) {
            $where->equalTo('periodo_ingreso_universidad', $filters['periodo_ingreso_universidad']);
        }

        if (!empty($filters['semestre_actual'])) {
            $where->equalTo('semestre_actual', $filters['semestre_actual']);
        }

        // NUEVO: Filtro por convocatoria
        if (!empty($filters['id_convocatoria'])) {
            $where->equalTo('id_convocatoria', $filters['id_convocatoria']);
        }

        if (!empty($filters['fecha_desde']) && !empty($filters['fecha_hasta'])) {
            $where->between('fecha_inscripcion', $filters['fecha_desde'], $filters['fecha_hasta']);
        } elseif (!empty($filters['fecha_desde'])) {
            $where->greaterThanOrEqualTo('fecha_inscripcion', $filters['fecha_desde']);
        } elseif (!empty($filters['fecha_hasta'])) {
            $where->lessThanOrEqualTo('fecha_inscripcion', $filters['fecha_hasta']);
        }

        $select->where($where);
        $select->order("form_psi.idForm DESC");

        return $this->selectWith($select)->toArray();
    }
    //------------------------------------------------------------------------------
    // En PfiformDAO.php, agrega estos métodos

    public function getConvocatorias($soloActivas = false)
    {
        $select = new Select('form_psi_convocatorias');
        $select->columns(['id_convocatoria', 'nombre_convocatoria', 'periodo', 'activo', 'fecha_inicio', 'fecha_fin']);

        if ($soloActivas) {
            $select->where(['activo' => 1]);
            $select->where->lessThanOrEqualTo('fecha_inicio', date('Y-m-d H:i:s'));
            $select->where->greaterThanOrEqualTo('fecha_fin', date('Y-m-d H:i:s'));
        }

        $select->order('fecha_inicio DESC');

        return $this->selectWith($select)->toArray();
    }

    public function getConvocatoriaNombre($idConvocatoria)
    {
        $select = new Select('form_psi_convocatorias');
        $select->columns(['nombre_convocatoria', 'periodo']);
        $select->where(['id_convocatoria' => $idConvocatoria]);
        $select->limit(1);

        $result = $this->selectWith($select)->toArray();
        if (count($result) > 0) {
            return $result[0]['nombre_convocatoria'] . ' (' . $result[0]['periodo'] . ')';
        }
        return '';
    }
    //------------------------------------------------------------------------------
}
