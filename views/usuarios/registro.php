<?php include 'views/layouts/header.php'; ?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow border-0 rounded-4">
            <div class="card-header bg-success text-white text-center py-3 rounded-top-4">
                <h4 class="mb-0 fw-bold">Crear Cuenta Nueva</h4>
            </div>
            <div class="card-body p-4">
                <?php if(isset($error) && $error): ?>
                    <div class="alert alert-danger rounded-pill text-center"><?= $error ?></div>
                <?php endif; ?>
                
                <form method="POST" action="index.php?c=Usuario&a=registro">
                    
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="form-label small text-muted text-uppercase fw-bold">Nombre</label>
                            <input type="text" name="nombre" class="form-control" required value="<?= isset($_POST['nombre']) ? htmlspecialchars($_POST['nombre']) : '' ?>">
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label small text-muted text-uppercase fw-bold">Apellidos</label>
                            <input type="text" name="apellidos" class="form-control" required value="<?= isset($_POST['apellidos']) ? htmlspecialchars($_POST['apellidos']) : '' ?>">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small text-muted text-uppercase fw-bold">Email</label>
                        <input type="email" name="email" class="form-control" required value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label small text-muted text-uppercase fw-bold">Usuario (Login)</label>
                        <input type="text" name="username" class="form-control" required value="<?= isset($_POST['username']) ? htmlspecialchars($_POST['username']) : '' ?>">
                    </div>

                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="form-label small text-muted text-uppercase fw-bold">Contraseña</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label small text-muted text-uppercase fw-bold">Repetir</label>
                            <input type="password" name="confirm_password" class="form-control" required>
                        </div>
                    </div>

                    <hr>
                    <div class="mb-4">
                        <label class="form-label text-muted small">Código de Administrador (Opcional)</label>
                        <input type="password" name="codigo_admin" class="form-control form-control-sm" placeholder="Solo si tienes el código secreto">
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-success rounded-pill fw-bold shadow-sm">Registrarse</button>
                        <a href="index.php?c=Usuario&a=login" class="btn btn-outline-secondary rounded-pill border-0">Ya tengo cuenta</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include 'views/layouts/footer.php'; ?>