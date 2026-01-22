<?php include 'views/layouts/header.php'; ?>

<div class="row justify-content-center">
    <div class="col-md-8 col-lg-6">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="card-header bg-primary text-white p-4 text-center border-0">
                <h3 class="mb-0 fw-bold"><i class="bi bi-person-gear me-2"></i> Mi Perfil</h3>
            </div>
            
            <div class="card-body p-5 bg-white">
                <?php if(isset($mensaje) && $mensaje): ?>
                    <div class="alert alert-<?= $tipo_mensaje ?> text-center rounded-pill mb-4 shadow-sm">
                        <?= $mensaje ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="index.php?c=Usuario&a=perfil" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                    
                    <input type="hidden" name="avatar_actual" value="<?= $usuario->avatar ?>">

                    <div class="text-center mb-4 position-relative">
                        <div class="d-inline-block position-relative">
                            <?php if ($usuario->avatar && file_exists("uploads/".$usuario->avatar)): ?>
                                <img src="uploads/<?= $usuario->avatar ?>" class="rounded-circle shadow object-fit-cover" style="width: 130px; height: 130px; border: 4px solid white;">
                            <?php else: ?>
                                <div class="rounded-circle bg-light text-primary d-flex align-items-center justify-content-center mx-auto shadow" style="width: 130px; height: 130px; font-size: 3.5rem; border: 4px solid white;">
                                    <?= strtoupper(substr($usuario->username, 0, 1)) ?>
                                </div>
                            <?php endif; ?>
                            
                            <label for="avatarInput" class="position-absolute bottom-0 end-0 bg-white text-primary rounded-circle shadow p-2" style="cursor: pointer; width: 40px; height: 40px; display: grid; place-items: center;">
                                <i class="bi bi-camera-fill"></i>
                            </label>
                            <input type="file" name="avatar" id="avatarInput" class="d-none" accept="image/*" onchange="previewImage(this)">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold text-muted small text-uppercase">Nombre de Usuario</label>
                        <input type="text" name="username" class="form-control bg-light border-0" value="<?= htmlspecialchars($usuario->username) ?>" required>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold text-muted small text-uppercase">Nueva Contraseña</label>
                        <input type="password" name="password" class="form-control bg-light border-0" placeholder="Dejar vacío para mantener la actual">
                        <div class="form-text text-muted small">Mínimo 8 caracteres, 1 mayúscula y 1 número.</div>
                    </div>

                    <div class="d-grid gap-2 mt-5">
                        <button type="submit" class="btn btn-primary rounded-pill py-2 fw-bold shadow-sm">Guardar Cambios</button>
                        <a href="index.php?c=Dashboard&a=index" class="btn btn-outline-secondary rounded-pill py-2 border-0">Volver al Dashboard</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include 'views/layouts/footer.php'; ?>

<script>
function previewImage(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            var img = input.parentElement.querySelector('img');
            // Si el usuario no tenía foto (era un div con letras), buscamos o creamos la img
            if (!img) {
                // Si no hay tag img, recargamos la página para simplificar, 
                // o podríamos reemplazar el div con JS, pero visualmente ya se ve que ha seleccionado algo.
                // Lo más sencillo es dejar que se actualice al hacer submit.
            } else {
                img.src = e.target.result;
            }
        }
        reader.readAsDataURL(input.files[0]);
    }
}
</script>