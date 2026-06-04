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
function verEditar(idCalendarioAcademico) {
    $.get('editar', { idCalendarioAcademico: idCalendarioAcademico }, setFormulario);
    bloqueoAjax();
}
function verDetalle(idCalendarioAcademico) {
    $.get('detalle', { idCalendarioAcademico: idCalendarioAcademico }, setFormulario);
    bloqueoAjax();
}
function verEliminar(idCalendarioAcademico) {
    $.get('eliminar', { idCalendarioAcademico: idCalendarioAcademico }, setFormulario);
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
            $.get('moverevento', { idCalendarioAcademico: event.idCalendarioAcademico, start: event.start.format(), end: event.end.format() }, setEventoAction, 'json');
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
            $.get('moverevento', { idCalendarioAcademico: event.idCalendarioAcademico, start: event.start.format(), end: event.end.format() }, setEventoAction, 'json');
            bloqueoAjax();
        } else {
            window.location.reload();
            revertFunc();
        }
    })
}
function eliminarEvento() {
    var idCalendarioAcademico = $("#idCalendarioAcademicoAux").val();
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
            $.get('eliminar', { idCalendarioAcademico: idCalendarioAcademico }, setEventoAction, 'json');
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

//------------------------------------------------------------------------------
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

//------------------------------------------------------------------------------
function limpiarFormBusq() {
    let cont = 0;
    $("#formBusqueda input").each(function () {
        $(this).val('');
        cont++;
    });
}
//------------------------------------------------------------------------------
function selectColor(tipo) {
    if (tipo == 'Periodo académico') {
        $("#textColor").val('#FFFFFF');
        $("#color").val('#ff6c08');
    } else if (tipo == 'Inicio y finalización de clases') {
        $("#textColor").val('#FFFFFF');
        $("#color").val('#ffb000');
    } else if (tipo == 'Planeación') {
        $("#textColor").val('#FFFFFF');
        $("#color").val('#5bae40');
    } else if (tipo == 'Trámites académicos') {
        $("#textColor").val('#FFFFFF');
        $("#color").val('#00aae5');
    } else if (tipo == 'Intersemestrales') {
        $("#textColor").val('#FFFFFF');
        $("#color").val('#5a00ba');
    } else if (tipo == 'Fin periodo académico') {
        $("#textColor").val('#FFFFFF');
        $("#color").val('#db141c');
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
