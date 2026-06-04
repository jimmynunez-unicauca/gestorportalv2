<?php

namespace Talentohumano\Modelo\DAO;

use Laminas\Db\TableGateway\AbstractTableGateway;
use Laminas\Db\Adapter\Adapter;
use Laminas\Db\Sql\Expression;
use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\Insert;
use Laminas\Db\Sql\Update;
use Talentohumano\Modelo\Entidades\Contratolaboral;
use Talentohumano\Modelo\Entidades\Tipocontratolaboral;

class ContratolaboralDAO extends AbstractTableGateway
{

    protected $table = 'contrato_laboral';

    //------------------------------------------------------------------------------

    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
    }

    //------------------------------------------------------------------------------
    public function getCLs($filtro = '')
    {
        $this->table = 'contrato_laboral';
        $select = new Select($this->table);
        $select->join('empleado', 'empleado.idEmpleado = contrato_laboral.idEmpleado', array(
            'identificacion',
            'nombre' => new \Laminas\Db\Sql\Expression("CONCAT(nombre1,' ',nombre2)"),
            'apellido' => new \Laminas\Db\Sql\Expression("CONCAT(apellido1,' ',apellido2)"),
        ))->join('tipo_contrato_laboral', 'tipo_contrato_laboral.idTipoContratoLaboral = contrato_laboral.idTipoContratoLaboral', array(
            'tipo',
        ));
        if ($filtro != '') {
            $select->where($filtro);
        } else {
            $select->order("contrato_laboral.idTipoContratoLaboral DESC")->limit(25);
        }
        //        echo $select->getSqlString();
        return $this->selectWith($select)->toArray();
    }
    public function getCLDetalle($idContratoLaboral = 0)
    {
        $this->table = 'contrato_laboral';
        $select = new Select($this->table);
        $select->join('empleado', 'empleado.idEmpleado = contrato_laboral.idEmpleado', array(
            'identificacion',
            'nombre' => new \Laminas\Db\Sql\Expression("CONCAT(nombre1,' ',nombre2)"),
            'apellido' => new \Laminas\Db\Sql\Expression("CONCAT(apellido1,' ',apellido2)"),
        ))->join('tipo_contrato_laboral', 'tipo_contrato_laboral.idTipoContratoLaboral = contrato_laboral.idTipoContratoLaboral', array(
            'tipo',
        ))->where("contrato_laboral.idContratoLaboral = $idContratoLaboral")->limit(1);

        //        echo $select->getSqlString();
        $datos = $this->selectWith($select)->toArray();
        if (count($datos) > 0) {
            return $datos[0];
        } else {
            return null;
        }
    }
    public function getCL($idContratoLaboral = 0)
    {
        return new Contratolaboral($this->select(array('idContratoLaboral' => $idContratoLaboral))->current()->getArrayCopy());
    }
    //------------------------------------------------------------------------------

    public function registrar(Contratolaboral $tclOBJ = null)
    {
        try {
            $this->table = 'contrato_laboral';
            $insert = new Insert($this->table);
            $datos = $tclOBJ->getArrayCopy();
            unset($datos['idContratoLaboral']);
            $insert->values($datos);
            //echo $insert->getSqlString();
            $this->insertWith($insert);
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }
    public function editar(Contratolaboral $tclOBJ = null)
    {
        try {
            $this->table = 'contrato_laboral';
            $idContratoLaboral = (int) $tclOBJ->getIdContratoLaboral();
            $update = new Update($this->table);
            $datos = $tclOBJ->getArrayCopy();
            $update->set($datos);
            $update->where("contrato_laboral.idContratoLaboral =  $idContratoLaboral");
            //echo $update->getSqlString();
            return $this->updateWith($update);
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }


    public function eliminar(Contratolaboral $tclOBJ = null)
    {
        try {
            $this->table = "contrato_laboral";
            $update = new Update($this->table);
            $update->set([
                'estado' => 'Anulado',
                'modificadopor' => $tclOBJ->getModificadopor(),
                'fechahoramod' => $tclOBJ->getFechahoramod(),
            ]);
            $update->where("contrato_laboral.idContratoLaboral = " . $tclOBJ->getIdContratoLaboral());
            //echo $update->getSqlString();
            $this->updateWith($update);
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }

    //------------------------------------------------------------------------------
    public function getTCLs($filtro = '')
    {
        $this->table = 'tipo_contrato_laboral';
        $select = new Select($this->table);
        if ($filtro != '') {
            $select->where($filtro);
        } else {
            $select->order("tipo_contrato_laboral.idTipoContratoLaboral DESC");
        }
        //        echo $select->getSqlString();
        return $this->selectWith($select)->toArray();
    }
    public function getTCLDetalle($idTipoContratoLaboral = 0)
    {
        $this->table = 'tipo_contrato_laboral';
        $select = new Select($this->table);
        $select->where("tipo_contrato_laboral.idTipoContratoLaboral = $idTipoContratoLaboral")->limit(1);
        //        echo $select->getSqlString();
        $datos = $this->selectWith($select)->toArray();
        if (count($datos) > 0) {
            return $datos[0];
        } else {
            return null;
        }
    }
    public function getTclOBJ($idTipoContratoLaboral = 0)
    {
        $this->table = 'tipo_contrato_laboral';
        $select = new Select($this->table);
        $select->where('tipo_contrato_laboral.idTipoContratoLaboral = ' . $idTipoContratoLaboral);
        //        print $select->getSqlString();
        $datos = $this->selectWith($select)->toArray();
        foreach ($datos as $dato) {
            return new Tipocontratolaboral($dato);
        }
        return null;
    }
    //------------------------------------------------------------------------------
    public function getInfoEmpleado($autocompletar = '')
    {
        $select = new Select('empleado');
        $select->columns(array(
            'idEmpleado',
            'nombre' => new \Laminas\Db\Sql\Expression("CONCAT(empleado.nombre1, ' ', empleado.nombre2, ' ', empleado.apellido1, ' ', empleado.apellido2)")
        ));
        $select->where("empleado.nombre1 like '%$autocompletar%' OR empleado.nombre2 like '%$autocompletar%' OR empleado.apellido1 like '%$autocompletar%' OR empleado.apellido2 like '%$autocompletar%'");
        //        echo $select->getSqlString();
        return $this->selectWith($select)->toArray();
    }
    //------------------------------------------------------------------------------
    public function getEmpleado($idEmpleado = 0)
    {
        $this->table = 'empleado';
        $select = new Select($this->table);
        $select->where("empleado.idEmpleado = $idEmpleado")->limit(1);
        //        echo $select->getSqlString();
        $datos = $this->selectWith($select)->toArray();
        if (count($datos) > 0) {
            return $datos[0];
        } else {
            return null;
        }
    }
    //------------------------------------------------------------------------------
    public function getEmpleadoByIdentificacion($identificacion = 0)
    {
        $this->table = 'empleado';
        $select = new Select($this->table);
        $select->where("empleado.identificacion = $identificacion")->limit(1);
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
