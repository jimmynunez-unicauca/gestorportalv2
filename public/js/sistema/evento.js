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

function verRegistrar(event) {
    $.get('registrar', { fecha: new Date(event).toISOString() }, setFormulario);
    bloqueoAjax();
}
function verEditar(idEvento) {
    $.get('editar', { idEvento: idEvento }, setFormulario);
    bloqueoAjax();
}
function verDetalle(idEvento) {
    $.get('detalle', { idEvento: idEvento }, setFormulario);
    bloqueoAjax();
}
function verEliminar(idEvento) {
    $.get('eliminar', { idEvento: idEvento }, setFormulario);
    bloqueoAjax();
}
function moverEvento(event) {
    Swal.fire({
        title: '&#191;Est&aacute;s seguro de este cambio&#63;',
        text: event.title + " se movera a: " + event.start.format() + " - " + event.end.format(),
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'SI',
        cancelButtonText: 'NO',
        allowOutsideClick: false
    }).then((result) => {
        if (result.isConfirmed) {
            $.get('moverevento', { idEvento: event.idEvento, start: event.start.format(), end: event.end.format() }, setEventoAction, 'json');
            bloqueoAjax();
        } else {
            window.location.reload();
            revertFunc();
        }
    });
}
function redimensionar(event) {
    Swal.fire({
        title: '&#191;Est&aacute;s seguro de este cambio&#63;',
        text: event.title + " se movera a: " + event.start.format() + " - " + event.end.format(),
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'SI',
        cancelButtonText: 'NO',
        allowOutsideClick: false
    }).then((result) => {
        if (result.isConfirmed) {
            $.get('moverevento', { idEvento: event.idEvento, start: event.start.format(), end: event.end.format() }, setEventoAction, 'json');
            bloqueoAjax();
        } else {
            window.location.reload();
            revertFunc();
        }
    })
}
function eliminarEvento() {
    var idEvento = $("#idEventoAux").val();
    Swal.fire({
        title: '&#191;Est&aacute;s seguro de eliminar el evento&#63;',
        text: 'No podra revertir esto',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'SI',
        cancelButtonText: 'NO',
        allowOutsideClick: false
    }).then((result) => {
        if (result.isConfirmed) {
            $.get('eliminar', { idEvento: idEvento }, setEventoAction, 'json');
            bloqueoAjax();
        }
    })
}
function setEventoAction(datos) {
    if (parseInt(datos['successOK']) === 1) {
        window.location.reload();
    } else {
        alert("SE HA PRESENTADO UN INCONVENIENTE EN <i><i class='fa fa-paw'></i>JIMSOFT</i>.");
        return false;
    }
}
function setFormulario(datos) {
    $("#divContenido").html(datos);
    $('#modalFormulario').modal('show');
}

