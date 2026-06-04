<?php

namespace Administracion\Formularios;

use Laminas\Form\Form;
use Laminas\Form\Element;

class PluginunicaucaForm extends Form
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
            case 'desactivar':
                $onsubmit = 'return validarGuardar(event, this,"DESACTIVAR")';
                $required = false;
                $disabled = true;
                break;
            default:
                $onsubmit = '';
                $required = false;
                $disabled = false;
                break;
        }
        parent::__construct('formPluginunicauca');
        $this->setAttribute('method', 'post');
        $this->setAttribute('data-toggle', 'validator');
        $this->setAttribute('role', 'form');
        $this->setAttribute('enctype', 'multipart/form-data');
        $this->setAttribute('action', $accion);
        $this->setAttribute('onsubmit', $onsubmit);

        // Nombre del módulo
        $this->add([
            'type' => Element\Text::class,
            'name' => 'nombre_modulo',
            'options' => [
                'label' => 'Nombre del módulo *',
            ],
            'attributes' => [
                /* 'maxlength' => 200, */
                'readonly' => !$required,
                'required' => $required,
                'class' => 'form-control',
                'placeholder' => 'Ej: forms-artes',
                'id' => 'nombre_modulo',
            ],
        ]);

        // Ruta del archivo
        $this->add([
            'type' => Element\Text::class,
            'name' => 'ruta_archivo',
            'options' => [
                'label' => 'Ruta del archivo *',
            ],
            'attributes' => [
                'readonly' => !$required,
                'required' => $required,
                'class' => 'form-control',
                'placeholder' => 'Ej: modules/forms-artes/shortcode.php',
                'id' => 'ruta_archivo',
            ],
        ]);

        // Descripción
        $this->add([
            'type' => Element\Textarea::class,
            'name' => 'descripcion',
            'options' => [
                'label' => 'Descripción *',
            ],
            'attributes' => [
                'readonly' => !$required,
                'required' => $required,
                'class' => 'form-control',
                'rows' => 4,
                'placeholder' => 'Descripción del módulo...',
                'id' => 'descripcion',
            ],
        ]);

        // Activo
        $this->add([
            'type' => Element\Checkbox::class,
            'name' => 'activo',
            'options' => [
                'label' => 'Activo *',
                'use_hidden_element' => true,
                'checked_value' => '1',
                'unchecked_value' => '0',
            ],
            'attributes' => [
                'class' => 'form-check-input',
                'id' => 'activo',
            ],
        ]);

        //------------------------------------------------------------------------------

        $this->add([
            'type' => Element\Number::class,
            'name' => 'id',
            'options' => [
                'label' => 'ID',
            ],
            'attributes' => [
                'readonly' => true,
                'style' => "font-weight: bold",
                'class' => 'form-control',
                'id' => 'id',
            ],
        ]);

        $this->add([
            'type' => Element\Text::class,
            'name' => 'fecha_creacion',
            'options' => [
                'label' => 'Fecha Registro',
            ],
            'attributes' => [
                'readonly' => true,
                'class' => 'form-control',
                'id' => 'fecha_creacion',
            ],
        ]);

        $this->add([
            'type' => Element\Text::class,
            'name' => 'fecha_actualizacion',
            'options' => [
                'label' => 'Fecha Actualizacion',
            ],
            'attributes' => [
                'readonly' => true,
                'class' => 'form-control',
                'id' => 'fecha_actualizacion',
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
        //------------------------------------------------------------------------------

    }
}
