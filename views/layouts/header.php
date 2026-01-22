<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }

// 1. Configuración de Modo (Público vs Privado)
$controlador_actual = $_GET['c'] ?? 'Pagina';
// Si estamos en Pagina o Usuario (login/registro), es vista pública. Si es Dashboard o Chat, es privada.
$modo_publico = ($controlador_actual === 'Pagina' || $controlador_actual === 'Usuario');

// 2. Conexión y Datos Globales
global $pdo; 
if (!isset($pdo) && file_exists('config/db.php')) { require_once 'config/db.php'; }
elseif (!isset($pdo) && file_exists('../config/db.php')) { require_once '../config/db.php'; }

$num_mensajes_nuevos = 0;
$mi_avatar_nav = null;
$categorias_menu = []; 

if (isset($pdo)) {
    // Cargar Categorías para el buscador
    try {
        $categorias_menu = $pdo->query("SELECT * FROM categorias ORDER BY nombre ASC")->fetchAll();
    } catch (Exception $e) { }

    // Datos del Usuario Logueado
    if (isset($_SESSION['user_id'])) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM mensajes WHERE destinatario_id = :yo AND leido = 0");
        $stmt->execute([':yo' => $_SESSION['user_id']]);
        $num_mensajes_nuevos = $stmt->fetchColumn();

        $stmtAv = $pdo->prepare("SELECT avatar FROM usuarios WHERE id = :id");
        $stmtAv->execute([':id' => $_SESSION['user_id']]);
        $mi_avatar_nav = $stmtAv->fetchColumn();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blog MVC</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootswatch@5.3.0/dist/zephyr/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <?php if (!$modo_publico): ?>
        <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">
    <?php endif; ?>
    
    <style>
        /* --- ESTILOS GENERALES Y COLORES --- */
        body { background-color: #f8f9fa; transition: background 0.3s, color 0.3s; min-height: 100vh; display: flex; flex-direction: column; }
        
        /* Ajuste de la Barra de Navegación */
        .navbar { padding-top: 0.8rem; padding-bottom: 0.8rem; }
        
        /* Avatar Redondo */
        .nav-avatar { width: 34px; height: 34px; object-fit: cover; border-radius: 50%; border: 2px solid rgba(255,255,255,0.8); }
        
        /* Buscador Combinado en Header */
        .search-group .form-select { border-top-right-radius: 0; border-bottom-right-radius: 0; background-color: #f8f9fa; border-right: none; max-width: 140px; }
        .search-group .form-control { border-radius: 0; border-left: 1px solid #ced4da; }
        .search-group .btn { border-top-left-radius: 0; border-bottom-left-radius: 0; }

        /* --- ESTILOS DEL CHAT (Integrados) --- */
        .chat-container { height: 75vh; background: #fff; border-radius: 12px; overflow: hidden; box-shadow: 0 5px 15px rgba(0,0,0,0.08); display: flex; }
        .chat-sidebar { width: 30%; background: #fff; border-right: 1px solid #eee; overflow-y: auto; }
        .chat-main { width: 70%; display: flex; flex-direction: column; background: #efeae2; } 
        .chat-header { padding: 15px; background: #f0f2f5; border-bottom: 1px solid #ddd; display: flex; align-items: center; justify-content: space-between; }
        .chat-messages { flex: 1; padding: 20px; overflow-y: auto; display: flex; flex-direction: column; gap: 8px; }
        .chat-input-area { padding: 15px; background: #f0f2f5; }
        .contact-item { padding: 15px; cursor: pointer; border-bottom: 1px solid #f9f9f9; transition: 0.2s; }
        .contact-item:hover { background: #f0f2f5; }
        .contact-item.active { background: #e3f2fd; border-left: 4px solid #0d6efd; }
        .message { max-width: 75%; padding: 10px 15px; border-radius: 8px; position: relative; font-size: 0.95rem; word-wrap: break-word; box-shadow: 0 1px 1px rgba(0,0,0,0.1); }
        .message-mine { align-self: flex-end; background: #d9fdd3; color: #000; border-top-right-radius: 0; }
        .message-other { align-self: flex-start; background: #fff; color: #000; border-top-left-radius: 0; }
        .msg-time { font-size: 0.7rem; color: #999; text-align: right; margin-top: 3px; display: block; }

        /* --- MODO OSCURO (Dark Mode) --- */
        body.dark-mode { background-color: #121212 !important; color: #e0e0e0; }
        body.dark-mode .navbar-light { background-color: #1e1e1e !important; border-bottom: 1px solid #333; }
        body.dark-mode .navbar-light .navbar-brand, body.dark-mode .navbar-light .nav-link { color: #fff !important; }
        body.dark-mode .card, body.dark-mode .bg-white { background-color: #1e1e1e !important; color: #fff; border-color: #333; }
        body.dark-mode .bg-light { background-color: #2d2d2d !important; color: #fff !important; }
        body.dark-mode input, body.dark-mode textarea, body.dark-mode select { background-color: #2d2d2d; border-color: #444; color: #fff; }
        body.dark-mode .dropdown-menu { background-color: #2d2d2d; border-color: #444; }
        body.dark-mode .dropdown-item { color: #fff; }
        body.dark-mode .dropdown-item:hover { background-color: #3d3d3d; }
    </style>
</head>
<body class="d-flex flex-column min-vh-100">

<nav class="navbar navbar-expand-lg <?= $modo_publico ? 'navbar-light bg-white shadow-sm' : 'navbar-dark bg-primary shadow' ?> mb-4">
  <div class="container">
    
    <a class="navbar-brand fw-bold text-uppercase" href="index.php?c=Pagina&a=index">
        <?= $modo_publico ? '<i class="bi bi-globe2 text-primary"></i> Mi Blog' : '<i class="bi bi-speedometer2"></i> Panel CMS' ?>
    </a>
    
    <button class="btn btn-sm btn-link nav-link rounded-circle ms-2" id="darkModeToggle" title="Cambiar Tema">
        <i class="bi bi-moon-stars-fill" id="iconMode"></i>
    </button>

    <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    
    <div class="collapse navbar-collapse" id="navbarNav">
      
      <ul class="navbar-nav me-auto align-items-center">
        <?php if ($modo_publico): ?>
            <li class="nav-item"><a class="nav-link px-3" href="index.php?c=Pagina&a=index">Inicio</a></li>
            <li class="nav-item"><a class="nav-link px-3" href="index.php?c=Pagina&a=blog">Blog</a></li>
        <?php else: ?>
            <?php if(isset($_SESSION['user_id'])): ?>
                <li class="nav-item"><a class="nav-link text-white fw-bold px-3" href="index.php?c=Pagina&a=index" target="_blank"><i class="bi bi-eye"></i> Ver Web</a></li>
                <li class="nav-item"><a class="nav-link text-white px-3" href="index.php?c=Dashboard&a=index">Mis Páginas</a></li>
                
                <li class="nav-item position-relative">
                    <a class="nav-link text-white px-3" href="index.php?c=Chat&a=index">
                        Mensajes
                        <?php if($num_mensajes_nuevos > 0): ?>
                            <span class="badge rounded-pill bg-danger ms-1 animate__animated animate__pulse animate__infinite">
                                <?= $num_mensajes_nuevos ?>
                            </span>
                        <?php endif; ?>
                    </a>
                </li>
                
                <li class="nav-item ms-2"><a class="btn btn-sm btn-light text-primary fw-bold rounded-pill px-3" href="index.php?c=Dashboard&a=crear">+ Crear</a></li>
            <?php endif; ?>
        <?php endif; ?>
      </ul>

      <div class="d-flex align-items-center gap-3 mt-3 mt-lg-0">
        
        <?php if ($modo_publico): ?>
            <form class="d-flex search-group shadow-sm rounded-pill overflow-hidden border" action="index.php" method="GET">
                <input type="hidden" name="c" value="Pagina">
                <input type="hidden" name="a" value="blog">
                
                <select name="cat" class="form-select border-0 bg-light text-muted small" style="width: auto; cursor: pointer;">
                    <option value="">Todo</option>
                    <?php foreach($categorias_menu as $cat): ?>
                        <option value="<?= $cat->id ?>" <?= (isset($_GET['cat']) && $_GET['cat'] == $cat->id) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat->nombre) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                
                <input class="form-control border-0" type="search" name="busqueda" placeholder="Buscar..." value="<?= htmlspecialchars($_GET['busqueda'] ?? '') ?>">
                
                <button class="btn btn-primary border-0 px-3" type="submit"><i class="bi bi-search"></i></button>
            </form>
        <?php endif; ?>

        <?php if(isset($_SESSION['user_id'])): ?>
            <div class="dropdown">
                <a href="#" class="d-flex align-items-center text-decoration-none dropdown-toggle <?= $modo_publico ? 'text-dark' : 'text-white' ?>" data-bs-toggle="dropdown">
                    <?php if($mi_avatar_nav && file_exists("uploads/".$mi_avatar_nav)): ?>
                        <img src="uploads/<?= $mi_avatar_nav ?>" class="nav-avatar me-2">
                    <?php else: ?>
                        <div class="nav-avatar me-2 bg-warning text-white d-flex align-items-center justify-content-center fw-bold small">
                            <?= strtoupper(substr($_SESSION['username'], 0, 1)) ?>
                        </div>
                    <?php endif; ?>
                    <span class="d-none d-md-inline fw-bold small"><?= htmlspecialchars($_SESSION['username']) ?></span>
                </a>
                <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 rounded-3 mt-2">
                    <li class="dropdown-header small text-muted text-uppercase">Mi Cuenta</li>
                    <li><a class="dropdown-item py-2" href="index.php?c=Usuario&a=perfil"><i class="bi bi-person-gear me-2 text-primary"></i> Editar Perfil</a></li>
                    
                    <?php if(!$modo_publico): ?>
                        <li><a class="dropdown-item py-2" href="index.php?c=Pagina&a=index"><i class="bi bi-globe me-2 text-success"></i> Ir a la Web</a></li>
                    <?php else: ?>
                        <li><a class="dropdown-item py-2" href="index.php?c=Dashboard&a=index"><i class="bi bi-speedometer2 me-2 text-warning"></i> Panel de Control</a></li>
                    <?php endif; ?>
                    
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item py-2 text-danger" href="index.php?c=Usuario&a=logout"><i class="bi bi-box-arrow-right me-2"></i> Cerrar Sesión</a></li>
                </ul>
            </div>
        <?php else: ?>
            <div class="d-flex gap-2">
                <a class="btn btn-outline-primary rounded-pill px-4" href="index.php?c=Usuario&a=login">Entrar</a>
                <a class="btn btn-primary rounded-pill px-4" href="index.php?c=Usuario&a=registro">Registro</a>
            </div>
        <?php endif; ?>
      </div>

    </div>
  </div>
</nav>

<div class="container pb-5">
    <input type="hidden" id="global_csrf" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
    
    <?php if(isset($_SESSION['flash'])): ?>
        <div class="alert alert-success alert-dismissible fade show mt-3 mb-4 shadow-sm rounded-3 border-0 border-start border-success border-5" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> <?= $_SESSION['flash'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['flash']); ?>
    <?php endif; ?>