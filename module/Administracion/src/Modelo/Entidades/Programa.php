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

class Programa implements InputFilterAwareInterface
{

    private $idPrograma;
    private $segmento;
    private $hora_inicio;
    private $hora_fin;
    private $nombre_programa;
    private $detalle_programa;
    private $color_programa;
    private $imagen;
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
            'name' => 'nombre_programa',
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
                        'max' => 100,
                    ],
                ],
            ],
        ]);
        $inputFilter->add([
            'name' => 'detalle_programa',
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
                    ],
                ],
            ],
        ]);

        $this->inputFilter = $inputFilter;
        return $this->inputFilter;
    }

    //------------------------------------------------------------------------------

    public function getIdPrograma()
    {
        return $this->idPrograma;
    }

    public function setIdPrograma($value)
    {
        $this->idPrograma = $value;
    }

    public function getSegmento()
    {
        return $this->segmento;
    }

    public function setSegmento($value)
    {
        $this->segmento = $value;
    }

    public function getHora_inicio()
    {
        return $this->hora_inicio;
    }

    public function setHora_inicio($value)
    {
        $this->hora_inicio = $value;
    }

    public function getHora_fin()
    {
        return $this->hora_fin;
    }

    public function setHora_fin($value)
    {
        $this->hora_fin = $value;
    }

    public function getNombre_programa()
    {
        return $this->nombre_programa;
    }

    public function setNombre_programa($value)
    {
        $this->nombre_programa = $value;
    }

    public function getDetalle_programa()
    {
        return $this->detalle_programa;
    }

    public function setDetalle_programa($value)
    {
        $this->detalle_programa = $value;
    }

    public function getColor_programa()
    {
        return $this->color_programa;
    }

    public function setColor_programa($value)
    {
        $this->color_programa = $value;
    }

    public function getImagen()
    {
        return $this->imagen;
    }

    public function setImagen($value)
    {
        $this->imagen = $value;
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
