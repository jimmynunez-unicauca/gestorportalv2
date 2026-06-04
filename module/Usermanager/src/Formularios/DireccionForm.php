<?php

namespace Contratos\Formularios;

use Laminas\Form\Form;
use Laminas\Form\Element;

class DireccionForm extends Form {

    public function __construct($accion = '', $tiposServicio = array()) {
        switch ($accion) {
            case 'registrar':
                $onsubmit = 'return validarRegistrarDireccion(event, this)';
                $required = true;
                $disabled = false;
                break;
            case 'editar':
                $onsubmit = 'return validarEditar()';
                $required = true;
                $disabled = false;
                break;
            case 'detalle':
                $onsubmit = '';
                $required = false;
                $disabled = true;
                break;
            case 'eliminar':
                $onsubmit = 'return validarEliminar()';
                $required = false;
                $disabled = true;
                break;
            default :
                $onsubmit = '';
                $required = false;
                $disabled = false;
                break;
        }

        parent::__construct('formDirecciones');
        $this->setAttribute('method', 'post');
        $this->setAttribute('data-toggle', 'validator');
        $this->setAttribute('role', 'form');
        $this->setAttribute('enctype', 'multipart/form-data');
        $this->setAttribute('action', $accion);
        $this->setAttribute('onsubmit', $onsubmit);

        $this->add([
            'type' => Element\Number::class,
            'name' => 'idMunicipio',
            'options' => [
                'label' => 'ID Municipio',
            ],
            'attributes' => [
                'readonly' => true,
                'class' => 'form-control',
                'id' => 'idMunicipio',
            ],
        ]);

    }

}
