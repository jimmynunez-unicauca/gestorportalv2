<?php

namespace Administracion\Formularios;

use Laminas\Form\Form;
use Laminas\Form\Element;

class OrganigramaForm extends Form
{
    public function __construct($accion = '', $nodosPadre = [])
    {
        parent::__construct('formOrganigrama');
        $this->setAttribute('method', 'post');
        $this->setAttribute('data-toggle', 'validator');
        $this->setAttribute('enctype', 'multipart/form-data');
        $this->setAttribute('action', $accion);

        // ID (oculto)
        $this->add([
            'type' => Element\Hidden::class,
            'name' => 'id',
            'attributes' => [
                'id' => 'id',
            ],
        ]);

        // Nombre
        $this->add([
            'type' => Element\Text::class,
            'name' => 'nombre',
            'options' => [
                'label' => 'Nombre del nodo *',
            ],
            'attributes' => [
                'required' => true,
                'class' => 'form-control',
                'id' => 'nombre',
                'placeholder' => 'Ej: Vicerrectoría Académica',
            ],
        ]);

        // Tipo
        $this->add([
            'type' => Element\Select::class,
            'name' => 'tipo',
            'options' => [
                'label' => 'Tipo *',
                'value_options' => [
                    '' => 'Seleccione...',

                    // Nivel Rectoría
                    'root' => '🏛️ Rectoría',

                    // Nivel Vicerrectorías
                    'vicerector' => '📌 Vicerrectoría',

                    // Nivel Facultades
                    'facultad' => '📚 Facultad',

                    // Nivel Divisiones y Departamentos
                    'division' => '📂 División',
                    'depto' => '📁 Departamento',

                    // Nivel Centros y Escuelas
                    'centro' => '⚖️ Centro',
                    'escuela' => '🎓 Escuela',
                    'instituto' => '🔬 Instituto',
                    'conservatorio' => '🎵 Conservatorio',

                    // Nivel Oficinas y Secretarías
                    'oficina' => '📋 Oficina',
                    'secretaria' => '📜 Secretaría',
                    'coordinacion' => '📌 Coordinación',
                    'decanatura' => '👨‍🏫 Decanatura',

                    // Nivel Comités y Consejos
                    'comite' => '🤝 Comité',
                    'consejo' => '👥 Consejo',
                    'junta' => '⚙️ Junta',

                    // Nivel Áreas y Grupos
                    'area' => '📍 Área',
                    'grupo' => '👥 Grupo',
                    'semillero' => '🌱 Semillero',

                    // Nivel Sedes y Regionalización
                    'sede' => '🏢 Sede',
                    'regionalizacion' => '🗺️ Regionalización',

                    // Nivel Programas y Estudios
                    'programa' => '📖 Programa',
                    'estudios' => '📘 Estudios',
                    'posgrado' => '🎓 Posgrado',
                    'maestria' => '📜 Maestría',
                    'doctorado' => '📜 Doctorado',
                    'especializacion' => '📜 Especialización',

                    // Nivel Unidades de apoyo
                    'unidad' => '⚙️ Unidad',
                    'laboratorio' => '🔬 Laboratorio',
                    'biblioteca' => '📚 Biblioteca',
                    'archivo' => '📁 Archivo',

                    // Nivel Proyectos
                    'proyecto' => '📊 Proyecto',
                    'observatorio' => '🔭 Observatorio',
                    'consultorio' => '⚖️ Consultorio',

                    // Nivel Otros
                    'catedra' => '📖 Cátedra',
                    'fondo' => '💰 Fondo',
                    'red' => '🌐 Red',
                ],
            ],
            'attributes' => [
                'required' => true,
                'class' => 'form-control',
                'id' => 'tipo',
            ],
        ]);

        // Padre (dependencia)
        $optionsPadre = ['' => '-- Ninguno (Raíz) --'];
        foreach ($nodosPadre as $np) {
            $optionsPadre[$np['id']] = $np['nombre'] . ' (' . $np['tipo'] . ')';
        }

        $this->add([
            'type' => Element\Select::class,
            'name' => 'padre_id',
            'options' => [
                'label' => 'Depende de',
                'value_options' => $optionsPadre,
            ],
            'attributes' => [
                'class' => 'form-control',
                'id' => 'padre_id',
            ],
        ]);

        // Ícono
        /* $this->add([
            'type' => Element\Select::class,
            'name' => 'icono',
            'options' => [
                'label' => 'Ícono',
                'value_options' => [
                    '' => '-- Por defecto --',
                    // Institucionales
                    'fa-university' => '🏛️ Universidad / Rectoría',
                    'fa-building' => '🏢 Edificio / Oficina',
                    'fa-institution' => '🏛️ Institución',
                    'fa-landmark' => '🏛️ Monumento / Rectoría',
                    // Vicerrectorías y Dirección
                    'fa-users' => '👥 Vicerrectoría / Dirección',
                    'fa-user-tie' => '👔 Decanatura / Dirección',
                    'fa-sitemap' => '🗺️ Organigrama / Estructura',
                    // Facultades y Academias
                    'fa-graduation-cap' => '🎓 Facultad / Graduación',
                    'fa-graduation-cap' => '📚 Educación / Clase',
                    'fa-school' => '🏫 Escuela / Colegio',
                    'fa-book-open' => '📖 Programa / Estudio',
                    // Divisiones y Departamentos
                    'fa-folder-open' => '📂 División / Departamento',
                    'fa-folder' => '📁 Carpeta / Archivo',
                    'fa-layer-group' => '🗂️ Grupo / Capa',
                    // Centros y Laboratorios
                    'fa-cubes' => '🧊 Centro / Laboratorio',
                    'fa-flask' => '🔬 Laboratorio / Ciencia',
                    'fa-microscope' => '🔬 Investigación',
                    'fa-dna' => '🧬 Genética / Biología',
                    // Oficinas y Secretarías
                    'fa-desktop' => '💻 Oficina / Tecnología',
                    'fa-laptop' => '💻 Computador / TIC',
                    'fa-print' => '🖨️ Imprenta / Documentos',
                    'fa-file-alt' => '📄 Documentos / Secretaría',
                    'fa-envelope' => '✉️ Correspondencia',
                    // Derecho y Legal
                    'fa-gavel' => '⚖️ Derecho / Justicia',
                    'fa-balance-scale' => '⚖️ Balanza / Derecho',
                    'fa-hand-peace' => '✋ Consultorio Jurídico',
                    // Salud
                    'fa-heartbeat' => '❤️ Salud / Medicina',
                    'fa-hospital' => '🏥 Hospital / Clínica',
                    'fa-stethoscope' => '🩺 Medicina / Salud',
                    'fa-pills' => '💊 Farmacia',
                    'fa-tooth' => '🦷 Odontología',
                    // Artes y Cultura
                    'fa-music' => '🎵 Música / Conservatorio',
                    'fa-paint-brush' => '🎨 Artes / Pintura',
                    'fa-palette' => '🎨 Artes Plásticas',
                    'fa-theater-masks' => '🎭 Teatro / Artes Escénicas',
                    'fa-guitar' => '🎸 Música Instrumental',
                    'fa-trumpet' => '🎺 Banda / Música',
                    // Ciencias Agrarias y Ambientales
                    'fa-leaf' => '🌿 Agrarias / Ambiente',
                    'fa-tree' => '🌳 Bosques / Forestal',
                    'fa-seedling' => '🌱 Agronomía / Semillero',
                    'fa-tractor' => '🚜 Agroindustria',
                    'fa-water' => '💧 Agua / Hidráulica',
                    // Ingeniería y Tecnología
                    'fa-microchip' => '💻 Electrónica / Sistemas',
                    'fa-code' => '</> Sistemas / Programación',
                    'fa-database' => '🗄️ Base de Datos',
                    'fa-wifi' => '📡 Telecomunicaciones / Redes',
                    'fa-hard-drive' => '💾 Hardware / Tecnología',
                    'fa-cogs' => '⚙️ Ingeniería / Mecánica',
                    'fa-drafting-compass' => '📐 Ingeniería Civil / Diseño',
                    'fa-ruler-combined' => '📏 Construcción / Vías',
                    'fa-chart-line' => '📊 Administrativa / Gestión',
                    'fa-chart-bar' => '📊 Estadísticas / Finanzas',
                    // Ciencias y Matemáticas
                    'fa-calculator' => '🧮 Matemáticas',
                    'fa-atom' => '⚛️ Física / Ciencias',
                    'fa-dna' => '🧬 Biología / Genética',
                    'fa-flask' => '🧪 Química / Laboratorio',
                    'fa-globe' => '🌍 Geografía / Global',
                    'fa-history' => '📜 Historia',
                    'fa-language' => '🗣️ Idiomas / Lingüística',
                    'fa-philosophy' => '💭 Filosofía',
                    'fa-pray' => '🙏 Estudios Religiosos',
                    // Comunicación
                    'fa-newspaper' => '📰 Comunicación / Prensa',
                    'fa-tv' => '📺 Televisión',
                    'fa-radio' => '📻 Radio',
                    'fa-photo-video' => '📷 Audiovisuales',
                    // Deportes y Recreación
                    'fa-futbol' => '⚽ Deportes / Educación Física',
                    'fa-basketball' => '🏀 Baloncesto',
                    'fa-swimmer' => '🏊 Natación',
                    'fa-running' => '🏃 Atletismo',
                    // Bienestar
                    'fa-gratipay' => '❤️ Bienestar',
                    'fa-smile' => '😊 Psicología / Bienestar',
                    'fa-heart' => '❤️ Salud Integral',
                    // Comités y Consejos
                    'fa-object-group' => '🤝 Comité / Consejo',
                    'fa-cogs' => '⚙️ Junta / Gestión',
                    'fa-chalkboard' => '📋 Consejo Académico',
                    // Bibliotecas y Archivos
                    'fa-book' => '📚 Biblioteca / Libros',
                    'fa-archive' => '📦 Archivo / Histórico',
                    // Regionalización y Sedes
                    'fa-map-marker-alt' => '📍 Sede / Ubicación',
                    'fa-map' => '🗺️ Regionalización',
                    // Investigación
                    'fa-search' => '🔍 Investigación',
                    'fa-lightbulb' => '💡 Innovación',
                    'fa-chart-line' => '📈 Proyectos',
                    // Otros
                    'fa-star' => '⭐ Destacado',
                    'fa-certificate' => '📜 Certificación',
                    'fa-tasks' => '✅ Tareas / Gestión',
                    'fa-clock' => '⏰ Tiempo / Calendario',
                    'fa-calendar-alt' => '📅 Eventos / Calendario',
                    'fa-phone-alt' => '📞 Contacto',
                    'fa-envelope' => '✉️ Correo',
                    'fa-comments' => '💬 Comunicación',
                    'fa-money' => '💵 Finanzas',
                    'fa-users' => '👥 Usuarios',
                ],
            ],
            'attributes' => [
                'class' => 'form-control',
                'id' => 'icono',
            ],
        ]); */

        // Color del nodo
        /* $this->add([
            'type' => Element\Select::class,
            'name' => 'color',
            'options' => [
                'label' => 'Color del nodo',
                'value_options' => [
                    '' => '-- Por defecto --',
                    '#0d2b42' => '🔵 Azul marino (Rectoría)',
                    '#1e4d6f' => '🔵 Azul (Vicerrectoría)',
                    '#fef7e8' => '🟡 Beige (Facultad)',
                    '#f5f7fa' => '⚪ Gris claro (Departamento)',
                    '#dc3545' => '🔴 Rojo',
                    '#28a745' => '🟢 Verde',
                    '#ffc107' => '🟡 Amarillo',
                    '#17a2b8' => '🔵 Cyan',
                    '#6f42c1' => '🟣 Púrpura',
                    '#fd7e14' => '🟠 Naranja',
                    '#20c997' => '🟢 Verde menta',
                    '#e83e8c' => '🌸 Rosa',
                    '#6c757d' => '⚫ Gris',
                    '#343a40' => '⚫ Gris oscuro',
                ],
            ],
            'attributes' => [
                'class' => 'form-control',
                'id' => 'color',
            ],
        ]); */

        // Orden
        $this->add([
            'type' => Element\Number::class,
            'name' => 'orden',
            'options' => [
                'label' => 'Orden',
            ],
            'attributes' => [
                'class' => 'form-control',
                'id' => 'orden',
                'min' => 0,
                'value' => 0,
            ],
        ]);

        // Descripción
        $this->add([
            'type' => Element\Textarea::class,
            'name' => 'descripcion',
            'options' => [
                'label' => 'Descripción',
            ],
            'attributes' => [
                'class' => 'form-control',
                'id' => 'descripcion',
                'rows' => 3,
                'placeholder' => 'Descripción opcional del nodo',
            ],
        ]);

        // Estado
        $this->add([
            'type' => Element\Text::class,
            'name' => 'estado',
            'options' => [
                'label' => 'Estado',
            ],
            'attributes' => [
                'readonly' => true,
                'class' => 'form-control',
                'id' => 'estado',
            ],
        ]);

        // Registrado por
        $this->add([
            'type' => Element\Text::class,
            'name' => 'registradopor',
            'options' => [
                'label' => 'Registrado Por',
            ],
            'attributes' => [
                'readonly' => true,
                'class' => 'form-control',
                'id' => 'registradopor',
            ],
        ]);

        // Fecha registro
        $this->add([
            'type' => Element\Text::class,
            'name' => 'created_at',
            'options' => [
                'label' => 'Fecha Registro',
            ],
            'attributes' => [
                'readonly' => true,
                'class' => 'form-control',
                'id' => 'created_at',
            ],
        ]);
    }
}
