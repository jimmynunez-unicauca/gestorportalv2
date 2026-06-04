<?php

namespace Formularios\Modelo\DAO;

use Laminas\Db\TableGateway\AbstractTableGateway;
use Laminas\Db\Adapter\Adapter;
use Laminas\Db\Sql\Expression;
use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\Insert;
use Laminas\Db\Sql\Update;
use Formularios\Modelo\Entidades\Convocatoria;

class ConvocatoriaDAO extends AbstractTableGateway
{
    protected $table = 'form_psi_convocatorias';

    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
    }

    public function fetchAll($filtro = '')
    {
        $select = new Select($this->table);
        $select->columns(['*']);
        $select->join('form_psi_config', 'form_psi_config.id_config = form_psi_convocatorias.id_config', ['nombre_formulario']);

        if ($filtro != '') {
            $select->where($filtro);
        } else {
            $select->order("form_psi_convocatorias.id_convocatoria DESC");
        }

        return $this->selectWith($select)->toArray();
    }

    public function getConvocatoria($idConvocatoria = 0)
    {
        $select = new Select($this->table);
        $select->columns(['*']);
        $select->join('form_psi_config', 'form_psi_config.id_config = form_psi_convocatorias.id_config', ['nombre_formulario']);
        $select->where(["form_psi_convocatorias.id_convocatoria = $idConvocatoria"]);
        $select->limit(1);

        $datos = $this->selectWith($select)->toArray();
        if (count($datos) > 0) {
            return new Convocatoria($datos[0]);
        }
        return null;
    }

    public function getFormConvocatoria($idConvocatoria = 0)
    {
        return new Convocatoria($this->select(['id_convocatoria' => $idConvocatoria])->current()->getArrayCopy());
    }

    public function getConvocatoriasPorConfig($idConfig)
    {
        $select = new Select($this->table);
        $select->columns(['*']);
        $select->where(['id_config' => $idConfig]);
        $select->order('fecha_inicio DESC');

        return $this->selectWith($select)->toArray();
    }

    public function registrar(Convocatoria $convocatoriaOBJ = null)
    {
        try {
            $insert = new Insert($this->table);
            $datos = $convocatoriaOBJ->getArrayCopy();
            unset($datos['id_convocatoria']);
            unset($datos['nombre_formulario']);

            // Limpiar datos vacíos
            $datos = array_filter($datos, function ($value) {
                return $value !== null && $value !== '';
            });

            $insert->values($datos);
            return $this->insertWith($insert);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function editar(Convocatoria $convocatoriaOBJ = null)
    {
        try {
            $idConvocatoria = (int) $convocatoriaOBJ->getIdConvocatoria();
            $update = new Update($this->table);
            $datos = $convocatoriaOBJ->getArrayCopy();
            unset($datos['id_convocatoria']);
            unset($datos['nombre_formulario']);

            // Limpiar datos vacíos
            $datos = array_filter($datos, function ($value) {
                return $value !== null && $value !== '';
            });

            $update->set($datos);
            $update->where(['id_convocatoria' => $idConvocatoria]);

            return $this->updateWith($update);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function eliminar(Convocatoria $convocatoriaOBJ = null)
    {
        return $this->delete(['id_convocatoria' => (int) $convocatoriaOBJ->getIdConvocatoria()]);
    }

    public function cambiarEstado($idConvocatoria, $estado)
    {
        $data = [
            'activo' => $estado,
            'updated_at' => new Expression('NOW()'),
        ];

        $update = new Update($this->table);
        $update->set($data);
        $update->where(['id_convocatoria' => (int) $idConvocatoria]);
        return $this->updateWith($update);
    }

    public function actualizarInscritos($idConvocatoria)
    {
        $data = [
            'inscritos_actuales' => new Expression('inscritos_actuales + 1'),
        ];

        $update = new Update($this->table);
        $update->set($data);
        $update->where(['id_convocatoria' => (int) $idConvocatoria]);
        return $this->updateWith($update);
    }
}
