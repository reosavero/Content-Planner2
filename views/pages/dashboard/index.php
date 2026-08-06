<?php
$stats = $stats ?? [];
$trend = $trend ?? [];
$platformStats = $platformStats ?? [];
$recentActivities = $recentActivities ?? [];
$upcomingSchedules = $upcomingSchedules ?? [];
$popularPosts = $popularPosts ?? [];
$userName = Session::get('user_name', 'Pengguna');
$userRoleName = Session::get('user_role_name', ucwords(str_replace('_', ' ', Session::get('user_role_slug', ''))));
$hasAvatar = Session::get('user_avatar');
$isMagangDashboard = $isMagangDashboard ?? false;
$assignedTasks = $assignedTasks ?? [];
?>

<?php if ($isMagangDashboard): ?>
<?php $groupedTasks = $groupedTasks ?? []; ?>

<style>
@keyframes fadeUp {
    from { opacity: 0; transform: translateY(16px); }
    to { opacity: 1; transform: translateY(0); }
}
@keyframes slideUpFade {
    from { opacity: 0; transform: translateY(30px) scale(0.97); }
    to { opacity: 1; transform: translateY(0) scale(1); }
}
.task-card-dash {
    opacity: 0;
}
.task-card-dash.fade-up {
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
.magang-welcome { background: linear-gradient(135deg, #003399, #0055cc); color: #fff; border-radius: var(--radius-xl); padding: var(--space-6); margin-bottom: var(--space-5); }
.magang-badge { font-size: 10px; padding: 3px 10px; border-radius: var(--radius-full); font-weight: var(--font-weight-semibold); white-space: nowrap; }
.magang-badge.assigned { background: #e3f2fd; color: #1565c0; }
.magang-badge.progress { background: #f3e5f5; color: #6a1b9a; }
.magang-badge.pending { background: #fff3e0; color: #e65100; }
.magang-badge.approved { background: #e8f5e9; color: #2e7d32; }
.magang-badge.revision { background: #ffebee; color: #c62828; }
.magang-badge.neutral { background: #f5f5f5; color: #757575; }


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


.task-card-dash {
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
    min-height: 100px;
}
.task-card-dash:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 25px -5px rgba(0,0,0,0.1);
    border-color: #cbd5e1;
}
.task-card-dash .task-title {
    font-size: 14px;
    font-weight: 600;
    color: #0f172a;
    margin-top: 0;
    margin-bottom: 12px;
    line-height: 1.4;
}
.task-card-dash .task-meta {
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




.submit-field { margin-bottom: var(--space-3); }
.submit-field label { font-size: 11px; font-weight: var(--font-weight-semibold); color: var(--text-secondary); display: block; margin-bottom: 4px; }
.submit-field textarea, .submit-field input { width: 100%; padding: var(--space-2) var(--space-3); border: 1px solid var(--border-light); border-radius: var(--radius-md); font-size: var(--text-sm); background: var(--surface-primary); }
.submit-field textarea:focus, .submit-field input:focus { border-color: var(--tvri-blue); outline: none; box-shadow: 0 0 0 3px rgba(0,51,153,0.1); }


.revision-panel {
    background: #fff5f5;
    border: 1px solid #ffcdd2;
    border-left: 4px solid var(--danger);
    border-radius: var(--radius-md);
    padding: var(--space-3);
    margin-bottom: var(--space-4);
    font-size: var(--text-sm);
    white-space: pre-wrap;
}
.revision-panel-title {
    display: flex;
    align-items: center;
    gap: var(--space-2);
    font-weight: var(--font-weight-bold);
    color: var(--danger);
    font-size: var(--text-xs);
    text-transform: uppercase;
    margin-bottom: var(--space-2);
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
</style>

<div class="magang-welcome">
    <div class="text-xs text-uppercase opacity-75">Dashboard Magang</div>
    <h3 class="mt-1 mb-1" style="color: #fff;">Halo, <?= htmlspecialchars($userName) ?> 👋</h3>
    <div class="opacity-75">Task dari Admin. Klik card task untuk melihat detail dan mengirim hasil.</div>
</div>



<?php if (!empty($groupedTasks)): ?>
    <?php foreach ($groupedTasks as $date => $group):
        $dayNum = date('d', strtotime($date));
        $dayName = $group['day_name'];
        $taskCount = count($group['tasks']);
    ?>
    <div class="magang-date-group">
        <div class="magang-date-header">
            <span class="date-badge"><?= $dayNum ?></span>
            <span class="day-name"><?= htmlspecialchars($dayName) ?> (<?= date('d/m/Y', strtotime($date)) ?>)</span>
            <span class="task-count"><?= $taskCount ?> task</span>
        </div>
        <div class="magang-task-grid">
            <?php foreach ($group['tasks'] as $task): ?>
                <div class="task-card-dash" onclick="openTaskSubmitModal(<?= (int)$task['id'] ?>)">
                    <div class="task-title">
                        <?= htmlspecialchars($task['title'] ?: '(Belum ada judul)') ?>
                    </div>

                    <div class="task-meta">
                        <span><i class="bi bi-person me-1"></i> <?= htmlspecialchars($task['assigned_by_name'] ?: 'Admin') ?></span>
                        <?php if (!empty($task['deadline'])):
                            $dlActive = isset($task['deadline_active']) ? (int)$task['deadline_active'] : 1;
                            $dlColor = $dlActive ? '#c53030' : '#64748b';
                        ?>
                            <span style="color:<?= $dlColor ?>;"><i class="bi bi-clock me-1"></i> Deadline: <?= date('d/m/Y', strtotime($task['deadline'])) ?></span>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endforeach; ?>
<?php else: ?>
    <div class="card">
        <div class="card-body">
            <div class="empty-state p-5 text-center"><i class="bi bi-inbox fs-1 text-tertiary"></i><h5 class="mt-2">Belum Ada Task</h5><p class="text-tertiary">Task dari Admin akan tampil di sini.</p></div>
        </div>
    </div>
<?php endif; ?>


<div class="modal" id="taskSubmitModal" onclick="if (event.target === this) closeTaskSubmitModal();">
    <div class="modal-content" style="max-width:640px;max-height:90vh;">
        <div class="modal-header">
            <h5><i class="bi bi-send me-2"></i> Kirim Hasil Task</h5>
            <button type="button" class="modal-close" onclick="closeTaskSubmitModal()">&times;</button>
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
    var isApproved = d.status === 'Approved';

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
        html += '<div class="revision-panel">';
        html += '  <div class="revision-panel-title"><i class="bi bi-arrow-counterclockwise"></i> Catatan Revisi dari Admin</div>';
        html += esc(d.revision_notes);
        html += '</div>';
    }

    if (isApproved) {
        html += '<div class="text-center py-4 text-success"><i class="bi bi-check-circle-fill fs-1 d-block mb-2"></i><h5>Task Telah Disetujui</h5><p>Task ini sudah selesai dan masuk Archive.</p></div>';
        footer.innerHTML = '<button type="button" class="btn btn-ghost" onclick="closeTaskSubmitModal()">Tutup</button>';
        body.innerHTML = html;
        return;
    }

    if (isPendingApproval) {
        html += '<div class="text-center py-3" style="background:#fff3e0;border-radius:var(--radius-md);margin-bottom:var(--space-4);">';
        html += '  <p class="mb-0" style="color:#e65100;"><i class="bi bi-clock me-1"></i> Task sedang menunggu review Admin.</p>';
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

    var MAX_TASK_FILE_SIZE = 256 * 1024 * 1024;
    if (hasFileSelected && fileInput.files[0].size > MAX_TASK_FILE_SIZE) {
        showToast('warning', 'Peringatan', 'File terlalu besar. Maksimal 256MB. Untuk file besar, gunakan Link Hasil (Google Drive).');
        return;
    }

    var formData = new FormData(form);


    var overlay = document.getElementById('pageLoadingOverlay');
    if (overlay) overlay.classList.add('show');

    var btn = document.querySelector('#taskSubmitFooter .btn-primary');
    btn.disabled = true;
    btn.innerHTML = '<span style="display:inline-block;width:14px;height:14px;border:2px solid #ffffff;border-top-color:transparent;border-radius:50%;animation:spin 0.7s linear infinite;vertical-align:-2px;margin-right:6px;"></span> Mengirim...';

    function restoreBtn() {
        if (overlay) overlay.classList.remove('show');
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-send me-1"></i> Kirim untuk Approval';
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
        var cards = document.querySelectorAll('.task-card-dash');
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

<?php else: ?>
<style>

.welcome-card {
    background: linear-gradient(135deg, #003399 0%, #0047b3 50%, #0055cc 100%);
    border-radius: var(--radius-xl);
    padding: var(--space-8) var(--space-6);
    position: relative;
    overflow: hidden;
    color: white;
    margin-bottom: var(--space-5);
}
.welcome-card::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -20%;
    width: 400px;
    height: 400px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.05);
    pointer-events: none;
}
.welcome-card::after {
    content: '';
    position: absolute;
    bottom: -30%;
    left: 10%;
    width: 300px;
    height: 300px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.03);
    pointer-events: none;
}
.welcome-card-content {
    position: relative;
    z-index: 1;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: var(--space-4);
}
.welcome-card-text {
    flex: 1;
    min-width: 200px;
}
.welcome-card-greeting {
    font-size: var(--text-xs);
    font-weight: var(--font-weight-medium);
    opacity: 0.75;
    letter-spacing: var(--tracking-wide);
    text-transform: uppercase;
    margin-bottom: var(--space-1);
}
.welcome-card-title {
    font-family: var(--font-heading);
    font-size: var(--text-2xl);
    font-weight: var(--font-weight-bold);
    color: #fff;
    margin-bottom: var(--space-1);
}
.welcome-card-sub {
    font-size: var(--text-sm);
    opacity: 0.8;
    line-height: 1.5;
}
.welcome-card-actions {
    display: flex;
    gap: var(--space-2);
    flex-shrink: 0;
}
.welcome-card .btn {
    background: rgba(255, 255, 255, 0.15);
    color: white;
    border-color: rgba(255, 255, 255, 0.3);
    backdrop-filter: blur(8px);
}
.welcome-card .btn:hover {
    background: rgba(255, 255, 255, 0.25);
    border-color: rgba(255, 255, 255, 0.5);
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.2);
}


.platform-dist-item {
    display: flex;
    align-items: center;
    gap: var(--space-3);
    padding: var(--space-2) 0;
    border-bottom: 1px solid var(--border-light);
}
.platform-dist-item:last-child {
    border-bottom: none;
}
.platform-dist-icon {
    width: 32px;
    height: 32px;
    border-radius: var(--radius-md);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.platform-dist-icon img {
    width: 20px;
    height: 20px;
    object-fit: contain;
}
.platform-dist-info {
    flex: 1;
    min-width: 0;
}
.platform-dist-name {
    font-size: var(--text-sm);
    font-weight: var(--font-weight-medium);
    color: var(--text-primary);
}
.platform-dist-bar {
    height: 4px;
    background: var(--gray-100);
    border-radius: var(--radius-full);
    margin-top: 4px;
    overflow: hidden;
}
.platform-dist-bar-fill {
    height: 100%;
    border-radius: var(--radius-full);
    transition: width 0.6s ease;
}


.schedule-item {
    display: flex;
    align-items: center;
    gap: var(--space-3);
    padding: var(--space-3) var(--space-5);
    border-bottom: 1px solid var(--border-light);
    text-decoration: none;
    transition: background var(--transition-fast);
}
.schedule-item:hover {
    background: var(--surface-secondary);
}
.schedule-date {
    text-align: center;
    min-width: 44px;
    flex-shrink: 0;
}
.schedule-date-month {
    font-size: var(--text-xs);
    color: var(--text-tertiary);
    text-transform: uppercase;
}
.schedule-date-day {
    font-family: var(--font-heading);
    font-size: var(--text-lg);
    font-weight: var(--font-weight-bold);
    color: var(--tvri-blue);
    line-height: 1;
}
.schedule-info {
    flex: 1;
    min-width: 0;
}
.schedule-title {
    font-size: var(--text-sm);
    font-weight: var(--font-weight-medium);
    color: var(--text-primary);
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.schedule-meta {
    display: flex;
    align-items: center;
    gap: var(--space-2);
    margin-top: 2px;
}


.quick-stat {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: var(--space-4);
    text-align: center;
    border-radius: var(--radius-lg);
    background: var(--surface-secondary);
    border: 1px solid var(--border-light);
}
.quick-stat-value {
    font-family: var(--font-heading);
    font-size: var(--text-2xl);
    font-weight: var(--font-weight-bold);
    color: var(--tvri-blue);
    line-height: 1;
}
.quick-stat-label {
    font-size: var(--text-xs);
    color: var(--text-tertiary);
    margin-top: var(--space-1);
}


.popular-post-item {
    display: flex;
    align-items: center;
    gap: var(--space-3);
    padding: var(--space-3) var(--space-5);
    border-bottom: 1px solid var(--border-light);
    text-decoration: none;
    transition: background var(--transition-fast);
}
.popular-post-item:hover {
    background: var(--surface-secondary);
}
.popular-post-rank {
    width: 28px;
    height: 28px;
    border-radius: var(--radius-full);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: var(--text-xs);
    font-weight: var(--font-weight-bold);
    flex-shrink: 0;
}
.popular-post-rank-1 { background: linear-gradient(135deg, #ffd700, #ffb300); color: #fff; }
.popular-post-rank-2 { background: linear-gradient(135deg, #e0e0e0, #bdbdbd); color: #fff; }
.popular-post-rank-3 { background: linear-gradient(135deg, #cd7f32, #b87333); color: #fff; }
.popular-post-rank-other { background: var(--surface-tertiary); color: var(--text-tertiary); }

.popular-post-info {
    flex: 1;
    min-width: 0;
}
.popular-post-title {
    font-size: var(--text-sm);
    font-weight: var(--font-weight-medium);
    color: var(--text-primary);
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.popular-post-engagement {
    display: flex;
    align-items: center;
    gap: var(--space-3);
    margin-top: 2px;
    font-size: var(--text-xs);
    color: var(--text-secondary);
}
.popular-post-engagement i {
    margin-right: 2px;
}
</style>


<div class="welcome-card">
    <div class="welcome-card-content">
        <div class="welcome-card-text">
            <div class="welcome-card-greeting">
                <i class="bi bi-sun"></i> Selamat <?= date('H') < 12 ? 'Pagi' : (date('H') < 18 ? 'Siang' : 'Malam') ?>,
            </div>
            <div class="welcome-card-title">
                <?= htmlspecialchars($userName) ?> 👋
            </div>
            <div class="welcome-card-sub">
                <?= htmlspecialchars($userRoleName) ?> · TVRI Jawa Timur
                · <?= date('d F Y') ?>
            </div>
        </div>
        <div class="welcome-card-actions">
            <a href="<?= BASE_URL ?>/planning/create" class="btn btn-sm">
                <i class="bi bi-plus-lg"></i> Buat Konten
            </a>
            <a href="<?= BASE_URL ?>/calendar" class="btn btn-sm">
                <i class="bi bi-calendar3"></i> Kalender
            </a>
        </div>
    </div>
</div>


<div class="row stagger">
    <div class="col-12 col-md-6 col-lg-3">
        <div class="card card-accent-primary">
            <div class="stat-card">
                <div class="stat-card-icon stat-card-icon-primary">
                    <i class="bi bi-calendar-check-fill"></i>
                </div>
                <div class="stat-card-body">
                    <div class="stat-card-label">Total Planning</div>
                    <div class="stat-card-value"><?= number_format($stats['total_planning'] ?? 0) ?></div>
                    <?php if (!empty($trend['planning'])): ?>
                        <div class="stat-card-change <?= ($trend['planning'] ?? 0) >= 0 ? 'stat-card-change-up' : 'stat-card-change-down' ?>">
                            <i class="bi bi-<?= ($trend['planning'] ?? 0) >= 0 ? 'arrow-up' : 'arrow-down' ?>"></i>
                            <?= number_format(abs($trend['planning'] ?? 0)) ?>% bulan ini
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-md-6 col-lg-3">
        <div class="card card-accent-success">
            <div class="stat-card">
                <div class="stat-card-icon stat-card-icon-success">
                    <i class="bi bi-check-circle-fill"></i>
                </div>
                <div class="stat-card-body">
                    <div class="stat-card-label">Berhasil Posting</div>
                    <div class="stat-card-value"><?= number_format($stats['total_success'] ?? 0) ?></div>
                    <?php if (!empty($trend['success'])): ?>
                        <div class="stat-card-change <?= ($trend['success'] ?? 0) >= 0 ? 'stat-card-change-up' : 'stat-card-change-down' ?>">
                            <i class="bi bi-<?= ($trend['success'] ?? 0) >= 0 ? 'arrow-up' : 'arrow-down' ?>"></i>
                            <?= number_format(abs($trend['success'] ?? 0)) ?>% bulan ini
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-md-6 col-lg-3">
        <div class="card card-accent-warning">
            <div class="stat-card">
                <div class="stat-card-icon stat-card-icon-warning">
                    <i class="bi bi-clock-fill"></i>
                </div>
                <div class="stat-card-body">
                    <div class="stat-card-label">Terjadwal</div>
                    <div class="stat-card-value"><?= number_format($stats['total_scheduled'] ?? 0) ?></div>
                    <div class="stat-card-change stat-card-change-up">
                        <i class="bi bi-calendar"></i> Minggu ini
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-md-6 col-lg-3">
        <div class="card card-accent-info">
            <div class="stat-card">
                <div class="stat-card-icon stat-card-icon-info">
                    <i class="bi bi-link-45deg"></i>
                </div>
                <div class="stat-card-body">
                    <div class="stat-card-label">Akun Terhubung</div>
                    <div class="stat-card-value"><?= number_format($stats['connected_accounts'] ?? 0) ?></div>
                    <div class="stat-card-change stat-card-change-up">
                        <i class="bi bi-share"></i> <?= number_format($stats['total_platforms'] ?? 0) ?> platform
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<div class="row">

    <div class="col-12 col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5><i class="bi bi-bar-chart-line-fill"></i> Tren Posting</h5>
                <div class="btn-group" style="gap: 4px;">
                    <button class="btn btn-sm <?= ($_GET['period'] ?? 'month') === 'week' ? 'active' : 'btn-outline' ?>" onclick="window.location.href='?period=week'">7H</button>
                    <button class="btn btn-sm <?= ($_GET['period'] ?? 'month') === 'month' ? 'active' : 'btn-outline' ?>" onclick="window.location.href='?period=month'">30H</button>
                    <button class="btn btn-sm <?= ($_GET['period'] ?? 'month') === 'year' ? 'active' : 'btn-outline' ?>" onclick="window.location.href='?period=year'">1T</button>
                </div>
            </div>
            <div class="card-body">
                <canvas id="trendChart" height="200"></canvas>
            </div>
        </div>
    </div>


    <div class="col-12 col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5><i class="bi bi-pie-chart-fill"></i> Distribusi Platform</h5>
            </div>
            <div class="card-body">
                <div style="position: relative; max-width: 200px; margin: 0 auto var(--space-4);">
                    <canvas id="platformChart" height="200"></canvas>
                </div>
                <?php if (!empty($platformStats)): ?>
                    <div>
                        <?php
                        $maxTotal = max(array_column($platformStats, 'total')) ?: 1;
                        $platformIcons = [
                            'facebook' => 'facebook.svg',
                            'instagram' => 'instagram.svg',
                            'youtube' => 'youtube.svg',
                            'tiktok' => 'tiktok.svg',
                            'twitter' => 'twitter-x.svg',
                            'x' => 'twitter-x.svg',
                            'threads' => 'threads.svg',
                        ];
                        ?>
                        <?php foreach ($platformStats as $p): ?>
                            <?php
                            $slug = strtolower($p['slug'] ?? $p['name'] ?? '');
                            $iconFile = $platformIcons[$slug] ?? '';
                            $pct = ($p['total'] / $maxTotal) * 100;
                            ?>
                            <div class="platform-dist-item">
                                <div class="platform-dist-icon" style="background: <?= $p['color'] ?? 'var(--surface-tertiary)' ?>20;">
                                    <?php if ($iconFile && file_exists(BASE_PATH . '/assets/img/platforms/' . $iconFile)): ?>
                                        <img src="<?= BASE_URL ?>/assets/img/platforms/<?= $iconFile ?>" alt="<?= htmlspecialchars($p['name']) ?>">
                                    <?php else: ?>
                                        <i class="bi bi-globe" style="color: <?= $p['color'] ?? 'var(--text-secondary)' ?>;"></i>
                                    <?php endif; ?>
                                </div>
                                <div class="platform-dist-info">
                                    <div class="platform-dist-name"><?= htmlspecialchars($p['name'] ?? '') ?></div>
                                    <div class="platform-dist-bar">
                                        <div class="platform-dist-bar-fill" style="width: <?= $pct ?>%; background: <?= $p['color'] ?? 'var(--tvri-blue)' ?>;"></div>
                                    </div>
                                </div>
                                <span style="font-weight: var(--font-weight-semibold); font-size: var(--text-sm); flex-shrink: 0;">
                                    <?= number_format($p['total'] ?? 0) ?>
                                </span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state" style="padding: var(--space-6) 0;">
                        <i class="bi bi-pie-chart"></i>
                        <h5>Belum Ada Data</h5>
                        <p>Data akan muncul setelah ada posting.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>


<div class="row" style="margin-top: 0;">

    <div class="col-12 col-lg-5">
        <div class="card">
            <div class="card-header">
                <h5><i class="bi bi-clock-history"></i> Jadwal Akan Datang</h5>
                <a href="<?= BASE_URL ?>/calendar" class="btn btn-sm btn-ghost">
                    <i class="bi bi-calendar3"></i>
                </a>
            </div>
            <div class="card-body p-0">
                <?php if (!empty($upcomingSchedules)): ?>
                    <div style="max-height: 400px; overflow-y: auto;">
                        <?php foreach ($upcomingSchedules as $schedule): ?>
                            <a href="<?= BASE_URL ?>/planning/<?= $schedule['id'] ?>" class="schedule-item">
                                <div class="schedule-date">
                                    <div class="schedule-date-month">
                                        <?= date('M', strtotime($schedule['scheduled_at'] ?? $schedule['tanggal_posting'])) ?>
                                    </div>
                                    <div class="schedule-date-day">
                                        <?= date('d', strtotime($schedule['scheduled_at'] ?? $schedule['tanggal_posting'])) ?>
                                    </div>
                                </div>
                                <div class="schedule-info">
                                    <div class="schedule-title">
                                        <?= htmlspecialchars($schedule['judul']) ?>
                                    </div>
                                    <div class="schedule-meta">
                                        <?php
                                        $sPlatformIcon = $platformIcons[strtolower($schedule['platform_slug'] ?? '')] ?? '';
                                        ?>
                                        <?php if ($sPlatformIcon && file_exists(BASE_PATH . '/assets/img/platforms/' . $sPlatformIcon)): ?>
                                            <img src="<?= BASE_URL ?>/assets/img/platforms/<?= $sPlatformIcon ?>"
                                                 style="width: 14px; height: 14px; object-fit: contain;" alt="">
                                        <?php else: ?>
                                            <i class="bi bi-globe" style="font-size: var(--text-xs); color: var(--text-tertiary);"></i>
                                        <?php endif; ?>
                                        <span style="font-size: var(--text-xs); color: var(--text-tertiary);">
                                            <?= htmlspecialchars($schedule['platform_name'] ?? '') ?>
                                        </span>
                                        <span style="font-size: var(--text-xs); color: var(--text-tertiary);">·</span>
                                        <span style="font-size: var(--text-xs); color: var(--text-tertiary);">
                                            <?= date('H:i', strtotime($schedule['jam_posting'] ?? $schedule['scheduled_at'])) ?>
                                        </span>
                                    </div>
                                </div>
                                <?= statusLabel($schedule['status']) ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="bi bi-calendar-check"></i>
                        <h5>Tidak Ada Jadwal</h5>
                        <p>Belum ada konten yang dijadwalkan.</p>
                        <a href="<?= BASE_URL ?>/planning/create" class="btn btn-primary btn-sm">
                            <i class="bi bi-plus"></i> Buat Planning
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>


    <div class="col-12 col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5><i class="bi bi-activity"></i> Aktivitas Terkini</h5>
                <a href="<?= BASE_URL ?>/activity-logs" class="btn btn-sm btn-ghost">
                    <i class="bi bi-arrow-right"></i>
                </a>
            </div>
            <div class="card-body p-0">
                <?php if (!empty($recentActivities)): ?>
                    <div class="timeline" style="padding: var(--space-5);">
                        <?php foreach ($recentActivities as $activity): ?>
                            <div class="timeline-item">
                                <div class="timeline-dot timeline-dot-<?php
                                    $type = $activity['type'] ?? 'info';
                                    if ($type === 'success') echo 'success';
                                    elseif ($type === 'error') echo 'danger';
                                    elseif ($type === 'warning') echo 'warning';
                                    else echo 'primary';
                                ?>">
                                    <i class="<?= iconClass($activity['icon'] ?? 'circle') ?>" style="font-size:10px;"></i>
                                </div>
                                <div class="timeline-content">
                                    <div class="timeline-title"><?= htmlspecialchars($activity['title'] ?? '') ?></div>
                                    <div class="timeline-text"><?= htmlspecialchars($activity['description'] ?? '') ?></div>
                                    <div class="timeline-time">
                                        <i class="bi bi-clock"></i> <?= waktuLalu($activity['created_at'] ?? '') ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="bi bi-activity"></i>
                        <h5>Belum Ada Aktivitas</h5>
                        <p>Aktivitas terbaru akan muncul di sini.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>


    <div class="col-12 col-lg-3">
        <div class="card">
            <div class="card-header">
                <h5><i class="bi bi-trophy-fill" style="color: #ffd700;"></i> Posting Populer</h5>
            </div>
            <div class="card-body p-0">
                <?php if (!empty($popularPosts)): ?>
                    <div style="max-height: 400px; overflow-y: auto;">
                        <?php foreach ($popularPosts as $i => $post): ?>
                            <a href="<?= BASE_URL ?>/planning/<?= $post['id'] ?>" class="popular-post-item">
                                <div class="popular-post-rank <?= $i < 3 ? 'popular-post-rank-' . ($i+1) : 'popular-post-rank-other' ?>">
                                    <?= $i + 1 ?>
                                </div>
                                <div class="popular-post-info">
                                    <div class="popular-post-title">
                                        <?= htmlspecialchars(truncateText($post['judul'], 35)) ?>
                                    </div>
                                    <div class="popular-post-engagement">
                                        <span><i class="bi bi-heart-fill" style="color: var(--danger);"></i> <?= number_format($post['engagement_like'] ?? 0) ?></span>
                                        <span><i class="bi bi-chat-fill" style="color: var(--info);"></i> <?= number_format($post['engagement_comment'] ?? 0) ?></span>
                                        <span><i class="bi bi-eye-fill" style="color: var(--success);"></i> <?= number_format($post['views'] ?? 0) ?></span>
                                    </div>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="bi bi-trophy"></i>
                        <h5>Belum Ada Data</h5>
                        <p>Data posting populer akan muncul setelah ada posting.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>



<script>
document.addEventListener('DOMContentLoaded', function() {

    var trendCtx = document.getElementById('trendChart');
    if (trendCtx) {
        var trend = <?= json_encode($trend['chart_data'] ?? []) ?>;
        var isDark = document.documentElement.getAttribute('data-theme') === 'dark';
        var gridColor = isDark ? 'rgba(255,255,255,0.04)' : 'rgba(0,0,0,0.04)';

        new Chart(trendCtx, {
            type: 'bar',
            data: {
                labels: trend.labels || [],
                datasets: [{
                    label: 'Posting',
                    data: trend.data || [],
                    backgroundColor: isDark ? 'rgba(77, 124, 255, 0.1)' : 'rgba(0, 51, 153, 0.06)',
                    borderColor: '#003399',
                    borderWidth: 2,
                    borderRadius: 6,
                    borderSkipped: false,
                    pointRadius: 0,
                    tension: 0.3,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: gridColor },
                        ticks: { font: { size: 10 } }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { font: { size: 10 } }
                    }
                }
            }
        });
    }

    var platCtx = document.getElementById('platformChart');
    if (platCtx) {
        var platformData = <?= json_encode($platformStats ?? []) ?>;
        var defaultColors = ['#1877F2', '#E4405F', '#FF0000', '#000000', '#1DA1F2', '#000000'];
        var bgColors = platformData.map(function(p, i) { return p.color || defaultColors[i % defaultColors.length]; });

        new Chart(platCtx, {
            type: 'doughnut',
            data: {
                labels: platformData.map(function(p) { return p.name }),
                datasets: [{
                    data: platformData.map(function(p) { return p.total }),
                    backgroundColor: bgColors,
                    borderWidth: 0,
                    hoverOffset: 8,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '78%',
                plugins: {
                    legend: { display: false }
                }
            }
        });
    }
});
</script>

<?php endif; ?>
