<?php

namespace Administracion\Modelo\Entidades;

use DomainException;
use Laminas\Filter\StringTrim;
use Laminas\Filter\StripTags;
use Laminas\InputFilter\InputFilter;
use Laminas\InputFilter\InputFilterAwareInterface;
use Laminas\InputFilter\InputFilterInterface;
use Laminas\Validator\StringLength;

class Programaacademico implements InputFilterAwareInterface
{
    private $idPrograma;
    private $idFacultad;
    private $programa;
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
            'name' => 'programa',
            'required' => true,
            'filters' => [['name' => StripTags::class], ['name' => StringTrim::class]],
            'validators' => [[
                'name' => StringLength::class,
                'options' => ['encoding' => 'UTF-8', 'max' => 100],
            ]],
        ]);

        $inputFilter->add([
            'name' => 'idFacultad',
            'required' => true,
            'filters' => [['name' => StringTrim::class]],
            'validators' => [['name' => \Laminas\Validator\Digits::class]],
        ]);

        $this->inputFilter = $inputFilter;
        return $this->inputFilter;
    }

    public function getIdPrograma()
    {
        return $this->idPrograma;
    }
    public function setIdPrograma($v)
    {
        $this->idPrograma = $v;
    }

    public function getIdFacultad()
    {
        return $this->idFacultad;
    }
    public function setIdFacultad($v)
    {
        $this->idFacultad = $v;
    }

    public function getPrograma()
    {
        return $this->programa;
    }
    public function setPrograma($v)
    {
        $this->programa = $v;
    }
}
