<?php

namespace Administracion\Formularios;

use Laminas\Form\Form;
use Laminas\Form\Element;

class ArchivoForm extends Form
{

    public function __construct($accion = '', $listaDepenedencias = array())
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
                $required = true;
                $disabled = true;
                break;
            default:
                $onsubmit = '';
                $required = false;
                $disabled = false;
                break;
        }

        parent::__construct('formArchivo');
        $this->setAttribute('method', 'post');
        $this->setAttribute('data-toggle', 'validator');
        $this->setAttribute('role', 'form');
        $this->setAttribute('enctype', 'multipart/form-data');
        $this->setAttribute('action', $accion);
        $this->setAttribute('onsubmit', $onsubmit);

        $this->add([
            'type' => Element\Select::class,
            'name' => 'idEmitido',
            'options' => [
                'label' => 'Dependencias *',
                'empty_option' => 'Seleccione...',
                'value_options' => $listaDepenedencias,
                'disable_inarray_validator' => true,
            ],
            'attributes' => [
                'required' => true,
                'class' => 'form-control',
                'id' => 'idEmitido',
            ],
        ]);
        $this->add([
            'type' => Element\Text::class,
            'name' => 'idResolucion',
            'options' => [
                'label' => 'Resolucion',
            ],
            'attributes' => [
                'readonly' => !$required,
                'required' => !$required,
                'class' => 'form-control',
                'id' => 'idResolucion',
            ],
        ]);
        $this->add([
            'type' => Element\Textarea::class,
            'name' => 'nombre',
            'options' => [
                'label' => 'Tituo *',
            ],
            'attributes' => [
                /* 'maxlength' => 200, */
                'readonly' => !$required,
                'required' => $required,
                'class' => 'form-control',
                'id' => 'nombre',
            ],
        ]);
        $this->add([
            'type' => Element\Textarea::class,
            'name' => 'descripcion',
            'options' => [
                'label' => 'Descripcion *',
            ],
            'attributes' => [
                /* 'maxlength' => 200, */
                'readonly' => !$required,
                'required' => $required,
                'class' => 'form-control',
                'id' => 'descripcion',
            ],
        ]);
        $this->add([
            'type' => Element\Select::class,
            'name' => 'tipo',
            'options' => [
                'label' => 'Tipo *',
                'empty_option' => 'Seleccione...',
                'value_options' => [
                    'Comunicados' => 'Comunicados',
                    'Resoluciones' => 'Resoluciones',
                    'Circulares' => 'Circulares',
                    'Acuerdos' => 'Acuerdos',
                    'Convocatorias' => 'Convocatorias',
                ],
                'disable_inarray_validator' => true,
            ],
            'attributes' => [
                'onchange' => 'selectTipo(this.value)',
                'readonly' => !$required,
                'required' => $required,
                'class' => 'form-control',
                'id' => 'tipo',
            ],
        ]);
        $this->add([
            'type' => Element\Date::class,
            'name' => 'publicacion',
            'options' => [
                'label' => 'Publicacion *',
            ],
            'attributes' => [
                'class' => 'form-control',
                'readonly' => !$required,
                'required' => $required,
                'id' => 'publicacion',
            ],
        ]);

        $this->add([
            'type' => Element\Text::class,
            'name' => 'inicioConvocatoria',
            'options' => [
                'label' => 'Inicio Convocatoria *',
            ],
            'attributes' => [
                'readonly' => !$required,
                'required' => !$required,
                'class' => 'form-control',
                'id' => 'inicioConvocatoria',
            ],
        ]);

        $this->add([
            'type' => Element\Text::class,
            'name' => 'finConvocatoria',
            'options' => [
                'label' => 'Fin Convocatoria *',
            ],
            'attributes' => [
                'readonly' => !$required,
                'required' => !$required,
                'class' => 'form-control',
                'id' => 'finConvocatoria',
            ],
        ]);
        /* $this->add([
            'type' => Element\Date::class,
            'name' => 'inicioConvocatoria',
            'options' => [
                'label' => 'Inicio Convocatoria *',
            ],
            'attributes' => [
                'class' => 'form-control',
                'readonly' => !$required,
                'required' => !$required,
                'id' => 'inicioConvocatoria',
            ],
        ]);
        $this->add([
            'type' => Element\Date::class,
            'name' => 'finConvocatoria',
            'options' => [
                'label' => 'Fin Convocatoria *',
            ],
            'attributes' => [
                'class' => 'form-control',
                'readonly' => !$required,
                'required' => !$required,
                'id' => 'finConvocatoria',
            ],
        ]); */
        $this->add([
            'type' => Element\File::class,
            'name' => 'archivo',
            'options' => [
                'label' => 'Archivo: pdf, docx, xlsx, pptx y zip',
            ],
            'attributes' => [
                'title' => 'Archivos permitidos: pdf, docx, xlsx, pptx y zip',
                'onchange' => 'validarAdjunto()',
                'readonly' => !$required,
                'required' => $required,
                'accept' => '.pdf,.docx,.xlsx,.pptx,.zip',
                'class' => 'form-control',
                'id' => 'archivo',
            ],
        ]);


        //------------------------------------------------------------------------------

        $this->add([
            'type' => Element\Number::class,
            'name' => 'idArchivo',
            'options' => [
                'label' => 'idArchivo',
            ],
            'attributes' => [
                'readonly' => true,
                'style' => "font-weight: bold",
                'class' => 'form-control',
                'id' => 'idArchivo',
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
