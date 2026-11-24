<?php

namespace Documentos\Modelo\Entidades;

use DomainException;
use Laminas\Filter\StringTrim;
use Laminas\Filter\StripTags;
use Laminas\InputFilter\InputFilter;
use Laminas\InputFilter\InputFilterAwareInterface;
use Laminas\InputFilter\InputFilterInterface;
use Laminas\Validator\StringLength;

class Instituto implements InputFilterAwareInterface
{
    private $idInstituto;
    private $idCiudades;
    private $institucion;
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
            'name' => 'institucion',
            'required' => true,
            'filters' => [['name' => StripTags::class], ['name' => StringTrim::class]],
            'validators' => [
                ['name' => StringLength::class, 'options' => ['encoding' => 'UTF-8', 'max' => 100]],
            ],
        ]);

        $inputFilter->add([
            'name' => 'idCiudades',
            'required' => true,
            'filters' => [['name' => 'Int']],
            'validators' => [['name' => 'GreaterThan', 'options' => ['min' => 0]]],
        ]);

        $this->inputFilter = $inputFilter;
        return $this->inputFilter;
    }

    // ---- getters/setters ----
    public function getIdInstituto()
    {
        return $this->idInstituto;
    }
    public function setIdInstituto($v)
    {
        $this->idInstituto = $v;
    }

    public function getIdCiudades()
    {
        return $this->idCiudades;
    }
    public function setIdCiudades($v)
    {
        $this->idCiudades = $v;
    }

    public function getInstitucion()
    {
        return $this->institucion;
    }
    public function setInstitucion($v)
    {
        $this->institucion = $v;
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
