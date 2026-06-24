<?php

namespace Administracion\Modelo\DAO;

use Administracion\Modelo\Entidades\Podcast;
use Laminas\Db\TableGateway\AbstractTableGateway;
use Laminas\Db\Adapter\Adapter;
use Laminas\Db\Sql\Expression;
use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\Insert;
use Laminas\Db\Sql\Update;
use Laminas\Db\Sql\Delete;
use Administracion\Modelo\Entidades\Programa;

class EmisoraDAO extends AbstractTableGateway
{

    protected $table = 'emisora';

    //------------------------------------------------------------------------------

    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
    }

    //------------------------------------------------------------------------------
    public function fetchAll($filtro = '')
    {
        $this->table = 'emisora';
        $select = new Select($this->table);
        $select->columns(['*']);
        if ($filtro != '') {
            $select->where($filtro);
        } else {
            $select->order("emisora.idPrograma DESC");
        }
        //        echo $select->getSqlString();
        return $this->selectWith($select)->toArray();
    }
    public function podcastAll($filtro = '')
    {
        $this->table = 'podcast';
        $select = new Select($this->table);
        $select->columns(['*']);
        if ($filtro != '') {
            $select->where($filtro);
        } else {
            $select->order("podcast.idPodcast DESC");
        }
        //        echo $select->getSqlString();
        return $this->selectWith($select)->toArray();
    }
    public function getProgramaDetalle($idPrograma = 0)
    {
        $select = new Select('emisora');
        $select->columns(['*'])->where("emisora.idPrograma = $idPrograma")->limit(1);
        //        echo $select->getSqlString();
        $datos = $this->selectWith($select)->toArray();
        if (count($datos) > 0) {
            return $datos[0];
        } else {
            return null;
        }
    }
    public function getPodcastDetalle($idPodcast = 0)
    {
        $select = new Select('podcast');
        $select->where(['idPodcast' => $idPodcast])->limit(1);
        $result = $this->selectWith($select)->current();
        if ($result) {
            return new Podcast($result->getArrayCopy());
        }
        return null;
    }
    public function getDias($idPrograma = 0)
    {
        $select = new Select('emisora_dias');
        $select->columns(['dia_semana'])
            ->where(['idPrograma' => $idPrograma]);

        $dias = [];
        foreach ($this->selectWith($select) as $row) {
            $dias[] = $row['dia_semana'];
        }

        return $dias; // Devuelve un array con los días seleccionados
    }
    public function getPrograma($idPrograma = 0)
    {
        return new Programa($this->select(array('idPrograma' => $idPrograma))->current()->getArrayCopy());
    }
    //------------------------------------------------------------------------------

    public function registrar(Programa $programaOBJ = null, $dias = array())
    {
        $connection = $this->getAdapter()->getDriver()->getConnection();
        $connection->beginTransaction();
        try {
            $this->table = 'emisora';
            $insert = new Insert($this->table);
            $datos = $programaOBJ->getArrayCopy();
            unset($datos['idPrograma']);
            $insert->values($datos);
            $this->insertWith($insert);
            $idPrograma = $this->getLastInsertValue();
            $this->table = 'emisora_dias';
            $insert = new Insert($this->table);
            foreach ($dias as $dia) {
                $insert->values([
                    'idPrograma' => $idPrograma,
                    'dia_semana' => $dia,
                ]);
                $this->insertWith($insert);
            }
            $connection->commit();
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }
    public function registrarPodcast(Podcast $programaOBJ = null)
    {
        try {
            $this->table = 'podcast';
            $insert = new Insert($this->table);
            $datos = $programaOBJ->getArrayCopy();
            unset($datos['idPodcast']);
            $insert->values($datos);
            $this->insertWith($insert);
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }
    public function editar(Programa $programaOBJ = null, $dias = array())
    {
        $connection = $this->getAdapter()->getDriver()->getConnection();
        $connection->beginTransaction();
        try {
            // Actualiza la tabla emisora
            $this->table = 'emisora';
            $idPrograma = (int) $programaOBJ->getIdPrograma();
            $update = new Update($this->table);
            $datos = $programaOBJ->getArrayCopy();
            $update->set($datos);
            $update->where("emisora.idPrograma = $idPrograma");
            $this->updateWith($update);

            // Borra los días anteriores en emisora_dias
            $this->table = 'emisora_dias';
            $delete = new Delete($this->table);
            $delete->where(['idPrograma' => $idPrograma]);
            $this->deleteWith($delete);

            // Inserta los nuevos días en emisora_dias
            foreach ($dias as $dia) {
                $insert = new Insert($this->table);
                $insert->values([
                    'idPrograma' => $idPrograma,
                    'dia_semana' => $dia,
                ]);
                $this->insertWith($insert);
            }

            // Confirma la transacción después de todas las operaciones
            $connection->commit();

            return true; // Retorna un valor al final de la función
        } catch (\Exception $e) {
            $connection->rollback(); // Realiza el rollback en caso de error
            throw new \Exception($e->getMessage()); // Agrega el mensaje de error para más claridad
        }
    }
    public function editarPodcast(Podcast $programaOBJ = null)
    {
        try {
            $this->table = 'podcast';
            $idPodcast = (int) $programaOBJ->getIdPodcast();
            $update = new Update($this->table);
            $datos = $programaOBJ->getArrayCopy();
            $update->set($datos);
            $update->where("podcast.idPodcast = $idPodcast");
            $this->updateWith($update);
            return true;
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function eliminarActivar(Programa $programaOBJ = null, $estado = '')
    {
        try {
            $this->table = "emisora";
            $update = new Update($this->table);
            $update->set([
                'estado' => $estado,
                'modificadopor' => $programaOBJ->getModificadopor(),
                'fechahoramod' => $programaOBJ->getFechahoramod(),
            ]);
            $update->where("emisora.idPrograma = " . $programaOBJ->getIdPrograma());
            //echo $update->getSqlString();
            $this->updateWith($update);
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }
    public function eliminarActivarPodcast(Podcast $programaOBJ = null, $estado = '')
    {
        try {
            $this->table = "podcast";
            $update = new Update($this->table);
            $update->set([
                'estado' => $estado,
                'modificadopor' => $programaOBJ->getModificadopor(),
                'fechahoramod' => $programaOBJ->getFechahoramod(),
            ]);
            $update->where("podcast.idPodcast = " . $programaOBJ->getIdPodcast());
            //echo $update->getSqlString();
            $this->updateWith($update);
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }
    public function eliminarDias($idPrograma)
    {
        $this->table = 'emisora_dias';
        $delete = new Delete($this->table);
        $delete->where(['idPrograma' => $idPrograma]);
        return $this->deleteWith($delete);
    }
    //------------------------------------------------------------------------------
}
