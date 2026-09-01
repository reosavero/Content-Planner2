<?php
$groupedTasks = $groupedTasks ?? [];
?>

<style>
@keyframes fadeUp {
    from { opacity: 0; transform: translateY(16px); }
    to { opacity: 1; transform: translateY(0); }
}
@keyframes slideUpFade {
    from { opacity: 0; transform: translateY(30px) scale(0.97); }
    to { opacity: 1; transform: translateY(0) scale(1); }
}
.task-card-approval {
    opacity: 0;
}
.task-card-approval.fade-up {
    animation: fadeUp 0.4s ease-out both;
}
#taskSubmitModal.show .modal-content {
    animation: slideUpFade 0.35s cubic-bezier(0.34, 1.56, 0.64, 1) both !important;
}
#taskSubmitModal.closing .modal-content {
    animation: slideDownFade 0.25s ease-in both !important;
}
@keyframes slideDownFade {
    from { opacity: 1; transform: translateY(0) scale(1); }
    to { opacity: 0; transform: translateY(20px) scale(0.97); }
}
.magang-date-group {
    margin-bottom: 24px;
}
.magang-date-header {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 14px 20px;
    background: var(--surface, #fff);
    border-radius: 14px 14px 0 0;
    border: 1px solid var(--border-light, #e2e8f0);
    border-bottom: none;
}
.magang-date-header .date-badge {
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
.magang-date-header .day-name {
    font-size: 15px;
    font-weight: 700;
    color: var(--text-primary, #0f172a);
}
.magang-date-header .task-count {
    margin-left: auto;
    font-size: 12px;
    color: #64748b;
    font-weight: 600;
}
.magang-task-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 16px;
    padding: 20px;
    background: var(--surface, #fff);
    border: 1px solid var(--border-light, #e2e8f0);
    border-radius: 0 0 14px 14px;
}


.task-card-approval {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 16px;
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    cursor: pointer;
    position: relative;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    min-height: 110px;
}
.task-card-approval:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 25px -5px rgba(0,0,0,0.1);
    border-color: #cbd5e1;
}
/* Warna kartu mengikuti status hanya aktif di mode gelap (lihat dark-mode.css) */
.task-card-approval.is-approved,
.task-card-approval.is-pending,
.task-card-approval.is-revision {
    /* mode terang: kartu tetap default (putih bersih) */
}
.task-card-approval .task-title {
    font-size: 14px;
    font-weight: 600;
    color: #0f172a;
    margin-top: 0;
    margin-bottom: 12px;
    line-height: 1.4;
}
.task-card-approval .task-meta {
    font-size: 12px;
    color: #64748b;
    display: flex;
    gap: 12px;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    margin-top: auto;
    padding-top: 10px;
    border-top: 1px solid #f1f5f9;
}
.magang-badge-status {
    font-size: 11px;
    font-weight: 600;
    padding: 4px 10px;
    border-radius: 6px;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}
.magang-badge-status.pending {
    background: #fff3e0;
    color: #e65100;
    border: 1px solid #ffe0b2;
}
.magang-badge-status.approved {
    background: #ecfdf5;
    color: #047857;
    border: 1px solid #a7f3d0;
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
.submit-field { margin-bottom: var(--space-3); }
.submit-field label { font-size: 11px; font-weight: var(--font-weight-semibold); color: var(--text-secondary); display: block; margin-bottom: 4px; }
.submit-field textarea, .submit-field input { width: 100%; padding: var(--space-2) var(--space-3); border: 1px solid var(--border-light); border-radius: var(--radius-md); font-size: var(--text-sm); background: var(--surface-primary); }
.submit-field textarea:focus, .submit-field input:focus { border-color: var(--tvri-blue); outline: none; box-shadow: 0 0 0 3px rgba(0,51,153,0.1); }
</style>

<div class="mb-4">
    <h4 class="fw-bold mb-1"><i class="bi bi-check2-square text-primary me-2"></i>Status Approval Task</h4>
    <p class="text-tertiary mb-0" style="font-size:13px;">Daftar task yang telah dikirim untuk approval. Klik card task dengan status <strong>Menunggu Approval</strong> untuk mengedit kembali.</p>
</div>

<div id="magangApprovalList">
    <?php require __DIR__ . '/_cards_magang.php'; ?>
</div>


<div class="modal" id="taskSubmitModal" onclick="if (event.target === this) closeTaskSubmitModal();">
    <div class="modal-content" style="max-width:640px;max-height:90vh;">
        <div class="modal-header">
            <h5><i class="bi bi-send me-2"></i> Detail & Submit Task</h5>
        </div>
        <div class="modal-body" id="taskSubmitBody" style="overflow-y:auto;">
            <div class="text-center py-5 text-tertiary">
                <div class="spinner-border text-primary mb-2"></div>
                <p>Memuat task...</p>
            </div>
        </div>
        <div class="modal-footer" id="taskSubmitFooter">
            <button type="button" class="btn btn-ghost" onclick="closeTaskSubmitModal()">Tutup</button>
        </div>
    </div>
</div>

<script>
function openTaskSubmitModal(taskId) {
    var modal = document.getElementById('taskSubmitModal');
    var body = document.getElementById('taskSubmitBody');
    var footer = document.getElementById('taskSubmitFooter');
    
    body.innerHTML = '<div class="text-center py-5 text-tertiary"><div class="spinner-border text-primary mb-2"></div><p>Memuat task...</p></div>';
    footer.innerHTML = '<button type="button" class="btn btn-ghost" onclick="closeTaskSubmitModal()">Tutup</button>';
    modal.classList.add('show');
    document.body.style.overflow = 'hidden';
    
    fetch(BASE_URL + '/timeline/get/' + taskId)
        .then(function(r) { return r.json(); })
        .then(function(res) {
            if (!res.success) {
                body.innerHTML = '<div class="text-center py-4 text-danger"><i class="bi bi-exclamation-triangle fs-1 d-block mb-2"></i><p>Gagal memuat task</p></div>';
                return;
            }
            renderTaskSubmit(body, footer, res.data);
        })
        .catch(function() {
            body.innerHTML = '<div class="text-center py-4 text-danger"><i class="bi bi-exclamation-triangle fs-1 d-block mb-2"></i><p>Gagal memuat task</p></div>';
        });
}

function renderTaskSubmit(body, footer, d) {
    var esc = function(s) { if (!s) return ''; return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); };
    var isNeedRevision = d.status === 'Need Revision';
    var isPendingApproval = d.status === 'Pending Approval';
    var isApproved = ['Approved', 'Selesai', 'Publish'].indexOf(d.status) !== -1;
    
    var html = '';
    
    
    html += '<div class="mb-3 pb-2 border-bottom">';
    html += '  <h5 class="mb-0 fw-bold" style="color:#0f172a; font-size:16px; line-height:1.4;">' + esc(d.title || '(Belum ada judul)') + '</h5>';
    html += '</div>';

    
    if (d.catatan) {
        html += '<div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:var(--radius-md); padding:var(--space-3); margin-bottom:var(--space-3); font-size:var(--text-sm);">';
        html += '  <div style="font-weight:var(--font-weight-semibold); color:#1a237e; margin-bottom:4px;"><i class="bi bi-info-circle me-1"></i> Instruksi dari Admin:</div>';
        html += '  <div style="color:var(--text-primary); white-space:pre-wrap;">' + esc(d.catatan) + '</div>';
        html += '</div>';
    }

    var renderTypeBadges = function(typesStr) {
        if (!typesStr) return '<span class="badge" style="background:#64748b; color:#fff; font-size:11px; padding:6px 14px; border-radius:8px;">Task</span>';
        var arr = typesStr.split(',').map(function(s){ return s.trim(); }).filter(Boolean);
        return arr.map(function(t) {
            return '<span class="badge" style="background:linear-gradient(135deg, #1a237e, #283593); color:#fff; font-weight:600; font-size:11px; padding:6px 14px; border-radius:8px; box-shadow:0 2px 6px rgba(26,35,126,0.15); margin-right:4px; margin-bottom:4px; display:inline-flex; align-items:center; gap:4px;"><i class="bi bi-tag-fill"></i>' + esc(t) + '</span>';
        }).join('');
    };

    
    html += '<div class="d-flex align-items-center justify-content-between gap-2 flex-wrap mb-3">';
    html += '  <div class="d-flex flex-wrap gap-1">';
    html += '    ' + renderTypeBadges(d.content_type);
    html += '  </div>';
    
    if (d.material_link || d.edit_link || d.file_attachment) {
        html += '  <div class="d-flex align-items-center gap-2 flex-wrap">';
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
    
    
    if (isNeedRevision && d.revision_notes) {
        html += '<div class="revision-panel mb-3 p-3" style="background:#fff5f5; border-left:4px solid var(--danger); border-radius:8px;">';
        html += '  <div class="text-danger fw-bold mb-1" style="font-size:12px;"><i class="bi bi-arrow-counterclockwise me-1"></i> CATATAN REVISI DARI ADMIN:</div>';
        html += '  <div style="font-size:13px;">' + esc(d.revision_notes) + '</div>';
        html += '</div>';
    }
    
    if (isApproved) {
        html += '<div class="text-center py-4 text-success" style="background:#ecfdf5; border-radius:12px; margin-bottom:16px;">';
        html += '  <i class="bi bi-check-circle-fill fs-1 d-block mb-2 text-success"></i>';
        html += '  <h5 class="fw-bold">Task Telah Disetujui</h5>';
        html += '  <p class="text-secondary mb-0" style="font-size:13px;">Task ini sudah di-approval oleh Admin.</p>';
        html += '</div>';
        
        if (d.result_link || d.result_file_attachment || d.file_attachment || d.user_notes) {
            html += '<div class="p-3 bg-light rounded-3 mb-3" style="font-size:13px;">';
            if (d.user_notes) html += '<div><strong>Catatan Pekerjaan:</strong> ' + esc(d.user_notes) + '</div>';
            html += '  <div class="d-flex align-items-center gap-2 flex-wrap mt-2">';
            if (d.result_link) html += '<a href="' + esc(d.result_link) + '" target="_blank" class="btn-task-link material" title="Buka Link Hasil"><i class="bi bi-link-45deg"></i> Link Hasil</a>';
            if (d.result_file_attachment || d.file_attachment) html += '<a href="' + esc(d.result_file_attachment || d.file_attachment) + '" target="_blank" class="btn-task-link file" title="Unduh File Hasil Pekerjaan"><i class="bi bi-file-earmark-arrow-down"></i> File Hasil Pekerjaan</a>';
            html += '  </div>';
            html += '</div>';
        }
        
        footer.innerHTML = '<button type="button" class="btn btn-ghost" onclick="closeTaskSubmitModal()">Tutup</button>';
        body.innerHTML = html;
        return;
    }
    
    if (isPendingApproval) {
        html += '<div class="text-center py-2 px-3 mb-3" style="background:#fff3e0; border-radius:8px;">';
        html += '  <p class="mb-0 text-warning-emphasis" style="font-size:12px; color:#e65100;"><i class="bi bi-clock-history me-1"></i> Task sedang menunggu review Admin. Anda masih dapat mengubah isi hasil dan mengklik <strong>Kirim Ulang</strong>.</p>';
        html += '</div>';
    }
    
    
    html += '<form id="submitTaskForm" data-has-existing-file="' + (d.result_file_attachment ? 'true' : 'false') + '" enctype="multipart/form-data">';
    html += '<input type="hidden" name="task_id" value="' + d.id + '">';
    
    
    html += '<div class="submit-field mb-3">';
    html += '  <label><i class="bi bi-link-45deg me-1"></i> Link Hasil (Google Drive, Canva, dll)</label>';
    html += '  <input type="url" name="result_link" id="taskResultLinkInput" placeholder="https://..." value="' + esc(d.result_link || '') + '">';
    html += '</div>';
    
    
    html += '<div class="submit-field mb-3">';
    html += '  <label><i class="bi bi-paperclip me-1"></i> Upload File Hasil Pekerjaan</label>';
    html += '  <input type="file" name="file_attachment" id="taskFileInput" class="form-control" style="font-size:13px;" accept="*/*">';
    if (d.result_file_attachment) {
        html += '  <div class="mt-2"><a href="' + esc(d.result_file_attachment) + '" target="_blank" class="btn-task-link file" title="Unduh File Hasil Pekerjaan"><i class="bi bi-file-earmark-arrow-down"></i> File Hasil Pekerjaan terunggah</a></div>';
    }
    html += '</div>';
    
    
    html += '<div class="submit-field mb-3">';
    html += '  <label><i class="bi bi-chat-dots me-1"></i> Catatan Pekerjaan (opsional)</label>';
    html += '  <textarea name="user_notes" rows="4" style="min-height:95px;" placeholder="Catatan tambahan...">' + esc(d.user_notes || '') + '</textarea>';
    html += '</div>';
    
    html += '</form>';
    
    body.innerHTML = html;
    
    footer.innerHTML = '<button type="button" class="btn btn-ghost" onclick="closeTaskSubmitModal()">Tutup</button>' +
        '<button type="button" class="btn btn-primary" onclick="submitTaskForApproval(' + d.id + ')"><i class="bi bi-send me-1"></i> ' + (isPendingApproval ? 'Kirim Ulang' : 'Kirim untuk Approval') + '</button>';
}

function submitTaskForApproval(taskId) {
    var form = document.getElementById('submitTaskForm');
    if (!form) return;
    
    var resultLinkInput = form.querySelector('[name="result_link"]');
    var fileInput = form.querySelector('[name="file_attachment"]');
    
    var resultLink = resultLinkInput ? resultLinkInput.value.trim() : '';
    var hasFileSelected = fileInput && fileInput.files && fileInput.files.length > 0;
    var hasExistingFile = form.getAttribute('data-has-existing-file') === 'true';
    
    if (!resultLink && !hasFileSelected && !hasExistingFile) {
        showToast('warning', 'Peringatan', 'Task tidak dapat dikirim. Wajib mengisi Link Hasil atau mengunggah File Hasil terlebih dahulu!');
        if (resultLinkInput) resultLinkInput.focus();
        return;
    }

    var formData = new FormData(form);

    var MAX_TASK_FILE_SIZE = 256 * 1024 * 1024; 
    if (hasFileSelected && fileInput.files[0].size > MAX_TASK_FILE_SIZE) {
        showToast('warning', 'Peringatan', 'File terlalu besar. Maksimal 256MB. Untuk file besar, gunakan Link Hasil (Google Drive).');
        return;
    }

    
    var overlay = document.getElementById('pageLoadingOverlay');
    if (overlay) overlay.classList.add('show');

    var btn = document.querySelector('#taskSubmitFooter .btn-primary');
    var originalLabel = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<span style="display:inline-block;width:14px;height:14px;border:2px solid #ffffff;border-top-color:transparent;border-radius:50%;animation:spin 0.7s linear infinite;vertical-align:-2px;margin-right:6px;"></span> Mengirim...';

    function restoreBtn() {
        if (overlay) overlay.classList.remove('show');
        btn.disabled = false;
        btn.innerHTML = originalLabel;
    }

    fetch(BASE_URL + '/timeline/submit-approval', { method: 'POST', body: formData })
        .then(function(r) { return r.text(); })
        .then(function(text) {
            var res = null;
            try { res = JSON.parse(text); } catch (e) {}
            if (!res) {
                showToast('error', 'Error', 'Terjadi kesalahan server. Silakan coba lagi atau hubungi admin.');
                restoreBtn();
                return;
            }
            if (res.success) {
                showToast('success', 'Berhasil', res.message);
                closeTaskSubmitModal();
                setTimeout(function() { location.reload(); }, 1000);
            } else {
                showToast('error', 'Gagal', res.message);
                restoreBtn();
            }
        })
        .catch(function() {
            showToast('error', 'Error', 'Terjadi kesalahan server. Periksa koneksi internet Anda.');
            restoreBtn();
        });
}

function closeTaskSubmitModal() {
    var modal = document.getElementById('taskSubmitModal');
    if (!modal || modal.classList.contains('closing')) return;
    modal.classList.add('closing');
    setTimeout(function() {
        modal.classList.remove('show', 'closing');
        document.body.style.overflow = '';
    }, 250);
}

document.addEventListener('DOMContentLoaded', function() {
    var modal = document.getElementById('taskSubmitModal');
    if (modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === this) {
                closeTaskSubmitModal();
            }
        });
    }
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            var m = document.getElementById('taskSubmitModal');
            if (m && m.classList.contains('show')) {
                closeTaskSubmitModal();
            }
        }
    });
});

document.addEventListener('DOMContentLoaded', function() {
    setTimeout(function() {
        var cards = document.querySelectorAll('.task-card-approval');
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
        <div class="page-loading-text">Mengirim untuk Approval...</div>
    </div>
</div>
