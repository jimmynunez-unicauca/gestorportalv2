<?php

namespace Administracion\Modelo\DAO;

use Laminas\Db\TableGateway\AbstractTableGateway;
use Laminas\Db\Adapter\Adapter;
use Laminas\Db\Sql\Expression;
use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\Insert;
use Laminas\Db\Sql\Update;
use Laminas\Db\Sql\Delete;
use Administracion\Modelo\Entidades\Archivo;

class ArchivoDAO extends AbstractTableGateway
{

    protected $table = 'archivos';

    //------------------------------------------------------------------------------

    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
    }

    //------------------------------------------------------------------------------
    public function fetchAll($filtro = '')
    {
        $this->table = 'archivos';
        $select = new Select($this->table);
        $select->columns(['*',]);
        if ($filtro != '') {
            $select->where($filtro);
        } else {
            $select->order("archivos.idArchivo DESC");
        }
        //        echo $select->getSqlString();
        return $this->selectWith($select)->toArray();
    }
    public function fetchAllEn($filtro = '')
    {
        $this->table = 'archivos_en';
        $select = new Select($this->table);
        $select->columns(['*',]);
        if ($filtro != '') {
            $select->where($filtro);
        } else {
            $select->order("archivos_en.idArchivo DESC");
        }
        //        echo $select->getSqlString();
        return $this->selectWith($select)->toArray();
    }
    public function getArchivoDetalle($idArchivo = 0)
    {
        $select = new Select('archivos');
        $select->columns(['*'])->where("archivos.idArchivo = $idArchivo")->limit(1);
        //        echo $select->getSqlString();
        $datos = $this->selectWith($select)->toArray();
        if (count($datos) > 0) {
            return $datos[0];
        } else {
            return null;
        }
    }
    public function getArchivoDetalleEn($idArchivo = 0)
    {
        $select = new Select('archivos_en');
        $select->columns(['*'])->where("archivos_en.idArchivo = $idArchivo")->limit(1);
        //        echo $select->getSqlString();
        $datos = $this->selectWith($select)->toArray();
        if (count($datos) > 0) {
            return $datos[0];
        } else {
            return null;
        }
    }
    public function getArchivo($idArchivo = 0)
    {
        return new Archivo($this->select(array('idArchivo' => $idArchivo))->current()->getArrayCopy());
    }
    //------------------------------------------------------------------------------
    public function registrar(Archivo $archivoOBJ = null, $depen = array())
    {
        $connection = $this->getAdapter()->getDriver()->getConnection();
        $connection->beginTransaction();
        try {
            // 1. Insertar en 'archivos'
            $this->table = 'archivos';
            $insert = new Insert($this->table);
            $datos = $archivoOBJ->getArrayCopy();
            unset($datos['idArchivo']); // Si es autoincremental
            $insert->values($datos);
            $this->insertWith($insert);
            $idArchivo = $this->getLastInsertValue();

            // 2. Insertar en 'archivos_en' (sin idArchivo, ya que es autoincremental)
            $this->table = 'archivos_en';
            $insertEn = new Insert($this->table);
            $datosEn = $datos; // Copia los mismos datos
            unset($datosEn['idArchivo']); // Aseguramos que no se inserte manualmente
            $datosEn['estado'] = 'Eliminado'; // Forzar estado
            $insertEn->values($datosEn);
            $this->insertWith($insertEn);

            // 3. Insertar dependencias (archivo_dependencia)
            $this->table = 'archivo_dependencia';
            $insertDep = new Insert($this->table);
            foreach ($depen as $idDependencia) {
                $insertDep->values([
                    'idArchivo' => $idArchivo,
                    'idDependencia' => $idDependencia,
                ]);
                $this->insertWith($insertDep);
            }

            $connection->commit();
        } catch (\Exception $e) {
            $connection->rollback();
            throw new \Exception($e);
        }
    }
    public function editar(Archivo $archivoOBJ = null)
    {
        try {
            $this->table = 'archivos';
            $idArchivo = (int) $archivoOBJ->getIdArchivo();
            $update = new Update($this->table);
            $datos = $archivoOBJ->getArrayCopy();
            $update->set($datos);
            $update->where("archivos.idArchivo = $idArchivo");
            //echo $update->getSqlString();
            return $this->updateWith($update);
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }
    public function editarEn(Archivo $archivoOBJ = null)
    {
        try {
            $this->table = 'archivos_en';
            $idArchivo = (int) $archivoOBJ->getIdArchivo();
            $update = new Update($this->table);
            $datos = $archivoOBJ->getArrayCopy();
            $update->set($datos);
            $update->where("archivos_en.idArchivo = $idArchivo");
            //echo $update->getSqlString();
            return $this->updateWith($update);
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }
    public function eliminar(Archivo $archivoOBJ = null)
    {
        try {
            $this->table = "archivos";
            $update = new Update($this->table);
            $update->set([
                'estado' => 'Eliminado',
                'modificadopor' => $archivoOBJ->getModificadopor(),
                'fechahoramod' => $archivoOBJ->getFechahoramod(),
            ]);
            $update->where("archivos.idArchivo = " . $archivoOBJ->getIdArchivo());
            //echo $update->getSqlString();
            $this->updateWith($update);
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }
    public function eliminarEn(Archivo $archivoOBJ = null)
    {
        try {
            $this->table = "archivos_en";
            $update = new Update($this->table);
            $update->set([
                'estado' => 'Eliminado',
                'modificadopor' => $archivoOBJ->getModificadopor(),
                'fechahoramod' => $archivoOBJ->getFechahoramod(),
            ]);
            $update->where("archivos_en.idArchivo = " . $archivoOBJ->getIdArchivo());
            //echo $update->getSqlString();
            $this->updateWith($update);
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }
    public function activar(Archivo $archivoOBJ = null)
    {
        try {
            $this->table = "archivos";
            $update = new Update($this->table);
            $update->set([
                'estado' => 'Activo',
                'modificadopor' => $archivoOBJ->getModificadopor(),
                'fechahoramod' => $archivoOBJ->getFechahoramod(),
            ]);
            $update->where("archivos.idArchivo = " . $archivoOBJ->getIdArchivo());
            //echo $update->getSqlString();
            $this->updateWith($update);
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }
    public function activarEn(Archivo $archivoOBJ = null)
    {
        try {
            $this->table = "archivos_en";
            $update = new Update($this->table);
            $update->set([
                'estado' => 'Activo',
                'modificadopor' => $archivoOBJ->getModificadopor(),
                'fechahoramod' => $archivoOBJ->getFechahoramod(),
            ]);
            $update->where("archivos_en.idArchivo = " . $archivoOBJ->getIdArchivo());
            //echo $update->getSqlString();
            $this->updateWith($update);
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }

    //------------------------------------------------------------------------------
    public function getDependencias()
    {
        $this->table = 'dependencias';
        $select = new Select($this->table);
        //        echo $select->getSqlString();
        return $this->selectWith($select)->toArray();
    }
    public function getDependenciasArch($idArchivo = 0)
    {
        $dependenciasTable = 'dependencias';
        $archivoDependenciaTable = 'archivo_dependencia';
        $select = new Select($dependenciasTable);
        $select->columns([
            'idDependencia',
            'dependencia'
        ]);
        // Subconsulta para seleccionar las dependencias que están en archivo_dependencia con el idArchivo específico
        $subSelect = new Select($archivoDependenciaTable);
        $subSelect->columns(['idDependencia']);
        $subSelect->where(['idArchivo' => $idArchivo]);
        // WHERE clause to filter out dependencies that are in archivo_dependencia for the given idArchivo
        $select->where->notIn('idDependencia', $subSelect);
        return $this->selectWith($select)->toArray();
    }





    //------------------------------------------------------------------------------
    public function getArchivoDependencia($idArchivo = 0)
    {
        $select = new Select('archivo_dependencia');
        $select->columns(array(
            'idArchivo',
            'idDependencia',
            'fechahorareg',
        ))->join('dependencias', 'dependencias.idDependencia = archivo_dependencia.idDependencia', array(
            'dependencia'
        ))->where("archivo_dependencia.idArchivo = $idArchivo");
        //        echo $select->getSqlString();
        return $this->selectWith($select)->toArray();
    }
    //------------------------------------------------------------------------------
    public function eliminarDepe($idArchivo = 0, $idDependencia = 0)
    {
        try {
            $this->table = 'archivo_dependencia';
            $delete = new Delete($this->table);
            $delete->where("idArchivo = $idArchivo AND idDependencia = $idDependencia");
            //            echo $delete->getSqlString();
            return $this->deleteWith($delete);
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }
    public function agregarDepe($idArchivo = 0, $idDependencia = 0)
    {
        try {
            $this->table = 'archivo_dependencia';
            $insert = new Insert($this->table);
            $insert->values([
                'idArchivo' => $idArchivo,
                'idDependencia' => $idDependencia,
            ]);
            $this->insertWith($insert);
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }
    //------------------------------------------------------------------------------
}
