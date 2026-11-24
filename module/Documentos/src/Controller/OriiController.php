<?php

declare(strict_types=1);

namespace Documentos\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\Authentication\AuthenticationService;
use Laminas\View\Model\ViewModel;
use Laminas\View\Model\JsonModel;
use Documentos\Modelo\DAO\OriiDAO;
use Documentos\Formularios\OriiForm;
use Documentos\Formularios\OriidocumentosForm;
use Documentos\Modelo\Entidades\Orii;
use Documentos\Modelo\Entidades\Oriidocumentos;

class OriiController extends AbstractActionController
{
    private $DAO;
    private $rutaLog = './public/log/';
    private $rutaArchivos;

    public function __construct(OriiDAO $dao)
    {
        $this->DAO = $dao;
        // 🔧 PRODUCCIÓN
        $this->rutaArchivos = '/var/www/html/newportal/archivos/orii/';

        // 💻 DESARROLLO LOCAL (comenta la línea de arriba y descomenta esta)
        //$this->rutaArchivos = './../newportal/archivos/orii/';
    }

    //==========================================================================
    // HELPERS
    //==========================================================================
    private function getInfoSesion(): array
    {
        $info = [
            'login'             => 'SIN INICIO DE SESION',
            'idEmpleadoCliente' => 0,
            'idUsuario'         => 0,
        ];
        $auth = new AuthenticationService();
        if ($auth->hasIdentity()) {
            $info['login']             = $auth->getIdentity()->login;
            $info['idEmpleadoCliente'] = $auth->getIdentity()->idEmpleadoCliente;
            $info['idUsuario']         = $auth->getIdentity()->idUsuario;
        }
        return $info;
    }

    private function registrarLogError(string $accion, \Throwable $ex): void
    {
        $msg = "\n" . date('Y-m-d H:i:s') . " {$accion} - OriiController\n"
            . $ex->getMessage()
            . "\n-----------------------------------------------------------------------\n";
        @file_put_contents($this->rutaLog . 'gestorportal.log', $msg, FILE_APPEND);
    }

    /**
     * Procesa una subida de archivo con validaciones.
     * @return string|null|false  nombre del archivo, null si no se envió, false si error
     */
    private function procesarArchivo(
        array $files,
        string $campo,
        array $extensiones,
        string $tamanoMin,
        string $tamanoMax,
        string $prefijo
    ) {
        if (!isset($files[$campo]) || $files[$campo]['error'] === UPLOAD_ERR_NO_FILE) {
            return null;
        }

        $uploadOK = new \Laminas\Validator\File\UploadFile();
        if (!$uploadOK->isValid($files[$campo])) {
            $this->flashMessenger()->addErrorMessage('ERROR AL CARGAR EL ARCHIVO');
            return false;
        }

        $sizeValidator = new \Laminas\Validator\File\Size([
            'min' => $tamanoMin,
            'max' => $tamanoMax,
        ]);
        if (!$sizeValidator->isValid($files[$campo])) {
            $this->flashMessenger()->addErrorMessage(
                "ARCHIVO FUERA DE RANGO ({$tamanoMin} - {$tamanoMax})"
            );
            return false;
        }

        $extValidator = new \Laminas\Validator\File\Extension([
            'extension' => $extensiones,
        ]);
        if (!$extValidator->isValid($files[$campo])) {
            $this->flashMessenger()->addErrorMessage('EXTENSIÓN NO PERMITIDA');
            return false;
        }

        // Crear carpeta si no existe (útil en el primer despliegue)
        if (!is_dir($this->rutaArchivos)) {
            @mkdir($this->rutaArchivos, 0775, true);
        }

        $ext = strtolower(pathinfo($files[$campo]['name'], PATHINFO_EXTENSION));
        $filter = new \Laminas\Filter\File\RenameUpload([
            'target'    => $this->rutaArchivos . "{$prefijo}_" . time() . '.' . $ext,
            'randomize' => true,
        ]);
        $upload = $filter->filter($files[$campo]);

        if (!empty($upload['error'])) {
            $this->flashMessenger()->addErrorMessage('NO FUE POSIBLE SUBIR EL ARCHIVO AL SERVIDOR');
            return false;
        }
        return basename($upload['tmp_name']);
    }

