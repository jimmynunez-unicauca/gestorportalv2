<?php

declare(strict_types=1);

namespace Documentos\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\Authentication\AuthenticationService;
use Laminas\View\Model\ViewModel;
use Laminas\View\Model\JsonModel;
use Documentos\Modelo\DAO\OcdiDAO;
use Documentos\Formularios\OcdiForm;
use Documentos\Modelo\Entidades\Ocdi;

class OcdiController extends AbstractActionController
{
    private $DAO;

    //--------------------------------------------------------------------------
    // RUTAS (Windows con forward slashes, sin "/" al final)
    //--------------------------------------------------------------------------
    private $rutaLog      = './public/log/';
    private $rutaArchivos = '/var/www/html/newportal/archivos/ocdi/revistas/documentos/';
    private $rutaImagenes = '/var/www/html/newportal/archivos/ocdi/revistas/imagenes/';
    /* private $rutaArchivos = './../newportal/archivos/ocdi/revistas/documentos';
    private $rutaImagenes = './../newportal/archivos/ocdi/revistas/imagenes'; */

    //--------------------------------------------------------------------------
    public function __construct(OcdiDAO $dao)
    {
        $this->DAO = $dao;
    }

    //--------------------------------------------------------------------------
    // SESIÓN
    //--------------------------------------------------------------------------
    public function getInfoSesion()
    {
        $infoSesion = [
            'idEmpleadoCliente' => 0,
            'login'             => 'SIN INICIO DE SESION',
        ];
        $auth = new AuthenticationService();
        if ($auth->hasIdentity()) {
            $infoSesion['login']              = $auth->getIdentity()->login;
            $infoSesion['idEmpleadoCliente']  = $auth->getIdentity()->idEmpleadoCliente;
            $infoSesion['idUsuario']          = $auth->getIdentity()->idUsuario;
        }
        return $infoSesion;
    }

    //--------------------------------------------------------------------------
    // FORMULARIO
    //--------------------------------------------------------------------------
    private function getFormulario($action = '', $idOcdi = 0)
    {
        $form = new OcdiForm($action);
        if ($idOcdi != 0) {
            $ocdiOBJ = $this->DAO->getOcdi($idOcdi);
            $form->bind($ocdiOBJ);
        }
        return $form;
    }

    //--------------------------------------------------------------------------
    // LOG DE ERRORES
    //--------------------------------------------------------------------------
    private function registrarLogError(string $contexto, \Throwable $ex): void
    {
        $msgLog = "\n" . date('Y-m-d H:i:s') . " {$contexto} - OcdiController \n"
            . $ex->getMessage()
            . "\n----------------------------------------------------------------------- \n";

        if (!is_dir($this->rutaLog)) {
            @mkdir($this->rutaLog, 0775, true);
        }
        @file_put_contents($this->rutaLog . 'gestorportal.log', $msgLog, FILE_APPEND);
    }

