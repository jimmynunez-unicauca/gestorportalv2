/* ============================================================================
 * Módulo Podcast — GestorPortalV2
 * La subida del MP3 se hace DIRECTO al servidor de la emisora vía XMLHttpRequest.
 * El formulario a Laminas solo lleva texto + audio_url (5 KB aprox).
 * ==========================================================================*/

//------------------------------------------------------------------------------
function bloqueoAjaxPodcast() {
    if (typeof $.blockUI === 'function') {
        $.blockUI({
            message: $('#msgBloqueoPodcast'),
            css: {
                border: 'none', padding: '15px', backgroundColor: '#000',
                '-webkit-border-radius': '10px', '-moz-border-radius': '10px',
                opacity: .85, color: '#fff', 'z-index': 10000000
            }
        });
        $('.blockOverlay').attr('style', $('.blockOverlay').attr('style') + 'z-index: 1100 !important');
    }
}

//------------------------------------------------------------------------------
// Apertura de modales
//------------------------------------------------------------------------------
function verRegistrarPodcast() {
    $.get('registrar', {}, setFormularioPodcast);
    bloqueoAjaxPodcast();
}
function verEditarPodcast(idPodcast) {
    $.get('editar', { idPodcast: idPodcast }, setFormularioPodcast);
    bloqueoAjaxPodcast();
}
function verDetallePodcast(idPodcast) {
    $.get('detalle', { idPodcast: idPodcast }, setFormularioPodcast);
    bloqueoAjaxPodcast();
}
function verDesactivarPodcast(idPodcast) {
    $.get('desactivar', { idPodcast: idPodcast }, setFormularioPodcast);
    bloqueoAjaxPodcast();
}
function verActivarPodcast(idPodcast) {
    $.get('activar', { idPodcast: idPodcast }, setFormularioPodcast);
    bloqueoAjaxPodcast();
}
function verEliminarPodcast(idPodcast) {
    $.get('eliminar', { idPodcast: idPodcast }, setFormularioPodcast);
    bloqueoAjaxPodcast();
}

function setFormularioPodcast(datos) {
    $('#divContenidoPodcast').html(datos);
    $('#modalFormulario').modal('show');
}

