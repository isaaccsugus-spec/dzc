<?php include 'views/layouts/header.php'; ?>

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>👥 Gestión de Usuarios</h2>
        <a href="index.php?c=Dashboard&a=index" class="btn btn-outline-secondary rounded-pill">
            <i class="bi bi-arrow-left"></i> Volver al Panel
        </a>
    </div>

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4">ID</th>
                        <th>Avatar</th>
                        <th>Usuario</th>
                        <th>Email</th>
                        <th>Rol</th>
                        <th class="text-end pe-4">Acciones</th>
                    </tr>
                </thead>
                <tbody class="bg-white">
                    <?php if (isset($usuarios) && count($usuarios) > 0): ?>
                        <?php foreach($usuarios as $u): ?>
                        <tr>
                            <td class="ps-4 text-muted">#<?= $u->id ?></td>
                            <td>
                                <?php if ($u->avatar && file_exists("uploads/".$u->avatar)): ?>
                                    <img src="uploads/<?= $u->avatar ?>" class="rounded-circle" style="width: 35px; height: 35px; object-fit: cover;">
                                <?php else: ?>
                                    <div class="bg-secondary text-white rounded-circle d-flex justify-content-center align-items-center fw-bold" style="width: 35px; height: 35px; font-size: 0.8rem;">
                                        <?= strtoupper(substr($u->username, 0, 1)) ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="fw-bold"><?= htmlspecialchars($u->username) ?></div>
                                <small class="text-muted"><?= htmlspecialchars($u->nombre . ' ' . $u->apellidos) ?></small>
                            </td>
                            <td class="text-muted small"><?= htmlspecialchars($u->email) ?></td>
                            
                            <td>
                                <?php if ($u->id != 1 && $u->id != $_SESSION['user_id']): ?>
                                    <form action="index.php?c=Usuario&a=cambiarRol" method="POST">
                                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                        <input type="hidden" name="id" value="<?= $u->id ?>">
                                        <select name="rol" class="form-select form-select-sm border-0 bg-light" onchange="this.form.submit()" style="width: 100px; cursor: pointer;">
                                            <option value="user" <?= $u->rol === 'user' ? 'selected' : '' ?>>Usuario</option>
                                            <option value="admin" <?= $u->rol === 'admin' ? 'selected' : '' ?>>Admin</option>
                                        </select>
                                    </form>
                                <?php else: ?>
                                    <span class="badge <?= $u->rol === 'admin' ? 'bg-danger' : 'bg-secondary' ?> rounded-pill">
                                        <?= strtoupper($u->rol) ?>
                                    </span>
                                <?php endif; ?>
                            </td>

                            <td class="text-end pe-4">
                                <?php if($_SESSION['user_id'] != $u->id && $u->id != 1): ?>
                                    <form action="index.php?c=Usuario&a=eliminar" method="POST" onsubmit="return confirm('¿Estás seguro de eliminar a este usuario? Se borrarán todas sus páginas y comentarios.');" style="display:inline;">
                                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                        <input type="hidden" name="id" value="<?= $u->id ?>">
                                        <button type="submit" class="btn btn-sm btn-light text-danger rounded-circle" title="Eliminar Usuario"><i class="bi bi-trash"></i></button>
                                    </form>
                                <?php else: ?>
                                    <small class="text-muted fst-italic">Protegido</small>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="7" class="text-center py-4">No hay usuarios registrados.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include 'views/layouts/footer.php'; ?>