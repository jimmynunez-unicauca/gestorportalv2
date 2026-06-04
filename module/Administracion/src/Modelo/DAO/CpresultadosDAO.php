<?php

namespace Administracion\Modelo\DAO;

use Laminas\Db\TableGateway\AbstractTableGateway;
use Laminas\Db\Adapter\Adapter;
use Laminas\Db\Sql\Expression;
use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\Insert;
use Laminas\Db\Sql\Update;
use Administracion\Modelo\Entidades\Cpresultados;

class CpresultadosDAO extends AbstractTableGateway
{

    protected $table = 'cp_resultados';

    //------------------------------------------------------------------------------

    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
    }

    //------------------------------------------------------------------------------
    public function fetchAll($filtro = '')
    {
        $this->table = 'cp_resultados';
        $select = new Select($this->table);
        $select->columns([
            'idCPResultados',
            'idEmitido',
            'nombre',
            'descripcion',
            'tipoDocumento',
            'archivo',
            'publicacion',
            'estado',
            'registradopor',
            'modificadopor',
            'fechahorareg',
            'fechahoramod',
        ]);
        if ($filtro != '') {
            $select->where($filtro);
        } else {
            $select->order("cp_resultados.idCPResultados DESC")->limit(1000);
        }
        //        echo $select->getSqlString();
        return $this->selectWith($select)->toArray();
    }
    public function getCpresultadosDetalle($idCPResultados = 0)
    {
        $select = new Select('cp_resultados');
        $select->columns(array(
            'idCPResultados',
            'idEmitido',
            'nombre',
            'descripcion',
            'tipoDocumento',
            'archivo',
            'publicacion',
            'estado',
            'registradopor',
            'modificadopor',
            'fechahorareg',
            'fechahoramod',
        ))->where("cp_resultados.idCPResultados = $idCPResultados")->limit(1);
        //        echo $select->getSqlString();
        $datos = $this->selectWith($select)->toArray();
        if (count($datos) > 0) {
            return $datos[0];
        } else {
            return null;
        }
    }
    public function getCpresultados($idCPResultados = 0)
    {
        return new Cpresultados($this->select(array('idCPResultados' => $idCPResultados))->current()->getArrayCopy());
    }
    //------------------------------------------------------------------------------

    public function registrar(Cpresultados $lumenOBJ = null)
    {
        try {
            $this->table = 'cp_resultados';
            $insert = new Insert($this->table);
            $datos = $lumenOBJ->getArrayCopy();
            unset($datos['idCPResultados']);
            $insert->values($datos);
            $this->insertWith($insert);
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }
    public function editar(Cpresultados $lumenOBJ = null)
    {
        try {
            $this->table = 'cp_resultados';
            $idCPResultados = (int) $lumenOBJ->getIdCpresultados();
            $update = new Update($this->table);
            $datos = $lumenOBJ->getArrayCopy();
            $update->set($datos);
            $update->where("cp_resultados.idCPResultados =  $idCPResultados");
            //echo $update->getSqlString();
            return $this->updateWith($update);
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }
    public function eliminar(Cpresultados $lumenOBJ = null)
    {
        try {
            $this->table = "cp_resultados";
            $update = new Update($this->table);
            $update->set([
                'estado' => 'Eliminado',
                'modificadopor' => $lumenOBJ->getModificadopor(),
                'fechahoramod' => $lumenOBJ->getFechahoramod(),
            ]);
            $update->where("cp_resultados.idCPResultados = " . $lumenOBJ->getIdCpresultados());
            //echo $update->getSqlString();
            $this->updateWith($update);
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }
    public function activar(Cpresultados $archivoOBJ = null)
    {
        try {
            $this->table = "cp_resultados";
            $update = new Update($this->table);
            $update->set([
                'estado' => 'Activo',
                'modificadopor' => $archivoOBJ->getModificadopor(),
                'fechahoramod' => $archivoOBJ->getFechahoramod(),
            ]);
            $update->where("cp_resultados.idCPResultados = " . $archivoOBJ->getIdCpresultados());
            //echo $update->getSqlString();
            $this->updateWith($update);
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }

    //------------------------------------------------------------------------------
    public function getDependencias()
    {
        $this->table = 'dependencias';
        $select = new Select($this->table);
        //        echo $select->getSqlString();
        return $this->selectWith($select)->toArray();
    }
    //------------------------------------------------------------------------------
    public function existeArchivo($archivo = '')
    {
        $this->table = 'cp_resultados';
        $rowset = $this->select(array('archivo' => $archivo));
        $row = $rowset->current();
        if (!$row) {
            return 0;
        }
        return 1;
    }
    //------------------------------------------------------------------------------
}
