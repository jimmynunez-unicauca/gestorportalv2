<?php

namespace Administracion\Formularios;

use Laminas\Form\Form;
use Laminas\Form\Element;

class ProgramaacademicoForm extends Form
{
    public function __construct($accion = '', array $facultades = [])
    {
        switch ($accion) {
            case 'registrar':
            case 'editar':
                $onsubmit = 'return validarGuardar(event, this,"' . strtoupper($accion) . '")';
                $readonly = false;
                $required = true;
                break;
            default:
                $onsubmit = '';
                $readonly = true;
                $required = false;
        }

        parent::__construct('formProgramaacademico');
        $this->setAttribute('method', 'post');
        $this->setAttribute('data-toggle', 'validator');
        $this->setAttribute('role', 'form');
        $this->setAttribute('action', $accion);
        $this->setAttribute('onsubmit', $onsubmit);

        $this->add([
            'type' => Element\Number::class,
            'name' => 'idPrograma',
            'options' => ['label' => 'ID'],
            'attributes' => ['readonly' => true, 'class' => 'form-control', 'id' => 'idPrograma'],
        ]);

        // Select de facultades
        $opcionesFacultad = ['' => '-- SELECCIONE --'];
        foreach ($facultades as $f) {
            $opcionesFacultad[(string) $f['idFacultad']] = $f['facultad'];
        }

        $this->add([
            'type' => Element\Select::class,
            'name' => 'idFacultad',
            'options' => [
                'label' => 'Facultad *',
                'value_options' => $opcionesFacultad,
            ],
            'attributes' => [
                'required' => $required,
                'disabled' => $readonly,
                'class'    => 'form-control',
                'id'       => 'idFacultad',
            ],
        ]);

        $this->add([
            'type' => Element\Text::class,
            'name' => 'programa',
            'options' => ['label' => 'Programa *'],
            'attributes' => [
                'maxlength' => 100,
                'readonly'  => $readonly,
                'required'  => $required,
                'class'     => 'form-control',
                'id'        => 'programa',
            ],
        ]);
    }
}
