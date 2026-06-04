<?php
// module/Formularios/src/Formularios/ConvocatoriaForm.php

namespace Formularios\Formularios;

use Laminas\Form\Form;
use Laminas\Form\Element;

class ConvocatoriaForm extends Form
{
    private $configuraciones = [];

    public function __construct($accion = '', $configuraciones = [])
    {
        $this->configuraciones = $configuraciones;

        switch ($accion) {
            case 'registrar':
                $onsubmit = 'return validarGuardar(event, this,"REGISTRAR")';
                $required = true;
                $disabled = false;
                break;
            case 'editar':
                $onsubmit = 'return validarGuardar(event, this,"EDITAR")';
                $required = true;
                $disabled = false;
                break;
            case 'detalle':
                $onsubmit = '';
                $required = false;
                $disabled = true;
                break;
            case 'eliminar':
                $onsubmit = 'return validarGuardar(event, this,"ELIMINAR")';
                $required = false;
                $disabled = true;
                break;
            case 'activar':
                $onsubmit = 'return validarGuardar(event, this,"ACTIVAR")';
                $required = false;
                $disabled = true;
                break;
            case 'desactivar':
                $onsubmit = 'return validarGuardar(event, this,"DESACTIVAR")';
                $required = false;
                $disabled = true;
                break;
            default:
                $onsubmit = '';
                $required = false;
                $disabled = false;
                break;
        }

        parent::__construct('formConvocatoria');
        $this->setAttribute('method', 'post');
        $this->setAttribute('data-toggle', 'validator');
        $this->setAttribute('role', 'form');
        $this->setAttribute('enctype', 'multipart/form-data');
        $this->setAttribute('action', $accion);
        $this->setAttribute('onsubmit', $onsubmit);

        // Select de formulario config
        $this->add([
            'type' => Element\Select::class,
            'name' => 'id_config',
            'options' => [
                'label' => 'Formulario PSI *',
                'value_options' => $this->configuraciones,
                'empty_option' => '-- Seleccione --',
            ],
            'attributes' => [
                'required' => $required,
                'disabled' => $disabled,
                'class' => 'form-control select2',
                'id' => 'id_config',
            ],
        ]);

        $this->add([
            'type' => Element\Text::class,
            'name' => 'nombre_convocatoria',
            'options' => [
                'label' => 'Nombre Convocatoria *',
            ],
            'attributes' => [
                'readonly' => !$required,
                'required' => $required,
                'class' => 'form-control',
                'id' => 'nombre_convocatoria',
                'placeholder' => 'Ej: PSI Inglés - Convocatoria Junio 2026',
            ],
        ]);

        $this->add([
            'type' => Element\Text::class,
            'name' => 'periodo',
            'options' => [
                'label' => 'Periodo *',
            ],
            'attributes' => [
                'readonly' => !$required,
                'required' => $required,
                'class' => 'form-control',
                'id' => 'periodo',
                'placeholder' => 'Ej: 2026-01, 2026-06, 2027-01',
            ],
        ]);

        $this->add([
            'type' => Element\Number::class,
            'name' => 'cupo_maximo',
            'options' => [
                'label' => 'Cupo Máximo *',
            ],
            'attributes' => [
                'readonly' => !$required,
                'required' => $required,
                'class' => 'form-control',
                'id' => 'cupo_maximo',
                'min' => 0,
            ],
        ]);

        $this->add([
            'type' => Element\DateTimeLocal::class,
            'name' => 'fecha_inicio',
            'options' => [
                'label' => 'Fecha Inicio *',
                'format' => 'Y-m-d\TH:i',
            ],
            'attributes' => [
                'class' => 'form-control',
                'readonly' => !$required,
                'required' => $required,
                'id' => 'fecha_inicio',
            ],
        ]);

        $this->add([
            'type' => Element\DateTimeLocal::class,
            'name' => 'fecha_fin',
            'options' => [
                'label' => 'Fecha Fin *',
                'format' => 'Y-m-d\TH:i',
            ],
            'attributes' => [
                'class' => 'form-control',
                'readonly' => !$required,
                'required' => $required,
                'id' => 'fecha_fin',
            ],
        ]);

        $this->add([
            'type' => Element\Time::class,
            'name' => 'hora_limite_diaria',
            'options' => [
                'label' => 'Hora Límite Diaria',
                'format' => 'H:i',
            ],
            'attributes' => [
                'readonly' => !$required,
                'class' => 'form-control',
                'id' => 'hora_limite_diaria',
            ],
        ]);

        // IMPORTANTE: Estos campos NO deben ser requeridos en el formulario
        $this->add([
            'type' => Element\Hidden::class,  // Cambiado a Hidden
            'name' => 'inscritos_actuales',
            'attributes' => [
                'id' => 'inscritos_actuales',
            ],
        ]);

        $this->add([
            'type' => Element\Hidden::class,  // Cambiado a Hidden
            'name' => 'id_convocatoria',
            'attributes' => [
                'id' => 'id_convocatoria',
            ],
        ]);

        // Campo activo como hidden
        $this->add([
            'type' => Element\Hidden::class,
            'name' => 'activo',
            'attributes' => [
                'id' => 'activo',
                'value' => '1',
            ],
        ]);

        // Campos readonly para visualización (opcionales)
        $this->add([
            'type' => Element\Text::class,
            'name' => 'activo_texto',
            'options' => ['label' => 'Estado'],
            'attributes' => [
                'readonly' => true,
                'class' => 'form-control',
                'id' => 'activo_texto',
            ],
        ]);

        $this->add([
            'type' => Element\Text::class,
            'name' => 'created_at',
            'options' => ['label' => 'Fecha Registro'],
            'attributes' => [
                'readonly' => true,
                'class' => 'form-control',
                'id' => 'created_at',
            ],
        ]);

        $this->add([
            'type' => Element\Text::class,
            'name' => 'updated_at',
            'options' => ['label' => 'Fecha Actualización'],
            'attributes' => [
                'readonly' => true,
                'class' => 'form-control',
                'id' => 'updated_at',
            ],
        ]);

        // Configurar el InputFilter para quitar la validación requerida
        $this->setInputFilter($this->getFormInputFilter());
    }

