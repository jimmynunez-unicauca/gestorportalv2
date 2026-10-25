<?php

namespace Administracion\Modelo\Entidades;

use DomainException;
use Laminas\Filter\StringTrim;
use Laminas\Filter\StripTags;
use Laminas\InputFilter\InputFilter;
use Laminas\InputFilter\InputFilterAwareInterface;
use Laminas\InputFilter\InputFilterInterface;
use Laminas\Validator\StringLength;

class Programa implements InputFilterAwareInterface
{
    private $idPrograma;
    private $nombre_programa;
    private $detalle_programa;
    private $color_programa;
    private $imagen;
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
            'name' => 'nombre_programa',
            'required' => true,
            'filters' => [['name' => StripTags::class], ['name' => StringTrim::class]],
            'validators' => [['name' => StringLength::class, 'options' => ['encoding' => 'UTF-8', 'max' => 100]]],
        ]);

        $inputFilter->add([
            'name' => 'detalle_programa',
            'required' => true,
            'filters' => [['name' => StripTags::class], ['name' => StringTrim::class]],
        ]);

        $this->inputFilter = $inputFilter;
        return $this->inputFilter;
    }

    // getters y setters ...
    public function getIdPrograma()
    {
        return $this->idPrograma;
    }
    public function setIdPrograma($v)
    {
        $this->idPrograma = $v;
    }

    public function getNombre_programa()
    {
        return $this->nombre_programa;
    }
    public function setNombre_programa($v)
    {
        $this->nombre_programa = $v;
    }

    public function getDetalle_programa()
    {
        return $this->detalle_programa;
    }
    public function setDetalle_programa($v)
    {
        $this->detalle_programa = $v;
    }

    public function getColor_programa()
    {
        return $this->color_programa;
    }
    public function setColor_programa($v)
    {
        $this->color_programa = $v;
    }

    public function getImagen()
    {
        return $this->imagen;
    }
    public function setImagen($v)
    {
        $this->imagen = $v;
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
