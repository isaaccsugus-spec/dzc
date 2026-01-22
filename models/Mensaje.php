<?php
class Mensaje {
    private $pdo;

    public function __construct() {
        global $pdo;
        $this->pdo = $pdo;
    }

    // Obtener contactos con contador de no leídos (Igual que en tu buzon.php)
    public function obtenerContactos($mi_id) {
        $sql = "SELECT DISTINCT u.id, u.username, u.avatar,
                (SELECT COUNT(*) FROM mensajes m_count 
                 WHERE m_count.remitente_id = u.id 
                 AND m_count.destinatario_id = :yo_count 
                 AND m_count.leido = 0) as sin_leer
                FROM usuarios u
                JOIN mensajes m ON (u.id = m.remitente_id OR u.id = m.destinatario_id)
                WHERE (m.remitente_id = :yo1 OR m.destinatario_id = :yo2) AND u.id != :yo3";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':yo_count' => $mi_id,
            ':yo1' => $mi_id, 
            ':yo2' => $mi_id, 
            ':yo3' => $mi_id
        ]);
        return $stmt->fetchAll();
    }

    // Obtener un usuario suelto (para cuando inicias chat nuevo)
    public function obtenerUsuario($id) {
        $stmt = $this->pdo->prepare("SELECT id, username, avatar FROM usuarios WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    // Cargar conversación y marcar como leídos
    public function obtenerConversacion($yo, $otro) {
        // 1. Marcar como leídos (UPDATE)
        $this->pdo->prepare("UPDATE mensajes SET leido = 1 WHERE remitente_id = ? AND destinatario_id = ?")
                  ->execute([$otro, $yo]);

        // 2. Obtener mensajes (SELECT)
        $sql = "SELECT * FROM mensajes 
                WHERE (remitente_id = :yo1 AND destinatario_id = :otro1) 
                   OR (remitente_id = :otro2 AND destinatario_id = :yo2)
                ORDER BY fecha ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':yo1' => $yo, ':otro1' => $otro, ':otro2' => $otro, ':yo2' => $yo]);
        return $stmt->fetchAll();
    }

    // Enviar mensaje
    public function enviar($remitente, $destinatario, $mensaje) {
        $sql = "INSERT INTO mensajes (remitente_id, destinatario_id, asunto, mensaje, leido) 
                VALUES (:rem, :dest, 'Chat', :men, 0)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([':rem' => $remitente, ':dest' => $destinatario, ':men' => $mensaje]);
    }
}
?>