<?php include 'views/layouts/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="fw-bold mb-0">
            <?php if($soy_admin): ?>
                <span class="badge bg-danger rounded-pill fs-6 align-middle me-2 shadow-sm">ADMIN</span> Panel Global
            <?php else: ?>
                Gestión de Contenido
            <?php endif; ?>
        </h2>
        <p class="text-muted mb-0">
            <?= $soy_admin ? 'Gestionando todo el contenido.' : 'Tus estadísticas y publicaciones.' ?>
        </p>
    </div>
    
    <?php if($soy_admin): ?>
        <a href="index.php?c=Usuario&a=lista" class="btn btn-dark rounded-pill px-4 shadow-sm hover-scale">
            <i class="bi bi-people-fill me-2"></i> Usuarios
        </a>
    <?php endif; ?>
</div>

<div class="row mb-4">
    <div class="col-md-3"><div class="card border-0 shadow-sm h-100"><div class="card-body d-flex align-items-center"><div class="bg-primary bg-opacity-10 p-3 rounded-circle me-3 text-primary"><i class="bi bi-file-earmark-text fs-4"></i></div><div><h3 class="fw-bold mb-0"><?= $stats['total_docs'] ?></h3><small class="text-muted">Páginas</small></div></div></div></div>
    <div class="col-md-3"><div class="card border-0 shadow-sm h-100"><div class="card-body d-flex align-items-center"><div class="bg-success bg-opacity-10 p-3 rounded-circle me-3 text-success"><i class="bi bi-eye fs-4"></i></div><div><h3 class="fw-bold mb-0"><?= $stats['total_visitas'] ?></h3><small class="text-muted">Visitas</small></div></div></div></div>
    <div class="col-md-3"><div class="card border-0 shadow-sm h-100"><div class="card-body d-flex align-items-center"><div class="bg-danger bg-opacity-10 p-3 rounded-circle me-3 text-danger"><i class="bi bi-heart fs-4"></i></div><div><h3 class="fw-bold mb-0"><?= $stats['total_likes'] ?></h3><small class="text-muted">Likes</small></div></div></div></div>
    <div class="col-md-3"><div class="card border-0 shadow-sm h-100"><div class="card-body d-flex align-items-center"><div class="bg-warning bg-opacity-10 p-3 rounded-circle me-3 text-warning"><i class="bi bi-pencil fs-4"></i></div><div><h3 class="fw-bold mb-0"><?= $stats['total_borradores'] ?></h3><small class="text-muted">Borradores</small></div></div></div></div>
</div>

<div class="row mb-5">
    <div class="col-lg-8 mb-4 mb-lg-0">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-transparent border-0 pt-4 ps-4"><h5 class="fw-bold mb-0 text-body">Engagement (Top 6)</h5></div>
            <div class="card-body"><canvas id="graficoBarras" style="max-height: 280px;"></canvas></div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-transparent border-0 pt-4 ps-4"><h5 class="fw-bold mb-0 text-body">Temáticas</h5></div>
            <div class="card-body d-flex align-items-center justify-content-center position-relative">
                <div style="width: 220px; height: 220px;"><canvas id="graficoDonut"></canvas></div>
                <?php if(empty($labels_cats)): ?><p class="text-muted small position-absolute">Sin datos</p><?php endif; ?>
            </div>
            <div class="card-footer bg-transparent border-0 text-center pb-4">
                <a href="index.php?c=Dashboard&a=crear" class="btn btn-sm btn-outline-primary rounded-pill px-4 shadow-sm"><i class="bi bi-plus-lg"></i> Crear Nueva Página</a>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm mb-4 bg-white rounded-4">
    <div class="card-body p-3">
        <form action="index.php" method="GET" class="row g-2 align-items-center">
            <input type="hidden" name="c" value="Dashboard">
            <input type="hidden" name="a" value="index">
            
            <div class="col-md-4">
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-search"></i></span>
                    <input type="text" name="busqueda" class="form-control border-start-0 bg-light" placeholder="Buscar por título..." value="<?= htmlspecialchars($_GET['busqueda'] ?? '') ?>">
                </div>
            </div>
            
            <div class="col-md-3">
                <select name="cat" class="form-select bg-light">
                    <option value="">Todas las Categorías</option>
                    <?php if(isset($todas_categorias)): ?>
                        <?php foreach($todas_categorias as $cat_opt): ?>
                            <option value="<?= $cat_opt->id ?>" <?= (isset($_GET['cat']) && $_GET['cat'] == $cat_opt->id) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat_opt->nombre) ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
            
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100 rounded-pill">Filtrar</button>
            </div>
            
            <?php if(isset($_GET['busqueda']) || isset($_GET['cat'])): ?>
                <div class="col-md-2">
                    <a href="index.php?c=Dashboard&a=index" class="btn btn-outline-secondary w-100 rounded-pill">Limpiar</a>
                </div>
            <?php endif; ?>
        </form>
    </div>
