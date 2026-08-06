<?php
$planning = $planning ?? [];
$tags = $tags ?? [];
?>

<div class="row">
    <div class="col-12 col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5><?= Security::escape($planning['judul']) ?></h5>
                <span><?= statusLabel($planning['status']) ?></span>
            </div>
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-6 col-md-3">
                        <div style="font-size: 0.75rem; color: var(--gray-400);">Platform</div>
                        <div style="font-size: 0.875rem; font-weight: 500;">
                            <i class="<?= iconClass($planning['platform_icon'] ?? 'bi-globe') ?>"></i>
                            <?= Security::escape($planning['platform_name'] ?? '-') ?>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div style="font-size: 0.75rem; color: var(--gray-400);">Dibuat Oleh</div>
                        <div style="font-size: 0.875rem; font-weight: 500;"><?= Security::escape($planning['creator_name'] ?? '-') ?></div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div style="font-size: 0.75rem; color: var(--gray-400);">Tanggal</div>
                        <div style="font-size: 0.875rem; font-weight: 500;"><?= formatTanggal($planning['created_at'], 'd M Y') ?></div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div style="font-size: 0.75rem; color: var(--gray-400);">Prioritas</div>
                        <div style="font-size: 0.875rem; font-weight: 500;"><?= Security::escape($planning['priority']) ?></div>
                    </div>
                </div>

                <?php if (!empty($planning['caption'])): ?>
                    <div class="mb-4">
                        <div style="font-size: 0.75rem; color: var(--gray-400); margin-bottom: 4px;">Caption</div>
                        <div style="background: var(--gray-50); padding: 16px; border-radius: 8px; font-size: 0.875rem; white-space: pre-wrap;">
                            <?= Security::escape($planning['caption']) ?>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if (!empty($tags)): ?>
                    <div class="mb-4">
                        <div style="font-size: 0.75rem; color: var(--gray-400); margin-bottom: 8px;">Tags</div>
                        <div class="d-flex gap-xs flex-wrap">
                            <?php foreach ($tags as $tag): ?>
                                <span class="badge" style="background: <?= Security::escape($tag['color'] ?? '#6c757d') ?>20; color: <?= Security::escape($tag['color'] ?? '#6c757d') ?>;">
                                    <?= Security::escape($tag['name']) ?>
                                </span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-4">
        
        <div class="card">
            <div class="card-header">
                <h5>Aksi Approval</h5>
            </div>
            <div class="card-body">
                <?php if (in_array($planning['status'], ['review', 'approved'])): ?>
                    
                    <form method="POST" action="<?= BASE_URL ?>/approval/<?= $planning['id'] ?>/approve" class="mb-3">
                        <?= Session::csrfField() ?>
                        <button type="submit" class="btn btn-success w-100" onclick="return confirm('Setujui konten ini?')">
                            <i class="bi bi-check-lg"></i> 
                            <?= Session::get('user_role_slug') === 'admin' ? 'Setujui & Jadwalkan' : 'Setujui & Jadwalkan' ?>
                        </button>
                    </form>

                    
                    <form method="POST" action="<?= BASE_URL ?>/approval/<?= $planning['id'] ?>/revision" class="mb-3">
                        <?= Session::csrfField() ?>
                        <div class="form-group">
                            <label class="form-label">Catatan Revisi</label>
                            <textarea name="catatan" class="form-control" rows="3" required placeholder="Jelaskan apa yang perlu direvisi..."></textarea>
                        </div>
                        <button type="submit" class="btn btn-warning w-100" onclick="return confirm('Minta revisi konten ini?')">
                            <i class="bi bi-arrow-counterclockwise"></i> Minta Revisi
                        </button>
                    </form>

                    
                    <form method="POST" action="<?= BASE_URL ?>/approval/<?= $planning['id'] ?>/reject">
                        <?= Session::csrfField() ?>
                        <div class="form-group">
                            <label class="form-label">Alasan Penolakan</label>
                            <textarea name="alasan" class="form-control" rows="3" required placeholder="Jelaskan alasan penolakan..."></textarea>
                        </div>
                        <button type="submit" class="btn btn-danger w-100" onclick="return confirm('Tolak konten ini?')">
                            <i class="bi bi-x-lg"></i> Tolak
                        </button>
                    </form>
                <?php elseif ($planning['status'] === 'revision'): ?>
                    <div class="alert" style="background: var(--warning-bg); border: none; border-radius: 8px;">
                        <i class="bi bi-arrow-counterclockwise"></i> Konten ini perlu revisi.
                        <?php if (!empty($planning['revision_note'])): ?>
                            <div style="margin-top: 8px; font-size: 0.8125rem;"><?= Security::escape($planning['revision_note']) ?></div>
                        <?php endif; ?>
                    </div>
                <?php elseif ($planning['status'] === 'scheduled'): ?>
                    <div class="alert" style="background: var(--info-bg); border: none; border-radius: 8px;">
                        <i class="bi bi-clock"></i> Konten sudah dijadwalkan.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
