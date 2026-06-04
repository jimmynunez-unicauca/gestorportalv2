<?php

namespace Talentohumano\Formularios;

use Laminas\Form\Form;
use Laminas\Form\Element;

class LocalizacionForm extends Form
{

    public function __construct($departamentos = array(), $municipios = array())
    {
        parent::__construct('formLocalizacion');
        $this->setAttribute('method', 'post');
        $this->setAttribute('data-toggle', 'validator');
        $this->setAttribute('role', 'form');
        $this->setAttribute('enctype', 'multipart/form-data');
        $this->setAttribute('action', '');

        $this->add([
            'type' => Element\Select::class,
            'name' => 'idDepartamento',
            'options' => [
                'label' => 'Departamento *',
                'empty_option' => 'Seleccione...',
                'value_options' => $departamentos,
                'disable_inarray_validator' => true,
            ],
            'attributes' => [
                'onchange' => 'getMunicipios(this.value)',
                'required' => true,
                'class' => 'form-control',
                'id' => 'idDepartamento',
            ],
        ]);
        $this->add([
            'type' => Element\Select::class,
            'name' => 'idMunicipio',
            'options' => [
                'label' => 'Municipio *',
                'empty_option' => 'Seleccione...',
                'value_options' => $municipios,
                'disable_inarray_validator' => true,
            ],
            'attributes' => [
                'required' => true,
                'class' => 'form-control',
                'id' => 'idMunicipio',
            ],
        ]);
    }
}
