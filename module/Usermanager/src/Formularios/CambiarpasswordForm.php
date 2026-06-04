<?php

namespace Usermanager\Formularios;

use Laminas\Form\Form;
use Laminas\Form\Element;

class CambiarpasswordForm extends Form
{

    public function __construct($accion = '')
    {

        parent::__construct('formCambiarpassword');
        $this->setAttribute('method', 'post');
        $this->setAttribute('data-toggle', 'validator');
        $this->setAttribute('role', 'form');
        $this->setAttribute('enctype', 'multipart/form-data');
        $this->setAttribute('action', 'cambiarpassword');
        $this->setAttribute('onsubmit', 'guardarNuevoPassword(); return false');


        $this->add([
            'type' => Element\Password::class,
            'name' => 'passwordactual',
            'options' => [
                'label' => 'Contraseña Actual *',
            ],
            'attributes' => [
                'required' => true,
                'size' => 40,
                'maxlength' => 25,
                'class' => 'form-control',
                'autocomplete' => false,
                'data-toggle' => 'tooltip',
                'title' => 'Proporcione la contraseña actual de su cuenta',
                'placeholder' => 'Introduce tu contraseña actual',
                'id' => 'passwordactual',
            ],
        ]);
        $this->add([
            'type' => Element\Password::class,
            'name' => 'password',
            'options' => [
                'label' => 'Nueva Contraseña *',
            ],
            'attributes' => [
                'required' => true,
                'size' => 40,
                'maxlength' => 25,
                'class' => 'form-control',
                'autocomplete' => false,
                'data-toggle' => 'tooltip',
                'title' => 'La contraseña debe tener al menos 8 caracteres',
                'placeholder' => 'Ingrese su nueva contraseña preferida',
                'onblur' => 'verificarPassword()',
                'id' => 'password',
            ],
        ]);
        $this->add([
            'type' => Element\Password::class,
            'name' => 'passwordConfirm',
            'options' => [
                'label' => 'Confirmar Contraseña *',
            ],
            'attributes' => [
                'required' => true,
                'size' => 40,
                'maxlength' => 25,
                'class' => 'form-control',
                'autocomplete' => false,
                'data-toggle' => 'tooltip',
                'title' => 'La contraseña debe coincidir con la proporcionada anteriormente',
                'placeholder' => 'Ingrese su nueva contraseña nuevamente',
                'onblur' => 'verificarPassword()',
                'id' => 'passwordConfirm',
            ],
        ]);

        //------------------------------------------------------------------------------

        $this->add([
            'type' => Element\Number::class,
            'name' => 'idUsuario',
            'options' => [
                'label' => 'ID Empleado',
            ],
            'attributes' => [
                'readonly' => true,
                'style' => "font-weight: bold",
                'class' => 'form-control',
                'id' => 'idUsuario ',
            ],
        ]);
    }
}
