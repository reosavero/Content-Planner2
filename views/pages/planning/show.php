<?php
$planning = $planning ?? [];
$tags = $tags ?? [];
$media = $media ?? [];
$logs = $logs ?? [];
$userRole = Session::get('user_role_slug');
$userId = Session::get('user_id');

$statusClass = match($planning['status'] ?? '') {
    'draft' => 'status-draft',
    'review' => 'status-review',
    'approved' => 'status-approved',
    'revision' => 'status-revision',
    'scheduled' => 'status-scheduled',
    'posting' => 'status-posting',
    'success' => 'status-success',
    'failed' => 'status-failed',
    'cancelled' => 'status-cancelled',
    default => 'badge-secondary'
};
?>


<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3" style="padding-bottom: 16px; border-bottom: 1px solid #e2e8f0;">
    <div>
        <a href="<?= BASE_URL ?>/planning?view=table" class="text-secondary text-sm font-semibold text-decoration-none d-inline-flex align-items-center mb-1">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Master Tabel Planning
        </a>
        <h2 class="font-bold text-2xl m-0" style="color: #0f172a;">Detail Planning Konten</h2>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= BASE_URL ?>/planning/<?= $planning['id'] ?>/edit" class="btn btn-primary" style="border-radius: 10px; font-weight: 600;">
            <i class="bi bi-pencil me-1"></i> Edit Planning Ini
        </a>
        <button type="button" class="btn btn-outline-danger" style="border-radius: 10px; font-weight: 600;" onclick="deleteDetailPlanning(<?= $planning['id'] ?>)">
            <i class="bi bi-trash me-1"></i> Hapus
        </button>
    </div>
</div>

