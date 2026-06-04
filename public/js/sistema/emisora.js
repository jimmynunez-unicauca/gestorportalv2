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
function verEditar(idPrograma) {
    $.get('editar', { idPrograma: idPrograma }, setFormulario);
    bloqueoAjax();
}
function verDetalle(idPrograma) {
    $.get('detalle', { idPrograma: idPrograma }, setFormulario);
    bloqueoAjax();
}
function verEliminar(idPrograma) {
    $.get('eliminar', { idPrograma: idPrograma }, setFormulario);
    bloqueoAjax();
}
function verActivar(idPrograma) {
    $.get('activar', { idPrograma: idPrograma }, setFormulario);
    bloqueoAjax();
}
function eliminarEvento() {
    var idPrograma = $("#idProgramaAux").val();
    Swal.fire({
        title: '&#191;Est&aacute;s seguro de eliminar el evento&#63;',
        text: 'No podra revertir esto',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'SI',
        cancelButtonText: 'NO',
        allowOutsideClick: false
    }).then((result) => {
        if (result.isConfirmed) {
            $.get('eliminar', { idPrograma: idPrograma }, setEventoAction, 'json');
            bloqueoAjax();
        }
    })
}
function setEventoAction(datos) {
    if (parseInt(datos['successOK']) === 1) {
        window.location.reload();
    } else {
        alert("SE HA PRESENTADO UN INCONVENIENTE EN <i><i class='fa fa-paw'></i>JIMSOFT</i>.");
        return false;
    }
}
function setFormulario(datos) {
    $("#divContenido").html(datos);
    $('#modalFormulario').modal('show');
}

function validarGuardar(evt, formulario, tipo) {
    evt.preventDefault();
    var diasSeleccionados = $('input[name="dias[]"]:checked').length;
    if (diasSeleccionados === 0) {
        Swal.fire("ERROR", "Debe seleccionar al menos un día de transmisión.", "error");
        return false
    } else {
        Swal.fire({
            title: "&#191;DESEA " + tipo + " EL PROGRAMA&#63;",
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
function actualizarImagen() {
    var idPrograma = $("#idPrograma").val();
    $.get('actualizarimagen', { idPrograma: idPrograma }, setFormulario);
    bloqueoAjax();
}
function setFormularioAux(datos) {
    $("#divContenidoAux").html(datos);
    $('#modalFormularioAux').modal('show');
}
//------------------------------------------------------------------------------
function validarFecha() {
    if ($("#start").val() != '' && $("#end").val() != '') {
        if ($("#start").val() > $("#end").val()) {
            Swal.fire({
                title: "ERROR",
                text: "La fecha final no puede ser menor a la inicial",
                icon: "error"
            });
            $("#start").val('')
            $("#end").val('')
        }
    }
}

function validarImagen() {
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
                } else if (fileSize > 512000) { // 500KB    
                    Swal.fire({
                        title: "La imagen no debe superar 500KB.",
                        text: "GestorPortalV2",
                        icon: "error",
                        confirmButtonColor: '#f0ad4e',
                        confirmButtonText: 'CERRAR',
                        allowOutsideClick: false
                    });
                    $('#imagen').val('');
                } else if (this.width !== 1000 || this.height !== 1000) {
                    Swal.fire({
                        title: "La imagen debe tener dimensiones iguales a 1000x1000 píxeles.",
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

function verImagen(imagen) {
    Swal.fire({
        html:
            '<img src="./../../../archivos/emisora/programas/' + imagen + '" width="100%" height="100%"/>',
        confirmButtonColor: '#f0ad4e',
        confirmButtonText: 'CERRAR',
        allowOutsideClick: false
    });
}