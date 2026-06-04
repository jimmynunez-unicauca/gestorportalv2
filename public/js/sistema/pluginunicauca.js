//------------------------------------------------------------------------------
function bloqueoAjax() {
    $.blockUI({
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
    });
    $('.blockOverlay').attr('style', $('.blockOverlay').attr('style') + 'z-index: 1100 !important');
}

//------------------------------------------------------------------------------
function verRegistrar() {
    $.get('registrar', {}, setFormulario);
    bloqueoAjax();
}

//------------------------------------------------------------------------------
function verEditar(id) {
    $.get('editar', { id: id }, setFormulario);
    bloqueoAjax();
}

//------------------------------------------------------------------------------
function verDetalle(id) {
    $.get('detalle', { id: id }, setFormulario);
    bloqueoAjax();
}

//------------------------------------------------------------------------------
function verEliminar(id) {
    $.get('eliminar', { id: id }, setFormulario);
    bloqueoAjax();
}

//------------------------------------------------------------------------------
function verActivar(id) {
    $.get('activar', { id: id }, setFormulario);
    bloqueoAjax();
}

//------------------------------------------------------------------------------
function verDesactivar(id) {
    $.get('desactivar', { id: id }, setFormulario);
    bloqueoAjax();
}

//------------------------------------------------------------------------------
function setFormulario(datos) {
    $("#divContenido").html(datos);
    $('#modalFormulario').modal('show');
    $.unblockUI();
}

//------------------------------------------------------------------------------
function validarGuardar(evt, formulario, tipo) {
    evt.preventDefault();

    var nombreModulo = $('#nombre_modulo').val();
    var rutaArchivo = $('#ruta_archivo').val();
    var descripcion = $('#descripcion').val();

    if (nombreModulo.trim() === '') {
        Swal.fire({
            title: "ERROR",
            text: "EL NOMBRE DEL MÓDULO NO PUEDE ESTAR VACÍO",
            icon: "error"
        });
        $('#nombre_modulo').focus();
        return false;
    }

    // Validar formato del nombre (solo minúsculas, números y guiones)
    var regexNombre = /^[a-z0-9-]+$/;
    if (tipo !== "ACTIVAR" && tipo !== "DESACTIVAR") {
        if (!regexNombre.test(nombreModulo)) {
            Swal.fire({
                title: "ERROR",
                text: "EL NOMBRE DEL MÓDULO SOLO PUEDE CONTENER MINÚSCULAS, NÚMEROS Y GUIONES",
                icon: "error"
            });
            $('#nombre_modulo').focus();
            return false;
        }
    }

    if (rutaArchivo.trim() === '') {
        Swal.fire({
            title: "ERROR",
            text: "LA RUTA DEL ARCHIVO NO PUEDE ESTAR VACÍA",
            icon: "error"
        });
        $('#ruta_archivo').focus();
        return false;
    }

    if (!rutaArchivo.endsWith('shortcode.php')) {
        Swal.fire({
            title: "ERROR",
            text: "LA RUTA DEBE TERMINAR EN 'shortcode.php'",
            icon: "error"
        });
        $('#ruta_archivo').focus();
        return false;
    }

    Swal.fire({
        title: "¿DESEA " + tipo + "?",
        text: "",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Sí",
        cancelButtonText: "No",
        allowOutsideClick: false
    }).then((result) => {
        if (result.isConfirmed) {
            bloqueoAjax();
            formulario.submit();
        }
    });
}

//------------------------------------------------------------------------------
// Auto-formatear el nombre del módulo al salir del campo
$(document).on('blur', '#nombre_modulo', function () {
    var valor = $(this).val();
    valor = valor.toLowerCase().replace(/\s+/g, '-').replace(/[^a-z0-9-]/g, '');
    $(this).val(valor);
});

//------------------------------------------------------------------------------
// Validar formato de ruta en tiempo real
$(document).on('keyup', '#ruta_archivo', function () {
    var valor = $(this).val();
    if (valor && !valor.endsWith('shortcode.php')) {
        $(this).css('border-color', '#dc3545');
    } else {
        $(this).css('border-color', '#d1d3e2');
    }
});

$(document).on('blur', '#ruta_archivo', function () {
    var valor = $(this).val();
    if (valor && !valor.endsWith('shortcode.php')) {
        $(this).css('border-color', '#dc3545');
        toastr.error('La ruta debe terminar en "shortcode.php"');
    }
});

//------------------------------------------------------------------------------
// Limpiar el modal al cerrarlo
$('#modalFormulario').on('hidden.bs.modal', function () {
    $('#divContenido').html('');
});