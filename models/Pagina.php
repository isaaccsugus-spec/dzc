<?php
class Pagina {
    private $pdo;

    public function __construct() {
        global $pdo; 
        $this->pdo = $pdo;
    }

    // --- PARTE PÚBLICA (PORTADA Y BLOG) ---
    
    // ESTA ES LA FUNCIÓN QUE TE DABA ERROR (FALTABA)
    public function obtenerInicio() {
        $stmt = $this->pdo->prepare("SELECT * FROM paginas WHERE es_inicio = 1 AND estado = 'publicado' LIMIT 1");
        $stmt->execute();
        return $stmt->fetch();
    }

    public function obtenerPorSlug($slug) {
        $sql = "SELECT p.*, u.username as autor_nombre, c.nombre as categoria_nombre 
                FROM paginas p 
                LEFT JOIN usuarios u ON p.user_id = u.id 
                LEFT JOIN categorias c ON p.categoria_id = c.id
                WHERE p.slug = :slug AND p.estado = 'publicado'";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':slug' => $slug]);
        return $stmt->fetch();
    }

    public function obtenerListado($filtros, $inicio, $limite, $usuario_id = 0) {
        $where = "estado = 'publicado'";
        $params = [];
        if (!empty($filtros['cat'])) { $where .= " AND categoria_id = :cat"; $params[':cat'] = $filtros['cat']; }
        if (!empty($filtros['busqueda'])) { $where .= " AND titulo LIKE :q"; $params[':q'] = "%" . $filtros['busqueda'] . "%"; }

        $sql = "SELECT p.*, 
                       (SELECT COUNT(*) FROM likes WHERE pagina_id = p.id) as total_likes_count, 
                       (SELECT COUNT(*) FROM likes WHERE pagina_id = p.id AND user_id = :uid) as tengo_like
                FROM paginas p 
                WHERE $where 
                ORDER BY fecha_creacion ASC 
                LIMIT :inicio, :limite";
        
        $stmt = $this->pdo->prepare($sql);
        foreach ($params as $k => $v) $stmt->bindValue($k, $v);
        $stmt->bindValue(':inicio', (int)$inicio, PDO::PARAM_INT);
        $stmt->bindValue(':limite', (int)$limite, PDO::PARAM_INT);
        $stmt->bindValue(':uid', $usuario_id);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function contarTotal($filtros) {
        $where = "estado = 'publicado'";
        $params = [];
        if (!empty($filtros['cat'])) { $where .= " AND categoria_id = :cat"; $params[':cat'] = $filtros['cat']; }
        if (!empty($filtros['busqueda'])) { $where .= " AND titulo LIKE :q"; $params[':q'] = "%{$filtros['busqueda']}%"; }
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM paginas WHERE $where");
        $stmt->execute($params);
        return $stmt->fetchColumn();
    }
    
    // Auxiliares Públicos
    public function sumarVisita($id) { $this->pdo->prepare("UPDATE paginas SET visitas = visitas + 1 WHERE id = ?")->execute([$id]); }
    public function obtenerComentarios($id) { $stmt = $this->pdo->prepare("SELECT c.*, u.username, u.avatar FROM comentarios c JOIN usuarios u ON c.user_id = u.id WHERE c.pagina_id = :pid ORDER BY c.fecha DESC"); $stmt->execute([':pid' => $id]); return $stmt->fetchAll(); }
    public function guardarComentario($pagina_id, $user_id, $comentario) { $stmt = $this->pdo->prepare("INSERT INTO comentarios (pagina_id, user_id, comentario) VALUES (:pid, :uid, :com)"); return $stmt->execute([':pid' => $pagina_id, ':uid' => $user_id, ':com' => $comentario]); }


    // --- PARTE PRIVADA (DASHBOARD) --- 
    
    public function obtenerParaDashboard($user_id, $soy_admin, $inicio, $limite, $busqueda = null, $cat_id = null) {
        $sql = "SELECT p.*, c.nombre as nombre_cat, u.username as autor,
                (SELECT COUNT(*) FROM likes WHERE pagina_id = p.id) as total_likes 
                FROM paginas p 
                LEFT JOIN categorias c ON p.categoria_id = c.id
                LEFT JOIN usuarios u ON p.user_id = u.id
                WHERE 1=1";
        
        $params = [];

        if (!$soy_admin) { $sql .= " AND p.user_id = :uid"; $params[':uid'] = $user_id; }
        if (!empty($busqueda)) { $sql .= " AND p.titulo LIKE :busqueda"; $params[':busqueda'] = "%$busqueda%"; }
        if (!empty($cat_id)) { $sql .= " AND p.categoria_id = :cat"; $params[':cat'] = $cat_id; }

        $sql .= " ORDER BY p.es_inicio DESC, p.fecha_creacion DESC LIMIT :inicio, :limite";
        
        $stmt = $this->pdo->prepare($sql);
        foreach ($params as $key => $val) { $stmt->bindValue($key, $val); }
        $stmt->bindValue(':inicio', (int)$inicio, PDO::PARAM_INT);
        $stmt->bindValue(':limite', (int)$limite, PDO::PARAM_INT);
        
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function contarDashboard($user_id, $soy_admin, $busqueda = null, $cat_id = null) {
        $sql = "SELECT COUNT(*) FROM paginas p WHERE 1=1";
        $params = [];
        if (!$soy_admin) { $sql .= " AND p.user_id = :uid"; $params[':uid'] = $user_id; }
        if (!empty($busqueda)) { $sql .= " AND p.titulo LIKE :busqueda"; $params[':busqueda'] = "%$busqueda%"; }
        if (!empty($cat_id)) { $sql .= " AND p.categoria_id = :cat"; $params[':cat'] = $cat_id; }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn();
    }

    public function obtenerEstadisticas($user_id, $soy_admin) {
        $where = $soy_admin ? "1=1" : "user_id = :uid";
        $params = $soy_admin ? [] : [':uid' => $user_id];
        
        $sql = "SELECT COUNT(*) as total_docs, COALESCE(SUM(visitas),0) as total_visitas,
                COALESCE(SUM((SELECT COUNT(*) FROM likes WHERE pagina_id = p.id)),0) as total_likes,
                SUM(CASE WHEN estado = 'borrador' THEN 1 ELSE 0 END) as total_borradores
                FROM paginas p WHERE $where";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function obtenerTopPosts($user_id, $soy_admin) {
        $where = $soy_admin ? "1=1" : "user_id = :uid";
        $params = $soy_admin ? [] : [':uid' => $user_id];
        $sql = "SELECT titulo, visitas, (SELECT COUNT(*) FROM likes WHERE pagina_id = p.id) as total_likes FROM paginas p WHERE $where ORDER BY visitas DESC LIMIT 6";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function obtenerCategoriasGrafico($user_id, $soy_admin) {
        $where = $soy_admin ? "1=1" : "p.user_id = :uid";
        $params = $soy_admin ? [] : [':uid' => $user_id];
        $sql = "SELECT c.nombre, COUNT(*) as cantidad FROM paginas p LEFT JOIN categorias c ON p.categoria_id = c.id WHERE $where GROUP BY c.id, c.nombre";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function crear($datos) {
        $sql = "INSERT INTO paginas (user_id, titulo, contenido, slug, estado, categoria_id, imagen) VALUES (:uid, :tit, :cont, :slug, :est, :cat, :img)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([':uid' => $datos['user_id'], ':tit' => $datos['titulo'], ':cont' => $datos['contenido'], ':slug' => $datos['slug'], ':est' => $datos['estado'], ':cat' => $datos['categoria_id'], ':img' => $datos['imagen']]);
    }

    public function obtenerPorId($id, $user_id, $soy_admin) {
        if ($soy_admin) { $stmt = $this->pdo->prepare("SELECT * FROM paginas WHERE id = :id"); $stmt->execute([':id' => $id]); } 
        else { $stmt = $this->pdo->prepare("SELECT * FROM paginas WHERE id = :id AND user_id = :uid"); $stmt->execute([':id' => $id, ':uid' => $user_id]); }
        return $stmt->fetch();
    }

    public function actualizar($id, $datos, $user_id, $soy_admin) {
        $where = $soy_admin ? "id = :id" : "id = :id AND user_id = :uid";
        $params = [':tit' => $datos['titulo'], ':cont' => $datos['contenido'], ':slug' => $datos['slug'], ':est' => $datos['estado'], ':cat' => $datos['categoria_id'], ':img' => $datos['imagen'], ':id' => $id];
        if (!$soy_admin) $params[':uid'] = $user_id;
        $sql = "UPDATE paginas SET titulo = :tit, contenido = :cont, slug = :slug, estado = :est, categoria_id = :cat, imagen = :img WHERE $where";
        return $this->pdo->prepare($sql)->execute($params);
    }

    public function borrar($id, $user_id, $soy_admin) {
        $where = $soy_admin ? "id = :id" : "id = :id AND user_id = :uid";
        $params = [':id' => $id]; if (!$soy_admin) $params[':uid'] = $user_id;
        return $this->pdo->prepare("DELETE FROM paginas WHERE $where")->execute($params);
    }
    
    public function fijarInicio($id) {
        $this->pdo->query("UPDATE paginas SET es_inicio = 0");
        $stmt = $this->pdo->prepare("UPDATE paginas SET es_inicio = 1 WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function obtenerCategorias() { return $this->pdo->query("SELECT * FROM categorias")->fetchAll(); }
}
?>