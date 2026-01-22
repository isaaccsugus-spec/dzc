<?php include 'views/layouts/header.php'; ?>

<div class="row justify-content-center">
    <div class="col-md-5">
        <div class="card shadow border-0 rounded-4">
            <div class="card-header bg-primary text-white text-center py-3 rounded-top-4">
                <h4 class="mb-0 fw-bold">Iniciar Sesión</h4>
            </div>
            <div class="card-body p-4">
                <?php if(isset($error) && $error): ?>
                    <div class="alert alert-danger rounded-pill text-center"><?= $error ?></div>
                <?php endif; ?>
                
                <form method="POST" action="index.php?c=Usuario&a=login">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">

                    <div class="mb-3">
                        <label class="form-label fw-bold">Usuario</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="bi bi-person-fill"></i></span>
                            <input type="text" name="username" class="form-control border-start-0" required placeholder="Tu nombre de usuario">
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label fw-bold">Contraseña</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="bi bi-lock-fill"></i></span>
                            <input type="password" name="password" class="form-control border-start-0" required placeholder="******">
                        </div>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary rounded-pill py-2 fw-bold shadow-sm">Entrar</button>
                    </div>
                </form>
            </div>
            <div class="card-footer bg-white text-center py-3 border-0">
                <small class="text-muted">¿No tienes cuenta? <a href="index.php?c=Usuario&a=registro" class="text-primary fw-bold text-decoration-none">Regístrate aquí</a></small>
            </div>
        </div>
    </div>
</div>

<?php include 'views/layouts/footer.php'; ?>