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
function verEditar(idUsuario) {
    $.get('editar', { idUsuario: idUsuario }, setFormulario);
    bloqueoAjax();
}
function verDetalle(idUsuario) {
    $.get('detalle', { idUsuario: idUsuario }, setFormulario);
    bloqueoAjax();
}
function verEliminar(idUsuario, estado) {
    $.get('eliminar', { idUsuario: idUsuario, estado: estado }, setFormulario);
    bloqueoAjax();
}
function setFormulario(datos) {
    $("#divContenido").html(datos);
    $('#modalFormulario').modal('show');
}
//------------------------------------------------------------------------------
function validarGuardar(evt, formulario, tipo) {
    evt.preventDefault();
    Swal.fire({
        title: "DESEA " + tipo + " EL USUARIO ?",
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
function getLogin(idEmpleadoCliente) {
    $("#login").val('');
    $("#password").val('');
    $("#passwordseguro").val('');
    $("#usuario").val('');
    if (idEmpleadoCliente !== '') {
        $("#login").attr('readonly', true);
        $("#login").attr('required', false);
        $("#usuario").attr('readonly', true);
        $("#usuario").attr('required', false);
        $.get('getLogin', { idEmpleadoCliente: idEmpleadoCliente }, setLogin, 'json');
    } else {
        $("#login").attr('readonly', false);
        $("#login").attr('required', true);
        $("#usuario").attr('readonly', false);
        $("#usuario").attr('required', true);
    }
}
function setLogin(datos) {
    if (parseInt(datos['error']) === 1) {
        alert("SE HA PRESENTADO UN INCONVENIENTE AL TRATAR DE OBTENER EL LOGIN DE USUARIO. POR FAVOR, INTENTE DE NUEVO. EN CASO DE PERSISTIR EL INCONVENIENTE COMUNIQUESE CON EL ADMINISTRADOR");
        location.reload();
        return;
    }
    let passwordGenerada = generarPassword();
    $("#login").val(quitarAcentos(datos['login'].toLowerCase()));
    $("#usuario").val(datos['usuario']);
    $("#password").val(passwordGenerada);
    $("#passwordseguro").val(passwordGenerada);
}

function verificarPassword() {
    let password = $('#password').val();
    let passwordConfirm = $('#passwordseguro').val();
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
            $('#passwordseguro').val('');
        }
        if (!/[A-Z]/.test(password)) {
            errorMessage += '<p>La contraseña debe contener al menos una letra mayúscula.</p>';
            $('#passwordseguro').val('');
        }
        if (!/[a-z]/.test(password)) {
            errorMessage += '<p>La contraseña debe contener al menos una letra minúscula.</p>';
            $('#passwordseguro').val('');
        }
        if (!/[0-9]/.test(password)) {
            errorMessage += '<p>La contraseña debe contener al menos un número.</p>';
            $('#passwordseguro').val('');
        }
        if (!/[!@#$%^&*(),.?":{}|<>]/.test(password)) {
            errorMessage += '<p>La contraseña debe contener al menos un carácter especial.</p>';
            $('#passwordseguro').val('');
        }
        if (password.indexOf(' ') !== -1) {
            errorMessage += '<p>La contraseña no puede contener espacios en blanco.</p>';
            $('#passwordseguro').val('');
        }
        if (password !== passwordConfirm) {
            errorMessage += '<p>Las contraseñas no coinciden.</p>';
            $('#passwordseguro').val('');
        }
        if (insecurePasswords.includes(password)) {
            errorMessage += '<p>La contraseña ingresada es demasiado común y no es segura. Por favor, elige una contraseña más fuerte.</p>';
            $('#passwordseguro').val('');
        }

        if (errorMessage) {
            $('#error').html('<div class="alert alert-danger">' + errorMessage + '</div>');
        } else {
            $('#error').html('<div class="alert alert-success">La contraseña cumple con los estándares de seguridad establecidos.</div>');
        }
    }
}
function quitarAcentos(cadena) {
    const acentos = { 'á': 'a', 'é': 'e', 'í': 'i', 'ó': 'o', 'ú': 'u', 'ñ': 'n', 'Á': 'A', 'É': 'E', 'Í': 'I', 'Ó': 'O', 'Ú': 'U', 'Ñ': 'N' };
    return cadena.split('').map(letra => acentos[letra] || letra).join('').toString();
}
//------------------------------------------------------------------------------
function generarPassword() {
    const longitud = 12; // Longitud deseada de la contraseña
    const caracteresMayusculas = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
    const caracteresMinusculas = "abcdefghijklmnopqrstuvwxyz";
    const caracteresNumeros = "0123456789";
    const caracteresEspeciales = "!@#$%^&*()_+{}[]|;:,.<>?";
    const caracteresTodos = caracteresMayusculas + caracteresMinusculas + caracteresNumeros;

    // Seleccionar al menos un carácter de cada tipo necesario
    let password = '';
    password += caracteresMayusculas.charAt(Math.floor(Math.random() * caracteresMayusculas.length));
    password += caracteresMinusculas.charAt(Math.floor(Math.random() * caracteresMinusculas.length));
    password += caracteresNumeros.charAt(Math.floor(Math.random() * caracteresNumeros.length));
    const caracterEspecialAleatorio = caracteresEspeciales.charAt(Math.floor(Math.random() * caracteresEspeciales.length));
    password += caracterEspecialAleatorio;

    // Completar el resto de la contraseña con caracteres aleatorios que no sean especiales
    for (let i = password.length; i < longitud; i++) {
        password += caracteresTodos.charAt(Math.floor(Math.random() * caracteresTodos.length));
    }

    // Mezclar los caracteres de la contraseña para mayor seguridad
    password = password.split('').sort(() => 0.5 - Math.random()).join('');

    return password;
}
function generarContrasenia() {
    let passwordGenerada = generarPassword();
    $("#password").val(passwordGenerada);
    $("#passwordseguro").val(passwordGenerada);
}
//------------------------------------------------------------------------------
