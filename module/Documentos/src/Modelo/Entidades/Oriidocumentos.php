<?php

namespace Documentos\Modelo\Entidades;

use DomainException;
use Laminas\Filter\StringTrim;
use Laminas\Filter\StripTags;
use Laminas\InputFilter\InputFilter;
use Laminas\InputFilter\InputFilterAwareInterface;
use Laminas\InputFilter\InputFilterInterface;
use Laminas\Validator\StringLength;

class Oriidocumentos implements InputFilterAwareInterface
{
    private $id_documentos_orii;
    private $idOrii;
    private $nombre_documento;
    private $documento = '';
    private $estado_documento;
    private $registradopor_documento;
    private $fechahorareg_documento;
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

        // ✅ SOLO nombre_documento. Nada de 'documento': lo valida procesarArchivo()
        $inputFilter->add([
            'name' => 'nombre_documento',
            'required' => true,
            'filters' => [
                ['name' => StripTags::class],
                ['name' => StringTrim::class],
            ],
            'validators' => [
                ['name' => StringLength::class, 'options' => ['encoding' => 'UTF-8', 'max' => 500]],
            ],
        ]);

        $this->inputFilter = $inputFilter;
        return $this->inputFilter;
    }

    // ---- getters/setters ----
    public function getId_documentos_orii()
    {
        return $this->id_documentos_orii;
    }
    public function setId_documentos_orii($v)
    {
        $this->id_documentos_orii = $v;
    }

    public function getIdOrii()
    {
        return $this->idOrii;
    }
    public function setIdOrii($v)
    {
        $this->idOrii = $v;
    }

    public function getNombre_documento()
    {
        return $this->nombre_documento;
    }
    public function setNombre_documento($v)
    {
        $this->nombre_documento = $v;
    }

    public function getDocumento()
    {
        return $this->documento;
    }
    public function setDocumento($v)
    {
        $this->documento = $v;
    }

    public function getEstado_documento()
    {
        return $this->estado_documento;
    }
    public function setEstado_documento($v)
    {
        $this->estado_documento = $v;
    }

    public function getRegistradopor_documento()
    {
        return $this->registradopor_documento;
    }
    public function setRegistradopor_documento($v)
    {
        $this->registradopor_documento = $v;
    }

    public function getFechahorareg_documento()
    {
        return $this->fechahorareg_documento;
    }
    public function setFechahorareg_documento($v)
    {
        $this->fechahorareg_documento = $v;
    }
}
