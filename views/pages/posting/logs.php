<?php
$data = $data ?? [];
?>
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Waktu</th>
                        <th>Konten</th>
                        <th>Platform</th>
                        <th>User</th>
                        <th>Status</th>
                        <th>Pesan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($data)): ?>
                        <tr>
                            <td colspan="6">
                                <div class="empty-state">
                                    <i class="bi bi-journal-text" style="font-size:2rem;color:var(--text-tertiary);"></i>
                                    <p style="color:var(--text-tertiary);margin-top:8px;">Belum ada log posting</p>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($data as $row): ?>
                            <tr>
                                <td>
                                    <small><?= date('d M Y H:i:s', strtotime($row['created_at'])) ?></small>
                                </td>
                                <td>
                                    <small><?= htmlspecialchars($row['judul'] ?? '-') ?></small>
                                </td>
                                <td>
                                    <?= htmlspecialchars($row['platform_name'] ?? '-') ?>
                                </td>
                                <td>
                                    <small><?= htmlspecialchars($row['user_name'] ?? '-') ?></small>
                                </td>
                                <td>
                                    <?php $cls = match($row['status'] ?? '') {
                                        'success' => 'badge-success',
                                        'failed' => 'badge-danger',
                                        default => 'badge-secondary'
                                    }; ?>
                                    <span class="badge <?= $cls ?>"><?= ucfirst($row['status'] ?? '-') ?></span>
                                </td>
                                <td>
                                    <small class="text-tertiary"><?= htmlspecialchars($row['message'] ?? $row['response'] ?? '-') ?></small>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<div class="mt-3">
    <a href="<?= BASE_URL ?>/posting" class="btn btn-outline btn-sm">
        <i class="bi bi-arrow-left"></i> Kembali ke Posting
    </a>
</div>
