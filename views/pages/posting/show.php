<?php
$posting = $posting ?? [];
$logs = $logs ?? [];
?>
<div class="row g-4">
    
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">Detail Konten</h6>
            </div>
            <div class="card-body">
                <table class="table table-sm table-borderless">
                    <tr>
                        <td class="text-tertiary" style="width:120px;">Judul</td>
                        <td class="fw-semibold"><?= htmlspecialchars($posting['judul'] ?? '-') ?></td>
                    </tr>
                    <tr>
                        <td class="text-tertiary">Platform</td>
                        <td>
                            <?php if (!empty($posting['platform_icon'])): ?>
                                <i class="<?= iconClass($posting['platform_icon']) ?>"></i>
                            <?php endif; ?>
                            <?= htmlspecialchars($posting['platform_name'] ?? '-') ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="text-tertiary">Status</td>
                        <td>
                            <?php $cls = match($posting['status'] ?? '') {
                                'draft' => 'badge-secondary',
                                'review' => 'badge-warning',
                                'approved' => 'badge-info',
                                'scheduled' => 'badge-primary',
                                'posting' => 'badge-info',
                                'success' => 'badge-success',
                                'failed' => 'badge-danger',
                                default => 'badge-secondary'
                            }; ?>
                            <span class="badge <?= $cls ?>"><?= ucfirst($posting['status'] ?? '-') ?></span>
                        </td>
                    </tr>
                    <tr>
                        <td class="text-tertiary">Caption</td>
                        <td><small><?= nl2br(htmlspecialchars($posting['caption'] ?? '-')) ?></small></td>
                    </tr>
                    <tr>
                        <td class="text-tertiary">Jadwal</td>
                        <td><?= !empty($posting['scheduled_at']) ? date('d M Y H:i', strtotime($posting['scheduled_at'])) : '-' ?></td>
                    </tr>
                    <tr>
                        <td class="text-tertiary">Jenis Konten</td>
                        <td><?= htmlspecialchars($posting['jenis_konten'] ?? '-') ?></td>
                    </tr>
                    <?php if (!empty($posting['media_path'])): ?>
                    <tr>
                        <td class="text-tertiary">Media</td>
                        <td>
                            <?php if (($posting['media_type'] ?? '') === 'image'): ?>
                                <img src="<?= BASE_URL ?>/uploads/<?= $posting['media_path'] ?>" alt="" style="max-width:200px;border-radius:8px;">
                            <?php else: ?>
                                <a href="<?= BASE_URL ?>/uploads/<?= $posting['media_path'] ?>" target="_blank" class="btn btn-sm btn-outline">
                                    <i class="bi bi-paperclip"></i> Lihat File
                                </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endif; ?>
                </table>
            </div>
        </div>
    </div>

    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">Log Posting</h6>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    <?php if (empty($logs)): ?>
                        <div class="list-group-item text-center text-tertiary py-4">
                            <i class="bi bi-clock-history" style="font-size:1.5rem;"></i>
                            <p class="mt-2 mb-0 small">Belum ada log</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($logs as $log): ?>
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <small class="text-tertiary"><?= date('d M H:i', strtotime($log['created_at'])) ?></small>
                                        <div class="mt-1">
                                            <?php $lc = match($log['status'] ?? '') {
                                                'success' => 'badge-success',
                                                'failed' => 'badge-danger',
                                                default => 'badge-secondary'
                                            }; ?>
                                            <span class="badge <?= $lc ?>"><?= ucfirst($log['status'] ?? '-') ?></span>
                                        </div>
                                    </div>
                                </div>
                                <?php if (!empty($log['message'])): ?>
                                    <small class="text-tertiary d-block mt-1"><?= htmlspecialchars($log['message']) ?></small>
                                <?php endif; ?>
                                <small class="text-tertiary d-block"><?= htmlspecialchars($log['user_name'] ?? '-') ?></small>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="mt-3">
    <a href="<?= BASE_URL ?>/posting" class="btn btn-outline btn-sm">
        <i class="bi bi-arrow-left"></i> Kembali
    </a>
</div>
