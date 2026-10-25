function bloqueoAjax() {
    $.blockUI({ message: $('#msgBloqueo'), css: { border: 'none', padding: '15px', backgroundColor: '#000', opacity: .85, color: '#fff', 'z-index': 10000000 } });
    $('.blockOverlay').attr('style', $('.blockOverlay').attr('style') + 'z-index: 1100 !important');
}

function verRegistrarExcepcion() {
    $.get(window.baseUrlExcepcion + '/registrar', {}, setFormulario);
    bloqueoAjax();
}
function verEditarExcepcion(id) {
    $.get(window.baseUrlExcepcion + '/editar', { idExcepcion: id }, setFormulario);
    bloqueoAjax();
}
function verDetalleExcepcion(id) {
    $.get(window.baseUrlExcepcion + '/detalle', { idExcepcion: id }, setFormulario);
    bloqueoAjax();
}
function verEliminarExcepcion(id) {
    $.get(window.baseUrlExcepcion + '/eliminar', { idExcepcion: id }, setFormulario);
    bloqueoAjax();
}

function setFormulario(datos) {
    $("#divContenido").html(datos);
    $('#modalFormulario').modal('show');
    toggleCamposExcepcion(); // Llamar al abrir para mostrar/ocultar según el tipo actual
}

function toggleCamposExcepcion() {
    var tipo = $('#tipo').val();

    // Ocultar todos
    $('#grupo_programacion_original').hide();
    $('#grupo_programa_reemplazo').hide();
    $('#grupo_hora_inicio').hide();
    $('#grupo_hora_fin').hide();

    if (tipo === 'cancelacion') {
        $('#grupo_programacion_original').show();
    } else if (tipo === 'cancelacion_dia') {
        // No requiere más campos (solo fecha y motivo)
    } else if (tipo === 'reemplazo') {
        $('#grupo_programa_reemplazo').show();
        $('#grupo_hora_inicio').show();
        $('#grupo_hora_fin').show();
    } else if (tipo === 'ajuste_horario') {
        $('#grupo_programacion_original').show();
        $('#grupo_hora_inicio').show();
        $('#grupo_hora_fin').show();
    }
}

function validarGuardarExcepcion(evt, formulario, tipo) {
    evt.preventDefault();
    Swal.fire({
        title: "&iquest;DESEA " + tipo + " LA EXCEPCIÓN?",
        icon: "warning", showCancelButton: true, confirmButtonText: "Si", cancelButtonText: "No"
    }).then((result) => {
        if (result.isConfirmed) {
            formulario.removeAttribute('onsubmit');
            formulario.submit();
            bloqueoAjax();
        }
    });
}