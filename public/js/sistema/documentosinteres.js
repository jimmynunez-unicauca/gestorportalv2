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
function verEditar(idDocumentosInteres) {
    $.get('editar', { idDocumentosInteres: idDocumentosInteres }, setFormulario);
    bloqueoAjax();
}
function verDetalle(idDocumentosInteres) {
    $.get('detalle', { idDocumentosInteres: idDocumentosInteres }, setFormulario);
    bloqueoAjax();
}
function verEliminar(idDocumentosInteres) {
    $.get('eliminar', { idDocumentosInteres: idDocumentosInteres }, setFormulario);
    bloqueoAjax();
}
function verActivar(idDocumentosInteres) {
    $.get('activar', { idDocumentosInteres: idDocumentosInteres }, setFormulario);
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
    if ($('#idLvmen').val() == '' || $('#nombre').val() == '') {
        Swal.fire({
            title: 'FALTA IMPLEMENTAR EL DOCUMENTO',
            text: 'GESTORPORTALv2',
            icon: 'error',
            allowOutsideClick: false
        });
        return false;
    } else {
        evt.preventDefault();
        Swal.fire({
            title: "&#191;DESEA " + tipo + " EL DOCUMENTO&#63;",
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
}

//------------------------------------------------------------------------------
function verBuscar() {
    $.get('buscardocumento', {}, setFormularioAux);
    bloqueoAjax();
}

function selectLvmen(idLvmen, nombre) {
    $('#idLvmen').val(idLvmen);
    $('#nombre').text(nombre);
    $('#modalFormularioAux').modal('hide');
    $('#modalFormularioAux').on('hidden.bs.modal', function (e) {
        $('#modalFormulario').css('overflow-y', 'auto');
    });
}
//------------------------------------------------------------------------------