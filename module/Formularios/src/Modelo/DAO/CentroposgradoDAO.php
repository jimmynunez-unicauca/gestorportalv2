<?php

namespace Formularios\Modelo\DAO;

use Laminas\Db\TableGateway\AbstractTableGateway;
use Laminas\Db\Adapter\Adapter;
use Laminas\Db\Sql\Expression;
use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\Insert;
use Laminas\Db\Sql\Update;

class CentroposgradoDAO extends AbstractTableGateway
{

    protected $table = 'form_cp';

    //------------------------------------------------------------------------------

    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
    }

    //------------------------------------------------------------------------------
    public function fetchAll($filtro = '')
    {
        $this->table = 'form_cp';
        $select = new Select($this->table);
        $select->columns(['*'])->join('posgrados', 'posgrados.idPosgrado = form_cp.idPosgrado', [
            'posgrado',
        ])->join('facultades', 'facultades.idFacultad = posgrados.idFacultad', [
            'facultad',
        ]);
        if ($filtro != '') {
            $select->where($filtro);
        } else {
            $select->order("form_cp.idFormCP DESC");
        }
        //        echo $select->getSqlString();
        return $this->selectWith($select)->toArray();
    }
    public function getFormDetalle($id = 0)
    {
        $select = new Select('form_cp');
        $select->columns(['*'])->join('posgrados', 'posgrados.idPosgrado = form_cp.idPosgrado', [
            'posgrado',
        ])->join('facultades', 'facultades.idFacultad = posgrados.idFacultad', [
            'facultad',
        ])->where("form_cp.idFormCP = $id")->limit(1);
        //        echo $select->getSqlString();
        $datos = $this->selectWith($select)->toArray();
        if (count($datos) > 0) {
            return $datos[0];
        } else {
            return null;
        }
    }
    //------------------------------------------------------------------------------

}