function validarGuardar(evt, formulario, tipo) {
    if ($("#start").val() == $("#end").val()) {
        Swal.fire({
            title: "ERROR",
            text: "La hora no pueden ser iguales",
            icon: "error"
        });
        return false;
    } else {
        var detalleContenido = editor.getData().trim();
        if (detalleContenido === '') {
            Swal.fire({
                title: "ERROR",
                text: "EL CAMPO DETALLE NO PUEDE ESTAR VACIO",
                icon: "error"
            });
            return false;
        } else {
            evt.preventDefault();
            Swal.fire({
                title: "&#191;DESEA " + tipo + " EL EVENTO&#63;",
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
    }
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
function selectColor(tipo) {
    if (tipo == 'Periodo academico') {
        $("#textColor").val('#FFFFFF');
        $("#color").val('#ff6c08');
    } else if (tipo == 'Inicio y finalizacion de clases') {
        $("#textColor").val('#FFFFFF');
        $("#color").val('#ffb000');
    } else if (tipo == 'Planeacion') {
        $("#textColor").val('#FFFFFF');
        $("#color").val('#5bae40');
    } else if (tipo == 'Tramites academicos') {
        $("#textColor").val('#FFFFFF');
        $("#color").val('#00aae5');
    } else if (tipo == 'Intersemestrales') {
        $("#textColor").val('#FFFFFF');
        $("#color").val('#5a00ba');
    } else if (tipo == 'Fin periodo academico') {
        $("#textColor").val('#FFFFFF');
        $("#color").val('#db141c');
    } else {
        $("#textColor").val('#FFFFFF');
        $("#color").val('#000066');
    }
}
//------------------------------------------------------------------------------
function actualizarImagen() {
    var idEvento = $("#idEvento").val();
    $.get('actualizarimagen', { idEvento: idEvento }, setFormulario);
    bloqueoAjax();
}
function setFormularioAux(datos) {
    $("#divContenidoAux").html(datos);
    $('#modalFormularioAux').modal('show');
}
//------------------------------------------------------------------------------
function validarFecha() {
    if ($("#start").val() != '' && $("#end").val() != '') {
        if ($("#start").val() > $("#end").val()) {
            Swal.fire({
                title: "ERROR",
                text: "La fecha final no puede ser menor a la inicial",
                icon: "error"
            });
            $("#start").val('')
            $("#end").val('')
        }
    }
}
function validarImagenOLD() {
    $(document).on('change', 'input[type="file"]', function () {
        // this.files[0].size recupera el tamaño del archivo
        // alert(this.files[0].size);

        var fileName = this.files[0].name;
        var fileSize = this.files[0].size;

        if (fileSize > 2000000) {
            Swal.fire({
                title: "El archivo no debe superar las 2MB",
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
                case 'jpg':
                case 'jpeg':
                case 'png':
                case 'gif':
                case 'bmp':
                case 'svg':
                    break;
                default:
                    Swal.fire({
                        title: "El archivo no tiene la extensión adecuada",
                        text: "Archivos permitidos: jpg,jpeg,png,gif,bmp y svg",
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

function validarImagen() {
    var input = $('#imagen')[0];
    var file = input.files[0];

    if (file) {
        var ext = file.name.split('.').pop().toLowerCase();
        var fileSize = file.size; // tamaño en bytes
        var img = new Image();
        if (file.type.startsWith('image/')) {
            img.onload = function () {
                if (ext !== 'jpg' && ext !== 'jpeg' && ext !== 'png') {
                    Swal.fire({
                        title: "La imagen debe ser de formato JPG o PNG.",
                        text: "GestorPortalV2",
                        icon: "error",
                        confirmButtonColor: '#f0ad4e',
                        confirmButtonText: 'CERRAR',
                        allowOutsideClick: false
                    });
                    $('#imagen').val('');
                } else if (fileSize > 2000000) { // 2MB    
                    Swal.fire({
                        title: "La imagen no debe superar 2MB.",
                        text: "GestorPortalV2",
                        icon: "error",
                        confirmButtonColor: '#f0ad4e',
                        confirmButtonText: 'CERRAR',
                        allowOutsideClick: false
                    });
                    $('#imagen').val('');
                } else if (this.width !== 700 || this.height !== 700) {
                    Swal.fire({
                        title: "La imagen debe tener dimensiones iguales a 700x700 píxeles.",
                        text: "GestorPortalV2",
                        icon: "error",
                        confirmButtonColor: '#f0ad4e',
                        confirmButtonText: 'CERRAR',
                        allowOutsideClick: false
                    });
                    $('#imagen').val('');
                } else {
                    Swal.fire({
                        title: "Imagen correcta.",
                        text: "GestorPortalV2",
                        icon: "success",
                        confirmButtonColor: '#f0ad4e',
                        confirmButtonText: 'CERRAR',
                        allowOutsideClick: false
                    });
                }
            };
        } else {
            Swal.fire({
                title: "El archivo no es una imagen.",
                text: "GestorPortalV2",
                icon: "error",
                confirmButtonColor: '#f0ad4e',
                confirmButtonText: 'CERRAR',
                allowOutsideClick: false
            });
            $('#imagen').val('');
        }
        img.src = URL.createObjectURL(file);
    }
}

function verImagen(imagen) {
    Swal.fire({
        html:
            '<img src="./../../../archivos/eventos/' + imagen + '" width="100%" height="100%"/>',
        confirmButtonColor: '#f0ad4e',
        confirmButtonText: 'CERRAR',
        allowOutsideClick: false
    });
}