<?php
$data = $data ?? [];
$filters = $filters ?? [];
$pagination = $pagination ?? [];
$platforms = $platforms ?? [];
$roleSlug = Session::get('user_role_slug');
$filterSearch = $filters['search'] ?? '';
?>

<style>

.approval-card-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 16px;
    margin-bottom: 24px;
}
@media (max-width: 1024px) {
    .approval-card-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}
@media (max-width: 640px) {
    .approval-card-grid {
        grid-template-columns: 1fr;
    }
}


.approval-task-card {
    background: var(--surface, #ffffff);
    border: 1px solid var(--border-light, #e2e8f0);
    border-radius: 12px;
    padding: 16px;
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    cursor: pointer;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    min-height: 140px;
    position: relative;
}
.approval-task-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 25px -5px rgba(0,0,0,0.08);
    border-color: #cbd5e1;
}


.approval-task-card {
    opacity: 0;
}
.approval-task-card.fade-up {
    animation: fadeUp 0.4s ease-out both;
}
@keyframes fadeUp {
    from { opacity: 0; transform: translateY(16px); }
    to { opacity: 1; transform: translateY(0); }
}

.approval-card-top {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 8px;
    margin-bottom: 10px;
}
.approval-card-title {
    font-weight: 600;
    font-size: 14px;
    color: #0f172a;
    line-height: 1.4;
    flex: 1;
    margin-bottom: 0;
}

.approval-card-sub {
    font-size: 12px;
    color: #64748b;
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
    margin-bottom: 12px;
}

.approval-card-bottom {
    display: flex;
    align-items: center;
    justify-content: space-between;
    font-size: 11px;
    color: #64748b;
    padding-top: 10px;
    border-top: 1px solid #f1f5f9;
    margin-top: auto;
}


.approval-date-group {
    margin-bottom: 24px;
}
.approval-date-header {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 14px 20px;
    background: var(--surface, #fff);
    border-radius: 14px 14px 0 0;
    border: 1px solid var(--border-light, #e2e8f0);
    border-bottom: none;
}
.approval-date-header .date-badge {
    background: linear-gradient(135deg, #003399, #0055cc);
    color: white;
    border-radius: 10px;
    padding: 5px 16px;
    font-size: 14px;
    font-weight: 700;
    box-shadow: 0 2px 8px rgba(0,51,153,0.25);
    min-width: 40px;
    text-align: center;
}
.approval-date-header .day-name {
    font-size: 15px;
    font-weight: 700;
    color: var(--text-primary, #0f172a);
}
.approval-date-header .task-count {
    margin-left: auto;
    font-size: 12px;
    color: #64748b;
    font-weight: 600;
}
.approval-task-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 16px;
    padding: 20px;
    background: var(--surface, #fff);
    border: 1px solid var(--border-light, #e2e8f0);
    border-radius: 0 0 14px 14px;
}
/* Warna kartu mengikuti status hanya aktif di mode gelap (lihat dark-mode.css) */
.approval-task-card.is-approved,
.approval-task-card.is-pending,
.approval-task-card.is-revision,
.approval-task-card.is-belum {
    /* mode terang: kartu tetap default */
}
.approval-badge-status {
    font-size: 11px;
    font-weight: 600;
    padding: 4px 10px;
    border-radius: 6px;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}
.approval-badge-status.pending {
    background: #fff3e0;
    color: #e65100;
    border: 1px solid #ffe0b2;
}
.approval-badge-status.revision {
    background: #fff5f5;
    color: #c53030;
    border: 1px solid #feb2b2;
}
.approval-badge-status.approved {
    background: #ecfdf5;
    color: #047857;
    border: 1px solid #a7f3d0;
}


.badge-status {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 3px 10px;
    border-radius: 6px;
    font-size: 10px;
    font-weight: 600;
    white-space: nowrap;
    flex-shrink: 0;
}
.badge-status.pending { background: #fff3e0; color: #e65100; border: 1px solid #ffe0b2; }
.badge-status.approved { background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; }
.badge-status.revision { background: #fff5f5; color: #c53030; border: 1px solid #feb2b2; }
.badge-status.assigned { background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; }
.badge-status.progress { background: #faf5ff; color: #7e22ce; border: 1px solid #e9d5ff; }
.badge-status.selesai { background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; }


.priority-badge {
    padding: 2px 8px;
    border-radius: 4px;
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
}
.priority-badge.urgent { background: #ffebee; color: #c62828; }
.priority-badge.high { background: #fff3e0; color: #e65100; }
.priority-badge.medium { background: #e8f5e9; color: #2e7d32; }
.priority-badge.low { background: #f5f5f5; color: #757575; }


#reviewModal .modal-content {
    max-width: 850px;
    max-height: 90vh;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    border-radius: 20px;
}
#reviewModal .modal-body {
    flex: 1;
    overflow-y: auto;
    min-height: 0;
    padding: var(--space-5);
}
#reviewModal .modal-footer {
    flex-shrink: 0;
    background: var(--surface-primary);
    border-top: 1px solid var(--border-light);
    padding: var(--space-4) var(--space-5);
    display: flex;
    gap: var(--space-3);
    justify-content: flex-end;
    flex-wrap: wrap;
}


#reviewModal .modal-content {
    transition: none !important;
}
#reviewModal.show .modal-content {
    animation: slideUpFade 0.35s cubic-bezier(0.34, 1.56, 0.64, 1) both !important;
}
#reviewModal.closing .modal-content {
    animation: slideDownFade 0.25s ease-in both !important;
}
@keyframes slideUpFade {
    from { opacity: 0; transform: translateY(30px) scale(0.97); }
    to { opacity: 1; transform: translateY(0) scale(1); }
}
@keyframes slideDownFade {
    from { opacity: 1; transform: translateY(0) scale(1); }
    to { opacity: 0; transform: translateY(20px) scale(0.97); }
}

.review-section {
    background: var(--surface-secondary);
    border: 1px solid var(--border-light);
    border-radius: var(--radius-lg);
    padding: var(--space-4);
    margin-bottom: var(--space-4);
}
.review-section-title {
    font-size: var(--text-xs);
    font-weight: var(--font-weight-bold);
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: var(--text-tertiary);
    margin-bottom: var(--space-3);
    display: flex;
    align-items: center;
    gap: var(--space-2);
}
.review-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: var(--space-3);
}
@media (max-width: 640px) { .review-grid { grid-template-columns: 1fr; } }
.review-field {
    display: flex;
    flex-direction: column;
    gap: 2px;
}
.review-field-label {
    font-size: 10px;
    font-weight: var(--font-weight-semibold);
    color: var(--text-tertiary);
    text-transform: uppercase;
}
.review-field-value {
    font-size: var(--text-sm);
    color: var(--text-primary);
    font-weight: var(--font-weight-medium);
    word-break: break-word;
}

.attachment-list {
    display: flex;
    flex-wrap: wrap;
    gap: var(--space-2);
}
.attachment-item {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 12px;
    background: var(--surface-primary);
    border: 1px solid var(--border-light);
    border-radius: var(--radius-md);
    font-size: var(--text-xs);
    color: var(--text-secondary);
    text-decoration: none;
    transition: all var(--transition-fast);
}
.attachment-item:hover {
    border-color: var(--tvri-blue);
    color: var(--tvri-blue);
    background: var(--tvri-blue-50);
}


.btn-task-link {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 14px;
    font-size: 12px;
    font-weight: 600;
    border-radius: 8px;
    text-decoration: none;
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 1px 3px rgba(0,0,0,0.05);
}
.btn-task-link.material {
    background: #eff6ff;
    color: #1d4ed8;
    border: 1px solid #bfdbfe;
}
.btn-task-link.material:hover {
    background: #dbeafe;
    color: #1e40af;
    transform: translateY(-1px);
    box-shadow: 0 4px 10px rgba(29,78,216,0.18);
}
.btn-task-link.edit {
    background: #f0fdf4;
    color: #15803d;
    border: 1px solid #bbf7d0;
}
.btn-task-link.edit:hover {
    background: #dcfce7;
    color: #166534;
    transform: translateY(-1px);
    box-shadow: 0 4px 10px rgba(21,128,61,0.18);
}
.btn-task-link.file {
    background: #faf5ff;
    color: #7e22ce;
    border: 1px solid #e9d5ff;
}
.btn-task-link.file:hover {
    background: #f3e8ff;
    color: #6b21a8;
    transform: translateY(-1px);
    box-shadow: 0 4px 10px rgba(126,34,206,0.18);
}

.activity-timeline {
    position: relative;
    padding-left: 24px;
}
.activity-timeline::before {
    content: '';
    position: absolute;
    left: 8px;
    top: 4px;
    bottom: 4px;
    width: 2px;
    background: var(--border-light);
}
.activity-item {
    position: relative;
    padding-bottom: var(--space-3);
}
.activity-item:last-child { padding-bottom: 0; }
.activity-item::before {
    content: '';
    position: absolute;
    left: -20px;
    top: 4px;
    width: 12px;
    height: 12px;
    border-radius: var(--radius-full);
    background: var(--tvri-blue);
    border: 2px solid var(--surface-primary);
}
.activity-item .activity-title {
    font-size: var(--text-xs);
    font-weight: var(--font-weight-semibold);
    color: var(--text-primary);
}
.activity-item .activity-meta {
    font-size: 10px;
    color: var(--text-tertiary);
}

.revision-textarea {
    width: 100%;
    min-height: 80px;
    padding: var(--space-3);
    border: 2px solid var(--danger);
    border-radius: var(--radius-md);
    font-size: var(--text-sm);
    resize: vertical;
    background: var(--surface-primary);
    transition: border var(--transition-fast);
}
.revision-textarea:focus {
    outline: none;
    border-color: var(--danger);
    box-shadow: 0 0 0 3px rgba(198,40,40,0.15);
}

.search-input-wrapper {
    position: relative;
    width: 100%;
    display: flex;
    align-items: center;
}
.search-input-wrapper i {
    position: absolute;
    left: 14px;
    top: 50%;
    transform: translateY(-50%);
    color: #94a3b8;
    font-size: 15px;
    pointer-events: none;
    z-index: 10;
}
.search-input-wrapper input {
    width: 100%;
    padding-left: 40px !important;
    border-radius: 10px;
    height: 42px;
    font-size: 13px;
    border: 1px solid #cbd5e1;
    box-shadow: 0 1px 2px rgba(0,0,0,0.04);
    transition: all 0.2s ease;
}
.search-input-wrapper input:focus {
    border-color: #003399;
    box-shadow: 0 0 0 3px rgba(0,51,153,0.12);
}
</style>


<div class="d-flex align-items-center justify-content-between gap-3 mb-4 flex-wrap">
    <div>
        <h4 class="fw-bold mb-1 d-flex align-items-center"><i class="bi bi-check2-square text-primary fs-4" style="margin-right:10px;"></i><span>Approval Task</span></h4>
        <p class="text-tertiary mb-0" style="font-size:13px;">Daftar task yang memerlukan persetujuan dan peninjauan Admin.</p>
    </div>
    <div style="min-width:260px; max-width:340px; width:100%;">
        <div class="search-input-wrapper">
            <i class="bi bi-search"></i>
            <input type="text" id="approvalSearchInput" class="form-control" placeholder="Pencarian task"
                   value="<?= htmlspecialchars($filterSearch) ?>" onkeyup="filterApprovalCardsPerLetter(this.value)" oninput="filterApprovalCardsPerLetter(this.value)">
        </div>
    </div>
</div>


<div id="approvalTaskList">
    <?php require __DIR__ . '/_cards_admin.php'; ?>
</div>


<div class="modal" id="reviewModal" onclick="if (event.target === this) closeReviewModal();">
    <div class="modal-content">
        <div class="modal-header">
            <h5><i class="bi bi-clipboard-check me-2"></i> Review Hasil Pekerjaan</h5>
            <button type="button" class="modal-close" onclick="closeReviewModal()">&times;</button>
        </div>
        <div class="modal-body" id="reviewModalBody">
            <div class="text-center py-5 text-tertiary">
                <div class="spinner-border text-primary mb-3" role="status" style="width:32px;height:32px;"></div>
                <p>Memuat detail task...</p>
            </div>
        </div>
        <div class="modal-footer" id="reviewModalFooter"></div>
    </div>
</div>

<script>
let currentTaskId = null;

function filterApprovalCardsPerLetter(query) {
    var q = query.toLowerCase().trim();
    var cards = document.querySelectorAll('.approval-task-card');
    var visibleCount = 0;
    
    cards.forEach(function(card) {
        var text = card.textContent.toLowerCase();
        if (q === '' || text.indexOf(q) !== -1) {
            card.style.display = 'flex';
            visibleCount++;
        } else {
            card.style.display = 'none';
        }
    });

    
    document.querySelectorAll('.approval-date-group').forEach(function(group) {
        var groupCards = group.querySelectorAll('.approval-task-card');
        var visible = Array.prototype.some.call(groupCards, function(c) { return c.style.display !== 'none'; });
        group.style.display = visible ? '' : 'none';
    });
    
    var emptyEl = document.getElementById('approvalSearchEmpty');
    if (emptyEl) {
        emptyEl.style.display = (visibleCount === 0 && cards.length > 0) ? 'block' : 'none';
    }
}

function openReviewModal(taskId) {
    currentTaskId = taskId;
    const modal = document.getElementById('reviewModal');
    const body = document.getElementById('reviewModalBody');
    const footer = document.getElementById('reviewModalFooter');
    
    body.innerHTML = '<div class="text-center py-5 text-tertiary"><div class="spinner-border text-primary mb-3" role="status" style="width:32px;height:32px;"></div><p>Memuat detail task...</p></div>';
    footer.innerHTML = '';
    
    if (modal._closeTimer) { clearTimeout(modal._closeTimer); modal._closeTimer = null; }
    modal.classList.remove('closing');
    modal.classList.add('show');
    document.body.style.overflow = 'hidden';
    
    fetch(BASE_URL + '/approval/' + taskId)
        .then(function(r) { return r.json(); })
        .then(function(res) {
            if (!res.success) {
                body.innerHTML = '<div class="text-center py-5 text-danger"><i class="bi bi-exclamation-triangle fs-1 d-block mb-2"></i><p>Gagal memuat detail task</p></div>';
                return;
            }
            renderReview(modal, body, footer, res.data, res.history || []);
        })
        .catch(function() {
            body.innerHTML = '<div class="text-center py-5 text-danger"><i class="bi bi-exclamation-triangle fs-1 d-block mb-2"></i><p>Gagal memuat detail task</p></div>';
        });
}

function renderReview(modal, body, footer, d, history) {
    var esc = function(s) { if (!s) return ''; return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); };
    var fmtDate = function(s) { if (!s) return '-'; return s.split(' ')[0].split('-').reverse().join('/'); };
    
    var html = '';
    
    
    html += '<div class="review-section">';
    html += '  <div class="review-section-title"><i class="bi bi-info-circle"></i> Informasi Task</div>';

    
    html += '  <div style="margin-bottom:12px;">';
    html += '    <div style="font-size:16px; font-weight:700; color:#0f172a; line-height:1.4;">' + esc(d.title || '(Belum ada judul)') + '</div>';
    html += '  </div>';

    
    if (d.catatan) {
        html += '<div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:var(--radius-md); padding:var(--space-3); margin-bottom:var(--space-3); font-size:var(--text-sm);">';
        html += '  <div style="font-weight:var(--font-weight-semibold); color:#1a237e; margin-bottom:4px;"><i class="bi bi-info-circle me-1"></i> Instruksi dari Admin:</div>';
        html += '  <div style="color:var(--text-primary); white-space:pre-wrap;">' + esc(d.catatan) + '</div>';
        html += '</div>';
    }

    html += '  <div class="review-grid mb-3">';
    var renderTypeBadges = function(typesStr) {
        if (!typesStr) return '<span class="badge" style="background:#64748b; color:#fff; font-size:11px; padding:4px 10px; border-radius:6px;">Task</span>';
        var arr = typesStr.split(',').map(function(s){ return s.trim(); }).filter(Boolean);
        return arr.map(function(t) {
            return '<span class="badge" style="background:linear-gradient(135deg, #1a237e, #283593); color:#fff; font-weight:600; font-size:11px; padding:4px 10px; border-radius:6px; display:inline-flex; align-items:center; gap:4px; margin-right:4px; margin-bottom:4px; box-shadow:0 2px 6px rgba(26,35,126,0.15);"><i class="bi bi-tag-fill"></i>' + esc(t) + '</span>';
        }).join('');
    };

    html += '    <div class="review-field"><span class="review-field-label">Jenis</span><span class="review-field-value">' + renderTypeBadges(d.content_type) + '</span></div>';
    html += '    <div class="review-field"><span class="review-field-label">Deadline</span><span class="review-field-value">' + fmtDate(d.deadline) + '</span></div>';
    html += '  </div>';

    if (d.material_link || d.edit_link || d.file_attachment) {
        html += '  <div class="d-flex align-items-center gap-2 flex-wrap pt-2 border-top">';
        if (d.material_link) {
            html += '    <a href="' + esc(d.material_link) + '" target="_blank" class="btn-task-link material" title="Buka Link Materi"><i class="bi bi-folder2-open"></i> Link Materi</a>';
        }
        if (d.edit_link) {
            html += '    <a href="' + esc(d.edit_link) + '" target="_blank" class="btn-task-link edit" title="Buka Link Edit"><i class="bi bi-pencil-square"></i> Link Edit</a>';
        }
        if (d.file_attachment) {
            html += '    <a href="' + esc(d.file_attachment) + '" target="_blank" class="btn-task-link file" title="Unduh Lampiran Info Task"><i class="bi bi-paperclip"></i> Lampiran Info Task</a>';
        }
        html += '  </div>';
    }
    html += '</div>';
    
    
    var resultFile = d.result_file_attachment || (d.result_link ? null : d.file_attachment);
    var hasResult = d.description_update || d.user_notes || d.result_link || resultFile || d.screenshot_attachment || d.video_attachment;
    
    html += '<div class="review-section">';
    html += '  <div class="review-section-title"><i class="bi bi-file-earmark-check"></i> Hasil Pekerjaan</div>';
    if (hasResult) {
        if (d.description_update) {
            html += '  <div class="review-grid mb-2">';
            html += '    <div class="review-field"><span class="review-field-label">Deskripsi Update</span><span class="review-field-value">' + esc(d.description_update) + '</span></div>';
            html += '  </div>';
        }

        
        if (d.user_notes) {
            html += '<div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:var(--radius-md); padding:var(--space-3); margin-bottom:var(--space-3); font-size:var(--text-sm);">';
            html += '  <div style="font-weight:var(--font-weight-semibold); color:#1a237e; margin-bottom:4px;"><i class="bi bi-info-circle me-1"></i> Catatan User:</div>';
            html += '  <div style="color:var(--text-primary); white-space:pre-wrap;">' + esc(d.user_notes) + '</div>';
            html += '</div>';
        }

        html += '  <div class="d-flex align-items-center gap-2 flex-wrap mt-2">';
        if (d.result_link) {
            html += '    <a href="' + esc(d.result_link) + '" target="_blank" class="btn-task-link material" title="Buka Link Hasil"><i class="bi bi-link-45deg"></i> Link Hasil</a>';
        }
        if (resultFile) {
            html += '    <a href="' + esc(resultFile) + '" target="_blank" class="btn-task-link file" title="Unduh File Hasil Pekerjaan"><i class="bi bi-file-earmark-arrow-down"></i> File Hasil Pekerjaan</a>';
        }
        if (d.screenshot_attachment) {
            html += '    <a href="' + esc(d.screenshot_attachment) + '" target="_blank" class="btn-task-link file" title="Buka Screenshot"><i class="bi bi-image"></i> Screenshot</a>';
        }
        if (d.video_attachment) {
            html += '    <a href="' + esc(d.video_attachment) + '" target="_blank" class="btn-task-link file" title="Buka Video"><i class="bi bi-film"></i> Video</a>';
        }
        html += '  </div>';
    } else {
        html += '  <p class="text-tertiary mb-0"><i class="bi bi-info-circle"></i> User belum mengirim hasil pekerjaan.</p>';
    }
    html += '</div>';
    
    
    if (d.revision_notes) {
        html += '<div class="review-section" style="border-left: 3px solid var(--danger);">';
        html += '  <div class="review-section-title" style="color: var(--danger);"><i class="bi bi-arrow-counterclockwise"></i> Catatan Revisi Sebelumnya</div>';
        html += '  <div style="white-space:pre-wrap;font-size:var(--text-sm);color:var(--text-primary);background:var(--danger-50);padding:var(--space-3);border-radius:var(--radius-md);">' + esc(d.revision_notes) + '</div>';
        html += '</div>';
    }
    
    body.innerHTML = html;
    
    
    var footerHtml = '';
    if (d.status === 'Pending Approval') {
        footerHtml += '<div style="flex:1;min-width:200px;">';
        footerHtml += '  <textarea id="revisionNotesInput" class="revision-textarea" placeholder="Isi catatan revisi (wajib jika revisi)..."></textarea>';
        footerHtml += '</div>';
        footerHtml += '<button type="button" class="btn btn-success" onclick="approveTask(' + d.id + ')" title="Setujui task"><i class="bi bi-check-lg me-1"></i> Approve</button>';
        footerHtml += '<button type="button" class="btn btn-danger" onclick="revisionTask(' + d.id + ')" title="Minta revisi"><i class="bi bi-arrow-counterclockwise me-1"></i> Revisi</button>';
    } else {
        footerHtml += '<span class="text-tertiary" style="font-size:var(--text-sm);"><i class="bi bi-check-circle me-1"></i> Task sudah ' + esc(d.status) + '</span>';
    }
    footer.innerHTML = footerHtml;
}

function approveTask(id) {
    
    var overlay = document.getElementById('pageLoadingOverlay');
    if (overlay) {
        var overlayText = overlay.querySelector('.page-loading-text');
        if (overlayText) overlayText.textContent = 'Menyetujui Task...';
        overlay.classList.add('show');
    }

    var formData = new FormData();
    formData.append('id', id);
    
    fetch(BASE_URL + '/approval/' + id + '/task-approve', { method: 'POST', body: formData })
        .then(function(r) { return r.json(); })
        .then(function(res) {
            if (res.success) {
                showToast('success', 'Berhasil', res.message);
                closeReviewModal();
                setTimeout(function() { location.reload(); }, 1000);
            } else {
                showToast('error', 'Gagal', res.message);
                if (overlay) overlay.classList.remove('show');
            }
        })
        .catch(function() {
            showToast('error', 'Error', 'Terjadi kesalahan server');
            if (overlay) overlay.classList.remove('show');
        });
}

function revisionTask(id) {
    var notes = document.getElementById('revisionNotesInput').value.trim();
    if (!notes) {
        showToast('warning', 'Catatan Diperlukan', 'Catatan revisi wajib diisi sebelum mengirim revisi.');
        document.getElementById('revisionNotesInput').focus();
        return;
    }

    
    var overlay = document.getElementById('pageLoadingOverlay');
    if (overlay) {
        var overlayText = overlay.querySelector('.page-loading-text');
        if (overlayText) overlayText.textContent = 'Mengirim Revisi...';
        overlay.classList.add('show');
    }
    
    var formData = new FormData();
    formData.append('id', id);
    formData.append('notes', notes);
    
    fetch(BASE_URL + '/approval/' + id + '/task-revision', { method: 'POST', body: formData })
        .then(function(r) { return r.json(); })
        .then(function(res) {
            if (res.success) {
                showToast('success', 'Revisi Dikirim', res.message);
                closeReviewModal();
                setTimeout(function() { location.reload(); }, 1000);
            } else {
                showToast('error', 'Gagal', res.message);
                if (overlay) overlay.classList.remove('show');
            }
        })
        .catch(function() {
            showToast('error', 'Error', 'Terjadi kesalahan server');
            if (overlay) overlay.classList.remove('show');
        });
}

function closeReviewModal() {
    var modal = document.getElementById('reviewModal');
    if (!modal || modal.classList.contains('closing')) return;
    modal.classList.add('closing');
    if (modal._closeTimer) clearTimeout(modal._closeTimer);
    modal._closeTimer = setTimeout(function() {
        modal.classList.remove('show');
        modal.classList.remove('closing');
        modal._closeTimer = null;
    }, 250);
    document.body.style.overflow = '';
    currentTaskId = null;
}

document.addEventListener('DOMContentLoaded', function() {
    var modal = document.getElementById('reviewModal');
    if (modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === this) {
                closeReviewModal();
            }
        });
    }
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            var m = document.getElementById('reviewModal');
            if (m && m.classList.contains('show')) {
                closeReviewModal();
            }
        }
    });
});


document.addEventListener('DOMContentLoaded', function() {
    setTimeout(function() {
        var cards = document.querySelectorAll('.approval-task-card');
        cards.forEach(function(card, i) {
            card.classList.add('fade-up');
            card.style.animationDelay = (i * 0.05) + 's';
        });
    }, 50);
});

</script>


<div id="pageLoadingOverlay" class="page-loading-overlay" aria-hidden="true">
    <div class="page-loading-box">
        <div class="page-loading-spinner"></div>
        <div class="page-loading-text">Memproses Task...</div>
    </div>
</div>
