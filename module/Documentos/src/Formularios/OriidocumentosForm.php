<?php

namespace Documentos\Formularios;

use Laminas\Form\Form;
use Laminas\Form\Element;

class OriidocumentosForm extends Form
{

    public function __construct($accion = '', $listaInsti = array())
    {
        switch ($accion) {
            case 'subirdocumento':
                $onsubmit = 'return validarGuardar(event, this,"REGISTRAR")';
                $required = true;
                $disabled = false;
                break;
            case 'editar':
                $onsubmit = 'return validarGuardar(event, this,"EDOriiR")';
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
                $onsubmit = 'return validarGuardar(event, this,"EDOriiR")';
                $required = false;
                $disabled = true;
                break;
            default:
                $onsubmit = '';
                $required = false;
                $disabled = false;
                break;
        }

        parent::__construct('formOriidocumentos');
        $this->setAttribute('method', 'post');
        $this->setAttribute('data-toggle', 'validator');
        $this->setAttribute('role', 'form');
        $this->setAttribute('enctype', 'multipart/form-data');
        $this->setAttribute('action', $accion);
        $this->setAttribute('onsubmit', $onsubmit);

        $this->add([
            'type' => Element\Text::class,
            'name' => 'nombre_documento',
            'options' => [
                'label' => 'Nombre del Documento *',
            ],
            'attributes' => [
                'maxlength' => 500,
                'readonly' => !$required,
                'required' => $required,
                'class' => 'form-control',
                'id' => 'nombre_documento',
            ],
        ]);
        $this->add([
            'type' => Element\File::class,
            'name' => 'documento',
            'options' => [
                'label' => 'Archivo: pdf',
            ],
            'attributes' => [
                'title' => 'Archivos permitidos: pdf',
                'onchange' => 'validarAdjunto()',
                'readonly' => !$required,
                'required' => $required,
                'accept' => '.pdf',
                'class' => 'form-control',
                'id' => 'documento',
            ],
        ]);

        //------------------------------------------------------------------------------
        $this->add([
            'type' => Element\Number::class,
            'name' => 'id_documentos_orii',
            'options' => [
                'label' => 'ID',
            ],
            'attributes' => [
                'readonly' => true,
                'style' => "font-weight: bold",
                'class' => 'form-control',
                'id' => 'id_documentos_orii',
            ],
        ]);
        $this->add([
            'type' => Element\Number::class,
            'name' => 'idOrii',
            'options' => [
                'label' => 'ID ORII',
            ],
            'attributes' => [
                'readonly' => true,
                'style' => "font-weight: bold",
                'class' => 'form-control',
                'id' => 'idOrii',
            ],
        ]);
        $this->add([
            'type' => Element\Text::class,
            'name' => 'estado_documento',
            'options' => [
                'label' => 'Estado',
            ],
            'attributes' => [
                'readonly' => true,
                'class' => 'form-control',
                'id' => 'estado_documento',
            ],
        ]);

        $this->add([
            'type' => Element\Text::class,
            'name' => 'registradopor_documento',
            'options' => [
                'label' => 'Registrado Por',
            ],
            'attributes' => [
                'readonly' => true,
                'class' => 'form-control',
                'id' => 'registradopor_documento',
            ],
        ]);

        $this->add([
            'type' => Element\Text::class,
            'name' => 'fechahorareg_documento',
            'options' => [
                'label' => 'Fecha Registro',
            ],
            'attributes' => [
                'readonly' => true,
                'class' => 'form-control',
                'id' => 'fechahorareg_documento',
            ],
        ]);
    }
}
