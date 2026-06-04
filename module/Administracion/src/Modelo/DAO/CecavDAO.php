<?php

namespace Administracion\Modelo\DAO;

use Laminas\Db\TableGateway\AbstractTableGateway;
use Laminas\Db\Adapter\Adapter;
use Laminas\Db\Sql\Expression;
use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\Insert;
use Laminas\Db\Sql\Update;

class CecavDAO extends AbstractTableGateway
{

    protected $table = 'form_cecav';

    //------------------------------------------------------------------------------

    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
    }

    //------------------------------------------------------------------------------
    public function fetchAll($filtro = '')
    {
        $this->table = 'form_cecav';
        $select = new Select($this->table);
        $select->columns([
            'idFormCecav',
            'idPrograma',
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
        ])->join('programas', 'programas.idPrograma = form_cecav.idPrograma', [
            'programa',
        ])->join('facultades', 'facultades.idFacultad = programas.idFacultad', [
            'facultad',
        ]);
        if ($filtro != '') {
            $select->where($filtro);
        } else {
            $select->order("form_cecav.idFormCecav DESC")->limit(25);
        }
        //        echo $select->getSqlString();
        return $this->selectWith($select)->toArray();
    }
    //------------------------------------------------------------------------------
    public function getFormDetalle($idFormCecav = 0)
    {
        $select = new Select('form_cecav');
        $select->columns(array(
            'idFormCecav',
            'idPrograma',
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
        ))->join('programas', 'programas.idPrograma = form_cecav.idPrograma', [
            'programa',
        ])->join('facultades', 'facultades.idFacultad = programas.idFacultad', [
            'facultad',
        ])->where("form_cecav.idFormCecav = $idFormCecav")->limit(1);
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
