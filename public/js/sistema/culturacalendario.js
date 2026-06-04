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

function verRegistrar(event) {
    $.get('registrar', { fecha: new Date(event).toISOString() }, setFormulario);
    bloqueoAjax();
}
function verEditar(idCalendarioCultura) {
    $.get('editar', { idCalendarioCultura: idCalendarioCultura }, setFormulario);
    bloqueoAjax();
}
function verDetalle(idCalendarioCultura) {
    $.get('detalle', { idCalendarioCultura: idCalendarioCultura }, setFormulario);
    bloqueoAjax();
}
function verEliminar(idCalendarioCultura) {
    $.get('eliminar', { idCalendarioCultura: idCalendarioCultura }, setFormulario);
    bloqueoAjax();
}
function moverEvento(event) {
    Swal.fire({
        title: '&#191;Est&aacute;s seguro de este cambio&#63;',
        text: event.title + " se movera a: " + event.start.format() + " - " + event.end.format(),
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'SI',
        cancelButtonText: 'NO',
        allowOutsideClick: false
    }).then((result) => {
        if (result.isConfirmed) {
            $.get('moverevento', { idCalendarioCultura: event.idCalendarioCultura, start: event.start.format(), end: event.end.format() }, setEventoAction, 'json');
            bloqueoAjax();
        } else {
            window.location.reload();
            revertFunc();
        }
    })
}
function redimensionar(event) {
    Swal.fire({
        title: '&#191;Est&aacute;s seguro de este cambio&#63;',
        text: event.title + " se movera a: " + event.start.format() + " - " + event.end.format(),
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'SI',
        cancelButtonText: 'NO',
        allowOutsideClick: false
    }).then((result) => {
        if (result.isConfirmed) {
            $.get('moverevento', { idCalendarioCultura: event.idCalendarioCultura, start: event.start.format(), end: event.end.format() }, setEventoAction, 'json');
            bloqueoAjax();
        } else {
            window.location.reload();
            revertFunc();
        }
    })
}
function eliminarEvento() {
    var idCalendarioCultura = $("#idCalendarioCulturaAux").val();
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
            $.get('eliminar', { idCalendarioCultura: idCalendarioCultura }, setEventoAction, 'json');
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
    Swal.fire({
        title: "&#191;DESEA " + tipo + " EL EVENTO&#63;",
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
function selectColor(tipo) {
    if (tipo == 'Direccion') {
        $("#textColor").val('#FFFFFF');
        $("#color").val('#84b723');
    } else if (tipo == 'Division Salud') {
        $("#textColor").val('#FFFFFF');
        $("#color").val('#20aae5');
    } else if (tipo == 'Division Cultura') {
        $("#textColor").val('#FFFFFF');
        $("#color").val('#ffb000');
    } else if (tipo == 'Division Deporte') {
        $("#textColor").val('#FFFFFF');
        $("#color").val('#5a00ba');
    } else {
        $("#textColor").val('#FFFFFF');
        $("#color").val('#000066');
    }
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
//------------------------------------------------------------------------------
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
                } else if (this.width !== 1080 || this.height !== 1350) {
                    Swal.fire({
                        title: "La imagen debe tener dimensiones iguales a 1080x1350 píxeles.",
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
            '<img src="./../../../archivos/cultura/' + imagen + '" width="100%" height="100%"/>',
        confirmButtonColor: '#f0ad4e',
        confirmButtonText: 'CERRAR',
        allowOutsideClick: false
    });
}
//------------------------------------------------------------------------------
function actualizarImagen() {
    var idCalendarioCultura = $("#idCalendarioCultura").val();
    $.get('actualizarimagen', { idCalendarioCultura: idCalendarioCultura }, setFormulario);
    bloqueoAjax();
}
//------------------------------------------------------------------------------