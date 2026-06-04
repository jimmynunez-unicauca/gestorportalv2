<?php

namespace Talentohumano\Formularios;

use Laminas\Form\Form;
use Laminas\Form\Element;

class BusquedaForm extends Form
{

    public function __construct()
    {
        parent::__construct('formBusqueda');
        $this->setAttribute('method', 'get');
        $this->setAttribute('data-toggle', 'validator');
        $this->setAttribute('role', 'form');
        $this->setAttribute('onsubmit', 'return validarFiltroBusqueda()');
        $this->setAttribute('action', 'index');

        $this->add([
            'type' => Element\Text::class,
            'name' => 'identificacionBusq',
            'options' => [
                'label' => 'Identificacion',
            ],
            'attributes' => [
                'pattern' => '[0-9]{5,15}',
                'required' => false,
                'class' => 'form-control',
                'id' => 'identificacionBusq',
            ],
        ]);

        $this->add([
            'type' => Element\Date::class,
            'name' => 'fechainiBusq',
            'options' => [
                'label' => 'Desde',
            ],
            'attributes' => [
                'class' => 'form-control',
                'required' => false,
                'id' => 'fechainiBusq',
            ],
        ]);

        $this->add([
            'type' => Element\Date::class,
            'name' => 'fechafinBusq',
            'options' => [
                'label' => 'Hasta',
            ],
            'attributes' => [
                'class' => 'form-control',
                'required' => false,
                'id' => 'fechafinBusq',
            ],
        ]);

        $this->add([
            'type' => Element\Text::class,
            'name' => 'nombre1Busq',
            'options' => [
                'label' => 'Primer Nombre',
            ],
            'attributes' => [
                'maxlength' => 20,
                'style' => 'text-transform: uppercase',
                'required' => false,
                'class' => 'form-control',
                'id' => 'nombre1Busq',
            ],
        ]);

        $this->add([
            'type' => Element\Text::class,
            'name' => 'nombre2Busq',
            'options' => [
                'label' => 'Segundo Nombre',
            ],
            'attributes' => [
                'maxlength' => 20,
                'style' => 'text-transform: uppercase',
                'required' => false,
                'class' => 'form-control',
                'id' => 'nombre2Busq',
            ],
        ]);

        $this->add([
            'type' => Element\Text::class,
            'name' => 'apellido1Busq',
            'options' => [
                'label' => 'Primer Apellido',
            ],
            'attributes' => [
                'maxlength' => 20,
                'style' => 'text-transform: uppercase',
                'required' => false,
                'class' => 'form-control',
                'id' => 'apellido1Busq',
            ],
        ]);

        $this->add([
            'type' => Element\Text::class,
            'name' => 'apellido2Busq',
            'options' => [
                'label' => 'Segundo Apellido',
            ],
            'attributes' => [
                'maxlength' => 20,
                'style' => 'text-transform: uppercase',
                'required' => false,
                'class' => 'form-control',
                'id' => 'apellido2Busq',
            ],
        ]);
    }
}
