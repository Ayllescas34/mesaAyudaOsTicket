<?php
// Carga el archivo principal de osTicket
require_once('../main.inc.php');

// Verifica que INCLUDE_DIR esté definido (previene acceso no autorizado)
if(!defined('INCLUDE_DIR')) die('Fatal Error. Kwaheri!');

// Configura los textos traducibles para el usuario
TextDomain::configureForUser();

// Incluye las clases necesarias de osTicket
require_once(INCLUDE_DIR.'class.staff.php');
require_once(INCLUDE_DIR.'class.csrf.php');

// Incluye nuestro archivo de autenticación personalizado
require_once 'auth_externo.php';

// Busca si hay un banner o mensaje personalizado en la pantalla de login
$content = Page::lookupByType('banner-staff');

// Recupera al usuario si ya está logueado como staff
$thisstaff = StaffAuthenticationBackend::getUser();

// Recupera destino de redirección tras login y mensaje previo de error si los hay
$dest = $_SESSION['_staff']['auth']['dest'] ?? null;
$msg = $_SESSION['_staff']['auth']['msg'] ?? null;

// Si no hay mensaje previo, toma el mensaje del banner
$msg = $msg ?: ($content ? $content->getLocalName() : __('Authentication Required'));

// Verifica que el destino no sea login o ajax, si no lo es, lo usa, si no, lo manda a index
$dest = ($dest && (!strstr($dest,'login.php') && !strstr($dest,'ajax.php'))) ? $dest : 'index.php';

// Para mostrar opción de restablecer contraseña si es necesario
$show_reset = false;

// Si se envió el formulario (método POST)
if ($_POST) {
    // Verifica si la petición fue AJAX
    $json = isset($_POST['ajax']) && $_POST['ajax'];

    // Función para enviar respuestas con código y mensaje (tanto para AJAX como normales)
    $respond = function($code, $message) use ($json, $ost) {
        if ($json) {
            $payload = is_array($message) ? $message : array('message' => $message);
            $payload['status'] = (int) $code;
            Http::response(200, JSONDataEncoder::encode($payload), 'application/json');
        } else {
            if (is_array($message)) $message = $message['message'];
            Http::response($code, $message);
        }
    };

    // Función para redirigir (normal o AJAX)
    $redirect = function($url) use ($json) {
        if ($json)
            Http::response(200, JsonDataEncoder::encode(['status' => 302, 'redirect' => $url]), 'application/json');
        else
            Http::redirect($url);
    };

    // Verifica el token CSRF como medida de seguridad
    if (!$ost->checkCSRFToken()) {
        $_SESSION['_staff']['auth']['msg'] = __('Valid CSRF Token Required');
        $redirect($_SERVER['REQUEST_URI']);
    }

    // Si el formulario tiene el campo 'userid', procede a autenticar
    if (isset($_POST['userid'])) {
        // Captura y limpia usuario y contraseña
        $username = trim($_POST['userid']);
        $password = trim($_POST['passwd']);

        // Llama a la función de autenticación personalizada
        $resultado = loginExterno($username, $password, 'osTicket');

        // Si la autenticación fue exitosa
        if ($resultado['autenticado']) {
            // Si es admin, intenta loguear como staff
            if ($resultado['rol'] === 'admin') {
                if ($staff = Staff::lookup(['username' => $username])) {
                    StaffSession::start($staff->getId(), $password); // Inicia sesión como staff
                    $redirect($dest);
                } else {
                    $msg = "Usuario válido pero no registrado como staff en osTicket.";
                }
            }
            // Si es cliente, inicia sesión como usuario
            elseif ($resultado['rol'] === 'cliente') {
                if ($user = Client::lookup(['email' => $username])) {
                    UserAuthenticationBackend::signOn($user);
                    $redirect('index.php');
                } else {
                    $msg = "Usuario válido pero no registrado como cliente en osTicket.";
                }
            }
            // Rol no permitido
            else {
                $msg = "Tu rol no tiene permisos para entrar a osTicket.";
            }
        }
        // Si la autenticación falló, muestra mensaje
        else {
            $msg = isset($resultado['error']) ? $resultado['error'] : __('Credenciales inválidas');
        }

        // Habilita opción para restablecer contraseña
        $show_reset = true;

        // Si fue AJAX, responde con mensaje
        if ($json) {
            $respond(401, ['message' => $msg, 'show_reset' => $show_reset]);
        } else {
            $ost->getCSRF()->rotate(); // Gira el token CSRF
        }
    }

    // Caso especial: autenticación de segundo factor (2FA)
    elseif (!strcmp($_POST['do'], '2fa') && $thisstaff && $thisstaff->is2FAPending() && ($auth=$thisstaff->get2FABackend())) {
        try {
            $form = $auth->getInputForm($_POST);
            if ($form->isValid() && $auth->validate($form, $thisstaff))
                $redirect($dest);
        } catch (ExpiredOTP $ex) {
            $thisstaff->logOut();
            $redirect('login.php');
        }

        $msg = __('Invalid Code');
        if ($json) {
            $respond(401, ['message' => $msg]);
        } else {
            $ost->getCSRF()->rotate();
        }
    }
}

// Otras rutas: autenticación externa vía backend
elseif (isset($_GET['do'])) {
    switch ($_GET['do']) {
        case 'ext':
            if ($bk = StaffAuthenticationBackend::getBackend($_GET['bk']))
                $bk->triggerAuth();
    }
    Http::redirect('login.php');
}

// Si ya hay sesión válida como staff, redirige directamente
elseif ($thisstaff && $thisstaff->isValid()) {
    Http::redirect($dest);
}

// Si aún no hay sesión pero se puede iniciar sesión automáticamente (SSO)
elseif (!$thisstaff || !($thisstaff->getId() || $thisstaff->isValid())) {
    if (($user = StaffAuthenticationBackend::processSignOn($errors, false)) && ($user instanceof StaffSession)) {
        Http::redirect($dest);
    } else if (isset($_SESSION['_staff']['auth']['msg'])) {
        $msg = $_SESSION['_staff']['auth']['msg'];
    }
}

// Renderiza el formulario de login (plantilla visual)
define("OSTSCPINC",TRUE);
include_once(INCLUDE_DIR.'staff/login.tpl.php');
