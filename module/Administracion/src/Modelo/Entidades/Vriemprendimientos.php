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

class Vriemprendimientos implements InputFilterAwareInterface
{

    private $id;
    private $nombre;
    private $detalle;
    private $tipoEmprendimiento;
    private $semillero;
    private $productoServicio;
    private $historia;
    private $whatsapp;
    private $imagen;
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
                        'max' => 100,
                    ],
                ],
            ],
        ]);
        $inputFilter->add([
            'name' => 'detalle',
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
    // GETTERS Y SETTERS
    //------------------------------------------------------------------------------

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

    public function getDetalle()
    {
        return $this->detalle;
    }
    public function setDetalle($detalle)
    {
        $this->detalle = $detalle;
        return $this;
    }

    public function getTipoEmprendimiento()
    {
        return $this->tipoEmprendimiento;
    }
    public function setTipoEmprendimiento($tipoEmprendimiento)
    {
        $this->tipoEmprendimiento = $tipoEmprendimiento;
        return $this;
    }

    public function getSemillero()
    {
        return $this->semillero;
    }
    public function setSemillero($semillero)
    {
        $this->semillero = $semillero;
        return $this;
    }

    public function getProductoServicio()
    {
        return $this->productoServicio;
    }
    public function setProductoServicio($productoServicio)
    {
        $this->productoServicio = $productoServicio;
        return $this;
    }

    public function getHistoria()
    {
        return $this->historia;
    }
    public function setHistoria($historia)
    {
        $this->historia = $historia;
        return $this;
    }

    public function getWhatsapp()
    {
        return $this->whatsapp;
    }
    public function setWhatsapp($whatsapp)
    {
        $this->whatsapp = $whatsapp;
        return $this;
    }

    public function getImagen()
    {
        return $this->imagen;
    }
    public function setImagen($imagen)
    {
        $this->imagen = $imagen;
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
