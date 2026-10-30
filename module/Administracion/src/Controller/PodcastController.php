<?php

declare(strict_types=1);

namespace Administracion\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use Laminas\Authentication\AuthenticationService;
use Administracion\Modelo\DAO\PodcastDAO;
use Administracion\Formularios\PodcastForm;
use Administracion\Modelo\Entidades\Podcast;

class PodcastController extends AbstractActionController
{
    private $DAO;
    private $uploadConfig;
    private $rutaLog = './public/log/';

    // Las imágenes de portada SÍ se guardan en gestorportalv2 (servidor A).
    // Los MP3 se van al servidor B por el navegador (ver podcast.js).
    private $rutaImagenes = '/var/www/html/newportal/archivos/emisora/podcast/';

    public function __construct(PodcastDAO $dao, array $uploadConfig)
    {
        $this->DAO          = $dao;
        $this->uploadConfig = $uploadConfig;
    }

    // -------------------------------------------------------------------------
    private function getInfoSesion(): array
    {
        $info = ['login' => 'SIN INICIO DE SESION'];
        $auth = new AuthenticationService();
        if ($auth->hasIdentity()) {
            $info['login'] = $auth->getIdentity()->login;
        }
        return $info;
    }

    private function registrarLogError(string $accion, \Throwable $ex): void
    {
        $msgLog = "\n" . date('Y-m-d H:i:s') . " {$accion} - PodcastController\n"
            . $ex->getMessage() . "\n"
            . $ex->getTraceAsString()
            . "\n-----------------------------------------------------------------------\n";
        @file_put_contents($this->rutaLog . 'gestorportal.log', $msgLog, FILE_APPEND);
    }

    // =========================================================================
    public function indexAction()
    {
        return new ViewModel([
            'fetchAll' => $this->DAO->fetchAll(),
        ]);
    }

    // =========================================================================
    public function registrarAction()
    {
        $infosesion = $this->getInfoSesion();
        $programas  = $this->DAO->getProgramasActivos();
        $form       = new PodcastForm('registrar', $programas);
        $request    = $this->getRequest();

        if (!$request->isPost()) {
            return (new ViewModel([
                'form'           => $form,
                'uploadEndpoint' => $this->uploadConfig['endpoint'],
                'uploadToken'    => $this->uploadConfig['token'],
                'uploadMock'     => str_starts_with($this->uploadConfig['endpoint'], 'MOCK:'),
            ]))->setTerminal(true);
        }

        $obj = new Podcast();
        $form->setInputFilter($obj->getInputFilter());
        $form->setData($request->getPost());

        if (!$form->isValid()) {
            $this->flashMessenger()->addErrorMessage(
                'INFORMACIÓN NO VÁLIDA: ' . json_encode($form->getMessages(), JSON_UNESCAPED_UNICODE)
            );
            return $this->redirect()->toUrl('index');
        }

        try {
            $audioUrl = (string) $request->getPost('audio_url', '');
            if ($audioUrl === '') {
                throw new \RuntimeException(
                    'Debe adjuntar un archivo MP3. El navegador no reportó la URL del audio.'
                );
            }

            $imagen = (string) $request->getPost('imagen', 'sinImagen.png');

            $obj->exchangeArray($form->getData());
            $obj->setAudio_url($audioUrl);
            $obj->setImagen($imagen);
            $obj->setEstado('Activo');
            $obj->setRegistradopor($infosesion['login']);
            $obj->setModificadopor('');
            $obj->setFechahorareg(date('Y-m-d H:i:s'));
            $obj->setFechahoramod('0000-00-00 00:00:00');

            $this->DAO->registrar($obj);
            $this->flashMessenger()->addSuccessMessage('EL PODCAST FUE REGISTRADO');
        } catch (\Throwable $ex) {
            $this->registrarLogError('REGISTRAR PODCAST', $ex);
            $this->flashMessenger()->addErrorMessage('ERROR: ' . $ex->getMessage());
        }

        return $this->redirect()->toUrl('index');
    }

