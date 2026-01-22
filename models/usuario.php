<?php
class Usuario {
    private $pdo;

    public function __construct() {
        global $pdo;
        $this->pdo = $pdo;
    }

    public function obtenerPorUsername($username) {
        $stmt = $this->pdo->prepare("SELECT * FROM usuarios WHERE username = :user");
        $stmt->execute([':user' => $username]);
        return $stmt->fetch();
    }

    public function existeDuplicado($username, $email) {
        $stmt = $this->pdo->prepare("SELECT id FROM usuarios WHERE username = :user OR email = :email");
        $stmt->execute([':user' => $username, ':email' => $email]);
        return $stmt->rowCount() > 0;
    }

    public function registrar($datos) {
        $sql = "INSERT INTO usuarios (nombre, apellidos, email, username, password, rol) 
                VALUES (:nom, :ape, :em, :user, :pass, :rol)";
        $stmt = $this->pdo->prepare($sql);
        
        if ($stmt->execute([
            ':nom' => $datos['nombre'], ':ape' => $datos['apellidos'], 
            ':em' => $datos['email'], ':user' => $datos['username'], 
            ':pass' => $datos['password'], ':rol' => $datos['rol']
        ])) {
            return $this->pdo->lastInsertId();
        }
        return false;
    }

    // --- MÉTODOS DE ADMIN (Gestión Usuarios) ---
    public function obtenerTodos() {
        return $this->pdo->query("SELECT * FROM usuarios ORDER BY id DESC")->fetchAll();
    }

    public function borrar($id) {
        // Evitar borrar al Admin ID 1
        if ($id == 1) return false;
        return $this->pdo->prepare("DELETE FROM usuarios WHERE id = ?")->execute([$id]);
    }

    // --- NUEVO: CAMBIAR ROL ---
    public function cambiarRol($id, $nuevo_rol) {
        // Protección: No se puede cambiar el rol del Super Admin (ID 1)
        if ($id == 1) return false;

        $stmt = $this->pdo->prepare("UPDATE usuarios SET rol = ? WHERE id = ?");
        return $stmt->execute([$nuevo_rol, $id]);
    }
}
?>