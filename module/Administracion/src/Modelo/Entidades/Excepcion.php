<?php

namespace Administracion\Modelo\Entidades;

use DomainException;
use Laminas\InputFilter\InputFilter;
use Laminas\InputFilter\InputFilterAwareInterface;
use Laminas\InputFilter\InputFilterInterface;
use Laminas\Filter\ToInt;

class Excepcion implements InputFilterAwareInterface
{
    private $idExcepcion;
    private $fecha;
    private $idProgramacion_original;
    private $idPrograma_reemplazo;
    private $hora_inicio;
    private $hora_fin;
    private $motivo;
    private $tipo;
    private $registradopor;
    private $fechahorareg;
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
            'name' => 'fecha',
            'required' => true,
        ]);

        $inputFilter->add([
            'name' => 'tipo',
            'required' => true,
        ]);

        $inputFilter->add([
            'name' => 'idProgramacion_original',
            'required' => false,
            'allow_empty' => true,
            'filters' => [
                ['name' => ToInt::class],
            ],
        ]);

        $inputFilter->add([
            'name' => 'idPrograma_reemplazo',
            'required' => false,
            'allow_empty' => true,
            'filters' => [
                ['name' => ToInt::class],
            ],
        ]);

        $this->inputFilter = $inputFilter;
        return $this->inputFilter;
    }

    // Getters y Setters
    public function getIdExcepcion()
    {
        return $this->idExcepcion;
    }
    public function setIdExcepcion($v)
    {
        $this->idExcepcion = $v;
    }

    public function getFecha()
    {
        return $this->fecha;
    }
    public function setFecha($v)
    {
        $this->fecha = $v;
    }

    public function getIdProgramacion_original()
    {
        return $this->idProgramacion_original;
    }
    public function setIdProgramacion_original($v)
    {
        $this->idProgramacion_original = $v;
    }

    public function getIdPrograma_reemplazo()
    {
        return $this->idPrograma_reemplazo;
    }
    public function setIdPrograma_reemplazo($v)
    {
        $this->idPrograma_reemplazo = $v;
    }

    public function getHora_inicio()
    {
        return $this->hora_inicio;
    }
    public function setHora_inicio($v)
    {
        $this->hora_inicio = $v;
    }

    public function getHora_fin()
    {
        return $this->hora_fin;
    }
    public function setHora_fin($v)
    {
        $this->hora_fin = $v;
    }

    public function getMotivo()
    {
        return $this->motivo;
    }
    public function setMotivo($v)
    {
        $this->motivo = $v;
    }

    public function getTipo()
    {
        return $this->tipo;
    }
    public function setTipo($v)
    {
        $this->tipo = $v;
    }

    public function getRegistradopor()
    {
        return $this->registradopor;
    }
    public function setRegistradopor($v)
    {
        $this->registradopor = $v;
    }

    public function getFechahorareg()
    {
        return $this->fechahorareg;
    }
    public function setFechahorareg($v)
    {
        $this->fechahorareg = $v;
    }
}
