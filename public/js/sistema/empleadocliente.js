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
function verEditar(idEmpleado) {
    $.get('editar', { idEmpleado: idEmpleado }, setFormulario);
    bloqueoAjax();
}
function verDetalle(idEmpleado) {
    $.get('detalle', { idEmpleado: idEmpleado }, setFormulario);
    bloqueoAjax();
}
function verEliminar(idEmpleado) {
    $.get('eliminar', { idEmpleado: idEmpleado }, setFormulario);
    bloqueoAjax();
}
function setFormulario(datos) {
    $("#divContenido").html(datos);
    $('#modalFormulario').modal('show');
}

function validarGuardar(evt, formulario, tipo) {
    evt.preventDefault();
    Swal.fire({
        title: "DESEA " + tipo + " EL EMPLEADO ?",
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
