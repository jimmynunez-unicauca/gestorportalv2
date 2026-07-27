//------------------------------------------------------------------------------
// BLOQUEO DE INTERFAZ
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
// FUNCIONES PARA ABRIR MODALES
//------------------------------------------------------------------------------
function verRegistrar(event) {
    $.get('registrar', { fecha: new Date(event).toISOString() }, setFormulario);
    bloqueoAjax();
}
function verEditar(idEvento) {
    $.get('editar', { idEvento: idEvento }, setFormulario);
    bloqueoAjax();
}
function verDetalle(idEvento) {
    $.get('detalle', { idEvento: idEvento }, setFormulario);
    bloqueoAjax();
}
function verEliminar(idEvento) {
    $.get('eliminar', { idEvento: idEvento }, setFormulario);
    bloqueoAjax();
}
function setFormulario(datos) {
    $("#divContenido").html(datos);
    $('#modalFormulario').modal('show');
}

//------------------------------------------------------------------------------
// FUNCIONES PARA MOVER Y ELIMINAR EVENTOS
//------------------------------------------------------------------------------
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
            $.get('moverevento', { idEvento: event.idEvento, start: event.start.format(), end: event.end.format() }, setEventoAction, 'json');
            bloqueoAjax();
        } else {
            window.location.reload();
            revertFunc();
        }
    });
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
            $.get('moverevento', { idEvento: event.idEvento, start: event.start.format(), end: event.end.format() }, setEventoAction, 'json');
            bloqueoAjax();
        } else {
            window.location.reload();
            revertFunc();
        }
    })
}
function eliminarEvento() {
    var idEvento = $("#idEventoAux").val();
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
            $.get('eliminar', { idEvento: idEvento }, setEventoAction, 'json');
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

//------------------------------------------------------------------------------
// VALIDACIÓN DE FECHAS (start y end) - NUEVA VERSIÓN
//------------------------------------------------------------------------------
/**
 * Convierte un string en formato 'YYYY-MM-DDTHH:mm' a objeto Date
 * (compatible con datetime-local)
 */
function stringToDate(str) {
    if (!str) return null;
    return new Date(str.replace('T', ' '));
}

/**
 * Valida que start sea menor que end y que ambos no estén vacíos.
 * Retorna true si es válido, false en caso contrario.
 * Actualiza los estados visuales con clases de Bootstrap.
 */
function validarFechas() {
    const startInput = document.getElementById('start');
    const endInput = document.getElementById('end');
    const startVal = startInput.value;
    const endVal = endInput.value;

    // Limpiar estados previos
    startInput.classList.remove('is-valid', 'is-invalid');
    endInput.classList.remove('is-valid', 'is-invalid');
    document.getElementById('startFeedback')?.remove();
    document.getElementById('endFeedback')?.remove();

    // Si alguno está vacío, no validamos (el required se encargará)
    if (!startVal || !endVal) {
        return true;
    }

    const startDate = stringToDate(startVal);
    const endDate = stringToDate(endVal);

    if (isNaN(startDate.getTime()) || isNaN(endDate.getTime())) {
        mostrarError(startInput, 'Formato de fecha inválido.');
        mostrarError(endInput, 'Formato de fecha inválido.');
        return false;
    }

    if (startDate >= endDate) {
        mostrarError(startInput, 'La fecha de inicio debe ser anterior a la de finalización.');
        mostrarError(endInput, 'La fecha de finalización debe ser posterior a la de inicio.');
        return false;
    }

    // Todo válido
    startInput.classList.add('is-valid');
    endInput.classList.add('is-valid');
    return true;
}

/**
 * Muestra un mensaje de error debajo del campo, con la clase is-invalid.
 */
function mostrarError(input, mensaje) {
    input.classList.add('is-invalid');
    const existing = input.parentNode.querySelector('.invalid-feedback');
    if (existing) existing.remove();
    const div = document.createElement('div');
    div.className = 'invalid-feedback';
    div.id = input.id + 'Feedback';
    div.textContent = mensaje;
    input.parentNode.appendChild(div);
}

//------------------------------------------------------------------------------
// EVENTOS EN TIEMPO REAL PARA VALIDACIÓN DE FECHAS
//------------------------------------------------------------------------------
$(document).on('change', '#start, #end', function () {
    validarFechas();
});

$(document).on('input', '#start, #end', function () {
    const input = this;
    if (input.value === '') {
        input.classList.remove('is-valid', 'is-invalid');
        const fb = input.parentNode.querySelector('.invalid-feedback');
        if (fb) fb.remove();
    } else {
        validarFechas();
    }
});

//------------------------------------------------------------------------------
// VALIDACIÓN AL ENVIAR EL FORMULARIO (reemplaza la función antigua)
//------------------------------------------------------------------------------
function validarGuardar(evt, formulario, tipo) {
    // 1. Validar fechas
    if (!validarFechas()) {
        Swal.fire({
            title: 'Error en fechas',
            text: 'Corrija los campos de fecha marcados en rojo.',
            icon: 'error',
            confirmButtonText: 'Aceptar'
        });
        evt.preventDefault();
        return false;
    }

    // 2. Validar contenido del editor (CKEditor)
    if (typeof editor !== 'undefined' && editor) {
        var detalleContenido = editor.getData().trim();
        if (detalleContenido === '') {
            Swal.fire({
                title: 'Error',
                text: 'El campo Contenido no puede estar vacío.',
                icon: 'error'
            });
            evt.preventDefault();
            return false;
        }
    }

    // 3. Confirmación de guardado
    evt.preventDefault();
    Swal.fire({
        title: '¿Desea ' + tipo + ' el evento?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí',
        cancelButtonText: 'No',
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
// FUNCIONES AUXILIARES (para otros módulos, pero no se eliminan)
//------------------------------------------------------------------------------
function getMunicipios(idDepartamento) {
    if (idDepartamento !== '') {
        $.get('getselectmunicipios', { idDepartamento: idDepartamento }, setMunicipios);
        bloqueoAjax();
    } else {
        $("#idMunicipio").html("<option value=''>Seleccione...</option>");
    }
}
function setMunicipios(html) {
    $("#idMunicipio").html(html);
}

function existeIdentificacion() {
    if ($("#identificacion").val() !== '') {
        $.get('existeidentificacion', { identificacion: $("#identificacion").val() }, setExisteIdentificacion, 'json');
        bloqueoAjax();
    }
}
function setExisteIdentificacion(datos) {
    if (parseInt(datos['error']) === 0) {
        if (parseInt(datos['existe']) === 1) {
            Swal.fire("LA IDENTIFICACION ( " + datos['identificacion'] + " ) YA SE ENCUENTRA REGISTRADA EN <i><i class='fa fa-paw'></i>JIMSOFT</i>.", "<i><i class='fa fa-paw'></i>JIMSOFT</i>", "error");
            $("#identificacion").val('');
            $("#identificacion").focus();
            return false;
        } else {
            return true;
        }
    } else {
        alert("SE HA PRESENTADO UN INCONVENIENTE EN <i><i class='fa fa-paw'></i>JIMSOFT</i>.");
        return false;
    }
}

function limpiarFormBusq() {
    let cont = 0;
    $("#formBusqueda input").each(function () {
        $(this).val('');
        cont++;
    });
}

function selectColor(tipo) {
    if (tipo == 'Periodo academico') {
        $("#textColor").val('#FFFFFF');
        $("#color").val('#ff6c08');
    } else if (tipo == 'Inicio y finalizacion de clases') {
        $("#textColor").val('#FFFFFF');
        $("#color").val('#ffb000');
    } else if (tipo == 'Planeacion') {
        $("#textColor").val('#FFFFFF');
        $("#color").val('#5bae40');
    } else if (tipo == 'Tramites academicos') {
        $("#textColor").val('#FFFFFF');
        $("#color").val('#00aae5');
    } else if (tipo == 'Intersemestrales') {
        $("#textColor").val('#FFFFFF');
        $("#color").val('#5a00ba');
    } else if (tipo == 'Fin periodo academico') {
        $("#textColor").val('#FFFFFF');
        $("#color").val('#db141c');
    } else {
        $("#textColor").val('#FFFFFF');
        $("#color").val('#000066');
    }
}

function actualizarImagen() {
    var idEvento = $("#idEvento").val();
    $.get('actualizarimagen', { idEvento: idEvento }, setFormularioAux);
    bloqueoAjax();
}
function setFormularioAux(datos) {
    $("#divContenidoAux").html(datos);
    $('#modalFormularioAux').modal('show');
}

//------------------------------------------------------------------------------
// VALIDACIÓN DE IMAGEN (mantenida como estaba)
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
                } else if (fileSize > 2000000) {
                    Swal.fire({
                        title: "La imagen no debe superar 2MB.",
                        text: "GestorPortalV2",
                        icon: "error",
                        confirmButtonColor: '#f0ad4e',
                        confirmButtonText: 'CERRAR',
                        allowOutsideClick: false
                    });
                    $('#imagen').val('');
                } else if (this.width !== 700 || this.height !== 700) {
                    Swal.fire({
                        title: "La imagen debe tener dimensiones iguales a 700x700 píxeles.",
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
        html: '<img src="./../../../archivos/eventos/' + imagen + '" width="100%" height="100%"/>',
        confirmButtonColor: '#f0ad4e',
        confirmButtonText: 'CERRAR',
        allowOutsideClick: false
    });
}

//------------------------------------------------------------------------------
// INICIALIZACIÓN (opcional)
//------------------------------------------------------------------------------
$(document).ready(function () {
    // No es necesario agregar nada aquí, la validación de fechas ya está
    // vinculada mediante los eventos 'change' e 'input'.
});