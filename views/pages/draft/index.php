<?php
$data = $data ?? [];
?>
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Judul Konten</th>
                        <th>Platform</th>
                        <th>Jadwal</th>
                        <th>Status</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($data)): ?>
                        <tr>
                            <td colspan="5">
                                <div class="empty-state">
                                    <i class="bi bi-file-earmark-text" style="font-size:2rem;color:var(--text-tertiary);"></i>
                                    <p style="color:var(--text-tertiary);margin-top:8px;">Belum ada draft konten</p>
                                    <a href="<?= BASE_URL ?>/planning/create" class="btn btn-primary btn-sm mt-2">
                                        <i class="bi bi-plus"></i> Buat Draft Baru
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($data as $row): ?>
                            <tr>
                                <td>
                                    <div class="fw-semibold"><?= htmlspecialchars($row['judul']) ?></div>
                                    <small class="text-tertiary"><?= htmlspecialchars($row['jenis_konten'] ?? '-') ?></small>
                                </td>
                                <td>
                                    <?php if (!empty($row['platform_icon'])): ?>
                                        <i class="<?= iconClass($row['platform_icon']) ?>"></i>
                                    <?php endif; ?>
                                    <?= htmlspecialchars($row['platform_name'] ?? '-') ?>
                                </td>
                                <td>
                                    <?= !empty($row['scheduled_at']) ? date('d M Y H:i', strtotime($row['scheduled_at'])) : '<span class="text-tertiary">Belum dijadwalkan</span>' ?>
                                </td>
                                <td>
                                    <span class="badge badge-info">Draft</span>
                                </td>
                                <td class="text-end">
                                    <div class="btn-group">
                                        <a href="<?= BASE_URL ?>/draft/<?= $row['id'] ?>/edit" class="btn btn-sm btn-ghost" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <button class="btn btn-sm btn-ghost" onclick="if(confirm('Kirim draft ini ke review?')){document.getElementById('submit-<?= $row['id'] ?>').submit()}" title="Kirim ke Review">
                                            <i class="bi bi-send"></i>
                                        </button>
                                    </div>
                                    <form id="submit-<?= $row['id'] ?>" method="POST" action="<?= BASE_URL ?>/draft/<?= $row['id'] ?>/submit" style="display:none;">
                                        <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= Session::csrfToken() ?>">
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
