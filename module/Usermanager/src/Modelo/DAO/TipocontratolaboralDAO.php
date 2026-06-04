<?php

namespace Talentohumano\Modelo\DAO;

use Laminas\Db\TableGateway\AbstractTableGateway;
use Laminas\Db\Adapter\Adapter;
use Laminas\Db\Sql\Expression;
use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\Insert;
use Laminas\Db\Sql\Update;
use Talentohumano\Modelo\Entidades\Tipocontratolaboral;

class TipocontratolaboralDAO extends AbstractTableGateway
{

    protected $table = 'tipo_contrato_laboral';

    //------------------------------------------------------------------------------

    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
    }

    //------------------------------------------------------------------------------
    public function getTCLs($filtro = '')
    {
        $this->table = 'tipo_contrato_laboral';
        $select = new Select($this->table);
        $select->columns([
            'idTipoContratoLaboral',
            'tipo',
            'plantilla',
            'estado',
            'registradopor',
            'modificadopor',
            'fechahorareg',
            'fechahoramod',
        ]);
        if ($filtro != '') {
            $select->where($filtro);
        } else {
            $select->order("tipo_contrato_laboral.idTipoContratoLaboral DESC")->limit(25);
        }
        //        echo $select->getSqlString();
        return $this->selectWith($select)->toArray();
    }
    public function getTCLDetalle($idTipoContratoLaboral = 0)
    {
        $select = new Select('tipo_contrato_laboral');
        $select->columns(array(
            'idTipoContratoLaboral',
            'tipo',
            'plantilla',
            'estado',
            'registradopor',
            'modificadopor',
            'fechahorareg',
            'fechahoramod',
        ))->where("tipo_contrato_laboral.idTipoContratoLaboral = $idTipoContratoLaboral")->limit(1);

        //        echo $select->getSqlString();
        $datos = $this->selectWith($select)->toArray();
        if (count($datos) > 0) {
            return $datos[0];
        } else {
            return null;
        }
    }
    public function getTCL($idTipoContratoLaboral = 0)
    {
        return new Tipocontratolaboral($this->select(array('idTipoContratoLaboral' => $idTipoContratoLaboral))->current()->getArrayCopy());
    }
    //------------------------------------------------------------------------------

    public function registrar(Tipocontratolaboral $tclOBJ = null)
    {
        try {
            $this->table = 'tipo_contrato_laboral';
            $insert = new Insert($this->table);
            $datos = $tclOBJ->getArrayCopy();
            unset($datos['idTipoContratoLaboral']);
            $insert->values($datos);
            //echo $insert->getSqlString();
            $this->insertWith($insert);
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }
    public function editar(Tipocontratolaboral $tclOBJ = null)
    {
        try {
            $this->table = 'tipo_contrato_laboral';
            $idTipoContratoLaboral = (int) $tclOBJ->getIdTipocontratolaboral();
            $update = new Update($this->table);
            $datos = $tclOBJ->getArrayCopy();
            $update->set($datos);
            $update->where("tipo_contrato_laboral.idTipoContratoLaboral =  $idTipoContratoLaboral");
            //echo $update->getSqlString();
            return $this->updateWith($update);
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }
    public function eliminar($idTipoContratoLaboral = 0, $registradopor = '')
    {
        try {
            $this->table = 'tipo_contrato_laboral';
            $update = new Update($this->table);
            $update->set([
                'estado' => 'Eliminado',
                'modificadopor' => $registradopor,
                'fechahoramod' => date('Y-m-d H:i:s'),
            ]);
            $update->where("tipo_contrato_laboral.idTipoContratoLaboral =  $idTipoContratoLaboral");
            //echo $update->getSqlString();
            return $this->updateWith($update);
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }

    //------------------------------------------------------------------------------
}
