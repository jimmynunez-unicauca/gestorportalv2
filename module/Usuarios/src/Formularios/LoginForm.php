<?php

namespace Usuarios\Formularios;

use Laminas\Form\Form;
use Laminas\Form\Element;

class LoginForm extends Form
{
    public function __construct($nombre = null)
    {
        parent::__construct($nombre);

        $this->setAttribute('method', 'post');
        $this->setAttribute('class', 'form-signin');
        $this->setAttribute('id', 'login-form');

        $this->add([
            'name' => 'login',
            'options' => [
                'label' => '',
            ],
            'type' => Element\Text::class,
            'attributes' => [
                'class' => 'form-control form-control-modern',
                'placeholder' => 'Usuario',
                'autofocus' => true,
                'required' => true,
                'autocomplete' => 'username',
                'value' => '',
                'id' => 'login',
                'style' => 'text-transform: lowercase;',
            ],
        ]);

        $this->add([
            'name' => 'password',
            'options' => [
                'label' => '',
            ],
            'type' => Element\Password::class,
            'attributes' => [
                'class' => 'form-control form-control-modern',
                'placeholder' => 'Contraseña',
                'required' => true,
                'autocomplete' => 'current-password',
                'value' => '',
                'id' => 'password',
            ],
        ]);

        $this->add([
            'type' => Element\Csrf::class,
            'name' => 'csrf',
            'options' => [
                'csrf_options' => [
                    'timeout' => 600,
                ],
            ],
        ]);

        $this->add([
            'name' => 'btnIniciarSesion',
            'type' => Element\Submit::class,
            'attributes' => [
                'value' => 'Iniciar Sesión',
                'class' => 'btn btn-login',
                'id' => 'btn-login',
            ],
        ]);
    }
}
