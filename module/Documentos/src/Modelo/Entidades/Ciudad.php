<?php

namespace Documentos\Modelo\Entidades;

use DomainException;
use Laminas\Filter\StringTrim;
use Laminas\Filter\StripTags;
use Laminas\InputFilter\InputFilter;
use Laminas\InputFilter\InputFilterAwareInterface;
use Laminas\InputFilter\InputFilterInterface;
use Laminas\Validator\StringLength;

class Ciudad implements InputFilterAwareInterface
{
    private $idCiudades;
    private $ciudad;
    private $state_province;
    private $idCountries;
    private $estado;
    private $registradopor;
    private $modificadopor;
    private $fechahorareg;
    private $fechahoramod;
    private $inputFilter;

    public function __construct(array $datos = null)
    {
        if (is_array($datos)) $this->exchangeArray($datos);
    }

    public function exchangeArray($data)
    {
        $metodos = get_class_methods($this);
        foreach ($data as $key => $value) {
            $metodo = 'set' . ucfirst($key);
            if (in_array($metodo, $metodos)) $this->$metodo($value);
        }
    }

    public function getArrayCopy()
    {
        $datos = get_object_vars($this);
        unset($datos['inputFilter']);
        return $datos;
    }

    public function setInputFilter(InputFilterInterface $inputFilter)
    {
        throw new DomainException('No permite inyección de filtro alterno');
    }

    public function getInputFilter()
    {
        if ($this->inputFilter) return $this->inputFilter;
        $inputFilter = new InputFilter();

        $inputFilter->add([
            'name' => 'ciudad',
            'required' => true,
            'filters' => [['name' => StripTags::class], ['name' => StringTrim::class]],
            'validators' => [
                ['name' => StringLength::class, 'options' => ['encoding' => 'UTF-8', 'max' => 100]],
            ],
        ]);

        $inputFilter->add([
            'name' => 'state_province',
            'required' => false,
            'filters' => [['name' => StripTags::class], ['name' => StringTrim::class], ['name' => 'Null']],
            'validators' => [
                ['name' => StringLength::class, 'options' => ['encoding' => 'UTF-8', 'max' => 100]],
            ],
        ]);

        $inputFilter->add([
            'name' => 'idCountries',
            'required' => true,
            'filters' => [['name' => 'Int']],
            'validators' => [['name' => 'GreaterThan', 'options' => ['min' => 0]]],
        ]);

        $this->inputFilter = $inputFilter;
        return $this->inputFilter;
    }

    // ---- getters/setters ----
    public function getIdCiudades()
    {
        return $this->idCiudades;
    }
    public function setIdCiudades($v)
    {
        $this->idCiudades = $v;
    }

    public function getCiudad()
    {
        return $this->ciudad;
    }
    public function setCiudad($v)
    {
        $this->ciudad = $v;
    }

    public function getState_province()
    {
        return $this->state_province;
    }
    public function setState_province($v)
    {
        $this->state_province = $v;
    }

    public function getIdCountries()
    {
        return $this->idCountries;
    }
    public function setIdCountries($v)
    {
        $this->idCountries = $v;
    }

    public function getEstado()
    {
        return $this->estado;
    }
    public function setEstado($v)
    {
        $this->estado = $v;
    }

    public function getRegistradopor()
    {
        return $this->registradopor;
    }
    public function setRegistradopor($v)
    {
        $this->registradopor = $v;
    }

    public function getModificadopor()
    {
        return $this->modificadopor;
    }
    public function setModificadopor($v)
    {
        $this->modificadopor = $v;
    }

    public function getFechahorareg()
    {
        return $this->fechahorareg;
    }
    public function setFechahorareg($v)
    {
        $this->fechahorareg = $v;
    }

    public function getFechahoramod()
    {
        return $this->fechahoramod;
    }
    public function setFechahoramod($v)
    {
        $this->fechahoramod = $v;
    }
}
