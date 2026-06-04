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
function verEditar(idRol) {
    $.get('editar', { idRol: idRol }, setFormulario);
    bloqueoAjax();
}
function verDetalle(idRol) {
    $.get('detalle', { idRol: idRol }, setFormulario);
    bloqueoAjax();
}
function verEliminar(idRol) {
    $.get('eliminar', { idRol: idRol }, setFormulario);
    bloqueoAjax();
}

function verRecursos(idRol) {
    $.get('rolesrcursos', { idRol: idRol }, setFormulario);
    bloqueoAjax();
}
function addRecurso(idRol) {
    $.get('addrecurso', { idRol: idRol }, setFormularioAux);
    bloqueoAjax();
}
function eliminarRecursoRol(idRol, idRecurso) {
    Swal.fire({
        title: '&#191;DESEAS ELIMINAR EL RECURSO DEL ROL&#63;',
        text: "No podras revertir esto",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#blue',
        cancelButtonColor: '#aaa',
        confirmButtonText: '<i class="fa fa-close"></i> Confirmar',
        cancelButtonText: 'Cancelar',
    }).then(function (result) {
        if (result.value) {
            $.get('eliminarRecursoRol', { idRol: idRol, idRecurso: idRecurso }, jsonEliminar, 'json');
            bloqueoAjax();
        } else {
            return false;
        }
    });

}
function jsonEliminar(datos) {
    if (parseInt(datos['exito']) === 1) {
        Swal.fire("EL RECURSO FUE ELIMINADO CON EXITO DEL ROL", 'GESTORPORTAL', "success");
        $.get('rolesrcursos', { idRol: datos['idRol'] }, setFormulario);
        return true;
    } else {
        Swal.fire("SE HA PRESENTADO UN INCONVENIENTE", 'GESTORPORTAL', "error");
        return false;
    }
}
//------------------------------------------------------------------------------
function verRegistrar2() {
    $.get('registrar2', {}, setFormulario);
    bloqueoAjax();
}
function verEditar2(idRecurso) {
    $.get('editar2', { idRecurso: idRecurso }, setFormulario);
    bloqueoAjax();
}
function verDetalle2(idRecurso) {
    $.get('detalle2', { idRecurso: idRecurso }, setFormulario);
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