<div class="row g-4">
    
    <div class="col-12 col-lg-8">
        
        <div class="card mb-4" style="border-radius: 16px; border: 1px solid #e2e8f0; background: #ffffff; box-shadow: 0 4px 12px rgba(0,0,0,0.03); overflow: hidden;">
            <div class="card-header" style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; padding: 16px 20px;">
                <div class="d-flex align-items-center gap-2">
                    <div style="width: 32px; height: 32px; border-radius: 8px; background: #e8eaf6; color: #1a237e; display: flex; align-items: center; justify-content: center; font-size: 15px;">
                        <i class="bi bi-file-text-fill"></i>
                    </div>
                    <div>
                        <h5 class="m-0" style="font-size: 15px; font-weight: 700; color: #0f172a;">Detail Utama Konten</h5>
                        <span style="font-size: 11px; color: #64748b;">Rincian naskah, instruksi, dan tautan pengerjaan</span>
                    </div>
                </div>
            </div>

            <div class="card-body" style="padding: 24px;">
                
                <div class="d-flex align-items-center gap-2 mb-3 flex-wrap">
                    <span class="badge <?= $statusClass ?>" style="padding: 6px 14px; font-size: 11px; font-weight: 700; border-radius: 10px; text-transform: uppercase;">
                        <i class="bi bi-info-circle me-1"></i> Status: <?= ucfirst($planning['status'] ?? 'draft') ?>
                    </span>
                    <span class="badge" style="background: <?= htmlspecialchars($planning['platform_color'] ?? '#1a237e') ?>; color: white; padding: 6px 14px; font-size: 11px; font-weight: 700; border-radius: 10px;">
                        <i class="<?= iconClass($planning['platform_icon'] ?? 'bi-globe') ?> me-1"></i>
                        <?= htmlspecialchars($planning['platform_name'] ?? 'Platform') ?>
                    </span>
                    <?php if (!empty($planning['kategori_name'])): ?>
                        <span class="badge" style="background: <?= htmlspecialchars($planning['kategori_color'] ?? '#3f51b5') ?>; color: white; padding: 6px 14px; font-size: 11px; font-weight: 700; border-radius: 10px;">
                            <i class="bi bi-tag-fill me-1"></i> <?= htmlspecialchars($planning['kategori_name']) ?>
                        </span>
                    <?php endif; ?>
                    <?php if (!empty($planning['jenis_konten_name'])): ?>
                        <span class="badge badge-outline" style="padding: 6px 14px; font-size: 11px; font-weight: 700; border-radius: 10px;">
                            <i class="bi bi-file-earmark-code me-1"></i> <?= htmlspecialchars($planning['jenis_konten_name']) ?>
                        </span>
                    <?php endif; ?>
                </div>

                
                <h3 class="font-bold text-xl mb-4" style="color: #0f172a; font-size: 20px; line-height: 1.4; font-weight: 700;">
                    <?= htmlspecialchars($planning['judul'] ?? '(Tanpa Judul)') ?>
                </h3>

                
                <div class="p-3 mb-4 rounded-xl d-flex align-items-center justify-content-between flex-wrap gap-2" style="background: #f0f4fe; border: 1px solid #c7d2fe; border-radius: 12px;">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-arrow-repeat text-primary font-bold" style="font-size: 16px;"></i>
                        <span style="font-size: 12px; color: #475569; font-weight: 700; text-transform: uppercase;">Tahap Workflow:</span>
                        <span class="badge bg-primary text-white font-bold" style="padding: 5px 12px; border-radius: 8px; font-size: 11px; letter-spacing: 0.5px;">
                            <?= strtoupper($planning['status'] ?? 'DRAFT') ?>
                        </span>
                    </div>
                    <div class="d-flex gap-2 flex-wrap">
                        <?php if (($planning['status'] ?? '') === 'draft'): ?>
                            <button onclick="updateWorkflowStatus('review')" class="btn btn-sm btn-primary" style="border-radius: 8px; font-weight: 600;">
                                <i class="bi bi-send me-1"></i> Kirim ke Review
                            </button>
                        <?php endif; ?>

                        <?php if (($planning['status'] ?? '') === 'review' && in_array($userRole, ['superadmin', 'admin'])): ?>
                            <button onclick="updateWorkflowStatus('approved')" class="btn btn-sm btn-success" style="background:#4caf50; border-color:#4caf50; border-radius: 8px; color:white; font-weight: 600;">
                                <i class="bi bi-check-circle me-1"></i> Setujui (Approve)
                            </button>
                            <button onclick="updateWorkflowStatus('revision')" class="btn btn-sm btn-warning" style="border-radius: 8px; font-weight: 600;">
                                <i class="bi bi-arrow-counterclockwise me-1"></i> Minta Revisi
                            </button>
                        <?php endif; ?>

                        <?php if (($planning['status'] ?? '') === 'approved' && in_array($userRole, ['superadmin', 'admin'])): ?>
                            <button onclick="updateWorkflowStatus('scheduled')" class="btn btn-sm btn-info" style="background:#0288d1; border-color:#0288d1; color:white; border-radius: 8px; font-weight: 600;">
                                <i class="bi bi-clock-history me-1"></i> Jadwalkan Posting
                            </button>
                        <?php endif; ?>
                    </div>
                </div>

                
                <div class="mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <label style="font-size: 12px; font-weight: 700; text-transform: uppercase; color: #334155; margin: 0;">
                            <i class="bi bi-card-text me-1" style="color: #1a237e;"></i> Naskah / Caption Konten
                        </label>
                        <?php if (!empty($planning['caption'])): ?>
                            <button class="btn btn-xs btn-outline" style="border-radius: 8px; font-size: 11px; padding: 3px 10px; font-weight: 600;" onclick="navigator.clipboard.writeText(document.getElementById('captionText').innerText); alert('Caption berhasil disalin');">
                                <i class="bi bi-copy me-1"></i> Salin Naskah
                            </button>
                        <?php endif; ?>
                    </div>
                    <div class="p-3 rounded-lg border text-sm" id="captionText" style="white-space: pre-wrap; min-height: 100px; color: #1e293b; line-height: 1.6; border-color: #cbd5e1 !important; background: #fafafa; border-radius: 12px; font-size: 13.5px;">
                        <?= !empty($planning['caption']) ? htmlspecialchars($planning['caption']) : '<span style="color: #94a3b8; font-style: italic;">(Belum ada naskah caption)</span>' ?>
                    </div>
                </div>

                
                <?php if (!empty($planning['hashtag_text'])): ?>
                    <div class="mb-4">
                        <label style="font-size: 12px; font-weight: 700; text-transform: uppercase; color: #334155; display: block; margin-bottom: 8px;">
                            <i class="bi bi-hash me-1" style="color: #3730a3;"></i> Hashtags
                        </label>
                        <div class="p-3 rounded-lg border text-xs font-mono" style="background: #eef2ff; border-color: #c7d2fe !important; border-radius: 12px; color: #3730a3; font-weight: 600; line-height: 1.5;">
                            <?= htmlspecialchars($planning['hashtag_text']) ?>
                        </div>
                    </div>
                <?php endif; ?>

                
                <?php if (!empty($planning['catatan'])): ?>
                    <div class="mb-4 text-start">
                        <label style="font-size: 12px; font-weight: 700; text-transform: uppercase; color: #334155; display: block; margin-bottom: 8px; text-align: left;">
                            <i class="bi bi-info-circle-fill me-1" style="color: #d97706;"></i> Catatan / Deskripsi Instruksi
                        </label>
                        <div class="p-3 rounded-lg border text-start" style="background: #fffbe6; border-color: #ffe58f !important; border-left: 4px solid #d97706 !important; color: #78350f; font-size: 13.5px; font-weight: 500; line-height: 1.6; white-space: pre-wrap; border-radius: 10px; text-align: left !important; text-align-last: left !important; text-justify: none;"><?= trim(htmlspecialchars($planning['catatan'])) ?></div>
                    </div>
                <?php endif; ?>

                
                <?php if (!empty($planning['material_link']) || !empty($planning['edit_link']) || !empty($media)): ?>
                    <div class="mb-2">
                        <label style="font-size: 12px; font-weight: 700; text-transform: uppercase; color: #334155; display: block; margin-bottom: 8px;">
                            <i class="bi bi-link-45deg me-1" style="color: #0288d1;"></i> Tautan & Media Lampiran
                        </label>
                        <div class="d-flex gap-2 flex-wrap">
                            <?php if (!empty($planning['material_link'])): ?>
                                <a href="<?= htmlspecialchars($planning['material_link']) ?>" target="_blank" class="btn btn-sm btn-outline" style="border-radius: 8px; font-size: 12px; font-weight: 600;">
                                    <i class="bi bi-folder2-open me-1" style="color: #1a237e;"></i> Buka Materi Drive
                                </a>
                            <?php endif; ?>
                            <?php if (!empty($planning['edit_link'])): ?>
                                <a href="<?= htmlspecialchars($planning['edit_link']) ?>" target="_blank" class="btn btn-sm btn-outline" style="border-radius: 8px; font-size: 12px; font-weight: 600;">
                                    <i class="bi bi-pencil-square me-1" style="color: #0288d1;"></i> Buka Link Edit (Canva)
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    
    <div class="col-12 col-lg-4">
        
        <div class="card mb-4" style="border-radius: 16px; border: 1px solid #e2e8f0; background: #ffffff; box-shadow: 0 4px 12px rgba(0,0,0,0.03); overflow: hidden;">
            <div class="card-header" style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; padding: 16px 20px;">
                <div class="d-flex align-items-center gap-2">
                    <div style="width: 32px; height: 32px; border-radius: 8px; background: #e8eaf6; color: #1a237e; display: flex; align-items: center; justify-content: center; font-size: 15px;">
                        <i class="bi bi-info-circle-fill"></i>
                    </div>
                    <div>
                        <h5 class="m-0" style="font-size: 15px; font-weight: 700; color: #0f172a;">Informasi Meta Planning</h5>
                        <span style="font-size: 11px; color: #64748b;">Rincian spesifikasi & penugasan</span>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <div style="display: flex; flex-direction: column;">
                    
                    <div class="d-flex justify-content-between align-items-center p-3" style="border-bottom: 1px solid #f1f5f9;">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-calendar-event" style="color: #1a237e; font-size: 14px;"></i>
                            <span style="font-size: 12px; font-weight: 600; color: #475569;">Tanggal Posting</span>
                        </div>
                        <strong style="font-size: 13px; font-weight: 700; color: #0f172a;">
                            <?= !empty($planning['tanggal_posting']) ? date('d F Y', strtotime($planning['tanggal_posting'])) : '-' ?>
                        </strong>
                    </div>

                    
                    <div class="d-flex justify-content-between align-items-center p-3" style="border-bottom: 1px solid #f1f5f9;">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-clock" style="color: #0288d1; font-size: 14px;"></i>
                            <span style="font-size: 12px; font-weight: 600; color: #475569;">Jam Posting Target</span>
                        </div>
                        <strong style="font-size: 13px; font-weight: 700; color: #0f172a;">
                            <?= !empty($planning['jam_posting']) ? date('H:i', strtotime($planning['jam_posting'])) . ' WIB' : '-' ?>
                        </strong>
                    </div>

                    
                    <div class="d-flex justify-content-between align-items-center p-3" style="border-bottom: 1px solid #f1f5f9;">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-flag" style="color: #ed6c02; font-size: 14px;"></i>
                            <span style="font-size: 12px; font-weight: 600; color: #475569;">Prioritas Pengerjaan</span>
                        </div>
                        <span style="background: #fff7ed; color: #c2410c; border: 1px solid #ffedd5; font-size: 11px; font-weight: 700; padding: 4px 10px; border-radius: 8px; text-transform: uppercase;">
                            <?= ucfirst($planning['priority'] ?? 'medium') ?>
                        </span>
                    </div>

                    
                    <div class="d-flex justify-content-between align-items-center p-3" style="border-bottom: 1px solid #f1f5f9;">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-person-badge" style="color: #2e7d32; font-size: 14px;"></i>
                            <span style="font-size: 12px; font-weight: 600; color: #475569;">PIC (Penanggung Jawab)</span>
                        </div>
                        <strong style="font-size: 13px; font-weight: 700; color: #1a237e; background: #e8eaf6; padding: 3px 10px; border-radius: 8px;">
                            <?= htmlspecialchars($planning['pic_name'] ?? '-') ?>
                        </strong>
                    </div>

                    
                    <div class="d-flex justify-content-between align-items-center p-3" style="border-bottom: 1px solid #f1f5f9;">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-person-workspace" style="color: #64748b; font-size: 14px;"></i>
                            <span style="font-size: 12px; font-weight: 600; color: #475569;">Dibuat Oleh</span>
                        </div>
                        <span style="font-size: 13px; font-weight: 600; color: #334155;">
                            <?= htmlspecialchars($planning['creator_name'] ?? '-') ?>
                        </span>
                    </div>

                    
                    <?php if (!empty($planning['deadline'])): ?>
                        <div class="d-flex justify-content-between align-items-center p-3" style="background: #fff5f5;">
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-exclamation-triangle" style="color: #d32f2f; font-size: 14px;"></i>
                                <span style="font-size: 12px; font-weight: 600; color: #991b1b;">Deadline Penyelesaian</span>
                            </div>
                            <strong style="font-size: 13px; font-weight: 700; color: #991b1b; background: #fee2e2; padding: 3px 10px; border-radius: 8px;">
                                <?= date('d F Y', strtotime($planning['deadline'])) ?>
                            </strong>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        
        <?php if (!empty($logs)): ?>
            <div class="card mb-4" style="border-radius: 16px; border: 1px solid #e2e8f0;">
                <div class="card-header" style="border-bottom: 1px solid #f1f5f9; padding: 16px 20px;">
                    <h5 class="m-0" style="font-size: 14px; font-weight: 700; color: #0f172a;">
                        <i class="bi bi-clock-history text-secondary me-1"></i> Riwayat Aktivitas
                    </h5>
                </div>
                <div class="card-body p-3">
                    <div style="font-size: 12px; display: flex; flex-direction: column; gap: 10px;">
                        <?php foreach ($logs as $log): ?>
                            <div style="padding-bottom: 8px; border-bottom: 1px dashed #e2e8f0;">
                                <div class="d-flex justify-content-between">
                                    <strong style="color: #0f172a;"><?= htmlspecialchars($log['user_name'] ?? 'Sistem') ?></strong>
                                    <span class="text-tertiary" style="font-size: 10px;"><?= date('d/m/H:i', strtotime($log['created_at'])) ?></span>
                                </div>
                                <span class="text-secondary"><?= htmlspecialchars($log['description'] ?? '') ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
