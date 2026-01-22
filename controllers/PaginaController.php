<?php
require_once 'models/Pagina.php';

class PaginaController {
    
    public function index() {
        $modelo = new Pagina();
        $home = $modelo->obtenerInicio();
        require_once 'views/paginas/inicio.php';
    }

    public function blog() {
        $modelo = new Pagina();
        $filtros = [
            'cat' => $_GET['cat'] ?? null,
            'busqueda' => $_GET['busqueda'] ?? null
        ];
        $pagina_actual = $_GET['pag'] ?? 1;
        $limite = 5;
        $inicio = ($pagina_actual - 1) * $limite;
        $usuario_id = $_SESSION['user_id'] ?? 0;

        $entradas = $modelo->obtenerListado($filtros, $inicio, $limite, $usuario_id);
        $total_registros = $modelo->contarTotal($filtros);

        require_once 'views/paginas/listado.php';
    }

    public function ver() {
        if (!isset($_GET['slug'])) { header("Location: index.php"); exit; }

        $modelo = new Pagina();
        $pagina = $modelo->obtenerPorSlug($_GET['slug']);

        if (!$pagina) { echo "Error 404"; return; }

        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION["visto_".$pagina->id])) {
            $modelo->sumarVisita($pagina->id);
            $_SESSION["visto_".$pagina->id] = true;
            $pagina->visitas++;
        }

        $comentarios = $modelo->obtenerComentarios($pagina->id);
        $total_comentarios = count($comentarios);
        $mi_id = $_SESSION['user_id'] ?? 0;

        require_once 'views/paginas/detalle.php';
    }

    public function comentar() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if (session_status() === PHP_SESSION_NONE) session_start();
            if (!isset($_SESSION['user_id'])) { header("Location: index.php?c=Usuario&a=login"); exit; }
            if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) die("Error de seguridad");

            $modelo = new Pagina();
            $modelo->guardarComentario($_POST['pagina_id'], $_SESSION['user_id'], htmlspecialchars(trim($_POST['comentario'])));
            header("Location: index.php?c=Pagina&a=ver&slug=" . $_POST['slug_retorno']);
        }
    }

    // --- NUEVO: FUNCIÓN LIKE INTEGRADA ---
    public function like() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            header('Content-Type: application/json');
            
            if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
                echo json_encode(['status'=>'error', 'msg'=>'Token inválido']); exit;
            }
            if (!isset($_SESSION['user_id'])) {
                echo json_encode(['status'=>'error', 'msg'=>'No logueado']); exit;
            }

            $pagina_id = $_POST['pagina_id'];
            $user_id = $_SESSION['user_id'];
            global $pdo; 

            $stmt = $pdo->prepare("SELECT id FROM likes WHERE user_id = ? AND pagina_id = ?");
            $stmt->execute([$user_id, $pagina_id]);
            $existe = $stmt->fetch();

            if ($existe) {
                $pdo->prepare("DELETE FROM likes WHERE id = ?")->execute([$existe->id]);
            } else {
                $pdo->prepare("INSERT INTO likes (user_id, pagina_id) VALUES (?, ?)")->execute([$user_id, $pagina_id]);
            }

            $stmtCount = $pdo->prepare("SELECT COUNT(*) FROM likes WHERE pagina_id = ?");
            $stmtCount->execute([$pagina_id]);
            $total = $stmtCount->fetchColumn();

            echo json_encode(['status'=>'ok', 'total'=>$total]);
            exit;
        }
    }
}
?>