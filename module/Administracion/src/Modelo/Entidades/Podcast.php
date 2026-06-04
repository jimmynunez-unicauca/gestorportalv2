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

class Podcast implements InputFilterAwareInterface
{

    private $idPodcast;
    private $titulo;
    private $descripcion;
    private $imagen;
    private $audio_url;
    private $categoria;
    private $transmision;
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
            'name' => 'titulo',
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
            'name' => 'descripcion',
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
                        /* 'max' => 100, */
                    ],
                ],
            ],
        ]);

        $this->inputFilter = $inputFilter;
        return $this->inputFilter;
    }

    //------------------------------------------------------------------------------
    public function getIdPodcast()
    {
        return $this->idPodcast;
    }

    public function setIdPodcast($value)
    {
        $this->idPodcast = $value;
    }

    public function getTitulo()
    {
        return $this->titulo;
    }

    public function setTitulo($value)
    {
        $this->titulo = $value;
    }

    public function getDescripcion()
    {
        return $this->descripcion;
    }

    public function setDescripcion($value)
    {
        $this->descripcion = $value;
    }

    public function getImagen()
    {
        return $this->imagen;
    }

    public function setImagen($value)
    {
        $this->imagen = $value;
    }

    public function getAudio_url()
    {
        return $this->audio_url;
    }

    public function setAudio_url($value)
    {
        $this->audio_url = $value;
    }

    public function getCategoria()
    {
        return $this->categoria;
    }

    public function setCategoria($value)
    {
        $this->categoria = $value;
    }

    public function getTransmision()
    {
        return $this->transmision;
    }

    public function setTransmision($value)
    {
        $this->transmision = $value;
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