    //--------------------------------------------------------------------------
    // HELPER ÚNICO DE SUBIDA DE ARCHIVOS
    //
    // Retorna:
    //   ['ok' => bool, 'sinArchivo' => bool, 'nombre' => ?string]
    //   - sinArchivo = true  → el usuario no envió archivo
    //   - ok = false         → hubo error (ya se agregó flash message)
    //   - ok = true          → 'nombre' tiene el basename final
    //--------------------------------------------------------------------------
    private function procesarArchivo(
        array  $files,
        string $nombreCampo,
        array  $extensiones,
        string $tamanoMin,
        string $tamanoMax,
        string $rutaDestino,
        string $prefijo
    ): array {
        if (!isset($files[$nombreCampo]) || $files[$nombreCampo]['error'] === UPLOAD_ERR_NO_FILE) {
            return ['ok' => false, 'sinArchivo' => true, 'nombre' => null];
        }

        $file = $files[$nombreCampo];

        // 1) Subida correcta
        $uploadOK = new \Laminas\Validator\File\UploadFile();
        if (!$uploadOK->isValid($file)) {
            $this->flashMessenger()->addErrorMessage("ERROR AL CARGAR EL ARCHIVO DE {$nombreCampo}");
            return ['ok' => false, 'sinArchivo' => false, 'nombre' => null];
        }

        // 2) Tamaño
        $sizeValidator = new \Laminas\Validator\File\Size(['min' => $tamanoMin, 'max' => $tamanoMax]);
        if (!$sizeValidator->isValid($file)) {
            $this->flashMessenger()->addErrorMessage(
                "EL ARCHIVO DE {$nombreCampo} ESTÁ FUERA DE RANGO. MÍN: {$tamanoMin} MÁX: {$tamanoMax}"
            );
            return ['ok' => false, 'sinArchivo' => false, 'nombre' => null];
        }

        // 3) Extensión
        $extValidator = new \Laminas\Validator\File\Extension(['extension' => $extensiones]);
        if (!$extValidator->isValid($file)) {
            $this->flashMessenger()->addErrorMessage(
                "EXTENSIÓN NO PERMITIDA PARA {$nombreCampo}. PERMITIDAS: " . implode(', ', $extensiones)
            );
            return ['ok' => false, 'sinArchivo' => false, 'nombre' => null];
        }

        // 4) Asegurar carpeta destino
        $rutaDestino = rtrim($rutaDestino, '/\\');
        if (!is_dir($rutaDestino)) {
            @mkdir($rutaDestino, 0775, true);
        }

        // 5) Mover archivo
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filter = new \Laminas\Filter\File\RenameUpload([
            'target'    => $rutaDestino . DIRECTORY_SEPARATOR . $prefijo . '_' . time() . '.' . $ext,
            'randomize' => true,
        ]);
        $upload = $filter->filter($file);

        if (!is_array($upload) || ($upload['error'] ?? 1) != 0) {
            $this->flashMessenger()->addErrorMessage("NO FUE POSIBLE SUBIR EL ARCHIVO DE {$nombreCampo}");
            return ['ok' => false, 'sinArchivo' => false, 'nombre' => null];
        }

        return ['ok' => true, 'sinArchivo' => false, 'nombre' => basename($upload['tmp_name'])];
    }

    //--------------------------------------------------------------------------
    // LISTADO
    //--------------------------------------------------------------------------
    public function indexAction()
    {
        return new ViewModel([
            'fetchAll' => $this->DAO->fetchAll(''),
        ]);
    }

    //--------------------------------------------------------------------------
    // REGISTRAR
    //--------------------------------------------------------------------------
    public function registrarAction()
    {
        $infosesion    = $this->getInfoSesion();
        $registradopor = $infosesion['login'];

        $form    = new OcdiForm('registrar');
        $request = $this->getRequest();

        if (!$request->isPost()) {
            return (new ViewModel(['form' => $form]))->setTerminal(true);
        }

        $ocdiOBJ = new Ocdi();
        $form->setInputFilter($ocdiOBJ->getInputFilter());
        $form->setData($request->getPost());

        if (!$form->isValid()) {
            $this->flashMessenger()->addErrorMessage('LA INFORMACION DE REGISTRO DEL ARCHIVO NO ES VALIDA');
            return ['form' => $form];
        }

        $files = $request->getFiles()->toArray();

        // --- Documento (obligatorio) ---
        $resDoc = $this->procesarArchivo(
            $files,
            'documento',
            ['pdf', 'docx', 'xlsx', 'pptx'],
            '250B',
            '25MB',
            $this->rutaArchivos,
            'OCDI'
        );

        if ($resDoc['sinArchivo']) {
            $this->flashMessenger()->addErrorMessage('EL ARCHIVO DE RESPALDO ADJUNTO NO FUE CARGADO CORRECTAMENTE.');
            return $this->redirect()->toUrl('index');
        }
        if (!$resDoc['ok']) {
            return $this->redirect()->toUrl('index');
        }

        // --- Imagen (opcional) ---
        $imagen = 'sinImagen.png'; // valor por defecto
        $resImg = $this->procesarArchivo(
            $files,
            'imagen',
            ['jpg', 'jpeg', 'png', 'gif'],
            '250B',
            '2MB',
            $this->rutaImagenes,
            'OCDI'
        );
        if ($resImg['ok']) {
            $imagen = $resImg['nombre'];
        }
        // Si la imagen falla, se ignora (es opcional) → queda 'sinImagen.png'

        // --- Persistencia ---
        $ocdiOBJ->exchangeArray($form->getData());
        $ocdiOBJ->setDocumento($resDoc['nombre']);
        $ocdiOBJ->setImagen($imagen);
        $ocdiOBJ->setEstado('Activo');
        $ocdiOBJ->setRegistradopor($registradopor);
        $ocdiOBJ->setModificadopor('');
        $ocdiOBJ->setFechahorareg(date('Y-m-d H:i:s'));
        $ocdiOBJ->setFechahoramod('0000-00-00 00:00:00');

        try {
            $this->DAO->registrar($ocdiOBJ);
            $this->flashMessenger()->addSuccessMessage('OCDI FUE REGISTRADO CON EXITO');
        } catch (\Exception $ex) {
            $this->registrarLogError('REGISTRAR OCDI', $ex);
            $this->flashMessenger()->addErrorMessage('SE HA PRESENTADO UN INCONVENIENTE! OCDI NO FUE REGISTRADO.');
        }

        return $this->redirect()->toUrl('index');
    }

