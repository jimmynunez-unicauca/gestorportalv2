<?php

namespace Formularios\Modelo\DAO;

use Laminas\Db\TableGateway\AbstractTableGateway;
use Laminas\Db\Adapter\Adapter;
use Laminas\Db\Sql\Expression;
use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\Insert;
use Laminas\Db\Sql\Update;

class ArtesDAO extends AbstractTableGateway
{

    protected $table = 'form_artes';

    //------------------------------------------------------------------------------

    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
    }

    //------------------------------------------------------------------------------
    public function fetchAll($filtro = '')
    {
        $this->table = 'form_artes';
        $select = new Select($this->table);
        $select->columns([
            'idFormArtes',
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
            $select->order("form_artes.idFormArtes DESC");
        }
        //        echo $select->getSqlString();
        return $this->selectWith($select)->toArray();
    }
    public function getFormDetalle($idEvento = 0)
    {
        $select = new Select('form_artes');
        $select->columns(array(
            'idFormArtes',
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
        ))->where("form_artes.idFormArtes = $idEvento")->limit(1);
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
