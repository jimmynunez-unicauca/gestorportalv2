<?php

namespace Administracion\Modelo\Entidades;

use DomainException;
use Laminas\Filter\StringTrim;
use Laminas\Filter\StripTags;
use Laminas\Filter\ToInt;
use Laminas\InputFilter\InputFilter;
use Laminas\InputFilter\InputFilterAwareInterface;
use Laminas\InputFilter\InputFilterInterface;
use Laminas\Validator\StringLength;

class Vriconvocatorias implements InputFilterAwareInterface
{
    private $id;
    private $nombre;
    private $titulo;
    private $fecha;
    private $documento;
    private $enlace;
    private $estado;
    private $registradopor;
    private $modificadopor;
    private $fechahorareg;
    private $fechahoramod;

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
        throw new DomainException(sprintf('%s does not allow injection of an alternate input filter', __CLASS__));
    }

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
            ],
            'validators' => [
                ['name' => StringLength::class, 'options' => ['encoding' => 'UTF-8', 'max' => 200]],
            ],
        ]);
        $inputFilter->add([
            'name' => 'titulo',
            'required' => true,
            'filters' => [
                ['name' => StripTags::class],
                ['name' => StringTrim::class],
            ],
            'validators' => [
                ['name' => StringLength::class, 'options' => ['encoding' => 'UTF-8', 'max' => 300]],
            ],
        ]);
        $inputFilter->add([
            'name' => 'fecha',
            'required' => true,
            'filters' => [['name' => StringTrim::class]],
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
    public function getNombre()
    {
        return $this->nombre;
    }
    public function setNombre($nombre)
    {
        $this->nombre = $nombre;
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
    public function getFecha()
    {
        return $this->fecha;
    }
    public function setFecha($fecha)
    {
        $this->fecha = $fecha;
        return $this;
    }
    public function getDocumento()
    {
        return $this->documento;
    }
    public function setDocumento($documento)
    {
        $this->documento = $documento;
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
}
