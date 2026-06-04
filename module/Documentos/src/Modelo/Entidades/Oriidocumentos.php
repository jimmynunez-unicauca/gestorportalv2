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

class Oriidocumentos implements InputFilterAwareInterface
{

    private $id_documentos_orii;
    private $idOrii;
    private $nombre_documento;
    private $documento;
    private $estado_documento;
    private $registradopor_documento;
    private $fechahorareg_documento;
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
            'name' => 'nombre_documento',
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

    public function getId_documentos_orii()
    {
        return $this->id_documentos_orii;
    }

    public function setId_documentos_orii($value)
    {
        $this->id_documentos_orii = $value;
    }

    public function getIdOrii()
    {
        return $this->idOrii;
    }

    public function setIdOrii($value)
    {
        $this->idOrii = $value;
    }

    public function getNombre_documento()
    {
        return $this->nombre_documento;
    }

    public function setNombre_documento($value)
    {
        $this->nombre_documento = $value;
    }

    public function getDocumento()
    {
        return $this->documento;
    }

    public function setDocumento($value)
    {
        $this->documento = $value;
    }

    public function getEstado_documento()
    {
        return $this->estado_documento;
    }

    public function setEstado_documento($value)
    {
        $this->estado_documento = $value;
    }

    public function getRegistradopor_documento()
    {
        return $this->registradopor_documento;
    }

    public function setRegistradopor_documento($value)
    {
        $this->registradopor_documento = $value;
    }

    public function getFechahorareg_documento()
    {
        return $this->fechahorareg_documento;
    }

    public function setFechahorareg_documento($value)
    {
        $this->fechahorareg_documento = $value;
    }
}
