<?php

namespace Administracion\Modelo\Entidades;

use DomainException;
use Laminas\Filter\StringTrim;
use Laminas\Filter\StripTags;
use Laminas\Filter\ToInt;
use Laminas\Filter\StringToUpper;
use Laminas\Filter\StringToLower;
use Laminas\Form\Element\DateTimeLocal;
use Laminas\InputFilter\InputFilter;
use Laminas\InputFilter\InputFilterAwareInterface;
use Laminas\InputFilter\InputFilterInterface;
use Laminas\Validator\StringLength;
use Laminas\Validator\EmailAddress;
use Laminas\Validator\Date;
use Laminas\Validator\Digits;
use Laminas\Validator\GreaterThan;
use Laminas\Validator\LessThan;

class Directorio implements InputFilterAwareInterface
{

    private $idDI;
    private $idDependencia;
    private $nombre;
    private $cargo;
    private $telefono;
    private $correo;
    private $direccion;
    private $asistente;
    private $cargoAsistente;
    private $unidad;
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

        $inputFilter->add([
            'name' => 'nombre',
            'required' => true,
            'filters' => [
                ['name' => StripTags::class],
                ['name' => StringTrim::class],
                /*   ['name' => StringToUpper::class], */
            ],
            'validators' => [
                [
                    'name' => StringLength::class,
                    'options' => [
                        'encoding' => 'UTF-8',
                        /*  'max' => 100, */
                    ],
                ],
            ],
        ]);

        $this->inputFilter = $inputFilter;
        return $this->inputFilter;
    }

    //------------------------------------------------------------------------------

    public function getIdDI()
    {
        return $this->idDI;
    }

    public function setIdDI($value)
    {
        $this->idDI = $value;
    }

    public function getIdDependencia()
    {
        return $this->idDependencia;
    }

    public function setIdDependencia($value)
    {
        $this->idDependencia = $value;
    }

    public function getNombre()
    {
        return $this->nombre;
    }

    public function setNombre($value)
    {
        $this->nombre = $value;
    }

    public function getCargo()
    {
        return $this->cargo;
    }

    public function setCargo($value)
    {
        $this->cargo = $value;
    }

    public function getTelefono()
    {
        return $this->telefono;
    }

    public function setTelefono($value)
    {
        $this->telefono = $value;
    }

    public function getCorreo()
    {
        return $this->correo;
    }

    public function setCorreo($value)
    {
        $this->correo = $value;
    }

    public function getDireccion()
    {
        return $this->direccion;
    }

    public function setDireccion($value)
    {
        $this->direccion = $value;
    }

    public function getAsistente()
    {
        return $this->asistente;
    }

    public function setAsistente($value)
    {
        $this->asistente = $value;
    }

    public function getCargoAsistente()
    {
        return $this->cargoAsistente;
    }

    public function setCargoAsistente($value)
    {
        $this->cargoAsistente = $value;
    }

    public function getUnidad()
    {
        return $this->unidad;
    }

    public function setUnidad($value)
    {
        $this->unidad = $value;
    }

    public function getEstado()
    {
        return $this->estado;
    }

    public function setEstado($value)
    {
        $this->estado = $value;
    }

    public function getRegistradopor()
    {
        return $this->registradopor;
    }

    public function setRegistradopor($value)
    {
        $this->registradopor = $value;
    }

    public function getModificadopor()
    {
        return $this->modificadopor;
    }

    public function setModificadopor($value)
    {
        $this->modificadopor = $value;
    }

    public function getFechahorareg()
    {
        return $this->fechahorareg;
    }

    public function setFechahorareg($value)
    {
        $this->fechahorareg = $value;
    }

    public function getFechahoramod()
    {
        return $this->fechahoramod;
    }

    public function setFechahoramod($value)
    {
        $this->fechahoramod = $value;
    }
}