//------------------------------------------------------------------------------
// Validación del formulario (registrar / editar)
//------------------------------------------------------------------------------
function validarGuardarPodcast(evt, formulario, tipo) {
    evt.preventDefault();

    var inputAudio = document.getElementById('audio');
    var inputImagen = document.getElementById('imagen_file');

    var tieneAudioNuevo = inputAudio && inputAudio.files && inputAudio.files[0];
    var tieneImagenNueva = inputImagen && inputImagen.files && inputImagen.files[0];

    if (tipo === 'REGISTRAR' || tipo === 'EDITAR') {
        var titulo = document.getElementById('titulo');
        if (!titulo || titulo.value.trim() === '') {
            Swal.fire('Falta el título', 'Debe ingresar un título.', 'warning');
            return false;
        }
    }

    if (tieneAudioNuevo) {
        var ext = inputAudio.files[0].name.split('.').pop().toLowerCase();
        if (ext !== 'mp3') {
            Swal.fire('Archivo inválido', 'Solo se permiten archivos MP3.', 'error');
            return false;
        }
    }

    if (tieneImagenNueva) {
        var extI = inputImagen.files[0].name.split('.').pop().toLowerCase();
        if (['jpg', 'jpeg', 'png', 'gif', 'webp'].indexOf(extI) === -1) {
            Swal.fire('Imagen inválida', 'Solo JPG, PNG, GIF o WEBP.', 'error');
            return false;
        }
        if (inputImagen.files[0].size > 2 * 1024 * 1024) {
            Swal.fire('Imagen muy grande', 'Máximo 2 MB.', 'error');
            return false;
        }
    }

    Swal.fire({
        title: '¿DESEA ' + tipo + ' EL PODCAST?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Si',
        cancelButtonText: 'No',
        allowOutsideClick: false
    }).then(function (result) {
        if (!result.isConfirmed) return;

        // Si hay audio nuevo O imagen nueva, subimos al Servidor B
        if (tieneAudioNuevo || tieneImagenNueva) {
            var audioFile = tieneAudioNuevo ? inputAudio.files[0] : null;
            var imagenFile = tieneImagenNueva ? inputImagen.files[0] : null;

            subirArchivosAlServidor(audioFile, imagenFile)
                .then(function (data) {
                    // Solo actualizamos el DOM si el servidor devolvió un valor nuevo
                    if (data.audio) document.getElementById('audio_url').value = data.audio;
                    if (data.imagen) document.getElementById('imagen').value = data.imagen;

                    formulario.removeAttribute('onsubmit');
                    if (inputAudio) inputAudio.disabled = true;
                    if (inputImagen) inputImagen.disabled = true;

                    formulario.submit();
                    bloqueoAjaxPodcast();
                })
                .catch(function (err) {
                    Swal.fire('Error al subir los archivos', String(err), 'error');
                });
        } else {
            // No hay archivos nuevos, solo se actualizan textos
            formulario.removeAttribute('onsubmit');
            formulario.submit();
            bloqueoAjaxPodcast();
        }
    });

    return false;
}
//------------------------------------------------------------------------------
// Subida del MP3 al servidor de la emisora
//------------------------------------------------------------------------------
function subirArchivosAlServidor(audioFile, imagenFile) {
    return new Promise(function (resolve, reject) {
        var cfg = window.PODCAST_UPLOAD;
        if (!cfg || !cfg.endpoint) return reject('No hay endpoint configurado.');

        // -------- Modo MOCK --------
        if (cfg.mock === true || String(cfg.endpoint).indexOf('MOCK:') === 0) {
            Swal.fire({
                title: 'Subiendo (modo desarrollo)…',
                html: '<div class="progress" style="height:22px"><div class="progress-bar progress-bar-striped active" style="width:100%">100%</div></div>',
                allowOutsideClick: false,
                showConfirmButton: false,
                timer: 1000
            });
            setTimeout(function () {
                Swal.close();
                var res = {};
                if (audioFile) res.audio = 'https://www.unicauca.edu.co/sitioemisora/audios/podcast/MOCK_' + Date.now() + '.mp3';
                if (imagenFile) res.imagen = 'MOCK_' + Date.now() + '.png';
                resolve(res);
            }, 1200);
            return;
        }

        // -------- Modo real --------
        var fd = new FormData();
        if (audioFile) {
            var nombreFinal = 'podcast_' + Date.now() + '_' + Math.random().toString(36).substring(2, 8) + '.mp3';
            fd.append('audio', audioFile);
            fd.append('nombreFinal', nombreFinal);
        }
        if (imagenFile) {
            fd.append('imagen', imagenFile);
        }

        var xhr = new XMLHttpRequest();
        xhr.open('POST', cfg.endpoint, true);
        if (cfg.token) xhr.setRequestHeader('X-Upload-Token', cfg.token);
        xhr.timeout = 0;

        Swal.fire({
            title: 'Subiendo archivos…',
            html: '<div class="progress" style="height:22px"><div id="uploadProgressPodcast" class="progress-bar progress-bar-striped active" style="width:0%">0%</div></div><small class="text-muted d-block mt-2">No cierres esta ventana</small>',
            allowOutsideClick: false,
            showConfirmButton: false
        });

        xhr.upload.onprogress = function (e) {
            if (e.lengthComputable) {
                var pct = Math.round((e.loaded / e.total) * 100);
                var bar = document.getElementById('uploadProgressPodcast');
                if (bar) { bar.style.width = pct + '%'; bar.textContent = pct + '%'; }
            }
        };

        xhr.onload = function () {
            Swal.close();
            var r = null;
            try { r = JSON.parse(xhr.responseText); } catch (e) { }
            if (xhr.status === 200 && r && r.ok) {
                resolve({ audio: r.audio_url, imagen: r.imagen });
            } else {
                reject((r && r.error) ? r.error : ('HTTP ' + xhr.status));
            }
        };

        xhr.onerror = function () { Swal.close(); reject('Error de red'); };
        xhr.ontimeout = function () { Swal.close(); reject('Timeout'); };

        xhr.send(fd);
    });
}
//------------------------------------------------------------------------------
// Validaciones locales de archivos (antes de enviar)
//------------------------------------------------------------------------------
function validarAudioPodcast() {
    var input = document.getElementById('audio');
    if (!input || !input.files || !input.files[0]) return;
    var f = input.files[0];
    var ext = f.name.split('.').pop().toLowerCase();
    if (ext !== 'mp3') {
        Swal.fire('Archivo inválido', 'Solo se permiten archivos MP3.', 'error');
        input.value = '';
        return;
    }
    var mb = (f.size / 1024 / 1024).toFixed(2);
    Swal.fire({
        toast: true, position: 'top-end', icon: 'success',
        title: 'MP3 listo: ' + mb + ' MB',
        showConfirmButton: false, timer: 2500
    });
}

function validarImagenPodcast() {
    var input = document.getElementById('imagen_file'); // <--- Corregido aquí
    if (!input || !input.files || !input.files[0]) return;
    var f = input.files[0];
    var ext = f.name.split('.').pop().toLowerCase();
    if (['jpg', 'jpeg', 'png', 'gif', 'webp'].indexOf(ext) === -1) {
        Swal.fire('Imagen inválida', 'Solo JPG, PNG, GIF o WEBP.', 'error');
        input.value = '';
        return;
    }
    if (f.size > 2 * 1024 * 1024) {
        Swal.fire('Imagen muy grande', 'Máximo 2 MB.', 'error');
        input.value = '';
    }
}