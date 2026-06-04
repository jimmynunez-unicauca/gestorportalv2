<?php
// module/Formularios/src/Modelo/Entidades/Convocatoria.php

namespace Formularios\Modelo\Entidades;

use Laminas\InputFilter\InputFilter;
use Laminas\InputFilter\InputFilterAwareInterface;
use Laminas\InputFilter\InputFilterInterface;

class Convocatoria implements InputFilterAwareInterface
{
    private $id_convocatoria;
    private $id_config;
    private $nombre_convocatoria;
    private $periodo;
    private $cupo_maximo;
    private $inscritos_actuales;
    private $fecha_inicio;
    private $fecha_fin;
    private $hora_limite_diaria;
    private $activo;
    private $created_at;
    private $updated_at;
    private $nombre_formulario;

    protected $inputFilter;

    public function __construct($data = null)
    {
        if ($data) {
            $this->exchangeArray($data);
        }
    }

    public function exchangeArray($data)
    {
        $this->id_convocatoria = $data['id_convocatoria'] ?? null;
        $this->id_config = $data['id_config'] ?? null;
        $this->nombre_convocatoria = $data['nombre_convocatoria'] ?? null;
        $this->periodo = $data['periodo'] ?? null;
        $this->cupo_maximo = $data['cupo_maximo'] ?? 0;
        $this->inscritos_actuales = $data['inscritos_actuales'] ?? 0;
        $this->fecha_inicio = $data['fecha_inicio'] ?? null;
        $this->fecha_fin = $data['fecha_fin'] ?? null;
        $this->hora_limite_diaria = $data['hora_limite_diaria'] ?? '11:30:00';
        $this->activo = $data['activo'] ?? 1;
        $this->created_at = $data['created_at'] ?? null;
        $this->updated_at = $data['updated_at'] ?? null;
        $this->nombre_formulario = $data['nombre_formulario'] ?? null;
    }

    public function getArrayCopy()
    {
        return [
            'id_convocatoria' => $this->id_convocatoria,
            'id_config' => $this->id_config,
            'nombre_convocatoria' => $this->nombre_convocatoria,
            'periodo' => $this->periodo,
            'cupo_maximo' => $this->cupo_maximo,
            'inscritos_actuales' => $this->inscritos_actuales,
            'fecha_inicio' => $this->fecha_inicio,
            'fecha_fin' => $this->fecha_fin,
            'hora_limite_diaria' => $this->hora_limite_diaria,
            'activo' => $this->activo,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'nombre_formulario' => $this->nombre_formulario,
        ];
    }

    // CORREGIDO: Este método debe retornar un objeto InputFilter
    public function getInputFilter()
    {
        if (!$this->inputFilter) {
            $inputFilter = new InputFilter();

            $inputFilter->add([
                'name' => 'id_config',
                'required' => true,
                'filters' => [
                    ['name' => 'Int'],
                ],
                'validators' => [
                    ['name' => 'NotEmpty'],
                ],
            ]);

            $inputFilter->add([
                'name' => 'nombre_convocatoria',
                'required' => true,
                'filters' => [
                    ['name' => 'StringTrim'],
                    ['name' => 'StripTags'],
                ],
                'validators' => [
                    ['name' => 'NotEmpty'],
                    ['name' => 'StringLength', 'options' => ['min' => 3, 'max' => 150]],
                ],
            ]);

            $inputFilter->add([
                'name' => 'periodo',
                'required' => true,
                'filters' => [
                    ['name' => 'StringTrim'],
                ],
                'validators' => [
                    ['name' => 'NotEmpty'],
                ],
            ]);

            $inputFilter->add([
                'name' => 'cupo_maximo',
                'required' => true,
                'filters' => [
                    ['name' => 'Int'],
                ],
                'validators' => [
                    ['name' => 'NotEmpty'],
                    ['name' => 'GreaterThan', 'options' => ['min' => 0]],
                ],
            ]);

            $inputFilter->add([
                'name' => 'fecha_inicio',
                'required' => true,
                'validators' => [
                    ['name' => 'NotEmpty'],
                ],
            ]);

            $inputFilter->add([
                'name' => 'fecha_fin',
                'required' => true,
                'validators' => [
                    ['name' => 'NotEmpty'],
                ],
            ]);

            // Estos campos NO son requeridos en el formulario de registro
            // Se llenan automáticamente en el controlador o BD
            $inputFilter->add([
                'name' => 'id_convocatoria',
                'required' => false,  // Cambiado a false
                'allow_empty' => true,
            ]);

            $inputFilter->add([
                'name' => 'inscritos_actuales',
                'required' => false,  // Cambiado a false
                'allow_empty' => true,
            ]);

            $inputFilter->add([
                'name' => 'hora_limite_diaria',
                'required' => false,
                'allow_empty' => true,
            ]);

            $inputFilter->add([
                'name' => 'activo',
                'required' => false,
            ]);

            $this->inputFilter = $inputFilter;
        }

        return $this->inputFilter;
    }

    public function setInputFilter(InputFilterInterface $inputFilter)
    {
        $this->inputFilter = $inputFilter;
    }

    // Getters y Setters
    public function getIdConvocatoria()
    {
        return $this->id_convocatoria;
    }
    public function setIdConvocatoria($id_convocatoria)
    {
        $this->id_convocatoria = $id_convocatoria;
    }

    public function getIdConfig()
    {
        return $this->id_config;
    }
    public function setIdConfig($id_config)
    {
        $this->id_config = $id_config;
    }

    public function getNombreConvocatoria()
    {
        return $this->nombre_convocatoria;
    }
    public function setNombreConvocatoria($nombre_convocatoria)
    {
        $this->nombre_convocatoria = $nombre_convocatoria;
    }

    public function getPeriodo()
    {
        return $this->periodo;
    }
    public function setPeriodo($periodo)
    {
        $this->periodo = $periodo;
    }

    public function getCupoMaximo()
    {
        return $this->cupo_maximo;
    }
    public function setCupoMaximo($cupo_maximo)
    {
        $this->cupo_maximo = $cupo_maximo;
    }

    public function getInscritosActuales()
    {
        return $this->inscritos_actuales;
    }
    public function setInscritosActuales($inscritos_actuales)
    {
        $this->inscritos_actuales = $inscritos_actuales;
    }

    public function getFechaInicio()
    {
        return $this->fecha_inicio;
    }
    public function setFechaInicio($fecha_inicio)
    {
        $this->fecha_inicio = $fecha_inicio;
    }

    public function getFechaFin()
    {
        return $this->fecha_fin;
    }
    public function setFechaFin($fecha_fin)
    {
        $this->fecha_fin = $fecha_fin;
    }

    public function getHoraLimiteDiaria()
    {
        return $this->hora_limite_diaria;
    }
    public function setHoraLimiteDiaria($hora_limite_diaria)
    {
        $this->hora_limite_diaria = $hora_limite_diaria;
    }

    public function getActivo()
    {
        return $this->activo;
    }
    public function setActivo($activo)
    {
        $this->activo = $activo;
    }

    public function getCreatedAt()
    {
        return $this->created_at;
    }
    public function setCreatedAt($created_at)
    {
        $this->created_at = $created_at;
    }

    public function getUpdatedAt()
    {
        return $this->updated_at;
    }
    public function setUpdatedAt($updated_at)
    {
        $this->updated_at = $updated_at;
    }

    public function getNombreFormulario()
    {
        return $this->nombre_formulario;
    }
    public function setNombreFormulario($nombre_formulario)
    {
        $this->nombre_formulario = $nombre_formulario;
    }
}
