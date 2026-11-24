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
function verEditar(id) { $.get('editar', { idInstituto: id }, setFormulario); bloqueoAjax(); }
function verDetalle(id) { $.get('detalle', { idInstituto: id }, setFormulario); bloqueoAjax(); }
function verDesactivar(id) { $.get('desactivar', { idInstituto: id }, setFormulario); bloqueoAjax(); }
function verActivar(id) { $.get('activar', { idInstituto: id }, setFormulario); bloqueoAjax(); }
function verEliminar(id) { $.get('eliminar', { idInstituto: id }, setFormulario); bloqueoAjax(); }

function setFormulario(datos) {
    $("#divContenido").html(datos);
    $('#modalFormulario').modal('show');
}

function validarGuardar(evt, formulario, tipo) {
    evt.preventDefault();
    Swal.fire({
        title: "&iquest;DESEA " + tipo + " LA INSTITUCIÓN?",
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