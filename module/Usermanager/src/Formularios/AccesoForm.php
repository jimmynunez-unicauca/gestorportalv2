<?php

namespace Usermanager\Formularios;

use Laminas\Form\Form;
use Laminas\Form\Element;

class AccesoForm extends Form
{

    public function __construct($accion = '', $listaEmpleado = array(), $listaRoles = array())
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

        parent::__construct('formContratolaboral');
        $this->setAttribute('method', 'post');
        $this->setAttribute('data-toggle', 'validator');
        $this->setAttribute('role', 'form');
        $this->setAttribute('enctype', 'multipart/form-data');
        $this->setAttribute('action', $accion);
        $this->setAttribute('onsubmit', $onsubmit);

        $this->add([
            'type' => Element\Select::class,
            'name' => 'idEmpleadoCliente',
            'options' => [
                'label' => 'Usuarios *',
                'empty_option' => 'Seleccione...',
                'value_options' => $listaEmpleado,
                'disable_inarray_validator' => true,
            ],
            'attributes' => [
                'required' => true,
                'class' => 'form-control',
                'onchange' => 'getLogin(this.value)',
                'id' => 'idEmpleadoCliente',
            ],
        ]);
        $this->add([
            'type' => Element\Select::class,
            'name' => 'idRol',
            'options' => [
                'label' => 'Roles *',
                'empty_option' => 'Seleccione...',
                'value_options' => $listaRoles,
                'disable_inarray_validator' => true,
            ],
            'attributes' => [
                'readonly' => !$required,
                'required' => $required,
                'class' => 'form-control',
                'id' => 'idRol',
            ],
        ]);
        $this->add([
            'type' => Element\Text::class,
            'name' => 'usuario',
            'options' => [
                'label' => 'Nombre',
            ],
            'attributes' => [
                'maxlength' => 50,
                'style' => 'text-transform: uppercase',
                'readonly' => $required,
                'required' => $required,
                'class' => 'form-control',
                'id' => 'usuario',
            ],
        ]);
        $this->add([
            'type' => Element\Text::class,
            'name' => 'login',
            'options' => [
                'label' => 'Acceso'
            ],
            'attributes' => [
                'required' => true,
                'readonly' => true,
                'size' => 40,
                'maxlength' => 25,
                'pattern' => '^[a-zA-Z0-9]+$',  # enforcing what type of data we accept
                'data-toggle' => 'tooltip',
                'class' => 'form-control',   # styling the text field
                'onchange' => 'existeLogin(this.value)',
                'title' => 'El nombre de usuario debe constar únicamente de caracteres alfanuméricos',
                'placeholder' => 'Introduzca su nombre de usuario',
                'id' => 'login',
            ]
        ]);
        $this->add([
            'type' => Element\Password::class,
            'name' => 'password',
            'options' => [
                'label' => 'Contraseña *'
            ],
            'attributes' => [
                'required' => true,
                'size' => 40,
                'maxlength' => 25,
                'autocomplete' => false,
                'data-toggle' => 'tooltip',
                'class' => 'form-control',   # styling
                'title' => 'La contraseña debe tener entre 8 y 25 caracteres',
                'placeholder' => 'Ingresa tu contraseña',
                'id' => 'password',
            ]
        ]);
        $this->add([
            'type' => Element\Password::class,
            'name' => 'passwordseguro',
            'options' => [
                'label' => 'Verificar contraseña *'
            ],
            'attributes' => [
                'required' => true,
                'size' => 40,
                'maxlength' => 25,
                'autocomplete' => false,
                'data-toggle' => 'tooltip',
                'class' => 'form-control',   # styling
                'onblur' => 'verificarPassword()',
                'title' => 'La contraseña debe coincidir con la proporcionada anteriormente',
                'placeholder' => 'Ingrese su contraseña nuevamente',
                'id' => 'passwordseguro',
            ]
        ]);

        //------------------------------------------------------------------------------

        $this->add([
            'type' => Element\Number::class,
            'name' => 'idUsuario',
            'options' => [
                'label' => 'ID',
            ],
            'attributes' => [
                'readonly' => true,
                'style' => "font-weight: bold",
                'class' => 'form-control',
                'id' => 'idUsuario',
            ],
        ]);
        $this->add([
            'type' => Element\Text::class,
            'name' => 'foto',
            'options' => [
                'label' => 'Fstado',
            ],
            'attributes' => [
                'readonly' => true,
                'class' => 'form-control',
                'id' => 'foto',
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