    private function getListaInstituciones(): array
    {
        $lista = [];
        foreach ($this->DAO->getInstituciones() as $in) {
            $lista[(int) $in['idInstituto']] =
                $in['institucion'] . ' (' . $in['pais'] . ' - ' . $in['ciudad'] . ')';
        }
        return $lista;
    }

    //==========================================================================
    // INDEX
    //==========================================================================
    public function indexAction()
    {
        return new ViewModel([
            'fetchAll' => $this->DAO->fetchAll(''),
        ]);
    }

    //==========================================================================
    // REGISTRAR
    //==========================================================================
    public function registrarAction()
    {
        $form = new OriiForm('registrar', $this->getListaInstituciones());
        $request = $this->getRequest();

        if (!$request->isPost()) {
            return (new ViewModel(['form' => $form]))->setTerminal(true);
        }

        $oriiOBJ = new Orii();
        $form->setInputFilter($oriiOBJ->getInputFilter());
        $form->setData($request->getPost());

        if (!$form->isValid()) {
            $this->flashMessenger()->addErrorMessage(
                'INFORMACIÓN NO VÁLIDA: ' . json_encode($form->getMessages(), JSON_UNESCAPED_UNICODE)
            );
            return $this->redirect()->toUrl('index');
        }

        $infosesion = $this->getInfoSesion();
        $oriiOBJ->exchangeArray($form->getData());
        $oriiOBJ->setEstado('Vigente');
        $oriiOBJ->setRegistradopor($infosesion['login']);
        $oriiOBJ->setModificadopor('');
        $oriiOBJ->setFechahorareg(date('Y-m-d H:i:s'));
        $oriiOBJ->setFechahoramod('0000-00-00 00:00:00');

        try {
            $this->DAO->registrar($oriiOBJ);
            $this->flashMessenger()->addSuccessMessage('LA ORII FUE REGISTRADA');
        } catch (\Throwable $ex) {
            $this->registrarLogError('REGISTRAR ORII', $ex);
            $this->flashMessenger()->addErrorMessage('LA ORII NO FUE REGISTRADA');
        }
        return $this->redirect()->toUrl('index');
    }

    //==========================================================================
    // EDITAR
    //==========================================================================
    public function editarAction()
    {
        $request = $this->getRequest();
        $idOrii = $request->isPost()
            ? (int) $request->getPost('idOrii', 0)
            : (int) $this->params()->fromQuery('idOrii', 0);

        if ($idOrii <= 0) {
            $this->flashMessenger()->addErrorMessage('ID DE ORII INVÁLIDO');
            return $this->redirect()->toUrl('index');
        }

        $infoOrii = $this->DAO->getOriiDetalle($idOrii);
        if (!$infoOrii) {
            $this->flashMessenger()->addErrorMessage('LA ORII NO EXISTE');
            return $this->redirect()->toUrl('index');
        }

        $form = new OriiForm('editar', $this->getListaInstituciones());
        $form->setData($infoOrii);

        if (!$request->isPost()) {
            return (new ViewModel(['form' => $form]))->setTerminal(true);
        }

        $oriiOBJ = new Orii();
        $form->setInputFilter($oriiOBJ->getInputFilter());
        $form->setData($request->getPost());

        if (!$form->isValid()) {
            $this->flashMessenger()->addErrorMessage(
                'INFORMACIÓN NO VÁLIDA: ' . json_encode($form->getMessages(), JSON_UNESCAPED_UNICODE)
            );
            return $this->redirect()->toUrl('index');
        }

        $infosesion = $this->getInfoSesion();
        $oriiOBJ->exchangeArray($form->getData());
        $oriiOBJ->setIdOrii($idOrii);
        $oriiOBJ->setModificadopor($infosesion['login']);
        $oriiOBJ->setFechahoramod(date('Y-m-d H:i:s'));

        try {
            $this->DAO->editar($oriiOBJ);
            $this->flashMessenger()->addSuccessMessage('LA ORII FUE EDITADA');
        } catch (\Throwable $ex) {
            $this->registrarLogError('EDITAR ORII', $ex);
            $this->flashMessenger()->addErrorMessage('LA ORII NO FUE EDITADA');
        }
        return $this->redirect()->toUrl('index');
    }

