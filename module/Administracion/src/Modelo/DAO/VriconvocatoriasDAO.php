<?php

namespace Administracion\Modelo\DAO;

use Laminas\Db\TableGateway\AbstractTableGateway;
use Laminas\Db\Adapter\Adapter;
use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\Insert;
use Laminas\Db\Sql\Update;
use Laminas\Db\Sql\Delete;
use Laminas\Db\Sql\Expression;
use Administracion\Modelo\Entidades\Vriconvocatorias;

class VriconvocatoriasDAO extends AbstractTableGateway
{
    protected $table = 'vri_convocatorias';

    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
    }

    public function fetchAll($filtro = '')
    {
        $select = new Select($this->table);
        $select->columns([
            '*',
            'proyectos_asociados' => new Expression(
                "(SELECT COUNT(*) FROM vri_proyectos_convocatorias pc WHERE pc.id_convocatoria = vri_convocatorias.id AND pc.estado = 'Activo')"
            ),
        ]);
        if ($filtro != '') {
            $select->where($filtro);
        } else {
            $select->order("vri_convocatorias.fecha DESC");
        }
        return $this->selectWith($select)->toArray();
    }

    public function getConvocatoriasActivas()
    {
        $select = new Select($this->table);
        $select->columns(['id', 'nombre', 'titulo', 'fecha'])
            ->where(['estado' => 'Activo'])
            ->order('fecha DESC');
        return $this->selectWith($select)->toArray();
    }

    public function getVriconvocatoriaDetalle($id = 0)
    {
        $select = new Select($this->table);
        $select->columns(['*'])->where("vri_convocatorias.id = " . (int)$id)->limit(1);
        $datos = $this->selectWith($select)->toArray();
        return count($datos) > 0 ? $datos[0] : null;
    }

    public function getVriconvocatoria($id = 0)
    {
        $row = $this->select(['id' => (int)$id])->current();
        return $row ? new Vriconvocatorias($row->getArrayCopy()) : null;
    }

    public function registrar(Vriconvocatorias $vriconvocatoriasOBJ = null)
    {
        try {
            $insert = new Insert($this->table);
            $datos = $vriconvocatoriasOBJ->getArrayCopy();
            unset($datos['id']);
            $insert->values($datos);
            $this->insertWith($insert);
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }

    public function editar(Vriconvocatorias $vriconvocatoriasOBJ = null)
    {
        try {
            $id = (int)$vriconvocatoriasOBJ->getId();
            $update = new Update($this->table);
            $datos = $vriconvocatoriasOBJ->getArrayCopy();
            $update->set($datos);
            $update->where("vri_convocatorias.id = $id");
            return $this->updateWith($update);
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }

    public function eliminar(Vriconvocatorias $vriconvocatoriasOBJ = null)
    {
        return $this->delete(['id' => (int)$vriconvocatoriasOBJ->getId()]);
    }

    public function cambiarEstado($id, $estado)
    {
        $update = new Update($this->table);
        $update->set([
            'estado' => $estado,
            'fechahoramod' => new Expression('NOW()'),
        ]);
        $update->where(['id' => (int)$id]);
        return $this->updateWith($update);
    }

    public function tieneProyectosAsociados($id)
    {
        $select = new Select('vri_proyectos_convocatorias');
        $select->columns(['total' => new Expression('COUNT(*)')])
            ->where(['id_convocatoria' => (int)$id, 'estado' => 'Activo']);
        $row = $this->selectWith($select)->toArray();
        return (int)($row[0]['total'] ?? 0);
    }
}
