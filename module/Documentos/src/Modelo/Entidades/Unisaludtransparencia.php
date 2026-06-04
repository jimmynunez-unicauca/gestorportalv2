<?php

namespace Documentos\Modelo\Entidades;

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

class Unisaludtransparencia implements InputFilterAwareInterface
{

    private $id;
    private $titulo;
    private $fecha_publicacion;
    private $tipo_documento;
    private $ruta_archivo;
    private $creado_por;
    private $creado_el;
    private $actualizado_por;
    private $actualizado_el;
    private $activo;
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
            'name' => 'titulo',
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
    public function getId(): mixed
    {
        return $this->id;
    }

    public function getTitulo(): mixed
    {
        return $this->titulo;
    }

    public function getFecha_publicacion(): mixed
    {
        return $this->fecha_publicacion;
    }

    public function getTipo_documento(): mixed
    {
        return $this->tipo_documento;
    }

    public function getRuta_archivo(): mixed
    {
        return $this->ruta_archivo;
    }

    public function getCreado_por(): mixed
    {
        return $this->creado_por;
    }

    public function getCreado_el(): mixed
    {
        return $this->creado_el;
    }

    public function getActualizado_por(): mixed
    {
        return $this->actualizado_por;
    }

    public function getActualizado_el(): mixed
    {
        return $this->actualizado_el;
    }

    public function getActivo(): mixed
    {
        return $this->activo;
    }

    public function setId(mixed $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function setTitulo(mixed $titulo): self
    {
        $this->titulo = $titulo;
        return $this;
    }

    public function setFecha_publicacion(mixed $fecha_publicacion): self
    {
        $this->fecha_publicacion = $fecha_publicacion;
        return $this;
    }

    public function setTipo_documento(mixed $tipo_documento): self
    {
        $this->tipo_documento = $tipo_documento;
        return $this;
    }

    public function setRuta_archivo(mixed $ruta_archivo): self
    {
        $this->ruta_archivo = $ruta_archivo;
        return $this;
    }

    public function setCreado_por(mixed $creado_por): self
    {
        $this->creado_por = $creado_por;
        return $this;
    }

    public function setCreado_el(mixed $creado_el): self
    {
        $this->creado_el = $creado_el;
        return $this;
    }

    public function setActualizado_por(mixed $actualizado_por): self
    {
        $this->actualizado_por = $actualizado_por;
        return $this;
    }

    public function setActualizado_el(mixed $actualizado_el): self
    {
        $this->actualizado_el = $actualizado_el;
        return $this;
    }

    public function setActivo(mixed $activo): self
    {
        $this->activo = $activo;
        return $this;
    }
}
