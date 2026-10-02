<?php

namespace Administracion\Modelo\DAO;

use Laminas\Db\TableGateway\AbstractTableGateway;
use Laminas\Db\Adapter\Adapter;
use Laminas\Db\Sql\Expression;
use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\Sql;

class UnisaludconsultaafiliadosDAO extends AbstractTableGateway
{
    protected $table = 'historial_consultas_unisalud';

    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
    }

    /* =========================================================
     *  HELPERS
     * ========================================================= */
    private function run($select)
    {
        $sql  = new Sql($this->adapter);
        $stmt = $sql->prepareStatementForSqlObject($select);
        $out  = [];
        foreach ($stmt->execute() as $row) {
            $out[] = $row;
        }
        return $out;
    }

    /**
     * $where SIEMPRE debe ser array: ['col' => val] o ['col >= ?' => val]
     * o string simple "usuario_id = 5"
     */
    private function countSimple(string $tabla, $where = null): int
    {
        $sql = new Sql($this->adapter);
        $sel = $sql->select()->from($tabla)
            ->columns(['total' => new Expression('COUNT(*)')]);
        if (!empty($where)) $sel->where($where);
        $r = $this->run($sel);
        return (int)($r[0]['total'] ?? 0);
    }

    private function countDistinct(string $tabla, string $campo, $where = null): int
    {
        $sql = new Sql($this->adapter);
        $sel = $sql->select()->from($tabla)
            ->columns(['total' => new Expression("COUNT(DISTINCT $campo)")]);
        if (!empty($where)) $sel->where($where);
        $r = $this->run($sel);
        return (int)($r[0]['total'] ?? 0);
    }

    /* =========================================================
     *  KPIs
     * ========================================================= */
    public function getKPIs(): array
    {
        return [
            'total_consultas'         => $this->countSimple('historial_consultas_unisalud'),
            'total_prestadores'       => $this->countSimple('usuarios_otp'),
            'total_afiliados_unicos'  => $this->countDistinct('historial_consultas_unisalud', 'identificacion_consultada'),
            'total_cotizantes'        => $this->countSimple('historial_consultas_unisalud', ['tipo_afiliado' => 'COTIZANTE']),
            'total_beneficiarios'     => $this->countSimple('historial_consultas_unisalud', ['tipo_afiliado' => 'BENEFICIARIO']),
            'total_logins'            => $this->countSimple('usuarios_otp_historial', ['accion' => 'verificacion', 'resultado' => 1]),
            'total_otp_enviados'      => $this->countSimple('usuarios_otp_historial', ['accion' => 'envio']),
            'logins_fallidos'         => $this->countSimple('usuarios_otp_historial', ['accion' => 'fallo']),
            'consultas_hoy'           => $this->countConsultasHoy(),
            'prestadores_activos_hoy' => $this->countPrestadoresHoy(),
        ];
    }

    private function countConsultasHoy(): int
    {
        $sql = new Sql($this->adapter);
        $sel = $sql->select()->from('historial_consultas_unisalud')
            ->columns(['total' => new Expression('COUNT(*)')])
            ->where("DATE(fecha_consulta) = CURDATE()");
        $r = $this->run($sel);
        return (int)($r[0]['total'] ?? 0);
    }

    private function countPrestadoresHoy(): int
    {
        $sql = new Sql($this->adapter);
        $sel = $sql->select()->from('historial_consultas_unisalud')
            ->columns(['total' => new Expression('COUNT(DISTINCT usuario_email)')])
            ->where("DATE(fecha_consulta) = CURDATE()");
        $r = $this->run($sel);
        return (int)($r[0]['total'] ?? 0);
    }

    /* =========================================================
     *  LISTADOS
     * ========================================================= */
    public function getConsultas(array $f = []): array
    {
        $sql = new Sql($this->adapter);
        $sel = $sql->select()->from(['h' => 'historial_consultas_unisalud'])->columns(['*']);

        if (!empty($f['usuario_email'])) {
            $sel->where(['h.usuario_email LIKE ?' => '%' . $f['usuario_email'] . '%']);
        }
        if (!empty($f['identificacion'])) {
            $sel->where(['h.identificacion_consultada LIKE ?' => '%' . $f['identificacion'] . '%']);
        }
        if (!empty($f['tipo_afiliado'])) {
            $sel->where(['h.tipo_afiliado = ?' => $f['tipo_afiliado']]);
        }
        if (!empty($f['fecha_desde'])) {
            // ⚠️  Placeholder '?' OBLIGATORIO
            $sel->where(['h.fecha_consulta >= ?' => $f['fecha_desde'] . ' 00:00:00']);
        }
        if (!empty($f['fecha_hasta'])) {
            $sel->where(['h.fecha_consulta <= ?' => $f['fecha_hasta'] . ' 23:59:59']);
        }

        $sel->order('h.fecha_consulta DESC');
        return $this->run($sel);
    }

    public function getUsuarios(array $f = []): array
    {
        $sql = new Sql($this->adapter);
        $sel = $sql->select()->from(['u' => 'usuarios_otp'])->columns([
            'id',
            'nombre',
            'email',
            'identificacion',
            'otp_verified',
            'email_verified',
            'created_at',
            'updated_at',
        ]);

        if (!empty($f['email'])) {
            $sel->where(['u.email LIKE ?' => '%' . $f['email'] . '%']);
        }
        $sel->order('u.id ASC');

        $rows = $this->run($sel);
        foreach ($rows as &$u) {
            // ⚠️  Usa array en lugar de string con Expression
            $u['total_consultas'] = $this->countSimple(
                'historial_consultas_unisalud',
                ['usuario_id' => (int)$u['id']]
            );
            $u['total_logins'] = $this->countSimple(
                'usuarios_otp_historial',
                ['usuario_id' => (int)$u['id'], 'accion' => 'verificacion', 'resultado' => 1]
            );
            $u['ultima_consulta'] = $this->getUltimaConsulta((int)$u['id']);
        }
        unset($u);
        return $rows;
    }

    private function getUltimaConsulta(int $id): ?string
    {
        $sql = new Sql($this->adapter);
        $sel = $sql->select()->from('historial_consultas_unisalud')
            ->columns(['fecha_consulta'])
            ->where(['usuario_id' => $id])
            ->order('fecha_consulta DESC')
            ->limit(1);
        $r = $this->run($sel);
        return $r[0]['fecha_consulta'] ?? null;
    }

    public function getAuditoria(array $f = []): array
    {
        $sql = new Sql($this->adapter);
        $sel = $sql->select()->from(['a' => 'usuarios_otp_historial'])->columns(['*']);

        if (!empty($f['email'])) {
            $sel->where(['a.email LIKE ?' => '%' . $f['email'] . '%']);
        }
        if (!empty($f['accion'])) {
            $sel->where(['a.accion = ?' => $f['accion']]);
        }
        if (!empty($f['fecha_desde'])) {
            $sel->where(['a.fecha >= ?' => $f['fecha_desde'] . ' 00:00:00']);
        }
        if (!empty($f['fecha_hasta'])) {
            $sel->where(['a.fecha <= ?' => $f['fecha_hasta'] . ' 23:59:59']);
        }

        $sel->order('a.fecha DESC');
        return $this->run($sel);
    }

    public function getAfiliadosAgrupados(array $f = []): array
    {
        $sql = new Sql($this->adapter);
        $sel = $sql->select()->from(['h' => 'historial_consultas_unisalud'])->columns([
            'identificacion_consultada',
            'nombre_consultado'  => new Expression('MAX(h.nombre_consultado)'),
            'tipo_afiliado'      => new Expression('MAX(h.tipo_afiliado)'),
            'es_beneficiario'    => new Expression('MAX(h.es_beneficiario)'),
            'total_consultas'    => new Expression('COUNT(*)'),
            'prestadores_unicos' => new Expression('COUNT(DISTINCT h.usuario_email)'),
            'ultima_consulta'    => new Expression('MAX(h.fecha_consulta)'),
        ])
            ->group(['h.identificacion_consultada'])
            ->order(new Expression('COUNT(*) DESC'));

        if (!empty($f['identificacion'])) {
            $sel->where(['h.identificacion_consultada LIKE ?' => '%' . $f['identificacion'] . '%']);
        }
        if (!empty($f['tipo_afiliado'])) {
            $sel->where(['h.tipo_afiliado = ?' => $f['tipo_afiliado']]);
        }
        return $this->run($sel);
    }

    /* =========================================================
     *  ESTADÍSTICAS
     * ========================================================= */
    public function getConsultasPorDia(): array
    {
        $sql = new Sql($this->adapter);
        $sel = $sql->select()->from(['h' => 'historial_consultas_unisalud'])->columns([
            'fecha' => new Expression('DATE(h.fecha_consulta)'),
            'total' => new Expression('COUNT(*)'),
        ])
            ->group([new Expression('DATE(h.fecha_consulta)')])
            ->order(new Expression('DATE(h.fecha_consulta) ASC'));
        return $this->run($sel);
    }

    public function getConsultasPorTipo(): array
    {
        $sql = new Sql($this->adapter);
        $sel = $sql->select()->from(['h' => 'historial_consultas_unisalud'])->columns([
            'tipo_afiliado',
            'total' => new Expression('COUNT(*)'),
        ])->group(['h.tipo_afiliado']);
        return $this->run($sel);
    }

    public function getTopPrestadores(int $limit = 10): array
    {
        $sql = new Sql($this->adapter);
        $sel = $sql->select()->from(['h' => 'historial_consultas_unisalud'])->columns([
            'usuario_email',
            'total'            => new Expression('COUNT(*)'),
            'afiliados_unicos' => new Expression('COUNT(DISTINCT h.identificacion_consultada)'),
        ])
            ->group(['h.usuario_email'])
            ->order(new Expression('COUNT(*) DESC'))
            ->limit($limit);
        return $this->run($sel);
    }

    public function getTopAfiliados(int $limit = 10): array
    {
        $sql = new Sql($this->adapter);
        $sel = $sql->select()->from(['h' => 'historial_consultas_unisalud'])->columns([
            'identificacion_consultada',
            'nombre_consultado' => new Expression('MAX(h.nombre_consultado)'),
            'total'             => new Expression('COUNT(*)'),
        ])
            ->group(['h.identificacion_consultada'])
            ->order(new Expression('COUNT(*) DESC'))
            ->limit($limit);
        return $this->run($sel);
    }

    public function getAccionesAuditoria(): array
    {
        $sql = new Sql($this->adapter);
        $sel = $sql->select()->from(['a' => 'usuarios_otp_historial'])->columns([
            'accion',
            'total' => new Expression('COUNT(*)'),
        ])->group(['a.accion']);
        return $this->run($sel);
    }

    public function getConsultasPorHora(): array
    {
        $sql = new Sql($this->adapter);
        $sel = $sql->select()->from(['h' => 'historial_consultas_unisalud'])->columns([
            'hora'  => new Expression('HOUR(h.fecha_consulta)'),
            'total' => new Expression('COUNT(*)'),
        ])
            ->group([new Expression('HOUR(h.fecha_consulta)')])
            ->order(new Expression('HOUR(h.fecha_consulta) ASC'));
        return $this->run($sel);
    }

    /* =========================================================
     *  DETALLES
     * ========================================================= */
    public function getDetalleUsuario(int $usuario_id): ?array
    {
        $sql = new Sql($this->adapter);
        $sel = $sql->select()->from(['u' => 'usuarios_otp'])->columns(['*'])
            ->where(['u.id' => $usuario_id]);
        $r = $this->run($sel);
        if (!$r) return null;

        $u = $r[0];
        $u['consultas'] = $this->getConsultas(['usuario_email' => $u['email']]);
        $u['auditoria'] = $this->getAuditoria(['email' => $u['email']]);

        $benef = 0;
        $cotiz = 0;
        foreach ($u['consultas'] as $c) {
            if ((int)$c['es_beneficiario'] === 1) $benef++;
            else $cotiz++;
        }

        $logins = 0;
        $otps = 0;
        foreach ($u['auditoria'] as $a) {
            if ($a['accion'] === 'verificacion' && (int)$a['resultado'] === 1) $logins++;
            if ($a['accion'] === 'envio') $otps++;
        }

        $u['resumen'] = [
            'total_consultas'  => count($u['consultas']),
            'afiliados_unicos' => count(array_unique(array_column($u['consultas'], 'identificacion_consultada'))),
            'beneficiarios'    => $benef,
            'cotizantes'       => $cotiz,
            'total_logins'     => $logins,
            'otp_enviados'     => $otps,
            'ultimo_acceso'    => $u['auditoria'][0]['fecha'] ?? null,
        ];
        return $u;
    }

    public function getDetalleAfiliado(string $identificacion): ?array
    {
        $consultas = $this->getConsultas(['identificacion' => $identificacion]);
        if (!$consultas) return null;

        $prestadores = [];
        foreach ($consultas as $c) {
            $k = $c['usuario_email'];
            if (!isset($prestadores[$k])) {
                $prestadores[$k] = ['email' => $k, 'total' => 0, 'ultima' => $c['fecha_consulta']];
            }
            $prestadores[$k]['total']++;
            if ($c['fecha_consulta'] > $prestadores[$k]['ultima']) {
                $prestadores[$k]['ultima'] = $c['fecha_consulta'];
            }
        }

        return [
            'identificacion'  => $identificacion,
            'nombre'          => $consultas[0]['nombre_consultado'],
            'tipo_afiliado'   => $consultas[0]['tipo_afiliado'],
            'es_beneficiario' => $consultas[0]['es_beneficiario'],
            'total_consultas' => count($consultas),
            'prestadores'     => array_values($prestadores),
            'consultas'       => $consultas,
        ];
    }

    /* =========================================================
     *  LEGACY
     * ========================================================= */
    public function fetchAll($filtro = '')
    {
        $select = new Select($this->table);
        $select->columns(['*']);
        if ($filtro != '') $select->where($filtro);
        else $select->order("historial_consultas_unisalud.id DESC");
        return $this->selectWith($select)->toArray();
    }
}
