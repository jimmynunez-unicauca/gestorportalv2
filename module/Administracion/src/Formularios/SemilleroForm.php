<?php

namespace Administracion\Formularios;

use Laminas\Form\Form;
use Laminas\Form\Element;

class SemilleroForm extends Form
{

    public function __construct($accion = '', $listaFacultad = array())
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
            case 'activar':
                $onsubmit = 'return validarGuardar(event, this,"ACTIVAR")';
                $required = false;
                $disabled = true;
                break;
            case 'actualizararchivo':
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

        parent::__construct('formSemillero');
        $this->setAttribute('method', 'post');
        $this->setAttribute('data-toggle', 'validator');
        $this->setAttribute('role', 'form');
        $this->setAttribute('enctype', 'multipart/form-data');
        $this->setAttribute('action', $accion);
        $this->setAttribute('onsubmit', $onsubmit);

        $this->add([
            'type' => Element\Select::class,
            'name' => 'idFacultad',
            'options' => [
                'label' => 'Facultades *',
                'empty_option' => 'Seleccione...',
                'value_options' => $listaFacultad,
                'disable_inarray_validator' => true,
            ],
            'attributes' => [
                'required' => true,
                'class' => 'form-control',
                'onchange' => 'getFacultad(this.value)',
                'id' => 'idFacultad',
            ],
        ]);
        $this->add([
            'type' => Element\Number::class,
            'name' => 'idSemillero',
            'options' => [
                'label' => 'idSemillero',
            ],
            'attributes' => [
                'readonly' => !$required,
                'required' => $required,
                'class' => 'form-control',
                'id' => 'idSemillero',
            ],
        ]);
        $this->add([
            'type' => Element\Text::class,
            'name' => 'nombre',
            'options' => [
                'label' => 'Nombre *',
            ],
            'attributes' => [
                'maxlength' => 200,
                'readonly' => !$required,
                'required' => $required,
                'class' => 'form-control',
                'id' => 'nombre',
            ],
        ]);
        $this->add([
            'type' => Element\Text::class,
            'name' => 'mentor',
            'options' => [
                'label' => 'Mentor *',
            ],
            'attributes' => [
                'maxlength' => 200,
                'readonly' => !$required,
                'required' => $required,
                'class' => 'form-control',
                'id' => 'mentor',
            ],
        ]);
        $this->add([
            'type' => Element\Email::class,
            'name' => 'correo',
            'options' => [
                'label' => 'Correo *',
            ],
            'attributes' => [
                'readonly' => !$required,
                'required' => $required,
                'class' => 'form-control',
                'id' => 'correo',
            ],
        ]);
        $this->add([
            'type' => Element\Textarea::class,
            'name' => 'detalle',
            'options' => [
                'label' => 'Detalle *',
            ],
            'attributes' => [
                'maxlength' => 200,
                'readonly' => !$required,
                'required' => $required,
                'class' => 'form-control',
                'id' => 'detalle',
                /* 'rows' => 5,
                'cols' => 40, */
            ],
        ]);
        $this->add([
            'type' => Element\Text::class,
            'name' => 'enlaceGruplac',
            'options' => [
                'label' => 'Enlace Gruplac *',
            ],
            'attributes' => [
                'readonly' => !$required,
                'required' => !$required,
                'class' => 'form-control',
                'id' => 'enlaceGruplac',
            ],
        ]);
        $this->add([
            'type' => Element\Text::class,
            'name' => 'enlaceSivri',
            'options' => [
                'label' => 'Enlace Sivri *',
            ],
            'attributes' => [
                'readonly' => !$required,
                'required' => !$required,
                'class' => 'form-control',
                'id' => 'enlaceSivri',
            ],
        ]);
        $this->add([
            'type' => Element\File::class,
            'name' => 'imagen',
            'options' => [
                'label' => 'Imagen',
            ],
            'attributes' => [
                'onchange' => 'validarAdjunto()',
                'readonly' => !$required,
                'required' => $required,
                'accept' => 'image/*',
                'class' => 'form-control',
                'id' => 'imagen',
            ],
        ]);
        //------------------------------------------------------------------------------

        $this->add([
            'type' => Element\Number::class,
            'name' => 'idSI',
            'options' => [
                'label' => 'ID',
            ],
            'attributes' => [
                'readonly' => true,
                'style' => "font-weight: bold",
                'class' => 'form-control',
                'id' => 'idSI',
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
