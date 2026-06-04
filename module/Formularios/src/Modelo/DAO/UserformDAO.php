<?php

namespace Formularios\Modelo\DAO;

use Laminas\Db\TableGateway\AbstractTableGateway;
use Laminas\Db\Adapter\Adapter;
use Laminas\Db\Sql\Expression;
use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\Insert;
use Laminas\Db\Sql\Update;
use Laminas\Db\Sql\Delete;
use Formularios\Modelo\Entidades\User;
use Formularios\Modelo\Entidades\Dep;

class UserformDAO extends AbstractTableGateway
{

    protected $table = 'user_dependencia';

    //------------------------------------------------------------------------------

    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
    }

    //------------------------------------------------------------------------------
    public function userAll($filtro = '')
    {
        $this->table = 'form_user';
        $select = new Select($this->table);
        $select->columns(['*']);
        if ($filtro != '') {
            $select->where($filtro);
        } else {
            $select->order("form_user.idform_user DESC");
        }
        //        echo $select->getSqlString();
        return $this->selectWith($select)->toArray();
    }
    public function depenAll($filtro = '')
    {
        $this->table = 'form_dependencia';
        $select = new Select($this->table);
        $select->columns(['*']);
        if ($filtro != '') {
            $select->where($filtro);
        } else {
            $select->order("form_dependencia.idform_dependencia DESC");
        }
        //        echo $select->getSqlString();
        return $this->selectWith($select)->toArray();
    }
    public function getFormDetalle($id = 0)
    {
        $select = new Select('user_dependencia');
        $select->columns(['*'])->where("user_dependencia.idform_user = $id")->limit(1);
        //        echo $select->getSqlString();
        $datos = $this->selectWith($select)->toArray();
        if (count($datos) > 0) {
            return $datos[0];
        } else {
            return null;
        }
    }
    //------------------------------------------------------------------------------
    public function getUserForm($id = '')
    {
        $this->table = 'user_dependencia';
        $select = new Select($this->table);
        $select->columns(['*'])->join('form_user', 'form_user.idform_user = user_dependencia.idform_user', array(
            'nombre',
            'correo',
        ))->join('form_dependencia', 'form_dependencia.idform_dependencia = user_dependencia.idform_dependencia', array(
            'dependencia',
        ))->where("user_dependencia.idform_user = $id");
        //echo $select->getSqlString();
        return $this->selectWith($select)->toArray();
    }
    public function getDepSelect($filtro = "")
    {
        $this->table = 'form_dependencia';
        $select = new Select($this->table);
        $select->columns(['*'])->where($filtro);
        //echo $select->getSqlString();
        return $this->selectWith($select)->toArray();
    }
    public function insertarUserForm($idform_user = 0, $check_lista = array(), $registradopor = '')
    {
        try {
            $this->table = 'user_dependencia';
            $insert = new Insert($this->table);
            foreach ($check_lista as $cl) {
                $insert->values([
                    'idform_user' => $idform_user,
                    'idform_dependencia' => $cl,
                    'registradopor' => $registradopor,
                    'fechahorareg' => date('Y-m-d H:i:s'),
                ]);
                $this->insertWith($insert);
            }
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }
    public function eliminarUserForm($idDependencia = 0, $idUser = 0)
    {
        try {
            $this->table = 'user_dependencia';
            $delete = new Delete($this->table);
            $delete->where("idform_dependencia = $idDependencia AND idform_user = $idUser");
            //            echo $delete->getSqlString();
            return $this->deleteWith($delete);
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }
    //------------------------------------------------------------------------------
    public function userDepAll()
    {
        $this->table = 'user_dependencia';
        $select = new Select($this->table);
        $select->columns(['*'])->join('form_user', 'form_user.idform_user = user_dependencia.idform_user', [
            'nombre',
            'correo',
        ])->join('form_dependencia', 'form_dependencia.idform_dependencia = user_dependencia.idform_dependencia', [
            'dependencia',
        ]);
        //echo $select->getSqlString();
        return $this->selectWith($select)->toArray();
    }
    //------------------------------------------------------------------------------
    public function registrar(User $userOBJ = null)
    {
        try {
            $this->table = 'form_user';
            $insert = new Insert($this->table);
            $datos = $userOBJ->getArrayCopy();
            unset($datos['idform_user']);
            $insert->values($datos);
            $this->insertWith($insert);
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }
    public function registrarDep(Dep $depOBJ = null)
    {
        try {
            $this->table = 'form_dependencia';
            $insert = new Insert($this->table);
            $datos = $depOBJ->getArrayCopy();
            unset($datos['idform_dependencia']);
            $insert->values($datos);
            $this->insertWith($insert);
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }
    //------------------------------------------------------------------------------
    public function editar(User $userOBJ = null)
    {
        try {
            $this->table = 'form_user';
            $idUser = (int) $userOBJ->getIdform_user();
            $update = new Update($this->table);
            $datos = $userOBJ->getArrayCopy();
            $update->set($datos);
            $update->where("form_user.idform_user =  $idUser");
            //echo $update->getSqlString();
            return $this->updateWith($update);
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }
    public function editarDep(Dep $depOBJ = null)
    {
        try {
            $this->table = 'form_dependencia';
            $idDep = (int) $depOBJ->getIdform_dependencia();
            $update = new Update($this->table);
            $datos = $depOBJ->getArrayCopy();
            $update->set($datos);
            $update->where("form_dependencia.idform_dependencia =  $idDep");
            //echo $update->getSqlString();
            return $this->updateWith($update);
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }
    //------------------------------------------------------------------------------
    public function getUserDetalle($id)
    {
        $select = new Select('form_user');
        $select->columns(['*'])->where("form_user.idform_user = $id")->limit(1);
        //        echo $select->getSqlString();
        $datos = $this->selectWith($select)->toArray();
        if (count($datos) > 0) {
            return $datos[0];
        } else {
            return null;
        }
    }
    public function getDepDetalle($id)
    {
        $select = new Select('form_dependencia');
        $select->columns(['*'])->where("form_dependencia.idform_dependencia  = $id")->limit(1);
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
