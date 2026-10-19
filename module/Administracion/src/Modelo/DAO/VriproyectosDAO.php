<?php

namespace Administracion\Modelo\DAO;

use Laminas\Db\TableGateway\AbstractTableGateway;
use Laminas\Db\Adapter\Adapter;
use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\Insert;
use Laminas\Db\Sql\Update;
use Laminas\Db\Sql\Delete;
use Laminas\Db\Sql\Expression;
use Administracion\Modelo\Entidades\Vriproyectos;

class VriproyectosDAO extends AbstractTableGateway
{
    protected $table = 'vri_proyectos';
    protected $tablaPuente = 'vri_proyectos_convocatorias';

    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
    }

    public function fetchAll($filtro = '')
    {
        $select = new Select($this->table);
        $select->columns([
            '*',
            'convocatorias_asociadas' => new Expression(
                "(SELECT GROUP_CONCAT(c.nombre SEPARATOR ' | ') 
                  FROM vri_proyectos_convocatorias pc 
                  INNER JOIN vri_convocatorias c ON c.id = pc.id_convocatoria 
                  WHERE pc.id_proyecto = vri_proyectos.id AND pc.estado = 'Activo')"
            ),
        ]);
        if ($filtro != '') {
            $select->where($filtro);
        }
        $select->order("vri_proyectos.id DESC");
        return $this->selectWith($select)->toArray();
    }

    public function getVriproyectoDetalle($id = 0)
    {
        $select = new Select($this->table);
        $select->columns(['*'])->where("vri_proyectos.id = " . (int)$id)->limit(1);
        $datos = $this->selectWith($select)->toArray();
        return count($datos) > 0 ? $datos[0] : null;
    }

    public function getVriproyecto($id = 0)
    {
        $row = $this->select(['id' => (int)$id])->current();
        return $row ? new Vriproyectos($row->getArrayCopy()) : null;
    }

    public function getConvocatoriasPorProyecto($idProyecto = 0)
    {
        $sql = new \Laminas\Db\Sql\Sql($this->adapter);
        $select = $sql->select($this->tablaPuente);
        $select->columns(['id_convocatoria'])
            ->where(['id_proyecto' => (int)$idProyecto, 'estado' => 'Activo']);

        $ids = [];
        foreach ($sql->prepareStatementForSqlObject($select)->execute() as $row) {
            $ids[] = (int)$row['id_convocatoria'];
        }
        return $ids;
    }

    public function getConvocatoriasAsociadasDetalle($idProyecto = 0)
    {
        $select = new Select($this->tablaPuente);
        $select->columns([])
            ->join(
                ['c' => 'vri_convocatorias'],
                'c.id = vri_proyectos_convocatorias.id_convocatoria',
                ['id', 'nombre', 'titulo', 'fecha']
            )
            ->where([
                'vri_proyectos_convocatorias.id_proyecto' => (int)$idProyecto,
                'vri_proyectos_convocatorias.estado' => 'Activo'
            ]);
        return $this->selectWith($select)->toArray();
    }

    public function registrar(Vriproyectos $vriproyectosOBJ = null)
    {
        try {
            $this->adapter->getDriver()->getConnection()->beginTransaction();

            $insert = new Insert($this->table);
            $datos = $vriproyectosOBJ->getArrayCopyPersistencia();
            unset($datos['id']);
            $insert->values($datos);
            $this->insertWith($insert);
            $idProyecto = (int)$this->getLastInsertValue();

            $this->sincronizarConvocatorias($idProyecto, $vriproyectosOBJ->getConvocatorias());

            $this->adapter->getDriver()->getConnection()->commit();
            return $idProyecto;
        } catch (\Exception $e) {
            $this->adapter->getDriver()->getConnection()->rollback();
            throw new \Exception($e);
        }
    }

    public function editar(Vriproyectos $vriproyectosOBJ = null)
    {
        try {
            $this->adapter->getDriver()->getConnection()->beginTransaction();

            $id = (int)$vriproyectosOBJ->getId();
            $update = new Update($this->table);
            $datos = $vriproyectosOBJ->getArrayCopyPersistencia();
            $update->set($datos);
            $update->where("vri_proyectos.id = $id");
            $this->updateWith($update);

            $this->sincronizarConvocatorias($id, $vriproyectosOBJ->getConvocatorias());

            $this->adapter->getDriver()->getConnection()->commit();
            return $id;
        } catch (\Exception $e) {
            $this->adapter->getDriver()->getConnection()->rollback();
            throw new \Exception($e);
        }
    }

    /**
     * Sincroniza la tabla puente:
     *  - Elimina físicamente las relaciones que ya no están seleccionadas
     *  - Inserta (o reactiva) las nuevas
     */
    public function sincronizarConvocatorias($idProyecto, array $idsConvocatorias, $usuario = 'admin.vri')
    {
        $idsConvocatorias = array_unique(array_map('intval', $idsConvocatorias));

        $sql = new \Laminas\Db\Sql\Sql($this->adapter);

        // ----------------------------------------------------------------
        // 1) Leer las convocatorias actuales del proyecto
        // ----------------------------------------------------------------
        $selectActual = $sql->select($this->tablaPuente);
        $selectActual->columns(['id_convocatoria'])
            ->where(['id_proyecto' => (int)$idProyecto, 'estado' => 'Activo']);
        $actuales = [];
        foreach ($sql->prepareStatementForSqlObject($selectActual)->execute() as $row) {
            $actuales[] = (int)$row['id_convocatoria'];
        }

        $aEliminar = array_diff($actuales, $idsConvocatorias);
        $aInsertar = array_diff($idsConvocatorias, $actuales);

        // ----------------------------------------------------------------
        // 2) Eliminar las que ya no están seleccionadas
        // ----------------------------------------------------------------
        if (!empty($aEliminar)) {
            $delete = $sql->delete($this->tablaPuente);
            $delete->where([
                'id_proyecto'     => (int)$idProyecto,
                'id_convocatoria' => array_values($aEliminar),
            ]);
            $sql->prepareStatementForSqlObject($delete)->execute();
        }

        // ----------------------------------------------------------------
        // 3) Insertar (o reactivar) las nuevas
        // ----------------------------------------------------------------
        foreach ($aInsertar as $idConvocatoria) {
            // ¿Existe pero estaba inactiva?
            $selectExist = $sql->select($this->tablaPuente);
            $selectExist->columns(['id'])
                ->where([
                    'id_proyecto'     => (int)$idProyecto,
                    'id_convocatoria' => (int)$idConvocatoria,
                ]);
            $existe = null;
            foreach ($sql->prepareStatementForSqlObject($selectExist)->execute() as $row) {
                $existe = $row;
                break;
            }

            if ($existe) {
                // Reactivar
                $update = $sql->update($this->tablaPuente);
                $update->set([
                    'estado'         => 'Activo',
                    'modificadopor'  => $usuario,
                    'fechahoramod'   => new \Laminas\Db\Sql\Expression('NOW()'),
                ]);
                $update->where(['id' => (int)$existe['id']]);
                $sql->prepareStatementForSqlObject($update)->execute();
            } else {
                // Insertar nuevo registro en la puente
                $insert = $sql->insert($this->tablaPuente);
                $insert->values([
                    'id_convocatoria'   => (int)$idConvocatoria,
                    'id_proyecto'       => (int)$idProyecto,
                    'fecha_vinculacion' => new \Laminas\Db\Sql\Expression('NOW()'),
                    'estado'            => 'Activo',
                    'registradopor'     => $usuario,
                    'modificadopor'     => $usuario,
                    'fechahorareg'      => new \Laminas\Db\Sql\Expression('NOW()'),
                    'fechahoramod'      => new \Laminas\Db\Sql\Expression('NOW()'),
                ]);
                $sql->prepareStatementForSqlObject($insert)->execute();
            }
        }
    }

    public function eliminar(Vriproyectos $vriproyectosOBJ = null)
    {
        try {
            $this->adapter->getDriver()->getConnection()->beginTransaction();
            $id = (int)$vriproyectosOBJ->getId();

            // 1) Borrar relaciones de la puente
            $deleteRel = new Delete($this->tablaPuente);
            $deleteRel->where(['id_proyecto' => $id]);
            $this->deleteWith($deleteRel);

            // 2) Borrar proyecto
            $this->delete(['id' => $id]);

            $this->adapter->getDriver()->getConnection()->commit();
            return true;
        } catch (\Exception $e) {
            $this->adapter->getDriver()->getConnection()->rollback();
            throw new \Exception($e);
        }
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
}
