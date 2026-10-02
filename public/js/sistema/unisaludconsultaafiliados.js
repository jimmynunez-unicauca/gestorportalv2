/* ================================================================
 *  Módulo Auditoría Unisalud – GestorPortal v2
 * ================================================================ */
(function ($) {
    'use strict';

    const U = window.UNISALUD_URLS || {};
    const PALETA = ['#2A3F54', '#26B99A', '#ff9800', '#9b59b6', '#00bcd4', '#e74c3c', '#3f51b5', '#8bc34a'];

    /* ---------- HELPERS ---------- */
    const fmtFecha = (s) => {
        if (!s) return '—';
        const d = new Date(s.replace(' ', 'T'));
        if (isNaN(d)) return s;
        return d.toLocaleString('es-CO', { year: 'numeric', month: '2-digit', day: '2-digit', hour: '2-digit', minute: '2-digit' });
    };

    const badge = (txt, color) => `<span class="badge badge-${color}">${txt}</span>`;
    const badgeTipo = (t) => t === 'BENEFICIARIO'
        ? badge('BENEFICIARIO', 'warning')
        : badge('COTIZANTE', 'info');
    const badgeAccion = (a) => {
        const map = { envio: 'info', reenvio: 'primary', verificacion: 'success', fallo: 'danger', logout: 'secondary', bloqueo: 'dark' };
        return badge(a.toUpperCase(), map[a] || 'secondary');
    };

    /* ---------- KPIs ---------- */
    function cargarKPIs() {
        $.getJSON(U.kpis).done(function (d) {
            $('#kpi-consultas').text(d.total_consultas);
            $('#kpi-prestadores').text(d.total_prestadores);
            $('#kpi-afiliados').text(d.total_afiliados_unicos);
            $('#kpi-hoy').text(d.consultas_hoy);
            $('#kpi-cotizantes').text(d.total_cotizantes);
            $('#kpi-beneficiarios').text(d.total_beneficiarios);
            $('#kpi-logins').text(d.total_logins);
            $('#kpi-fallos').text(d.logins_fallidos);
        });
    }

    /* ---------- GRÁFICAS ---------- */
    function cargarGraficas() {
        $.getJSON(U.estadisticas).done(function (d) {
            graficaPorDia(d.porDia);
            graficaPorTipo(d.porTipo);
            graficaTopPrestadores(d.topPrestadores);
            graficaTopAfiliados(d.topAfiliados);
            graficaPorHora(d.porHora);
            graficaAuditoria(d.accionesAuditoria);
        });
    }

    function graficaPorDia(data) {
        const ctx = document.getElementById('chartPorDia');
        if (!ctx) return;
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: data.map(x => x.fecha),
                datasets: [{
                    label: 'Consultas',
                    data: data.map(x => x.total),
                    borderColor: '#2A3F54',
                    backgroundColor: 'rgba(42,63,84,0.15)',
                    fill: true,
                    tension: 0.35,
                    pointRadius: 3,
                    pointBackgroundColor: '#26B99A',
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                legend: { display: false },
                scales: { yAxes: [{ ticks: { beginAtZero: true, precision: 0 } }] }
            }
        });
    }

    function graficaPorTipo(data) {
        const ctx = document.getElementById('chartPorTipo');
        if (!ctx) return;
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: data.map(x => x.tipo_afiliado || 'N/D'),
                datasets: [{
                    data: data.map(x => x.total),
                    backgroundColor: ['#2A3F54', '#ff9800', '#26B99A', '#e74c3c']
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                legend: { position: 'bottom' }
            }
        });
    }

    function graficaTopPrestadores(data) {
        const ctx = document.getElementById('chartTopPrestadores');
        if (!ctx) return;
        new Chart(ctx, {
            type: 'horizontalBar',
            data: {
                labels: data.map(x => x.usuario_email),
                datasets: [{
                    label: 'Consultas',
                    data: data.map(x => x.total),
                    backgroundColor: '#26B99A'
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                legend: { display: false },
                scales: { xAxes: [{ ticks: { beginAtZero: true, precision: 0 } }] }
            }
        });
    }

    function graficaTopAfiliados(data) {
        const ctx = document.getElementById('chartTopAfiliados');
        if (!ctx) return;
        new Chart(ctx, {
            type: 'horizontalBar',
            data: {
                labels: data.map(x => (x.nombre_consultado || x.identificacion_consultada).substring(0, 32)),
                datasets: [{
                    label: 'Consultas',
                    data: data.map(x => x.total),
                    backgroundColor: '#ff9800'
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                legend: { display: false },
                scales: { xAxes: [{ ticks: { beginAtZero: true, precision: 0 } }] }
            }
        });
    }

    function graficaPorHora(data) {
        const ctx = document.getElementById('chartPorHora');
        if (!ctx) return;
        const labels = Array.from({ length: 24 }, (_, i) => i + 'h');
        const totals = Array(24).fill(0);
        data.forEach(x => totals[parseInt(x.hora, 10)] = x.total);
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Consultas',
                    data: totals,
                    backgroundColor: '#3f51b5'
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                legend: { display: false },
                scales: { yAxes: [{ ticks: { beginAtZero: true, precision: 0 } }] }
            }
        });
    }

    function graficaAuditoria(data) {
        const ctx = document.getElementById('chartAuditoria');
        if (!ctx) return;
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: data.map(x => x.accion.toUpperCase()),
                datasets: [{
                    data: data.map(x => x.total),
                    backgroundColor: PALETA
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                legend: { position: 'bottom' }
            }
        });
    }

    /* ---------- FILTROS ---------- */
    function filtrosQS() {
        const p = new URLSearchParams();
        const map = {
            '#filtroEmail': 'usuario_email',
            '#filtroIdentificacion': 'identificacion',
            '#filtroTipo': 'tipo_afiliado',
            '#filtroFechaDesde': 'fecha_desde',
            '#filtroFechaHasta': 'fecha_hasta'
        };
        Object.entries(map).forEach(([sel, key]) => {
            const v = $(sel).val();
            if (v) p.append(key, v);
        });
        return p.toString();
    }

    /* ---------- DATATABLES ---------- */
    const botones = [
        { extend: 'copy', className: 'btn btn-sm btn-secondary', text: '<i class="fa fa-copy"></i> Copiar' },
        { extend: 'excel', className: 'btn btn-sm btn-success', text: '<i class="fa fa-file-excel-o"></i> Excel' },
        { extend: 'csv', className: 'btn btn-sm btn-info', text: '<i class="fa fa-file-text-o"></i> CSV' },
        { extend: 'pdf', className: 'btn btn-sm btn-danger', text: '<i class="fa fa-file-pdf-o"></i> PDF', orientation: 'landscape', pageSize: 'A4' },
        { extend: 'print', className: 'btn btn-sm btn-dark', text: '<i class="fa fa-print"></i> Imprimir' }
    ];

    const langES = {
        sProcessing: "Procesando...",
        sLengthMenu: "Mostrar _MENU_ registros",
        sZeroRecords: "No se encontraron resultados",
        sEmptyTable: "Ningún dato disponible en esta tabla",
        sInfo: "Mostrando _START_ a _END_ de _TOTAL_ registros",
        sInfoEmpty: "Mostrando 0 a 0 de 0 registros",
        sInfoFiltered: "(filtrado de _MAX_ registros totales)",
        sSearch: "Buscar:",
        oPaginate: { sFirst: "Primero", sLast: "Último", sNext: "Siguiente", sPrevious: "Anterior" }
    };

    let dtConsultas, dtPrestadores, dtAfiliados, dtAuditoria;

    function initConsultas() {
        dtConsultas = $('#tablaConsultas').DataTable({
            ajax: { url: U.consultas + (filtrosQS() ? '?' + filtrosQS() : ''), dataSrc: 'data' },
            dom: "<'row'<'col-sm-6'B><'col-sm-6'f>>" +
                "<'row'<'col-sm-12'tr>>" +
                "<'row'<'col-sm-5'i><'col-sm-7'p>>",
            buttons: botones,
            language: langES,
            responsive: true,
            order: [[7, 'desc']],
            pageLength: 10,
            columns: [
                { data: 'id', title: 'ID', width: '50px' },
                { data: 'usuario_email', title: 'Prestador' },
                { data: 'identificacion_consultada', title: 'Identificación' },
                { data: 'nombre_consultado', title: 'Afiliado', defaultContent: '—' },
                { data: 'tipo_afiliado', title: 'Tipo', render: (d) => badgeTipo(d) },
                { data: 'es_beneficiario', title: 'Benef.', render: (d) => parseInt(d) === 1 ? badge('SÍ', 'success') : badge('NO', 'secondary') },
                { data: 'ip', title: 'IP', defaultContent: '—' },
                { data: 'fecha_consulta', title: 'Fecha', render: fmtFecha }
            ]
        });
    }

    function initPrestadores() {
        dtPrestadores = $('#tablaPrestadores').DataTable({
            ajax: { url: U.usuarios, dataSrc: 'data' },
            dom: "<'row'<'col-sm-6'B><'col-sm-6'f>>" +
                "<'row'<'col-sm-12'tr>>" +
                "<'row'<'col-sm-5'i><'col-sm-7'p>>",
            buttons: botones,
            language: langES,
            responsive: true,
            pageLength: 10,
            columns: [
                { data: 'id', title: 'ID', width: '60px' },
                { data: 'nombre', title: 'Nombre' },
                { data: 'email', title: 'Email' },
                { data: 'identificacion', title: 'Identificación' },
                { data: 'total_consultas', title: 'Consultas', render: (d) => badge(d, 'primary') },
                { data: 'total_logins', title: 'Logins', render: (d) => badge(d, 'success') },
                { data: 'ultima_consulta', title: 'Última Consulta', render: fmtFecha },
                {
                    data: null, title: 'Acciones', orderable: false, searchable: false,
                    render: (row) => `<button class="btn btn-sm btn-primary btn-ver-prestador" data-id="${row.id}">
                        <i class="fa fa-eye"></i> Ver</button>`
                }
            ]
        });
    }

    function initAfiliados() {
        dtAfiliados = $('#tablaAfiliados').DataTable({
            ajax: { url: U.afiliados + (filtrosQS() ? '?' + filtrosQS() : ''), dataSrc: 'data' },
            dom: "<'row'<'col-sm-6'B><'col-sm-6'f>>" +
                "<'row'<'col-sm-12'tr>>" +
                "<'row'<'col-sm-5'i><'col-sm-7'p>>",
            buttons: botones,
            language: langES,
            responsive: true,
            pageLength: 10,
            order: [[3, 'desc']],
            columns: [
                { data: 'identificacion_consultada', title: 'Identificación' },
                { data: 'nombre_consultado', title: 'Nombre', defaultContent: '—' },
                { data: 'tipo_afiliado', title: 'Tipo', render: (d) => badgeTipo(d) },
                { data: 'total_consultas', title: 'Consultas', render: (d) => badge(d, 'danger') },
                { data: 'prestadores_unicos', title: 'Prestadores', render: (d) => badge(d, 'info') },
                { data: 'ultima_consulta', title: 'Última', render: fmtFecha },
                {
                    data: null, title: 'Acciones', orderable: false, searchable: false,
                    render: (row) => `<button class="btn btn-sm btn-success btn-ver-afiliado" data-id="${row.identificacion_consultada}">
                        <i class="fa fa-eye"></i> Ver</button>`
                }
            ]
        });
    }

    function initAuditoria() {
        dtAuditoria = $('#tablaAuditoria').DataTable({
            ajax: { url: U.auditoria, dataSrc: 'data' },
            dom: "<'row'<'col-sm-6'B><'col-sm-6'f>>" +
                "<'row'<'col-sm-12'tr>>" +
                "<'row'<'col-sm-5'i><'col-sm-7'p>>",
            buttons: botones,
            language: langES,
            responsive: true,
            order: [[0, 'desc']],
            pageLength: 10,
            columns: [
                { data: 'id', title: 'ID', width: '60px' },
                { data: 'email', title: 'Prestador' },
                { data: 'accion', title: 'Acción', render: badgeAccion },
                { data: 'resultado', title: 'Resultado', render: (d) => parseInt(d) === 1 ? badge('OK', 'success') : badge('FALLO', 'danger') },
                { data: 'ip', title: 'IP', defaultContent: '—' },
                { data: 'detalle', title: 'Detalle', defaultContent: '—' },
                { data: 'fecha', title: 'Fecha', render: fmtFecha }
            ]
        });
    }

    /* ---------- MODALES ---------- */
    $(document).on('click', '.btn-ver-prestador', function () {
        const id = $(this).data('id');
        $('#modalPrestador').modal('show');
        $('#modalPrestadorBody').html('<div class="text-center p-4"><i class="fa fa-spinner fa-spin fa-2x"></i></div>');
        $.getJSON(U.detalleUsuario.replace('ID', id)).done(renderPrestador);
    });

    $(document).on('click', '.btn-ver-afiliado', function () {
        const id = $(this).data('id');
        $('#modalAfiliado').modal('show');
        $('#modalAfiliadoBody').html('<div class="text-center p-4"><i class="fa fa-spinner fa-spin fa-2x"></i></div>');
        $.getJSON(U.detalleAfiliado.replace('ID', encodeURIComponent(id))).done(renderAfiliado);
    });

    function renderPrestador(d) {
        if (d.error) { $('#modalPrestadorBody').html(`<div class="alert alert-danger">${d.error}</div>`); return; }
        const r = d.resumen || {};
        const html = `
            <div class="row mb-3">
                <div class="col-md-6"><b>Nombre:</b> ${d.nombre}</div>
                <div class="col-md-6"><b>Email:</b> ${d.email}</div>
                <div class="col-md-6"><b>Identificación:</b> ${d.identificacion}</div>
                <div class="col-md-6"><b>Último acceso:</b> ${fmtFecha(r.ultimo_acceso)}</div>
            </div>
            <div class="row text-center mb-3">
                <div class="col"><div class="mini-kpi bg-primary">${r.total_consultas || 0}<small>Consultas</small></div></div>
                <div class="col"><div class="mini-kpi bg-info">${r.afiliados_unicos || 0}<small>Afiliados</small></div></div>
                <div class="col"><div class="mini-kpi bg-warning">${r.beneficiarios || 0}<small>Beneficiarios</small></div></div>
                <div class="col"><div class="mini-kpi bg-success">${r.total_logins || 0}<small>Logins</small></div></div>
                <div class="col"><div class="mini-kpi bg-dark">${r.otp_enviados || 0}<small>OTP</small></div></div>
            </div>
            <h6 class="border-bottom pb-1"><i class="fa fa-search"></i> Historial de consultas (${d.consultas.length})</h6>
            <div class="table-responsive" style="max-height:300px;">
                <table class="table table-sm table-striped">
                    <thead><tr><th>Identificación</th><th>Nombre</th><th>Tipo</th><th>Fecha</th></tr></thead>
                    <tbody>
                        ${d.consultas.slice(0, 50).map(c => `
                            <tr>
                                <td>${c.identificacion_consultada}</td>
                                <td>${c.nombre_consultado || '—'}</td>
                                <td>${badgeTipo(c.tipo_afiliado)}</td>
                                <td>${fmtFecha(c.fecha_consulta)}</td>
                            </tr>`).join('')}
                    </tbody>
                </table>
            </div>
            <h6 class="border-bottom pb-1 mt-3"><i class="fa fa-shield-alt"></i> Auditoría OTP (${d.auditoria.length})</h6>
            <div class="table-responsive" style="max-height:250px;">
                <table class="table table-sm table-striped">
                    <thead><tr><th>Acción</th><th>Resultado</th><th>IP</th><th>Fecha</th></tr></thead>
                    <tbody>
                        ${d.auditoria.map(a => `
                            <tr>
                                <td>${badgeAccion(a.accion)}</td>
                                <td>${parseInt(a.resultado) === 1 ? badge('OK', 'success') : badge('FALLO', 'danger')}</td>
                                <td>${a.ip || '—'}</td>
                                <td>${fmtFecha(a.fecha)}</td>
                            </tr>`).join('')}
                    </tbody>
                </table>
            </div>`;
        $('#modalPrestadorBody').html(html);
    }

    function renderAfiliado(d) {
        if (d.error) { $('#modalAfiliadoBody').html(`<div class="alert alert-danger">${d.error}</div>`); return; }
        const html = `
            <div class="row mb-3">
                <div class="col-md-6"><b>Identificación:</b> ${d.identificacion}</div>
                <div class="col-md-6"><b>Nombre:</b> ${d.nombre}</div>
                <div class="col-md-6"><b>Tipo:</b> ${badgeTipo(d.tipo_afiliado)}</div>
                <div class="col-md-6"><b>Total consultas:</b> ${badge(d.total_consultas, 'danger')}</div>
            </div>
            <h6 class="border-bottom pb-1"><i class="fa fa-user-md"></i> Prestadores que lo consultaron (${d.prestadores.length})</h6>
            <div class="table-responsive" style="max-height:220px;">
                <table class="table table-sm table-striped">
                    <thead><tr><th>Prestador</th><th>Consultas</th><th>Última</th></tr></thead>
                    <tbody>
                        ${d.prestadores.map(p => `
                            <tr>
                                <td>${p.email}</td>
                                <td>${badge(p.total, 'primary')}</td>
                                <td>${fmtFecha(p.ultima)}</td>
                            </tr>`).join('')}
                    </tbody>
                </table>
            </div>
            <h6 class="border-bottom pb-1 mt-3"><i class="fa fa-history"></i> Historial completo (${d.consultas.length})</h6>
            <div class="table-responsive" style="max-height:300px;">
                <table class="table table-sm table-striped">
                    <thead><tr><th>Prestador</th><th>Fecha</th><th>IP</th></tr></thead>
                    <tbody>
                        ${d.consultas.map(c => `
                            <tr>
                                <td>${c.usuario_email}</td>
                                <td>${fmtFecha(c.fecha_consulta)}</td>
                                <td>${c.ip || '—'}</td>
                            </tr>`).join('')}
                    </tbody>
                </table>
            </div>`;
        $('#modalAfiliadoBody').html(html);
    }

    /* ---------- RECARGAR ---------- */
    function recargar() {
        [dtConsultas, dtPrestadores, dtAfiliados, dtAuditoria].forEach(dt => {
            if (dt) {
                const url = dt.ajax.url();
                const base = url.split('?')[0];
                dt.ajax.url(base + (filtrosQS() ? '?' + filtrosQS() : '')).load();
            }
        });
    }

    /* ---------- INIT ---------- */
    $(function () {
        // Fechas por defecto: últimos 30 días
        const hoy = new Date();
        const hace30 = new Date(); hace30.setDate(hoy.getDate() - 30);
        $('#filtroFechaHasta').val(hoy.toISOString().slice(0, 10));
        $('#filtroFechaDesde').val(hace30.toISOString().slice(0, 10));

        cargarKPIs();
        cargarGraficas();

        initConsultas();
        initPrestadores();
        initAfiliados();
        initAuditoria();

        $('#btnFiltrar').on('click', recargar);
        $('#btnLimpiar').on('click', function () {
            $('#filtroEmail, #filtroIdentificacion, #filtroTipo, #filtroFechaDesde, #filtroFechaHasta').val('');
            recargar();
        });

        // Refresco automático de KPIs cada 60s
        setInterval(cargarKPIs, 60000);
    });

})(jQuery);