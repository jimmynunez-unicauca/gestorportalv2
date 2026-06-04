<?php

namespace Documentos\Formularios;

use Laminas\Form\Form;
use Laminas\Form\Element;

class OcdiForm extends Form
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

        parent::__construct('formOcdi');
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
                'maxlength' => 200,
                'readonly' => !$required,
                'required' => $required,
                'class' => 'form-control',
                'id' => 'titulo',
            ],
        ]);
        $this->add([
            'type' => Element\Date::class,
            'name' => 'fecha',
            'options' => [
                'label' => 'Publicación *',
                'format' => 'Y-m-d',
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
            'name' => 'documento',
            'options' => [
                'label' => 'Documento: pdf',
            ],
            'attributes' => [
                'title' => 'Archivos permitidos: pdf',
                'onchange' => 'validarPDF(this)',
                'readonly' => !$required,
                'required' => $required,
                'accept' => '.pdf',
                'class' => 'form-control',
                'id' => 'documento',
            ],
        ]);
        $this->add([
            'type' => Element\File::class,
            'name' => 'imagen',
            'options' => [
                'label' => 'Imagen',
            ],
            'attributes' => [
                'onchange' => 'validarImagen(this)',
                'readonly' => !$required,
                'required' => !$required,
                'accept' => 'image/*',
                'class' => 'form-control',
                'id' => 'imagen',
            ],
        ]);
        //------------------------------------------------------------------------------

        $this->add([
            'type' => Element\Number::class,
            'name' => 'idRevistasOcdi',
            'options' => [
                'label' => 'ID',
            ],
            'attributes' => [
                'readonly' => true,
                'style' => "font-weight: bold",
                'class' => 'form-control',
                'id' => 'idRevistasOcdi',
            ],
        ]);
        if ($accion == 'editar') {
            $this->add([
                'type' => Element\Select::class,
                'name' => 'estado',
                'options' => [
                    'label' => 'Tipo *',
                    'empty_option' => 'Seleccione...',
                    'value_options' => [
                        'Activo' => 'Activo',
                        'Eliminado' => 'Eliminado',
                    ],
                    'disable_inarray_validator' => true,
                ],
                'attributes' => [
                    'readonly' => !$required,
                    'required' => $required,
                    'class' => 'form-control',
                    'id' => 'estado',
                ],
            ]);
        } else {
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
        }

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
