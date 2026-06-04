<?php

namespace Administracion\Modelo\Entidades;

use DomainException;
use Laminas\Filter\StringTrim;
use Laminas\Filter\StripTags;
use Laminas\Filter\ToInt;
use Laminas\Filter\StringToUpper;
use Laminas\Filter\StringToLower;
use Laminas\Form\Element\DateTimeLocal;
use Laminas\InputFilter\InputFilter;
use Laminas\InputFilter\InputFilterAwareInterface;
use Laminas\InputFilter\InputFilterInterface;
use Laminas\Validator\StringLength;
use Laminas\Validator\EmailAddress;
use Laminas\Validator\Date;
use Laminas\Validator\Digits;
use Laminas\Validator\GreaterThan;
use Laminas\Validator\LessThan;
use Laminas\Validator\InArray;

class Rectoria implements InputFilterAwareInterface
{

    private $idRectoria;
    private $nombre;
    private $descripcion;
    private $dirigido;
    private $tipoInforme;
    private $tipo;
    private $formato;
    private $publicacion;
    private $tipoFecha;
    private $fecha;
    private $archivo;
    private $estado;
    private $registradopor;
    private $modificadopor;
    private $fechahorareg;
    private $fechahoramod;
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
            'name' => 'nombre',
            'required' => true,
            'filters' => [
                ['name' => StripTags::class],
                ['name' => StringTrim::class],
                /*   ['name' => StringToUpper::class], */
            ],
            'validators' => [
                [
                    'name' => StringLength::class,
                    'options' => [
                        'encoding' => 'UTF-8',
                        /*  'max' => 100, */
                    ],
                ],
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
            'validators' => [
                [
                    'name' => StringLength::class,
                    'options' => [
                        'encoding' => 'UTF-8',
                        /*  'max' => 200, */
                    ],
                ],
            ],
        ]);
        $inputFilter->add([
            'name' => 'tipoInforme',
            'required' => false,
            'validators' => [
                [
                    'name' => InArray::class,
                    'options' => [
                        'haystack' => [
                            'Plan anticorrupción y atención al ciudadano',
                            'Seguimiento al Sistema PQRSF',
                            'Evaluación independiente del sistema de control interno',
                            'Seguimiento eKogui',
                            'Otros'
                        ],
                        'messages' => [
                            InArray::NOT_IN_ARRAY => 'Selecciona una opción válida para tipo de informe.',
                        ],
                    ],
                ],
            ],
        ]);
        $inputFilter->add([
            'name' => 'tipoFecha',
            'required' => false,
            'validators' => [
                [
                    'name' => InArray::class,
                    'options' => [
                        'haystack' => ['Suscripción', 'Corte'],
                        'messages' => [
                            InArray::NOT_IN_ARRAY => 'Selecciona una opción válida para tipo de informe.',
                        ],
                    ],
                ],
            ],
        ]);
        $inputFilter->add([
            'name' => 'fecha',
            'required' => false,
            'validators' => [
                [
                    'name' => Date::class,
                    'options' => [
                        'format' => 'Y-m-d',
                    ],
                ],
            ],
        ]);

        $this->inputFilter = $inputFilter;
        return $this->inputFilter;
    }

    //------------------------------------------------------------------------------

    public function getIdRectoria()
    {
        return $this->idRectoria;
    }

    public function setIdRectoria($value)
    {
        $this->idRectoria = $value;
    }

    public function getNombre()
    {
        return $this->nombre;
    }

    public function setNombre($value)
    {
        $this->nombre = $value;
    }

    public function getDescripcion()
    {
        return $this->descripcion;
    }

    public function setDescripcion($value)
    {
        $this->descripcion = $value;
    }

    public function getDirigido()
    {
        return $this->dirigido;
    }

    public function setDirigido($value)
    {
        $this->dirigido = $value;
    }

    public function getTipoInforme()
    {
        return $this->tipoInforme;
    }

    public function setTipoInforme($value)
    {
        $this->tipoInforme = $value;
    }

    public function getTipo()
    {
        return $this->tipo;
    }

    public function setTipo($value)
    {
        $this->tipo = $value;
    }

    public function getFormato()
    {
        return $this->formato;
    }

    public function setFormato($value)
    {
        $this->formato = $value;
    }

    public function getPublicacion()
    {
        return $this->publicacion;
    }

    public function setPublicacion($value)
    {
        $this->publicacion = $value;
    }

    public function getTipoFecha()
    {
        return $this->tipoFecha;
    }

    public function setTipoFecha($value)
    {
        $this->tipoFecha = $value;
    }

    public function getFecha()
    {
        return $this->fecha;
    }

    public function setFecha($value)
    {
        $this->fecha = $value;
    }

    public function getArchivo()
    {
        return $this->archivo;
    }

    public function setArchivo($value)
    {
        $this->archivo = $value;
    }

    public function getEstado()
    {
        return $this->estado;
    }

    public function setEstado($value)
    {
        $this->estado = $value;
    }

    public function getRegistradopor()
    {
        return $this->registradopor;
    }

    public function setRegistradopor($value)
    {
        $this->registradopor = $value;
    }

    public function getModificadopor()
    {
        return $this->modificadopor;
    }

    public function setModificadopor($value)
    {
        $this->modificadopor = $value;
    }

    public function getFechahorareg()
    {
        return $this->fechahorareg;
    }

    public function setFechahorareg($value)
    {
        $this->fechahorareg = $value;
    }

    public function getFechahoramod()
    {
        return $this->fechahoramod;
    }

    public function setFechahoramod($value)
    {
        $this->fechahoramod = $value;
    }
}
