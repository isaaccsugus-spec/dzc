<?php
require_once 'models/Pagina.php';

class DashboardController {

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['user_id'])) {
            header("Location: index.php?c=Usuario&a=login");
            exit;
        }
    }

    // DASHBOARD PRINCIPAL
    public function index() {
        $modelo = new Pagina();
        $user_id = $_SESSION['user_id'];
        $soy_admin = ($_SESSION['rol'] === 'admin');

        // 1. Recoger Filtros
        $busqueda = $_GET['busqueda'] ?? null;
        $filtro_cat = $_GET['cat'] ?? null;

        // 2. Paginación
        $pagina_actual = isset($_GET['pag']) ? (int)$_GET['pag'] : 1;
        if ($pagina_actual < 1) $pagina_actual = 1;
        $limite = 10;
        $inicio = ($pagina_actual - 1) * $limite;

        // 3. Obtener Datos (Pasando filtros)
        $paginas_tabla = $modelo->obtenerParaDashboard($user_id, $soy_admin, $inicio, $limite, $busqueda, $filtro_cat);
        $total_items = $modelo->contarDashboard($user_id, $soy_admin, $busqueda, $filtro_cat);
        $total_hojas = ceil($total_items / $limite);

        // 4. Datos para Gráficos y Selectores
        $stats = $modelo->obtenerEstadisticas($user_id, $soy_admin);
        $topPosts = $modelo->obtenerTopPosts($user_id, $soy_admin);
        $catsGrafico = $modelo->obtenerCategoriasGrafico($user_id, $soy_admin);
        $todas_categorias = $modelo->obtenerCategorias(); // Para el dropdown del filtro

        // Preparar JS
        $labels_paginas = []; $data_visitas = []; $data_likes = [];
        foreach($topPosts as $p) {
            $labels_paginas[] = strlen($p->titulo) > 12 ? substr($p->titulo, 0, 12) . '...' : $p->titulo;
            $data_visitas[] = $p->visitas;
            $data_likes[] = $p->total_likes;
        }
        $labels_cats = []; $data_cats = [];
        foreach($catsGrafico as $c) {
            $labels_cats[] = $c->nombre ?? 'Sin Cat';
            $data_cats[] = $c->cantidad;
        }

        require_once 'views/dashboard/panel.php';
    }

    // ... (Resto de métodos: crear, editar, borrar... MANTENLOS IGUAL, INCLUYENDO _subirImagen) ...
    
    public function crear() {
        $modelo = new Pagina();
        $categorias = $modelo->obtenerCategorias();
        $error = "";

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) die("Token inválido");

            $titulo = trim($_POST['titulo']);
            $res_img = $this->_subirImagen($_FILES['imagen']);

            if (!$res_img['ok']) {
                $error = $res_img['error'];
            } else {
                $slug = strtolower(trim(preg_replace('/[^a-z0-9-]+/', '-', $titulo), '-'));
                $base = $slug; $c=1;
                while($modelo->obtenerPorSlug($slug)) { $slug = $base . '-' . $c++; }

                $datos = [
                    'user_id' => $_SESSION['user_id'], 'titulo' => $titulo, 'contenido' => $_POST['contenido'],
                    'slug' => $slug, 'estado' => $_POST['estado'], 'categoria_id' => $_POST['categoria_id'], 'imagen' => $res_img['nombre']
                ];
                
                if ($modelo->crear($datos)) {
                    $_SESSION['flash'] = "¡Página creada!";
                    header("Location: index.php?c=Dashboard&a=index"); exit;
                } else { $error = "Error al guardar."; }
            }
        }
        require_once 'views/dashboard/crear.php';
    }

    public function editar() {
        if (!isset($_GET['id'])) { header("Location: index.php?c=Dashboard&a=index"); exit; }
        $modelo = new Pagina();
        $id = $_GET['id'];
        $user_id = $_SESSION['user_id'];
        $soy_admin = ($_SESSION['rol'] === 'admin');

        $pagina = $modelo->obtenerPorId($id, $user_id, $soy_admin);
        if (!$pagina) { echo "Acceso denegado"; return; }

        $categorias = $modelo->obtenerCategorias();
        $error = "";

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) die("Token inválido");

            $titulo = trim($_POST['titulo']);
            $nombre_imagen = $pagina->imagen; 
            $res_img = $this->_subirImagen($_FILES['imagen']);
            
            if (!$res_img['ok']) { $error = $res_img['error']; } 
            else {
                if ($res_img['nombre']) $nombre_imagen = $res_img['nombre'];
                $slug = strtolower(trim(preg_replace('/[^a-z0-9-]+/', '-', $titulo), '-'));
                
                $datos = [
                    'titulo' => $titulo, 'contenido' => $_POST['contenido'], 'slug' => $slug,
                    'estado' => $_POST['estado'], 'categoria_id' => $_POST['categoria_id'], 'imagen' => $nombre_imagen
                ];
                
                if ($modelo->actualizar($id, $datos, $user_id, $soy_admin)) {
                    $_SESSION['flash'] = "¡Cambios guardados!";
                    header("Location: index.php?c=Dashboard&a=index"); exit;
                } else { $error = "Error al actualizar."; }
            }
        }
        require_once 'views/dashboard/editar.php';
    }

    public function borrar() {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) die("Token inválido");
            $modelo = new Pagina();
            if ($modelo->borrar($_POST['id'], $_SESSION['user_id'], ($_SESSION['rol'] === 'admin'))) {
                $_SESSION['flash'] = "Página eliminada.";
            } else { $_SESSION['flash'] = "Error al borrar."; }
        }
        header("Location: index.php?c=Dashboard&a=index");
    }

    public function fijar() {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) die("Token inválido");
            $modelo = new Pagina();
            $modelo->fijarInicio($_POST['id']);
            $_SESSION['flash'] = "Portada actualizada.";
        }
        header("Location: index.php?c=Dashboard&a=index");
    }

    private function _subirImagen($archivo) {
        if (!isset($archivo) || $archivo['error'] === 4) return ['ok' => true, 'nombre' => null];
        if ($archivo['error'] !== 0) return ['ok' => false, 'error' => "Error subida"];
        $ext = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) return ['ok' => false, 'error' => "Formato no válido"];
        $nombre = "img_" . time() . "_" . rand(100,999) . "." . $ext;
        if (!is_dir('uploads')) mkdir('uploads');
        if (move_uploaded_file($archivo['tmp_name'], "uploads/" . $nombre)) return ['ok' => true, 'nombre' => $nombre];
        return ['ok' => false, 'error' => "Error mover archivo"];
    }
}
?>