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
use Administracion\Modelo\Entidades\Comarca;

class ComarcaDAO extends AbstractTableGateway
{

    protected $table = 'podcast_comarca';

    //------------------------------------------------------------------------------

    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
    }

    //------------------------------------------------------------------------------
    public function fetchAll($filtro = '')
    {
        $this->table = 'podcast_comarca';
        $select = new Select($this->table);
        $select->columns(['*']);
        if ($filtro != '') {
            $select->where($filtro);
        } else {
            $select->order("podcast_comarca.idPodcastComarca DESC")->limit(25);
        }
        //        echo $select->getSqlString();
        return $this->selectWith($select)->toArray();
    }
    public function getPodcastDetalle($idPodcastComarca = 0)
    {
        $select = new Select('podcast_comarca');
        $select->columns(['*'])->where("podcast_comarca.idPodcastComarca = $idPodcastComarca")->limit(1);
        //        echo $select->getSqlString();
        $datos = $this->selectWith($select)->toArray();
        if (count($datos) > 0) {
            return $datos[0];
        } else {
            return null;
        }
    }

    public function getPodcast($idPodcastComarca = 0)
    {
        return new Comarca($this->select(array('idPodcastComarca' => $idPodcastComarca))->current()->getArrayCopy());
    }
    //------------------------------------------------------------------------------
    public function registrar(Comarca $comarcaOBJ = null)
    {
        try {
            $this->table = 'podcast_comarca';
            $insert = new Insert($this->table);
            $datos = $comarcaOBJ->getArrayCopy();
            unset($datos['idPodcastComarca']);
            $insert->values($datos);
            $this->insertWith($insert);
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }
    public function editar(Comarca $comarcaOBJ = null)
    {
        try {
            $this->table = 'podcast_comarca';
            $idPodcastComarca = (int) $comarcaOBJ->getIdPodcastComarca();
            $update = new Update($this->table);
            $datos = $comarcaOBJ->getArrayCopy();
            $update->set($datos);
            $update->where("podcast_comarca.idPodcastComarca = $idPodcastComarca");
            $this->updateWith($update);
            return true;
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function eliminarActivar(Comarca $comarcaOBJ = null, $estado = '')
    {
        try {
            $this->table = "podcast_comarca";
            $update = new Update($this->table);
            $update->set([
                'estado' => $estado,
                'modificadopor' => $comarcaOBJ->getModificadopor(),
                'fechahoramod' => $comarcaOBJ->getFechahoramod(),
            ]);
            $update->where("podcast_comarca.idPodcastComarca = " . $comarcaOBJ->getIdPodcastComarca());
            //echo $update->getSqlString();
            $this->updateWith($update);
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }
    //------------------------------------------------------------------------------
}
