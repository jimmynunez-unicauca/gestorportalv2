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
function actualizarArchivo() {
    var id = $("#id").val();
    $.get('actualizararchivo', { id: id }, setFormulario);
    bloqueoAjax();
}

//------------------------------------------------------------------------------
function setFormulario(datos) {
    $("#divContenido").html(datos);
    $('#modalFormulario').modal('show');
}
function setFormularioAux(datos) {
    $("#divContenidoAux").html(datos);
    $('#modalFormularioAux').modal('show');
}
//------------------------------------------------------------------------------

function validarGuardar(evt, formulario, tipo) {
    evt.preventDefault();
    Swal.fire({
        title: "&#191;DESEA " + tipo + " EL EMPRENDIMIENTO&#63;",
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

function validarImagen() {
    var input = $('#imagen')[0];
    var file = input.files[0];

    if (file) {
        var ext = file.name.split('.').pop().toLowerCase();
        var fileSize = file.size;
        var img = new Image();
        if (file.type.startsWith('image/')) {
            img.onload = function () {
                if (ext !== 'jpg' && ext !== 'jpeg' && ext !== 'png') {
                    Swal.fire({
                        title: "La imagen debe ser de formato JPG o PNG.",
                        text: "GestorPortalV2",
                        icon: "error",
                        confirmButtonColor: '#f0ad4e',
                        confirmButtonText: 'CERRAR',
                        allowOutsideClick: false
                    });
                    $('#imagen').val('');
                } else if (fileSize > 500000) {
                    Swal.fire({
                        title: "La imagen no debe superar 500KB.",
                        text: "GestorPortalV2",
                        icon: "error",
                        confirmButtonColor: '#f0ad4e',
                        confirmButtonText: 'CERRAR',
                        allowOutsideClick: false
                    });
                    $('#imagen').val('');
                } else {
                    Swal.fire({
                        title: "Imagen correcta.",
                        text: "GestorPortalV2",
                        icon: "success",
                        confirmButtonColor: '#f0ad4e',
                        confirmButtonText: 'CERRAR',
                        allowOutsideClick: false
                    });
                }
            };
        } else {
            Swal.fire({
                title: "El archivo no es una imagen.",
                text: "GestorPortalV2",
                icon: "error",
                confirmButtonColor: '#f0ad4e',
                confirmButtonText: 'CERRAR',
                allowOutsideClick: false
            });
            $('#imagen').val('');
        }
        img.src = URL.createObjectURL(file);
    }
}

//------------------------------------------------------------------------------
function verImagen(imagen) {
    Swal.fire({
        html: '<img src="./../../../archivos/vri_emprendimientos/' + imagen + '" width="100%" height="100%"/>',
        confirmButtonColor: '#f0ad4e',
        confirmButtonText: 'CERRAR',
        allowOutsideClick: false
    });
}
//------------------------------------------------------------------------------