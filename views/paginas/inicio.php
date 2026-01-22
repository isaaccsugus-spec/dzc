<?php include 'views/layouts/header.php'; ?>

<div class="row justify-content-center">
    <div class="col-lg-10">
        <?php if (isset($home) && $home): ?>
            <?php if ($home->imagen && file_exists("uploads/".$home->imagen)): ?>
                <img src='uploads/<?= $home->imagen ?>' class='img-fluid rounded-4 mb-4 w-100 shadow-sm' style='max-height:400px; object-fit:cover;'>
            <?php endif; ?>
            
            <div class='p-5 mb-4 bg-white rounded-4 shadow-sm'>
                <h1 class='display-4 fw-bold mb-4 text-center text-body'><?= htmlspecialchars($home->titulo) ?></h1>
                <div class='contenido-web lead'><?= $home->contenido ?></div>
            </div>
            
            <div class='text-center mb-5'>
                <a href='index.php?c=Pagina&a=ver&slug=<?= $home->slug ?>' class='btn btn-primary btn-lg rounded-pill px-5 shadow'>Leer Artículo Completo</a>
            </div>
        <?php else: ?>
            <div class="alert alert-info text-center py-5">
                <h3>¡Bienvenido al Blog MVC!</h3>
                <p>Aún no has definido una página de inicio en la base de datos.</p>
                <a href="index.php?c=Pagina&a=blog" class="btn btn-outline-primary">Ir al Blog</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'views/layouts/footer.php'; ?>