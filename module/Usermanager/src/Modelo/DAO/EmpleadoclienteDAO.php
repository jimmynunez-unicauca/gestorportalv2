<?php

namespace Usermanager\Modelo\DAO;

use Laminas\Db\TableGateway\AbstractTableGateway;
use Laminas\Db\Adapter\Adapter;
use Laminas\Db\Sql\Expression;
use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\Insert;
use Laminas\Db\Sql\Update;
use Usermanager\Modelo\Entidades\Empleadocliente;

class EmpleadoclienteDAO extends AbstractTableGateway
{

    protected $table = 'empleadocliente';

    //------------------------------------------------------------------------------

    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
    }

    //------------------------------------------------------------------------------
    public function fetchAll($filtro = '')
    {
        $this->table = 'empleadocliente';
        $select = new Select($this->table);
        $select->columns(['*']);
        if ($filtro != '') {
            $select->where($filtro);
        } else {
            $select->order("empleadocliente.idEmpleadoCliente DESC");
        }
        //        echo $select->getSqlString();
        return $this->selectWith($select)->toArray();
    }
    public function getEmpleadoDetalle($idEmpleado = 0)
    {
        $select = new Select('empleadocliente');
        $select->columns(['*'])->where("empleadocliente.idEmpleadoCliente = $idEmpleado")->limit(1);
        //        echo $select->getSqlString();
        $datos = $this->selectWith($select)->toArray();
        if (count($datos) > 0) {
            return $datos[0];
        } else {
            return null;
        }
    }
    public function getEmpleado($idEmpleado = 0)
    {
        return new Empleadocliente($this->select(array('idEmpleadoCliente' => $idEmpleado))->current()->getArrayCopy());
    }
    //------------------------------------------------------------------------------

    public function existeIdentificacion($identificacion = '')
    {
        $this->table = 'empleadocliente';
        $rowset = $this->select(array('identificacion' => $identificacion));
        $row = $rowset->current();
        if (!$row) {
            return 0;
        }
        return 1;
    }

    //------------------------------------------------------------------------------

    public function registrar(Empleadocliente $empleadoOBJ = null)
    {
        try {
            $this->table = 'empleadocliente';
            $insert = new Insert($this->table);
            $datos = $empleadoOBJ->getArrayCopy();
            unset($datos['idEmpleadoCliente']);
            $insert->values($datos);
            $this->insertWith($insert);
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }
    public function editar(Empleadocliente $empleadoOBJ = null)
    {
        $connection = $this->getAdapter()->getDriver()->getConnection();
        $connection->beginTransaction();
        try {
            //------------------------------------------------------------------
            $this->table = 'empleadocliente';
            $idEmpleado = (int) $empleadoOBJ->getIdEmpleadoCliente();
            $update = new Update($this->table);
            $datos = $empleadoOBJ->getArrayCopy();
            $update->set($datos);
            $update->where("empleadocliente.idEmpleadoCliente =  $idEmpleado");
            //echo $update->getSqlString();
            $this->updateWith($update);
            //------------------------------------------------------------------            
            $this->table = 'usuario';
            $update = new Update($this->table);
            $update->set(['usuario' => strtoupper($empleadoOBJ->getNombre() . ' ' . $empleadoOBJ->getApellido())]);
            $update->where("usuario.idEmpleadoCliente =  $idEmpleado");
            //echo $update2->getSqlString();
            $this->updateWith($update);
            $connection->commit();
            //------------------------------------------------------------------
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }
    public function eliminar(Empleadocliente $empleadoOBJ = null)
    {
        try {
            $this->table = "empleadocliente";
            $update = new Update($this->table);
            $update->set([
                'estado' => 'Eliminado',
                'modificadopor' => $empleadoOBJ->getModificadopor(),
                'fechahoramod' => $empleadoOBJ->getFechahoramod(),
            ]);
            $update->where("empleadocliente.idEmpleadoCliente = " . $empleadoOBJ->getIdEmpleadoCliente());
            //echo $update->getSqlString();
            $this->updateWith($update);
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }

    //------------------------------------------------------------------------------
}
