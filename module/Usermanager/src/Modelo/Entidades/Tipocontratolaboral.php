<?php

namespace Talentohumano\Modelo\Entidades;

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

class Tipocontratolaboral implements InputFilterAwareInterface
{

    private $idTipoContratoLaboral;
    private $tipo;
    private $plantilla;
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
            'name' => 'tipo',
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
                        'max' => 50,
                    ],
                ],
            ],
        ]);


        $this->inputFilter = $inputFilter;
        return $this->inputFilter;
    }

    //------------------------------------------------------------------------------

    function getIdTipoContratoLaboral()
    {
        return $this->idTipoContratoLaboral;
    }

    function getTipo()
    {
        return $this->tipo;
    }

    function getPlantilla()
    {
        return $this->plantilla;
    }

    function getEstado()
    {
        return $this->estado;
    }

    function getRegistradopor()
    {
        return $this->registradopor;
    }

    function getModificadopor()
    {
        return $this->modificadopor;
    }

    function getFechahorareg()
    {
        return $this->fechahorareg;
    }

    function getFechahoramod()
    {
        return $this->fechahoramod;
    }

    function setIdTipoContratoLaboral($idTipoContratoLaboral)
    {
        $this->idTipoContratoLaboral = $idTipoContratoLaboral;
    }

    function setTipo($tipo)
    {
        $this->tipo = $tipo;
    }

    function setPlantilla($plantilla)
    {
        $this->plantilla = $plantilla;
    }

    function setEstado($estado)
    {
        $this->estado = $estado;
    }

    function setRegistradopor($registradopor)
    {
        $this->registradopor = $registradopor;
    }

    function setModificadopor($modificadopor)
    {
        $this->modificadopor = $modificadopor;
    }

    function setFechahorareg($fechahorareg)
    {
        $this->fechahorareg = $fechahorareg;
    }

    function setFechahoramod($fechahoramod)
    {
        $this->fechahoramod = $fechahoramod;
    }
}
