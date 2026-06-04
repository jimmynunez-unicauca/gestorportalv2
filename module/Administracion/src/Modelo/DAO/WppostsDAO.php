<?php

namespace Administracion\Modelo\DAO;

use Laminas\Db\TableGateway\AbstractTableGateway;
use Laminas\Db\Adapter\Adapter;
use Laminas\Db\Sql\Expression;
use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\Insert;
use Laminas\Db\Sql\Update;
use Laminas\Db\Sql\Delete;

class WppostsDAO extends AbstractTableGateway
{

    protected $table = 'wp_posts';

    //------------------------------------------------------------------------------

    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
    }

    //------------------------------------------------------------------------------
    public function fetchAll($filtro = '')
    {
        $this->table = 'wp_posts';
        $select = new Select($this->table);
        $select->columns([
            'revision_id' => 'ID',
            'post_date',
            //'post_content',
            'post_title',
            'post_parent',
        ]);
        $select->join(
            'wp_users',
            'wp_posts.post_author = wp_users.ID',
            [
                'author_name' => 'display_name',
                'author_email' => 'user_email',
            ]
        );
        $select->where(['wp_posts.post_type' => 'revision']);
        // Aplicar filtro si existe
        if (!empty($filtro)) {
            $select->where($filtro);
        }
        $select->order("wp_posts.post_date DESC");
        /* echo $select->getSqlString(); */
        return $this->selectWith($select)->toArray();
    }


    public function getWppostsDetalle($id = 0)
    {
        // Crear la consulta
        $select = new Select('wp_posts');
        // Seleccionar columnas de wp_posts y wp_users
        $select->columns([
            'revision_id' => 'ID',
            'post_date',
            'post_content',
            'post_title',
            'post_parent',
        ]);
        // Realizar el JOIN con wp_users para obtener el nombre del autor
        $select->join(
            'wp_users',                      // Tabla con la que se hace JOIN
            'wp_posts.post_author = wp_users.ID', // Condición de unión
            [
                'author_name' => 'display_name',  // Nombre del autor
                'author_email' => 'user_email',  // Correo del autor (opcional)
            ]
        );
        // Filtro para buscar por el ID
        $select->where("wp_posts.ID = $id")->limit(1);
        // Ejecutar la consulta
        $datos = $this->selectWith($select)->toArray();
        // Retornar el primer resultado o null si no existe
        if (count($datos) > 0) {
            return $datos[0];
        } else {
            return null;
        }
    }
    //------------------------------------------------------------------------------
    public function getUsuarios()
    {
        $select = new Select('wp_users');
        $select->columns([
            'ID',
            'display_name'
        ]);
        $select->order('display_name ASC');

        return $this->selectWith($select)->toArray();
    }
    //------------------------------------------------------------------------------
}
