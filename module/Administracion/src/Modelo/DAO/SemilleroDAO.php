<?php

namespace Administracion\Modelo\DAO;

use Laminas\Db\TableGateway\AbstractTableGateway;
use Laminas\Db\Adapter\Adapter;
use Laminas\Db\Sql\Expression;
use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\Insert;
use Laminas\Db\Sql\Update;
use Administracion\Modelo\Entidades\Semillero;

class SemilleroDAO extends AbstractTableGateway
{

    protected $table = 'semillero_investigacion';

    //------------------------------------------------------------------------------

    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
    }

    //------------------------------------------------------------------------------
    public function fetchAll($filtro = '')
    {
        $this->table = 'semillero_investigacion';
        $select = new Select($this->table);
        $select->columns([
            'idSI',
            'idFacultad',
            'idSemillero',
            'nombre',
            'mentor',
            'correo',
            'detalle',
            'enlaceGruplac',
            'enlaceSivri',
            'imagen',
            'estado',
            'registradopor',
            'modificadopor',
            'fechahorareg',
            'fechahoramod',
        ]);
        if ($filtro != '') {
            $select->where($filtro);
        } else {
            $select->order("semillero_investigacion.idSI DESC")->limit(1000);
        }
        //        echo $select->getSqlString();
        return $this->selectWith($select)->toArray();
    }
    public function getSemilleroDetalle($id = 0)
    {
        $select = new Select('semillero_investigacion');
        $select->columns(array(
            'idSI',
            'idFacultad',
            'idSemillero',
            'nombre',
            'mentor',
            'correo',
            'detalle',
            'enlaceGruplac',
            'enlaceSivri',
            'imagen',
            'estado',
            'registradopor',
            'modificadopor',
            'fechahorareg',
            'fechahoramod',
        ))->where("semillero_investigacion.idSI = $id")->limit(1);
        //        echo $select->getSqlString();
        $datos = $this->selectWith($select)->toArray();
        if (count($datos) > 0) {
            return $datos[0];
        } else {
            return null;
        }
    }
    public function getSemillero($id = 0)
    {
        return new Semillero($this->select(array('idSI' => $id))->current()->getArrayCopy());
    }
    //------------------------------------------------------------------------------

    public function registrar(Semillero $lumenOBJ = null)
    {
        try {
            $this->table = 'semillero_investigacion';
            $insert = new Insert($this->table);
            $datos = $lumenOBJ->getArrayCopy();
            unset($datos['idSI']);
            $insert->values($datos);
            $this->insertWith($insert);
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }
    public function editar(Semillero $lumenOBJ = null)
    {
        try {
            $this->table = 'semillero_investigacion';
            $id = (int) $lumenOBJ->getIdSI();
            $update = new Update($this->table);
            $datos = $lumenOBJ->getArrayCopy();
            $update->set($datos);
            $update->where("semillero_investigacion.idSI =  $id");
            //echo $update->getSqlString();
            return $this->updateWith($update);
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }
    public function eliminar(Semillero $lumenOBJ = null)
    {
        try {
            $this->table = "semillero_investigacion";
            $update = new Update($this->table);
            $update->set([
                'estado' => 'Eliminado',
                'modificadopor' => $lumenOBJ->getModificadopor(),
                'fechahoramod' => $lumenOBJ->getFechahoramod(),
            ]);
            $update->where("semillero_investigacion.idSI = " . $lumenOBJ->getIdSI());
            //echo $update->getSqlString();
            $this->updateWith($update);
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }
    public function activar(Semillero $archivoOBJ = null)
    {
        try {
            $this->table = "semillero_investigacion";
            $update = new Update($this->table);
            $update->set([
                'estado' => 'Activo',
                'modificadopor' => $archivoOBJ->getModificadopor(),
                'fechahoramod' => $archivoOBJ->getFechahoramod(),
            ]);
            $update->where("semillero_investigacion.idSI = " . $archivoOBJ->getIdSI());
            //echo $update->getSqlString();
            $this->updateWith($update);
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }

    //------------------------------------------------------------------------------
    public function getFacultades()
    {
        $this->table = 'facultades';
        $select = new Select($this->table);
        //        echo $select->getSqlString();
        return $this->selectWith($select)->toArray();
    }
    //------------------------------------------------------------------------------
    public function existeArchivo($archivo = '')
    {
        $this->table = 'semillero_investigacion';
        $rowset = $this->select(array('archivo' => $archivo));
        $row = $rowset->current();
        if (!$row) {
            return 0;
        }
        return 1;
    }
    //------------------------------------------------------------------------------
}
