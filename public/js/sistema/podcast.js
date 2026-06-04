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
    $.get('registrar-podcast', {}, setFormulario);
    bloqueoAjax();
}
function verEditar(idPodcast) {
    $.get('editar-podcast', { idPodcast: idPodcast }, setFormulario);
    bloqueoAjax();
}
function verDetalle(idPodcast) {
    $.get('detalle-podcast', { idPodcast: idPodcast }, setFormulario);
    bloqueoAjax();
}
function verEliminar(idPodcast) {
    $.get('eliminar-podcast', { idPodcast: idPodcast }, setFormulario);
    bloqueoAjax();
}
function verActivar(idPodcast) {
    $.get('activar-podcast', { idPodcast: idPodcast }, setFormulario);
    bloqueoAjax();
}
function setFormulario(datos) {
    $("#divContenido").html(datos);
    $('#modalFormulario').modal('show');
}

function validarGuardar(evt, formulario, tipo) {
    evt.preventDefault();
    Swal.fire({
        title: "&#191;DESEA " + tipo + " EL PODCAST&#63;",
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
    var idPodcast = $("#idPodcast").val();
    $.get('actualizarimagen-podcast', { idPodcast: idPodcast }, setFormulario);
    bloqueoAjax();
}
function actualizarAudio() {
    var idPodcast = $("#idPodcast").val();
    $.get('actualizaraudio-podcast', { idPodcast: idPodcast }, setFormulario);
    bloqueoAjax();
}
function setFormularioAux(datos) {
    $("#divContenidoAux").html(datos);
    $('#modalFormularioAux').modal('show');
}
//------------------------------------------------------------------------------
function validarImagen() {
    const input = $('#imagen')[0];
    const file = input.files[0];

    if (!file) return;

    const ALLOWED_EXT = ['jpg', 'jpeg', 'png', 'webp'];
    const MAX_SIZE = 300 * 1024; // 300 KB recomendado
    const IMG_MIN = 600; // lado mínimo de la imagen cuadrada (600x600)

    const ext = file.name.split('.').pop().toLowerCase();

    // Validar tipo archivo
    if (!file.type.startsWith('image/')) {
        mostrarError("El archivo no es una imagen.");
        limpiarInput();
        return;
    }

    // Validar extensión
    if (!ALLOWED_EXT.includes(ext)) {
        mostrarError("La imagen debe ser JPG, PNG o WebP.");
        limpiarInput();
        return;
    }

    // Validar peso
    if (file.size > MAX_SIZE) {
        mostrarError("La imagen no debe superar los 300KB.");
        limpiarInput();
        return;
    }

    // Validar dimensiones
    const img = new Image();
    img.onload = function () {
        if (this.width !== this.height) {
            mostrarError("La imagen debe ser estrictamente cuadrada.");
            limpiarInput();
            return;
        }

        if (this.width < IMG_MIN || this.height < IMG_MIN) {
            mostrarError(`La imagen cuadrada debe ser mínimo de ${IMG_MIN}x${IMG_MIN} píxeles.`);
            limpiarInput();
            return;
        }

        // Imagen válida
        Swal.fire({
            title: "Imagen válida.",
            text: "GestorPortalV2",
            icon: "success",
            confirmButtonColor: '#f0ad4e',
            confirmButtonText: 'CERRAR',
            allowOutsideClick: false
        });
    };

    img.src = URL.createObjectURL(file);

    // Funciones auxiliares
    function mostrarError(msg) {
        Swal.fire({
            title: msg,
            text: "GestorPortalV2",
            icon: "error",
            confirmButtonColor: '#f0ad4e',
            confirmButtonText: "CERRAR",
            allowOutsideClick: false
        });
    }

    function limpiarInput() {
        $('#imagen').val('');
    }
}
function validarAudio() {
    var input = $('#audio_url')[0];
    var file = input.files[0];

    if (file) {
        var ext = file.name.split('.').pop().toLowerCase();
        var fileSize = file.size; // tamaño en bytes (10MB son 10 * 1024 * 1024 bytes)
        var audio = new Audio();

        if (file.type.startsWith('audio/')) {
            if (ext !== 'mp3' && ext !== 'wav' && ext !== 'aac') {
                Swal.fire({
                    title: "El audio debe ser de formato MP3, WAV o AAC.",
                    text: "GestorPortalV2",
                    icon: "error",
                    confirmButtonColor: '#f0ad4e',
                    confirmButtonText: 'CERRAR',
                    allowOutsideClick: false
                });
                $('#audio_url').val('');
            } else if (fileSize > 25 * 1024 * 1024) { // 10MB límite
                Swal.fire({
                    title: "El audio no debe superar 25MB.",
                    text: "GestorPortalV2",
                    icon: "error",
                    confirmButtonColor: '#f0ad4e',
                    confirmButtonText: 'CERRAR',
                    allowOutsideClick: false
                });
                $('#audio_url').val('');
            } else {
                audio.onloadedmetadata = function () {
                    Swal.fire({
                        title: "Audio correcto.",
                        text: "GestorPortalV2",
                        icon: "success",
                        confirmButtonColor: '#f0ad4e',
                        confirmButtonText: 'CERRAR',
                        allowOutsideClick: false
                    });
                };
                audio.src = URL.createObjectURL(file);
            }
        } else {
            Swal.fire({
                title: "El archivo no es un audio.",
                text: "GestorPortalV2",
                icon: "error",
                confirmButtonColor: '#f0ad4e',
                confirmButtonText: 'CERRAR',
                allowOutsideClick: false
            });
            $('#audio_url').val('');
        }
    }
}


function verImagen(imagen) {
    Swal.fire({
        html:
            '<img src="./../../../archivos/emisora/podcast/' + imagen + '" width="100%" height="100%"/>',
        confirmButtonColor: '#f0ad4e',
        confirmButtonText: 'CERRAR',
        allowOutsideClick: false
    });
}