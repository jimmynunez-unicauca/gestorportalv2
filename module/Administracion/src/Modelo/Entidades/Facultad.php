<?php

namespace Administracion\Modelo\Entidades;

use DomainException;
use Laminas\Filter\StringTrim;
use Laminas\Filter\StripTags;
use Laminas\InputFilter\InputFilter;
use Laminas\InputFilter\InputFilterAwareInterface;
use Laminas\InputFilter\InputFilterInterface;
use Laminas\Validator\StringLength;

class Facultad implements InputFilterAwareInterface
{
    private $idFacultad;
    private $facultad;
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
            'name' => 'facultad',
            'required' => true,
            'filters' => [['name' => StripTags::class], ['name' => StringTrim::class]],
            'validators' => [[
                'name' => StringLength::class,
                'options' => ['encoding' => 'UTF-8', 'max' => 100],
            ]],
        ]);

        $this->inputFilter = $inputFilter;
        return $this->inputFilter;
    }

    public function getIdFacultad()
    {
        return $this->idFacultad;
    }
    public function setIdFacultad($v)
    {
        $this->idFacultad = $v;
    }

    public function getFacultad()
    {
        return $this->facultad;
    }
    public function setFacultad($v)
    {
        $this->facultad = $v;
    }
}
