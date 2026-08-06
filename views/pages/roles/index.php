<?php
$roles = $roles ?? [];
?>
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Nama Role</th>
                        <th>Slug</th>
                        <th>Deskripsi</th>
                        <th>Jumlah User</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($roles)): ?>
                        <tr>
                            <td colspan="4">
                                <div class="empty-state">
                                    <i class="bi bi-shield-fill" style="font-size:2rem;color:var(--text-tertiary);"></i>
                                    <p style="color:var(--text-tertiary);margin-top:8px;">Belum ada data role</p>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($roles as $role): ?>
                            <tr>
                                <td class="fw-semibold"><?= htmlspecialchars($role['name']) ?></td>
                                <td><code><?= htmlspecialchars($role['slug'] ?? '-') ?></code></td>
                                <td><small class="text-tertiary"><?= htmlspecialchars($role['description'] ?? '-') ?></small></td>
                                <td>
                                    <span class="badge badge-soft"><?= (int)($role['user_count'] ?? 0) ?> user</span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
