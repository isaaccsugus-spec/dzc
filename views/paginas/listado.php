<?php include 'views/layouts/header.php'; ?>

<h2 class='mb-4 fw-bold text-body'>Publicaciones Recientes</h2>

<?php if ($entradas): ?>
    <div class='row'>
    <?php foreach($entradas as $pag): 
        $clase_btn = ($pag->tengo_like > 0) ? 'btn-danger' : 'btn-outline-danger';
        $icono_btn = ($pag->tengo_like > 0) ? 'bi-heart-fill' : 'bi-heart';
    ?>
        <div class='col-md-12 mb-4'>
            <div class='card border-0 shadow-sm overflow-hidden h-100'>
                <?php if ($pag->imagen && file_exists("uploads/".$pag->imagen)): ?>
                    <img src='uploads/<?= $pag->imagen ?>' class='card-img-top object-fit-cover' style='height: 250px;'>
                <?php endif; ?>
                
                <div class='card-body p-4'>
                    <h3 class='h4 mb-2'>
                        <a href='index.php?c=Pagina&a=ver&slug=<?= $pag->slug ?>' class='text-decoration-none text-body fw-bold'><?= htmlspecialchars($pag->titulo) ?></a>
                    </h3>
                    <div class='text-muted small mb-3'><i class="bi bi-calendar3"></i> <?= date('d/m/Y', strtotime($pag->fecha_creacion)) ?></div>
                    <p class='card-text text-muted'><?= substr(strip_tags($pag->contenido), 0, 150) ?>...</p>
                    
                    <div class='d-flex justify-content-between align-items-center mt-3 pt-3 border-top'>
                        <a href='index.php?c=Pagina&a=ver&slug=<?= $pag->slug ?>' class='btn btn-primary btn-sm rounded-pill px-3'>Leer más</a>
                        
                        <button onclick='darLike(<?= $pag->id ?>)' id='btnLike<?= $pag->id ?>' class='btn <?= $clase_btn ?> btn-sm rounded-pill px-3'>
                            <i class='bi <?= $icono_btn ?>' id='iconLike<?= $pag->id ?>'></i> <span id='countLike<?= $pag->id ?>'><?= $pag->total_likes_count ?></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
    </div>
    
    <div class="d-flex justify-content-center mt-4 mb-5">
        <?php 
        $pag = $_GET['pag'] ?? 1;
        // Reconstruir URL manteniendo filtros
        $params = $_GET; unset($params['pag']); 
        $base = "index.php?" . http_build_query($params);
        
        // Calcular total si no viene (o pasarlo desde controlador mejor, aquí asumimos que tienes $total_registros)
        // Nota: En PaginaController.php ya pasamos $total_registros
        $total_hojas = ceil($total_registros / 5);

        if($pag > 1) echo "<a href='$base&pag=".($pag-1)."' class='btn btn-outline-primary me-2'>Anterior</a>";
        if($pag < $total_hojas) echo "<a href='$base&pag=".($pag+1)."' class='btn btn-outline-primary'>Siguiente</a>";
        ?>
    </div>

<?php else: ?>
    <div class='alert alert-info py-5 text-center'>No hay resultados que coincidan.</div>
<?php endif; ?>

<?php include 'views/layouts/footer.php'; ?>

<script>
function darLike(idPagina) {
    var token = document.getElementById('global_csrf').value;
    if(!token) { alert("Inicia sesión para dar like"); window.location.href='index.php?c=Usuario&a=login'; return; }
    
    var btn = document.getElementById('btnLike' + idPagina);
    var icn = document.getElementById('iconLike' + idPagina);
    var counter = document.getElementById('countLike' + idPagina);
    
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

    fetch('index.php?c=Pagina&a=like', { method: 'POST', body: f })
    .then(r => r.json())
    .then(d => { if (d.status === 'ok') counter.innerText = d.total; });
}
</script>