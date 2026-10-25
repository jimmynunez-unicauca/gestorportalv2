<?php

namespace Administracion\Modelo\DAO;

use Laminas\Db\TableGateway\AbstractTableGateway;
use Laminas\Db\Adapter\Adapter;
use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\Insert;
use Laminas\Db\Sql\Update;
use Laminas\Db\Sql\Delete;
use Administracion\Modelo\Entidades\Programa;
use Administracion\Modelo\Entidades\Programacion;
use Administracion\Modelo\Entidades\Excepcion;

class EmisoraDAO extends AbstractTableGateway
{
    protected $table = 'emisora_programas';

    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
    }

    //==========================================================================
    // PROGRAMAS (emisora_programas)
    //==========================================================================
    public function fetchAll($filtro = '')
    {
        $this->table = 'emisora_programas';
        $select = new Select($this->table);
        $select->columns(['*']);
        if ($filtro != '') {
            $select->where($filtro);
        } else {
            $select->order("emisora_programas.idPrograma DESC");
        }
        return $this->selectWith($select)->toArray();
    }

    public function getProgramaDetalle($idPrograma = 0)
    {
        $select = new Select('emisora_programas');
        $select->columns(['*'])->where(['idPrograma' => $idPrograma])->limit(1);
        $datos = $this->selectWith($select)->toArray();
        return count($datos) > 0 ? $datos[0] : null;
    }

    public function getPrograma($idPrograma = 0)
    {
        $select = new Select('emisora_programas');
        $select->where(['idPrograma' => (int) $idPrograma])->limit(1);
        $row = $this->selectWith($select)->current();
        return $row ? new Programa($row->getArrayCopy()) : null;
    }

    public function getProgramasActivos()
    {
        $select = new Select('emisora_programas');
        $select->columns(['idPrograma', 'nombre_programa'])
            ->where(['estado' => 'Activo'])
            ->order('nombre_programa ASC');
        return $this->selectWith($select)->toArray();
    }

    public function registrar(Programa $programaOBJ)
    {
        $this->table = 'emisora_programas';
        $insert = new Insert($this->table);
        $datos = $programaOBJ->getArrayCopy();
        unset($datos['idPrograma']);
        $insert->values($datos);
        $this->insertWith($insert);
    }

    public function editar(Programa $programaOBJ)
    {
        $this->table = 'emisora_programas';
        $id = (int) $programaOBJ->getIdPrograma();
        $update = new Update($this->table);
        $datos = $programaOBJ->getArrayCopy();
        unset($datos['idPrograma']);
        $update->set($datos);
        $update->where(['idPrograma' => $id]);
        $this->updateWith($update);
    }

    public function eliminarActivar(Programa $programaOBJ, $estado = '')
    {
        $this->table = 'emisora_programas';
        $update = new Update($this->table);
        $update->set([
            'estado'         => $estado,
            'modificadopor'  => $programaOBJ->getModificadopor(),
            'fechahoramod'   => $programaOBJ->getFechahoramod(),
        ]);
        $update->where(['idPrograma' => $programaOBJ->getIdPrograma()]);
        $this->updateWith($update);
    }

    //==========================================================================
    // PROGRAMACIÓN (emisora_programacion)
    //==========================================================================
    public function fetchAllProgramacion($filtro = '')
    {
        $select = new Select('emisora_programacion');
        $select->columns(['*'])
            ->join(
                ['p' => 'emisora_programas'],
                'emisora_programacion.idPrograma = p.idPrograma',
                ['nombre_programa' => 'nombre_programa', 'color_programa' => 'color_programa'],
                Select::JOIN_LEFT
            );
        if ($filtro != '') {
            $select->where($filtro);
        } else {
            $select->order('emisora_programacion.idProgramacion DESC');
        }
        return $this->selectWith($select)->toArray();
    }

    public function getProgramacion($idProgramacion = 0)
    {
        $select = new Select('emisora_programacion');
        $select->where(['idProgramacion' => (int) $idProgramacion])->limit(1);
        $row = $this->selectWith($select)->current();
        return $row ? new Programacion($row->getArrayCopy()) : null;
    }

    public function getProgramacionDetalle($idProgramacion = 0)
    {
        $select = new Select('emisora_programacion');
        $select->columns(['*'])
            ->join(
                ['p' => 'emisora_programas'],
                'emisora_programacion.idPrograma = p.idPrograma',
                ['nombre_programa' => 'nombre_programa', 'color_programa' => 'color_programa'],
                Select::JOIN_LEFT
            )
            ->where(['emisora_programacion.idProgramacion' => $idProgramacion])
            ->limit(1);
        $datos = $this->selectWith($select)->toArray();
        return count($datos) > 0 ? $datos[0] : null;
    }

    public function registrarProgramacion(Programacion $obj)
    {
        $this->table = 'emisora_programacion';
        $insert = new Insert($this->table);
        $datos = $obj->getArrayCopy();
        unset($datos['idProgramacion']);
        if (isset($datos['vigente_hasta']) && $datos['vigente_hasta'] === '') {
            $datos['vigente_hasta'] = null;
        }
        $insert->values($datos);
        $this->insertWith($insert);
    }

    public function editarProgramacion(Programacion $obj)
    {
        $this->table = 'emisora_programacion';
        $id = (int) $obj->getIdProgramacion();
        $update = new Update($this->table);
        $datos = $obj->getArrayCopy();
        unset($datos['idProgramacion']);
        if (isset($datos['vigente_hasta']) && $datos['vigente_hasta'] === '') {
            $datos['vigente_hasta'] = null;
        }
        $update->set($datos);
        $update->where(['idProgramacion' => $id]);
        $this->updateWith($update);
    }

    public function eliminarActivarProgramacion(Programacion $obj, $estado = '')
    {
        $this->table = 'emisora_programacion';
        $update = new Update($this->table);
        $update->set([
            'estado'         => $estado,
            'modificadopor'  => $obj->getModificadopor(),
            'fechahoramod'   => $obj->getFechahoramod(),
        ]);
        $update->where(['idProgramacion' => $obj->getIdProgramacion()]);
        $this->updateWith($update);
    }

    public function borrarProgramacion($idProgramacion)
    {
        $this->table = 'emisora_programacion';
        $delete = new Delete($this->table);
        $delete->where(['idProgramacion' => (int) $idProgramacion]);
        $this->deleteWith($delete);
    }

    //==========================================================================
    // CALENDARIO (expande programación semanal en eventos por fecha)
    //==========================================================================
    public function getEventosCalendario(\DateTime $start, \DateTime $end): array
    {
        // 1. Programaciones activas
        $select = new Select('emisora_programacion');
        $select->columns(['*'])
            ->where(['estado' => 'Activo'])
            ->where->lessThanOrEqualTo('vigente_desde', $end->format('Y-m-d'))
            ->and->nest()
            ->isNull('vigente_hasta')
            ->or->greaterThanOrEqualTo('vigente_hasta', $start->format('Y-m-d'))
            ->unnest();

        $programaciones = $this->selectWith($select)->toArray();

        // 2. Programas (solo los activos)
        $selectP = new Select('emisora_programas');
        $selectP->columns(['idPrograma', 'nombre_programa', 'color_programa'])
            ->where(['estado' => 'Activo']);
        $programas = [];
        foreach ($this->selectWith($selectP) as $p) {
            $programas[$p['idPrograma']] = $p;
        }

        // 3. Excepciones en el rango
        $selectE = new Select('emisora_excepciones');
        $selectE->columns(['*'])
            ->where->greaterThanOrEqualTo('fecha', $start->format('Y-m-d'))
            ->and->lessThanOrEqualTo('fecha', $end->format('Y-m-d'));
        $excepciones = $this->selectWith($selectE)->toArray();

        // 4. Expandir
        $mapDias = [
            'domingo' => 0,
            'lunes' => 1,
            'martes' => 2,
            'miércoles' => 3,
            'jueves' => 4,
            'viernes' => 5,
            'sábado' => 6,
        ];

        $eventos = [];
        $current = clone $start;
        while ($current < $end) {
            $diaNum = (int) $current->format('w');
            $fecha  = $current->format('Y-m-d');

            // ¿Hay cancelación de DÍA COMPLETO para esta fecha?
            $cancelacionDia = false;
            foreach ($excepciones as $ex) {
                if ($ex['tipo'] === 'cancelacion_dia' && $ex['fecha'] === $fecha) {
                    $cancelacionDia = true;
                    break;
                }
            }

            // Si NO hay cancelación de día completo, procesar la programación normal
            if (!$cancelacionDia) {
                foreach ($programaciones as $prog) {
                    // ¿Aplica este día?
                    $diasProg = array_map('trim', explode(',', $prog['dias_semana']));
                    $coincide = false;
                    foreach ($diasProg as $diaNombre) {
                        if (isset($mapDias[$diaNombre]) && $mapDias[$diaNombre] === $diaNum) {
                            $coincide = true;
                            break;
                        }
                    }
                    if (!$coincide) continue;

                    // ¿Está dentro de la vigencia?
                    if ($prog['vigente_desde'] > $fecha) continue;
                    if ($prog['vigente_hasta'] && $prog['vigente_hasta'] < $fecha) continue;

                    // Buscar excepciones para esta programación y fecha
                    $omitir = false;
                    $ajuste = null;
                    foreach ($excepciones as $ex) {
                        if ($ex['fecha'] !== $fecha) continue;

                        if (
                            $ex['tipo'] === 'cancelacion'
                            && (int) $ex['idProgramacion_original'] === (int) $prog['idProgramacion']
                        ) {
                            $omitir = true;
                            break;
                        }

                        if (
                            $ex['tipo'] === 'ajuste_horario'
                            && (int) $ex['idProgramacion_original'] === (int) $prog['idProgramacion']
                        ) {
                            $omitir = true;
                            $ajuste = $ex;
                            break;
                        }
                    }

                    if ($omitir) {
                        if ($ajuste && $ajuste['hora_inicio'] && $ajuste['hora_fin']) {
                            $info = $programas[$prog['idPrograma']] ?? null;
                            if ($info) {
                                $eventos[] = [
                                    'id'        => 'ex_' . $ajuste['idExcepcion'],
                                    'title'     => $info['nombre_programa'] . ' (Ajuste)',
                                    'start'     => $fecha . 'T' . $ajuste['hora_inicio'],
                                    'end'       => $fecha . 'T' . $ajuste['hora_fin'],
                                    'color'     => $info['color_programa'] ?: '#000066',
                                    'textColor' => '#ffffff',
                                ];
                            }
                        }
                        continue;
                    }

                    // Agregar programa original
                    $info = $programas[$prog['idPrograma']] ?? null;
                    if (!$info) continue;

                    $eventos[] = [
                        'id'        => (int) $prog['idProgramacion'],
                        'title'     => $info['nombre_programa'],
                        'start'     => $fecha . 'T' . $prog['hora_inicio'],
                        'end'       => $fecha . 'T' . $prog['hora_fin'],
                        'color'     => $info['color_programa'] ?: '#000066',
                        'textColor' => '#ffffff',
                    ];
                }
            }

            // Los REEMPLAZOS se procesan SIEMPRE (incluso si hay cancelación de día completo)
            foreach ($excepciones as $ex) {
                if ($ex['tipo'] !== 'reemplazo' || $ex['fecha'] !== $fecha) continue;
                if (!$ex['idPrograma_reemplazo'] || !$ex['hora_inicio'] || !$ex['hora_fin']) continue;

                $info = $programas[$ex['idPrograma_reemplazo']] ?? null;
                if (!$info) continue;

                $eventos[] = [
                    'id'        => 'ex_' . $ex['idExcepcion'],
                    'title'     => $info['nombre_programa'] . ' (Reemplazo)',
                    'start'     => $fecha . 'T' . $ex['hora_inicio'],
                    'end'       => $fecha . 'T' . $ex['hora_fin'],
                    'color'     => '#e63946',
                    'textColor' => '#ffffff',
                ];
            }

            $current->modify('+1 day');
        }

        return $eventos;
    }

    //==========================================================================
    // PROGRAMAS: verificar si tiene programación asociada
    //==========================================================================
    public function tieneProgramacion($idPrograma)
    {
        $select = new Select('emisora_programacion');
        $select->columns(['total' => new \Laminas\Db\Sql\Expression('COUNT(*)')])
            ->where(['idPrograma' => (int) $idPrograma]);
        $row = $this->selectWith($select)->current();
        return $row && (int) $row['total'] > 0;
    }

    //==========================================================================
    // PROGRAMAS: borrar definitivamente (hard delete)
    //==========================================================================
    public function borrarPrograma($idPrograma)
    {
        $this->table = 'emisora_programas';
        $delete = new Delete($this->table);
        $delete->where(['idPrograma' => (int) $idPrograma]);
        $this->deleteWith($delete);
    }

    public function tieneExcepciones($idProgramacion)
    {
        $select = new Select('emisora_excepciones');
        $select->columns(['total' => new \Laminas\Db\Sql\Expression('COUNT(*)')])
            ->where(['idProgramacion_original' => (int) $idProgramacion]);
        $row = $this->selectWith($select)->current();
        return $row && (int) $row['total'] > 0;
    }

    //==========================================================================
    // EXCEPCIONES (emisora_excepciones)
    //==========================================================================
    public function fetchAllExcepciones($filtro = '')
    {
        $select = new Select('emisora_excepciones');
        $select->columns(['*'])
            ->join(
                ['pr' => 'emisora_programacion'],
                'emisora_excepciones.idProgramacion_original = pr.idProgramacion',
                ['prog_original' => 'idProgramacion'],
                Select::JOIN_LEFT
            )
            ->join(
                ['p_orig' => 'emisora_programas'],
                'pr.idPrograma = p_orig.idPrograma',
                ['nombre_original' => 'nombre_programa'],
                Select::JOIN_LEFT
            )
            ->join(
                ['p_reemp' => 'emisora_programas'],
                'emisora_excepciones.idPrograma_reemplazo = p_reemp.idPrograma',
                ['nombre_reemplazo' => 'nombre_programa'],
                Select::JOIN_LEFT
            );

        if ($filtro != '') {
            $select->where($filtro);
        } else {
            $select->order('emisora_excepciones.fecha DESC');
        }
        return $this->selectWith($select)->toArray();
    }

    public function getExcepcion($idExcepcion = 0)
    {
        $select = new Select('emisora_excepciones');
        $select->where(['idExcepcion' => (int) $idExcepcion])->limit(1);
        $row = $this->selectWith($select)->current();
        return $row ? new Excepcion($row->getArrayCopy()) : null;
    }

    public function getExcepcionDetalle($idExcepcion = 0)
    {
        $select = new Select('emisora_excepciones');
        $select->columns(['*'])
            ->join(['p_orig' => 'emisora_programas'], 'emisora_excepciones.idPrograma_reemplazo = p_orig.idPrograma', ['nombre_reemplazo' => 'nombre_programa'], Select::JOIN_LEFT)
            ->where(['idExcepcion' => (int) $idExcepcion])->limit(1);
        $datos = $this->selectWith($select)->toArray();
        return count($datos) > 0 ? $datos[0] : null;
    }

    public function registrarExcepcion(Excepcion $obj)
    {
        $this->table = 'emisora_excepciones';
        $insert = new Insert($this->table);
        $insert->values([
            'fecha'                   => $obj->getFecha(),
            'tipo'                    => $obj->getTipo(),
            'idProgramacion_original' => $obj->getIdProgramacion_original(),
            'idPrograma_reemplazo'    => $obj->getIdPrograma_reemplazo(),
            'hora_inicio'             => $obj->getHora_inicio(),
            'hora_fin'                => $obj->getHora_fin(),
            'motivo'                  => $obj->getMotivo(),
            'registradopor'           => $obj->getRegistradopor(),
            'fechahorareg'            => $obj->getFechahorareg(),
        ]);
        $this->insertWith($insert);
    }

    public function editarExcepcion(Excepcion $obj)
    {
        $this->table = 'emisora_excepciones';
        $id = (int) $obj->getIdExcepcion();
        $update = new Update($this->table);
        $update->set([
            'fecha'                   => $obj->getFecha(),
            'tipo'                    => $obj->getTipo(),
            'idProgramacion_original' => $obj->getIdProgramacion_original(),
            'idPrograma_reemplazo'    => $obj->getIdPrograma_reemplazo(),
            'hora_inicio'             => $obj->getHora_inicio(),
            'hora_fin'                => $obj->getHora_fin(),
            'motivo'                  => $obj->getMotivo(),
        ]);
        $update->where(['idExcepcion' => $id]);
        $this->updateWith($update);
    }

    public function eliminarExcepcion($idExcepcion)
    {
        $this->table = 'emisora_excepciones';
        $delete = new Delete($this->table);
        $delete->where(['idExcepcion' => (int) $idExcepcion]);
        $this->deleteWith($delete);
    }

    public function getProgramacionesSelect()
    {
        $select = new Select('emisora_programacion');
        $select->columns(['idProgramacion', 'hora_inicio', 'hora_fin', 'dias_semana'])
            ->join(['p' => 'emisora_programas'], 'emisora_programacion.idPrograma = p.idPrograma', ['nombre_programa' => 'nombre_programa'])
            ->where(['emisora_programacion.estado' => 'Activo']);
        return $this->selectWith($select)->toArray();
    }
}
