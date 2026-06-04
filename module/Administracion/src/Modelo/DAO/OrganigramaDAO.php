<?php

namespace Administracion\Modelo\DAO;

use Laminas\Db\TableGateway\AbstractTableGateway;
use Laminas\Db\Adapter\Adapter;
use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\Insert;
use Laminas\Db\Sql\Update;
use Laminas\Db\Sql\Delete;
use Administracion\Modelo\Entidades\Organigrama;

class OrganigramaDAO extends AbstractTableGateway
{
    protected $table = 'nodos';
    protected $adapter;

    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
    }

    //------------------------------------------------------------------------------
    public function fetchAll($filtro = '')
    {
        $sql = "SELECT n.*, 
                   m.icono, 
                   m.descripcion as metadata_descripcion,
                   m.color,
                   (SELECT COUNT(*) FROM nodos WHERE padre_id = n.id) as num_hijos,
                   (SELECT nombre FROM nodos WHERE id = n.padre_id) as nombre_padre,
                   CASE WHEN n.activo = 1 THEN 'Activo' ELSE 'Inactivo' END as estado
            FROM nodos n
            LEFT JOIN nodos_metadata m ON m.nodo_id = n.id";

        if ($filtro != '') {
            $sql .= " WHERE " . $filtro;
        }

        $sql .= " ORDER BY COALESCE(n.padre_id, 0) ASC, n.orden ASC";

        $statement = $this->adapter->query($sql);
        $result = $statement->execute();

        return $result->getResource()->fetchAll(\PDO::FETCH_ASSOC);
    }
    //------------------------------------------------------------------------------
    public function getTree($padre_id = null, $padre_nombre = null)
    {
        $sql = "SELECT id, nombre, tipo, padre_id, orden, activo 
            FROM nodos 
            WHERE activo = 1 " . ($padre_id === null ? "AND padre_id IS NULL" : "AND padre_id = ?") . "
            ORDER BY orden ASC";

        $statement = $this->adapter->query($sql);
        if ($padre_id === null) {
            $result = $statement->execute();
        } else {
            $result = $statement->execute([$padre_id]);
        }

        $nodos = $result->getResource()->fetchAll(\PDO::FETCH_ASSOC);

        foreach ($nodos as &$nodo) {
            // Cargar metadata (icono, descripcion, color)
            $sqlMeta = "SELECT icono, descripcion, color FROM nodos_metadata WHERE nodo_id = ?";
            $stmtMeta = $this->adapter->query($sqlMeta);
            $resultMeta = $stmtMeta->execute([$nodo['id']]);
            $meta = $resultMeta->current();

            $nodo['icono'] = $meta ? $meta['icono'] : null;
            $nodo['descripcion'] = $meta ? $meta['descripcion'] : null;
            $nodo['color'] = $meta ? $meta['color'] : null;
            $nodo['padre_nombre'] = $padre_nombre; // Guardar el nombre del padre

            $nodo['children'] = $this->getTree($nodo['id'], $nodo['nombre']);
        }

        return $nodos;
    }

    //------------------------------------------------------------------------------
    public function getNodo($id)
    {
        $sql = "SELECT n.*, m.icono, m.descripcion as metadata_descripcion
                FROM nodos n
                LEFT JOIN nodos_metadata m ON m.nodo_id = n.id
                WHERE n.id = ?";
        $statement = $this->adapter->query($sql);
        $result = $statement->execute([$id]);
        $data = $result->current();
        return $data ? new Organigrama($data) : null;
    }

    //------------------------------------------------------------------------------
    public function getNodoDetalle($id)
    {
        $sql = "SELECT n.*, 
                   m.icono, 
                   m.descripcion as metadata_descripcion,
                   m.color,
                   (SELECT nombre FROM nodos WHERE id = n.padre_id) as nombre_padre
            FROM nodos n
            LEFT JOIN nodos_metadata m ON m.nodo_id = n.id
            WHERE n.id = ?";
        $statement = $this->adapter->query($sql);
        $result = $statement->execute([$id]);
        $data = $result->current();

        if ($data) {
            $data['estado'] = ($data['activo'] == 1) ? 'Activo' : 'Inactivo';
            // Asegurar que descripcion tenga valor
            if (empty($data['descripcion']) && !empty($data['metadata_descripcion'])) {
                $data['descripcion'] = $data['metadata_descripcion'];
            }
        }

        return $data;
    }

    //------------------------------------------------------------------------------
    public function getNombrePadre($id)
    {
        $sql = "SELECT nombre FROM nodos WHERE id = (SELECT padre_id FROM nodos WHERE id = ?)";
        $statement = $this->adapter->query($sql);
        $result = $statement->execute([$id]);
        $data = $result->current();
        return $data ? $data['nombre'] : 'Ninguno (Raíz)';
    }

    //------------------------------------------------------------------------------
    public function getHijos($padre_id)
    {
        $sql = "SELECT id, nombre, tipo FROM nodos WHERE padre_id = ? AND activo = 1 ORDER BY orden ASC";
        $statement = $this->adapter->query($sql);
        $result = $statement->execute([$padre_id]);
        return $result->getResource()->fetchAll(\PDO::FETCH_ASSOC);
    }

    //------------------------------------------------------------------------------
    public function getNodosParaPadre($excluir_id = null)
    {
        $sql = "SELECT id, nombre, tipo FROM nodos WHERE activo = 1";
        if ($excluir_id) {
            $sql .= " AND id != $excluir_id";
        }
        $sql .= " ORDER BY nombre ASC";

        $statement = $this->adapter->query($sql);
        $result = $statement->execute();
        return $result->getResource()->fetchAll(\PDO::FETCH_ASSOC);
    }

    //------------------------------------------------------------------------------
    public function registrar(Organigrama $organigramaOBJ = null)
    {
        error_log("DAO REGISTRAR - INICIO");

        $connection = $this->adapter->getDriver()->getConnection();
        $connection->beginTransaction();

        try {
            // Calcular el orden
            $padre_id = $organigramaOBJ->getPadreId();
            error_log("DAO REGISTRAR - Padre ID: " . ($padre_id ?: 'NULL'));

            if ($padre_id) {
                $sqlOrden = "SELECT COALESCE(MAX(orden), -1) + 1 as nuevo_orden FROM nodos WHERE padre_id = ?";
                $stmtOrden = $this->adapter->query($sqlOrden);
                $result = $stmtOrden->execute([$padre_id]);
                $orden = $result->current()['nuevo_orden'];
            } else {
                $sqlOrden = "SELECT COALESCE(MAX(orden), -1) + 1 as nuevo_orden FROM nodos WHERE padre_id IS NULL";
                $stmtOrden = $this->adapter->query($sqlOrden);
                $result = $stmtOrden->execute();
                $orden = $result->current()['nuevo_orden'];
            }

            $organigramaOBJ->setOrden($orden);
            error_log("DAO REGISTRAR - Orden calculado: " . $orden);

            // Insertar en nodos
            $sql = "INSERT INTO nodos (nombre, tipo, padre_id, orden, activo, registradopor) 
                VALUES (?, ?, ?, ?, ?, ?)";

            error_log("DAO REGISTRAR - SQL: " . $sql);
            error_log("DAO REGISTRAR - Valores: " . print_r([
                $organigramaOBJ->getNombre(),
                $organigramaOBJ->getTipo(),
                $organigramaOBJ->getPadreId(),
                $organigramaOBJ->getOrden(),
                $organigramaOBJ->getActivo(),
                $organigramaOBJ->getRegistradopor()
            ], true));

            $statement = $this->adapter->query($sql);
            $result = $statement->execute([
                $organigramaOBJ->getNombre(),
                $organigramaOBJ->getTipo(),
                $organigramaOBJ->getPadreId(),
                $organigramaOBJ->getOrden(),
                $organigramaOBJ->getActivo(),
                $organigramaOBJ->getRegistradopor()
            ]);

            $id = $this->adapter->getDriver()->getLastGeneratedValue();
            error_log("DAO REGISTRAR - ID generado: " . $id);

            // Insertar en nodos_metadata
            $icono = $organigramaOBJ->getIcono();
            $descripcion = $organigramaOBJ->getDescripcion();
            $color = $organigramaOBJ->getColor();

            if (!empty($icono) || !empty($descripcion) || !empty($color)) {
                $sqlMeta = "INSERT INTO nodos_metadata (nodo_id, icono, descripcion, color) VALUES (?, ?, ?, ?)";
                $stmtMeta = $this->adapter->query($sqlMeta);
                $stmtMeta->execute([
                    $id,
                    !empty($icono) ? $icono : null,
                    !empty($descripcion) ? $descripcion : null,
                    !empty($color) ? $color : null
                ]);
                error_log("DAO REGISTRAR - Metadata insertada");
            }

            $connection->commit();
            error_log("DAO REGISTRAR - TRANSACCIÓN COMMIT EXITOSA");
            return $id;
        } catch (\Exception $e) {
            $connection->rollback();
            error_log("DAO REGISTRAR - ERROR: " . $e->getMessage());
            error_log("DAO REGISTRAR - TRACE: " . $e->getTraceAsString());
            throw new \Exception($e->getMessage());
        }
    }

    //------------------------------------------------------------------------------
    public function editar(Organigrama $organigramaOBJ = null)
    {
        try {
            $sql = "UPDATE nodos SET 
                    nombre = ?,
                    tipo = ?,
                    padre_id = ?,
                    orden = ?
                WHERE id = ?";

            $statement = $this->adapter->query($sql);
            $statement->execute([
                $organigramaOBJ->getNombre(),
                $organigramaOBJ->getTipo(),
                $organigramaOBJ->getPadreId(),
                $organigramaOBJ->getOrden(),
                $organigramaOBJ->getId()
            ]);

            // Actualizar metadata (icono, descripcion y color)
            $icono = $organigramaOBJ->getIcono();
            $descripcion = $organigramaOBJ->getDescripcion();
            $color = $organigramaOBJ->getColor();

            // Verificar si existe registro en nodos_metadata
            $sqlCheck = "SELECT COUNT(*) as total FROM nodos_metadata WHERE nodo_id = ?";
            $stmtCheck = $this->adapter->query($sqlCheck);
            $resultCheck = $stmtCheck->execute([$organigramaOBJ->getId()]);
            $existe = $resultCheck->current()['total'];

            if ($existe > 0) {
                $sqlMeta = "UPDATE nodos_metadata SET icono = ?, descripcion = ?, color = ? WHERE nodo_id = ?";
                $stmtMeta = $this->adapter->query($sqlMeta);
                $stmtMeta->execute([
                    !empty($icono) ? $icono : null,
                    !empty($descripcion) ? $descripcion : null,
                    !empty($color) ? $color : null,
                    $organigramaOBJ->getId()
                ]);
            } else {
                $sqlMeta = "INSERT INTO nodos_metadata (nodo_id, icono, descripcion, color) VALUES (?, ?, ?, ?)";
                $stmtMeta = $this->adapter->query($sqlMeta);
                $stmtMeta->execute([
                    $organigramaOBJ->getId(),
                    !empty($icono) ? $icono : null,
                    !empty($descripcion) ? $descripcion : null,
                    !empty($color) ? $color : null
                ]);
            }

            return true;
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    //------------------------------------------------------------------------------
    public function eliminar($id)
    {
        try {
            $sql = "UPDATE nodos SET activo = 0 WHERE id = ?";
            $statement = $this->adapter->query($sql);
            $statement->execute([$id]);
            return true;
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function activar($id)
    {
        try {
            $sql = "UPDATE nodos SET activo = 1 WHERE id = ?";
            $statement = $this->adapter->query($sql);
            $statement->execute([$id]);
            return true;
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    //------------------------------------------------------------------------------
    public function getRutaCompleta($id, $incluirActual = true)
    {
        $ruta = [];
        $actual = $id;

        while ($actual) {
            $sql = "SELECT id, nombre, padre_id FROM nodos WHERE id = ?";
            $stmt = $this->adapter->query($sql);
            $result = $stmt->execute([$actual]);
            $nodo = $result->current();

            if ($nodo) {
                if ($incluirActual || $actual != $id) {
                    array_unshift($ruta, $nodo['nombre']);
                }
                $actual = $nodo['padre_id'];
            } else {
                break;
            }
        }

        return implode(' → ', $ruta);
    }

    // Método para obtener todos los nodos con su ruta completa
    public function fetchAllConRuta($filtro = '')
    {
        $sql = "SELECT n.*, 
                   m.icono, 
                   m.descripcion as metadata_descripcion,
                   (SELECT COUNT(*) FROM nodos WHERE padre_id = n.id) as num_hijos,
                   CASE WHEN n.activo = 1 THEN 'Activo' ELSE 'Inactivo' END as estado
            FROM nodos n
            LEFT JOIN nodos_metadata m ON m.nodo_id = n.id";
        if ($filtro != '') {
            $sql .= " WHERE " . $filtro;
        }
        $sql .= " ORDER BY COALESCE(n.padre_id, 0) ASC, n.orden ASC";

        $statement = $this->adapter->query($sql);
        $result = $statement->execute();
        $nodos = $result->getResource()->fetchAll(\PDO::FETCH_ASSOC);

        // Agregar ruta completa a cada nodo
        foreach ($nodos as &$nodo) {
            $nodo['ruta_completa'] = $this->getRutaCompleta($nodo['id'], false);
        }

        return $nodos;
    }
    //------------------------------------------------------------------------------
    public function getRutaCompletaPorId($id)
    {
        if (empty($id)) {
            return '';
        }

        $ruta = [];
        $actual = $id;

        while ($actual) {
            $sql = "SELECT id, nombre, padre_id FROM nodos WHERE id = ?";
            $stmt = $this->adapter->query($sql);
            $result = $stmt->execute([$actual]);
            $nodo = $result->current();

            if ($nodo) {
                array_unshift($ruta, $nodo['nombre']);
                $actual = $nodo['padre_id'];
            } else {
                break;
            }
        }

        return implode(' → ', $ruta);
    }
    //------------------------------------------------------------------------------
    public function verificarDatos()
    {
        $sql = "SELECT COUNT(*) as total FROM nodos WHERE activo = 1";
        $statement = $this->adapter->query($sql);
        $result = $statement->execute();
        $total = $result->current()['total'];

        error_log("VERIFICAR DATOS - Total nodos activos: " . $total);
        return $total;
    }
    //------------------------------------------------------------------------------
}
