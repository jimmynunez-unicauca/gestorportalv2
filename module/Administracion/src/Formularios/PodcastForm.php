<?php

namespace Administracion\Formularios;

use Laminas\Form\Form;
use Laminas\Form\Element;

class PodcastForm extends Form
{
    public function __construct($accion = '', array $programas = [])
    {
        switch ($accion) {
            case 'registrar':
                $onsubmit = 'return validarGuardarPodcast(event, this, "REGISTRAR")';
                $required = true;
                $disabled = false;
                break;
            case 'editar':
                $onsubmit = 'return validarGuardarPodcast(event, this, "EDITAR")';
                $required = true;
                $disabled = false;
                break;
            case 'detalle':
                $onsubmit = '';
                $required = false;
                $disabled = true;
                break;
            case 'desactivar':
                $onsubmit = 'return validarGuardarPodcast(event, this, "DESACTIVAR")';
                $required = false;
                $disabled = true;
                break;
            case 'activar':
                $onsubmit = 'return validarGuardarPodcast(event, this, "ACTIVAR")';
                $required = false;
                $disabled = true;
                break;
            case 'eliminar':
                $onsubmit = 'return validarGuardarPodcast(event, this, "ELIMINAR")';
                $required = false;
                $disabled = true;
                break;
            default:
                $onsubmit = '';
                $required = false;
                $disabled = false;
        }

        parent::__construct('formPodcast');
        $this->setAttribute('method', 'post');
        $this->setAttribute('enctype', 'multipart/form-data');
        $this->setAttribute('data-toggle', 'validator');
        $this->setAttribute('role', 'form');
        $this->setAttribute('action', $accion);
        $this->setAttribute('onsubmit', $onsubmit);
        $this->setAttribute('id', 'formPodcast');

        // ---- Datos del podcast ----
        $this->add([
            'type' => Element\Text::class,
            'name' => 'titulo',
            'options' => ['label' => 'Título *'],
            'attributes' => [
                'maxlength' => 100,
                'readonly' => $disabled,
                'required' => $required,
                'class' => 'form-control',
                'id' => 'titulo',
            ],
        ]);

        $this->add([
            'type' => Element\Textarea::class,
            'name' => 'descripcion',
            'options' => ['label' => 'Descripción *'],
            'attributes' => [
                'readonly' => $disabled,
                'required' => $required,
                'class' => 'form-control',
                'rows' => 3,
                'id' => 'descripcion',
            ],
        ]);

        $opciones = ['' => 'Seleccione un programa'];
        foreach ($programas as $p) {
            $opciones[$p['idPrograma']] = $p['nombre_programa'];
        }
        $this->add([
            'type' => Element\Select::class,
            'name' => 'idPrograma',
            'options' => [
                'label' => 'Programa asociado *',
                'value_options' => $opciones,
            ],
            'attributes' => [
                'required' => $required,
                'class' => 'form-control',
                'disabled' => $disabled,
                'id' => 'idPrograma',
            ],
        ]);

        $this->add([
            'type' => Element\Date::class,
            'name' => 'transmision',
            'options' => ['label' => 'Fecha de transmisión *'],
            'attributes' => [
                'required' => $required,
                'class' => 'form-control',
                'readonly' => $disabled,
                'id' => 'transmision',
            ],
        ]);

        $this->add([
            'type' => Element\Text::class,
            'name' => 'categoria',
            'options' => ['label' => 'Categoría'],
            'attributes' => [
                'maxlength' => 45,
                'class' => 'form-control',
                'readonly' => $disabled,
                'id' => 'categoria',
            ],
        ]);

        // ---- Archivo MP3: solo al registrar o editar ----
        // En el controlador se procesa, pero la subida real la hace el navegador
        // directamente al servidor de la emisora. Ver podcast.js.
        if (in_array($accion, ['registrar', 'editar'], true)) {
            $this->add([
                'type' => Element\File::class,
                'name' => 'audio',
                'options' => ['label' => 'Archivo de audio (MP3)' . ($accion === 'registrar' ? ' *' : '')],
                'attributes' => [
                    'required' => $accion === 'registrar',
                    'accept' => '.mp3,audio/mpeg',
                    'class' => 'form-control',
                    'id' => 'audio',
                    'onchange' => 'validarAudioPodcast()',
                ],
            ]);
        }

        // ---- Imagen de portada: solo al registrar o editar ----
        if (in_array($accion, ['registrar', 'editar'], true)) {
            $this->add([
                'type' => Element\Hidden::class,
                'name' => 'imagen',
                'options' => ['label' => 'Imagen de portada'],
                'attributes' => [
                    'accept' => 'image/*',
                    'class' => 'form-control',
                    'id' => 'imagen',
                    'onchange' => 'validarImagenPodcast()',
                ],
            ]);
        } else {
            $this->add([
                'type' => Element\Text::class,
                'name' => 'imagen',
                'options' => ['label' => 'Imagen'],
                'attributes' => [
                    'readonly' => true,
                    'class' => 'form-control',
                    'id' => 'imagen',
                ],
            ]);
        }

        // ---- Campos ocultos y auditoría ----
        $this->add([
            'type' => Element\Hidden::class,
            'name' => 'idPodcast',
            'attributes' => ['id' => 'idPodcast'],
        ]);

        // audio_url: la llena el JS después de subir el MP3 al servidor B
        $this->add([
            'type' => Element\Hidden::class,
            'name' => 'audio_url',
            'attributes' => ['id' => 'audio_url'],
        ]);

        $this->add([
            'type' => Element\Text::class,
            'name' => 'estado',
            'options' => ['label' => 'Estado'],
            'attributes' => [
                'readonly' => true,
                'class' => 'form-control',
                'id' => 'estado',
            ],
        ]);
        $this->add([
            'type' => Element\Text::class,
            'name' => 'registradopor',
            'options' => ['label' => 'Registrado Por'],
            'attributes' => [
                'readonly' => true,
                'class' => 'form-control',
                'id' => 'registradopor',
            ],
        ]);
        $this->add([
            'type' => Element\Text::class,
            'name' => 'modificadopor',
            'options' => ['label' => 'Modificado Por'],
            'attributes' => [
                'readonly' => true,
                'class' => 'form-control',
                'id' => 'modificadopor',
            ],
        ]);
        $this->add([
            'type' => Element\Text::class,
            'name' => 'fechahorareg',
            'options' => ['label' => 'Fecha Registro'],
            'attributes' => [
                'readonly' => true,
                'class' => 'form-control',
                'id' => 'fechahorareg',
            ],
        ]);
        $this->add([
            'type' => Element\Text::class,
            'name' => 'fechahoramod',
            'options' => ['label' => 'Fecha Actualización'],
            'attributes' => [
                'readonly' => true,
                'class' => 'form-control',
                'id' => 'fechahoramod',
            ],
        ]);
    }
}
