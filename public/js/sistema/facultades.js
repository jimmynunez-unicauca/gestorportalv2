$(document).ajaxStop($.unblockUI);

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

function verRegistrar() { $.get('registrar', {}, setFormulario); bloqueoAjax(); }
function verEditar(idFacultad) { $.get('editar', { idFacultad }, setFormulario); bloqueoAjax(); }
function verDetalle(idFacultad) { $.get('detalle', { idFacultad }, setFormulario); bloqueoAjax(); }
function verEliminar(idFacultad) { $.get('eliminar', { idFacultad }, setFormulario); bloqueoAjax(); }

function setFormulario(datos) {
    $('#divContenido').html(datos);
    $('#modalFormulario').modal('show');
}

function validarGuardar(evt, formulario, tipo) {
    evt.preventDefault();
    Swal.fire({
        title: '¿DESEA ' + tipo + ' LA FACULTAD?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí',
        cancelButtonText: 'No',
        allowOutsideClick: false
    }).then((r) => {
        if (r.isConfirmed) {
            formulario.removeAttribute('onsubmit');
            formulario.submit();
            bloqueoAjax();
        }
    });
}