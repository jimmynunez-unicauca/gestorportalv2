<?php

namespace Administracion\Modelo\Entidades;

use DomainException;
use Laminas\Filter\StringTrim;
use Laminas\Filter\StripTags;
use Laminas\InputFilter\InputFilter;
use Laminas\InputFilter\InputFilterAwareInterface;
use Laminas\InputFilter\InputFilterInterface;
use Laminas\Validator\StringLength;
use Laminas\Validator\InArray;

class Programacion implements InputFilterAwareInterface
{
    private $idProgramacion;
    private $idPrograma;
    private $segmento;
    private $hora_inicio;
    private $hora_fin;
    private $dias_semana;
    private $vigente_desde;
    private $vigente_hasta;
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
        // Para el INSERT/UPDATE, no incluimos campos autogenerados o que se manejan aparte
        unset($datos['dias']); // no existe pero por si acaso
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
            'name' => 'idPrograma',
            'required' => true,
            'filters' => [['name' => \Laminas\Filter\ToInt::class]],
        ]);

        $inputFilter->add([
            'name' => 'segmento',
            'required' => true,
            'validators' => [[
                'name' => InArray::class,
                'options' => ['haystack' => ['madrugada', 'dia']],
            ]],
        ]);

        $inputFilter->add([
            'name' => 'hora_inicio',
            'required' => true,
        ]);

        $inputFilter->add([
            'name' => 'hora_fin',
            'required' => true,
        ]);

        $inputFilter->add([
            'name' => 'dias',
            'required' => true,
            'validators' => [[
                'name' => \Laminas\Validator\NotEmpty::class,
            ]],
        ]);

        $inputFilter->add([
            'name' => 'vigente_desde',
            'required' => true,
        ]);

        // Vigente hasta (opcional)
        $inputFilter->add([
            'name' => 'vigente_hasta',
            'required' => false,
            'allow_empty' => true,
            'filters' => [
                ['name' => \Laminas\Filter\ToNull::class],
            ],
            'validators' => [
                [
                    'name' => \Laminas\Validator\Callback::class,
                    'options' => [
                        'callback' => function ($value) {
                            // Si está vacío o es null, es válido (campo opcional)
                            if ($value === null || $value === '') {
                                return true;
                            }
                            // Si tiene valor, debe ser una fecha válida en formato Y-m-d
                            $date = \DateTime::createFromFormat('Y-m-d', $value);
                            return $date && $date->format('Y-m-d') === $value;
                        },
                        'messages' => [
                            \Laminas\Validator\Callback::INVALID_VALUE => 'La fecha "vigente hasta" no es válida.',
                        ],
                    ],
                ],
            ],
        ]);

        $this->inputFilter = $inputFilter;
        return $this->inputFilter;
    }

    // Getters y setters
    public function getIdProgramacion()
    {
        return $this->idProgramacion;
    }
    public function setIdProgramacion($v)
    {
        $this->idProgramacion = $v;
    }

    public function getIdPrograma()
    {
        return $this->idPrograma;
    }
    public function setIdPrograma($v)
    {
        $this->idPrograma = $v;
    }

    public function getSegmento()
    {
        return $this->segmento;
    }
    public function setSegmento($v)
    {
        $this->segmento = $v;
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

    public function getDias_semana()
    {
        return $this->dias_semana;
    }
    public function setDias_semana($v)
    {
        $this->dias_semana = $v;
    }

    public function getVigente_desde()
    {
        return $this->vigente_desde;
    }
    public function setVigente_desde($v)
    {
        $this->vigente_desde = $v;
    }

    public function getVigente_hasta()
    {
        return $this->vigente_hasta;
    }
    public function setVigente_hasta($v)
    {
        $this->vigente_hasta = $v;
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
