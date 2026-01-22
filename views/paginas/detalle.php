<?php include 'views/layouts/header.php'; ?>

<div class='mb-5'>
    <?php if ($pagina->imagen && file_exists("uploads/".$pagina->imagen)): ?>
        <img src='uploads/<?= $pagina->imagen ?>' class='img-fluid rounded-4 mb-4 w-100 object-fit-cover shadow-sm' style='max-height: 450px;'>
    <?php endif; ?>

    <div class='text-center'>
        <?php if (!empty($pagina->categoria_nombre)): ?>
            <a href="index.php?c=Pagina&a=blog&cat=<?= $pagina->categoria_id ?>" class="badge bg-primary bg-opacity-10 text-primary mb-2 text-decoration-none">
                <?= htmlspecialchars($pagina->categoria_nombre) ?>
            </a>
        <?php endif; ?>
        <h1 class='display-4 fw-bold text-body mb-3'><?= htmlspecialchars($pagina->titulo) ?></h1>
        <div class='text-muted d-flex justify-content-center align-items-center gap-3'>
            <span><i class='bi bi-person-circle'></i> <?= htmlspecialchars($pagina->autor_nombre ?? 'Anónimo') ?></span> | 
            <span><i class='bi bi-calendar3'></i> <?= date('d/m/Y', strtotime($pagina->fecha_creacion)) ?></span> | 
            <span><i class='bi bi-eye'></i> <?= $pagina->visitas ?></span>
        </div>
    </div>
</div>

<div class='contenido-web p-5 bg-white shadow rounded-4 mb-5' style="min-height: 300px;">
    <?= $pagina->contenido ?>
    
    <div class="mt-5 d-flex align-items-center justify-content-between border-top pt-4">
        <div>
            <?php 
            // Comprobar si di like
            $mi_id = $_SESSION['user_id'] ?? 0;
            global $pdo; // Truco rápido para vista, idealmente pasa desde controlador
            $stmtL = $pdo->prepare("SELECT id FROM likes WHERE user_id = ? AND pagina_id = ?");
            $stmtL->execute([$mi_id, $pagina->id]);
            $tengo_like = ($stmtL->rowCount() > 0);
            $total_likes = $pdo->query("SELECT COUNT(*) FROM likes WHERE pagina_id = {$pagina->id}")->fetchColumn();
            
            $clase_btn = $tengo_like ? 'btn-danger' : 'btn-outline-danger';
            $icono_btn = $tengo_like ? 'bi-heart-fill' : 'bi-heart';
            ?>
            <button onclick='darLike(<?= $pagina->id ?>)' id='btnLike<?= $pagina->id ?>' class='btn <?= $clase_btn ?> rounded-pill px-4 shadow-sm'>
                <i class='bi <?= $icono_btn ?>' id='iconLike<?= $pagina->id ?>'></i> <span id='countLike<?= $pagina->id ?>' class='fw-bold ms-1'><?= $total_likes ?></span>
            </button>
        </div>
        
        <div class='d-flex gap-2'>
            <?php 
            $protocolo = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https" : "http";
            $url_actual = $protocolo . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
            $texto_compartir = urlencode("¡Mira este artículo: " . $pagina->titulo . "!");
            $url_encoded = urlencode($url_actual);
            ?>
            <span class='text-muted me-2 align-self-center small'>Compartir:</span>
            <a href='https://api.whatsapp.com/send?text=<?= $texto_compartir ?>%20<?= $url_encoded ?>' target='_blank' class='btn btn-success btn-sm rounded-circle d-flex align-items-center justify-content-center' style='width:35px;height:35px;'><i class='bi bi-whatsapp'></i></a>
            <a href='https://twitter.com/intent/tweet?text=<?= $texto_compartir ?>&url=<?= $url_encoded ?>' target='_blank' class='btn btn-dark btn-sm rounded-circle d-flex align-items-center justify-content-center' style='width:35px;height:35px;'><i class='bi bi-twitter-x'></i></a>
            <a href='https://www.facebook.com/sharer/sharer.php?u=<?= $url_encoded ?>' target='_blank' class='btn btn-primary btn-sm rounded-circle d-flex align-items-center justify-content-center' style='width:35px;height:35px;'><i class='bi bi-facebook'></i></a>
        </div>
    </div>
</div>

