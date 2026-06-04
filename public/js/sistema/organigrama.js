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
    $.get('registrar', {}, function (datos) {
        $("#divContenido").html(datos);
        $('#modalFormulario').modal('show');
    });
    bloqueoAjax();
}

function verEditar(id) {
    $.get('editar', { id: id }, function (datos) {
        $("#divContenido").html(datos);
        $('#modalFormulario').modal('show');
    });
    bloqueoAjax();
}

function verDetalle(id) {
    $.get('detalle', { id: id }, function (datos) {
        $("#divContenido").html(datos);
        $('#modalFormulario').modal('show');
    });
    bloqueoAjax();
}

function verEliminar(id) {
    $.get('eliminar', { id: id }, function (datos) {
        $("#divContenido").html(datos);
        $('#modalFormulario').modal('show');
    });
    bloqueoAjax();
}

function verActivar(id) {
    $.get('activar', { id: id }, function (datos) {
        $("#divContenido").html(datos);
        $('#modalFormulario').modal('show');
    });
    bloqueoAjax();
}

function validarGuardar(event, form, tipo) {
    event.preventDefault();
    Swal.fire({
        title: "¿DESEA " + tipo + " EL NODO?",
        text: "",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Si",
        cancelButtonText: "No",
        allowOutsideClick: false
    }).then((result) => {
        if (result.isConfirmed) {
            form.submit();
            bloqueoAjax();
        }
    });
    return false;
}