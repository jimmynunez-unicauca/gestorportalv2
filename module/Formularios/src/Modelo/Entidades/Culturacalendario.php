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

class Culturacalendario implements InputFilterAwareInterface
{

    private $idCalendarioCultura;
    private $tipo;
    private $title;
    private $descripcion;
    private $dirigido;
    private $costo;
    private $organizado;
    private $imagen;
    private $lugar;
    private $start;
    private $end;
    private $color;
    private $textColor;
    private $allDay;
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
            'name' => 'title',
            'required' => true,
            'filters' => [
                ['name' => StripTags::class],
                ['name' => StringTrim::class],
                /* ['name' => StringToUpper::class], */
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
        /* $inputFilter->add([
            'name' => 'start',
            'required' => true,
            'filters' => [
                ['name' => StripTags::class],
                ['name' => StringTrim::class],
            ],
            'validators' => [
                [
                    'name' => DateTimeLocal::class,
                    'options' => [
                        'format' => 'Y-m-d\TH:iP'
                    ],
                ],
            ],
        ]);
        $inputFilter->add([
            'name' => 'end',
            'required' => true,
            'filters' => [
                ['name' => StripTags::class],
                ['name' => StringTrim::class],
            ],
            'validators' => [
                [
                    'name' => DateTimeLocal::class,
                    'options' => [
                        'format' => 'Y-m-d\TH:iP'
                    ],
                ],
            ],
        ]); */
        $inputFilter->add([
            'name' => 'allDay',
            'required' => true,
            'filters' => [
                ['name' => StripTags::class],
                ['name' => StringTrim::class],
            ],
        ]);


        $this->inputFilter = $inputFilter;
        return $this->inputFilter;
    }

    //------------------------------------------------------------------------------

    public function getIdCalendarioCultura()
    {
        return $this->idCalendarioCultura;
    }

    public function setIdCalendarioCultura($value)
    {
        $this->idCalendarioCultura = $value;
    }

    public function getTipo()
    {
        return $this->tipo;
    }

    public function setTipo($value)
    {
        $this->tipo = $value;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function setTitle($value)
    {
        $this->title = $value;
    }

    public function getDescripcion()
    {
        return $this->descripcion;
    }

    public function setDescripcion($value)
    {
        $this->descripcion = $value;
    }

    public function getDirigido()
    {
        return $this->dirigido;
    }

    public function setDirigido($value)
    {
        $this->dirigido = $value;
    }

    public function getCosto()
    {
        return $this->costo;
    }

    public function setCosto($value)
    {
        $this->costo = $value;
    }

    public function getOrganizado()
    {
        return $this->organizado;
    }

    public function setOrganizado($value)
    {
        $this->organizado = $value;
    }

    public function getImagen()
    {
        return $this->imagen;
    }

    public function setImagen($value)
    {
        $this->imagen = $value;
    }

    public function getLugar()
    {
        return $this->lugar;
    }

    public function setLugar($value)
    {
        $this->lugar = $value;
    }

    public function getStart()
    {
        return $this->start;
    }

    public function setStart($value)
    {
        $this->start = $value;
    }

    public function getEnd()
    {
        return $this->end;
    }

    public function setEnd($value)
    {
        $this->end = $value;
    }

    public function getColor()
    {
        return $this->color;
    }

    public function setColor($value)
    {
        $this->color = $value;
    }

    public function getTextColor()
    {
        return $this->textColor;
    }

    public function setTextColor($value)
    {
        $this->textColor = $value;
    }

    public function getAllDay()
    {
        return $this->allDay;
    }

    public function setAllDay($value)
    {
        $this->allDay = $value;
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
