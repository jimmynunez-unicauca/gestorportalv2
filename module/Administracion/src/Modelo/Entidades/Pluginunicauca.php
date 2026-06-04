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

class Pluginunicauca implements InputFilterAwareInterface
{

    private $id;
    private $nombre_modulo;
    private $ruta_archivo;
    private $descripcion;
    private $activo;
    private $fecha_creacion;
    private $fecha_actualizacion;
    private $registradopor;
    private $modificadopor;
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
            'name' => 'nombre_modulo',
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
        $inputFilter->add([
            'name' => 'descripcion',
            'required' => false,
            'allow_empty' => true,
            'filters' => [
                ['name' => StripTags::class],
                ['name' => StringTrim::class],
            ],
            'validators' => [
                [
                    'name' => StringLength::class,
                    'options' => [
                        'encoding' => 'UTF-8',
                        /*  'max' => 200, */
                    ],
                ],
            ],
        ]);
        $inputFilter->add([
            'name' => 'ruta_archivo',
            'required' => true,
            'filters' => [
                ['name' => StripTags::class],
                ['name' => StringTrim::class],
            ],
            'validators' => [
                [
                    'name' => StringLength::class,
                    'options' => [
                        'encoding' => 'UTF-8',
                        /* 'max' => 500, */
                    ],
                ],
            ],
        ]);
        $inputFilter->add([
            'name' => 'activo',
            'required' => false,
            'allow_empty' => true,
            'filters' => [
                ['name' => ToInt::class],
            ],
        ]);


        $this->inputFilter = $inputFilter;
        return $this->inputFilter;
    }

    //------------------------------------------------------------------------------



    public function getId(): mixed
    {
        return $this->id;
    }

    public function getNombre_modulo(): mixed
    {
        return $this->nombre_modulo;
    }

    public function getRuta_archivo(): mixed
    {
        return $this->ruta_archivo;
    }

    public function getDescripcion(): mixed
    {
        return $this->descripcion;
    }

    public function getActivo(): mixed
    {
        return $this->activo;
    }

    public function getFecha_creacion(): mixed
    {
        return $this->fecha_creacion;
    }

    public function getFecha_actualizacion(): mixed
    {
        return $this->fecha_actualizacion;
    }

    public function getRegistradopor(): mixed
    {
        return $this->registradopor;
    }

    public function getModificadopor(): mixed
    {
        return $this->modificadopor;
    }

    public function setId(mixed $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function setNombre_modulo(mixed $nombre_modulo): self
    {
        $this->nombre_modulo = $nombre_modulo;
        return $this;
    }

    public function setRuta_archivo(mixed $ruta_archivo): self
    {
        $this->ruta_archivo = $ruta_archivo;
        return $this;
    }

    public function setDescripcion(mixed $descripcion): self
    {
        $this->descripcion = $descripcion;
        return $this;
    }

    public function setActivo(mixed $activo): self
    {
        $this->activo = $activo;
        return $this;
    }

    public function setFecha_creacion(mixed $fecha_creacion): self
    {
        $this->fecha_creacion = $fecha_creacion;
        return $this;
    }

    public function setFecha_actualizacion(mixed $fecha_actualizacion): self
    {
        $this->fecha_actualizacion = $fecha_actualizacion;
        return $this;
    }

    public function setRegistradopor(mixed $registradopor): self
    {
        $this->registradopor = $registradopor;
        return $this;
    }

    public function setModificadopor(mixed $modificadopor): self
    {
        $this->modificadopor = $modificadopor;
        return $this;
    }
}
