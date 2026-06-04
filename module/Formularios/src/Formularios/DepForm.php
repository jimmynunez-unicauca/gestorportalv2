<?php

namespace Formularios\Formularios;

use Laminas\Form\Form;
use Laminas\Form\Element;

class DepForm extends Form
{

    public function __construct($accion = '')
    {
        switch ($accion) {
            case 'registrardep':
                $onsubmit = 'return validarGuardarDep(event, this,"REGISTRAR")';
                $required = true;
                $disabled = false;
                break;
            case 'editardep':
                $onsubmit = 'return validarGuardarDep(event, this,"EDITAR")';
                $required = true;
                $disabled = false;
                break;
            case 'detalledep':
                $onsubmit = '';
                $required = false;
                $disabled = true;
                break;
            case 'eliminardep':
                $onsubmit = 'return validarGuardarDep(event, this,"ELIMINAR")';
                $required = false;
                $disabled = true;
                break;
            case 'activardep':
                $onsubmit = 'return validarGuardarDep(event, this,"ACTIVAR")';
                $required = false;
                $disabled = true;
                break;
            default:
                $onsubmit = '';
                $required = false;
                $disabled = false;
                break;
        }

        parent::__construct('formDep');
        $this->setAttribute('method', 'post');
        $this->setAttribute('data-toggle', 'validator');
        $this->setAttribute('role', 'form');
        $this->setAttribute('enctype', 'multipart/form-data');
        $this->setAttribute('action', $accion);
        $this->setAttribute('onsubmit', $onsubmit);

        $this->add([
            'type' => Element\Text::class,
            'name' => 'dependencia',
            'options' => [
                'label' => 'Dependencia *',
            ],
            'attributes' => [
                'maxlength' => 100,
                'readonly' => !$required,
                'required' => $required,
                'class' => 'form-control',
                'id' => 'dependencia',
            ],
        ]);

        //------------------------------------------------------------------------------

        $this->add([
            'type' => Element\Number::class,
            'name' => 'idform_dependencia',
            'options' => [
                'label' => 'idArchivo',
            ],
            'attributes' => [
                'readonly' => true,
                'style' => "font-weight: bold",
                'class' => 'form-control',
                'id' => 'idform_dependencia',
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
