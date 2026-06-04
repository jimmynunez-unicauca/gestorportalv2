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
function verEditar(idOcdi) {
    $.get('editar', { idOcdi: idOcdi }, setFormulario);
    bloqueoAjax();
}
function verDetalle(idOcdi) {
    $.get('detalle', { idOcdi: idOcdi }, setFormulario);
    bloqueoAjax();
}
//------------------------------------------------------------------------------
function verImagen(imagen) {
    Swal.fire({
        html:
            '<img src="./../../../archivos/ocdi/revistas/imagenes/' + imagen + '" width="100%" height="100%"/>',
        confirmButtonColor: '#f0ad4e',
        confirmButtonText: 'CERRAR',
        allowOutsideClick: false
    });
}
//------------------------------------------------------------------------------
function verSubir(idOcdi) {
    $.get('subirdocumento', { idOcdi: idOcdi }, setFormulario);
    bloqueoAjax();
}
function setFormulario(datos) {
    $("#divContenido").html(datos);
    $('#modalFormulario').modal('show');
}
function validarGuardar(evt, formulario, tipo) {
    evt.preventDefault();
    Swal.fire({
        title: "&#191;DESEA " + tipo + " EL BOLETIN&#63;",
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
function validarImagen(input) {
    const archivo = input.files[0];

    if (!archivo) return;

    const tiposPermitidos = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif'];
    const maxSize = 2 * 1024 * 1024; // 2 MB

    if (!tiposPermitidos.includes(archivo.type)) {
        Swal.fire({
            icon: 'error',
            title: 'Archivo no permitido',
            html: 'El archivo seleccionado no es una imagen válida. <br><b>Formatos permitidos:</b> JPG, PNG, GIF.',
            confirmButtonColor: '#000066'
        });
        input.value = ''; // Limpia el input
        return;
    }

    if (archivo.size > maxSize) {
        Swal.fire({
            icon: 'error',
            title: 'Imagen muy pesada',
            html: 'La imagen no debe superar los <b>2 MB</b> de tamaño.',
            confirmButtonColor: '#000066'
        });
        input.value = ''; // Limpia el input
        return;
    }
    // Imagen válida
    Swal.fire({
        icon: 'success',
        title: 'Imagen válida: ' + archivo.name,
        html: 'La imagen fue cargada correctamente.',
        confirmButtonColor: '#000066',
        timer: 1500,
        showConfirmButton: false
    });
}

//------------------------------------------------------------------------------
function validarPDF(input) {
    const archivo = input.files[0];

    if (!archivo) return; // Si no se seleccionó archivo, no hacer nada

    const extension = archivo.name.split('.').pop().toLowerCase();
    const maxSize = 25 * 1024 * 1024; // 25 MB en bytes

    // Validar extensión
    if (extension !== 'pdf') {
        Swal.fire({
            icon: 'error',
            title: 'Archivo inválido',
            html: 'Solo se permite subir archivos con extensión <b>PDF</b>.',
            confirmButtonColor: '#000066'
        });
        input.value = ''; // Limpiar el campo
        return;
    }

    // Validar tamaño
    if (archivo.size > maxSize) {
        Swal.fire({
            icon: 'error',
            title: 'Archivo demasiado grande',
            html: 'El archivo no debe superar los <b>25 MB</b>.',
            confirmButtonColor: '#000066'
        });
        input.value = ''; // Limpiar el campo
        return;
    }

    // Si pasa ambas validaciones, todo bien
    Swal.fire({
        icon: 'success',
        title: 'Archivo válido',
        html: 'El archivo PDF fue cargado correctamente.',
        confirmButtonColor: '#000066',
        timer: 1500,
        showConfirmButton: false
    });
}

//------------------------------------------------------------------------------