    //--------------------------------------------------------------------------
    // EDITAR (solo datos del formulario, sin tocar archivos)
    //--------------------------------------------------------------------------
    public function editarAction()
    {
        $request = $this->getRequest();

        if (!$request->isPost()) {
            $idOcdi   = (int) $this->params()->fromQuery('idOcdi', 0);
            $infoOcdi = $this->DAO->getOcdiDetalle($idOcdi);
            $form     = new OcdiForm('editar');
            $form->setData($infoOcdi);

            return (new ViewModel(['form' => $form]))->setTerminal(true);
        }

        $form  = new OcdiForm('editar');
        $clOBJ = new Ocdi();
        $form->setInputFilter($clOBJ->getInputFilter());
        $form->setData($request->getPost());

        if (!$form->isValid()) {
            $this->flashMessenger()->addErrorMessage('LA INFORMACION A GUARDAR NO ES VALIDA');
            return $this->redirect()->toUrl('index');
        }

        try {
            $clOBJ->exchangeArray($form->getData());
            $infosesion     = $this->getInfoSesion();
            $modificadopor  = $infosesion['login'];
            $clOBJ->setModificadopor($modificadopor);
            $clOBJ->setFechahoramod(date('Y-m-d H:i:s'));

            $this->DAO->editar($clOBJ);
            $this->flashMessenger()->addSuccessMessage('LA INFORMACION DEL ARCHIVO FUE ACTUALIZADA');
        } catch (\Exception $ex) {
            $this->registrarLogError('ACTUALIZAR OCDI', $ex);
            $this->flashMessenger()->addErrorMessage('SE HA PRESENTADO UN INCONVENIENTE!.');
        }

        return $this->redirect()->toUrl('index');
    }

    //--------------------------------------------------------------------------
    // DETALLE
    //--------------------------------------------------------------------------
    public function detalleAction()
    {
        $idOcdi   = (int) $this->params()->fromQuery('idOcdi', 0);
        $infoOcdi = $this->DAO->getOcdiDetalle($idOcdi);

        return (new ViewModel(['form' => $infoOcdi]))->setTerminal(true);
    }

    //--------------------------------------------------------------------------
    // VER ARCHIVO (descarga / inline)
    //--------------------------------------------------------------------------
    public function verArchivoAction()
    {
        $idOcdi   = (int) $this->params()->fromRoute('id1', 0);
        $archivos = $this->DAO->getOcdiDetalle($idOcdi);
        $archivo  = $archivos['documento'];

        if ($archivo === '' || $archivo === null) {
            $this->flashMessenger()->addErrorMessage('NO FUE POSIBLE OBTENER EL ARCHIVO DESDE LA BASE DE DATOS');
            return $this->redirect()->toUrl('index');
        }

        $rutaCompleta = rtrim($this->rutaArchivos, '/\\') . DIRECTORY_SEPARATOR . $archivo;

        if (!is_file($rutaCompleta)) {
            $this->flashMessenger()->addErrorMessage('NO FUE POSIBLE ENCONTRAR EL ARCHIVO EN EL SERVIDOR');
            return $this->redirect()->toUrl('../../administracion/index');
        }

        $ext = strtolower(pathinfo($rutaCompleta, PATHINFO_EXTENSION));

        switch ($ext) {
            case 'pdf':
                header('Content-Type: application/pdf');
                break;
            case 'jpg':
            case 'jpeg':
                header('Content-Type: image/jpeg');
                break;
            case 'png':
                header('Content-Type: image/png');
                break;
            case 'gif':
                header('Content-Type: image/gif');
                break;
            default:
                header('Content-Type: application/octet-stream');
                header('Content-Disposition: attachment; filename="archivoRespaldo.' . $ext . '"');
                break;
        }

        readfile($rutaCompleta);
        exit; // evita que Laminas intente renderizar nada después
    }

