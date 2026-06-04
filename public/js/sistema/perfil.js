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

function verPerfil(idUsuario) {
    $.get('detalle', { idUsuario: idUsuario }, setFormulario);
    bloqueoAjax();
}
function verEditar(idEmpleado) {
    $.get('editar', { idEmpleado: idEmpleado }, setFormulario);
    bloqueoAjax();
}
function cambiarPassword() {
    $.get('cambiarpassword', {}, setFormulario);
    bloqueoAjax();
}
function setFormulario(datos) {
    $("#divContenido").html(datos);
    $('#modalFormulario').modal('show');
}
function validarGuardar(evt, formulario, tipo) {
    evt.preventDefault();
    Swal.fire({
        title: "&#191;DESEA " + tipo + " EL PERFIL?",
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
function verificarPassword() {
    let password = $('#password').val();
    let passwordConfirm = $('#passwordConfirm').val();
    let errorMessage = '';
    const insecurePasswords = [
        "Password123!", "12345!Abcdefghi", "Changeme123!",
        "Baseball123!", "Admin!1234567890", "Letmein!123abcxyz@",
        "Test123!xyz123@", "P@ssword123!", "Abc123!xyz456@",
        "Qazwsx!123abcxyz@", "1234Abcd!xyz123@", "1Qaz2Wsx3Edc!@",
        "!Qwerty123abcxyz@", "P@ssw0rd123!", "Soccer123!xyz789@",
        "Golfer123!abcxyz@", "Changeme!123xyzabc@", "Starwars123!abcdef@",
        "Football123!xyzdef@", "@Dmin1234567"
    ];

    if (password !== '') {
        if (password.length < 12) {
            errorMessage += '<p>La contraseña debe tener al menos 12 caracteres.</p>';
            $('#passwordConfirm').val('');
        }
        if (!/[A-Z]/.test(password)) {
            errorMessage += '<p>La contraseña debe contener al menos una letra mayúscula.</p>';
            $('#passwordConfirm').val('');
        }
        if (!/[a-z]/.test(password)) {
            errorMessage += '<p>La contraseña debe contener al menos una letra minúscula.</p>';
            $('#passwordConfirm').val('');
        }
        if (!/[0-9]/.test(password)) {
            errorMessage += '<p>La contraseña debe contener al menos un número.</p>';
            $('#passwordConfirm').val('');
        }
        if (!/[!@#$%^&*(),.?":{}|<>]/.test(password)) {
            errorMessage += '<p>La contraseña debe contener al menos un carácter especial.</p>';
            $('#passwordConfirm').val('');
        }
        if (password.indexOf(' ') !== -1) {
            errorMessage += '<p>La contraseña no puede contener espacios en blanco.</p>';
            $('#passwordConfirm').val('');
        }
        if (password !== passwordConfirm) {
            errorMessage += '<p>Las contraseñas no coinciden.</p>';
            $('#passwordConfirm').val('');
        }
        if (insecurePasswords.includes(password)) {
            errorMessage += '<p>La contraseña ingresada es demasiado común y no es segura. Por favor, elige una contraseña más fuerte.</p>';
            $('#passwordConfirm').val('');
        }

        if (errorMessage) {
            $('#error').html('<div class="alert alert-danger">' + errorMessage + '</div>');
        } else {
            $('#error').html('<div class="alert alert-success">La contraseña cumple con los estándares de seguridad establecidos.</div>');
        }
    }


}
function guardarNuevoPassword() {
    let password = $('#password').val();
    let passwordConfirm = $('#passwordConfirm').val();
    if (password !== '' && passwordConfirm !== '') {
        Swal.fire({
            html:
                '<h2><b>PARA QUE EL CAMBIO DE CONTRASEÑA SEA REGISTRADO LA SESION ACTUAL DEBE CERRARSE</b></h2>' +
                '<h4><small>&#191;DESEA REGISTRAR EL CAMBIO DE CONTRASEÑA&#63;</small></h4>' +
                '<p class="animated bounce estilologo"><i><i class="fa fa-paw"></i>GestorPortal</i></p>',
            icon: 'info',
            allowOutsideClick: false,
            confirmButtonText: 'Aceptar',
            showCancelButton: true,
        }).then((result) => {
            if (result.isConfirmed) {
                $.post('cambiarpassword', $("#formCambiarpassword").serialize(), setNuevoPassword, 'json');
                bloqueoAjax();
            }
        });
        return false;
    } else {
        Swal.fire('LOS CAMPOS NO PUEDEN ESTAR VACIOS', 'GestorPortal', 'error');
    }
}

function setNuevoPassword(respuesta) {
    switch (parseInt(respuesta['error'])) {
        case 0:
            Swal.fire({
                title: 'LA CONTRASEÑA FUE ACTUALIZADA',
                icon: 'success',
                text: 'GestorPortal',
                allowOutsideClick: false,
                confirmButtonText: 'Aceptar',
            }).then((result) => {
                if (result.isConfirmed) {
                    location.href = '/gestorportalv2/cerrarsesion';
                }
            });
            break;
        case 1:
            Swal.fire({
                title: 'LA CONTRASEÑA NO FUE ACTUALIZADA',
                icon: 'error',
                text: 'GestorPortal',
                allowOutsideClick: false,
                confirmButtonText: 'Aceptar',
            }).then((result) => {
                if (result.isConfirmed) {
                    $('#modalFormulario').modal('hide');
                }
            });
            break;
        case 2:
            Swal.fire({
                title: 'LA CONTRASEÑA ACTUAL ES INCORRECTA',
                icon: 'error',
                text: 'GestorPortal',
                allowOutsideClick: false,
                confirmButtonText: 'Aceptar',
            }).then((result) => {
                if (result.isConfirmed) {
                    $('#modalFormulario').modal('hide');
                }
            });
            break;
    }
    return false;
}
//------------------------------------------------------------------------------