    // Agregar este método para definir el filtro de entrada del formulario
    private function getFormInputFilter()
    {
        $inputFilter = new \Laminas\InputFilter\InputFilter();

        // Campos requeridos
        $inputFilter->add([
            'name' => 'id_config',
            'required' => true,
        ]);

        $inputFilter->add([
            'name' => 'nombre_convocatoria',
            'required' => true,
        ]);

        $inputFilter->add([
            'name' => 'periodo',
            'required' => true,
        ]);

        $inputFilter->add([
            'name' => 'cupo_maximo',
            'required' => true,
        ]);

        $inputFilter->add([
            'name' => 'fecha_inicio',
            'required' => true,
        ]);

        $inputFilter->add([
            'name' => 'fecha_fin',
            'required' => true,
        ]);

        // Campos NO requeridos
        $inputFilter->add([
            'name' => 'hora_limite_diaria',
            'required' => false,
            'allow_empty' => true,
        ]);

        $inputFilter->add([
            'name' => 'inscritos_actuales',
            'required' => false,
            'allow_empty' => true,
        ]);

        $inputFilter->add([
            'name' => 'id_convocatoria',
            'required' => false,
            'allow_empty' => true,
        ]);

        $inputFilter->add([
            'name' => 'activo',
            'required' => false,
        ]);

        $inputFilter->add([
            'name' => 'activo_texto',
            'required' => false,
        ]);

        $inputFilter->add([
            'name' => 'created_at',
            'required' => false,
        ]);

        $inputFilter->add([
            'name' => 'updated_at',
            'required' => false,
        ]);

        return $inputFilter;
    }
}