    //--------------------------------------------------------------------------
    // SUBIR / REEMPLAZAR DOCUMENTO
    //--------------------------------------------------------------------------
    public function subirdocumentoAction()
    {
        $request = $this->getRequest();
        $id = $request->isPost()
            ? (int) $request->getPost('idRevistasOcdi', 0)
            : (int) $this->params()->fromQuery('idRevistasOcdi', 0);

        $ocdiOBJ = $this->DAO->getOcdi($id);
        if (!$ocdiOBJ) {
            $this->flashMessenger()->addErrorMessage('EL REGISTRO NO EXISTE');
            return $this->redirect()->toUrl('index');
        }

        $form = new OcdiForm('subirdocumento');
        $form->bind($ocdiOBJ);

        if (!$request->isPost()) {
            return (new ViewModel(['form' => $form]))->setTerminal(true);
        }

        $res = $this->procesarArchivo(
            $request->getFiles()->toArray(),
            'documento',
            ['pdf', 'docx', 'xlsx', 'pptx'],
            '250B',
            '25MB',
            $this->rutaArchivos,
            'OCDI'
        );

        if ($res['sinArchivo']) {
            $this->flashMessenger()->addErrorMessage('DEBE SELECCIONAR UN DOCUMENTO');
            return $this->redirect()->toUrl('index');
        }
        if (!$res['ok']) {
            return $this->redirect()->toUrl('index');
        }

        try {
            $session = $this->getInfoSesion();
            $ocdiOBJ->setDocumento($res['nombre']);
            $ocdiOBJ->setModificadopor($session['login']);
            $ocdiOBJ->setFechahoramod(date('Y-m-d H:i:s'));
            $this->DAO->editar($ocdiOBJ);

            $this->flashMessenger()->addSuccessMessage('EL DOCUMENTO FUE ACTUALIZADO CON EXITO');
        } catch (\Exception $ex) {
            $this->registrarLogError('ACTUALIZAR DOCUMENTO', $ex);
            $this->flashMessenger()->addErrorMessage('EL DOCUMENTO NO FUE ACTUALIZADO');
        }

        return $this->redirect()->toUrl('index');
    }

    //--------------------------------------------------------------------------
    // SUBIR / REEMPLAZAR IMAGEN
    //--------------------------------------------------------------------------
    public function subirimagenAction()
    {
        $request = $this->getRequest();
        $id = $request->isPost()
            ? (int) $request->getPost('idRevistasOcdi', 0)
            : (int) $this->params()->fromQuery('idRevistasOcdi', 0);

        $ocdiOBJ = $this->DAO->getOcdi($id);
        if (!$ocdiOBJ) {
            $this->flashMessenger()->addErrorMessage('EL REGISTRO NO EXISTE');
            return $this->redirect()->toUrl('index');
        }

        $form = new OcdiForm('subirimagen');
        $form->bind($ocdiOBJ);

        if (!$request->isPost()) {
            return (new ViewModel(['form' => $form]))->setTerminal(true);
        }

        $res = $this->procesarArchivo(
            $request->getFiles()->toArray(),
            'imagen',
            ['jpg', 'jpeg', 'png', 'gif'],
            '250B',
            '2MB',
            $this->rutaImagenes,   // ⬅️ carpeta de imágenes
            'OCDI'
        );

        if ($res['sinArchivo']) {
            $this->flashMessenger()->addErrorMessage('DEBE SELECCIONAR UNA IMAGEN');
            return $this->redirect()->toUrl('index');
        }
        if (!$res['ok']) {
            return $this->redirect()->toUrl('index');
        }

        try {
            $session = $this->getInfoSesion();
            $ocdiOBJ->setImagen($res['nombre']);   // ⬅️ setImagen, NO setDocumento
            $ocdiOBJ->setModificadopor($session['login']);
            $ocdiOBJ->setFechahoramod(date('Y-m-d H:i:s'));
            $this->DAO->editar($ocdiOBJ);

            $this->flashMessenger()->addSuccessMessage('LA IMAGEN FUE ACTUALIZADA CON EXITO');
        } catch (\Exception $ex) {
            $this->registrarLogError('ACTUALIZAR IMAGEN', $ex);
            $this->flashMessenger()->addErrorMessage('LA IMAGEN NO FUE ACTUALIZADA');
        }

        return $this->redirect()->toUrl('index');
    }
}
