//------------------------------------------------------------------------------
function bloqueoAjax() {
    $.blockUI({
        message: $('#msgBloqueo'),
        css: {
            border: 'none', padding: '15px', backgroundColor: '#000',
            '-webkit-border-radius': '10px', '-moz-border-radius': '10px',
            opacity: .85, color: '#fff', 'z-index': 10000000
        }
    });
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
function verDesactivar(idPrograma) {
    $.get('desactivar', { idPrograma: idPrograma }, setFormulario);
    bloqueoAjax();
}
function verActivar(idPrograma) {
    $.get('activar', { idPrograma: idPrograma }, setFormulario);
    bloqueoAjax();
}
function verEliminar(idPrograma) {
    $.get('eliminar', { idPrograma: idPrograma }, setFormulario);
    bloqueoAjax();
}
function verActualizarImagen(idPrograma) {
    $.get('actualizarimagen', { idPrograma: idPrograma }, setFormularioAux);
    bloqueoAjax();
}
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
        title: "&iquest;DESEA " + tipo + " EL PROGRAMA?",
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
function actualizarImagen() {
    var idPrograma = $("#idPrograma").val();
    verActualizarImagen(idPrograma);
}
//------------------------------------------------------------------------------
function validarImagen() {
    var input = $('#imagen')[0];
    if (!input || !input.files || !input.files[0]) return;
    var file = input.files[0];
    var ext = file.name.split('.').pop().toLowerCase();
    var fileSize = file.size;
    var img = new Image();

    if (!file.type.startsWith('image/')) {
        Swal.fire("El archivo no es una imagen.", "GestorPortalV2", "error");
        $('#imagen').val('');
        return;
    }
    img.onload = function () {
        if (ext !== 'jpg' && ext !== 'jpeg' && ext !== 'png') {
            Swal.fire("La imagen debe ser de formato JPG o PNG.", "GestorPortalV2", "error");
            $('#imagen').val('');
        } else if (fileSize > 512000) {
            Swal.fire("La imagen no debe superar 500KB.", "GestorPortalV2", "error");
            $('#imagen').val('');
        } else if (this.width !== 1000 || this.height !== 1000) {
            Swal.fire("La imagen debe tener dimensiones iguales a 1000x1000 píxeles.", "GestorPortalV2", "error");
            $('#imagen').val('');
        } else {
            Swal.fire("Imagen correcta.", "GestorPortalV2", "success");
        }
    };
    img.src = URL.createObjectURL(file);
}
//------------------------------------------------------------------------------
function verImagen(imagen) {
    Swal.fire({
        html: '<img src="./../../../archivos/emisora/programas/' + imagen + '" width="100%" height="100%"/>',
        confirmButtonColor: '#f0ad4e',
        confirmButtonText: 'CERRAR',
        allowOutsideClick: false
    });
}