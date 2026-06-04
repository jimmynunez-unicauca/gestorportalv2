<?php

namespace Administracion\Modelo\Entidades;

use DomainException;
use Laminas\Filter\StringTrim;
use Laminas\Filter\StripTags;
use Laminas\InputFilter\InputFilter;
use Laminas\InputFilter\InputFilterAwareInterface;
use Laminas\InputFilter\InputFilterInterface;
use Laminas\Validator\StringLength;
use Laminas\Validator\InArray;

class Organigrama implements InputFilterAwareInterface
{
    private $id;
    private $nombre;
    private $tipo;
    private $padre_id;
    private $orden;
    private $activo;
    private $created_at;
    private $updated_at;
    private $icono;
    private $descripcion;
    private $registradopor;
    private $modificadopor;
    private $estado;  // Para compatibilidad con el controlador
    private $num_hijos;
    private $nombre_padre;
    private $color;

    private $inputFilter;

    public function __construct(array $datos = null)
    {
        if (is_array($datos)) {
            $this->exchangeArray($datos);
        }
    }

    public function exchangeArray($data)
    {
        $this->id = $data['id'] ?? null;
        $this->nombre = $data['nombre'] ?? null;
        $this->tipo = $data['tipo'] ?? null;
        $this->padre_id = $data['padre_id'] ?? null;
        $this->orden = $data['orden'] ?? 0;
        $this->activo = $data['activo'] ?? 1;
        $this->created_at = $data['created_at'] ?? null;
        $this->updated_at = $data['updated_at'] ?? null;
        $this->icono = $data['icono'] ?? null;
        $this->descripcion = $data['descripcion'] ?? $data['metadata_descripcion'] ?? null;
        $this->registradopor = $data['registradopor'] ?? null;
        $this->modificadopor = $data['modificadopor'] ?? null;
        $this->estado = ($this->activo == 1) ? 'Activo' : 'Inactivo';
        $this->color = $data['color'] ?? null;
    }

    public function getArrayCopy()
    {
        return [
            'id' => $this->id,
            'nombre' => $this->nombre,
            'tipo' => $this->tipo,
            'padre_id' => $this->padre_id,
            'orden' => $this->orden,
            'activo' => $this->activo,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'icono' => $this->icono,
            'descripcion' => $this->descripcion,
            'registradopor' => $this->registradopor,
            'modificadopor' => $this->modificadopor,
            'estado' => $this->estado,
            'color' => $this->color,
        ];
    }

    public function setInputFilter(InputFilterInterface $inputFilter)
    {
        throw new DomainException(__CLASS__ . ' does not allow injection of an alternate input filter');
    }

    public function getInputFilter()
    {
        if ($this->inputFilter) {
            return $this->inputFilter;
        }

        $inputFilter = new InputFilter();

        // Validación para 'nombre'
        $inputFilter->add([
            'name' => 'nombre',
            'required' => true,
            'filters' => [
                ['name' => StripTags::class],
                ['name' => StringTrim::class],
            ],
            'validators' => [
                [
                    'name' => StringLength::class,
                    'options' => [
                        'encoding' => 'UTF-8',
                        'min' => 3,
                        'max' => 200,
                    ],
                ],
            ],
        ]);

        // Validación para 'tipo' - ACEPTAR LOS NUEVOS VALORES
        $inputFilter->add([
            'name' => 'tipo',
            'required' => true,
            'allow_empty' => false,
            'validators' => [
                [
                    'name' => InArray::class,
                    'options' => [
                        'haystack' => [
                            'root',
                            'vicerector',
                            'facultad',
                            'division',
                            'depto',
                            'centro',
                            'escuela',
                            'instituto',
                            'conservatorio',
                            'oficina',
                            'secretaria',
                            'coordinacion',
                            'decanatura',
                            'comite',
                            'consejo',
                            'junta',
                            'area',
                            'grupo',
                            'semillero',
                            'sede',
                            'regionalizacion',
                            'programa',
                            'estudios',
                            'posgrado',
                            'maestria',
                            'doctorado',
                            'especializacion',
                            'unidad',
                            'laboratorio',
                            'biblioteca',
                            'archivo',
                            'proyecto',
                            'observatorio',
                            'consultorio',
                            'catedra',
                            'fondo',
                            'red'
                        ],
                    ],
                ],
            ],
        ]);

        // Validación para 'padre_id' (opcional)
        $inputFilter->add([
            'name' => 'padre_id',
            'required' => false,
            'allow_empty' => true,
        ]);

        // Validación para 'descripcion' (opcional)
        $inputFilter->add([
            'name' => 'descripcion',
            'required' => false,
            'allow_empty' => true,
            /* 'filters' => [
                ['name' => StripTags::class],
                ['name' => StringTrim::class],
            ], */
        ]);

        // Validación para 'orden'
        $inputFilter->add([
            'name' => 'orden',
            'required' => false,
            'allow_empty' => true,
        ]);

        $this->inputFilter = $inputFilter;
        return $this->inputFilter;
    }

    // ==================== GETTERS Y SETTERS ====================

    public function getId()
    {
        return $this->id;
    }
    public function setId($value)
    {
        $this->id = $value;
    }

    public function getNombre()
    {
        return $this->nombre;
    }
    public function setNombre($value)
    {
        $this->nombre = $value;
    }

    public function getTipo()
    {
        return $this->tipo;
    }
    public function setTipo($value)
    {
        $this->tipo = $value;
    }

    public function getPadreId()
    {
        return $this->padre_id;
    }
    public function setPadreId($value)
    {
        $this->padre_id = $value;
    }

    public function getOrden()
    {
        return $this->orden;
    }
    public function setOrden($value)
    {
        $this->orden = $value;
    }

    public function getActivo()
    {
        return $this->activo;
    }
    public function setActivo($value)
    {
        $this->activo = $value;
        $this->estado = ($value == 1) ? 'Activo' : 'Inactivo';
    }

    public function getCreatedAt()
    {
        return $this->created_at;
    }
    public function setCreatedAt($value)
    {
        $this->created_at = $value;
    }

    public function getUpdatedAt()
    {
        return $this->updated_at;
    }
    public function setUpdatedAt($value)
    {
        $this->updated_at = $value;
    }

    public function getIcono()
    {
        return $this->icono;
    }
    public function setIcono($value)
    {
        $this->icono = $value;
    }

    public function getDescripcion()
    {
        return $this->descripcion;
    }
    public function setDescripcion($value)
    {
        $this->descripcion = $value;
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

    public function getEstado()
    {
        return $this->estado;
    }
    public function setEstado($value)
    {
        $this->estado = $value;
        if ($value == 'Activo') {
            $this->activo = 1;
        } else {
            $this->activo = 0;
        }
    }

    public function getNumHijos()
    {
        return $this->num_hijos;
    }
    public function setNumHijos($value)
    {
        $this->num_hijos = $value;
    }

    public function getNombrePadre()
    {
        return $this->nombre_padre;
    }
    public function setNombrePadre($value)
    {
        $this->nombre_padre = $value;
    }
    public function getColor()
    {
        return $this->color;
    }
    public function setColor($value)
    {
        $this->color = $value;
    }
}
