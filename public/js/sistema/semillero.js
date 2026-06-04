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
function verEditar(idSI) {
    $.get('editar', { idSI: idSI }, setFormulario);
    bloqueoAjax();
}
function verDetalle(idSI) {
    $.get('detalle', { idSI: idSI }, setFormulario);
    bloqueoAjax();
}
function verEliminar(idSI) {
    $.get('eliminar', { idSI: idSI }, setFormulario);
    bloqueoAjax();
}
function verActivar(idSI) {
    $.get('activar', { idSI: idSI }, setFormulario);
    bloqueoAjax();
}
function setFormulario(datos) {
    $("#divContenido").html(datos);
    $('#modalFormulario').modal('show');
}

function validarGuardar(evt, formulario, tipo) {
    evt.preventDefault();
    Swal.fire({
        title: "&#191;DESEA " + tipo + "&#63;",
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
function actualizarArchivo() {
    var idSI = $("#idSI").val();
    $.get('actualizararchivo', { idSI: idSI }, setFormulario);
    bloqueoAjax();
}
function setFormularioAux(datos) {
    $("#divContenidoAux").html(datos);
    $('#modalFormularioAux').modal('show');
}
//------------------------------------------------------------------------------
function validarAdjunto() {
    var input = $('#imagen')[0];
    var file = input.files[0];

    if (file) {
        var ext = file.name.split('.').pop().toLowerCase();
        var fileSize = file.size; // tamaño en bytes
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
                } else if (fileSize > 2000000) { // 2MB    
                    Swal.fire({
                        title: "La imagen no debe superar 2MB.",
                        text: "GestorPortalV2",
                        icon: "error",
                        confirmButtonColor: '#f0ad4e',
                        confirmButtonText: 'CERRAR',
                        allowOutsideClick: false
                    });
                    $('#imagen').val('');
                } /* else if (this.width !== 700 || this.height !== 700) {
                    Swal.fire({
                        title: "La imagen debe tener dimensiones iguales a 700x700 píxeles.",
                        text: "GestorPortalV2",
                        icon: "error",
                        confirmButtonColor: '#f0ad4e',
                        confirmButtonText: 'CERRAR',
                        allowOutsideClick: false
                    });
                    $('#imagen').val('');
                } */ else {
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
        html:
            '<img src="./../../../archivos/vicerrectoria_investigaciones/' + imagen + '" width="100%" height="100%"/>',
        confirmButtonColor: '#f0ad4e',
        confirmButtonText: 'CERRAR',
        allowOutsideClick: false
    });
}
//------------------------------------------------------------------------------