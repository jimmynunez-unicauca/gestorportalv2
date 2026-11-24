<?php

namespace Documentos\Modelo\DAO;

use Laminas\Db\TableGateway\AbstractTableGateway;
use Laminas\Db\Adapter\Adapter;
use Laminas\Db\Sql\Expression;
use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\Insert;
use Laminas\Db\Sql\Update;
use Laminas\Db\Sql\Delete;
use Documentos\Modelo\Entidades\Orii;
use Documentos\Modelo\Entidades\Oriidocumentos;

class OriiDAO extends AbstractTableGateway
{
    protected $table = 'orii';

    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
    }

    //==========================================================================
    // ORII
    //==========================================================================
    public function fetchAll($filtro = '')
    {
        $select = new Select('orii');
        $select->columns([
            'idOrii',
            'idInstituto',
            'numero_convenio',
            'nombre_convenio',
            'descripcion',
            'publicacion',
            'estado',
            'tipo',
            'registradopor',
            'modificadopor',
            'fechahorareg',
            'fechahoramod',
        ])
            ->join(
                'instituto',
                'instituto.idInstituto = orii.idInstituto',
                ['institucion']
            )
            ->join(
                'ciudades',
                'ciudades.idCiudades = instituto.idCiudades',
                ['ciudad', 'state_province']
            )
            ->join(
                'countries',
                'countries.id = ciudades.idCountries',
                ['pais']
            );

        if ($filtro != '') {
            $select->where($filtro);
        } else {
            $select->order('orii.idOrii DESC');
        }
        return $this->selectWith($select)->toArray();
    }

    public function getOriiDetalle($idOrii = 0)
    {
        $select = new Select('orii');
        $select->columns([
            'idOrii',
            'idInstituto',
            'numero_convenio',
            'nombre_convenio',
            'descripcion',
            'publicacion',
            'estado',
            'tipo',
            'registradopor',
            'modificadopor',
            'fechahorareg',
            'fechahoramod',
        ])
            ->join(
                'instituto',
                'instituto.idInstituto = orii.idInstituto',
                ['institucion']
            )
            ->join(
                'ciudades',
                'ciudades.idCiudades = instituto.idCiudades',
                ['ciudad', 'state_province']
            )
            ->join(
                'countries',
                'countries.id = ciudades.idCountries',
                ['pais']
            )
            ->where(['orii.idOrii' => (int) $idOrii])
            ->limit(1);
        $datos = $this->selectWith($select)->toArray();
        return count($datos) > 0 ? $datos[0] : null;
    }

    public function getOrii($idOrii = 0)
    {
        $select = new Select('orii');
        $select->where(['idOrii' => (int) $idOrii])->limit(1);
        $row = $this->selectWith($select)->current();
        return $row ? new Orii($row->getArrayCopy()) : null;
    }

    public function registrar(Orii $oriiOBJ)
    {
        $this->table = 'orii';
        $insert = new Insert('orii');
        $datos = $oriiOBJ->getArrayCopy();
        unset($datos['idOrii']);
        $insert->values($datos);
        $this->insertWith($insert);
    }

    public function editar(Orii $oriiOBJ)
    {
        $this->table = 'orii';
        $id = (int) $oriiOBJ->getIdOrii();
        $update = new Update('orii');
        $datos = $oriiOBJ->getArrayCopy();
        unset($datos['idOrii']);
        $update->set($datos);
        $update->where(['idOrii' => $id]);
        return $this->updateWith($update);
    }

    public function eliminarActivar(Orii $oriiOBJ, string $estado = '')
    {
        $this->table = 'orii';
        $update = new Update('orii');
        $update->set([
            'estado'        => $estado,
            'modificadopor' => $oriiOBJ->getModificadopor(),
            'fechahoramod'  => $oriiOBJ->getFechahoramod(),
        ]);
        $update->where(['idOrii' => (int) $oriiOBJ->getIdOrii()]);
        $this->updateWith($update);
    }

    public function borrarOrii($idOrii)
    {
        $this->table = 'orii';
        $delete = new Delete('orii');
        $delete->where(['idOrii' => (int) $idOrii]);
        $this->deleteWith($delete);
    }

    public function tieneDocumentos($idOrii)
    {
        $this->table = 'documentos_orii';
        $select = new Select('documentos_orii');
        $select->columns(['total' => new Expression('COUNT(*)')])
            ->where(['idOrii' => (int) $idOrii]);
        $row = $this->selectWith($select)->current();
        return $row && (int) $row['total'] > 0;
    }

    //==========================================================================
    // INSTITUCIONES (para el <select>)
    //==========================================================================
    public function getInstituciones()
    {
        $select = new Select('instituto');
        $select->columns(['idInstituto', 'institucion'])
            ->join(
                'ciudades',
                'ciudades.idCiudades = instituto.idCiudades',
                ['ciudad']
            )
            ->join(
                'countries',
                'countries.id = ciudades.idCountries',
                ['pais']
            )
            ->order(['pais ASC', 'ciudad ASC', 'institucion ASC']);
        return $this->selectWith($select)->toArray();
    }

    //==========================================================================
    // DOCUMENTOS ORII
    //==========================================================================
    public function getDocumento($id_documentos_orii)
    {
        $select = new Select('documentos_orii');
        $select->where(['id_documentos_orii' => (int) $id_documentos_orii])
            ->limit(1);
        $row = $this->selectWith($select)->current();
        return $row ? $row->getArrayCopy() : null;
    }

    /**
     * Lista los documentos de una ORII.
     * @param bool $incluirEliminados si true, devuelve también los 'Eliminado'
     */
    public function getOriiDocumentos($idOrii, bool $incluirEliminados = false)
    {
        $select = new Select('documentos_orii');
        $select->where(['idOrii' => (int) $idOrii]);
        if (!$incluirEliminados) {
            $select->where(['estado_documento' => 'Activo']);
        }
        $select->order('fechahorareg_documento DESC');
        return $this->selectWith($select)->toArray();
    }

    public function registrarDocumento(Oriidocumentos $obj)
    {
        $this->table = 'documentos_orii';
        $insert = new Insert('documentos_orii');
        $datos = $obj->getArrayCopy();
        unset($datos['id_documentos_orii']);
        $insert->values($datos);
        $this->insertWith($insert);
    }

    public function desactivarDocumento($id_documentos_orii)
    {
        $this->table = 'documentos_orii';
        $update = new Update('documentos_orii');
        $update->set(['estado_documento' => 'Eliminado']);
        $update->where(['id_documentos_orii' => (int) $id_documentos_orii]);
        $this->updateWith($update);
    }

    public function activarDocumento($id_documentos_orii)
    {
        $this->table = 'documentos_orii';
        $update = new Update('documentos_orii');
        $update->set(['estado_documento' => 'Activo']);
        $update->where(['id_documentos_orii' => (int) $id_documentos_orii]);
        $this->updateWith($update);
    }

    public function borrarDocumento($id_documentos_orii)
    {
        $this->table = 'documentos_orii';
        $delete = new Delete('documentos_orii');
        $delete->where(['id_documentos_orii' => (int) $id_documentos_orii]);
        $this->deleteWith($delete);
    }
}
