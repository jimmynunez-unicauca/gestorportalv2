<?php

namespace Formularios\Modelo\DAO;

use Laminas\Db\TableGateway\AbstractTableGateway;
use Laminas\Db\Adapter\Adapter;
use Laminas\Db\Sql\Expression;
use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\Insert;
use Laminas\Db\Sql\Update;

class FchsDAO extends AbstractTableGateway
{

    protected $table = 'form_fchs';

    //------------------------------------------------------------------------------

    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
    }

    //------------------------------------------------------------------------------
    public function fetchAll($filtro = '')
    {
        $this->table = 'form_fchs';
        $select = new Select($this->table);
        $select->columns([
            'idForm_fchs',
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
            $select->order("form_fchs.idForm_fchs DESC");
        }
        //        echo $select->getSqlString();
        return $this->selectWith($select)->toArray();
    }
    public function getFormDetalle($idEvento = 0)
    {
        $select = new Select('form_fchs');
        $select->columns(array(
            'idForm_fchs',
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
        ))->where("form_fchs.idForm_fchs = $idEvento")->limit(1);
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
