<?php

namespace Usermanager\Modelo\Entidades;

use DomainException;
use Laminas\Filter\StringTrim;
use Laminas\Filter\StripTags;
use Laminas\Filter\ToInt;
use Laminas\Filter\StringToUpper;
use Laminas\Filter\StringToLower;
use Laminas\InputFilter\InputFilter;
use Laminas\InputFilter\InputFilterAwareInterface;
use Laminas\InputFilter\InputFilterInterface;
use Laminas\Validator\StringLength;
use Laminas\Validator\EmailAddress;
use Laminas\Validator\Date;
use Laminas\Validator\Digits;
use Laminas\Validator\GreaterThan;
use Laminas\Validator\LessThan;

class Empleadocliente implements InputFilterAwareInterface
{

    private $idEmpleadoCliente;
    private $nombre;
    private $apellido;
    private $tipoIdentificacion;
    private $identificacion;
    private $fechaNacimiento;
    private $email;
    private $telefono;
    private $direccion;
    private $estado;
    private $genero;
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

        $inputFilter->add([
            'name' => 'nombre',
            'required' => true,
            'filters' => [
                ['name' => StripTags::class],
                ['name' => StringTrim::class],
                ['name' => StringToUpper::class],
            ],
            'validators' => [
                [
                    'name' => StringLength::class,
                    'options' => [
                        'encoding' => 'UTF-8',
                        'max' => 20,
                    ],
                ],
            ],
        ]);
        $inputFilter->add([
            'name' => 'apellido',
            'required' => true,
            'filters' => [
                ['name' => StripTags::class],
                ['name' => StringTrim::class],
                ['name' => StringToUpper::class],
            ],
            'validators' => [
                [
                    'name' => StringLength::class,
                    'options' => [
                        'encoding' => 'UTF-8',
                        'max' => 20,
                    ],
                ],
            ],
        ]);
        $inputFilter->add([
            'name' => 'identificacion',
            'required' => true,
            'filters' => [
                ['name' => ToInt::class],
            ],
            'validators' => [
                ['name' => Digits::class],
                [
                    'name' => GreaterThan::class,
                    'options' => [
                        'min' => 1,
                    ],
                ],
            ],
        ]);
        $inputFilter->add([
            'name' => 'genero',
            'required' => true,
            'filters' => [
                ['name' => StripTags::class],
                ['name' => StringTrim::class],
            ],
        ]);
        $inputFilter->add([
            'name' => 'direccion',
            'required' => true,
            'filters' => [
                ['name' => StripTags::class],
                ['name' => StringTrim::class],
                ['name' => StringToUpper::class],
            ],
            'validators' => [
                [
                    'name' => StringLength::class,
                    'options' => [
                        'encoding' => 'UTF-8',
                        'max' => 100,
                    ],
                ],
            ],
        ]);
        $inputFilter->add([
            'name' => 'telefono',
            'required' => true,
            'filters' => [
                ['name' => ToInt::class],
            ],
            'validators' => [
                ['name' => Digits::class],
                [
                    'name' => GreaterThan::class,
                    'options' => [
                        'min' => 0,
                    ],
                ],
            ],
        ]);
        $inputFilter->add([
            'name' => 'email',
            'required' => true,
            'filters' => [
                ['name' => StripTags::class],
                ['name' => StringTrim::class],
                ['name' => StringToLower::class],
            ],
            'validators' => [
                ['name' => EmailAddress::class],
                [
                    'name' => StringLength::class,
                    'options' => [
                        'encoding' => 'UTF-8',
                        'min' => 10,
                        'max' => 100,
                    ],
                ],
            ],
        ]);
        $inputFilter->add([
            'name' => 'fechaNacimiento',
            'required' => true,
            'filters' => [
                ['name' => StripTags::class],
                ['name' => StringTrim::class],
            ],
            'validators' => [
                [
                    'name' => Date::class,
                    'options' => [
                        'format' => 'Y-m-d'
                    ],
                ],
            ],
        ]);

        $this->inputFilter = $inputFilter;
        return $this->inputFilter;
    }

    //------------------------------------------------------------------------------



    /**
     * Get the value of idEmpleadoCliente
     */
    public function getIdEmpleadoCliente()
    {
        return $this->idEmpleadoCliente;
    }

    /**
     * Set the value of idEmpleadoCliente
     *
     * @return  self
     */
    public function setIdEmpleadoCliente($idEmpleadoCliente)
    {
        $this->idEmpleadoCliente = $idEmpleadoCliente;

        return $this;
    }

    /**
     * Get the value of nombre
     */
    public function getNombre()
    {
        return $this->nombre;
    }

    /**
     * Set the value of nombre
     *
     * @return  self
     */
    public function setNombre($nombre)
    {
        $this->nombre = $nombre;

        return $this;
    }

    /**
     * Get the value of apellido
     */
    public function getApellido()
    {
        return $this->apellido;
    }

    /**
     * Set the value of apellido
     *
     * @return  self
     */
    public function setApellido($apellido)
    {
        $this->apellido = $apellido;

        return $this;
    }

    /**
     * Get the value of tipoIdentificacion
     */
    public function getTipoIdentificacion()
    {
        return $this->tipoIdentificacion;
    }

    /**
     * Set the value of tipoIdentificacion
     *
     * @return  self
     */
    public function setTipoIdentificacion($tipoIdentificacion)
    {
        $this->tipoIdentificacion = $tipoIdentificacion;

        return $this;
    }

    /**
     * Get the value of identificacion
     */
    public function getIdentificacion()
    {
        return $this->identificacion;
    }

    /**
     * Set the value of identificacion
     *
     * @return  self
     */
    public function setIdentificacion($identificacion)
    {
        $this->identificacion = $identificacion;

        return $this;
    }

    /**
     * Get the value of fechaNacimiento
     */
    public function getFechaNacimiento()
    {
        return $this->fechaNacimiento;
    }

    /**
     * Set the value of fechaNacimiento
     *
     * @return  self
     */
    public function setFechaNacimiento($fechaNacimiento)
    {
        $this->fechaNacimiento = $fechaNacimiento;

        return $this;
    }

    /**
     * Get the value of email
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set the value of email
     *
     * @return  self
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get the value of telefono
     */
    public function getTelefono()
    {
        return $this->telefono;
    }

    /**
     * Set the value of telefono
     *
     * @return  self
     */
    public function setTelefono($telefono)
    {
        $this->telefono = $telefono;

        return $this;
    }

    /**
     * Get the value of direccion
     */
    public function getDireccion()
    {
        return $this->direccion;
    }

    /**
     * Set the value of direccion
     *
     * @return  self
     */
    public function setDireccion($direccion)
    {
        $this->direccion = $direccion;

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
     * Get the value of genero
     */
    public function getGenero()
    {
        return $this->genero;
    }

    /**
     * Set the value of genero
     *
     * @return  self
     */
    public function setGenero($genero)
    {
        $this->genero = $genero;

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
