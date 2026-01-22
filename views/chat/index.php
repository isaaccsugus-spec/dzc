<?php include 'views/layouts/header.php'; ?>

<div class="container-fluid px-0">
    <div class="row mb-3 px-3">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <h2>💬 Mis Conversaciones</h2>
            <a href="index.php?c=Dashboard&a=index" class="btn btn-outline-secondary btn-sm rounded-pill">&larr; Volver al Panel</a>
        </div>
    </div>

    <div class="chat-container mx-3 mb-4">
        <div class="chat-sidebar">
            <div class="p-3 bg-light border-bottom fw-bold text-muted small">CONTACTOS</div>
            <?php if (count($contactos) > 0): ?>
                <?php foreach($contactos as $contacto): ?>
                    <?php 
                        $noleidos = $contacto->sin_leer;
                        $tiene_foto = !empty($contacto->avatar) && file_exists("uploads/" . $contacto->avatar);
                    ?>
                    <a href="index.php?c=Chat&a=index&chat_con=<?= $contacto->id ?>" class="text-decoration-none text-body">
                        <div class="contact-item d-flex align-items-center <?= ($chat_con_id == $contacto->id) ? 'active' : '' ?>">
                            <div class="me-3 position-relative">
                                <?php if ($tiene_foto): ?>
                                    <img src="uploads/<?= $contacto->avatar ?>" class="rounded-circle object-fit-cover" style="width: 40px; height: 40px;">
                                <?php else: ?>
                                    <div class="bg-secondary text-white rounded-circle d-flex justify-content-center align-items-center fw-bold" style="width: 40px; height: 40px;">
                                        <?= strtoupper(substr($contacto->username, 0, 1)) ?>
                                    </div>
                                <?php endif; ?>
                                <?php if($noleidos > 0): ?><span class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle"></span><?php endif; ?>
                            </div>
                            <div>
                                <div class="fw-bold"><?= htmlspecialchars($contacto->username) ?></div>
                                <?php if($noleidos > 0): ?><small class="text-danger fw-bold"><?= $noleidos ?> nuevos</small><?php endif; ?>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="p-4 text-center text-muted">No tienes chats activos.</div>
            <?php endif; ?>
        </div>

        <div class="chat-main">
            <?php if ($chat_con_id && isset($usuario_chat)): ?>
                
                <div class="chat-header d-flex justify-content-between">
                    <div class="d-flex align-items-center">
                        <?php 
                            $tiene_foto_chat = !empty($usuario_chat->avatar) && file_exists("uploads/" . $usuario_chat->avatar);
                        ?>
                        <?php if ($tiene_foto_chat): ?>
                            <img src="uploads/<?= $usuario_chat->avatar ?>" class="rounded-circle object-fit-cover me-2" style="width: 40px; height: 40px;">
                        <?php else: ?>
                            <div class="bg-primary text-white rounded-circle d-flex justify-content-center align-items-center me-2 fw-bold" style="width: 40px; height: 40px;">
                                <?= strtoupper(substr($usuario_chat->username, 0, 1)) ?>
                            </div>
                        <?php endif; ?>
                        <h5 class="mb-0"><?= htmlspecialchars($usuario_chat->username) ?></h5>
                    </div>
                    
                    <button class="btn btn-outline-primary btn-sm rounded-pill" data-bs-toggle="modal" data-bs-target="#modalPerfil">
                        📄 Ver Publicaciones
                    </button>
                </div>

                <div class="chat-messages" id="chatBox">
                    <div class="text-center mt-5"><div class="spinner-border text-primary" role="status"></div></div>
                </div>

                <div class="chat-input-area">
                    <form id="formEnviar" class="d-flex">
                        <input type="hidden" id="chatConId" value="<?= $chat_con_id ?>">
                        <input type="hidden" id="csrfToken" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                        
                        <input type="text" id="inputMensaje" class="form-control me-2 rounded-pill" placeholder="Escribe un mensaje..." required autocomplete="off" autofocus>
                        <button type="submit" class="btn btn-success rounded-circle"><i class="bi bi-send-fill"></i></button>
                    </form>
                </div>

            <?php else: ?>
                <div class="h-100 d-flex flex-column justify-content-center align-items-center text-muted">
                    <div style="font-size: 4rem;">💬</div>
                    <h3>Selecciona un chat para empezar</h3>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php if ($chat_con_id && isset($usuario_chat)): ?>
<div class="modal fade" id="modalPerfil" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title">Artículos de <?= htmlspecialchars($usuario_chat->username) ?></h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <?php if(count($paginas_usuario_chat) > 0): ?>
            <div class="list-group">
                <?php foreach($paginas_usuario_chat as $pag): ?>
                    <a href="index.php?c=Pagina&a=ver&slug=<?= $pag->slug ?>" target="_blank" class="list-group-item list-group-item-action">
                        <div class="d-flex w-100 justify-content-between">
                            <h6 class="mb-1 text-primary"><?= htmlspecialchars($pag->titulo) ?></h6>
                            <small><?= date('d/m/Y', strtotime($pag->fecha_creacion)) ?></small>
                        </div>
                        <small class="text-muted">Clic para leer</small>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="alert alert-info text-center">Este usuario no tiene artículos publicados.</div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
<?php endif; ?>

<?php include 'views/layouts/footer.php'; ?>

<script>
document.addEventListener("DOMContentLoaded", function() {
    var chatBox = document.getElementById("chatBox");
    var chatConId = document.getElementById("chatConId") ? document.getElementById("chatConId").value : null;
    var token = document.getElementById('csrfToken') ? document.getElementById('csrfToken').value : '';

    if (!chatConId) return;

    function cargarMensajes() {
        var formData = new FormData();
        formData.append('csrf_token', token);

        // RUTA MVC: index.php?c=Chat&a=ajax_load
        fetch('index.php?c=Chat&a=ajax_load&chat_con=' + chatConId, {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(html => {
            var esAbajo = (chatBox.scrollHeight - chatBox.scrollTop === chatBox.clientHeight);
            var htmlActual = chatBox.innerHTML;
            
            // Filtramos errores de PHP para que no rompan el chat
            if(html !== htmlActual && !html.includes("<b>Warning</b>") && !html.includes("<b>Fatal error</b>")) {
                chatBox.innerHTML = html;
                if (esAbajo || htmlActual.includes("spinner-border")) {
                    chatBox.scrollTop = chatBox.scrollHeight;
                }
            }
        });
    }

    document.getElementById("formEnviar").addEventListener("submit", function(e) {
        e.preventDefault();
        var input = document.getElementById("inputMensaje");
        var mensaje = input.value;
        if (mensaje.trim() === "") return;

        var formData = new FormData();
        formData.append('mensaje', mensaje);
        formData.append('csrf_token', token);

        // RUTA MVC: index.php?c=Chat&a=ajax_send
        fetch('index.php?c=Chat&a=ajax_send&chat_con=' + chatConId, { method: 'POST', body: formData })
            .then(() => {
                input.value = "";
                cargarMensajes();
                setTimeout(() => chatBox.scrollTop = chatBox.scrollHeight, 100);
            });
    });

    cargarMensajes(); 
    setInterval(cargarMensajes, 2000); 
});
</script>