<?php

namespace Administracion\Formularios;

use Laminas\Form\Form;
use Laminas\Form\Element;

class ExcepcionForm extends Form
{
    public function __construct($accion = '')
    {
        switch ($accion) {
            case 'registrar':
                $onsubmit = 'return validarGuardarExcepcion(event, this,"REGISTRAR")';
                $required = true;
                $disabled = false;
                break;
            case 'editar':
                $onsubmit = 'return validarGuardarExcepcion(event, this,"EDITAR")';
                $required = true;
                $disabled = false;
                break;
            case 'detalle':
                $onsubmit = '';
                $required = false;
                $disabled = true;
                break;
            case 'eliminar':
                $onsubmit = 'return validarGuardarExcepcion(event, this,"ELIMINAR")';
                $required = false;
                $disabled = true;
                break;
            default:
                $onsubmit = '';
                $required = false;
                $disabled = false;
                break;
        }

        parent::__construct('formExcepcion');
        $this->setAttribute('method', 'post');
        $this->setAttribute('onsubmit', $onsubmit);

        $this->add([
            'type' => Element\Date::class,
            'name' => 'fecha',
            'options' => ['label' => 'Fecha de la excepción *'],
            'attributes' => [
                'readonly' => !$required,
                'required' => $required,
                'class' => 'form-control',
                'id' => 'fecha',
            ],
        ]);

        $this->add([
            'type' => Element\Select::class,
            'name' => 'tipo',
            'options' => [
                'label' => 'Tipo de excepción *',
                'empty_option' => 'Seleccione...',
                'value_options' => [
                    'cancelacion'     => 'Cancelación (Un programa no se emite)',
                    'cancelacion_dia' => 'Cancelación de DÍA COMPLETO (Festivo)',
                    'reemplazo'       => 'Reemplazo (Otro programa se emite)',
                    'ajuste_horario'  => 'Ajuste de horario (Mismo programa, otra hora)',
                ],
            ],
            'attributes' => [
                'readonly' => !$required,
                'required' => $required,
                'class' => 'form-control',
                'id' => 'tipo',
                'onchange' => 'toggleCamposExcepcion()',
            ],
        ]);

        $this->add([
            'type' => Element\Select::class,
            'name' => 'idProgramacion_original',
            'options' => [
                'label' => 'Programación afectada (Cancelación / Ajuste)',
                'empty_option' => 'Seleccione...',
                'disable_inarray_validator' => true,
            ],
            'attributes' => [
                'readonly' => !$required,
                'class' => 'form-control',
                'id' => 'idProgramacion_original',
            ],
        ]);

        $this->add([
            'type' => Element\Select::class,
            'name' => 'idPrograma_reemplazo',
            'options' => [
                'label' => 'Programa que reemplaza',
                'empty_option' => 'Seleccione...',
                'disable_inarray_validator' => true,
            ],
            'attributes' => [
                'readonly' => !$required,
                'class' => 'form-control',
                'id' => 'idPrograma_reemplazo',
            ],
        ]);

        $this->add([
            'type' => Element\Time::class,
            'name' => 'hora_inicio',
            'options' => ['label' => 'Hora de Inicio (Reemplazo / Ajuste)'],
            'attributes' => [
                'readonly' => !$required,
                'class' => 'form-control',
                'id' => 'hora_inicio',
                'step' => '1',
            ],
        ]);

        $this->add([
            'type' => Element\Time::class,
            'name' => 'hora_fin',
            'options' => ['label' => 'Hora de Fin (Reemplazo / Ajuste)'],
            'attributes' => [
                'readonly' => !$required,
                'class' => 'form-control',
                'id' => 'hora_fin',
                'step' => '1',
            ],
        ]);

        $this->add([
            'type' => Element\Textarea::class,
            'name' => 'motivo',
            'options' => ['label' => 'Motivo *'],
            'attributes' => [
                'readonly' => !$required,
                'required' => $required,
                'class' => 'form-control',
                'id' => 'motivo',
                'maxlength' => 150,
            ],
        ]);

        // Auditoría
        $this->add([
            'type' => Element\Number::class,
            'name' => 'idExcepcion',
            'attributes' => ['readonly' => true, 'class' => 'form-control', 'id' => 'idExcepcion']
        ]);
        $this->add([
            'type' => Element\Text::class,
            'name' => 'registradopor',
            'attributes' => ['readonly' => true, 'class' => 'form-control', 'id' => 'registradopor']
        ]);
        $this->add([
            'type' => Element\Text::class,
            'name' => 'fechahorareg',
            'attributes' => ['readonly' => true, 'class' => 'form-control', 'id' => 'fechahorareg']
        ]);
    }
}
