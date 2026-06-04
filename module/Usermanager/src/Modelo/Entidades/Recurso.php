<?php

namespace Usermanager\Modelo\Entidades;

use DomainException;
use Laminas\Filter\StringTrim;
use Laminas\Filter\StripTags;
use Laminas\Filter\ToInt;
use Laminas\Filter\StringToUpper;
use Laminas\Filter\StringToLower;
use Laminas\InputFilter\InputFilter;
use Laminas\InputFilter\InputFilterAwareInterface;
use Laminas\InputFilter\InputFilterInterface;
use Laminas\Validator\StringLength;
use Laminas\Validator\EmailAddress;
use Laminas\Validator\Date;
use Laminas\Validator\Digits;
use Laminas\Validator\GreaterThan;
use Laminas\Validator\LessThan;

class Recurso implements InputFilterAwareInterface
{

    private $idRecurso;
    private $recurso;
    private $metodo;
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

        $this->inputFilter = $inputFilter;
        return $this->inputFilter;
    }

    //------------------------------------------------------------------------------

    /**
     * Get the value of idRecurso
     */
    public function getIdRecurso()
    {
        return $this->idRecurso;
    }

    /**
     * Set the value of idRecurso
     *
     * @return  self
     */
    public function setIdRecurso($idRecurso)
    {
        $this->idRecurso = $idRecurso;

        return $this;
    }

    /**
     * Get the value of recurso
     */
    public function getRecurso()
    {
        return $this->recurso;
    }

    /**
     * Set the value of recurso
     *
     * @return  self
     */
    public function setRecurso($recurso)
    {
        $this->recurso = $recurso;

        return $this;
    }

    /**
     * Get the value of metodo
     */
    public function getMetodo()
    {
        return $this->metodo;
    }

    /**
     * Set the value of metodo
     *
     * @return  self
     */
    public function setMetodo($metodo)
    {
        $this->metodo = $metodo;

        return $this;
    }

    /**
     * Get the value of estado
     */
    public function getEstado()
    {
        return $this->estado;
    }

    /**
     * Set the value of estado
     *
     * @return  self
     */
    public function setEstado($estado)
    {
        $this->estado = $estado;

        return $this;
    }

    /**
     * Get the value of registradopor
     */
    public function getRegistradopor()
    {
        return $this->registradopor;
    }

    /**
     * Set the value of registradopor
     *
     * @return  self
     */
    public function setRegistradopor($registradopor)
    {
        $this->registradopor = $registradopor;

        return $this;
    }

    /**
     * Get the value of modificadopor
     */
    public function getModificadopor()
    {
        return $this->modificadopor;
    }

    /**
     * Set the value of modificadopor
     *
     * @return  self
     */
    public function setModificadopor($modificadopor)
    {
        $this->modificadopor = $modificadopor;

        return $this;
    }

    /**
     * Get the value of fechahorareg
     */
    public function getFechahorareg()
    {
        return $this->fechahorareg;
    }

    /**
     * Set the value of fechahorareg
     *
     * @return  self
     */
    public function setFechahorareg($fechahorareg)
    {
        $this->fechahorareg = $fechahorareg;

        return $this;
    }

    /**
     * Get the value of fechahoramod
     */
    public function getFechahoramod()
    {
        return $this->fechahoramod;
    }

    /**
     * Set the value of fechahoramod
     *
     * @return  self
     */
    public function setFechahoramod($fechahoramod)
    {
        $this->fechahoramod = $fechahoramod;

        return $this;
    }
}
