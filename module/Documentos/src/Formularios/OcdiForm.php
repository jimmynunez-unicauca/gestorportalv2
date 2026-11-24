<?php

namespace Documentos\Formularios;

use Laminas\Form\Form;
use Laminas\Form\Element;

class OriiForm extends Form
{
    public function __construct($accion = '', $listaInsti = [])
    {
        // ─── Configuración por acción ─────────────────────────────────
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

        parent::__construct('formOrii');
        $this->setAttribute('method', 'post');
        $this->setAttribute('data-toggle', 'validator');
        $this->setAttribute('role', 'form');
        $this->setAttribute('enctype', 'multipart/form-data');
        $this->setAttribute('action', $accion);
        $this->setAttribute('onsubmit', $onsubmit);

        // ─── Campos del formulario ────────────────────────────────────
        $this->add([
            'type' => Element\Select::class,
            'name' => 'idInstituto',
            'options' => [
                'label' => 'Instituciones *',
                'empty_option' => 'Seleccione...',
                'value_options' => $listaInsti,
                'disable_inarray_validator' => true,
            ],
            'attributes' => [
                'required' => $required,
                'class' => 'form-control',
                'id' => 'idInstituto',
            ],
        ]);

        $this->add([
            'type' => Element\Text::class,
            'name' => 'numero_convenio',
            'options' => ['label' => 'Número de Convenio *'],
            'attributes' => [
                'maxlength' => 100,
                'readonly' => !$required,
                'required' => $required,
                'class' => 'form-control',
                'id' => 'numero_convenio',
            ],
        ]);

        $this->add([
            'type' => Element\Text::class,
            'name' => 'nombre_convenio',
            'options' => ['label' => 'Nombre de Convenio *'],
            'attributes' => [
                'maxlength' => 200,
                'readonly' => !$required,
                'required' => $required,
                'class' => 'form-control',
                'id' => 'nombre_convenio',
            ],
        ]);

        $this->add([
            'type' => Element\Textarea::class,
            'name' => 'descripcion',
            'options' => ['label' => 'Descripción'],
            'attributes' => [
                'readonly' => !$required,
                'required' => false,
                'class' => 'form-control',
                'id' => 'descripcion',
            ],
        ]);

        $this->add([
            'type' => Element\Date::class,
            'name' => 'publicacion',
            'options' => [
                'label' => 'Publicación *',
                'format' => 'Y-m-d',
            ],
            'attributes' => [
                'class' => 'form-control',
                'readonly' => !$required,
                'required' => $required,
                'id' => 'publicacion',
            ],
        ]);

        $this->add([
            'type' => Element\Select::class,
            'name' => 'tipo',
            'options' => [
                'label' => 'Tipo *',
                'empty_option' => 'Seleccione...',
                'value_options' => [
                    'Marco' => 'Marco',
                    'Específico' => 'Específico',
                ],
                'disable_inarray_validator' => true,
            ],
            'attributes' => [
                'required' => $required,
                'class' => 'form-control',
                'id' => 'tipo',
            ],
        ]);

        // ─── ID (auditoría) ───────────────────────────────────────────
        $this->add([
            'type' => Element\Number::class,
            'name' => 'idOrii',
            'options' => ['label' => 'ID'],
            'attributes' => [
                'readonly' => true,
                'style' => 'font-weight: bold',
                'class' => 'form-control',
                'id' => 'idOrii',
            ],
        ]);

        // ─── Estado ───────────────────────────────────────────────────
        // IMPORTANTE: SIEMPRE required => false, así el InputFilter
        // automático del Form NUNCA lo exige.
        // ✅ CORREGIDO: comparación === (no asignación =)
        if ($accion === 'editar') {
            $this->add([
                'type' => Element\Select::class,
                'name' => 'estado',
                'options' => [
                    'label' => 'Estado',
                    'empty_option' => 'Seleccione...',
                    'value_options' => [
                        'Vigente'   => 'Vigente',
                        'Cancelado' => 'Cancelado',
                        'Eliminado' => 'Eliminado',
                    ],
                    'disable_inarray_validator' => true,
                ],
                'attributes' => [
                    'required' => false,           // ← NUNCA requerido
                    'class' => 'form-control',
                    'id' => 'estado',
                ],
            ]);
        } else {
            $this->add([
                'type' => Element\Text::class,
                'name' => 'estado',
                'options' => ['label' => 'Estado'],
                'attributes' => [
                    'readonly' => true,
                    'required' => false,           // ← NUNCA requerido
                    'class' => 'form-control',
                    'id' => 'estado',
                ],
            ]);
        }

        // ─── Auditoría (todos required => false) ─────────────────────
        $this->add([
            'type' => Element\Text::class,
            'name' => 'registradopor',
            'options' => ['label' => 'Registrado Por'],
            'attributes' => ['readonly' => true, 'required' => false, 'class' => 'form-control', 'id' => 'registradopor'],
        ]);
        $this->add([
            'type' => Element\Text::class,
            'name' => 'modificadopor',
            'options' => ['label' => 'Modificado Por'],
            'attributes' => ['readonly' => true, 'required' => false, 'class' => 'form-control', 'id' => 'modificadopor'],
        ]);
        $this->add([
            'type' => Element\Text::class,
            'name' => 'fechahorareg',
            'options' => ['label' => 'Fecha Registro'],
            'attributes' => ['readonly' => true, 'required' => false, 'class' => 'form-control', 'id' => 'fechahorareg'],
        ]);
        $this->add([
            'type' => Element\Text::class,
            'name' => 'fechahoramod',
            'options' => ['label' => 'Fecha Actualización'],
            'attributes' => ['readonly' => true, 'required' => false, 'class' => 'form-control', 'id' => 'fechahoramod'],
        ]);
    }
}
