<?php

namespace Administracion\Modelo\DAO;

use Laminas\Db\TableGateway\AbstractTableGateway;
use Laminas\Db\Adapter\Adapter;
use Laminas\Db\Sql\Expression;
use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\Insert;
use Laminas\Db\Sql\Update;
use Laminas\Db\Sql\Delete;

class WploginDAO extends AbstractTableGateway
{

    protected $table = 'wp_posts';

    //------------------------------------------------------------------------------

    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
    }

    //------------------------------------------------------------------------------
    public function fetchAll($filtro = '')
    {
        $this->table = 'wp_login_history';
        $select = new Select($this->table);
        $select->columns([
            '*'
        ]);
        if ($filtro != '') {
            $select->where($filtro);
        } else {
            $select->order("wp_login_history.id DESC");
        }
        //        echo $select->getSqlString();
        return $this->selectWith($select)->toArray();
    }



    public function getWploginDetalle($idDI = 0)
    {
        $select = new Select('wp_login_history');
        $select->columns(['*'])->where("wp_login_history.id = $idDI")->limit(1);
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
