<?php

namespace Administracion\Modelo\DAO;

use Laminas\Db\TableGateway\AbstractTableGateway;
use Laminas\Db\Adapter\Adapter;
use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\Insert;
use Laminas\Db\Sql\Update;
use Laminas\Db\Sql\Delete;
use Laminas\Db\Sql\Expression;
use Administracion\Modelo\Entidades\Podcast;

class PodcastDAO extends AbstractTableGateway
{
    protected $table = 'podcast';

    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
    }

    public function fetchAll($filtro = '')
    {
        $select = new Select($this->table);
        $select->columns(['*'])
            ->join(
                ['p' => 'emisora_programas'],
                'podcast.idPrograma = p.idPrograma',
                ['nombre_programa' => 'nombre_programa'],
                Select::JOIN_LEFT
            );
        if ($filtro != '') {
            $select->where($filtro);
        } else {
            $select->order('podcast.idPodcast DESC');
        }
        return $this->selectWith($select)->toArray();
    }

    public function getPodcast($idPodcast = 0)
    {
        $select = new Select($this->table);
        $select->where(['idPodcast' => (int) $idPodcast])->limit(1);
        $row = $this->selectWith($select)->current();
        return $row ? new Podcast($row->getArrayCopy()) : null;
    }

    public function getPodcastDetalle($idPodcast = 0)
    {
        $select = new Select($this->table);
        $select->columns(['*'])
            ->join(
                ['p' => 'emisora_programas'],
                'podcast.idPrograma = p.idPrograma',
                ['nombre_programa' => 'nombre_programa'],
                Select::JOIN_LEFT
            )
            ->where(['podcast.idPodcast' => (int) $idPodcast])
            ->limit(1);
        $datos = $this->selectWith($select)->toArray();
        return count($datos) > 0 ? $datos[0] : null;
    }

    public function getProgramasActivos()
    {
        $select = new Select('emisora_programas');
        $select->columns(['idPrograma', 'nombre_programa'])
            ->where(['estado' => 'Activo'])
            ->order('nombre_programa ASC');
        return $this->selectWith($select)->toArray();
    }

    public function registrar(Podcast $obj)
    {
        $insert = new Insert($this->table);
        $datos = $obj->getArrayCopy();
        unset($datos['idPodcast']);
        $insert->values($datos);
        $this->insertWith($insert);
        return (int) $this->getLastInsertValue();
    }

    public function editar(Podcast $obj)
    {
        $id = (int) $obj->getIdPodcast();
        $update = new Update($this->table);
        $datos = $obj->getArrayCopy();
        unset($datos['idPodcast']);
        $update->set($datos);
        $update->where(['idPodcast' => $id]);
        $this->updateWith($update);
    }

    public function eliminarActivar(Podcast $obj, $estado = '')
    {
        $update = new Update($this->table);
        $update->set([
            'estado'        => $estado,
            'modificadopor' => $obj->getModificadopor(),
            'fechahoramod'  => $obj->getFechahoramod(),
        ]);
        $update->where(['idPodcast' => $obj->getIdPodcast()]);
        $this->updateWith($update);
    }

    public function borrarPodcast($idPodcast)
    {
        $delete = new Delete($this->table);
        $delete->where(['idPodcast' => (int) $idPodcast]);
        $this->deleteWith($delete);
    }
}