    //==========================================================================
    // DETALLE
    //==========================================================================
    public function detalleAction()
    {
        $idOrii = (int) $this->params()->fromQuery('idOrii', 0);
        $infoOrii = $this->DAO->getOriiDetalle($idOrii);
        if (!$infoOrii) {
            $this->flashMessenger()->addErrorMessage('LA ORII NO EXISTE');
            return $this->redirect()->toUrl('index');
        }
        return (new ViewModel([
            'form'           => $infoOrii,
            'orriDocumentos' => $this->DAO->getOriiDocumentos($idOrii),
        ]))->setTerminal(true);
    }

    //==========================================================================
    // DESACTIVAR (estado = Cancelado)
    //==========================================================================
    public function desactivarAction()
    {
        $request = $this->getRequest();

        if (!$request->isPost()) {
            $idOrii = (int) $this->params()->fromQuery('idOrii', 0);
            $info = $this->DAO->getOriiDetalle($idOrii);
            if (!$info) {
                $this->flashMessenger()->addErrorMessage('LA ORII NO EXISTE');
                return $this->redirect()->toUrl('index');
            }
            $form = new OriiForm('desactivar');
            $form->setData($info);
            return (new ViewModel(['form' => $form]))->setTerminal(true);
        }

        $idOrii = (int) $request->getPost('idOrii', 0);
        if ($idOrii <= 0) {
            $this->flashMessenger()->addErrorMessage('ID DE ORII INVÁLIDO');
            return $this->redirect()->toUrl('index');
        }

        try {
            $session = $this->getInfoSesion();
            $oriiOBJ = new Orii();
            $oriiOBJ->setIdOrii($idOrii);
            $oriiOBJ->setModificadopor($session['login']);
            $oriiOBJ->setFechahoramod(date('Y-m-d H:i:s'));
            $this->DAO->eliminarActivar($oriiOBJ, 'Eliminado');
            $this->flashMessenger()->addSuccessMessage('LA ORII FUE DESACTIVADA');
        } catch (\Throwable $ex) {
            $this->registrarLogError('DESACTIVAR ORII', $ex);
            $this->flashMessenger()->addErrorMessage('LA ORII NO FUE DESACTIVADA');
        }
        return $this->redirect()->toUrl('index');
    }

    //==========================================================================
    // ACTIVAR (estado = Vigente)
    //==========================================================================
    public function activarAction()
    {
        $request = $this->getRequest();

        if (!$request->isPost()) {
            $idOrii = (int) $this->params()->fromQuery('idOrii', 0);
            $info = $this->DAO->getOriiDetalle($idOrii);
            if (!$info) {
                $this->flashMessenger()->addErrorMessage('LA ORII NO EXISTE');
                return $this->redirect()->toUrl('index');
            }
            $form = new OriiForm('activar');
            $form->setData($info);
            return (new ViewModel(['form' => $form]))->setTerminal(true);
        }

        $idOrii = (int) $request->getPost('idOrii', 0);
        if ($idOrii <= 0) {
            $this->flashMessenger()->addErrorMessage('ID DE ORII INVÁLIDO');
            return $this->redirect()->toUrl('index');
        }

        try {
            $session = $this->getInfoSesion();
            $oriiOBJ = new Orii();
            $oriiOBJ->setIdOrii($idOrii);
            $oriiOBJ->setModificadopor($session['login']);
            $oriiOBJ->setFechahoramod(date('Y-m-d H:i:s'));
            $this->DAO->eliminarActivar($oriiOBJ, 'Vigente');
            $this->flashMessenger()->addSuccessMessage('LA ORII FUE ACTIVADA');
        } catch (\Throwable $ex) {
            $this->registrarLogError('ACTIVAR ORII', $ex);
            $this->flashMessenger()->addErrorMessage('LA ORII NO FUE ACTIVADA');
        }
        return $this->redirect()->toUrl('index');
    }

