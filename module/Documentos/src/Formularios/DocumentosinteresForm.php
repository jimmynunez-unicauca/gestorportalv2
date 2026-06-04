<?php

namespace Documentos\Formularios;

use Laminas\Form\Form;
use Laminas\Form\Element;

class DocumentosinteresForm extends Form
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

        parent::__construct('formDocumentosinteres');
        $this->setAttribute('method', 'post');
        $this->setAttribute('data-toggle', 'validator');
        $this->setAttribute('role', 'form');
        $this->setAttribute('enctype', 'multipart/form-data');
        $this->setAttribute('action', $accion);
        $this->setAttribute('onsubmit', $onsubmit);

        $this->add([
            'type' => Element\Textarea::class,
            'name' => 'nombre',
            'options' => [
                'label' => 'Tituo *',
            ],
            'attributes' => [
                /* 'maxlength' => 200, */
                'readonly' => $required,
                'required' => $required,
                'class' => 'form-control',
                'id' => 'nombre',
            ],
        ]);
        $this->add([
            'type' => Element\Number::class,
            'name' => 'idLvmen',
            'options' => [
                'label' => 'ID LVMEN *',
            ],
            'attributes' => [
                'readonly' => $required,
                'required' => $required,
                'class' => 'form-control',
                'id' => 'idLvmen',
            ],
        ]);

        //------------------------------------------------------------------------------

        $this->add([
            'type' => Element\Number::class,
            'name' => 'idDocumentosInteres',
            'options' => [
                'label' => 'ID',
            ],
            'attributes' => [
                'readonly' => true,
                'style' => "font-weight: bold",
                'class' => 'form-control',
                'id' => 'idDocumentosInteres',
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
