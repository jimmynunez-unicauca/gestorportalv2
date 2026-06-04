<?php

namespace Usermanager\Formularios;

use Laminas\Form\Form;
use Laminas\Form\Element;

class EmpleadoclienteForm extends Form
{

    public function __construct($accion = '')
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
            default:
                $onsubmit = '';
                $required = false;
                $disabled = false;
                break;
        }

        parent::__construct('formEmpleadocliente');
        $this->setAttribute('method', 'post');
        $this->setAttribute('data-toggle', 'validator');
        $this->setAttribute('role', 'form');
        $this->setAttribute('enctype', 'multipart/form-data');
        $this->setAttribute('action', $accion);
        $this->setAttribute('onsubmit', $onsubmit);

        $this->add([
            'type' => Element\Text::class,
            'name' => 'nombre',
            'options' => [
                'label' => 'Nombre *',
            ],
            'attributes' => [
                'maxlength' => 20,
                'style' => 'text-transform: uppercase',
                'readonly' => !$required,
                'required' => $required,
                'class' => 'form-control',
                'id' => 'nombre',
            ],
        ]);
        $this->add([
            'type' => Element\Text::class,
            'name' => 'apellido',
            'options' => [
                'label' => 'Apellido *',
            ],
            'attributes' => [
                'maxlength' => 20,
                'style' => 'text-transform: uppercase',
                'readonly' => !$required,
                'required' => $required,
                'class' => 'form-control',
                'id' => 'apellido',
            ],
        ]);
        $this->add([
            'type' => Element\Select::class,
            'name' => 'tipoIdentificacion',
            'options' => [
                'label' => 'Tipo de identificacion *',
                'empty_option' => 'Seleccione...',
                'value_options' => [
                    'Cedula' => 'Cedula',
                    'Tarjeta de Identidad' => 'Tarjeta de Identidad',
                    'Pasaporte' => 'Pasaporte',
                ],
                'disable_inarray_validator' => true,
            ],
            'attributes' => [
                'readonly' => !$required,
                'required' => $required,
                'class' => 'form-control',
                'id' => 'tipoIdentificacion',
            ],
        ]);
        $this->add([
            'type' => Element\Text::class,
            'name' => 'identificacion',
            'options' => [
                'label' => 'Identificacion *',
            ],
            'attributes' => [
                'min' => 1,
                'onchange' => 'existeIdentificacion()',
                'readonly' => !$required,
                'required' => $required,
                'class' => 'form-control',
                'id' => 'identificacion',
            ],
        ]);
        $this->add([
            'type' => Element\Date::class,
            'name' => 'fechaNacimiento',
            'options' => [
                'label' => 'Fecha de nacimiento *',
            ],
            'attributes' => [
                'readonly' => !$required,
                'required' => $required,
                'class' => 'form-control',
                'id' => 'fechaNacimiento',
            ],
        ]);
        $this->add([
            'type' => Element\Email::class,
            'name' => 'email',
            'options' => [
                'label' => 'Email *',
            ],
            'attributes' => [
                'minlength' => 10,
                'maxlength' => 100,
                'style' => 'text-transform: lowercase',
                'readonly' => !$required,
                'required' => $required,
                'class' => 'form-control',
                'id' => 'email',
            ],
        ]);
        $this->add([
            'type' => Element\Number::class,
            'name' => 'telefono',
            'options' => [
                'label' => 'Telefono *',
            ],
            'attributes' => [
                'min' => 0,
                'readonly' => !$required,
                'required' => $required,
                'class' => 'form-control',
                'id' => 'telefono',
            ],
        ]);
        $this->add([
            'type' => Element\Text::class,
            'name' => 'direccion',
            'options' => [
                'label' => 'Direccion *',
            ],
            'attributes' => [
                'maxlength' => 100,
                'style' => 'text-transform: uppercase',
                'readonly' => !$required,
                'required' => $required,
                'class' => 'form-control',
                'id' => 'direccion',
            ],
        ]);
        $this->add([
            'type' => Element\Select::class,
            'name' => 'genero',
            'options' => [
                'label' => 'Genero *',
                'empty_option' => 'Seleccione...',
                'value_options' => [
                    'Femenino' => 'Femenino',
                    'Masculino' => 'Masculino',
                ],
                'disable_inarray_validator' => true,
            ],
            'attributes' => [
                'readonly' => !$required,
                'required' => $required,
                'class' => 'form-control',
                'id' => 'genero',
            ],
        ]);

        //------------------------------------------------------------------------------

        $this->add([
            'type' => Element\Number::class,
            'name' => 'idEmpleadoCliente',
            'options' => [
                'label' => 'ID Empleado',
            ],
            'attributes' => [
                'readonly' => true,
                'style' => "font-weight: bold",
                'class' => 'form-control',
                'id' => 'idEmpleadoCliente ',
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
