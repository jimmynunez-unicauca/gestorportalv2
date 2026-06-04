<?php

namespace Usermanager\Modelo\Entidades;

use DomainException;
use Laminas\Filter\StringTrim;
use Laminas\Filter\StripTags;
use Laminas\Filter\ToInt;
use Laminas\Filter\StringToUpper;
use Laminas\InputFilter\InputFilter;
use Laminas\InputFilter\InputFilterAwareInterface;
use Laminas\InputFilter\InputFilterInterface;
use Laminas\Validator\StringLength;
use Laminas\Validator\Date;

class Acceso implements InputFilterAwareInterface
{

    private $idUsuario;
    private $idEmpleadoCliente;
   /*  private $idRol; */
    private $usuario;
    private $login;
    private $password;
    private $passwordseguro;
    private $foto;
    private $estado;
    private $registradopor;
    private $modificadopor;
    private $fechahorareg;
    private $fechahoramod;

    //------------------------------------------------------------------------------
    private $inputFilter;

    //------------------------------------------------------------------------------

    public function __construct(array $datos = null)
    {
        if (is_array($datos)) {
            $this->exchangeArray($datos);
        }
    }

    //------------------------------------------------------------------------------

    public function exchangeArray($data)
    {
        $metodos = get_class_methods($this);
        foreach ($data as $key => $value) {
            $metodo = 'set' . ucfirst($key);
            if (in_array($metodo, $metodos)) {
                $this->$metodo($value);
            }
        }
    }

    //------------------------------------------------------------------------------

    public function getArrayCopy()
    {
        $datos = get_object_vars($this);
        unset($datos['inputFilter']);
        return $datos;
    }

    //------------------------------------------------------------------------------

    public function setInputFilter(InputFilterInterface $inputFilter)
    {
        throw new DomainException(sprintf('%s does not allow injection of an alternate input filter', __CLASS__));
    }

    //------------------------------------------------------------------------------

    public function getInputFilter()
    {
        if ($this->inputFilter) {
            return $this->inputFilter;
        }

        $inputFilter = new InputFilter();
        //------------------------------- $inputFilter->add-------------------------
        $this->inputFilter = $inputFilter;
        return $this->inputFilter;
    }

    //------------------------------------------------------------------------------


    /**
     * Get the value of idUsuario
     */
    public function getIdUsuario()
    {
        return $this->idUsuario;
    }

    /**
     * Set the value of idUsuario
     *
     * @return  self
     */
    public function setIdUsuario($idUsuario)
    {
        $this->idUsuario = $idUsuario;

        return $this;
    }

    /**
     * Get the value of idEmpleadoCliente
     */
    public function getIdEmpleadoCliente()
    {
        return $this->idEmpleadoCliente;
    }

    /**
     * Set the value of idTipoContratoLaboral
     *
     * @return  self
     */
    public function setIdEmpleadoCliente($idEmpleadoCliente)
    {
        $this->idEmpleadoCliente = $idEmpleadoCliente;

        return $this;
    }

    /**
     * Get the value of idRol
     */
  /*   public function getIdRol()
    {
        return $this->idRol;
    } */

    /**
     * Set the value of idRol
     *
     * @return  self
     */
  /*   public function setIdRol($idRol)
    {
        $this->idRol = $idRol;

        return $this;
    } */

    /**
     * Get the value of usuario
     */
    public function getUsuario()
    {
        return $this->usuario;
    }

    /**
     * Set the value of usuario
     *
     * @return  self
     */
    public function setUsuario($usuario)
    {
        $this->usuario = $usuario;

        return $this;
    }

    /**
     * Get the value of login
     */
    public function getLogin()
    {
        return $this->login;
    }

    /**
     * Set the value of login
     *
     * @return  self
     */
    public function setLogin($login)
    {
        $this->login = $login;

        return $this;
    }

    /**
     * Get the value of password
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set the value of password
     *
     * @return  self
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Get the value of passwordseguro
     */
    public function getPasswordseguro()
    {
        return $this->passwordseguro;
    }

    /**
     * Set the value of passwordseguro
     *
     * @return  self
     */
    public function setPasswordseguro($passwordseguro)
    {
        $this->passwordseguro = $passwordseguro;

        return $this;
    }

    /**
     * Get the value of foto
     */
    public function getFoto()
    {
        return $this->foto;
    }

    /**
     * Set the value of foto
     *
     * @return  self
     */
    public function setFoto($foto)
    {
        $this->foto = $foto;

        return $this;
    }

    /**
     * Get the value of estado
     */
    public function getEstado()
    {
        return $this->estado;
    }

    /**
     * Set the value of estado
     *
     * @return  self
     */
    public function setEstado($estado)
    {
        $this->estado = $estado;

        return $this;
    }

    /**
     * Get the value of registradopor
     */
    public function getRegistradopor()
    {
        return $this->registradopor;
    }

    /**
     * Set the value of registradopor
     *
     * @return  self
     */
    public function setRegistradopor($registradopor)
    {
        $this->registradopor = $registradopor;

        return $this;
    }

    /**
     * Get the value of modificadopor
     */
    public function getModificadopor()
    {
        return $this->modificadopor;
    }

    /**
     * Set the value of modificadopor
     *
     * @return  self
     */
    public function setModificadopor($modificadopor)
    {
        $this->modificadopor = $modificadopor;

        return $this;
    }

    /**
     * Get the value of fechahorareg
     */
    public function getFechahorareg()
    {
        return $this->fechahorareg;
    }

    /**
     * Set the value of fechahorareg
     *
     * @return  self
     */
    public function setFechahorareg($fechahorareg)
    {
        $this->fechahorareg = $fechahorareg;

        return $this;
    }

    /**
     * Get the value of fechahoramod
     */
    public function getFechahoramod()
    {
        return $this->fechahoramod;
    }

    /**
     * Set the value of fechahoramod
     *
     * @return  self
     */
    public function setFechahoramod($fechahoramod)
    {
        $this->fechahoramod = $fechahoramod;

        return $this;
    }
}
