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
function verEditar(idCPResultados) {
    $.get('editar', { idCPResultados: idCPResultados }, setFormulario);
    bloqueoAjax();
}
function verDetalle(idCPResultados) {
    $.get('detalle', { idCPResultados: idCPResultados }, setFormulario);
    bloqueoAjax();
}
function verEliminar(idCPResultados) {
    $.get('eliminar', { idCPResultados: idCPResultados }, setFormulario);
    bloqueoAjax();
}
function verActivar(idCPResultados) {
    $.get('activar', { idCPResultados: idCPResultados }, setFormulario);
    bloqueoAjax();
}
function setFormulario(datos) {
    $("#divContenido").html(datos);
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
function getTipoProceso(idProceso) {
    if (idProceso !== '') {
        $.get('getSelectTipoProcesos', { idProceso: idProceso }, setTipoProceso);
        bloqueoAjax();
    } else {
        $("#idTipoProceso").html("<option value=''>Seleccione...</option>");
        $("#idSubproceso").html("<option value=''>Seleccione...</option>");
    }
}
function setTipoProceso(html) {
    $("#idTipoProceso").html(html);
}
function getSubProceso(idTipoProceso) {
    if (idTipoProceso !== '') {
        $.get('getSelectSubprocesos', { idTipoProceso: idTipoProceso }, setSubproceso);
        bloqueoAjax();
    } else {
        $("#idSubproceso").html("<option value=''>Seleccione...</option>");
    }
}
function setSubproceso(html) {
    $("#idSubproceso").html(html);
}

//------------------------------------------------------------------------------
function actualizarArchivo() {
    var idCPResultados = $("#idCPResultados").val();
    $.get('actualizararchivo', { idCPResultados: idCPResultados }, setFormulario);
    bloqueoAjax();
}
function setFormularioAux(datos) {
    $("#divContenidoAux").html(datos);
    $('#modalFormularioAux').modal('show');
}
//------------------------------------------------------------------------------
function validarAdjunto() {
    if ($("#archivo").val() !== '') {
        var filename = $('#archivo').val().replace(/.*(\/|\\)/, '');
        $.get('existearchivo', { archivo: filename }, setExisteArchivo, 'json');
        bloqueoAjax();
    }
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
                case 'zip':
                    break;
                default:
                    Swal.fire({
                        title: "El archivo no tiene la extensión adecuada",
                        text: "Archivos permitidos: pdf, docx, xlsx, pptx y zip",
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
function setExisteArchivo(datos) {
    if (parseInt(datos['error']) === 0) {
        if (parseInt(datos['existe']) === 1) {
            Swal.fire("El nombre del archivo ( " + datos['archivo'] + " ) ya se encuentra registrado en <i><i class='fa fa-paw'></i>gestorportalv2</i>.", "<i><i class='fa fa-paw'></i>gestorportalv2</i>", "error");
            $("#archivo").val('');
            $("#archivo").focus();
            return false;
        } else {
            return true;
        }
    } else {
        alert("SE HA PRESENTADO UN INCONVENIENTE EN <i><i class='fa fa-paw'></i>gestorportalv2</i>.");
        return false;
    }
}
//------------------------------------------------------------------------------