</div>
<?php if(isset($paginas_tabla) && count($paginas_tabla) > 0): ?>
    <div class="card border-0 shadow rounded-4 overflow-hidden mb-4">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4">Destacado</th>
                        <?php if($soy_admin): ?><th>Autor</th><?php endif; ?>
                        <th>Título / Estado</th>
                        <th>Categoría</th>
                        <th class="text-center">Métricas</th>
                        <th>Fecha</th>
                        <th class="text-end pe-4">Acciones</th>
                    </tr>
                </thead>
                <tbody class="bg-white">
                    <?php foreach($paginas_tabla as $pag): ?>
                    <tr class="<?= $pag->es_inicio ? 'bg-warning bg-opacity-10' : '' ?>">
                        <td class="ps-4 text-center">
                            <?php if($pag->estado == 'publicado'): ?>
                                <form action="index.php?c=Dashboard&a=fijar" method="POST" style="display:inline;">
                                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                    <input type="hidden" name="id" value="<?= $pag->id ?>">
                                    <button type="submit" class="btn p-0 border-0 bg-transparent text-<?= $pag->es_inicio ? 'warning' : 'secondary opacity-25' ?> fs-4 btn-star-hover"><i class="bi bi-star<?= $pag->es_inicio ? '-fill' : '' ?>"></i></button>
                                </form>
                            <?php else: ?><i class="bi bi-lock text-muted opacity-25"></i><?php endif; ?>
                        </td>
                        <?php if($soy_admin): ?><td><span class="badge bg-primary bg-opacity-10 text-primary fw-normal"><?= htmlspecialchars($pag->autor ?? 'N/A') ?></span></td><?php endif; ?>
                        <td><div class="d-flex flex-column"><span class="fw-bold text-dark"><?= htmlspecialchars($pag->titulo) ?></span><small class="<?= $pag->estado == 'publicado' ? 'text-success' : 'text-muted' ?>">● <?= ucfirst($pag->estado) ?></small></div></td>
                        <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($pag->nombre_cat ?? 'General') ?></span></td>
                        <td class="text-center text-muted small"><i class="bi bi-eye"></i> <?= $pag->visitas ?> &nbsp; <i class="bi bi-heart text-danger"></i> <?= $pag->total_likes ?></td>
                        <td class="text-muted small"><?= date('d/m/y', strtotime($pag->fecha_creacion)) ?></td>
                        <td class="text-end pe-4">
                            <div class="d-inline-flex gap-1">
                                <a href="index.php?c=Pagina&a=ver&slug=<?= $pag->slug ?>" target="_blank" class="btn btn-sm btn-light rounded-circle"><i class="bi bi-arrow-up-right"></i></a>
                                <a href="index.php?c=Dashboard&a=editar&id=<?= $pag->id ?>" class="btn btn-sm btn-light rounded-circle"><i class="bi bi-pencil"></i></a>
                                <form action="index.php?c=Dashboard&a=borrar" method="POST" style="display:inline;" onsubmit="return confirm('¿Borrar?');">
                                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                    <input type="hidden" name="id" value="<?= $pag->id ?>">
                                    <button type="submit" class="btn btn-sm btn-light text-danger rounded-circle"><i class="bi bi-trash"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <?php 
    // Construir URL base manteniendo filtros
    $params = $_GET; unset($params['pag']); 
    $base_url = "index.php?" . http_build_query($params);
    $pagina_actual = $pagina_actual ?? 1;
    ?>
    <?php if(isset($total_hojas) && $total_hojas > 1): ?>
    <div class="d-flex justify-content-center mb-5">
        <nav aria-label="Navegación">
            <ul class="pagination pagination-sm shadow-sm">
                <li class="page-item <?= ($pagina_actual <= 1) ? 'disabled' : '' ?>">
                    <a class="page-link rounded-start-pill border-0" href="<?= $base_url ?>&pag=<?= $pagina_actual - 1 ?>"><i class="bi bi-chevron-left"></i> Anterior</a>
                </li>
                <?php for($i=1; $i<=$total_hojas; $i++): ?>
                    <li class="page-item <?= ($i == $pagina_actual) ? 'active' : '' ?>">
                        <a class="page-link border-0 <?= ($i == $pagina_actual) ? 'bg-primary text-white' : '' ?>" href="<?= $base_url ?>&pag=<?= $i ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>
                <li class="page-item <?= ($pagina_actual >= $total_hojas) ? 'disabled' : '' ?>">
                    <a class="page-link rounded-end-pill border-0" href="<?= $base_url ?>&pag=<?= $pagina_actual + 1 ?>">Siguiente <i class="bi bi-chevron-right"></i></a>
                </li>
            </ul>
        </nav>
    </div>
    <?php endif; ?>

