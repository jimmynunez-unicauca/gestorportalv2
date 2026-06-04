<?php

namespace Talentohumano\Modelo\Entidades;

use DomainException;
use Laminas\Filter\StringTrim;
use Laminas\Filter\StripTags;
use Laminas\Filter\ToInt;
use Laminas\Filter\StringToUpper;
use Laminas\InputFilter\InputFilter;
use Laminas\InputFilter\InputFilterAwareInterface;
use Laminas\InputFilter\InputFilterInterface;
use Laminas\Validator\StringLength;

class Busqueda implements InputFilterAwareInterface
{

    private $identificacionBusq;
    private $nombre1Busq;
    private $nombre2Busq;
    private $apellido1Busq;
    private $apellido2Busq;
    private $fechainiBusq;
    private $fechafinBusq;
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
            'name' => 'identificacion',
            'required' => false,
            'filters' => [
                ['name' => ToInt::class],
            ],
        ]);

        $inputFilter->add([
            'name' => 'nombre1',
            'required' => false,
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
                        'min' => 2,
                        'max' => 20,
                    ],
                ],
            ],
        ]);

        $inputFilter->add([
            'name' => 'nombre2',
            'required' => false,
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
                        'min' => 2,
                        'max' => 20,
                    ],
                ],
            ],
        ]);

        $inputFilter->add([
            'name' => 'apellido1',
            'required' => false,
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
                        'min' => 2,
                        'max' => 20,
                    ],
                ],
            ],
        ]);

        $inputFilter->add([
            'name' => 'apellido2',
            'required' => false,
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
                        'min' => 2,
                        'max' => 20,
                    ],
                ],
            ],
        ]);

        $inputFilter->add([
            'name' => 'fechainiBusq',
            'required' => false,
            'filters' => [
                ['name' => StripTags::class],
                ['name' => StringTrim::class],
            ],
            'validators' => [
                [
                    'name' => StringLength::class,
                    'options' => [
                        'encoding' => 'UTF-8',
                        'min' => 10,
                        'max' => 10,
                    ],
                ],
            ],
        ]);

        $inputFilter->add([
            'name' => 'fechafinBusq',
            'required' => false,
            'filters' => [
                ['name' => StripTags::class],
                ['name' => StringTrim::class],
            ],
            'validators' => [
                [
                    'name' => StringLength::class,
                    'options' => [
                        'encoding' => 'UTF-8',
                        'min' => 10,
                        'max' => 10,
                    ],
                ],
            ],
        ]);

        $this->inputFilter = $inputFilter;
        return $this->inputFilter;
    }

    //------------------------------------------------------------------------------

    public function getFiltroBusqueda()
    {
        $filtro = "";
        if ($this->fechainiBusq != '' && $this->fechafinBusq != '') {
            $filtro = "DATE(empleado.fechahorareg) >= '" . $this->fechainiBusq . "' AND DATE(empleado.fechahorareg) <= '" . $this->fechafinBusq . "'";
        } else {
            if ($this->identificacionBusq != '') {
                $filtro = "empleado.identificacion = " . $this->identificacionBusq;
            }
            if ($this->nombre1Busq != '') {
                if ($filtro != "") {
                    $filtro .= " AND ";
                }
                $filtro .= "empleado.nombre1 like '%" . $this->nombre1Busq . "%'";
            }
            if ($this->nombre2Busq != '') {
                if ($filtro != "") {
                    $filtro .= " AND ";
                }
                $filtro .= "empleado.nombre2 like '%" . $this->nombre2Busq . "%'";
            }
            if ($this->apellido1Busq != '') {
                if ($filtro != "") {
                    $filtro .= " AND ";
                }
                $filtro .= "empleado.apellido1 like '%" . $this->apellido1Busq . "%'";
            }
            if ($this->apellido2Busq != '') {
                if ($filtro != "") {
                    $filtro .= " AND ";
                }
                $filtro .= "empleado.apellido2 like '%" . $this->apellido2Busq . "%'";
            }
        }
        return $filtro;
    }

    //------------------------------------------------------------------------------

    public function getIdentificacionBusq()
    {
        return $this->identificacionBusq;
    }

    public function getNombre1Busq()
    {
        return $this->nombre1Busq;
    }

    public function getNombre2Busq()
    {
        return $this->nombre2Busq;
    }

    public function getApellido1Busq()
    {
        return $this->apellido1Busq;
    }

    public function getApellido2Busq()
    {
        return $this->apellido2Busq;
    }

    public function getFechahorainiBusq()
    {
        return $this->fechahorainiBusq;
    }

    public function getFechahorafinBusq()
    {
        return $this->fechahorafinBusq;
    }

    public function setIdentificacionBusq($identificacionBusq): void
    {
        $this->identificacionBusq = $identificacionBusq;
    }

    public function setNombre1Busq($nombre1Busq): void
    {
        $this->nombre1Busq = $nombre1Busq;
    }

    public function setNombre2Busq($nombre2Busq): void
    {
        $this->nombre2Busq = $nombre2Busq;
    }

    public function setApellido1Busq($apellido1Busq): void
    {
        $this->apellido1Busq = $apellido1Busq;
    }

    public function setApellido2Busq($apellido2Busq): void
    {
        $this->apellido2Busq = $apellido2Busq;
    }

    public function setFechahorainiBusq($fechahorainiBusq): void
    {
        $this->fechahorainiBusq = $fechahorainiBusq;
    }

    public function setFechahorafinBusq($fechahorafinBusq): void
    {
        $this->fechahorafinBusq = $fechahorafinBusq;
    }
}