    // =========================================================================
    public function editarAction()
    {
        $request   = $this->getRequest();
        $idPodcast = $request->isPost()
            ? (int) $request->getPost('idPodcast', 0)
            : (int) $this->params()->fromQuery('idPodcast', 0);

        if ($idPodcast <= 0) {
            $this->flashMessenger()->addErrorMessage('ID DE PODCAST INVÁLIDO');
            return $this->redirect()->toUrl('index');
        }

        $obj = $this->DAO->getPodcast($idPodcast);
        if (!$obj) {
            $this->flashMessenger()->addErrorMessage('EL PODCAST NO EXISTE');
            return $this->redirect()->toUrl('index');
        }

        $programas = $this->DAO->getProgramasActivos();
        $form = new PodcastForm('editar', $programas);
        $form->setInputFilter($obj->getInputFilter());
        $form->setData($obj->getArrayCopy());

        if (!$request->isPost()) {
            return (new ViewModel([
                'form'           => $form,
                'uploadEndpoint' => $this->uploadConfig['endpoint'],
                'uploadToken'    => $this->uploadConfig['token'],
                'uploadMock'     => str_starts_with($this->uploadConfig['endpoint'], 'MOCK:'),
                'audioActual'    => $obj->getAudio_url(),
            ]))->setTerminal(true);
        }

        $form->setData($request->getPost());
        if (!$form->isValid()) {
            $this->flashMessenger()->addErrorMessage(
                'INFORMACIÓN NO VÁLIDA: ' . json_encode($form->getMessages(), JSON_UNESCAPED_UNICODE)
            );
            return $this->redirect()->toUrl('index');
        }

        try {
            $audioUrl = (string) $request->getPost('audio_url', '');
            if ($audioUrl === '') {
                $audioUrl = $obj->getAudio_url();
            }

            $imagen = (string) $request->getPost('imagen', '');
            if (empty($imagen)) {
                $imagen = $obj->getImagen();
            }

            $session = $this->getInfoSesion();
            $obj->exchangeArray($form->getData());
            $obj->setAudio_url($audioUrl);
            $obj->setImagen($imagen);
            $obj->setModificadopor($session['login']);
            $obj->setFechahoramod(date('Y-m-d H:i:s'));

            $this->DAO->editar($obj);
            $this->flashMessenger()->addSuccessMessage('EL PODCAST FUE EDITADO');
        } catch (\Throwable $ex) {
            $this->registrarLogError('EDITAR PODCAST', $ex);
            $this->flashMessenger()->addErrorMessage('ERROR: ' . $ex->getMessage());
        }

        return $this->redirect()->toUrl('index');
    }

    // =========================================================================
    public function detalleAction()
    {
        $id   = (int) $this->params()->fromQuery('idPodcast', 0);
        $info = $this->DAO->getPodcastDetalle($id);
        if (!$info) {
            $this->flashMessenger()->addErrorMessage('EL PODCAST NO EXISTE');
            return $this->redirect()->toUrl('index');
        }
        return (new ViewModel(['info' => $info]))->setTerminal(true);
    }

    // =========================================================================
    public function desactivarAction()
    {
        return $this->cambiarEstadoAction('Eliminado', 'DESACTIVADO', 'desactivar');
    }

    public function activarAction()
    {
        return $this->cambiarEstadoAction('Activo', 'ACTIVADO', 'activar');
    }

    private function cambiarEstadoAction(string $estado, string $etiqueta, string $accionForm)
    {
        $request = $this->getRequest();

        if (!$request->isPost()) {
            $id   = (int) $this->params()->fromQuery('idPodcast', 0);
            $info = $this->DAO->getPodcastDetalle($id);
            if (!$info) {
                $this->flashMessenger()->addErrorMessage('EL PODCAST NO EXISTE');
                return $this->redirect()->toUrl('index');
            }
            $form = new PodcastForm($accionForm);
            $form->setData($info);
            return (new ViewModel(['form' => $form]))->setTerminal(true);
        }

        $id = (int) $request->getPost('idPodcast', 0);
        if ($id <= 0) {
            $this->flashMessenger()->addErrorMessage('ID INVÁLIDO');
            return $this->redirect()->toUrl('index');
        }

        try {
            $session = $this->getInfoSesion();
            $obj = new Podcast();
            $obj->setIdPodcast($id);
            $obj->setModificadopor($session['login']);
            $obj->setFechahoramod(date('Y-m-d H:i:s'));
            $this->DAO->eliminarActivar($obj, $estado);
            $this->flashMessenger()->addSuccessMessage("EL PODCAST FUE {$etiqueta}");
        } catch (\Throwable $ex) {
            $this->registrarLogError(strtoupper($accionForm) . ' PODCAST', $ex);
            $this->flashMessenger()->addErrorMessage("EL PODCAST NO FUE {$etiqueta}");
        }
        return $this->redirect()->toUrl('index');
    }

