<?php

namespace Usermanager\Modelo\DAO;

use Laminas\Crypt\Password\Bcrypt;
use Laminas\Db\TableGateway\AbstractTableGateway;
use Laminas\Db\Adapter\Adapter;
use Laminas\Db\Sql\Expression;
use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\Insert;
use Laminas\Db\Sql\Update;
use Usermanager\Modelo\Entidades\Empleadocliente;

class PerfilDAO extends AbstractTableGateway
{

    protected $table = 'empleadocliente';

    //------------------------------------------------------------------------------

    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
    }

    //------------------------------------------------------------------------------
    public function usuariosAll($filtro = '')
    {
        $this->table = 'usuario';
        $select = new Select($this->table);
        $select->columns([
            'idUsuario',
            'idEmpleadoCliente',
            'usuario',
            'login',
            'password',
            'passwordseguro',
            'foto',
            'estado',
            'registradopor',
            'modificadopor',
            'fechahorareg',
            'fechahoramod',
        ])->join('empleadocliente', 'empleadocliente.idEmpleadoCliente = usuario.idEmpleadoCliente', array(
            'identificacion',
            'fechaNacimiento',
            'email',
            'telefono',
            'direccion',
            'genero',
        ));
        if ($filtro != '') {
            $select->where($filtro);
        } else {
            $select->order("usuario.idUsuario DESC");
        }
        //        echo $select->getSqlString();
        return $this->selectWith($select)->toArray();
    }
    public function getUsuarioDetalle($idUsuario = 0)
    {
        $select = new Select('usuario');
        $select->columns(array(
            'idUsuario',
            'idEmpleadoCliente',
            'usuario',
            'login',
            'password',
            'passwordseguro',
            'foto',
            'estado',
            'registradopor',
            'modificadopor',
            'fechahorareg',
            'fechahoramod',
        ))->join('empleadocliente', 'empleadocliente.idEmpleadoCliente = usuario.idEmpleadoCliente', array(
            'tipoIdentificacion',
            'identificacion',
            'fechaNacimiento',
            'email',
            'telefono',
            'direccion',
            'genero',
        ))->where("usuario.idUsuario = $idUsuario")->limit(1);
        //        echo $select->getSqlString();
        $datos = $this->selectWith($select)->toArray();
        if (count($datos) > 0) {
            return $datos[0];
        } else {
            return null;
        }
    }
    public function getEmpleado($idEmpleado = 0)
    {
        return new Empleadocliente($this->select(array('idEmpleadoCliente' => $idEmpleado))->current()->getArrayCopy());
    }
    //------------------------------------------------------------------------------
    public function updatePassword($password = '', $session = array())
    {
        try {
            $this->table = "usuario";
            $update = new Update($this->table);
            $update->set([
                'password' => (new Bcrypt())->create($password),
                'passwordseguro' => $password,
                'modificadopor' => $session['login'],
                'fechahoramod' => date('Y-m-d H:i:s'),
            ]);
            $update->where("usuario.idUsuario = " . $session['idUsuario']);
            //echo $update->getSqlString();
            $this->updateWith($update);
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }
    //------------------------------------------------------------------------------
    public function editar(Empleadocliente $empleadoOBJ = null)
    {
        try {
            $this->table = 'empleadocliente';
            $idEmpleado = (int) $empleadoOBJ->getIdEmpleadoCliente();
            $update = new Update($this->table);
            $datos = $empleadoOBJ->getArrayCopy();
            $update->set($datos);
            $update->where("empleadocliente.idEmpleadoCliente =  $idEmpleado");
            //echo $update->getSqlString();
            return $this->updateWith($update);
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }
    //------------------------------------------------------------------------------
    public function cambiarFoto($idUsuario = 0, $login = '', $avatar = '')
    {
        try {
            $this->table = "usuario";
            $update = new Update($this->table);
            $update->set([
                'foto' => $avatar,
                'modificadopor' => $login,
                'fechahoramod' => (date('Y-m-d H:i:s')),
            ]);
            $update->where("usuario.idUsuario = " . $idUsuario);
            //echo $update->getSqlString();
            $this->updateWith($update);
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }
    //------------------------------------------------------------------------------
}
