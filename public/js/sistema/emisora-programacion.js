//------------------------------------------------------------------------------
function bloqueoAjax() {
    $.blockUI({
        message: $('#msgBloqueo'),
        css: {
            border: 'none', padding: '15px', backgroundColor: '#000',
            '-webkit-border-radius': '10px', '-moz-border-radius': '10px',
            opacity: .85, color: '#fff', 'z-index': 10000000
        }
    });
    $('.blockOverlay').attr('style', $('.blockOverlay').attr('style') + 'z-index: 1100 !important');
}
//------------------------------------------------------------------------------
function verRegistrarProgramacion() {
    $.get('registrar', {}, setFormulario);
    bloqueoAjax();
}
function verEditarProgramacion(id) {
    $.get('editar', { idProgramacion: id }, setFormulario);
    bloqueoAjax();
}
function verDetalleProgramacion(id) {
    $.get('detalle', { idProgramacion: id }, setFormulario);
    bloqueoAjax();
}
function verActivarProgramacion(id) {
    $.get('activar', { idProgramacion: id }, setFormulario);
    bloqueoAjax();
}
function verDesactivarProgramacion(id) {
    $.get('desactivar', { idProgramacion: id }, setFormulario);
    bloqueoAjax();
}
function verEliminarProgramacion(id) {
    $.get('eliminar', { idProgramacion: id }, setFormulario);
    bloqueoAjax();
}
function verDetalle(idPrograma) {
    $.get('../emisora/detalle', { idPrograma: idPrograma }, setFormulario);
    bloqueoAjax();
}
function setFormulario(datos) {
    $("#divContenido").html(datos);
    $('#modalFormulario').modal('show');
    $('#vigente_hasta').removeAttr('required');
}
//------------------------------------------------------------------------------
function validarGuardarProgramacion(evt, formulario, tipo) {
    evt.preventDefault();
    var requiereDias = (tipo === 'REGISTRAR' || tipo === 'EDITAR');
    if (requiereDias) {
        var diasSeleccionados = document.querySelectorAll('input[name="dias[]"]:checked').length;
        if (diasSeleccionados === 0) {
            Swal.fire("ERROR", "Debe seleccionar al menos un día.", "error");
            return false;
        }
    }
    Swal.fire({
        title: "&iquest;DESEA " + tipo + " LA PROGRAMACIÓN?",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Si",
        cancelButtonText: "No",
        allowOutsideClick: false
    }).then((result) => {
        if (result.isConfirmed) {
            formulario.removeAttribute('onsubmit');
            formulario.submit();
            bloqueoAjax();
        }
    });
}
//------------------------------------------------------------------------------
function initCalendarioProgramacion() {
    var $cal = $('#calendar-programacion');
    if (!$cal.length || typeof $cal.fullCalendar !== 'function') return;
    var eventsUrl = $cal.data('events-url') || 'calendar';

    $cal.fullCalendar({
        header: {
            left: 'prev,next today',
            center: 'title',
            right: 'month,agendaWeek,agendaDay'
        },
        defaultView: 'agendaWeek',
        locale: 'es',
        height: 700,
        editable: true,              // Habilita arrastrar y redimensionar
        eventStartEditable: true,    // Permite mover el inicio (arrastrar)
        eventDurationEditable: true, // Permite cambiar la duración (redimensionar)
        nowIndicator: true,          // Muestra la línea roja de la hora actual
        timezone: 'America/Bogota',  // Zona horaria para la línea y los eventos
        selectable: false,
        eventLimit: true,
        allDaySlot: false,
        minTime: '00:00:00',
        maxTime: '24:00:00',
        slotLabelFormat: 'HH:mm',
        timeFormat: 'HH:mm',
        events: eventsUrl,
        eventClick: function (event) {
            if (event.id && String(event.id).indexOf('ex_') === 0) {
                Swal.fire('Excepción', 'Este evento es un reemplazo por excepción y no se puede editar aquí.', 'info');
                return;
            }
            verDetalleProgramacion(event.id);
        },
        // --- Callback al arrastrar un evento ---
        eventDrop: function (event, delta, revertFunc) {
            if (String(event.id).indexOf('ex_') === 0) {
                Swal.fire('Aviso', 'Los eventos de excepción no se pueden mover.', 'warning');
                revertFunc();
                return;
            }

            var nuevoInicio = event.start.format('YYYY-MM-DDTHH:mm:ss');
            var nuevoFin = event.end ? event.end.format('YYYY-MM-DDTHH:mm:ss') : null;

            $.ajax({
                url: 'actualizarhorario',
                type: 'POST',
                dataType: 'json',
                data: {
                    idProgramacion: event.id,
                    nuevoInicio: nuevoInicio,
                    nuevoFin: nuevoFin
                }
            }).done(function (res) {
                if (res.success) {
                    Swal.fire('Éxito', 'Horario actualizado correctamente.', 'success');
                    $cal.fullCalendar('refetchEvents');
                } else {
                    Swal.fire('Error', res.message || 'No se pudo actualizar el horario.', 'error');
                    revertFunc(); // Revierte el cambio visual si falla el servidor
                }
            }).fail(function () {
                Swal.fire('Error', 'Error de conexión al actualizar el horario.', 'error');
                revertFunc();
            });
        },
        // --- Callback al redimensionar un evento ---
        eventResize: function (event, delta, revertFunc) {
            if (String(event.id).indexOf('ex_') === 0) {
                Swal.fire('Aviso', 'Los eventos de excepción no se pueden redimensionar.', 'warning');
                revertFunc();
                return;
            }

            var nuevoInicio = event.start.format('YYYY-MM-DDTHH:mm:ss');
            var nuevoFin = event.end ? event.end.format('YYYY-MM-DDTHH:mm:ss') : null;

            $.ajax({
                url: 'actualizarhorario',
                type: 'POST',
                dataType: 'json',
                data: {
                    idProgramacion: event.id,
                    nuevoInicio: nuevoInicio,
                    nuevoFin: nuevoFin
                }
            }).done(function (res) {
                if (res.success) {
                    Swal.fire('Éxito', 'Duración actualizada correctamente.', 'success');
                    $cal.fullCalendar('refetchEvents');
                } else {
                    Swal.fire('Error', res.message || 'No se pudo actualizar la duración.', 'error');
                    revertFunc();
                }
            }).fail(function () {
                Swal.fire('Error', 'Error de conexión al actualizar la duración.', 'error');
                revertFunc();
            });
        },
        loading: function (isLoading) {
            if (isLoading) bloqueoAjax(); else $.unblockUI();
        }
    });
}
//------------------------------------------------------------------------------
function refrescarCalendario() {
    var $cal = $('#calendar-programacion');
    if ($cal.length && typeof $cal.fullCalendar === 'function') {
        $cal.fullCalendar('refetchEvents');
    }
}