<div class='row'><div class='col-12 mb-5'>
    <h3 class="fw-bold text-body mb-4"><i class="bi bi-chat-text-fill text-primary me-2"></i>Comentarios (<?= $total_comentarios ?>)</h3>
    
    <?php if(isset($_SESSION['user_id'])): ?>
        <div class='card mb-4 bg-light border-0 shadow-sm'>
            <div class='card-body p-4'>
                <form method='POST' action='index.php?c=Pagina&a=comentar'>
                    <input type='hidden' name='csrf_token' value='<?= $_SESSION['csrf_token'] ?>'>
                    <input type='hidden' name='pagina_id' value='<?= $pagina->id ?>'>
                    <input type='hidden' name='slug_retorno' value='<?= $pagina->slug ?>'>
                    
                    <textarea name='comentario' class='form-control mb-3 border-0' rows="3" required placeholder='Escribe tu opinión...' style="resize:none;"></textarea>
                    <div class="d-flex justify-content-end"><button type='submit' class='btn btn-primary rounded-pill px-4'>Publicar</button></div>
                </form>
            </div>
        </div>
    <?php else: ?>
        <div class='alert alert-info text-center rounded-pill mb-5'>Inicia sesión para comentar.</div>
    <?php endif; ?>

    <?php if ($total_comentarios > 0): ?>
        <?php foreach($comentarios as $com): 
             $avatar_html = ($com->avatar && file_exists("uploads/".$com->avatar)) 
                ? "<img src='uploads/{$com->avatar}' class='rounded-circle object-fit-cover' style='width: 45px; height: 45px;'>"
                : "<div class='bg-secondary text-white rounded-circle d-flex justify-content-center align-items-center fw-bold' style='width: 45px; height: 45px;'>".strtoupper(substr($com->username, 0, 1))."</div>";
        ?>
            <div class='d-flex mb-4 p-3 bg-white rounded-4 shadow-sm'>
                <div class="me-3"><?= $avatar_html ?></div>
                <div class="w-100">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <h6 class="fw-bold text-body mb-0"><?= htmlspecialchars($com->username) ?></h6>
                        <small class="text-muted" style="font-size: 0.8rem;"><?= date('d M, Y H:i', strtotime($com->fecha)) ?></small>
                    </div>
                    <p class="mb-0 text-body"><?= nl2br(htmlspecialchars($com->comentario)) ?></p>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="text-center text-muted py-4">Aún no hay comentarios.</div>
    <?php endif; ?>
</div></div>

<div class='d-flex justify-content-between mb-5'>
    <a href='index.php?c=Pagina&a=blog' class='btn btn-outline-secondary rounded-pill px-4'>&larr; Volver al Blog</a>
    <?php if(isset($pagina->user_id) && isset($mi_id) && $mi_id != $pagina->user_id): ?>
        <a href='index.php?c=Chat&a=index&chat_con=<?= $pagina->user_id ?>' class='btn btn-success rounded-pill px-4 shadow'><i class='bi bi-chat-dots-fill'></i> Chat con Autor</a>
    <?php endif; ?>
</div>

<?php include 'views/layouts/footer.php'; ?>

<script>
function darLike(idPagina) {
    var token = document.getElementById('global_csrf').value;
    if(!token) { alert("Inicia sesión para dar like"); window.location.href='index.php?c=Usuario&a=login'; return; }
    
    var btn = document.getElementById('btnLike' + idPagina);
    var icn = document.getElementById('iconLike' + idPagina);
    var counter = document.getElementById('countLike' + idPagina);
    
    // Feedback visual inmediato
    if (btn.classList.contains('btn-danger')) {
        btn.classList.replace('btn-danger', 'btn-outline-danger');
        icn.classList.replace('bi-heart-fill', 'bi-heart');
        counter.innerText = Math.max(0, parseInt(counter.innerText) - 1);
    } else {
        btn.classList.replace('btn-outline-danger', 'btn-danger');
        icn.classList.replace('bi-heart', 'bi-heart-fill');
        counter.innerText = parseInt(counter.innerText) + 1;
    }

    var f = new FormData(); 
    f.append('pagina_id', idPagina);
    f.append('csrf_token', token);

    // AQUÍ ESTABA EL ERROR: Ahora apunta al controlador correcto
    fetch('index.php?c=Pagina&a=like', { method: 'POST', body: f })
    .then(r => r.json())
    .then(d => {
        if (d.status === 'ok') { 
            counter.innerText = d.total; 
        }
    });
}
</script>