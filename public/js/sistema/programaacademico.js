// Desbloquea la UI cuando termina cualquier AJAX (éxito o error)
$(document).ajaxStop(function () {
    $.unblockUI();
});

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
function verEditar(idPrograma) { $.get('editar', { idPrograma }, setFormulario); bloqueoAjax(); }
function verDetalle(idPrograma) { $.get('detalle', { idPrograma }, setFormulario); bloqueoAjax(); }
function verEliminar(idPrograma) { $.get('eliminar', { idPrograma }, setFormulario); bloqueoAjax(); }

function setFormulario(datos) {
    $('#divContenido').html(datos);
    $('#modalFormulario').modal('show');
}

function validarGuardar(evt, formulario, tipo) {
    evt.preventDefault();
    Swal.fire({
        title: '¿DESEA ' + tipo + ' EL PROGRAMA ACADÉMICO?',
        text: '',
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