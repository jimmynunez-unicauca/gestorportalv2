<?php

namespace Administracion\Formularios;

use Laminas\Form\Form;
use Laminas\Form\Element;

class FacultadForm extends Form
{
    public function __construct($accion = '')
    {
        switch ($accion) {
            case 'registrar':
            case 'editar':
                $onsubmit = 'return validarGuardar(event, this,"' . strtoupper($accion) . '")';
                $readonly = false;
                $required = true;
                break;
            case 'detalle':
            case 'eliminar':
                $onsubmit = '';
                $readonly = true;
                $required = false;
                break;
            default:
                $onsubmit = '';
                $readonly = false;
                $required = false;
        }

        parent::__construct('formFacultad');
        $this->setAttribute('method', 'post');
        $this->setAttribute('data-toggle', 'validator');
        $this->setAttribute('role', 'form');
        $this->setAttribute('action', $accion);
        $this->setAttribute('onsubmit', $onsubmit);

        $this->add([
            'type' => Element\Number::class,
            'name' => 'idFacultad',
            'options' => ['label' => 'ID'],
            'attributes' => ['readonly' => true, 'class' => 'form-control', 'id' => 'idFacultad'],
        ]);

        $this->add([
            'type' => Element\Text::class,
            'name' => 'facultad',
            'options' => ['label' => 'Facultad *'],
            'attributes' => [
                'maxlength' => 100,
                'readonly'  => $readonly,
                'required'  => $required,
                'class'     => 'form-control',
                'id'        => 'facultad',
            ],
        ]);
    }
}
