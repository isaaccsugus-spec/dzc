<?php
require_once 'models/Usuario.php';

class UsuarioController {

    public function login() {
        if (isset($_SESSION['user_id'])) { header("Location: index.php?c=Dashboard&a=index"); exit; }
        $error = '';

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) die("Error Token");

            $username = trim($_POST['username']);
            $password = $_POST['password'];

            $modelo = new Usuario();
            $user = $modelo->obtenerPorUsername($username);

            if ($user && password_verify($password, $user->password)) {
                session_regenerate_id(true);
                $_SESSION['user_id'] = $user->id;
                $_SESSION['username'] = $user->username;
                $_SESSION['rol'] = $user->rol;
                header("Location: index.php?c=Dashboard&a=index"); exit;
            } else {
                $error = "Credenciales incorrectas.";
            }
        }
        require_once 'views/usuarios/login.php';
    }

    public function registro() {
        if (isset($_SESSION['user_id'])) { header("Location: index.php?c=Dashboard&a=index"); exit; }
        $error = '';

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $datos = [
                'nombre' => trim($_POST['nombre']), 'apellidos' => trim($_POST['apellidos']),
                'email' => trim($_POST['email']), 'username' => trim($_POST['username']),
                'password' => $_POST['password'], 'rol' => 'user'
            ];
            
            if (empty($datos['username']) || empty($datos['password'])) $error = "Faltan datos.";
            elseif ($datos['password'] !== $_POST['confirm_password']) $error = "Contraseñas no coinciden.";
            else {
                $modelo = new Usuario();
                if ($modelo->existeDuplicado($datos['username'], $datos['email'])) {
                    $error = "Usuario o Email ya existe.";
                } else {
                    if ($_POST['codigo_admin'] === "Administrador1") $datos['rol'] = 'admin';
                    $datos['password'] = password_hash($datos['password'], PASSWORD_BCRYPT);
                    
                    if ($nuevo_id = $modelo->registrar($datos)) {
                        $_SESSION['user_id'] = $nuevo_id;
                        $_SESSION['username'] = $datos['username'];
                        $_SESSION['rol'] = $datos['rol'];
                        $_SESSION['flash'] = "¡Bienvenido!";
                        header("Location: index.php?c=Dashboard&a=index"); exit;
                    } else { $error = "Error BD"; }
                }
            }
        }
        require_once 'views/usuarios/registro.php';
    }

    public function perfil() {
        if (!isset($_SESSION['user_id'])) { header("Location: index.php?c=Usuario&a=login"); exit; }
        global $pdo; 
        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $usuario = $stmt->fetch();
        $mensaje = ""; $tipo_mensaje = "";

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) die("Token Error");
            $nuevo_user = trim($_POST['username']);
            $nueva_pass = $_POST['password'];
            $nombre_avatar = $_POST['avatar_actual'];
            
            $archivo = $_FILES['avatar'];
            if (isset($archivo) && $archivo['error'] === 0) {
                $ext = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
                $nombre_avatar = "u".time().rand(100,999).".".$ext;
                move_uploaded_file($archivo['tmp_name'], "uploads/".$nombre_avatar);
                $_SESSION['avatar'] = $nombre_avatar;
            }

            try {
                if (!empty($nueva_pass)) {
                    $hash = password_hash($nueva_pass, PASSWORD_BCRYPT);
                    $pdo->prepare("UPDATE usuarios SET username = ?, password = ?, avatar = ? WHERE id = ?")->execute([$nuevo_user, $hash, $nombre_avatar, $_SESSION['user_id']]);
                } else {
                    $pdo->prepare("UPDATE usuarios SET username = ?, avatar = ? WHERE id = ?")->execute([$nuevo_user, $nombre_avatar, $_SESSION['user_id']]);
                }
                $_SESSION['username'] = $nuevo_user;
                $usuario->username = $nuevo_user; $usuario->avatar = $nombre_avatar;
                $mensaje = "Perfil actualizado"; $tipo_mensaje = "success";
            } catch (Exception $e) {
                $mensaje = "Error: Usuario ya existe."; $tipo_mensaje = "danger";
            }
        }
        require_once 'views/usuarios/perfil.php';
    }

    // --- ACCIONES DE ADMIN ---
    public function lista() {
        if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'admin') { header("Location: index.php"); exit; }
        $modelo = new Usuario();
        $usuarios = $modelo->obtenerTodos();
        require_once 'views/usuarios/lista.php';
    }

    public function eliminar() {
        if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'admin') die("Acceso denegado");
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) die("Token Error");
            $modelo = new Usuario();
            if ($modelo->borrar($_POST['id'])) $_SESSION['flash'] = "Usuario eliminado.";
            else $_SESSION['flash'] = "No se puede eliminar al Super Admin.";
        }
        header("Location: index.php?c=Usuario&a=lista");
    }

    // --- NUEVO: CAMBIAR ROL ---
    public function cambiarRol() {
        if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'admin') die("Acceso denegado");
        
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) die("Token Error");
            
            $id = $_POST['id'];
            $nuevo_rol = $_POST['rol']; // 'admin' o 'user'

            // Evitar que uno se cambie a sí mismo (para no bloquearse)
            if ($id == $_SESSION['user_id']) {
                $_SESSION['flash'] = "No puedes cambiar tu propio rol.";
            } else {
                $modelo = new Usuario();
                if ($modelo->cambiarRol($id, $nuevo_rol)) {
                    $_SESSION['flash'] = "Rol actualizado correctamente.";
                } else {
                    $_SESSION['flash'] = "Error al cambiar rol o usuario protegido.";
                }
            }
        }
        header("Location: index.php?c=Usuario&a=lista");
    }

    public function logout() { session_destroy(); header("Location: index.php?c=Usuario&a=login"); exit; }
}
?>