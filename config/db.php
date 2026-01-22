<?php
// db.php

// 1. CARGAMOS LIBRERÍAS
require_once dirname(__DIR__) . '/vendor/autoload.php';
// VARIABLES DE CONEXIÓN
$host = '127.0.0.1';
$port = '3307';      
$db   = 'PRUEBAS';    
$user = 'root';
$pass = ''; // Contraseña en plano para entorno local/pruebas
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;port=$port;dbname=$db;charset=$charset";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    die("Error de conexion: " . $e->getMessage());
}

// INICIO DE SESIÓN
if (session_status() === PHP_SESSION_NONE) { session_start(); }

// TOKEN CSRF
if (empty($_SESSION['csrf_token'])) {
    if (function_exists('random_bytes')) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    } else {
        $_SESSION['csrf_token'] = bin2hex(openssl_random_pseudo_bytes(32));
    }
}

// ---------------------------------------------------------
// FUNCIÓN 1: LIMPIEZA DE HTML (Seguridad XSS)
// ---------------------------------------------------------
function limpiarHTML($html) {
    if (empty($html)) return '';
    
    $config = HTMLPurifier_Config::createDefault();
    $config->set('HTML.Doctype', 'HTML 4.01 Transitional'); 
    $config->set('HTML.Allowed', 'p,b,strong,i,em,u,a[href|title|target],ul,ol,li,br,span[style|class],img[src|width|height|alt|class],h1,h2,h3,h4,h5,h6,blockquote,div[class|style],table[class],thead,tbody,tr,th,td');
    $config->set('Attr.AllowedClasses', null); 
    $config->set('Attr.AllowedFrameTargets', ['_blank']);
    $config->set('Cache.DefinitionImpl', null); // Descomentar si falla caché en XAMPP

    $purifier = new HTMLPurifier($config);
    return $purifier->purify($html);
}

// ---------------------------------------------------------
// FUNCIÓN 2: SUBIDA DE IMÁGENES CENTRALIZADA (DRY)
// ---------------------------------------------------------
function subirImagen($archivo, $prefijo = 'post') {
    // Retorna array: ['ok' => bool, 'nombre' => string|null, 'error' => string|null]
    
    // PASO 0: OPCIONALIDAD
    // Si el usuario no seleccionó archivo, NO es un error.
    if (!isset($archivo) || $archivo['error'] === UPLOAD_ERR_NO_FILE) {
        return ['ok' => true, 'nombre' => null, 'error' => null];
    }

    // 1. Validar errores de subida
    if ($archivo['error'] !== 0) {
        return ['ok' => false, 'error' => 'Error genérico al subir archivo.'];
    }

    // 2. Validar Peso (2MB)
    if ($archivo['size'] > 2 * 1024 * 1024) {
        return ['ok' => false, 'error' => 'La imagen es muy pesada (Máx 2MB).'];
    }

    // 3. Validación MIME (Magic Bytes - Seguridad Real)
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime_real = $finfo->file($archivo['tmp_name']);
    
    $mimes_permitidos = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png', 
        'image/webp' => 'webp',
        'image/gif'  => 'gif'
    ];

    if (!array_key_exists($mime_real, $mimes_permitidos)) {
        return ['ok' => false, 'error' => "Formato no válido ($mime_real)."];
    }

    // 4. Generar nombre único
    $ext = $mimes_permitidos[$mime_real];
    // Aseguramos que la carpeta exista
    if (!is_dir(__DIR__ . '/uploads')) mkdir(__DIR__ . '/uploads', 0755, true);
    
    $nombre_final = $prefijo . "_" . time() . "_" . rand(100,999) . "." . $ext;
    
    // 5. Mover archivo
    if (move_uploaded_file($archivo['tmp_name'], __DIR__ . "/uploads/" . $nombre_final)) {
        return ['ok' => true, 'nombre' => $nombre_final, 'error' => null];
    } else {
        return ['ok' => false, 'error' => 'Error al guardar el archivo en el servidor.'];
    }
}
?>