    //==========================================================================
    // ELIMINAR DEFINITIVAMENTE
    //==========================================================================
    public function eliminarAction()
    {
        $request = $this->getRequest();

        if (!$request->isPost()) {
            $idOrii = (int) $this->params()->fromQuery('idOrii', 0);
            $info = $this->DAO->getOriiDetalle($idOrii);
            if (!$info) {
                $this->flashMessenger()->addErrorMessage('LA ORII NO EXISTE');
                return $this->redirect()->toUrl('index');
            }
            $form = new OriiForm('eliminar');
            $form->setData($info);
            return (new ViewModel([
                'form'            => $form,
                'tieneDocumentos' => $this->DAO->tieneDocumentos($idOrii),
            ]))->setTerminal(true);
        }

        $idOrii = (int) $request->getPost('idOrii', 0);
        if ($idOrii <= 0) {
            $this->flashMessenger()->addErrorMessage('ID DE ORII INVÁLIDO');
            return $this->redirect()->toUrl('index');
        }

        if ($this->DAO->tieneDocumentos($idOrii)) {
            $this->flashMessenger()->addErrorMessage(
                'NO SE PUEDE ELIMINAR: LA ORII TIENE DOCUMENTOS ASOCIADOS.'
            );
            return $this->redirect()->toUrl('index');
        }

        try {
            // Limpiar archivos huérfanos del disco
            foreach ($this->DAO->getOriiDocumentos($idOrii, true) as $doc) {
                $ruta = $this->rutaArchivos . $doc['documento'];
                if (is_file($ruta)) {
                    @unlink($ruta);
                }
            }
            $this->DAO->borrarOrii($idOrii);
            $this->flashMessenger()->addSuccessMessage('LA ORII FUE ELIMINADA DEFINITIVAMENTE');
        } catch (\Throwable $ex) {
            $this->registrarLogError('ELIMINAR ORII', $ex);
            $this->flashMessenger()->addErrorMessage('LA ORII NO FUE ELIMINADA');
        }
        return $this->redirect()->toUrl('index');
    }

    //==========================================================================
    // SUBIR DOCUMENTO PDF
    //==========================================================================
    public function subirdocumentoAction()
    {
        $request = $this->getRequest();
        $idOrii = $request->isPost()
            ? (int) $request->getPost('idOrii', 0)
            : (int) $this->params()->fromQuery('idOrii', 0);

        $oriiDetalle = $this->DAO->getOriiDetalle($idOrii);
        if (!$oriiDetalle) {
            $this->flashMessenger()->addErrorMessage('LA ORII NO EXISTE');
            return $this->redirect()->toUrl('index');
        }

        $form = new OriidocumentosForm('subirdocumento');

        // --- GET: mostrar formulario ---
        if (!$request->isPost()) {
            return (new ViewModel([
                'form'           => $form,
                'orriDocumentos' => $this->DAO->getOriiDocumentos($idOrii),
                'oriiDetalle'    => $oriiDetalle,
            ]))->setTerminal(true);
        }

        // --- 1) Validar SOLO texto (sin archivos) ---
        $oriiDocOBJ = new Oriidocumentos();
        $form->setInputFilter($oriiDocOBJ->getInputFilter());
        $form->setData($request->getPost());

        if (!$form->isValid()) {
            $this->flashMessenger()->addErrorMessage(
                'INFORMACIÓN NO VÁLIDA: ' . json_encode($form->getMessages(), JSON_UNESCAPED_UNICODE)
            );
            return $this->redirect()->toUrl('index');
        }

        // --- 2) Procesar el archivo por separado ---
        $files = $request->getFiles()->toArray();
        $nombreArchivo = $this->procesarArchivo(
            $files,
            'documento',
            ['pdf'],
            '250B',
            '25MB',
            'ORII_' . $idOrii
        );

        if ($nombreArchivo === null) {
            $this->flashMessenger()->addErrorMessage('DEBE SELECCIONAR UN ARCHIVO PDF');
            return $this->redirect()->toUrl('index');
        }
        if ($nombreArchivo === false) {
            return $this->redirect()->toUrl('index');
        }

        // --- 3) Armar la entidad con valores 100% controlados ---
        $infosesion = $this->getInfoSesion();

        $datos = $form->getData();
        unset($datos['documento'], $datos['id_documentos_orii']);  // limpiar

        $oriiDocOBJ->exchangeArray($datos);
        $oriiDocOBJ->setId_documentos_orii(null);          // auto_increment
        $oriiDocOBJ->setIdOrii((int) $idOrii);
        $oriiDocOBJ->setDocumento((string) $nombreArchivo);
        $oriiDocOBJ->setEstado_documento('Activo');
        $oriiDocOBJ->setRegistradopor_documento((string) $infosesion['login']);
        $oriiDocOBJ->setFechahorareg_documento(date('Y-m-d H:i:s'));

        // --- 4) Insertar con logging del error real ---
        try {
            $this->DAO->registrarDocumento($oriiDocOBJ);
            $this->flashMessenger()->addSuccessMessage('EL DOCUMENTO FUE REGISTRADO');
        } catch (\Throwable $ex) {
            $this->registrarLogError('REGISTRAR DOCUMENTO ORII', $ex);
            // Si falló el INSERT, borrar el PDF para no dejar huérfano en disco
            $ruta = $this->rutaArchivos . $nombreArchivo;
            if (is_file($ruta)) {
                @unlink($ruta);
            }
            // ✅ Mostrar el error REAL para poder diagnosticar
            $this->flashMessenger()->addErrorMessage(
                'EL DOCUMENTO NO FUE REGISTRADO: ' . $ex->getMessage()
            );
        }
        return $this->redirect()->toUrl('index');
    }

