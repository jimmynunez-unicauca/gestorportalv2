<?php

namespace Administracion\Formularios;

use Laminas\Form\Form;
use Laminas\Form\Element;

class VriemprendimientosForm extends Form
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
                $onsubmit = 'return validarGuardar(event, this,"ACTUALIZAR IMAGEN")';
                $required = false;
                $disabled = true;
                break;
            default:
                $onsubmit = '';
                $required = false;
                $disabled = false;
                break;
        }

        parent::__construct('formVriemprendimientos');
        $this->setAttribute('method', 'post');
        $this->setAttribute('data-toggle', 'validator');
        $this->setAttribute('role', 'form');
        $this->setAttribute('enctype', 'multipart/form-data');
        $this->setAttribute('action', $accion);
        $this->setAttribute('onsubmit', $onsubmit);

        $this->add([
            'type' => Element\Text::class,
            'name' => 'nombre',
            'options' => [
                'label' => 'Nombre *',
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
            'name' => 'detalle',
            'options' => [
                'label' => 'Detalle *',
            ],
            'attributes' => [
                /* 'maxlength' => 200, */
                'readonly' => !$required,
                'required' => $required,
                'class' => 'form-control',
                'id' => 'detalle',
            ],
        ]);
        $this->add([
            'type' => Element\Select::class,
            'name' => 'tipoEmprendimiento',
            'options' => [
                'label' => 'Tipo de Emprendimiento *',
                'empty_option' => 'Seleccione...',
                'value_options' => [
                    'Tecnología' => 'Tecnología',
                    'Sostenible' => 'Sostenible',
                    'Creativo' => 'Creativo',
                    'Alimentos' => 'Alimentos',
                    'Servicios' => 'Servicios',
                    'Ciencia' => 'Ciencia',
                    'Mujeres emprendedoras' => 'Mujeres emprendedoras',
                ],
                'disable_inarray_validator' => true,
            ],
            'attributes' => [
                'readonly' => !$required,
                'required' => $required,
                'class' => 'form-control',
                'id' => 'tipoEmprendimiento',
            ],
        ]);
        $this->add([
            'type' => Element\Text::class,
            'name' => 'semillero',
            'options' => [
                'label' => 'Semillero *',
            ],
            'attributes' => [
                /* 'maxlength' => 200, */
                'readonly' => !$required,
                'required' => $required,
                'class' => 'form-control',
                'id' => 'semillero',
            ],
        ]);
        $this->add([
            'type' => Element\Textarea::class,
            'name' => 'productoServicio',
            'options' => [
                'label' => 'Producto/Servicio *',
            ],
            'attributes' => [
                /* 'maxlength' => 200, */
                'readonly' => !$required,
                'required' => $required,
                'class' => 'form-control',
                'id' => 'productoServicio',
            ],
        ]);
        $this->add([
            'type' => Element\Textarea::class,
            'name' => 'historia',
            'options' => [
                'label' => 'Historia *',
            ],
            'attributes' => [
                /* 'maxlength' => 200, */
                'readonly' => !$required,
                'required' => $required,
                'class' => 'form-control',
                'id' => 'historia',
            ],
        ]);
        $this->add([
            'type' => Element\Text::class,
            'name' => 'whatsapp',
            'options' => [
                'label' => 'WhatsApp *',
            ],
            'attributes' => [
                /* 'maxlength' => 200, */
                'readonly' => !$required,
                'required' => $required,
                'class' => 'form-control',
                'id' => 'whatsapp',
            ],
        ]);
        $this->add([
            'type' => Element\File::class,
            'name' => 'imagen',
            'options' => [
                'label' => 'Imagen *',
            ],
            'attributes' => [
                'title' => 'Archivos permitidos: .jpg, .jpeg, .png, .gif',
                'onchange' => 'validarImagen()',
                'readonly' => !$required,
                'required' => $required,
                'accept' => 'image/jpeg, image/png, image/gif',
                'class' => 'form-control',
                'id' => 'imagen',
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
