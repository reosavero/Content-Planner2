<?php
$data = $data ?? [];
?>
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Platform</th>
                        <th>Slug</th>
                        <th>Urutan</th>
                        <th>Status</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($data)): ?>
                        <tr>
                            <td colspan="5">
                                <div class="empty-state">
                                    <i class="bi bi-share" style="font-size:2rem;color:var(--text-tertiary);"></i>
                                    <p style="color:var(--text-tertiary);margin-top:8px;">Belum ada platform</p>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($data as $row): ?>
                            <tr>
                                <td>
                                    <i class="<?= iconClass($row['icon'] ?? 'bi-globe') ?>" style="color:<?= $row['color'] ?? 'var(--text-secondary)' ?>"></i>
                                    <span class="fw-semibold ms-1"><?= htmlspecialchars($row['name']) ?></span>
                                </td>
                                <td><code><?= htmlspecialchars($row['slug'] ?? '-') ?></code></td>
                                <td><small><?= $row['sort_order'] ?? 0 ?></small></td>
                                <td>
                                    <span class="badge <?= ($row['is_active'] ?? 1) ? 'badge-success' : 'badge-secondary' ?>">
                                        <?= ($row['is_active'] ?? 1) ? 'Aktif' : 'Nonaktif' ?>
                                    </span>
                                </td>
                                <td class="text-end">
                                    <button class="btn btn-sm btn-ghost" onclick="togglePlatform(<?= $row['id'] ?>)" title="Toggle Aktif">
                                        <i class="bi bi-toggle-on"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function togglePlatform(id) {
    var form = new FormData();
    form.append('<?= CSRF_TOKEN_NAME ?>', '<?= Session::csrfToken() ?>');
    fetch('<?= BASE_URL ?>/master/platform/' + id + '/toggle', {
        method: 'POST',
        body: form
    }).then(r => r.json()).then(d => {
        if (d.success) location.reload();
        else alert(d.message || 'Gagal');
    }).catch(() => location.reload());
}
</script>
