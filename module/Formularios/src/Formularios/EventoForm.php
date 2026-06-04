<?php

namespace Administracion\Formularios;

use Laminas\Form\Form;
use Laminas\Form\Element;

class EventoForm extends Form
{

    public function __construct($accion = '', $fecha = '')
    {
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
            case 'actualizarimagen':
                $onsubmit = 'return validarGuardar(event, this,"EDITAR")';
                $required = false;
                $disabled = true;
                break;
            default:
                $onsubmit = '';
                $required = false;
                $disabled = false;
                break;
        }

        parent::__construct('formEvento');
        $this->setAttribute('method', 'post');
        $this->setAttribute('data-toggle', 'validator');
        $this->setAttribute('role', 'form');
        $this->setAttribute('enctype', 'multipart/form-data');
        $this->setAttribute('action', $accion);
        $this->setAttribute('onsubmit', $onsubmit);

        $this->add([
            'type' => Element\Textarea::class,
            'name' => 'titulo',
            'options' => [
                'label' => 'Titulo *',
            ],
            'attributes' => [
                /*  'maxlength' => 100, */
                /* 'style' => 'text-transform: uppercase', */
                'readonly' => !$required,
                'required' => $required,
                'class' => 'form-control',
                'id' => 'titulo',
            ],
        ]);
        $this->add([
            'type' => Element\Textarea::class,
            'name' => 'contenido',
            'options' => [
                'label' => 'Contenido *',
            ],
            'attributes' => [
                /* 'maxlength' => 200, */
                'readonly' => !$required,
                'required' => $required,
                'class' => 'form-control',
                'id' => 'contenido',
            ],
        ]);
        $this->add([
            'type' => Element\File::class,
            'name' => 'imagen',
            'options' => [
                'label' => 'Imagen',
            ],
            'attributes' => [
                'onchange' => 'validarImagen()',
                'readonly' => !$required,
                'required' => $required,
                'accept' => 'image/*',
                'class' => 'form-control',
                'id' => 'imagen',
            ],
        ]);
        $this->add([
            'type' => Element\Text::class,
            'name' => 'lugar',
            'options' => [
                'label' => 'Lugar *',
            ],
            'attributes' => [
                'maxlength' => 100,
                'readonly' => !$required,
                'required' => $required,
                'class' => 'form-control',
                'id' => 'lugar',
            ],
        ]);
        $this->add([
            'type' => Element\Text::class,
            'name' => 'localizacion',
            'options' => [
                'label' => 'Localizacion *',
            ],
            'attributes' => [
                /* 'maxlength' => 200, */
                'readonly' => true,
                'required' => false,
                'class' => 'form-control',
                'id' => 'localizacion',
            ],
        ]);
        $this->add([
            'type' => Element\DateTimeLocal::class,
            'name' => 'start',
            'options' => [
                'label' => 'Empieza *',
            ],
            'attributes' => [
                'onchange' => 'validarFecha()',
                'readonly' => !$required,
                'required' => $required,
                'class' => 'form-control',
                'value' => $fecha,
                'id' => 'start',
            ],
        ]);
        $this->add([
            'type' => Element\DateTimeLocal::class,
            'name' => 'end',
            'options' => [
                'label' => 'Termina *',
            ],
            'attributes' => [
                'onchange' => 'validarFecha()',
                'readonly' => !$required,
                'required' => $required,
                'class' => 'form-control',
                'value' => $fecha,
                'id' => 'end',
            ],
        ]);
        $this->add([
            'type' => Element\Color::class,
            'name' => 'color',
            'options' => [
                'label' => 'Color *',
            ],
            'attributes' => [
                'maxlength' => 20,
                'value' => "#000066",
                'style' => 'text-transform: uppercase',
                'readonly' => !$required,
                'required' => $required,
                'class' => 'form-control',
                'id' => 'color',
            ],
        ]);
        $this->add([
            'type' => Element\Color::class,
            'name' => 'textColor',
            'options' => [
                'label' => 'Color texto*',
            ],
            'attributes' => [
                'maxlength' => 20,
                'value' => "#ffffff",
                'style' => 'text-transform: uppercase',
                'readonly' => !$required,
                'required' => $required,
                'class' => 'form-control',
                'id' => 'textColor',
            ],
        ]);
        $this->add([
            'type' => Element\Select::class,
            'name' => 'allDay',
            'options' => [
                'label' => 'Todo el día *',
                'empty_option' => 'Seleccione...',
                'value_options' => [
                    'false' => 'NO',
                    'true' => 'SI',
                ],
                'disable_inarray_validator' => true,
            ],
            'attributes' => [
                'readonly' => !$required,
                'required' => $required,
                'class' => 'form-control',
                'id' => 'allDay',
            ],
        ]);

        //------------------------------------------------------------------------------

        $this->add([
            'type' => Element\Number::class,
            'name' => 'idEvento',
            'options' => [
                'label' => 'idEvento',
            ],
            'attributes' => [
                'readonly' => true,
                'style' => "font-weight: bold",
                'class' => 'form-control',
                'id' => 'idEvento',
            ],
        ]);

        $this->add([
            'type' => Element\Text::class,
            'name' => 'estado',
            'options' => [
                'label' => 'Estado',
            ],
            'attributes' => [
                'readonly' => true,
                'class' => 'form-control',
                'id' => 'estado',
            ],
        ]);

        $this->add([
            'type' => Element\Text::class,
            'name' => 'registradopor',
            'options' => [
                'label' => 'Registrado Por',
            ],
            'attributes' => [
                'readonly' => true,
                'class' => 'form-control',
                'id' => 'registradopor',
            ],
        ]);

        $this->add([
            'type' => Element\Text::class,
            'name' => 'modificadopor',
            'options' => [
                'label' => 'Modificado Por',
            ],
            'attributes' => [
                'readonly' => true,
                'class' => 'form-control',
                'id' => 'modificadopor',
            ],
        ]);

        $this->add([
            'type' => Element\Text::class,
            'name' => 'fechahorareg',
            'options' => [
                'label' => 'Fecha Registro',
            ],
            'attributes' => [
                'readonly' => true,
                'class' => 'form-control',
                'id' => 'fechahorareg',
            ],
        ]);

        $this->add([
            'type' => Element\Text::class,
            'name' => 'fechahoramod',
            'options' => [
                'label' => 'Fecha Actualizacion',
            ],
            'attributes' => [
                'readonly' => true,
                'class' => 'form-control',
                'id' => 'fechahoramod',
            ],
        ]);
    }
}
