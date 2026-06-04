<?php

namespace Usermanager\Modelo\DAO;

use Laminas\Db\TableGateway\AbstractTableGateway;
use Laminas\Db\Adapter\Adapter;
use Laminas\Db\Sql\Expression;
use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\Insert;
use Laminas\Db\Sql\Update;
use Laminas\Db\Sql\Delete;
use Usermanager\Modelo\Entidades\Rol;
use Usermanager\Modelo\Entidades\Recurso;

class RolDAO extends AbstractTableGateway
{

    protected $table = 'roles';

    //------------------------------------------------------------------------------

    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
    }

    //------------------------------------------------------------------------------
    public function fetchAll($filtro = '')
    {
        $this->table = 'roles';
        $select = new Select($this->table);
        $select->columns([
            'idRol',
            'rol',
            'detalle',
            'estado',
            'registradopor',
            'modificadopor',
            'fechahorareg',
            'fechahoramod',
        ]);
        if ($filtro != '') {
            $select->where($filtro);
        } else {
            $select->order("roles.idRol DESC");
        }
        //        echo $select->getSqlString();
        return $this->selectWith($select)->toArray();
    }
    public function getRecursos($filtro = '')
    {
        $this->table = 'recursos_rbac';
        $select = new Select($this->table);
        $select->columns([
            'idRecurso',
            'recurso',
            'metodo',
            'estado',
            'registradopor',
            'modificadopor',
            'fechahorareg',
            'fechahoramod',
        ]);
        if ($filtro != '') {
            $select->where($filtro);
        } else {
            $select->order("recursos_rbac.idRecurso DESC");
        }
        //        echo $select->getSqlString();
        return $this->selectWith($select)->toArray();
    }
    public function getRolDetalle($idRol = 0)
    {
        $select = new Select('roles');
        $select->columns(array(
            'idRol',
            'rol',
            'detalle',
            'estado',
            'registradopor',
            'modificadopor',
            'fechahorareg',
            'fechahoramod',
        ))->where("roles.idRol = $idRol")->limit(1);
        //        echo $select->getSqlString();
        $datos = $this->selectWith($select)->toArray();
        if (count($datos) > 0) {
            return $datos[0];
        } else {
            return null;
        }
    }
    public function getRecursoDetalle($idRecurso = 0)
    {
        $select = new Select('recursos_rbac');
        $select->columns(array(
            'idRecurso',
            'recurso',
            'metodo',
            'estado',
            'registradopor',
            'modificadopor',
            'fechahorareg',
            'fechahoramod',
        ))->where("recursos_rbac.idRecurso = $idRecurso")->limit(1);
        //        echo $select->getSqlString();
        $datos = $this->selectWith($select)->toArray();
        if (count($datos) > 0) {
            return $datos[0];
        } else {
            return null;
        }
    }
    //------------------------------------------------------------------------------
    public function getRol($idRol = 0)
    {
        return new Rol($this->select(array('idRol' => $idRol))->current()->getArrayCopy());
    }
    //------------------------------------------------------------------------------

    public function registrar(Rol $rolOBJ = null)
    {
        try {
            $this->table = 'roles';
            $insert = new Insert($this->table);
            $datos = $rolOBJ->getArrayCopy();
            unset($datos['idRol']);
            $insert->values($datos);
            $this->insertWith($insert);
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }
    public function registrar2(Recurso $recursoOBJ = null)
    {
        try {
            $this->table = 'recursos_rbac';
            $insert = new Insert($this->table);
            $datos = $recursoOBJ->getArrayCopy();
            unset($datos['idRecurso']);
            $insert->values($datos);
            $this->insertWith($insert);
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }
    //------------------------------------------------------------------------------

    public function editar(Rol $rolOBJ = null)
    {
        try {
            $this->table = 'roles';
            $idRol = (int) $rolOBJ->getIdRol();
            $update = new Update($this->table);
            $datos = $rolOBJ->getArrayCopy();
            $update->set($datos);
            $update->where("roles.idRol =  $idRol");
            //echo $update->getSqlString();
            return $this->updateWith($update);
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }
    public function editar2(Recurso $recursoOBJ = null)
    {
        try {
            $this->table = 'recursos_rbac';
            $idRecurso = (int) $recursoOBJ->getIdRecurso();
            $update = new Update($this->table);
            $datos = $recursoOBJ->getArrayCopy();
            $update->set($datos);
            $update->where("recursos_rbac.idRecurso =  $idRecurso");
            //echo $update->getSqlString();
            return $this->updateWith($update);
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }
    //------------------------------------------------------------------------------
    public function eliminar(Rol $rolOBJ = null)
    {
        try {
            $this->table = "roles";
            $update = new Update($this->table);
            $update->set([
                'estado' => 'Eliminado',
                'modificadopor' => $rolOBJ->getModificadopor(),
                'fechahoramod' => $rolOBJ->getFechahoramod(),
            ]);
            $update->where("roles.idRol = " . $rolOBJ->getIdRol());
            //echo $update->getSqlString();
            $this->updateWith($update);
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }

    //------------------------------------------------------------------------------

    public function getRolesRecursos($idRol = '')
    {
        $this->table = 'recursorbac_rol';
        $select = new Select($this->table);
        $select->columns([
            'idRol',
            'idRecurso',
        ])->join('recursos_rbac', 'recursos_rbac.idRecurso = recursorbac_rol.idRecurso', array(
            'recurso',
            'metodo',
        ))->where("recursorbac_rol.idRol = $idRol");
        //        echo $select->getSqlString();
        return $this->selectWith($select)->toArray();
    }

    public function getRecursosSelect($filtro = '')
    {
        $this->table = 'recursos_rbac';
        $select = new Select($this->table);
        $select->columns([
            'idRecurso',
            'recurso',
            'metodo',
            'estado',
            'registradopor',
            'modificadopor',
            'fechahorareg',
            'fechahoramod',
        ]);
        if ($filtro != '') {
            $select->where($filtro);
        } else {
            $select->order("recursos_rbac.idRecurso DESC");
        }
        //        echo $select->getSqlString();
        return $this->selectWith($select)->toArray();
    }
    public function insertarRecursoRol($idRol = 0, $check_lista = array())
    {
        try {
            $this->table = 'recursorbac_rol';
            $insert = new Insert($this->table);
            foreach ($check_lista as $cl) {
                $insert->values([
                    'idRol' => $idRol,
                    'idRecurso' => $cl,
                ]);
                $this->insertWith($insert);
            }
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }

    public function eliminarRecusoRol($idRol = 0, $idRecurso = 0)
    {
        try {
            $this->table = 'recursorbac_rol';
            $delete = new Delete($this->table);
            $delete->where("idRecurso = $idRecurso AND idRol = $idRol");
            //            echo $delete->getSqlString();
            return $this->deleteWith($delete);
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }
    //------------------------------------------------------------------------------
}
