<?php

namespace Administracion\Formularios;

use Laminas\Form\Form;
use Laminas\Form\Element;

class ProcesoForm extends Form
{

    public function __construct($proceso = array(), $tipo = array(), $subProceso = array())
    {

        parent::__construct('formProceso');
        $this->setAttribute('method', 'post');
        $this->setAttribute('data-toggle', 'validator');
        $this->setAttribute('role', 'form');
        $this->setAttribute('enctype', 'multipart/form-data');
        $this->setAttribute('action', '');

        $this->add([
            'type' => Element\Select::class,
            'name' => 'idProceso',
            'options' => [
                'label' => 'Procesos *',
                'empty_option' => 'Seleccione...',
                'value_options' => $proceso,
                'disable_inarray_validator' => true,
            ],
            'attributes' => [
                'required' => true,
                'class' => 'form-control',
                'onchange' => 'getTipoProceso(this.value)',
                'id' => 'idProceso',
            ],
        ]);
        $this->add([
            'type' => Element\Select::class,
            'name' => 'idTipoProceso',
            'options' => [
                'label' => 'Tipo procesos *',
                'empty_option' => 'Seleccione...',
                'value_options' => $tipo,
                'disable_inarray_validator' => true,
            ],
            'attributes' => [
                'required' => true,
                'class' => 'form-control',
                'onchange' => 'getSubProceso(this.value)',
                'id' => 'idTipoProceso',
            ],
        ]);
        $this->add([
            'type' => Element\Select::class,
            'name' => 'idSubproceso',
            'options' => [
                'label' => 'Subprocesos *',
                'empty_option' => 'Seleccione...',
                'value_options' => $subProceso,
                'disable_inarray_validator' => true,
            ],
            'attributes' => [
                'required' => true,
                'class' => 'form-control',
                'id' => 'idSubproceso',
            ],
        ]);
    }
}