    // =========================================================================
    public function eliminarAction()
    {
        $request = $this->getRequest();

        if (!$request->isPost()) {
            $id   = (int) $this->params()->fromQuery('idPodcast', 0);
            $info = $this->DAO->getPodcastDetalle($id);
            if (!$info) {
                $this->flashMessenger()->addErrorMessage('EL PODCAST NO EXISTE');
                return $this->redirect()->toUrl('index');
            }
            $form = new PodcastForm('eliminar');
            $form->setData($info);
            return (new ViewModel(['form' => $form]))->setTerminal(true);
        }

        $id = (int) $request->getPost('idPodcast', 0);
        if ($id <= 0) {
            $this->flashMessenger()->addErrorMessage('ID INVÁLIDO');
            return $this->redirect()->toUrl('index');
        }

        try {
            $this->DAO->borrarPodcast($id);
            $this->flashMessenger()->addSuccessMessage(
                'EL PODCAST FUE ELIMINADO DEFINITIVAMENTE. ' .
                    'Recuerde borrar el MP3 del servidor de la emisora manualmente si es necesario.'
            );
        } catch (\Throwable $ex) {
            $this->registrarLogError('ELIMINAR PODCAST', $ex);
            $this->flashMessenger()->addErrorMessage('ERROR: ' . $ex->getMessage());
        }
        return $this->redirect()->toUrl('index');
    }

    // =========================================================================
    // ADMINISTRACIÓN DE ARCHIVOS EN SERVIDOR B
    // =========================================================================
    public function gestionarchivosAction()
    {
        // 1. Obtener los archivos que están registrados en la BD
        $podcasts = $this->DAO->fetchAll();
        $archivosEnUso = [];
        foreach ($podcasts as $p) {
            if (!empty($p['audio_url'])) {
                $archivosEnUso[] = basename(parse_url($p['audio_url'], PHP_URL_PATH));
            }
            if (!empty($p['imagen']) && $p['imagen'] !== 'sinImagen.png') {
                $archivosEnUso[] = $p['imagen'];
            }
        }

        // 2. Pedir la lista al Servidor B vía cURL
        $archivosServidor = [];
        $urlBase = str_replace('upload_podcast.php', 'gestionar_archivos.php', $this->uploadConfig['endpoint']);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $urlBase . '?accion=listar&token=' . urlencode($this->uploadConfig['token']));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        // Si el servidor B usa un certificado SSL autofirmado en pruebas, descomenta la siguiente línea:
        // curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $respuesta = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200 && $respuesta) {
            $json = json_decode($respuesta, true);
            if (isset($json['archivos'])) {
                $archivosServidor = $json['archivos'];
            }
        } else {
            $this->flashMessenger()->addErrorMessage('No se pudo conectar con el Servidor B para listar los archivos.');
        }

        // 3. Cruzar datos y calcular tamaños
        $listaFinal = [];
        foreach ($archivosServidor as $archivo) {
            $archivo['en_uso'] = in_array($archivo['nombre'], $archivosEnUso);
            $archivo['tamano_legible'] = $this->formatearBytes($archivo['tamano']);
            $listaFinal[] = $archivo;
        }

        // Ordenar: Primero los huérfanos (no en uso), luego por peso (más grandes primero)
        usort($listaFinal, function ($a, $b) {
            if ($a['en_uso'] !== $b['en_uso']) return $a['en_uso'] ? 1 : -1;
            return $b['tamano'] <=> $a['tamano'];
        });

        return new ViewModel([
            'archivos' => $listaFinal,
            'uploadToken' => $this->uploadConfig['token'],
            'uploadEndpoint' => $urlBase,
            // NUEVAS VARIABLES: Calculamos las URLs base reemplazando el nombre del script
            'baseUrlAudios' => str_replace('upload_podcast.php', 'audios/podcast/', $this->uploadConfig['endpoint']),
            'baseUrlImagenes' => str_replace('upload_podcast.php', 'audios/podcast/imagenes/', $this->uploadConfig['endpoint']),
        ]);
    }

    public function eliminararchivoAction()
    {
        $request = $this->getRequest();
        if (!$request->isPost()) {
            return $this->redirect()->toUrl('gestionarchivos');
        }

        $nombre = $request->getPost('nombre');
        $tipo = $request->getPost('tipo');

        $urlBase = str_replace('upload_podcast.php', 'gestionar_archivos.php', $this->uploadConfig['endpoint']);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $urlBase);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
            'accion' => 'eliminar',
            'nombre' => $nombre,
            'tipo'   => $tipo,
            'token'  => $this->uploadConfig['token']
        ]));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $respuesta = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200) {
            $this->flashMessenger()->addSuccessMessage('EL ARCHIVO FUE ELIMINADO DEL SERVIDOR B.');
        } else {
            $json = json_decode($respuesta, true);
            $errorMsg = $json['error'] ?? 'Error desconocido';
            $this->flashMessenger()->addErrorMessage('NO SE PUDO ELIMINAR: ' . $errorMsg);
        }

        return $this->redirect()->toUrl('gestionarchivos');
    }

    // Función auxiliar para formatear bytes a MB/GB
    private function formatearBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
