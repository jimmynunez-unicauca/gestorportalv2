<?php

namespace Administracion\Formularios;

use Laminas\Form\Form;
use Laminas\Form\Element;

class VriconvocatoriasForm extends Form
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
                $onsubmit = 'return validarGuardar(event, this,"ACTUALIZAR DOCUMENTO")';
                $required = false;
                $disabled = true;
                break;
            default:
                $onsubmit = '';
                $required = false;
                $disabled = false;
                break;
        }

        parent::__construct('formVriconvocatorias');
        $this->setAttribute('method', 'post');
        $this->setAttribute('data-toggle', 'validator');
        $this->setAttribute('role', 'form');
        $this->setAttribute('enctype', 'multipart/form-data');
        $this->setAttribute('action', $accion);
        $this->setAttribute('onsubmit', $onsubmit);

        $this->add([
            'type' => Element\Text::class,
            'name' => 'nombre',
            'options' => ['label' => 'Nombre corto *'],
            'attributes' => [
                'readonly' => !$required,
                'required' => $required,
                'maxlength' => 200,
                'class' => 'form-control',
                'id' => 'nombre',
                'placeholder' => 'Ej. Convocatoria VRI N° 004 de 2025',
            ],
        ]);
        $this->add([
            'type' => Element\Textarea::class,
            'name' => 'titulo',
            'options' => ['label' => 'Título completo *'],
            'attributes' => [
                'readonly' => !$required,
                'required' => $required,
                'maxlength' => 300,
                'rows' => 3,
                'class' => 'form-control',
                'id' => 'titulo',
            ],
        ]);
        $this->add([
            'type' => Element\Date::class,
            'name' => 'fecha',
            'options' => ['label' => 'Fecha principal *'],
            'attributes' => [
                'readonly' => !$required,
                'required' => $required,
                'class' => 'form-control',
                'id' => 'fecha',
            ],
        ]);
        $this->add([
            'type' => Element\File::class,
            'name' => 'documento',
            'options' => ['label' => 'Documento (PDF/DOC/DOCX)'],
            'attributes' => [
                'title' => 'Archivos permitidos: .pdf, .doc, .docx',
                'readonly' => !$required,
                'required' => false,
                'accept' => '.pdf,.doc,.docx',
                'class' => 'form-control',
                'id' => 'documento',
            ],
        ]);
        $this->add([
            'type' => Element\Url::class,
            'name' => 'enlace',
            'options' => ['label' => 'Enlace externo (opcional)'],
            'attributes' => [
                'readonly' => !$required,
                'required' => false,
                'maxlength' => 255,
                'class' => 'form-control',
                'id' => 'enlace',
                'placeholder' => 'https://...',
            ],
        ]);
        $this->add([
            'type' => Element\Number::class,
            'name' => 'id',
            'options' => ['label' => 'ID'],
            'attributes' => ['readonly' => true, 'style' => 'font-weight: bold', 'class' => 'form-control', 'id' => 'id'],
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
