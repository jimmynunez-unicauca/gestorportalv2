function bloqueoAjax() {
    $.blockUI({
        message: $('#msgBloqueo'),
        css: {
            border: 'none',
            padding: '15px',
            backgroundColor: '#000',
            opacity: .85,
            color: '#fff',
            'z-index': 10000000
        }
    });
}

function verRegistrar() {
    $.get('registrar', {}, function (data) {
        $("#divContenido").html(data);
        $('#modalFormulario').modal('show');
        $('.select2').select2({ width: '100%' });
    });
    bloqueoAjax();
}

function verEditar(id) {
    $.get('editar', { id: id }, function (data) {
        $("#divContenido").html(data);
        $('#modalFormulario').modal('show');
        $('.select2').select2({ width: '100%' });
    });
    bloqueoAjax();
}

function verDetalle(id) {
    $.get('detalle', { id: id }, function (data) {
        $("#divContenido").html(data);
        $('#modalFormulario').modal('show');
    });
    bloqueoAjax();
}

function verEliminar(id) {
    $.get('eliminar', { id: id }, function (data) {
        $("#divContenido").html(data);
        $('#modalFormulario').modal('show');
    });
    bloqueoAjax();
}

function verActivar(id) {
    Swal.fire({
        title: "¿ACTIVAR CONVOCATORIA?",
        text: "Esta convocatoria estará disponible para inscripciones",
        icon: "question",
        showCancelButton: true,
        confirmButtonText: "Si, activar",
        cancelButtonText: "Cancelar"
    }).then((result) => {
        if (result.isConfirmed) {
            $.post('activar', { id_convocatoria: id }, function () {
                location.reload();
            });
            bloqueoAjax();
        }
    });
}

function verDesactivar(id) {
    Swal.fire({
        title: "¿DESACTIVAR CONVOCATORIA?",
        text: "Esta convocatoria ya no recibirá más inscripciones",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Si, desactivar",
        cancelButtonText: "Cancelar"
    }).then((result) => {
        if (result.isConfirmed) {
            $.post('desactivar', { id_convocatoria: id }, function () {
                location.reload();
            });
            bloqueoAjax();
        }
    });
}

function validarGuardar(evt, formulario, tipo) {
    evt.preventDefault();
    Swal.fire({
        title: "¿DESEA " + tipo + " LA CONVOCATORIA?",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Si",
        cancelButtonText: "No"
    }).then((result) => {
        if (result.isConfirmed) {
            formulario.removeAttribute('onsubmit');
            formulario.submit();
            bloqueoAjax();
        }
    });
}