<?php

namespace Administracion\Formularios;

use Laminas\Form\Form;
use Laminas\Form\Element;

class DirectorioForm extends Form
{

    public function __construct($accion = '', $dependencias = array())
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
            default:
                $onsubmit = '';
                $required = false;
                $disabled = false;
                break;
        }

        parent::__construct('formDirectorio');
        $this->setAttribute('method', 'post');
        $this->setAttribute('data-toggle', 'validator');
        $this->setAttribute('role', 'form');
        $this->setAttribute('enctype', 'multipart/form-data');
        $this->setAttribute('action', $accion);
        $this->setAttribute('onsubmit', $onsubmit);

        $this->add([
            'type' => Element\Select::class,
            'name' => 'idDependencia',
            'options' => [
                'label' => 'Dependencia *',
                'empty_option' => 'Seleccione...',
                'value_options' => $dependencias,
                'disable_inarray_validator' => true,
            ],
            'attributes' => [
                'readonly' => !$required,
                'required' => $required,
                'class' => 'form-control',
                'id' => 'idDependencia',
            ],
        ]);
        $this->add([
            'type' => Element\Text::class,
            'name' => 'nombre',
            'options' => [
                'label' => 'Nombre *',
            ],
            'attributes' => [
                /* 'maxlength' => 200, */
                'readonly' => !$required,
                'required' => $required,
                'class' => 'form-control',
                'id' => 'nombre',
            ],
        ]);
        $this->add([
            'type' => Element\Text::class,
            'name' => 'cargo',
            'options' => [
                'label' => 'Cargo *',
            ],
            'attributes' => [
                'readonly' => !$required,
                'required' => $required,
                'class' => 'form-control',
                'id' => 'cargo',
            ],
        ]);
        $this->add([
            'type' => Element\Text::class,
            'name' => 'telefono',
            'options' => [
                'label' => 'Telefono *',
            ],
            'attributes' => [
                'readonly' => !$required,
                'required' => $required,
                'class' => 'form-control',
                'id' => 'telefono',
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
            'type' => Element\Text::class,
            'name' => 'asistente',
            'options' => [
                'label' => 'Asistente',
            ],
            'attributes' => [
                'readonly' => !$required,
                'required' => !$required,
                'class' => 'form-control',
                'id' => 'asistente',
            ],
        ]);
        $this->add([
            'type' => Element\Text::class,
            'name' => 'direccion',
            'options' => [
                'label' => 'Dirección',
            ],
            'attributes' => [
                'readonly' => !$required,
                'required' => !$required,
                'class' => 'form-control',
                'id' => 'direccion',
            ],
        ]);
        $this->add([
            'type' => Element\Text::class,
            'name' => 'cargoAsistente',
            'options' => [
                'label' => 'Cargo Asistente',
            ],
            'attributes' => [
                'readonly' => !$required,
                'required' => !$required,
                'class' => 'form-control',
                'id' => 'cargoAsistente',
            ],
        ]);
        $this->add([
            'type' => Element\Select::class,
            'name' => 'unidad',
            'options' => [
                'label' => 'Unidad *',
                'empty_option' => 'Seleccione...',
                'value_options' => [
                    'Directivos' => 'Directivos',
                    'Facultades' => 'Facultades',
                    'Centros' => 'Centros',
                    'Divisiones' => 'Divisiones',
                    'Oficinas Asesoras' => 'Oficinas Asesoras',
                    'Áreas' => 'Áreas',
                    'Extensión social' => 'Extensión social',
                    'Museos' => 'Museos',
                ],
                'disable_inarray_validator' => true,
            ],
            'attributes' => [
                'readonly' => !$required,
                'required' => $required,
                'class' => 'form-control',
                'id' => 'unidad',
            ],
        ]);
        //------------------------------------------------------------------------------

        $this->add([
            'type' => Element\Number::class,
            'name' => 'idDI',
            'options' => [
                'label' => 'ID',
            ],
            'attributes' => [
                'readonly' => true,
                'style' => "font-weight: bold",
                'class' => 'form-control',
                'id' => 'idDI',
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