<?php else: ?>
    <div class="text-center py-5 bg-white rounded-4 shadow-sm">
        <div class="bg-light d-inline-block p-4 rounded-circle mb-3"><i class="bi bi-journal-plus text-muted" style="font-size: 3rem;"></i></div>
        <h4 class="fw-bold text-dark">No se encontraron resultados</h4>
        <?php if(!empty($_GET['busqueda']) || !empty($_GET['cat'])): ?>
            <p class="text-muted mb-4">Prueba a cambiar los filtros de búsqueda.</p>
            <a href="index.php?c=Dashboard&a=index" class="btn btn-outline-primary rounded-pill px-4">Limpiar Filtros</a>
        <?php else: ?>
            <p class="text-muted mb-4">Crea tu primera página para empezar.</p>
            <a href="index.php?c=Dashboard&a=crear" class="btn btn-primary rounded-pill px-4">Comenzar</a>
        <?php endif; ?>
    </div>
<?php endif; ?>

<?php include 'views/layouts/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const labels = <?= json_encode($labels_paginas) ?>;
    const dataVisitas = <?= json_encode($data_visitas) ?>;
    const dataLikes = <?= json_encode($data_likes) ?>;
    const catLabels = <?= json_encode($labels_cats) ?>;
    const catData = <?= json_encode($data_cats) ?>;

    const ctxBar = document.getElementById('graficoBarras');
    if (ctxBar && labels.length > 0) { new Chart(ctxBar, { type: 'bar', data: { labels: labels, datasets: [{ label: 'Visitas', data: dataVisitas, backgroundColor: '#0d6efd' }, { label: 'Likes', data: dataLikes, backgroundColor: '#dc3545' }] }, options: { maintainAspectRatio: false } }); }
    const ctxDonut = document.getElementById('graficoDonut');
    if (ctxDonut && catLabels.length > 0) { new Chart(ctxDonut, { type: 'doughnut', data: { labels: catLabels, datasets: [{ data: catData, backgroundColor: ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b'], hoverOffset: 4 }] }, options: { maintainAspectRatio: false, plugins: { legend: { display: false } } } }); }
</script>
<style>.btn-star-hover:hover { color: #ffc107 !important; opacity: 1 !important; transform: scale(1.1); transition: 0.2s; cursor: pointer; } .hover-scale:hover { transform: scale(1.05); transition: 0.2s; }</style>