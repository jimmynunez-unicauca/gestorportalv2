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
function verEditar(idPodcastComarca) {
    $.get('editar', { idPodcastComarca: idPodcastComarca }, setFormulario);
    bloqueoAjax();
}
function verDetalle(idPodcastComarca) {
    $.get('detalle', { idPodcastComarca: idPodcastComarca }, setFormulario);
    bloqueoAjax();
}
function verEliminar(idPodcastComarca) {
    $.get('eliminar', { idPodcastComarca: idPodcastComarca }, setFormulario);
    bloqueoAjax();
}
function verActivar(idPodcastComarca) {
    $.get('activar', { idPodcastComarca: idPodcastComarca }, setFormulario);
    bloqueoAjax();
}
function setFormulario(datos) {
    $("#divContenido").html(datos);
    $('#modalFormulario').modal('show');
}

function validarGuardar(evt, formulario, tipo) {
    evt.preventDefault();

    const tipoFormato = $("#tipo").val();

    let url = "";
    let id = "";
    let etiqueta = "";

    if (tipoFormato === 'Video') {
        url = $("#video_url").val().trim();
        id = $("#video_id").val().trim();
        etiqueta = "Video";

    } else if (['Podcast', 'Audiolibro', 'Clase', 'Conferencia'].includes(tipoFormato)) {
        url = $("#audio_url").val().trim();
        id = $("#audio_id").val().trim();
        etiqueta = "Audio";

    } else {
        Swal.fire("Error", "Por favor seleccione un tipo de formato", "error");
        return;
    }

    // Validación común
    if (url === '' && id === '') {
        Swal.fire("Error", `Debe completar un campo de ${etiqueta} (URL o ID)`, "error");
        return;
    }

    if (url !== '' && id !== '') {
        Swal.fire("Error", `Solo puede llenar un campo de ${etiqueta}`, "error");
        return;
    }

    // Confirmación
    Swal.fire({
        title: `¿DESEA ${tipo} EL PODCAST?`,
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Sí",
        cancelButtonText: "No",
        allowOutsideClick: false
    }).then((result) => {
        if (result.isConfirmed) {
            formulario.removeAttribute('onsubmit');
            bloqueoAjax();
            formulario.submit();
        }
    });
}



//------------------------------------------------------------------------------
function actualizarImagen() {
    var idPodcastComarca = $("#idPodcastComarca").val();
    $.get('actualizarimagen', { idPodcastComarca: idPodcastComarca }, setFormulario);
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

//------------------------------------------------------------------------------
function verImagen(imagen) {
    Swal.fire({
        html:
            '<img src="./../../../archivos/podcast_comarca/imagenes/' + imagen + '" width="100%" height="100%"/>',
        confirmButtonColor: '#f0ad4e',
        confirmButtonText: 'CERRAR',
        allowOutsideClick: false
    });
}
//------------------------------------------------------------------------------
function tipoArchivo(tipo) {
    $(".divVideo").hide("slow");
    $(".divAudio").hide("slow");
    if (tipo == 'Video') {
        $("#audio_url").val("");
        $("#audio_id").val("");
        $(".divVideo").show('slow');
        $(".divAudio").hide("slow");
    } else {
        $("#video_url").val("");
        $("#video_id").val("");
        $(".divVideo").hide("slow");
        $(".divAudio").show('slow');
    }
}
//------------------------------------------------------------------------------