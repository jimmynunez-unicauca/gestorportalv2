<?php

declare(strict_types=1);

namespace Inicio\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\Authentication\AuthenticationService;
use Laminas\View\Model\ViewModel;
use Inicio\Modelo\DAO\InicioDAO;
use Inicio\Modelo\DAO\PortalDAO;

class BandejaController extends AbstractActionController
{

    private $DAO;
    private $portalDAO;

    public function __construct(InicioDAO $dao, PortalDAO $portalDao)
    {
        $this->DAO = $dao;
        $this->portalDAO = $portalDao;
    }
    //------------------------------------------------------------------------------

    public function getInfoSesion()
    {
        $infoSesion = [
            'idEmpleadoCliente' => 0,
            'login' => 'SIN INICIO DE SESION',
            'usuario' => 'SIN INICIO DE SESION',
        ];
        $auth = new AuthenticationService();
        if ($auth->hasIdentity()) {
            $infoSesion['login'] = $auth->getIdentity()->login;
            $infoSesion['idEmpleadoCliente'] = $auth->getIdentity()->idEmpleadoCliente;
            $infoSesion['usuario'] = $auth->getIdentity()->usuario;
        }
        return $infoSesion;
    }

    //------------------------------------------------------------------------------

    public function indexAction()
    {
        $infosesion = $this->getInfoSesion();
        $usuario = $infosesion['usuario'];
        $anio = (int) $this->params()->fromQuery('anio', date('Y'));
        $anios = $this->DAO->obtenerAniosDistinct();
        // Array con los nombres de los formularios
        $forms = [
            'form_academica',
            'form_agraria',
            'form_artes',
            'form_cecav',
            'form_conflicto_interes',
            'form_contables',
            'form_cp',
            'form_cultura',
            'form_dae_empre',
            'form_dae_propiedad',
            'form_egresados',
            'form_emisora',
            'form_fchs',
            'form_fic',
            'form_fiet',
            'form_ocdi',
            'form_orii',
            'form_pqrsf',
            'form_rectoria',
            'form_rendicion_cuentas',
            'form_viceadmin',
            'form_viceinvest',
            'form_facned',
            'form_fderecho',
            'form_fsalud',
            'form_comarca',
            'form_secretariageneral',
            'form_unisalud',
            'form_unisalud_rendicion_cuentas',
        ];
        // Inicializar arrays para almacenar los contadores y los datos adicionales por mes
        $formCounts = [];
        $additionalData = [];
        foreach ($forms as $form) {
            // Contador total de cada formulario
            $formCounts[$form] = $this->DAO->getCountForm($form);
            // Obtener la cantidad de registros por cada mes del año
            for ($month = 1; $month <= 12; $month++) {
                $startDate = sprintf($anio . '-%02d-01', $month); // Formato: YYYY-MM-01
                $endDate = sprintf($anio . '-%02d-%02d', $month, cal_days_in_month(CAL_GREGORIAN, $month, $anio)); // Formato: YYYY-MM-DD
                // Guardar el resultado en un array asociativo con el nombre del formulario y mes
                $additionalData["{$form}_{$month}"] = $this->DAO->getTotalByFechas($form, $startDate, $endDate);
            }
        }
        $otrosDatos = [
            'anio' => $anio,
            'anios' => $anios,
            'usuario' => $usuario,
            // NUEVO: Datos adicionales para el dashboard mejorado
            'ultimas_actividades' => $this->DAO->getUltimasActividades(10),
            'eventos_proximos' => $this->DAO->getEventosProximos(5),
            'documentos_recientes' => $this->DAO->getDocumentosRecientes(5),
            'estadisticas_semanales' => $this->DAO->getEstadisticasSemanales($anio, date('m')),
            // WordPress / newportal
            'wp_summary' => $this->portalDAO->getSummary(),
            'wp_posts' => $this->portalDAO->getRecentPosts(5),
            'wp_comments' => $this->portalDAO->getRecentComments(5),
            'wp_users' => $this->portalDAO->getRecentUsers(5),
            'wp_events' => $this->portalDAO->getUpcomingEvents(5),
        ];
        // Combinar todos los datos para la vista
        $viewData = array_merge($formCounts, $additionalData, $otrosDatos);
        // Enviar todos los datos a la vista
        return new ViewModel($viewData);
    }
}
