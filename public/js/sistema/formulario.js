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
function verDetalle(id) {
    $.get('detalle', { id: id }, setFormulario);
    bloqueoAjax();
}
function verDetalleDep(id) {
    $.get('detalledep', { id: id }, setFormulario);
    bloqueoAjax();
}
function setFormulario(datos) {
    $("#divContenido").html(datos);
    $('#modalFormulario').modal('show');
}
//------------------------------------------------------------------------------
function verForm(id) {
    $.get('userform', { id: id }, setFormulario);
    bloqueoAjax();
}
function addDep(id) {
    $.get('adddep', { id: id }, setFormularioAux);
    bloqueoAjax();
}
function setFormularioAux(datos) {
    $("#divContenidoAux").html(datos);
    $('#modalFormularioAux').modal('show');
}

function eliminarUserForm(idform_dependencia, idform_user) {
    Swal.fire({
        title: '&#191;DESEAS ELIMINAR EL FORMULARIO DEL CORREO&#63;',
        text: "No podras revertir esto",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#blue',
        cancelButtonColor: '#aaa',
        confirmButtonText: '<i class="fa fa-close"></i> Confirmar',
        cancelButtonText: 'Cancelar',
    }).then(function (result) {
        if (result.value) {
            $.get('eliminarUserForm', { idform_dependencia: idform_dependencia, idform_user: idform_user }, jsonEliminar, 'json');
            bloqueoAjax();
        } else {
            return false;
        }
    });

}
function jsonEliminar(datos) {
    if (parseInt(datos['exito']) === 1) {
        Swal.fire("EL FORMULARIO FUE BORRADO DEL CORREO", 'GESTORPORTAL', "success");
        $.get('userform', { id: datos['idUser'] }, setFormulario);
        return true;
    } else {
        Swal.fire("SE HA PRESENTADO UN INCONVENIENTE", 'GESTORPORTAL', "error");
        return false;
    }
}
//------------------------------------------------------------------------------
function verRegistrar() {
    $.get('registrar', {}, setFormulario);
    bloqueoAjax();
}
function verRegistrarDep() {
    $.get('registrardep', {}, setFormulario);
    bloqueoAjax();
}
function verEditar(id) {
    $.get('editar', { id: id }, setFormulario);
    bloqueoAjax();
}
function verEditarDep(id) {
    $.get('editardep', { id: id }, setFormulario);
    bloqueoAjax();
}
function validarGuardar(evt, formulario, tipo) {
    evt.preventDefault();
    Swal.fire({
        title: "&#191;DESEA " + tipo + " EL CORREO&#63;",
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
function validarGuardarDep(evt, formulario, tipo) {
    evt.preventDefault();
    Swal.fire({
        title: "&#191;DESEA " + tipo + " LA DEPENDENCIA&#63;",
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