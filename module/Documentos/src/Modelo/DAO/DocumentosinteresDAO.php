<?php

namespace Documentos\Modelo\DAO;

use Laminas\Db\TableGateway\AbstractTableGateway;
use Laminas\Db\Adapter\Adapter;
use Laminas\Db\Sql\Expression;
use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\Insert;
use Laminas\Db\Sql\Update;
use Laminas\Db\Sql\Delete;
use Documentos\Modelo\Entidades\Documentosinteres;

class DocumentosinteresDAO extends AbstractTableGateway
{

    protected $table = 'documentos_interes';

    //------------------------------------------------------------------------------

    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
    }

    //------------------------------------------------------------------------------
    public function fetchAll($filtro = '')
    {
        $this->table = 'documentos_interes';
        $select = new Select($this->table);
        $select->columns(['*'])->join(
            'lvmen',
            'lvmen.idLvmen = documentos_interes.idLvmen',
            ['*']
        );
        if ($filtro != '') {
            $select->where($filtro);
        } else {
            $select->order("documentos_interes.idDocumentosInteres DESC");
        }
        //        echo $select->getSqlString();
        return $this->selectWith($select)->toArray();
    }
    public function getDocDetalle($id = 0)
    {
        $select = new Select('documentos_interes');
        $select->columns([
            '*',
            'emitido' => new Expression("(SELECT dependencia FROM dependencias WHERE dependencias.idDependencia = lvmen.idEmitido)"),
            'subproceso' => new Expression("(SELECT subproceso FROM subproceso WHERE subproceso.idSubproceso = lvmen.idSubproceso)"),
            'idTP' => new Expression("(SELECT idTipoProceso FROM subproceso WHERE subproceso.idSubproceso = lvmen.idSubproceso)"),
            'tipoProceso' => new Expression("(SELECT tipoProceso FROM tipo_proceso WHERE tipo_proceso.idTipoProceso = idTP)"),
            'idP' => new Expression("(SELECT idProceso FROM tipo_proceso WHERE tipo_proceso.idTipoProceso = idTP)"),
            'proceso' => new Expression("(SELECT proceso FROM proceso WHERE proceso.idProceso = idP)"),
        ])->join(
            'lvmen',
            'lvmen.idLvmen = documentos_interes.idLvmen',
            ['*']
        )->where("documentos_interes.idDocumentosInteres = $id")->limit(1);
        //        echo $select->getSqlString();
        $datos = $this->selectWith($select)->toArray();
        if (count($datos) > 0) {
            return $datos[0];
        } else {
            return null;
        }
    }
    //------------------------------------------------------------------------------

    public function registrar(Documentosinteres $docOBJ = null)
    {
        try {
            $this->table = 'documentos_interes';
            $insert = new Insert($this->table);
            $datos = $docOBJ->getArrayCopy();
            unset($datos['idDocumentosInteres']);
            $insert->values($datos);
            $this->insertWith($insert);
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }

    public function eliminar(Documentosinteres $docOBJ = null)
    {
        try {
            $this->table = 'documentos_interes';
            $delete = new Delete($this->table);
            $delete->where("documentos_interes.idDocumentosInteres = " . $docOBJ->getIdDocumentosInteres());
            //            echo $delete->getSqlString();
            return $this->deleteWith($delete);
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }
    //------------------------------------------------------------------------------
    public function getLvmen()
    {
        $this->table = 'lvmen';
        $select = new Select($this->table);
        $select->columns(['*']);
        $select->order("lvmen.idLvmen DESC");
        //        echo $select->getSqlString();
        return $this->selectWith($select)->toArray();
    }
    //------------------------------------------------------------------------------
}
