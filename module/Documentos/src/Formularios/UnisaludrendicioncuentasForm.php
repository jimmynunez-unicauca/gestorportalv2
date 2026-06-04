<?php

namespace Documentos\Formularios;

use Laminas\Form\Form;
use Laminas\Form\Element;

class UnisaludrendicioncuentasForm extends Form
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

        parent::__construct('formUnisaludrendicioncuentas');
        $this->setAttribute('method', 'post');
        $this->setAttribute('data-toggle', 'validator');
        $this->setAttribute('role', 'form');
        $this->setAttribute('enctype', 'multipart/form-data');
        $this->setAttribute('action', $accion);
        $this->setAttribute('onsubmit', $onsubmit);

        $this->add([
            'type' => Element\Textarea::class,
            'name' => 'titulo',
            'options' => [
                'label' => 'Titulo *',
            ],
            'attributes' => [
                /* 'maxlength' => 200, */
                'readonly' => !$required,
                'required' => $required,
                'class' => 'form-control',
                'id' => 'titulo',
            ],
        ]);
        $this->add([
            'name' => 'fecha_publicacion',
            'type' => Element\DateTimeLocal::class,
            'options' => [
                'label' => 'Fecha Final',
            ],
            'attributes' => [
                'readonly' => !$required,
                'required' => $required,
                'class' => 'form-control',
                'id' => 'fecha_publicacion',
            ],
        ]);
        $this->add([
            'type' => Element\Select::class,
            'name' => 'tipo_documento',
            'options' => [
                'label' => 'Tipo *',
                'empty_option' => 'Seleccione...',
                'value_options' => [
                    'Informe de afiliaciones' => 'Informe de afiliaciones',
                    'Informe de PQRS' => 'Informe de PQRS',
                    'Encuesta de satisfacción' => 'Encuesta de satisfacción',
                    'Informe de gestión' => 'Informe de gestión',
                    'Rendición de cuentas' => 'Rendición de cuentas',
                    'Otros documentos de interés' => 'Otros documentos de interés',
                ],
                'disable_inarray_validator' => true,
            ],
            'attributes' => [
                'readonly' => !$required,
                'required' => $required,
                'class' => 'form-control',
                'id' => 'tipo_documento',
            ],
        ]);
        $this->add([
            'type' => Element\File::class,
            'name' => 'ruta_archivo',
            'options' => [
                'label' => 'Archivo: pdf, docx, xlsx y pptx',
            ],
            'attributes' => [
                'title' => 'Archivos permitidos: pdf, docx, xlsx y pptx',
                'onchange' => 'validarAdjunto()',
                'readonly' => !$required,
                'required' => $required,
                'accept' => '.pdf,.docx,.xlsx,.pptx',
                'class' => 'form-control',
                'id' => 'ruta_archivo',
            ],
        ]);
        $this->add([
            'type' => Element\Checkbox::class,
            'name' => 'activo',
            'options' => [
                'label' => 'Estado',
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
            'name' => 'creado_por',
            'options' => [
                'label' => 'Registrado Por',
            ],
            'attributes' => [
                'readonly' => true,
                'class' => 'form-control',
                'id' => 'creado_por',
            ],
        ]);

        $this->add([
            'type' => Element\Text::class,
            'name' => 'actualizado_por',
            'options' => [
                'label' => 'Modificado Por',
            ],
            'attributes' => [
                'readonly' => true,
                'class' => 'form-control',
                'id' => 'actualizado_por',
            ],
        ]);

        $this->add([
            'type' => Element\Text::class,
            'name' => 'creado_el',
            'options' => [
                'label' => 'Fecha Registro',
            ],
            'attributes' => [
                'readonly' => true,
                'class' => 'form-control',
                'id' => 'creado_el',
            ],
        ]);

        $this->add([
            'type' => Element\Text::class,
            'name' => 'actualizado_el',
            'options' => [
                'label' => 'Fecha Actualizacion',
            ],
            'attributes' => [
                'readonly' => true,
                'class' => 'form-control',
                'id' => 'actualizado_el',
            ],
        ]);
    }
}
