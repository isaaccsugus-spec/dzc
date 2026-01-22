<?php
// 1. INICIAR SESIÓN Y CONFIGURACIÓN
session_start();
require_once 'config/db.php';

// 2. DEFINIR CONTROLADOR Y ACCIÓN POR DEFECTO
// Si no piden nada, cargamos la Portada (PaginaController -> index)
$nombre_controlador = 'PaginaController';
$accion = 'index';

// 3. DETECTAR RUTAS (Ej: index.php?c=Usuario&a=login)
if (isset($_GET['c'])) {
    // Limpiamos y formamos el nombre (Ej: usuario -> UsuarioController)
    $nombre_controlador = ucfirst($_GET['c']) . 'Controller';
}

if (isset($_GET['a'])) {
    $accion = $_GET['a'];
}

// 4. CARGAR EL CONTROLADOR
$ruta_controlador = 'controllers/' . $nombre_controlador . '.php';

if (file_exists($ruta_controlador)) {
    require_once $ruta_controlador;
    
    // Verificamos que la clase existe (Ej: class UsuarioController)
    if (class_exists($nombre_controlador)) {
        $controlador = new $nombre_controlador();
        
        // Verificamos que el método existe (Ej: public function login())
        if (method_exists($controlador, $accion)) {
            // ¡EJECUTAMOS LA ACCIÓN!
            $controlador->$accion();
        } else {
            die("Error 404: La acción '$accion' no existe en este controlador.");
        }
    } else {
        die("Error 500: La clase '$nombre_controlador' no se encuentra.");
    }
} else {
    die("Error 404: La página que buscas no existe.");
}
?>