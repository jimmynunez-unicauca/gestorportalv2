<?php

namespace Administracion\Modelo\Entidades;

use DomainException;
use Laminas\Filter\StringTrim;
use Laminas\Filter\StripTags;
use Laminas\InputFilter\InputFilter;
use Laminas\InputFilter\InputFilterAwareInterface;
use Laminas\InputFilter\InputFilterInterface;
use Laminas\Validator\StringLength;

class Vriproyectos implements InputFilterAwareInterface
{
    private $id;
    private $titulo;
    private $descripcion;
    private $id_asignado;
    private $estado_proyecto;
    private $grupo_investigacion;
    private $director;
    private $correo;
    private $fecha;
    private $tipo_proyecto;
    private $enlace;
    private $estado;
    private $registradopor;
    private $modificadopor;
    private $fechahorareg;
    private $fechahoramod;

    // No persistente: arreglo de ids de convocatorias seleccionadas en el formulario
    private $convocatorias = [];

    private $inputFilter;

    public function __construct(array $datos = null)
    {
        if (is_array($datos)) {
            $this->exchangeArray($datos);
        }
    }

    public function exchangeArray($data)
    {
        $metodos = get_class_methods($this);
        foreach ($data as $key => $value) {
            $keyCamel = str_replace(' ', '', ucwords(str_replace('_', ' ', $key)));
            $metodo = 'set' . $keyCamel;
            if (in_array($metodo, $metodos)) {
                $this->$metodo($value);
            }
        }
    }

    public function getArrayCopy()
    {
        $datos = get_object_vars($this);
        unset($datos['inputFilter']);
        return $datos;
    }

    public function getArrayCopyPersistencia()
    {
        $datos = $this->getArrayCopy();
        unset($datos['convocatorias']);
        return $datos;
    }

    public function setInputFilter(InputFilterInterface $inputFilter)
    {
        throw new DomainException(sprintf('%s does not allow injection of an alternate input filter', __CLASS__));
    }

    public function getInputFilter()
    {
        if ($this->inputFilter) {
            return $this->inputFilter;
        }

        $inputFilter = new InputFilter();

        $inputFilter->add([
            'name' => 'titulo',
            'required' => true,
            'filters' => [
                ['name' => StripTags::class],
                ['name' => StringTrim::class],
            ],
            'validators' => [
                ['name' => StringLength::class, 'options' => ['encoding' => 'UTF-8', 'max' => 500]],
            ],
        ]);
        $inputFilter->add([
            'name' => 'descripcion',
            'required' => true,
            'filters' => [
                ['name' => StripTags::class],
                ['name' => StringTrim::class],
            ],
        ]);
        $inputFilter->add([
            'name' => 'id_asignado',
            'required' => true,
            'filters' => [['name' => StringTrim::class]],
            'validators' => [
                ['name' => StringLength::class, 'options' => ['encoding' => 'UTF-8', 'max' => 25]],
            ],
        ]);
        $inputFilter->add([
            'name' => 'estado_proyecto',
            'required' => true,
        ]);
        $inputFilter->add([
            'name' => 'grupo_investigacion',
            'required' => true,
            'filters' => [
                ['name' => StripTags::class],
                ['name' => StringTrim::class],
            ],
        ]);
        $inputFilter->add([
            'name' => 'director',
            'required' => true,
            'filters' => [
                ['name' => StripTags::class],
                ['name' => StringTrim::class],
            ],
            'validators' => [
                ['name' => StringLength::class, 'options' => ['encoding' => 'UTF-8', 'max' => 150]],
            ],
        ]);
        $inputFilter->add([
            'name' => 'correo',
            'required' => true,
            'filters' => [['name' => StringTrim::class]],
            'validators' => [
                ['name' => \Laminas\Validator\EmailAddress::class],
            ],
        ]);
        $inputFilter->add([
            'name' => 'fecha',
            'required' => true,
            'filters' => [['name' => StringTrim::class]],
        ]);
        $inputFilter->add([
            'name' => 'tipo_proyecto',
            'required' => true,
        ]);
        $inputFilter->add([
            'name' => 'enlace',
            'required' => false,
            'allow_empty' => true,
            'filters' => [
                ['name' => StripTags::class],
                ['name' => StringTrim::class],
            ],
            'validators' => [
                ['name' => StringLength::class, 'options' => ['encoding' => 'UTF-8', 'max' => 255]],
            ],
        ]);
        $inputFilter->add([
            'name' => 'convocatorias',
            'required' => false,
            'allow_empty' => true,
            'filters' => [],
            'validators' => [],
        ]);

        $this->inputFilter = $inputFilter;
        return $this->inputFilter;
    }

