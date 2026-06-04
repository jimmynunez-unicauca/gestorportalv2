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

class Archivo implements InputFilterAwareInterface
{

    private $idArchivo;
    private $idResolucion;
    private $idEmitido;
    private $nombre;
    private $descripcion;
    private $tipo;
    private $inicioConvocatoria;
    private $finConvocatoria;
    private $publicacion;
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


        $this->inputFilter = $inputFilter;
        return $this->inputFilter;
    }

    //------------------------------------------------------------------------------




    /**
     * Get the value of idArchivo
     */
    public function getIdArchivo()
    {
        return $this->idArchivo;
    }

    /**
     * Set the value of idArchivo
     *
     * @return  self
     */
    public function setIdArchivo($idArchivo)
    {
        $this->idArchivo = $idArchivo;

        return $this;
    }

    /**
     * Get the value of idResolucion
     */
    public function getIdResolucion()
    {
        return $this->idResolucion;
    }

    /**
     * Set the value of idResolucion
     *
     * @return  self
     */
    public function setIdResolucion($idResolucion)
    {
        $this->idResolucion = $idResolucion;

        return $this;
    }

    /**
     * Get the value of nombre
     */
    public function getNombre()
    {
        return $this->nombre;
    }

    /**
     * Set the value of nombre
     *
     * @return  self
     */
    public function setNombre($nombre)
    {
        $this->nombre = $nombre;

        return $this;
    }

    /**
     * Get the value of descripcion
     */
    public function getDescripcion()
    {
        return $this->descripcion;
    }

    /**
     * Set the value of descripcion
     *
     * @return  self
     */
    public function setDescripcion($descripcion)
    {
        $this->descripcion = $descripcion;

        return $this;
    }

    /**
     * Get the value of archivo
     */
    public function getArchivo()
    {
        return $this->archivo;
    }

    /**
     * Set the value of archivo
     *
     * @return  self
     */
    public function setArchivo($archivo)
    {
        $this->archivo = $archivo;

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

    /**
     * Get the value of tipo
     */
    public function getTipo()
    {
        return $this->tipo;
    }

    /**
     * Set the value of tipo
     *
     * @return  self
     */
    public function setTipo($tipo)
    {
        $this->tipo = $tipo;

        return $this;
    }

    /**
     * Get the value of publicacion
     */
    public function getPublicacion()
    {
        return $this->publicacion;
    }

    /**
     * Set the value of publicacion
     *
     * @return  self
     */
    public function setPublicacion($publicacion)
    {
        $this->publicacion = $publicacion;

        return $this;
    }

    /**
     * Get the value of inicioConvocatoria
     */
    public function getInicioConvocatoria()
    {
        return $this->inicioConvocatoria;
    }

    /**
     * Set the value of inicioConvocatoria
     *
     * @return  self
     */
    public function setInicioConvocatoria($inicioConvocatoria)
    {
        $this->inicioConvocatoria = $inicioConvocatoria;

        return $this;
    }

    /**
     * Get the value of finConvocatoria
     */
    public function getFinConvocatoria()
    {
        return $this->finConvocatoria;
    }

    /**
     * Set the value of finConvocatoria
     *
     * @return  self
     */
    public function setFinConvocatoria($finConvocatoria)
    {
        $this->finConvocatoria = $finConvocatoria;

        return $this;
    }

    /**
     * Get the value of idEmitido
     */
    public function getIdEmitido()
    {
        return $this->idEmitido;
    }

    /**
     * Set the value of idEmitido
     *
     * @return  self
     */
    public function setIdEmitido($idEmitido)
    {
        $this->idEmitido = $idEmitido;

        return $this;
    }
}
