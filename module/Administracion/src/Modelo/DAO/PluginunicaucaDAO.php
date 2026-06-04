<?php

namespace Administracion\Modelo\DAO;

use Laminas\Db\TableGateway\AbstractTableGateway;
use Laminas\Db\Adapter\Adapter;
use Laminas\Db\Sql\Expression;
use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\Insert;
use Laminas\Db\Sql\Update;
use Administracion\Modelo\Entidades\Pluginunicauca;

class PluginunicaucaDAO extends AbstractTableGateway
{

    protected $table = 'modulos_plugin_unicauca';

    //------------------------------------------------------------------------------
    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
    }

    //------------------------------------------------------------------------------
    public function fetchAll($filtro = '')
    {
        $select = new Select($this->table);
        $select->columns(['*']);
        if ($filtro != '') {
            $select->where($filtro);
        }
        $select->order("id DESC");
        return $this->selectWith($select)->toArray();
    }

    //------------------------------------------------------------------------------
    public function registrar(Pluginunicauca $pluginunicaucaOBJ = null)
    {
        try {
            $insert = new Insert($this->table);
            $datos = $pluginunicaucaOBJ->getArrayCopy();
            unset($datos['id']);
            $insert->values($datos);
            $this->insertWith($insert);
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }
    //------------------------------------------------------------------------------
    public function editar(Pluginunicauca $pluginunicaucaOBJ = null)
    {
        try {
            $id = (int) $pluginunicaucaOBJ->getId();
            $update = new Update($this->table);
            $datos = $pluginunicaucaOBJ->getArrayCopy();
            $update->set($datos);
            $update->where("modulos_plugin_unicauca.id =  $id");
            //echo $update->getSqlString();
            return $this->updateWith($update);
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }
    //------------------------------------------------------------------------------
    public function getPluginunicauca($id)
    {
        $id = (int) $id;
        $select = new Select($this->table);
        $select->where(['id' => $id]);
        $rowset = $this->selectWith($select);
        return $rowset->current();
    }

    //------------------------------------------------------------------------------
    public function eliminar(Pluginunicauca $pluginunicaucaOBJ = null)
    {
        return $this->delete(['id' => (int) $pluginunicaucaOBJ->getId()]);
    }

    //------------------------------------------------------------------------------
    public function cambiarEstado($id, $estado)
    {
        $data = [
            'activo' => $estado,
            'fecha_actualizacion' => new Expression('NOW()'),
        ];

        $update = new Update($this->table);
        $update->set($data);
        $update->where(['id' => (int) $id]);
        return $this->updateWith($update);
    }

    //------------------------------------------------------------------------------
    public function validarNombreModulo($nombre, $id = 0)
    {
        $select = new Select($this->table);
        $select->where(['nombre_modulo' => $nombre]);
        if ($id > 0) {
            $select->where->notEqualTo('id', $id);
        }
        $rowset = $this->selectWith($select);
        return $rowset->count() > 0 ? false : true;
    }
    //------------------------------------------------------------------------------
}
