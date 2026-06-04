<?php

namespace Usermanager\Modelo\DAO;

use Laminas\Db\TableGateway\AbstractTableGateway;
use Laminas\Db\Adapter\Adapter;
use Laminas\Db\Sql\Expression;
use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\Insert;
use Laminas\Db\Sql\Update;
use Usermanager\Modelo\Entidades\Acceso;
use Usermanager\Modelo\Entidades\Empleadocliente;

class AccesoDAO extends AbstractTableGateway
{

    protected $table = 'usuario';

    //------------------------------------------------------------------------------

    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
    }

    //------------------------------------------------------------------------------
    public function fetchAll($filtro = '')
    {
        $this->table = 'usuario';
        $select = new Select($this->table);
        if ($filtro != '') {
            $select->where($filtro);
        } else {
            $select->order("usuario.idUsuario DESC")->limit(25);
        }
        //        echo $select->getSqlString();
        return $this->selectWith($select)->toArray();
    }
    public function getEmpleados($filtro = '')
    {
        $this->table = 'empleadocliente';
        $select = new Select($this->table);
        $select->where($filtro);
        //        echo $select->getSqlString();
        return $this->selectWith($select)->toArray();
    }
    public function getRoles()
    {
        $this->table = 'roles';
        $select = new Select($this->table);
        //        echo $select->getSqlString();
        return $this->selectWith($select)->toArray();
    }
    public function getEmpleadoCliente($idEmpleadoCliente = 0)
    {
        $select = new Select('empleadocliente');
        $select->columns(['*'])->where("empleadocliente.idEmpleadoCliente = $idEmpleadoCliente")->limit(1);
        //        echo $select->getSqlString();
        $datos = $this->selectWith($select)->toArray();
        if (count($datos) > 0) {
            return $datos[0];
        } else {
            return null;
        }
    }
    public function getUsuarioDetalle($idUsuario = 0)
    {
        $select = new Select('usuario');
        $select->columns(['*'])->join('usuario_rol', 'usuario_rol.idUsuario = usuario.idUsuario', array(
            'idRol',
        ))->join('roles', 'roles.idRol = usuario_rol.idRol', array(
            'rol',
        ))->where("usuario.idUsuario = $idUsuario")->limit(1);
        //        echo $select->getSqlString();
        $datos = $this->selectWith($select)->toArray();
        if (count($datos) > 0) {
            return $datos[0];
        } else {
            return null;
        }
    }
    public function existeLogin($login = '')
    {
        $this->table = 'usuario';
        $select = new Select($this->table);
        $select->columns(array('existe' => new Expression('COUNT(idUsuario)')))
            ->where(array('login' => $login));
        //        print $select->getSqlString();
        $datos = $this->selectWith($select)->toArray();
        if ($datos[0]['existe'] > 0) {
            return true;
        } else {
            return false;
        }
    }
    public function getCL($idUsuario = 0)
    {
        return new Acceso($this->select(array('idUsuario' => $idUsuario))->current()->getArrayCopy());
    }
    //------------------------------------------------------------------------------

    public function registrar(Acceso $tclOBJ = null, $idRol = 0)
    {
        $connection = $this->getAdapter()->getDriver()->getConnection();
        $connection->beginTransaction();
        try {
            $this->table = 'usuario';
            $insert = new Insert($this->table);
            $datos = $tclOBJ->getArrayCopy();
            unset($datos['idUsuario']);
            $insert->values($datos);
            //echo $insert->getSqlString();
            $this->insertWith($insert);
            $idUsuario = $this->getLastInsertValue();
            $this->table = 'usuario_rol';
            $insert = new Insert($this->table);
            $insert->values([
                'idUsuario' => $idUsuario,
                'idRol' => $idRol,
            ]);
            $this->insertWith($insert);
            $connection->commit();
        } catch (\Exception $e) {
            $connection->rollback();
            throw new \Exception($e);
        }
    }
    public function editar($idUsuario = 0, $idRol = 0)
    {
        try {
            $this->table = "usuario_rol";
            $update = new Update($this->table);
            $update->set([
                'idRol' => $idRol,
            ]);
            $update->where("usuario_rol.idUsuario = " . $idUsuario);
            //echo $update->getSqlString();
            $this->updateWith($update);
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }


    public function eliminar($idUser = 0, $estado = '')
    {
        try {
            $this->table = "usuario";
            $update = new Update($this->table);
            $update->set([
                'estado' => $estado,
            ]);
            $update->where("usuario.idUsuario = $idUser");
            //echo $update->getSqlString();
            $this->updateWith($update);
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }

    //------------------------------------------------------------------------------
    public function getTCLs($filtro = '')
    {
        $this->table = 'tipo_usuario';
        $select = new Select($this->table);
        if ($filtro != '') {
            $select->where($filtro);
        } else {
            $select->order("tipo_usuario.idUsuario DESC");
        }
        //        echo $select->getSqlString();
        return $this->selectWith($select)->toArray();
    }
    public function getTCLDetalle($idUsuario = 0)
    {
        $this->table = 'tipo_usuario';
        $select = new Select($this->table);
        $select->where("tipo_usuario.idUsuario = $idUsuario")->limit(1);
        //        echo $select->getSqlString();
        $datos = $this->selectWith($select)->toArray();
        if (count($datos) > 0) {
            return $datos[0];
        } else {
            return null;
        }
    }
    public function getTclOBJ($idUsuario = 0)
    {
        $this->table = 'tipo_usuario';
        $select = new Select($this->table);
        $select->where('tipo_usuario.idUsuario = ' . $idUsuario);
        //        print $select->getSqlString();
        $datos = $this->selectWith($select)->toArray();
        foreach ($datos as $dato) {
            return new TipoAcceso($dato);
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