    public function getId()
    {
        return $this->id;
    }
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }
    public function getTitulo()
    {
        return $this->titulo;
    }
    public function setTitulo($titulo)
    {
        $this->titulo = $titulo;
        return $this;
    }
    public function getDescripcion()
    {
        return $this->descripcion;
    }
    public function setDescripcion($descripcion)
    {
        $this->descripcion = $descripcion;
        return $this;
    }
    public function getIdAsignado()
    {
        return $this->id_asignado;
    }
    public function setIdAsignado($id_asignado)
    {
        $this->id_asignado = $id_asignado;
        return $this;
    }
    public function getEstadoProyecto()
    {
        return $this->estado_proyecto;
    }
    public function setEstadoProyecto($estado_proyecto)
    {
        $this->estado_proyecto = $estado_proyecto;
        return $this;
    }
    public function getGrupoInvestigacion()
    {
        return $this->grupo_investigacion;
    }
    public function setGrupoInvestigacion($grupo_investigacion)
    {
        $this->grupo_investigacion = $grupo_investigacion;
        return $this;
    }
    public function getDirector()
    {
        return $this->director;
    }
    public function setDirector($director)
    {
        $this->director = $director;
        return $this;
    }
    public function getCorreo()
    {
        return $this->correo;
    }
    public function setCorreo($correo)
    {
        $this->correo = $correo;
        return $this;
    }
    public function getFecha()
    {
        return $this->fecha;
    }
    public function setFecha($fecha)
    {
        $this->fecha = $fecha;
        return $this;
    }
    public function getTipoProyecto()
    {
        return $this->tipo_proyecto;
    }
    public function setTipoProyecto($tipo_proyecto)
    {
        $this->tipo_proyecto = $tipo_proyecto;
        return $this;
    }
    public function getEnlace()
    {
        return $this->enlace;
    }
    public function setEnlace($enlace)
    {
        $this->enlace = $enlace;
        return $this;
    }
    public function getEstado()
    {
        return $this->estado;
    }
    public function setEstado($estado)
    {
        $this->estado = $estado;
        return $this;
    }
    public function getRegistradopor()
    {
        return $this->registradopor;
    }
    public function setRegistradopor($registradopor)
    {
        $this->registradopor = $registradopor;
        return $this;
    }
    public function getModificadopor()
    {
        return $this->modificadopor;
    }
    public function setModificadopor($modificadopor)
    {
        $this->modificadopor = $modificadopor;
        return $this;
    }
    public function getFechahorareg()
    {
        return $this->fechahorareg;
    }
    public function setFechahorareg($fechahorareg)
    {
        $this->fechahorareg = $fechahorareg;
        return $this;
    }
    public function getFechahoramod()
    {
        return $this->fechahoramod;
    }
    public function setFechahoramod($fechahoramod)
    {
        $this->fechahoramod = $fechahoramod;
        return $this;
    }
    public function getConvocatorias()
    {
        return $this->convocatorias;
    }
    public function setConvocatorias($convocatorias)
    {
        if (is_string($convocatorias)) {
            $convocatorias = explode(',', $convocatorias);
        }
        $this->convocatorias = array_filter(array_map('intval', (array)$convocatorias));
        return $this;
    }
}
