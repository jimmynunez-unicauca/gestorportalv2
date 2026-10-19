<?php

namespace Administracion\Formularios;

use Laminas\Form\Form;
use Laminas\Form\Element;

class VriproyectosForm extends Form
{
    public function __construct($accion = '', array $convocatoriasDisponibles = [])
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
            default:
                $onsubmit = '';
                $required = false;
                $disabled = false;
                break;
        }

        parent::__construct('formVriproyectos');
        $this->setAttribute('method', 'post');
        $this->setAttribute('data-toggle', 'validator');
        $this->setAttribute('role', 'form');
        $this->setAttribute('action', $accion);
        $this->setAttribute('onsubmit', $onsubmit);

        $this->add([
            'type' => Element\Text::class,
            'name' => 'titulo',
            'options' => ['label' => 'Título del proyecto *'],
            'attributes' => [
                'readonly' => !$required,
                'required' => $required,
                'maxlength' => 500,
                'class' => 'form-control',
                'id' => 'titulo',
            ],
        ]);
        $this->add([
            'type' => Element\Textarea::class,
            'name' => 'descripcion',
            'options' => ['label' => 'Descripción *'],
            'attributes' => [
                'readonly' => !$required,
                'required' => $required,
                'rows' => 3,
                'class' => 'form-control',
                'id' => 'descripcion',
            ],
        ]);
        $this->add([
            'type' => Element\Text::class,
            'name' => 'id_asignado',
            'options' => ['label' => 'Código único asignado *'],
            'attributes' => [
                'readonly' => !$required,
                'required' => $required,
                'maxlength' => 25,
                'class' => 'form-control',
                'id' => 'id_asignado',
            ],
        ]);
        $this->add([
            'type' => Element\Select::class,
            'name' => 'estado_proyecto',
            'options' => [
                'label' => 'Estado del Proyecto *',
                'empty_option' => 'Seleccione...',
                'value_options' => [
                    'Formulación' => 'Formulación',
                    'En Ejecución' => 'En Ejecución',
                    'Aprobado'    => 'Aprobado',
                    'Prórroga'    => 'Prórroga',
                    'Terminado'   => 'Terminado',
                    'Finalizado'  => 'Finalizado',
                ],
                'disable_inarray_validator' => true,
            ],
            'attributes' => [
                'readonly' => !$required,
                'required' => $required,
                'class' => 'form-control',
                'id' => 'estado_proyecto',
            ],
        ]);
        $this->add([
            'type' => Element\Text::class,
            'name' => 'grupo_investigacion',
            'options' => ['label' => 'Grupo de investigación *'],
            'attributes' => [
                'readonly' => !$required,
                'required' => $required,
                'class' => 'form-control',
                'id' => 'grupo_investigacion',
            ],
        ]);
        $this->add([
            'type' => Element\Text::class,
            'name' => 'director',
            'options' => ['label' => 'Director del proyecto *'],
            'attributes' => [
                'readonly' => !$required,
                'required' => $required,
                'maxlength' => 150,
                'class' => 'form-control',
                'id' => 'director',
            ],
        ]);
        $this->add([
            'type' => Element\Email::class,
            'name' => 'correo',
            'options' => ['label' => 'Correo electrónico *'],
            'attributes' => [
                'readonly' => !$required,
                'required' => $required,
                'maxlength' => 150,
                'class' => 'form-control',
                'id' => 'correo',
            ],
        ]);
        $this->add([
            'type' => Element\Date::class,
            'name' => 'fecha',
            'options' => ['label' => 'Fecha del proyecto *'],
            'attributes' => [
                'readonly' => !$required,
                'required' => $required,
                'class' => 'form-control',
                'id' => 'fecha',
            ],
        ]);
        $this->add([
            'type' => Element\Select::class,
            'name' => 'tipo_proyecto',
            'options' => [
                'label' => 'Tipo de Proyecto *',
                'empty_option' => 'Seleccione...',
                'value_options' => [
                    'Proyectos del Sistema General de Regalías (SGR)'                  => 'Proyectos del Sistema General de Regalías (SGR)',
                    'Proyectos externos (convenios con ministerios e instituciones externas)' => 'Proyectos externos (convenios con ministerios e instituciones externas)',
                    'Proyectos de desarrollo interno'                                    => 'Proyectos de desarrollo interno',
                    'Apropiación Social del Conocimiento proyectos de desarrollo interno' => 'Apropiación Social del Conocimiento proyectos de desarrollo interno',
                    'Apropiación Social del Conocimiento proyectos de convocatorias interno' => 'Apropiación Social del Conocimiento proyectos de convocatorias interno',
                    'Emprendimiento de desarrollo interno'                              => 'Emprendimiento de desarrollo interno',
                    'Emprendimiento de convocatoria interna'                            => 'Emprendimiento de convocatoria interna',
                    'Transferencia de proyectos de convocatoria interno'                => 'Transferencia de proyectos de convocatoria interno',
                    'Proyectos de convocatorias internas'                               => 'Proyectos de convocatorias internas',
                ],
                'disable_inarray_validator' => true,
            ],
            'attributes' => [
                'readonly' => !$required,
                'required' => $required,
                'class' => 'form-control',
                'id' => 'tipo_proyecto',
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

        // ---- Convocatorias asociadas (select) ----
        $opcionesConvocatorias = [];
        foreach ($convocatoriasDisponibles as $c) {
            $opcionesConvocatorias[(int)$c['id']] = $c['nombre'] . ' — ' . substr($c['titulo'], 0, 80);
        }
        $this->add([
            'type' => Element\Select::class,
            'name' => 'convocatorias',
            'options' => [
                'label' => 'Convocatorias asociadas (opcional)',
                'value_options' => $opcionesConvocatorias,
                'disable_inarray_validator' => true,
            ],
            'attributes' => [
                'multiple' => 'multiple',
                'size' => 6,
                'class' => 'form-control',
                'id' => 'convocatorias',
                'disabled' => $disabled,
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
