<?php

namespace Administracion\Formularios;

use Laminas\Form\Form;
use Laminas\Form\Element;

class ComarcaForm extends Form
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
            case 'actualizarimagen':
                $onsubmit = 'return validarGuardar(event, this,"EDITAR")';
                $required = true;
                $disabled = false;
                break;
            case 'actualizaraudio':
                $onsubmit = 'return validarGuardar(event, this,"EDITAR")';
                $required = true;
                $disabled = false;
                break;
            case 'actualizarvideo':
                $onsubmit = 'return validarGuardar(event, this,"EDITAR")';
                $required = true;
                $disabled = false;
                break;
            default:
                $onsubmit = '';
                $required = false;
                $disabled = false;
                break;
        }

        parent::__construct('formComarca');
        $this->setAttribute('method', 'post');
        $this->setAttribute('data-toggle', 'validator');
        $this->setAttribute('role', 'form');
        $this->setAttribute('enctype', 'multipart/form-data');
        $this->setAttribute('action', $accion);
        $this->setAttribute('onsubmit', $onsubmit);

        $this->add([
            'type' => Element\Text::class,
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
            'type' => Element\Textarea::class,
            'name' => 'detalle',
            'options' => [
                'label' => 'Contenido *',
            ],
            'attributes' => [
                'maxlength' => 100,
                'readonly' => !$required,
                'required' => $required,
                'class' => 'form-control',
                'id' => 'detalle',
            ],
        ]);
        $this->add([
            'type' => Element\Text::class,
            'name' => 'autor',
            'options' => [
                'label' => 'Autor *',
            ],
            'attributes' => [
                /* 'maxlength' => 200, */
                'readonly' => !$required,
                'required' => $required,
                'class' => 'form-control',
                'id' => 'autor',
            ],
        ]);
        $this->add([
            'type' => Element\Date::class,
            'name' => 'fecha',
            'options' => [
                'label' => 'Publicación *',
            ],
            'attributes' => [
                'class' => 'form-control',
                'readonly' => !$required,
                'required' => $required,
                'id' => 'fecha',
            ],
        ]);
        $this->add([
            'type' => Element\File::class,
            'name' => 'imagen',
            'options' => [
                'label' => 'Imagen',
            ],
            'attributes' => [
                'onchange' => 'validarImagen()',
                'readonly' => !$required,
                'required' => !$required,
                'accept' => 'image/*',
                'class' => 'form-control',
                'id' => 'imagen',
            ],
        ]);
        $this->add([
            'type' => Element\Text::class,
            'name' => 'audio_url',
            'options' => [
                'label' => 'Spotify URL *',
            ],
            'attributes' => [
                'readonly' => !$required,
                'required' => !$required,
                'class' => 'form-control',
                'id' => 'audio_url',
            ],
        ]);
        $this->add([
            'type' => Element\Text::class,
            'name' => 'audio_id',
            'options' => [
                'label' => 'Spotify ID *',
            ],
            'attributes' => [
                'maxlength' => 100,
                'readonly' => !$required,
                'required' => !$required,
                'class' => 'form-control',
                'id' => 'audio_id',
            ],
        ]);
        $this->add([
            'type' => Element\Text::class,
            'name' => 'video_url',
            'options' => [
                'label' => 'Youtube URL *',
            ],
            'attributes' => [
                'readonly' => !$required,
                'required' => !$required,
                'class' => 'form-control',
                'id' => 'video_url',
            ],
        ]);
        $this->add([
            'type' => Element\Text::class,
            'name' => 'video_id',
            'options' => [
                'label' => 'Youtube ID *',
            ],
            'attributes' => [
                'maxlength' => 100,
                'readonly' => !$required,
                'required' => !$required,
                'class' => 'form-control',
                'id' => 'video_id',
            ],
        ]);
        $this->add([
            'type' => Element\Select::class,
            'name' => 'tipo',
            'options' => [
                'label' => 'Categoría *',
                'empty_option' => 'Seleccione...',
                'value_options' => [
                    'Podcast' => 'Podcast',
                    'Video' => 'Video',
                    'Audiolibro' => 'Audiolibro',
                    'Clase' => 'Clase',
                    'Conferencia' => 'Conferencia',
                ],
                'disable_inarray_validator' => true,
            ],
            'attributes' => [
                'onchange' => 'tipoArchivo(this.value)',
                'readonly' => !$required,
                'required' => $required,
                'class' => 'form-control',
                'id' => 'tipo',
            ],
        ]);


        //------------------------------------------------------------------------------

        $this->add([
            'type' => Element\Number::class,
            'name' => 'idPodcastComarca',
            'options' => [
                'label' => 'ID',
            ],
            'attributes' => [
                'readonly' => true,
                'style' => "font-weight: bold",
                'class' => 'form-control',
                'id' => 'idPodcastComarca',
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
