<?php

namespace Administracion\Modelo\Entidades;

use DomainException;
use Laminas\Filter\StringTrim;
use Laminas\Filter\StripTags;
use Laminas\InputFilter\InputFilter;
use Laminas\InputFilter\InputFilterAwareInterface;
use Laminas\InputFilter\InputFilterInterface;
use Laminas\Validator\StringLength;

class Podcast implements InputFilterAwareInterface
{
    private $idPodcast;
    private $titulo;
    private $descripcion;
    private $imagen;
    private $audio_url;
    private $categoria;
    private $transmision;
    private $idPrograma;
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
        $f = new InputFilter();

        $f->add([
            'name' => 'titulo',
            'required' => true,
            'filters' => [['name' => StripTags::class], ['name' => StringTrim::class]],
            'validators' => [[
                'name' => StringLength::class,
                'options' => ['encoding' => 'UTF-8', 'max' => 100],
            ]],
        ]);
        $f->add([
            'name' => 'descripcion',
            'required' => true,
            'filters' => [['name' => StripTags::class], ['name' => StringTrim::class]],
        ]);
        $f->add(['name' => 'idPrograma',  'required' => true]);
        $f->add(['name' => 'transmision', 'required' => true]);
        $f->add(['name' => 'audio_url',   'required' => true]);
        $f->add(['name' => 'categoria',   'required' => false]);
        $f->add(['name' => 'imagen',      'required' => false]);

        $this->inputFilter = $f;
        return $this->inputFilter;
    }

    public function getIdPodcast()
    {
        return $this->idPodcast;
    }
    public function setIdPodcast($v)
    {
        $this->idPodcast = $v;
    }

    public function getTitulo()
    {
        return $this->titulo;
    }
    public function setTitulo($v)
    {
        $this->titulo = $v;
    }

    public function getDescripcion()
    {
        return $this->descripcion;
    }
    public function setDescripcion($v)
    {
        $this->descripcion = $v;
    }

    public function getImagen()
    {
        return $this->imagen;
    }
    public function setImagen($v)
    {
        $this->imagen = $v;
    }

    public function getAudio_url()
    {
        return $this->audio_url;
    }
    public function setAudio_url($v)
    {
        $this->audio_url = $v;
    }

    public function getCategoria()
    {
        return $this->categoria;
    }
    public function setCategoria($v)
    {
        $this->categoria = $v;
    }

    public function getTransmision()
    {
        return $this->transmision;
    }
    public function setTransmision($v)
    {
        $this->transmision = $v;
    }

    public function getIdPrograma()
    {
        return $this->idPrograma;
    }
    public function setIdPrograma($v)
    {
        $this->idPrograma = $v;
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
