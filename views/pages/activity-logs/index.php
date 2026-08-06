<?php
$data = $data ?? [];
$pagination = $pagination ?? [];
$filters = $filters ?? [];
$modules = $modules ?? [];
$actions = $actions ?? [];
$users = $users ?? [];
$search_q = $search_q ?? '';
?>


<div class="d-flex justify-content-end mb-3">
    <form method="GET" action="<?= BASE_URL ?>/activity-logs" class="d-flex gap-2" style="max-width: 400px; width: 100%;">
        <input type="text" name="q" class="form-control" placeholder="Cari kata... (deskripsi, module, aksi, user)" value="<?= htmlspecialchars($search_q) ?>" style="border-radius: 10px; font-size: 13px; height: 38px;">
        <button type="submit" class="btn btn-primary btn-sm" style="border-radius: 10px; height: 38px;"><i class="bi bi-search"></i> Cari</button>
        <?php if ($search_q !== ''): ?>
            <a href="<?= BASE_URL ?>/activity-logs" class="btn btn-outline btn-sm" title="Bersihkan pencarian" style="border-radius: 10px; height: 38px;"><i class="bi bi-x-lg"></i></a>
        <?php endif; ?>
    </form>
</div>


<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0" style="font-size:var(--text-sm);">
                <thead>
                    <tr>
                        <th>Waktu</th>
                        <th>User</th>
                        <th>Module</th>
                        <th>Aksi</th>
                        <th>Deskripsi</th>
                        <th>IP</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($data)): ?>
                        <tr>
                            <td colspan="6">
                                <div class="empty-state">
                                    <i class="bi bi-activity" style="font-size:2rem;color:var(--text-tertiary);"></i>
                                    <p style="color:var(--text-tertiary);margin-top:8px;">Belum ada log aktivitas</p>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($data as $log): ?>
                            <tr>
                                <td>
                                    <small class="text-tertiary"><?= date('d M Y H:i:s', strtotime($log['created_at'])) ?></small>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <?php if (!empty($log['user_avatar'])): ?>
                                            <img src="<?= BASE_URL ?>/uploads/<?= $log['user_avatar'] ?>" alt="" style="width:24px;height:24px;border-radius:50%;object-fit:cover;">
                                        <?php else: ?>
                                            <div style="width:24px;height:24px;border-radius:50%;background:var(--tvri-blue);color:white;display:flex;align-items:center;justify-content:center;font-size:10px;font-weight:600;">
                                                <?= strtoupper(substr($log['user_name'] ?? 'U', 0, 1)) ?>
                                            </div>
                                        <?php endif; ?>
                                        <small><?= htmlspecialchars($log['user_name'] ?? 'System') ?></small>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge badge-soft"><?= htmlspecialchars(ucfirst($log['module'] ?? '-')) ?></span>
                                </td>
                                <td>
                                    <?php
                                    $actionClass = match($log['action']) {
                                        'create' => 'badge-success',
                                        'update' => 'badge-info',
                                        'delete' => 'badge-danger',
                                        'login' => 'badge-primary',
                                        'logout' => 'badge-secondary',
                                        default => 'badge-soft'
                                    };
                                    ?>
                                    <span class="badge <?= $actionClass ?>"><?= htmlspecialchars(ucfirst($log['action'] ?? '-')) ?></span>
                                </td>
                                <td>
                                    <small class="text-tertiary"><?= htmlspecialchars($log['description'] ?? '-') ?></small>
                                </td>
                                <td><small class="text-tertiary"><?= htmlspecialchars($log['ip_address'] ?? '-') ?></small></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>


<?php if (!empty($pagination) && ($pagination['total_pages'] ?? 0) > 1): ?>
<div class="d-flex justify-content-center mt-4">
    <nav>
        <ul class="pagination">
            <?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?>
                <li class="page-item <?= ($pagination['current_page'] ?? 1) == $i ? 'active' : '' ?>">
                    <a class="page-link" href="?page=<?= $i ?><?= $search_q !== '' ? '&q=' . urlencode($search_q) : '' ?>"><?= $i ?></a>
                </li>
            <?php endfor; ?>
        </ul>
    </nav>
</div>
<?php endif; ?>
