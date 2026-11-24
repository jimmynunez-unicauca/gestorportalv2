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
function verEditar(idOrii) {
    $.get('editar', { idOrii: idOrii }, setFormulario);
    bloqueoAjax();
}
function verDetalle(idOrii) {
    $.get('detalle', { idOrii: idOrii }, setFormulario);
    bloqueoAjax();
}
function verDesactivar(idOrii) {
    $.get('desactivar', { idOrii: idOrii }, setFormulario);
    bloqueoAjax();
}
function verActivar(idOrii) {
    $.get('activar', { idOrii: idOrii }, setFormulario);
    bloqueoAjax();
}
function verEliminar(idOrii) {
    $.get('eliminar', { idOrii: idOrii }, setFormulario);
    bloqueoAjax();
}

function eliminarDoc(id_documentos_orii) {
    Swal.fire({
        title: "&iquest;DESEA ELIMINAR EL DOCUMENTO?",
        text: "El archivo también se borrará del servidor.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Sí",
        cancelButtonText: "No",
        allowOutsideClick: false
    }).then((result) => {
        if (result.isConfirmed) {
            $.get('eliminar-doc', { id_documentos_orii: id_documentos_orii }, function (r) {
                if (r && r.success === false) {
                    Swal.fire('Error', r.message || 'No se pudo eliminar', 'error');
                    return;
                }
                window.location.reload();
            }, 'json');
            bloqueoAjax();
        }
    });
}

function verSubir(idOrii) {
    $.get('subirdocumento', { idOrii: idOrii }, setFormulario);
    bloqueoAjax();
}
function setFormulario(datos) {
    $("#divContenido").html(datos);
    $('#modalFormulario').modal('show');
}
function setDatos(datos) {
    window.location.reload();
}

function validarGuardar(evt, formulario, tipo) {
    evt.preventDefault();
    Swal.fire({
        title: "&#191;DESEA " + tipo + " ORII&#63;",
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
function validarAdjunto() {
    $(document).on('change', 'input[type="file"]', function () {
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
                    break;
                default:
                    Swal.fire({
                        title: "El archivo no tiene la extensión adecuada",
                        text: "Archivos permitidos: pdf",
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