<?php include 'views/layouts/header.php'; ?>
<div class="row justify-content-center">
    <div class="col-md-10">
        <h2 class="mb-4">Crear Nueva Página</h2>
        <?php if(isset($error) && $error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>

        <form method="POST" action="index.php?c=Dashboard&a=crear" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Título</label>
                    <input type="text" name="titulo" class="form-control" required>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Categoría</label>
                    <select name="categoria_id" class="form-select">
                        <?php foreach($categorias as $cat): ?>
                            <option value="<?= $cat->id ?>"><?= htmlspecialchars($cat->nombre) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Estado</label>
                    <select name="estado" class="form-select">
                        <option value="publicado">Publicado</option>
                        <option value="borrador">Borrador</option>
                    </select>
                </div>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Imagen (Opcional)</label>
                <input type="file" name="imagen" class="form-control" accept="image/*">
            </div>
            
            <div class="mb-3">
                <label class="form-label">Contenido</label>
                <textarea name="contenido" id="contenido" class="form-control"></textarea>
            </div>
            
            <button type="submit" class="btn btn-primary">Guardar</button>
            <a href="index.php?c=Dashboard&a=index" class="btn btn-secondary">Cancelar</a>
        </form>
    </div>
</div>
<?php include 'views/layouts/footer.php'; ?>