function updateWorkflowStatus(newStatus) {
    if (!confirm('Ubah status planning ke "' + newStatus + '"?')) return;

    var formData = new FormData();
    formData.append('status', newStatus);
    formData.append('<?= CSRF_TOKEN_NAME ?>', '<?= Session::csrfToken() ?>');

    fetch('<?= BASE_URL ?>/planning/<?= $planning['id'] ?>/status', {
        method: 'POST',
        body: formData
    })
    .then(function(r) { return r.json(); })
    .then(function(res) {
        if (res.success) {
            window.location.reload();
        } else {
            alert(res.message || 'Gagal mengubah status');
        }
    })
    .catch(function() {
        alert('Terjadi kesalahan sistem saat memperbarui status');
    });
}

function deleteDetailPlanning(id) {
    if (!confirm('Apakah Anda yakin ingin menghapus planning ini?')) return;

    var formData = new FormData();
    formData.append('<?= CSRF_TOKEN_NAME ?>', '<?= Session::csrfToken() ?>');

    fetch('<?= BASE_URL ?>/planning/' + id + '/delete', {
        method: 'POST',
        body: formData
    })
    .then(function(r) { return r.json(); })
    .then(function(res) {
        if (res.success) {
            window.location.href = '<?= BASE_URL ?>/planning?view=table';
        } else {
            alert(res.message || 'Gagal menghapus planning');
        }
    })
    .catch(function() {
        alert('Terjadi kesalahan sistem');
    });
}
</script>
