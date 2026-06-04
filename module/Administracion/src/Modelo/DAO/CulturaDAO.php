<?php

namespace Administracion\Modelo\DAO;

use Laminas\Db\TableGateway\AbstractTableGateway;
use Laminas\Db\Adapter\Adapter;
use Laminas\Db\Sql\Expression;
use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\Insert;
use Laminas\Db\Sql\Update;
use Administracion\Modelo\Entidades\Cultura;

class CulturaDAO extends AbstractTableGateway
{

    protected $table = 'form_cultura';

    //------------------------------------------------------------------------------

    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
    }

    //------------------------------------------------------------------------------
    public function fetchAll($filtro = '')
    {
        $this->table = 'form_cultura';
        $select = new Select($this->table);
        $select->columns([
            'idFormCultura',
            'nombre',
            'correo',
            'telefono',
            'asunto',
            'comentario',
            'estado',
            'registradopor',
            'modificadopor',
            'fechahorareg',
            'fechahoramod',
        ]);
        if ($filtro != '') {
            $select->where($filtro);
        } else {
            $select->order("form_cultura.idFormCultura DESC")->limit(25);
        }
        //        echo $select->getSqlString();
        return $this->selectWith($select)->toArray();
    }
    public function getFormDetalle($idFormCultura = 0)
    {
        $select = new Select('form_cultura');
        $select->columns(array(
            'idFormCultura',
            'nombre',
            'correo',
            'telefono',
            'asunto',
            'comentario',
            'estado',
            'registradopor',
            'modificadopor',
            'fechahorareg',
            'fechahoramod',
        ))->where("form_cultura.idFormCultura = $idFormCultura")->limit(1);
        //        echo $select->getSqlString();
        $datos = $this->selectWith($select)->toArray();
        if (count($datos) > 0) {
            return $datos[0];
        } else {
            return null;
        }
    }
    public function getForm($idFormCultura = 0)
    {
        return new Cultura($this->select(array('idFormCultura' => $idFormCultura))->current()->getArrayCopy());
    }

    //------------------------------------------------------------------------------
}
