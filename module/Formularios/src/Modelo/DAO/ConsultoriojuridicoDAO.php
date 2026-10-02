<?php

namespace Formularios\Modelo\DAO;

use Laminas\Db\TableGateway\AbstractTableGateway;
use Laminas\Db\Adapter\Adapter;
use Laminas\Db\Sql\Expression;
use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\Insert;
use Laminas\Db\Sql\Update;

class ConsultoriojuridicoDAO extends AbstractTableGateway
{

    protected $table = 'form_consultorio_juridico';

    //------------------------------------------------------------------------------

    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
    }

    //------------------------------------------------------------------------------
    public function fetchAll($filtro = '')
    {
        $this->table = 'form_consultorio_juridico';
        $select = new Select($this->table);
        $select->columns(['*']);
        if ($filtro != '') {
            $select->where($filtro);
        } else {
            $select->order("form_consultorio_juridico.idForm  DESC");
        }
        //        echo $select->getSqlString();
        return $this->selectWith($select)->toArray();
    }
    public function getFormDetalle($id = 0)
    {
        $select = new Select('form_consultorio_juridico');
        $select->columns(['*'])->where("form_consultorio_juridico.idForm = $id")->limit(1);
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
