<?php

namespace Formularios\Modelo\Entidades;

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

class Pfi implements InputFilterAwareInterface
{

    private $id_config;
    private $nombre_formulario;
    private $slug;
    private $activo;
    private $descripcion;
    private $instrucciones;
    private $created_at;
    private $updated_at;
    private $created_by;
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
        // Convertir snake_case a camelCase para los setters
        foreach ($data as $key => $value) {
            // Convertir 'nombre_formulario' a 'nombreFormulario'
            $camelCase = lcfirst(str_replace('_', '', ucwords($key, '_')));
            $metodo = 'set' . ucfirst($camelCase);

            if (method_exists($this, $metodo)) {
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
            'name' => 'nombre_formulario',
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
                        'min' => 1,
                        'max' => 255,
                    ],
                ],
            ],
        ]);

        // 🔴 NUEVO: Campo slug
        $inputFilter->add([
            'name' => 'slug',
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
                        'min' => 1,
                        'max' => 255,
                    ],
                ],
            ],
        ]);

        // 🔴 NUEVO: Campo activo
        $inputFilter->add([
            'name' => 'activo',
            'required' => false,
            'allow_empty' => true,
            'filters' => [
                ['name' => ToInt::class],
            ],
            'validators' => [
                [
                    'name' => Digits::class,
                ],
            ],
        ]);

        // 🔴 NUEVO: Campo created_by
        $inputFilter->add([
            'name' => 'created_by',
            'required' => false,
            'allow_empty' => true,
            'filters' => [
                ['name' => StripTags::class],
                ['name' => StringTrim::class],
            ],
        ]);

        // 🔴 NUEVO: Campo created_at
        $inputFilter->add([
            'name' => 'created_at',
            'required' => false,
            'allow_empty' => true,
            'filters' => [
                ['name' => StripTags::class],
                ['name' => StringTrim::class],
            ],
            'validators' => [
                [
                    'name' => Date::class,
                    'options' => [
                        'format' => 'Y-m-d H:i:s',
                    ],
                ],
            ],
        ]);

        // Campo descripcion (ya lo tienes)
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
                    ],
                ],
            ],
        ]);

        // Campo instrucciones (ya lo tienes)
        $inputFilter->add([
            'name' => 'instrucciones',
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
                    ],
                ],
            ],
        ]);

        $this->inputFilter = $inputFilter;
        return $this->inputFilter;
    }
    //------------------------------------------------------------------------------

    public function getIdConfig()
    {
        return $this->id_config;
    }

    public function setIdConfig($value)
    {
        $this->id_config = $value;
    }

    public function getNombreFormulario()
    {
        return $this->nombre_formulario;
    }

    public function setNombreFormulario($value)
    {
        $this->nombre_formulario = $value;
    }

    public function getSlug()
    {
        return $this->slug;
    }

    public function setSlug($value)
    {
        $this->slug = $value;
    }

    public function getActivo()
    {
        return $this->activo;
    }

    public function setActivo($value)
    {
        $this->activo = $value;
    }

    public function getDescripcion()
    {
        return $this->descripcion;
    }

    public function setDescripcion($value)
    {
        $this->descripcion = $value;
    }

    public function getInstrucciones()
    {
        return $this->instrucciones;
    }

    public function setInstrucciones($value)
    {
        $this->instrucciones = $value;
    }

    public function getCreatedAt()
    {
        return $this->created_at;
    }

    public function setCreatedAt($value)
    {
        $this->created_at = $value;
    }

    public function getUpdatedAt()
    {
        return $this->updated_at;
    }

    public function setUpdatedAt($value)
    {
        $this->updated_at = $value;
    }

    public function getCreatedBy()
    {
        return $this->created_by;
    }

    public function setCreatedBy($value)
    {
        $this->created_by = $value;
    }
}
