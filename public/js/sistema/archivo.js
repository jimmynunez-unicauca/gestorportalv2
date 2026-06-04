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
function verEditar(idArchivo) {
    $.get('editar', { idArchivo: idArchivo }, setFormulario);
    bloqueoAjax();
}
function verEditarEn(idArchivo) {
    $.get('editaren', { idArchivo: idArchivo }, setFormulario);
    bloqueoAjax();
}
function verDetalle(idArchivo) {
    $.get('detalle', { idArchivo: idArchivo }, setFormulario);
    bloqueoAjax();
}
function verDetalleEn(idArchivo) {
    $.get('detalleen', { idArchivo: idArchivo }, setFormulario);
    bloqueoAjax();
}
function verEliminar(idArchivo) {
    $.get('eliminar', { idArchivo: idArchivo }, setFormulario);
    bloqueoAjax();
}
function verEliminarEn(idArchivo) {
    $.get('eliminaren', { idArchivo: idArchivo }, setFormulario);
    bloqueoAjax();
}
function verActivar(idArchivo) {
    $.get('activar', { idArchivo: idArchivo }, setFormulario);
    bloqueoAjax();
}
function verActivarEn(idArchivo) {
    $.get('activaren', { idArchivo: idArchivo }, setFormulario);
    bloqueoAjax();
}
function setFormulario(datos) {
    $("#divContenido").html(datos);
    /* CKEDITOR.replace("descripcion"); */
    $('#modalFormulario').modal('show');
}

function validarGuardar(evt, formulario, tipo) {
    evt.preventDefault();
    Swal.fire({
        title: "&#191;DESEA " + tipo + " EL ARCHIVO&#63;",
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
function selectTipo(tipo) {
    if (tipo == 'Convocatorias') {
        $("#idResolucion").val("");
        $('#inicioConvocatoria').attr('type', 'date');
        $('#finConvocatoria').attr('type', 'date');
        $("#divInicioConvocatoria").show('slow');
        $("#divFinConvocatoria").show('slow');
        $('#inicioConvocatoria').attr('required', true);
        $('#finConvocatoria').attr('required', true);
        $("#divResolucion").hide("slow");
        $('#idResolucion').attr('required', false);
    } else if (tipo == 'Resoluciones') {
        $('#inicioConvocatoria').attr('type', 'text');
        $('#finConvocatoria').attr('type', 'text');
        $("#inicioConvocatoria").val('0000-00-00');
        $("#finConvocatoria").val('0000-00-00');
        $("#divResolucion").show('slow');
        $('#idResolucion').attr('required', true);
        $("#divInicioConvocatoria").hide("slow");
        $("#divFinConvocatoria").hide("slow");
        $('#inicioConvocatoria').attr('required', false);
        $('#finConvocatoria').attr('required', false);
    } else {
        $('#inicioConvocatoria').attr('type', 'text');
        $('#finConvocatoria').attr('type', 'text');
        $("#inicioConvocatoria").val('0000-00-00');
        $("#finConvocatoria").val('0000-00-00');
        $("#idResolucion").val("");
        $("#divInicioConvocatoria").hide("slow");
        $("#divFinConvocatoria").hide("slow");
        $("#divResolucion").hide("slow");
        $('#inicioConvocatoria').attr('required', false);
        $('#finConvocatoria').attr('required', false);
        $('#idResolucion').attr('required', false);
    }
}
//------------------------------------------------------------------------------
function actualizarArchivo() {
    var idArchivo = $("#idArchivo").val();
    $.get('actualizararchivo', { idArchivo: idArchivo }, setFormularioAux);
    bloqueoAjax();
}
function actualizarArchivoEn() {
    var idArchivo = $("#idArchivo").val();
    $.get('actualizararchivoen', { idArchivo: idArchivo }, setFormularioAux);
    bloqueoAjax();
}
function setFormularioAux(datos) {
    $("#divContenidoAux").html(datos);
    $('#modalFormularioAux').modal('show');
}
//------------------------------------------------------------------------------
function validarAdjunto() {
    $(document).on('change', 'input[type="file"]', function () {
        // this.files[0].size recupera el tamaño del archivo
        // alert(this.files[0].size);

        var fileName = this.files[0].name;
        var fileSize = this.files[0].size;

        if (fileSize > 25000000) {
            Swal.fire({
                title: "El archivo no debe superar las 25MB",
                text: "GestorPortal",
                icon: "error",
                confirmButtonColor: '#f0ad4e',
                confirmButtonText: 'CERRAR',
                allowOutsideClick: false
            });
            this.value = '';
            this.files[0].name = '';
        } else {
            // recuperamos la extensión del archivo
            var ext = fileName.split('.').pop();

            // Convertimos en minúscula porque 
            // la extensión del archivo puede estar en mayúscula
            ext = ext.toLowerCase();

            // console.log(ext);
            switch (ext) {
                case 'pdf':
                case 'docx':
                case 'xlsx':
                case 'pptx':
                    break;
                default:
                    Swal.fire({
                        title: "El archivo no tiene la extensión adecuada",
                        text: "Archivos permitidos: pdf, docx, xlsx y pptx",
                        icon: "error",
                        confirmButtonColor: '#f0ad4e',
                        confirmButtonText: 'CERRAR',
                        allowOutsideClick: false
                    });
                    this.value = ''; // reset del valor
                    this.files[0].name = '';
            }
        }
    });
}
//------------------------------------------------------------------------------
function eliminarDep(idDependencia) {
    var idArchivo = $("#idArchivo").val();
    Swal.fire({
        title: "&#191;DESEA ELIMINAR LA DEPENDENCIA&#63;",
        text: "",
        icon: "info",
        showCancelButton: true,
        confirmButtonText: "Si",
        cancelButtonText: "No",
        allowOutsideClick: false
    }).then((result) => {
        if (result.isConfirmed) {
            $.get('eliminar-dep', { idArchivo: idArchivo, idDependencia: idDependencia }, setDatos, 'json');
            bloqueoAjax();
        }
    });

}
function setDatos(datos) {
    window.location.reload();
}
//------------------------------------------------------------------------------
function agregarDep() {
    var idArchivo = $("#idArchivo").val();
    $.get('agregardepe', { idArchivo: idArchivo }, setFormularioAux);
    bloqueoAjax();
}
function addDep(idDependencia) {
    var idArchivo = $("#idArchivo").val();
    Swal.fire({
        title: "&#191;DESEA AGREGAR LA DEPENDENCIA&#63;",
        text: "",
        icon: "info",
        showCancelButton: true,
        confirmButtonText: "Si",
        cancelButtonText: "No",
        allowOutsideClick: false
    }).then((result) => {
        if (result.isConfirmed) {
            $.get('add-dep', { idArchivo: idArchivo, idDependencia: idDependencia }, setDatos, 'json');
            bloqueoAjax();
        }
    });
}
//------------------------------------------------------------------------------