<?php

namespace Documentos\Modelo\Entidades;

use DomainException;
use Laminas\Filter\StringTrim;
use Laminas\Filter\StripTags;
use Laminas\InputFilter\InputFilter;
use Laminas\InputFilter\InputFilterAwareInterface;
use Laminas\InputFilter\InputFilterInterface;
use Laminas\Validator\Date;
use Laminas\Validator\GreaterThan;
use Laminas\Validator\InArray;
use Laminas\Validator\StringLength;

class Orii implements InputFilterAwareInterface
{
    private $idOrii;
    private $idInstituto;
    private $numero_convenio;
    private $nombre_convenio;
    private $descripcion;
    private $publicacion;
    private $estado;
    private $tipo;
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

    public function setInputFilter(InputFilterInterface $inputFilter)
    {
        throw new DomainException('No permite inyección de filtro alterno');
    }

    public function getInputFilter()
    {
        if ($this->inputFilter) return $this->inputFilter;

        $inputFilter = new InputFilter();

        $inputFilter->add([
            'name' => 'idInstituto',
            'required' => true,
            'filters' => [['name' => 'Int']],
            'validators' => [
                ['name' => GreaterThan::class, 'options' => ['min' => 0]],
            ],
        ]);

        $inputFilter->add([
            'name' => 'numero_convenio',
            'required' => true,
            'filters' => [
                ['name' => StripTags::class],
                ['name' => StringTrim::class],
            ],
            'validators' => [
                ['name' => StringLength::class, 'options' => ['encoding' => 'UTF-8', 'max' => 100]],
            ],
        ]);

        $inputFilter->add([
            'name' => 'nombre_convenio',
            'required' => true,
            'filters' => [
                ['name' => StripTags::class],
                ['name' => StringTrim::class],
            ],
            'validators' => [
                ['name' => StringLength::class, 'options' => ['encoding' => 'UTF-8', 'max' => 200]],
            ],
        ]);

        $inputFilter->add([
            'name' => 'descripcion',
            'required' => false,
            'allow_empty' => true,
            'filters' => [
                ['name' => StripTags::class],
                ['name' => StringTrim::class],
            ],
        ]);

        $inputFilter->add([
            'name' => 'publicacion',
            'required' => true,
            'filters' => [['name' => StringTrim::class]],
            'validators' => [
                ['name' => Date::class, 'options' => ['format' => 'Y-m-d']],
            ],
        ]);

        $inputFilter->add([
            'name' => 'tipo',
            'required' => true,
            'validators' => [
                ['name' => InArray::class, 'options' => ['haystack' => ['Marco', 'Específico']]],
            ],
        ]);

        // 🛡️ BLINDAJE: aunque 'estado' venga en el Form o en el POST,
        //    NUNCA será requerido. El controller lo setea por código
        //    (Vigente en registrar, Cancelado/Vigente en desactivar/activar).
        $inputFilter->add([
            'name' => 'estado',
            'required' => false,
            'allow_empty' => true,
        ]);

        // 🛡️ Mismo blindaje para campos de auditoría (por si acaso)
        foreach (['registradopor', 'modificadopor', 'fechahorareg', 'fechahoramod', 'idOrii'] as $campo) {
            $inputFilter->add([
                'name' => $campo,
                'required' => false,
                'allow_empty' => true,
            ]);
        }

        $this->inputFilter = $inputFilter;
        return $this->inputFilter;
    }

    // ---- getters/setters ----
    public function getIdOrii()
    {
        return $this->idOrii;
    }
    public function setIdOrii($v)
    {
        $this->idOrii = $v;
    }

    public function getIdInstituto()
    {
        return $this->idInstituto;
    }
    public function setIdInstituto($v)
    {
        $this->idInstituto = $v;
    }

    public function getNumero_convenio()
    {
        return $this->numero_convenio;
    }
    public function setNumero_convenio($v)
    {
        $this->numero_convenio = $v;
    }

    public function getNombre_convenio()
    {
        return $this->nombre_convenio;
    }
    public function setNombre_convenio($v)
    {
        $this->nombre_convenio = $v;
    }

    public function getDescripcion()
    {
        return $this->descripcion;
    }
    public function setDescripcion($v)
    {
        $this->descripcion = $v;
    }

    public function getPublicacion()
    {
        return $this->publicacion;
    }
    public function setPublicacion($v)
    {
        $this->publicacion = $v;
    }

    public function getEstado()
    {
        return $this->estado;
    }
    public function setEstado($v)
    {
        $this->estado = $v;
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
