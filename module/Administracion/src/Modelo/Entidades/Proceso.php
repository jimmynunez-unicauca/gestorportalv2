<?php

namespace Administracion\Modelo\Entidades;

use DomainException;
use Laminas\Filter\ToInt;
use Laminas\InputFilter\InputFilter;
use Laminas\InputFilter\InputFilterAwareInterface;
use Laminas\InputFilter\InputFilterInterface;

class Proceso implements InputFilterAwareInterface
{

    private $idProceso;
    private $idSubproceso;
    private $idTipoProceso;
    private $subproceso;

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
            'name' => 'idProceso',
            'required' => true,
            'filters' => [
                ['name' => ToInt::class],
            ],
        ]);
        $inputFilter->add([
            'name' => 'idSubproceso',
            'required' => true,
            'filters' => [
                ['name' => ToInt::class],
            ],
        ]);
        $inputFilter->add([
            'name' => 'idTipoProceso',
            'required' => true,
            'filters' => [
                ['name' => ToInt::class],
            ],
        ]);


        $this->inputFilter = $inputFilter;
        return $this->inputFilter;
    }

    //------------------------------------------------------------------------------

    public function getIdProceso()
    {
        return $this->idProceso;
    }

    public function setIdProceso($value)
    {
        $this->idProceso = $value;
    }

    public function getIdSubproceso()
    {
        return $this->idSubproceso;
    }

    public function setIdSubproceso($value)
    {
        $this->idSubproceso = $value;
    }

    public function getIdTipoProceso()
    {
        return $this->idTipoProceso;
    }

    public function setIdTipoProceso($value)
    {
        $this->idTipoProceso = $value;
    }

    public function getSubproceso()
    {
        return $this->subproceso;
    }

    public function setSubproceso($value)
    {
        $this->subproceso = $value;
    }
}
