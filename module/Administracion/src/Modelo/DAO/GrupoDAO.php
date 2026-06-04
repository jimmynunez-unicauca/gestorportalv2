<?php

namespace Administracion\Modelo\DAO;

use Laminas\Db\TableGateway\AbstractTableGateway;
use Laminas\Db\Adapter\Adapter;
use Laminas\Db\Sql\Expression;
use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\Insert;
use Laminas\Db\Sql\Update;
use Administracion\Modelo\Entidades\Grupo;

class GrupoDAO extends AbstractTableGateway
{

    protected $table = 'grupo_investigacion';

    //------------------------------------------------------------------------------

    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
    }

    //------------------------------------------------------------------------------
    public function fetchAll($filtro = '')
    {
        $this->table = 'grupo_investigacion';
        $select = new Select($this->table);
        $select->columns([
            'idGI',
            'idFacultad',
            'idGVRI',
            'idGSIVRI',
            'codColombia',
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
            $select->order("grupo_investigacion.idGI DESC")->limit(1000);
        }
        //        echo $select->getSqlString();
        return $this->selectWith($select)->toArray();
    }
    public function getGrupoDetalle($id = 0)
    {
        $select = new Select('grupo_investigacion');
        $select->columns(array(
            'idGI',
            'idFacultad',
            'idGVRI',
            'idGSIVRI',
            'codColombia',
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
        ))->where("grupo_investigacion.idGI = $id")->limit(1);
        //        echo $select->getSqlString();
        $datos = $this->selectWith($select)->toArray();
        if (count($datos) > 0) {
            return $datos[0];
        } else {
            return null;
        }
    }
    public function getGrupo($id = 0)
    {
        return new Grupo($this->select(array('idGI' => $id))->current()->getArrayCopy());
    }
    //------------------------------------------------------------------------------

    public function registrar(Grupo $lumenOBJ = null)
    {
        try {
            $this->table = 'grupo_investigacion';
            $insert = new Insert($this->table);
            $datos = $lumenOBJ->getArrayCopy();
            unset($datos['idGI']);
            $insert->values($datos);
            $this->insertWith($insert);
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }
    public function editar(Grupo $lumenOBJ = null)
    {
        try {
            $this->table = 'grupo_investigacion';
            $id = (int) $lumenOBJ->getIdGI();
            $update = new Update($this->table);
            $datos = $lumenOBJ->getArrayCopy();
            $update->set($datos);
            $update->where("grupo_investigacion.idGI =  $id");
            //echo $update->getSqlString();
            return $this->updateWith($update);
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }
    public function eliminar(Grupo $lumenOBJ = null)
    {
        try {
            $this->table = "grupo_investigacion";
            $update = new Update($this->table);
            $update->set([
                'estado' => 'Eliminado',
                'modificadopor' => $lumenOBJ->getModificadopor(),
                'fechahoramod' => $lumenOBJ->getFechahoramod(),
            ]);
            $update->where("grupo_investigacion.idGI = " . $lumenOBJ->getIdGI());
            //echo $update->getSqlString();
            $this->updateWith($update);
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }
    public function activar(Grupo $archivoOBJ = null)
    {
        try {
            $this->table = "grupo_investigacion";
            $update = new Update($this->table);
            $update->set([
                'estado' => 'Activo',
                'modificadopor' => $archivoOBJ->getModificadopor(),
                'fechahoramod' => $archivoOBJ->getFechahoramod(),
            ]);
            $update->where("grupo_investigacion.idGI = " . $archivoOBJ->getIdGI());
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
        $this->table = 'grupo_investigacion';
        $rowset = $this->select(array('archivo' => $archivo));
        $row = $rowset->current();
        if (!$row) {
            return 0;
        }
        return 1;
    }
    //------------------------------------------------------------------------------
}
