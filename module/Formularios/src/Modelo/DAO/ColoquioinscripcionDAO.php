<?php

namespace Formularios\Modelo\DAO;

use Laminas\Db\TableGateway\AbstractTableGateway;
use Laminas\Db\Adapter\Adapter;
use Laminas\Db\Sql\Expression;
use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\Insert;
use Laminas\Db\Sql\Update;
use Laminas\Db\Sql\Where;

class ColoquioinscripcionDAO extends AbstractTableGateway
{

    protected $table = 'form_coloquio_inscripcion';

    //------------------------------------------------------------------------------

    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
    }

    //------------------------------------------------------------------------------
    public function fetchAll($filtro = '')
    {
        $this->table = 'form_coloquio_inscripcion';
        $select = new Select($this->table);
        $select->columns([
            '*',
            'pais' => new Expression("(SELECT pais FROM countries WHERE countries.id = form_coloquio_inscripcion.idCountries)"),
        ]);
        if ($filtro != '') {
            $select->where($filtro);
        } else {
            $select->order("form_coloquio_inscripcion.idForm DESC");
        }
        return $this->selectWith($select)->toArray();
    }

    //------------------------------------------------------------------------------
    public function getFormDetalle($idForm = 0)
    {
        $select = new Select('form_coloquio_inscripcion');
        $select->columns([
            '*',
            'pais' => new Expression("(SELECT pais FROM countries WHERE countries.id = form_coloquio_inscripcion.idCountries)"),
        ])->where("form_coloquio_inscripcion.idForm = $idForm")->limit(1);
        $datos = $this->selectWith($select)->toArray();
        if (count($datos) > 0) {
            return $datos[0];
        } else {
            return null;
        }
    }

    //------------------------------------------------------------------------------
    // NUEVOS MÉTODOS PARA FILTROS Y EXPORTACIÓN

    public function getPaises()
    {
        $select = new Select('countries');
        $select->columns(['id', 'pais']);
        $select->order('pais ASC');
        return $this->selectWith($select)->toArray();
    }

    public function getDistinctValues($campo)
    {
        $select = new Select('form_coloquio_inscripcion');
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

    public function getMinMaxFechas()
    {
        $select = new Select('form_coloquio_inscripcion');
        $select->columns([
            'fecha_min' => new Expression('MIN(fechahorareg)'),
            'fecha_max' => new Expression('MAX(fechahorareg)')
        ]);
        $resultado = $this->selectWith($select)->toArray();
        return [
            'min' => $resultado[0]['fecha_min'] ?? date('Y-m-d'),
            'max' => $resultado[0]['fecha_max'] ?? date('Y-m-d')
        ];
    }

    public function fetchAllWithFilters($filters = [])
    {
        $select = new Select('form_coloquio_inscripcion');
        $select->columns([
            '*',
            'pais' => new Expression("(SELECT pais FROM countries WHERE countries.id = form_coloquio_inscripcion.idCountries)"),
        ]);

        $where = new Where();

        if (!empty($filters['idCountries'])) {
            $where->equalTo('idCountries', $filters['idCountries']);
        }

        if (!empty($filters['estado'])) {
            $where->equalTo('estado', $filters['estado']);
        }

        if (!empty($filters['rol_academico'])) {
            $where->equalTo('rol_academico', $filters['rol_academico']);
        }

        if (!empty($filters['fecha_desde']) && !empty($filters['fecha_hasta'])) {
            $where->between('fechahorareg', $filters['fecha_desde'] . ' 00:00:00', $filters['fecha_hasta'] . ' 23:59:59');
        } elseif (!empty($filters['fecha_desde'])) {
            $where->greaterThanOrEqualTo('fechahorareg', $filters['fecha_desde'] . ' 00:00:00');
        } elseif (!empty($filters['fecha_hasta'])) {
            $where->lessThanOrEqualTo('fechahorareg', $filters['fecha_hasta'] . ' 23:59:59');
        }

        $select->where($where);
        $select->order("form_coloquio_inscripcion.idForm DESC");

        return $this->selectWith($select)->toArray();
    }

    public function fetchAllForExport($filters = [])
    {
        $select = new Select('form_coloquio_inscripcion');
        $select->columns([
            '*',
            'pais' => new Expression("(SELECT pais FROM countries WHERE countries.id = form_coloquio_inscripcion.idCountries)"),
        ]);

        $where = new Where();

        if (!empty($filters['idCountries'])) {
            $where->equalTo('idCountries', $filters['idCountries']);
        }

        if (!empty($filters['estado'])) {
            $where->equalTo('estado', $filters['estado']);
        }

        if (!empty($filters['rol_academico'])) {
            $where->equalTo('rol_academico', $filters['rol_academico']);
        }

        if (!empty($filters['fecha_desde']) && !empty($filters['fecha_hasta'])) {
            $where->between('fechahorareg', $filters['fecha_desde'] . ' 00:00:00', $filters['fecha_hasta'] . ' 23:59:59');
        } elseif (!empty($filters['fecha_desde'])) {
            $where->greaterThanOrEqualTo('fechahorareg', $filters['fecha_desde'] . ' 00:00:00');
        } elseif (!empty($filters['fecha_hasta'])) {
            $where->lessThanOrEqualTo('fechahorareg', $filters['fecha_hasta'] . ' 23:59:59');
        }

        $select->where($where);
        $select->order("form_coloquio_inscripcion.idForm DESC");

        return $this->selectWith($select)->toArray();
    }
    //------------------------------------------------------------------------------
}
