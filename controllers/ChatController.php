<?php
require_once 'models/Mensaje.php';
require_once 'models/Pagina.php'; // Para el modal de "Ver Publicaciones"

class ChatController {
    
    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['user_id'])) { header("Location: index.php?c=Usuario&a=login"); exit; }
    }

    // VISTA PRINCIPAL (HTML)
    public function index() {
        $modelo = new Mensaje();
        $mi_id = $_SESSION['user_id'];
        $chat_con_id = $_GET['chat_con'] ?? null;

        // 1. Cargar lista de contactos
        $contactos = $modelo->obtenerContactos($mi_id);
        
        // 2. Si hay un chat seleccionado
        $usuario_chat = null;
        $paginas_usuario_chat = [];

        if ($chat_con_id) {
            // Verificar si el contacto está en la lista, si no, añadirlo
            $en_lista = false;
            foreach($contactos as $c) { if ($c->id == $chat_con_id) $en_lista = true; }
            
            if (!$en_lista) {
                $nuevo = $modelo->obtenerUsuario($chat_con_id);
                if ($nuevo) {
                    $nuevo->sin_leer = 0; // Objeto stdClass
                    array_unshift($contactos, $nuevo);
                }
            }
            
            // Obtener datos del usuario con quien hablo
            foreach($contactos as $c) { if ($c->id == $chat_con_id) $usuario_chat = $c; }

            // Obtener sus artículos (Para el modal "Ver Publicaciones")
            // Usamos un truco: reutilizamos el modelo Pagina, pero filtramos a mano o usamos el método dashboard
            $modPag = new Pagina();
            // Traemos sus páginas (usando el método dashboard que filtra por usuario)
            // false = no soy admin (para que filtre por SU id), 0, 50 = límite
            // NOTA: Para no complicar el modelo Pagina, reutilizamos obtenerParaDashboard
            // OJO: Hay que llamar a obtenerParaDashboard pasando el ID del OTRO usuario.
            // Como obtenerParaDashboard usa user_id filtro, instanciaremos un método ad-hoc o query directa.
            // Para mantener MVC puro, usaremos la conexión global aquí o añadimos método en Pagina.
            // OPCIÓN RÁPIDA: Query directa aquí (excepción aceptable para no tocar Pagina.php otra vez)
            global $pdo;
            $stmtP = $pdo->prepare("SELECT * FROM paginas WHERE user_id = ? AND estado = 'publicado' ORDER BY fecha_creacion DESC");
            $stmtP->execute([$chat_con_id]);
            $paginas_usuario_chat = $stmtP->fetchAll();
        }

        require_once 'views/chat/index.php';
    }

    // AJAX: CARGAR MENSAJES (JSON/HTML)
    public function ajax_load() {
        $this->verificarToken();
        
        $modelo = new Mensaje();
        $chat_con_id = $_GET['chat_con'] ?? 0;
        $mi_id = $_SESSION['user_id'];

        $mensajes = $modelo->obtenerConversacion($mi_id, $chat_con_id);

        // Renderizamos solo los <div> de los mensajes
        if ($mensajes) {
            foreach($mensajes as $msg) {
                $es_mio = ($msg->remitente_id == $mi_id);
                $clase = $es_mio ? 'message-mine' : 'message-other';
                $checks = '';
                if($es_mio) {
                    $color = $msg->leido ? '#53bdeb' : '#999'; 
                    $icono = $msg->leido ? '✓✓' : '✓';
                    $checks = "<span style='font-size: 0.8rem; color: $color; margin-left: 5px;'>$icono</span>";
                }
                echo "<div class='message $clase'>" . nl2br(htmlspecialchars($msg->mensaje)) . "
                        <div class='d-flex justify-content-end align-items-center mt-1'>
                            <span class='msg-time'>" . date('H:i', strtotime($msg->fecha)) . "</span>$checks
                        </div></div>";
            }
        } else {
            echo "<div class='text-center text-muted mt-5'><small>No hay mensajes. ¡Saluda! 👋</small></div>";
        }
        exit; // Importante cortar aquí para no cargar el resto de la web
    }

    // AJAX: ENVIAR MENSAJE
    public function ajax_send() {
        $this->verificarToken();
        
        $modelo = new Mensaje();
        $chat_con_id = $_GET['chat_con'] ?? 0;
        $mensaje = trim($_POST['mensaje'] ?? '');
        
        if (!empty($mensaje) && $chat_con_id) {
            $modelo->enviar($_SESSION['user_id'], $chat_con_id, $mensaje);
        }
        exit;
    }

    private function verificarToken() {
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            die("Error de seguridad: Token inválido");
        }
    }
}
?>