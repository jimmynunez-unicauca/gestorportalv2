<?php

namespace Administracion\Formularios;

use Laminas\Form\Form;
use Laminas\Form\Element;

class ProgramaForm extends Form
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
            case 'desactivar':
                $onsubmit = 'return validarGuardar(event, this,"DESACTIVAR")';
                $required = false;
                $disabled = true;
                break;
            case 'activar':
                $onsubmit = 'return validarGuardar(event, this,"ACTIVAR")';
                $required = false;
                $disabled = true;
                break;
            case 'actualizarimagen':
                $onsubmit = 'return validarGuardar(event, this,"ACTUALIZAR IMAGEN")';
                $required = false;
                $disabled = true;
                break;
            case 'eliminar':
                $onsubmit = 'return validarGuardar(event, this,"ELIMINAR")';
                $required = false;
                $disabled = true;
                break;
            default:
                $onsubmit = '';
                $required = false;
                $disabled = false;
                break;
        }

        parent::__construct('formPrograma');
        $this->setAttribute('method', 'post');
        $this->setAttribute('data-toggle', 'validator');
        $this->setAttribute('role', 'form');
        $this->setAttribute('enctype', 'multipart/form-data');
        $this->setAttribute('action', $accion);
        $this->setAttribute('onsubmit', $onsubmit);

        $this->add([
            'type' => Element\Textarea::class,
            'name' => 'nombre_programa',
            'options' => ['label' => 'Título *'],
            'attributes' => [
                'maxlength' => 100,
                'readonly' => !$required,
                'required' => $required,
                'class' => 'form-control',
                'id' => 'nombre_programa',
            ],
        ]);

        $this->add([
            'type' => Element\Textarea::class,
            'name' => 'detalle_programa',
            'options' => ['label' => 'Contenido *'],
            'attributes' => [
                'readonly' => !$required,
                'required' => $required,
                'class' => 'form-control',
                'id' => 'detalle_programa',
            ],
        ]);

        $this->add([
            'type' => Element\Color::class,
            'name' => 'color_programa',
            'options' => ['label' => 'Color *'],
            'attributes' => [
                'maxlength' => 20,
                'value' => '#000066',
                'style' => 'text-transform: uppercase',
                'readonly' => !$required,
                'required' => $required,
                'class' => 'form-control',
                'id' => 'color_programa',
            ],
        ]);

        // Imagen: File solo en registrar y actualizarimagen
        $accionesConFile = ['registrar', 'actualizarimagen'];
        if (in_array($accion, $accionesConFile, true)) {
            $this->add([
                'type' => Element\File::class,
                'name' => 'imagen',
                'options' => ['label' => 'Imagen'],
                'attributes' => [
                    'onchange' => 'validarImagen()',
                    'required' => $accion === 'actualizarimagen',
                    'accept' => 'image/*',
                    'class' => 'form-control',
                    'id' => 'imagen',
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

        // Campos de auditoría
        $this->add([
            'type' => Element\Number::class,
            'name' => 'idPrograma',
            'options' => ['label' => 'ID'],
            'attributes' => ['readonly' => true, 'class' => 'form-control', 'id' => 'idPrograma'],
        ]);
        $this->add([
            'type' => Element\Text::class,
            'name' => 'estado',
            'options' => ['label' => 'Estado'],
            'attributes' => ['readonly' => true, 'class' => 'form-control', 'id' => 'estado'],
        ]);
        $this->add([
            'type' => Element\Text::class,
            'name' => 'registradopor',
            'options' => ['label' => 'Registrado Por'],
            'attributes' => ['readonly' => true, 'class' => 'form-control', 'id' => 'registradopor'],
        ]);
        $this->add([
            'type' => Element\Text::class,
            'name' => 'modificadopor',
            'options' => ['label' => 'Modificado Por'],
            'attributes' => ['readonly' => true, 'class' => 'form-control', 'id' => 'modificadopor'],
        ]);
        $this->add([
            'type' => Element\Text::class,
            'name' => 'fechahorareg',
            'options' => ['label' => 'Fecha Registro'],
            'attributes' => ['readonly' => true, 'class' => 'form-control', 'id' => 'fechahorareg'],
        ]);
        $this->add([
            'type' => Element\Text::class,
            'name' => 'fechahoramod',
            'options' => ['label' => 'Fecha Actualización'],
            'attributes' => ['readonly' => true, 'class' => 'form-control', 'id' => 'fechahoramod'],
        ]);
    }
}
