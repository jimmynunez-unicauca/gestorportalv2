<?php

namespace Administracion\Formularios;

use Laminas\Form\Form;
use Laminas\Form\Element;

class ProgramacionForm extends Form
{
    public function __construct($accion = '')
    {
        switch ($accion) {
            case 'registrar':
                $onsubmit = 'return validarGuardarProgramacion(event, this,"REGISTRAR")';
                $required = true;
                $disabled = false;
                break;
            case 'editar':
                $onsubmit = 'return validarGuardarProgramacion(event, this,"EDITAR")';
                $required = true;
                $disabled = false;
                break;
            case 'detalle':
                $onsubmit = '';
                $required = false;
                $disabled = true;
                break;
            case 'activar':
                $onsubmit = 'return validarGuardarProgramacion(event, this,"ACTIVAR")';
                $required = false;
                $disabled = true;
                break;
            case 'desactivar':
                $onsubmit = 'return validarGuardarProgramacion(event, this,"DESACTIVAR")';
                $required = false;
                $disabled = true;
                break;
            case 'eliminar':
                $onsubmit = 'return validarGuardarProgramacion(event, this,"ELIMINAR")';
                $required = false;
                $disabled = true;
                break;
            default:
                $onsubmit = '';
                $required = false;
                $disabled = false;
                break;
        }

        parent::__construct('formProgramacion');
        $this->setAttribute('method', 'post');
        $this->setAttribute('data-toggle', 'validator');
        $this->setAttribute('role', 'form');
        $this->setAttribute('action', $accion);
        $this->setAttribute('onsubmit', $onsubmit);

        $this->add([
            'type' => Element\Select::class,
            'name' => 'idPrograma',
            'options' => [
                'label' => 'Programa *',
                'empty_option' => 'Seleccione...',
                'disable_inarray_validator' => true,
            ],
            'attributes' => [
                'readonly' => !$required,
                'required' => $required,
                'class' => 'form-control',
                'id' => 'idPrograma',
            ],
        ]);

        $this->add([
            'type' => Element\Select::class,
            'name' => 'segmento',
            'options' => [
                'label' => 'Segmento *',
                'empty_option' => 'Seleccione...',
                'value_options' => ['madrugada' => 'Madrugada', 'dia' => 'Día'],
                'disable_inarray_validator' => true,
            ],
            'attributes' => [
                'readonly' => !$required,
                'required' => $required,
                'class' => 'form-control',
                'id' => 'segmento',
            ],
        ]);

        $this->add([
            'type' => Element\Time::class,
            'name' => 'hora_inicio',
            'options' => ['label' => 'Hora de Inicio *'],
            'attributes' => [
                'readonly' => !$required,
                'required' => $required,
                'class' => 'form-control',
                'id' => 'hora_inicio',
                'step' => '1',
                'placeholder' => 'HH:MM',
            ],
        ]);

        $this->add([
            'type' => Element\Time::class,
            'name' => 'hora_fin',
            'options' => ['label' => 'Hora de Fin *'],
            'attributes' => [
                'readonly' => !$required,
                'required' => $required,
                'class' => 'form-control',
                'id' => 'hora_fin',
                'step' => '1',
                'placeholder' => 'HH:MM',
            ],
        ]);

        $this->add([
            'type' => Element\MultiCheckbox::class,
            'name' => 'dias',
            'options' => [
                'label' => 'Días de transmisión *',
                'value_options' => [
                    'lunes' => 'Lunes',
                    'martes' => 'Martes',
                    'miércoles' => 'Miércoles',
                    'jueves' => 'Jueves',
                    'viernes' => 'Viernes',
                    'sábado' => 'Sábado',
                    'domingo' => 'Domingo',
                ],
            ],
            'attributes' => ['class' => 'form-check-input', 'id' => 'dias'],
        ]);

        $this->add([
            'type' => Element\Date::class,
            'name' => 'vigente_desde',
            'options' => ['label' => 'Vigente desde *'],
            'attributes' => [
                'readonly' => !$required,
                'required' => $required,
                'class' => 'form-control',
                'id' => 'vigente_desde',
            ],
        ]);

        $this->add([
            'type' => Element\Date::class,
            'name' => 'vigente_hasta',
            'options' => ['label' => 'Vigente hasta (opcional)'],
            'attributes' => [
                'readonly' => !$required,
                'required' => false,
                'class' => 'form-control',
                'id' => 'vigente_hasta',
            ],
        ]);

        // Auditoría
        $this->add([
            'type' => Element\Number::class,
            'name' => 'idProgramacion',
            'attributes' => ['readonly' => true, 'class' => 'form-control', 'id' => 'idProgramacion']
        ]);
        $this->add([
            'type' => Element\Text::class,
            'name' => 'estado',
            'attributes' => ['readonly' => true, 'class' => 'form-control', 'id' => 'estado']
        ]);
        $this->add([
            'type' => Element\Text::class,
            'name' => 'registradopor',
            'attributes' => ['readonly' => true, 'class' => 'form-control', 'id' => 'registradopor']
        ]);
        $this->add([
            'type' => Element\Text::class,
            'name' => 'modificadopor',
            'attributes' => ['readonly' => true, 'class' => 'form-control', 'id' => 'modificadopor']
        ]);
        $this->add([
            'type' => Element\Text::class,
            'name' => 'fechahorareg',
            'attributes' => ['readonly' => true, 'class' => 'form-control', 'id' => 'fechahorareg']
        ]);
        $this->add([
            'type' => Element\Text::class,
            'name' => 'fechahoramod',
            'attributes' => ['readonly' => true, 'class' => 'form-control', 'id' => 'fechahoramod']
        ]);
    }
}
