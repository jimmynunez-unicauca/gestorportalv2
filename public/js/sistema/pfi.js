//------------------------------------------------------------------------------
function bloqueoAjax() {
    $.blockUI(
        {
            message: $('#msgBloqueo'),
            css: {
                border: 'none',
                padding: '15px',
                backgroundColor: '#000',
                '-webkit-border-radius': '10px',
                '-moz-border-radius': '10px',
                opacity: .85,
                color: '#fff',
                'z-index': 10000000
            }
        }
    );
    $('.blockOverlay').attr('style', $('.blockOverlay').attr('style') + 'z-index: 1100 !important');
}

//------------------------------------------------------------------------------

function verRegistrar() {
    $.get('registrar', {}, setFormulario);
    bloqueoAjax();
}
function verEditar(id) {
    $.get('editar', { id: id }, setFormulario);
    bloqueoAjax();
}
function verDetalle(id) {
    $.get('detalle', { id: id }, setFormulario);
    bloqueoAjax();
}
function verEliminar(id) {
    $.get('eliminar', { id: id }, setFormulario);
    bloqueoAjax();
}
function verActivar(id) {
    $.get('activar', { id: id }, setFormulario);
    bloqueoAjax();
}
function verDesactivar(id) {
    $.get('desactivar', { id: id }, setFormulario);
    bloqueoAjax();
}
function setFormulario(datos) {
    $("#divContenido").html(datos);
    $('#modalFormulario').modal('show');
}
function setFormularioAux(datos) {
    $("#divContenidoAux").html(datos);
    $('#modalFormularioAux').modal('show');
}

function validarGuardar(evt, formulario, tipo) {
    evt.preventDefault();
    Swal.fire({
        title: "&#191;DESEA " + tipo + " EL FORMULARIO&#63;",
        text: "",
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