    //==========================================================================
    // VER ARCHIVO (por id_documentos_orii)
    //==========================================================================
    public function verArchivoAction()
    {
        $id_documento = (int) $this->params()->fromRoute('id1', 0);
        $doc = $this->DAO->getDocumento($id_documento);

        if (!$doc) {
            $this->flashMessenger()->addErrorMessage('EL DOCUMENTO NO EXISTE');
            return $this->redirect()->toUrl('index');
        }

        $archivo = $this->rutaArchivos . $doc['documento'];
        if (!is_file($archivo)) {
            $this->flashMessenger()->addErrorMessage('EL ARCHIVO NO EXISTE EN EL SERVIDOR');
            return $this->redirect()->toUrl('index');
        }

        $ext  = strtolower(pathinfo($archivo, PATHINFO_EXTENSION));
        $mime = match ($ext) {
            'pdf'         => 'application/pdf',
            'jpg', 'jpeg' => 'image/jpeg',
            'png'         => 'image/png',
            default       => 'application/octet-stream',
        };

        $response = $this->getResponse();
        $response->getHeaders()
            ->addHeaderLine('Content-Type', $mime)
            ->addHeaderLine('Content-Disposition', 'inline; filename="' . basename($doc['documento']) . '"')
            ->addHeaderLine('Cache-Control', 'no-store');
        $response->setContent(file_get_contents($archivo));
        return $response;
    }

    //==========================================================================
    // ELIMINAR DOCUMENTO
    //==========================================================================
    public function eliminardocAction()
    {
        $id_documento = (int) $this->params()->fromQuery('id_documentos_orii', 0);
        if ($id_documento <= 0) {
            return new JsonModel(['success' => false, 'message' => 'ID inválido']);
        }

        try {
            $doc = $this->DAO->getDocumento($id_documento);
            if (!$doc) {
                return new JsonModel(['success' => false, 'message' => 'El documento no existe']);
            }

            $ruta = $this->rutaArchivos . $doc['documento'];
            if (is_file($ruta)) {
                @unlink($ruta);
            }
            $this->DAO->borrarDocumento($id_documento);

            $this->flashMessenger()->addSuccessMessage('EL DOCUMENTO FUE ELIMINADO');
            return new JsonModel(['success' => true]);
        } catch (\Throwable $ex) {
            $this->registrarLogError('ELIMINAR DOCUMENTO ORII', $ex);
            return new JsonModel(['success' => false, 'message' => 'Error al eliminar']);
        }
    }
}
