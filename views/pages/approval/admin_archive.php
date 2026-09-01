<?php
$groupedMonths = $groupedMonths ?? [];
$totalTasks = $totalTasks ?? 0;
$isSuperAdmin = (Session::get('user_role_slug') === 'superadmin');
$monthNamesIndo = [
    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni',
    7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
];
$dayNamesIndo = [
    'Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa',
    'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'
];

function adminStatusBadge($status) {
    $map = [
        'Approved' => ['label' => 'Approved', 'class' => 'badge-approve'],
        'Publish' => ['label' => 'Selesai', 'class' => 'badge-selesai'],
        'Selesai' => ['label' => 'Selesai', 'class' => 'badge-selesai'],
    ];
    $s = $map[$status] ?? ['label' => $status, 'class' => 'badge-secondary'];
    return '<span class="badge ' . $s['class'] . '" style="font-size:11px; padding:2px 10px; border-radius:6px; font-weight:600;">' . htmlspecialchars($s['label']) . '</span>';
}


$monthsJson = [];
foreach ($groupedMonths as $mk => $month) {
    $m = [
        'key' => $month['key'],
        'name' => $month['name'],
        'year' => $month['year'],
        'month' => $month['month'],
        'total_tasks' => $month['total_tasks'],
        'date_groups' => []
    ];
    foreach ($month['date_groups'] as $date => $grp) {
        $g = [
            'date' => $date,
            'day_name' => $grp['day_name'],
            'tasks' => []
        ];
        foreach ($grp['tasks'] as $task) {
            $g['tasks'][] = [
                'id' => (int)$task['id'],
                'title' => $task['title'] ?? '(Belum ada judul)',
                'task_date' => $task['task_date'] ?? '',
                'deadline' => $task['deadline'] ?? '',
                'catatan' => $task['catatan'] ?? '',
                'status' => $task['status'] ?? '',
                'assignee_name' => $task['assignee_name'] ?? $task['pic_name'] ?? '-',
                'assigned_by_name' => $task['assigned_by_name'] ?? '',
                'platform_name' => $task['platform_name'] ?? '',
                'platform_icon' => $task['platform_icon'] ?? '',
                'platform_color' => $task['platform_color'] ?? '',
                'result_link' => $task['result_link'] ?? '',
                'result_file_attachment' => $task['result_file_attachment'] ?? '',
                'user_notes' => $task['user_notes'] ?? '',
                'description_update' => $task['description_update'] ?? '',
                'screenshot_attachment' => $task['screenshot_attachment'] ?? '',
                'video_attachment' => $task['video_attachment'] ?? '',
                'revision_notes' => $task['revision_notes'] ?? '',
                'approved_at' => $task['approved_at'] ?? '',
                'approved_by' => $task['approved_by'] ?? '',
                'approver_name' => $task['approver_name'] ?? '',
            ];
        }
        $m['date_groups'][] = $g;
    }
    $monthsJson[] = $m;
}
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
@keyframes slideDownFade {
    from { opacity: 1; transform: translateY(0) scale(1); }
    to { opacity: 0; transform: translateY(20px) scale(0.97); }
}


.month-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    padding: 24px;
    cursor: pointer;
    transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    min-height: 140px;
    text-align: center;
}
.month-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 30px -8px rgba(0, 51, 153, 0.15);
    border-color: #003399;
}
.month-card:active {
    transform: translateY(-2px);
}
.month-card .month-icon {
    font-size: 32px;
    color: #003399;
    margin-bottom: 8px;
}
.month-card .month-name {
    font-size: 16px;
    font-weight: 700;
    color: #1e293b;
    margin-bottom: 4px;
}
.month-card .month-count {
    font-size: 12px;
    color: #64748b;
    background: #f1f5f9;
    padding: 2px 12px;
    border-radius: 20px;
    font-weight: 600;
}
.month-card.fade-up {
    animation: fadeUp 0.4s ease-out both;
}


.back-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 16px;
    border-radius: 10px;
    font-size: 13px;
    font-weight: 600;
    background: #f1f5f9;
    color: #475569;
    border: 1px solid #e2e8f0;
    cursor: pointer;
    transition: all 0.2s;
}
.back-btn:hover {
    background: #e2e8f0;
    color: #1e293b;
}
.back-btn.header-back {
    background: rgba(255, 255, 255, 0.15);
    color: #fff;
    border: 1px solid rgba(255, 255, 255, 0.35);
    backdrop-filter: blur(4px);
    width: 38px;
    height: 38px;
    padding: 0;
    justify-content: center;
    border-radius: 10px;
    font-size: 15px;
}
.back-btn.header-back:hover {
    background: rgba(255, 255, 255, 0.28);
    color: #fff;
    transform: translateY(-1px);
}
[data-theme="dark"] .back-btn.header-back {
    background: rgba(255, 255, 255, 0.2) !important;
    color: #ffffff !important;
    border-color: rgba(255, 255, 255, 0.4) !important;
}
[data-theme="dark"] .back-btn.header-back:hover {
    background: rgba(255, 255, 255, 0.35) !important;
    color: #ffffff !important;
}


.archive-timeline-header .archive-delete-btn {
    position: relative;
    z-index: 1;
    width: 38px;
    height: 38px;
    border-radius: 10px;
    border: 1px solid rgba(255, 255, 255, 0.35);
    background: rgba(255, 255, 255, 0.15);
    color: #fff;
    backdrop-filter: blur(4px);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 15px;
    cursor: pointer;
    transition: all 0.2s;
    flex-shrink: 0;
}
.archive-timeline-header .archive-delete-btn:hover {
    background: #dc2626;
    border-color: #dc2626;
    color: #fff;
    transform: translateY(-1px);
    box-shadow: 0 6px 16px -4px rgba(220, 38, 38, 0.5);
}


.archive-timeline-header {
    position: relative;
    overflow: hidden;
    background: linear-gradient(135deg, #001b5e 0%, #003399 45%, #1a56db 100%);
    border-radius: 16px;
    padding: 22px 26px;
    margin-bottom: 20px;
    color: #fff;
    box-shadow: 0 12px 34px -6px rgba(0, 51, 153, 0.45);
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
}
.archive-timeline-header::before,
.archive-timeline-header::after {
    content: '';
    position: absolute;
    border-radius: 50%;
    pointer-events: none;
}
.archive-timeline-header::before {
    width: 230px;
    height: 230px;
    top: -115px;
    right: -50px;
    background: rgba(255, 255, 255, 0.08);
}
.archive-timeline-header::after {
    width: 150px;
    height: 150px;
    bottom: -85px;
    right: 130px;
    background: rgba(255, 255, 255, 0.05);
}
.archive-timeline-header .banner-icon {
    width: 52px;
    height: 52px;
    border-radius: 14px;
    background: rgba(255, 255, 255, 0.15);
    border: 1px solid rgba(255, 255, 255, 0.28);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    color: #fff;
    box-shadow: 0 6px 16px -4px rgba(0, 0, 0, 0.3);
    flex-shrink: 0;
}
.archive-timeline-header .banner-label {
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1.2px;
    opacity: 0.75;
    margin-bottom: 2px;
}
.archive-timeline-header h4 {
    font-weight: 800;
    margin: 0;
    font-size: 22px;
    color: #fff;
    display: flex;
    align-items: center;
    gap: 10px;
    text-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
}


.archive-day-header {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px 16px;
    margin: 16px 0 8px 0;
    border-radius: 10px;
    background: #f8fafc;
    border-left: 4px solid #003399;
}
.archive-day-header .day-badge {
    width: 36px;
    height: 36px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #003399;
    color: #fff;
    font-weight: 700;
    font-size: 14px;
    border-radius: 8px;
    flex-shrink: 0;
}
.archive-day-header .day-info {
    font-size: 13px;
    font-weight: 600;
    color: #1e293b;
}
.archive-day-header .day-info .day-count {
    font-weight: 400;
    color: #94a3b8;
    margin-left: 8px;
}


.archive-task-card {
    background: var(--surface, #ffffff);
    border: 1px solid var(--border-light, #e2e8f0);
    border-radius: 14px;
    padding: 18px;
    margin-bottom: 0;
    transition: all 0.22s cubic-bezier(0.4, 0, 0.2, 1);
    cursor: pointer;
    position: relative;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}
.archive-task-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 28px -6px rgba(0, 51, 153, 0.12);
    border-color: #bfdbfe;
}
.archive-task-card .task-title {
    font-size: 15px;
    font-weight: 700;
    color: var(--text-primary, #1e293b);
    margin-bottom: 8px;
    line-height: 1.45;
    padding-top: 2px;
    padding-right: 95px;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
.archive-task-card .task-meta {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 12px;
    font-size: 11px;
    color: var(--text-secondary, #64748b);
    margin-top: auto;
    padding-top: 10px;
    border-top: 1px solid var(--border-light, #e2e8f0);
}
.archive-task-card .task-meta span {
    display: inline-flex;
    align-items: center;
    gap: 4px;
}
.archive-task-card .task-meta .assignee {
    color: #475569;
    font-weight: 500;
    margin-right: auto;
}
.archive-task-card.fade-up {
    animation: fadeUp 0.4s ease-out both;
}
.archive-task-card .task-delete-btn {
    width: 26px;
    height: 26px;
    border-radius: 8px;
    border: 1px solid #fecaca;
    background: #fef2f2;
    color: #dc2626;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    cursor: pointer;
    transition: all 0.2s;
    opacity: 0.85;
    flex-shrink: 0;
    padding: 0;
}
.archive-task-card .task-delete-btn:hover {
    background: #dc2626;
    color: #fff;
    border-color: #dc2626;
    transform: scale(1.1);
    opacity: 1;
}


.archive-task-card .status-badge-top,
.archive-task-card .task-status-badge {
    position: absolute;
    top: 12px;
    right: 12px;
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 0.3px;
    text-transform: uppercase;
    line-height: 1.3;
    z-index: 2;
    pointer-events: none;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
    border: 1px solid rgba(255, 255, 255, 0.3);
}
.archive-task-card .task-status-badge.status-approved {
    background: #dcfce7;
    color: #166534;
    border-color: #86efac;
}
.archive-task-card .task-status-badge.status-selesai {
    background: #d1fae5;
    color: #065f46;
    border-color: #6ee7b7;
}
.archive-task-card .task-status-badge.status-other {
    background: #e2e8f0;
    color: #475569;
    border-color: #cbd5e1;
}


.date-group {
    margin-bottom: 28px !important;
}
.date-header {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 14px 20px;
    background: var(--surface, #ffffff);
    border-radius: 14px 14px 0 0;
    border: 1px solid var(--border-light, #e2e8f0);
    border-bottom: none;
    scroll-margin-top: 120px;
}
.date-header .date-badge {
    background: var(--tvri-blue-gradient, linear-gradient(135deg, #003399, #0047b3));
    color: white;
    border-radius: 10px;
    padding: 5px 16px;
    font-size: 14px;
    font-weight: 700;
    box-shadow: 0 2px 8px rgba(0, 51, 153, 0.25);
}
.date-header .day-name {
    font-size: 15px;
    font-weight: 700;
    color: var(--text-primary, #1e293b);
}
.task-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(290px, 1fr));
    gap: 16px;
    padding: 20px;
    background: var(--surface, #ffffff);
    border: 1px solid var(--border-light, #e2e8f0);
    border-radius: 0 0 14px 14px;
}


#archiveModal .review-section {
    background: #f8fafc;
    border-radius: 12px;
    padding: 16px;
    margin-bottom: 16px;
}
#archiveModal .review-section-title {
    font-size: 13px;
    font-weight: 700;
    color: #1e293b;
    margin-bottom: 12px;
    display: flex;
    align-items: center;
    gap: 8px;
}
#archiveModal .review-grid {
    display: grid;
    gap: 8px;
}
#archiveModal .review-field {
    display: flex;
    flex-direction: column;
    gap: 2px;
}
#archiveModal .review-field-label {
    font-size: 11px;
    font-weight: 600;
    color: #94a3b8;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}
#archiveModal .review-field-value {
    font-size: 13px;
    color: #1e293b;
}
.btn-task-link {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 5px 14px;
    border-radius: 8px;
    font-size: 12px;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.18s ease;
    background: #f1f5f9;
    color: #1e293b;
    border: 1px solid #e2e8f0;
}
.btn-task-link:hover {
    background: #e2e8f0;
    color: #0f172a;
    transform: translateY(-1px);
}
.btn-task-link.material {
    background: #eff6ff;
    color: #1e40af;
    border-color: #bfdbfe;
}
.btn-task-link.material:hover {
    background: #dbeafe;
}
.btn-task-link.edit {
    background: #fef3c7;
    color: #92400e;
    border-color: #fde68a;
}
.btn-task-link.edit:hover {
    background: #fde68a;
}
.btn-task-link.file {
    background: #f0fdf4;
    color: #166534;
    border-color: #bbf7d0;
}
.btn-task-link.file:hover {
    background: #dcfce7;
}
html.modal-open,
body.modal-open {
    overflow: hidden !important;
    height: 100vh !important;
    touch-action: none;
    overscroll-behavior: none !important;
}
body.modal-open .main-content,
body.modal-open .page-content {
    overflow: hidden !important;
}
#archiveModal .modal-body {
    max-height: calc(85vh - 130px);
    overflow-y: auto !important;
}
#archiveModal.show .modal-content {
    animation: slideUpFade 0.35s cubic-bezier(0.34, 1.56, 0.64, 1) both !important;
}
#archiveModal.closing .modal-content {
    animation: slideDownFade 0.25s ease-in both !important;
}
#archiveDeleteModal.show .modal-content {
    animation: slideUpFade 0.35s cubic-bezier(0.34, 1.56, 0.64, 1) both !important;
}
#archiveDeleteModal.closing .modal-content {
    animation: slideDownFade 0.25s ease-in both !important;
}
</style>

<div class="container-fluid px-4 pt-3 pb-4">

    <div id="archiveHeader" class="d-flex justify-content-between align-items-center mb-4">
        <h4 style="font-weight:700; color:#1e293b; display:flex; align-items:center; gap:10px; margin:0;">
            <i class="bi bi-archive-fill" style="color:#16a34a;"></i> Archive — Riwayat Task
            <span class="badge" style="background:#e2e8f0; color:#475569; font-size:13px; font-weight:600; border-radius:8px; padding:3px 12px;"><?= $totalTasks ?> Task</span>
        </h4>
    </div>


    <div id="monthView">
        <?php if (empty($groupedMonths)): ?>
            <div class="text-center py-5" style="color:#94a3b8;">
                <i class="bi bi-archive" style="font-size:48px; display:block; margin-bottom:12px;"></i>
                <h5 style="font-weight:600;">Belum Ada Task di Archive</h5>
                <p style="font-size:14px;">Task yang sudah disetujui akan muncul di sini.</p>
            </div>
        <?php else: ?>
            <div class="row g-3">
                <?php $cardIdx = 0; ?>
                <?php foreach ($groupedMonths as $month):
                    $monthNum = $month['month'];
                    $monthShort = $monthNamesIndo[$monthNum] ?? '';
                ?>
                    <div class="col-6 col-md-4 col-lg-3 col-xl-2">
                        <div class="month-card fade-up" data-month="<?= htmlspecialchars($month['key'], ENT_QUOTES) ?>" style="animation-delay:<?= round($cardIdx * 0.06, 2) ?>s;" onclick="showMonthTasks('<?= htmlspecialchars($month['key'], ENT_QUOTES) ?>')">
                            <div class="month-icon"><i class="bi bi-calendar3"></i></div>
                            <div class="month-name"><?= htmlspecialchars($monthShort) ?> <?= htmlspecialchars($month['year']) ?></div>
                            <div class="month-count"><?= $month['total_tasks'] ?> Task</div>
                        </div>
                    </div>
                <?php $cardIdx++; endforeach; ?>
            </div>
        <?php endif; ?>
    </div>


    <div id="taskListView" style="display:none;">
        <div class="archive-timeline-header" id="timelineHeader">
            <div class="d-flex align-items-center gap-3" style="position:relative; z-index:1;">
                <button class="back-btn header-back" onclick="showMonthView()" title="Kembali ke daftar bulan">
                    <i class="bi bi-arrow-left"></i>
                </button>
                <div class="banner-icon"><i class="bi bi-calendar3"></i></div>
                <div>
                    <div class="banner-label">Archive</div>
                    <h4 id="selectedMonthTitle"><span id="selectedMonthName"></span></h4>
                </div>
            </div>
            <?php if ($isSuperAdmin): ?>
            <button type="button" class="archive-delete-btn" id="archiveDeleteMonthBtn" title="Hapus semua task bulan ini dari archive dan database" onclick="confirmDeleteMonth(currentArchiveMonth, currentArchiveMonthName)"><i class="bi bi-trash3"></i></button>
            <?php endif; ?>
        </div>
        <div id="taskListContent"></div>
    </div>
</div>


<div class="modal" id="archiveModal" tabindex="-1" role="dialog" aria-hidden="true" onclick="if (event.target === this) closeArchiveModal();">
    <div class="modal-dialog modal-dialog-centered" style="max-width:640px; width:90%; margin:1.75rem auto;">
        <div class="modal-content border-0 shadow-lg" style="border-radius:16px; padding:16px;">
            <div class="modal-header border-0 pb-2" style="display:flex; justify-content:space-between; align-items:center;">
                <h5 style="font-weight:700; color:#1e293b; margin:0;"><i class="bi bi-archive-fill me-2" style="color:#16a34a;"></i> Detail Archive</h5>
                <button type="button" class="btn-close" onclick="closeArchiveModal()" aria-label="Tutup"></button>
            </div>
            <div class="modal-body pt-0" id="archiveModalBody"></div>
            <div class="modal-footer border-0 pt-2">
                <button type="button" class="btn btn-secondary" onclick="closeArchiveModal()" style="border-radius:10px; font-weight:600;">Tutup</button>
            </div>
        </div>
    </div>
</div>


<?php if ($isSuperAdmin): ?>
<div class="modal" id="archiveDeleteModal" tabindex="-1" role="dialog" aria-hidden="true" onclick="if (event.target === this) closeDeleteModal();">
    <div class="modal-dialog modal-dialog-centered" style="max-width:400px; width:90%; margin:1.75rem auto;">
        <div class="modal-content border-0 shadow-lg" style="border-radius:16px; padding:16px;">
            <div class="modal-body text-center" style="padding:20px 16px;">
                <div style="width:56px; height:56px; border-radius:50%; background:#fef2f2; border:1px solid #fecaca; display:flex; align-items:center; justify-content:center; margin:0 auto 14px;">
                    <i class="bi bi-trash3-fill" style="color:#dc2626; font-size:24px;"></i>
                </div>
                <h5 style="font-weight:700; color:#1e293b; margin-bottom:8px;">Hapus Archive Bulan</h5>
                <p id="archiveDeleteMessage" style="font-size:13px; color:#64748b; margin-bottom:20px;"></p>
                <div class="d-flex gap-2 justify-content-center">
                    <button type="button" class="btn btn-ghost" onclick="closeDeleteModal()">Batal</button>
                    <button type="button" class="btn btn-danger" id="archiveDeleteOk" style="border-radius:10px; font-weight:600;"><i class="bi bi-trash3 me-1"></i> Hapus</button>
                </div>
            </div>
        </div>
    </div>
</div>


<div class="modal" id="archiveDeleteTaskModal" tabindex="-1" role="dialog" aria-hidden="true" onclick="if (event.target === this) closeDeleteTaskModal();">
    <div class="modal-dialog modal-dialog-centered" style="max-width:400px; width:90%; margin:1.75rem auto;">
        <div class="modal-content border-0 shadow-lg" style="border-radius:16px; padding:16px;">
            <div class="modal-body text-center" style="padding:20px 16px;">
                <div style="width:56px; height:56px; border-radius:50%; background:#fef2f2; border:1px solid #fecaca; display:flex; align-items:center; justify-content:center; margin:0 auto 14px;">
                    <i class="bi bi-trash3-fill" style="color:#dc2626; font-size:24px;"></i>
                </div>
                <h5 style="font-weight:700; color:#1e293b; margin-bottom:8px;">Hapus Task Archive</h5>
                <p id="archiveDeleteTaskMessage" style="font-size:13px; color:#64748b; margin-bottom:20px;"></p>
                <div class="d-flex gap-2 justify-content-center">
                    <button type="button" class="btn btn-ghost" onclick="closeDeleteTaskModal()">Batal</button>
                    <button type="button" class="btn btn-danger" id="archiveDeleteTaskOk" style="border-radius:10px; font-weight:600;"><i class="bi bi-trash3 me-1"></i> Hapus</button>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<script>

var ARCHIVE_MONTHS = <?= json_encode($monthsJson) ?>;
var currentArchiveMonth = '';
var currentArchiveMonthName = '';
var IS_SUPERADMIN = <?= $isSuperAdmin ? 'true' : 'false' ?>;

function fmtDate(s) {
    if (!s) return '-';
    var p = s.split(' ')[0].split('-');
    return p.reverse().join('/');
}

function fmtDateTime(dt) {
    if (!dt) return '-';
    var parts = dt.split(' ');
    if (parts.length < 2) return fmtDate(dt);
    return fmtDate(parts[0]) + ' ' + parts[1].substring(0, 5);
}

function htmlspecialchars(str) {
    if (!str) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

function statusBadgeHtml(status) {
    var map = {
        'Approved': { label: 'Approved', cls: 'badge-approve' },
        'Publish': { label: 'Selesai', cls: 'badge-selesai' },
        'Selesai': { label: 'Selesai', cls: 'badge-selesai' },
    };
    var s = map[status] || { label: status, cls: 'badge-secondary' };
    return '<span class="badge ' + s.cls + '" style="font-size:10px; padding:2px 10px; border-radius:20px; font-weight:600;">' + htmlspecialchars(s.label) + '</span>';
}

function archiveStatusClass(status) {
    var map = { 'Approved': 'status-approved', 'Publish': 'status-selesai', 'Selesai': 'status-selesai' };
    return map[status] || 'status-other';
}
function archiveStatusLabel(status) {
    var map = { 'Approved': 'Approved', 'Publish': 'Selesai', 'Selesai': 'Selesai' };
    return map[status] || status;
}
function archiveStatusColor(status) {
    var map = { 'Approved': '#22c55e', 'Publish': '#10b981', 'Selesai': '#10b981' };
    return map[status] || '#64748b';
}

function showMonthView() {
    document.getElementById('monthView').style.display = '';
    document.getElementById('taskListView').style.display = 'none';
    document.getElementById('archiveHeader').style.display = '';
}

function showMonthTasks(monthKey) {
    var data = ARCHIVE_MONTHS.find(function(m) { return m.key === monthKey; });
    if (!data) return;

    document.getElementById('monthView').style.display = 'none';
    document.getElementById('archiveHeader').style.display = 'none';
    document.getElementById('taskListView').style.display = '';

    document.getElementById('selectedMonthName').textContent = data.name;

    currentArchiveMonth = monthKey;
    currentArchiveMonthName = data.name;

    var html = '';
    if (!data.date_groups || data.date_groups.length === 0) {
        html += '<div class="text-center py-5" style="color:#94a3b8;"><i class="bi bi-inbox fs-1 d-block mb-2"></i><p>Tidak ada task di bulan ini.</p></div>';
    } else {
        data.date_groups.forEach(function(group, gi) {
            var dayNum = group.date ? group.date.split('-')[2] : '?';
            html += '<div class="date-group">';
            html += '  <div class="date-header fade-up" style="animation-delay:' + (gi * 0.04) + 's;">';
            html += '    <span class="date-badge">' + dayNum + '</span>';
            html += '    <span class="day-name">' + htmlspecialchars(group.day_name) + ' (' + fmtDate(group.date) + ')</span>';
            html += '  </div>';
            html += '  <div class="task-grid">';

            group.tasks.forEach(function(task, ti) {
                html += '    <div class="archive-task-card fade-up" id="archive-task-' + task.id + '" style="animation-delay:' + ((gi * 4 + ti) * 0.04) + 's;" onclick="openArchiveDetail(' + task.id + ')">';
                html += '      <span class="task-status-badge ' + archiveStatusClass(task.status) + '">' + archiveStatusLabel(task.status) + '</span>';
                html += '      <div class="task-title">' + htmlspecialchars(task.title) + '</div>';
                html += '      <div class="task-meta">';
                html += '        <span class="assignee"><i class="bi bi-person-fill" style="color:#003399;"></i> ' + htmlspecialchars(task.assignee_name) + '</span>';
                if (IS_SUPERADMIN) {
                    html += '        <button type="button" class="task-delete-btn" title="Hapus task ini dari archive dan database" onclick="event.stopPropagation(); confirmDeleteTask(' + task.id + ')"><i class="bi bi-trash3"></i></button>';
                }
                html += '      </div>';
                html += '    </div>';
            });

            html += '  </div>';
            html += '</div>';
        });
    }

    var content = document.getElementById('taskListContent');
    content.innerHTML = html;

    void content.offsetHeight;
    var cards = content.querySelectorAll('.fade-up');
    cards.forEach(function(c) {
        c.classList.remove('fade-up');
        void c.offsetHeight;
        c.classList.add('fade-up');
    });

}

function openArchiveDetail(id) {
    var task = null;
    for (var mi = 0; mi < ARCHIVE_MONTHS.length; mi++) {
        for (var gi = 0; gi < ARCHIVE_MONTHS[mi].date_groups.length; gi++) {
            for (var ti = 0; ti < ARCHIVE_MONTHS[mi].date_groups[gi].tasks.length; ti++) {
                if (ARCHIVE_MONTHS[mi].date_groups[gi].tasks[ti].id === id) {
                    task = ARCHIVE_MONTHS[mi].date_groups[gi].tasks[ti];
                    break;
                }
            }
            if (task) break;
        }
        if (task) break;
    }

    if (!task) {
        fetchFromServer(id);
        return;
    }

    showDetailModal(task);
}

function fetchFromServer(id) {
    var modal = document.getElementById('archiveModal');
    var body = document.getElementById('archiveModalBody');
    body.innerHTML = '<div class="text-center py-4"><div class="spinner-border text-primary mb-2"></div><p class="text-tertiary" style="font-size:13px;">Memuat detail...</p></div>';
    modal.classList.add('show');
    modal.style.display = 'block';
    document.body.classList.add('modal-open');
    document.documentElement.classList.add('modal-open');

    fetch(BASE_URL + '/archive/' + id, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(function(r) { return r.json(); })
    .then(function(res) {
        if (res.success) {
            showDetailModal(res.data);
        } else {
            body.innerHTML = '<div class="text-center py-4 text-danger"><i class="bi bi-exclamation-triangle fs-1 d-block mb-2"></i><p>' + htmlspecialchars(res.message || 'Gagal memuat detail') + '</p></div>';
        }
    })
    .catch(function() {
        body.innerHTML = '<div class="text-center py-4 text-danger"><i class="bi bi-exclamation-triangle fs-1 d-block mb-2"></i><p>Terjadi kesalahan saat memuat detail archive</p></div>';
    });
}

function showDetailModal(d) {
    var modal = document.getElementById('archiveModal');
    var body = document.getElementById('archiveModalBody');

    var esc = htmlspecialchars;
    var stColor = archiveStatusColor(d.status);
    var stLabel = archiveStatusLabel(d.status);
    var showWorkflow = (d.status === 'Approved' || d.status === 'Selesai' || d.status === 'Publish');
    var showScheduleInfo = showWorkflow;

    var html = '';
    html += '<div style="margin-bottom: 20px; padding-bottom: 16px; border-bottom: 1px solid #f1f5f9;">';
    html += '  <div class="d-flex align-items-center gap-2 mb-2">';
    html += '    <span style="background:#e8eaf6; color:#1a237e; font-size:11px; font-weight:700; padding:4px 10px; border-radius:6px; text-transform:uppercase;">' + esc(d.content_type || d.platform_name || 'KONTEN') + '</span>';
    html += '    <span style="background:' + stColor + '20; color:' + stColor + '; font-size:10px; font-weight:700; padding:4px 10px; border-radius:20px; text-transform:uppercase; margin-left:auto; border:1px solid ' + stColor + '40;">' + stLabel + '</span>';
    html += '  </div>';
    html += '  <h4 style="font-size: 18px; font-weight: 700; color: #0f172a; margin: 0; line-height: 1.4;">' + esc(d.title || '(Belum ada judul)') + '</h4>';
    html += '</div>';

    html += '<div class="row g-3 mb-4">';
    html += '  <div class="col-6"><div style="background:#f8fafc; padding:12px 14px; border-radius:10px; border:1px solid #e2e8f0;"><span style="font-size:11px; color:#64748b; font-weight:600; text-transform:uppercase; display:block; margin-bottom:2px;"><i class="bi bi-person me-1"></i> PIC (Penanggung Jawab)</span><strong style="font-size:13px; color:#0f172a;">' + esc(d.assignee_name || '-') + '</strong></div></div>';
    if (d.deadline) {
        html += '  <div class="col-6"><div style="background:#fff5f5; padding:12px 14px; border-radius:10px; border:1px solid #fed7d7;"><span style="font-size:11px; color:#c53030; font-weight:600; text-transform:uppercase; display:block; margin-bottom:2px;"><i class="bi bi-clock me-1"></i> Deadline</span><strong style="font-size:13px; color:#9b2c2c;">' + fmtDate(d.deadline) + '</strong></div></div>';
    }
    html += '</div>';

    if (showWorkflow || d.approved_at || d.approver_name) {
        html += '<div class="mb-4">';
        html += '  <div class="d-flex justify-content-between align-items-center mb-2"><span style="font-size:12px; font-weight:700; color:#15803d; text-transform:uppercase;"><i class="bi bi-check-circle-fill me-1"></i> Informasi Persetujuan (Approved Info)</span></div>';
        html += '  <div class="row g-2">';
        html += '    <div class="col-6"><div style="background:#f0fdf4; padding:10px 12px; border-radius:10px; border:1px solid #bbf7d0;"><span style="font-size:11px; color:#15803d; font-weight:600; text-transform:uppercase; display:block; margin-bottom:2px;"><i class="bi bi-person-check me-1"></i> Disetujui Oleh</span><strong style="font-size:13px; color:#166534;">' + esc(d.approver_name || d.assigned_by_name || 'Admin / Superadmin') + '</strong></div></div>';
        html += '    <div class="col-6"><div style="background:#f0fdf4; padding:10px 12px; border-radius:10px; border:1px solid #bbf7d0;"><span style="font-size:11px; color:#15803d; font-weight:600; text-transform:uppercase; display:block; margin-bottom:2px;"><i class="bi bi-calendar-check me-1"></i> Tanggal Disetujui</span><strong style="font-size:13px; color:#166534;">' + (d.approved_at ? fmtDateTime(d.approved_at) : (d.updated_at ? fmtDateTime(d.updated_at) : '-')) + '</strong></div></div>';
        html += '  </div>';
        html += '</div>';
    }

    if (d.catatan) {
        html += '<div class="mb-4">';
        html += '  <div class="d-flex justify-content-between align-items-center mb-2"><span style="font-size:12px; font-weight:700; color:#334155; text-transform:uppercase;"><i class="bi bi-info-circle me-1"></i> Catatan & Instruksi</span></div>';
        html += '  <div style="background:#f1f5f9; border-left:4px solid #1a237e; border-radius:8px; padding:14px 16px; font-size:13px; color:#1e293b; line-height:1.6; white-space:pre-wrap;">' + esc(d.catatan) + '</div>';
        html += '</div>';
    }

    if (d.material_link || d.edit_link || d.file_attachment) {
        html += '<div class="mb-2">';
        html += '  <span style="font-size:12px; font-weight:700; color:#334155; text-transform:uppercase; display:block; margin-bottom:8px;"><i class="bi bi-link-45deg me-1"></i> Tautan & Lampiran</span>';
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
        html += '</div>';
    }

    if (showWorkflow) {
        var resultFile = d.result_file_attachment || (d.result_link ? null : d.file_attachment);
        var hasResult = d.description_update || d.user_notes || d.result_link || resultFile || d.screenshot_attachment || d.video_attachment;
        html += '<div class="mb-4">';
        html += '  <div class="d-flex justify-content-between align-items-center mb-2"><span style="font-size:12px; font-weight:700; color:#334155; text-transform:uppercase;"><i class="bi bi-file-earmark-check me-1"></i> Hasil Pekerjaan</span></div>';
        if (hasResult) {
            if (d.description_update) {
                html += '  <div style="background:#f0fdf4; border-left:4px solid #16a34a; border-radius:8px; padding:12px 14px; font-size:13px; color:#14532d; line-height:1.6; white-space:pre-wrap; margin-bottom:8px;"><span style="font-weight:700; display:block; font-size:11px; text-transform:uppercase; color:#15803d; margin-bottom:4px;">Deskripsi Update</span>' + esc(d.description_update) + '</div>';
            }
            if (d.user_notes) {
                html += '  <div style="background:#fffbeb; border-left:4px solid #f59e0b; border-radius:8px; padding:12px 14px; font-size:13px; color:#713f12; line-height:1.6; white-space:pre-wrap; margin-bottom:8px;"><span style="font-weight:700; display:block; font-size:11px; text-transform:uppercase; color:#b45309; margin-bottom:4px;">Catatan User</span>' + esc(d.user_notes) + '</div>';
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
            html += '  <div style="background:#f8fafc; border:1px dashed #cbd5e1; border-radius:8px; padding:14px; font-size:13px; color:#64748b; text-align:center;"><i class="bi bi-inbox me-1"></i> Belum ada hasil pekerjaan yang diunggah.</div>';
        }
        html += '</div>';

        if (showScheduleInfo) {
            html += '<div class="mb-4">';
            html += '  <div class="d-flex justify-content-between align-items-center mb-2"><span style="font-size:12px; font-weight:700; color:#334155; text-transform:uppercase;"><i class="bi bi-calendar-event me-1"></i> Informasi Schedule / Upload</span></div>';
            html += '  <div class="row g-2 schedule-info-row">';
            html += '    <div class="col-6"><div style="background:#eff6ff; padding:10px 12px; border-radius:10px; border:1px solid #bfdbfe;"><span style="font-size:11px; color:#1d4ed8; font-weight:600; text-transform:uppercase; display:block; margin-bottom:2px;"><i class="bi bi-calendar3 me-1"></i> Tanggal Upload</span><strong style="font-size:13px; color:#1e3a8a;">' + fmtDate(d.task_date) + '</strong></div></div>';
            html += '    <div class="col-6"><div style="background:#eff6ff; padding:10px 12px; border-radius:10px; border:1px solid #bfdbfe;"><span style="font-size:11px; color:#1d4ed8; font-weight:600; text-transform:uppercase; display:block; margin-bottom:2px;"><i class="bi bi-clock me-1"></i> Jam Upload</span><strong style="font-size:13px; color:#1e3a8a;">' + esc(d.scheduled_time || '-') + '</strong></div></div>';
            if (d.submitted_at) {
                html += '    <div class="col-6"><div style="background:#f8fafc; padding:10px 12px; border-radius:10px; border:1px solid #e2e8f0;"><span style="font-size:11px; color:#64748b; font-weight:600; text-transform:uppercase; display:block; margin-bottom:2px;"><i class="bi bi-send me-1"></i> Dikirim</span><strong style="font-size:13px; color:#0f172a;">' + fmtDateTime(d.submitted_at) + '</strong></div></div>';
            }
            if (d.approved_at) {
                html += '    <div class="col-6"><div style="background:#f0fdf4; padding:10px 12px; border-radius:10px; border:1px solid #bbf7d0;"><span style="font-size:11px; color:#15803d; font-weight:600; text-transform:uppercase; display:block; margin-bottom:2px;"><i class="bi bi-check-circle me-1"></i> Disetujui</span><strong style="font-size:13px; color:#166534;">' + fmtDateTime(d.approved_at) + '</strong></div></div>';
            }
            html += '  </div>';
            html += '</div>';
        }
    }

    body.innerHTML = html;

    modal.classList.add('show');
    modal.style.display = 'block';
    document.body.classList.add('modal-open');
    document.documentElement.classList.add('modal-open');
}

function closeArchiveModal() {
    var el = document.getElementById('archiveModal');
    if (!el || el.classList.contains('closing')) return;
    el.classList.add('closing');
    setTimeout(function() {
        el.classList.remove('show', 'closing');
        el.style.display = 'none';
        document.body.classList.remove('modal-open');
        document.documentElement.classList.remove('modal-open');
    }, 250);
}

var deleteMonthKey = null;
function confirmDeleteMonth(key, name) {
    deleteMonthKey = key;
    document.getElementById('archiveDeleteMessage').textContent = 'Yakin ingin menghapus semua task bulan ' + name + ' dari archive dan database?';
    var modal = document.getElementById('archiveDeleteModal');
    if (!modal) return;
    if (modal._closeTimer) { clearTimeout(modal._closeTimer); modal._closeTimer = null; }
    modal.classList.remove('closing');
    modal.classList.add('show');
    modal.style.display = 'block';
    document.body.classList.add('modal-open');
    document.documentElement.classList.add('modal-open');
}
function closeDeleteModal() {
    var modal = document.getElementById('archiveDeleteModal');
    if (!modal || modal.classList.contains('closing')) return;
    modal.classList.add('closing');
    if (modal._closeTimer) clearTimeout(modal._closeTimer);
    modal._closeTimer = setTimeout(function() {
        modal.classList.remove('show', 'closing');
        modal.style.display = 'none';
        document.body.classList.remove('modal-open');
        document.documentElement.classList.remove('modal-open');
    }, 250);
}
var deleteOkBtn = document.getElementById('archiveDeleteOk');
if (deleteOkBtn) {
    deleteOkBtn.addEventListener('click', function() {
        if (!deleteMonthKey) return;
        var btn = this;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Menghapus...';
        var fd = new FormData();
        fd.append('month', deleteMonthKey);
        fetch('<?= BASE_URL ?>/archive/delete-month', { method: 'POST', body: fd })
            .then(function(r) { return r.json(); })
            .then(function(res) {
                closeDeleteModal();
                if (res.success) {
                    showToast('success', 'Archive Terhapus', res.message || 'Semua task bulan terhapus dari database');
                    setTimeout(function() { location.reload(); }, 900);
                } else {
                    showToast('error', 'Gagal', res.message || 'Gagal menghapus archive');
                    btn.disabled = false;
                    btn.innerHTML = '<i class="bi bi-trash3 me-1"></i> Hapus';
                }
            })
            .catch(function() {
                showToast('error', 'Gagal', 'Terjadi kesalahan saat menghapus archive');
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-trash3 me-1"></i> Hapus';
            });
    });
}

var deleteTaskId = null;
function findArchiveTask(id) {
    for (var mi = 0; mi < ARCHIVE_MONTHS.length; mi++) {
        for (var gi = 0; gi < ARCHIVE_MONTHS[mi].date_groups.length; gi++) {
            for (var ti = 0; ti < ARCHIVE_MONTHS[mi].date_groups[gi].tasks.length; ti++) {
                if (ARCHIVE_MONTHS[mi].date_groups[gi].tasks[ti].id === id) {
                    return ARCHIVE_MONTHS[mi].date_groups[gi].tasks[ti];
                }
            }
        }
    }
    return null;
}
function removeArchiveTask(id) {
    for (var mi = 0; mi < ARCHIVE_MONTHS.length; mi++) {
        var groups = ARCHIVE_MONTHS[mi].date_groups;
        for (var gi = 0; gi < groups.length; gi++) {
            var tasks = groups[gi].tasks;
            for (var ti = 0; ti < tasks.length; ti++) {
                if (tasks[ti].id === id) {
                    tasks.splice(ti, 1);
                    if (tasks.length === 0) {
                        groups.splice(gi, 1);
                    }
                    if (ARCHIVE_MONTHS[mi].total_tasks > 0) {
                        ARCHIVE_MONTHS[mi].total_tasks--;
                    }
                    return mi;
                }
            }
        }
    }
    return -1;
}
function confirmDeleteTask(id) {
    var task = findArchiveTask(id);
    deleteTaskId = id;
    document.getElementById('archiveDeleteTaskMessage').textContent = 'Yakin ingin menghapus task "' + (task ? task.title : '') + '" dari archive dan database?';
    var modal = document.getElementById('archiveDeleteTaskModal');
    if (!modal) return;
    if (modal._closeTimer) { clearTimeout(modal._closeTimer); modal._closeTimer = null; }
    modal.classList.remove('closing');
    modal.classList.add('show');
    modal.style.display = 'block';
    document.body.classList.add('modal-open');
}
function closeDeleteTaskModal() {
    var modal = document.getElementById('archiveDeleteTaskModal');
    if (!modal || modal.classList.contains('closing')) return;
    modal.classList.add('closing');
    if (modal._closeTimer) clearTimeout(modal._closeTimer);
    modal._closeTimer = setTimeout(function() {
        modal.classList.remove('show', 'closing');
        modal.style.display = 'none';
        document.body.classList.remove('modal-open');
    }, 250);
}
var deleteTaskOkBtn = document.getElementById('archiveDeleteTaskOk');
if (deleteTaskOkBtn) {
    deleteTaskOkBtn.addEventListener('click', function() {
        if (!deleteTaskId) return;
        var btn = this;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Menghapus...';
        var fd = new FormData();
        fd.append('id', deleteTaskId);
        fetch('<?= BASE_URL ?>/archive/delete-task', { method: 'POST', body: fd })
            .then(function(r) { return r.json(); })
            .then(function(res) {
                closeDeleteTaskModal();
                if (res.success) {
                    removeArchiveTask(deleteTaskId);
                    var card = document.getElementById('archive-task-' + deleteTaskId);
                    if (card) {
                        var group = card.closest ? card.closest('.date-group') : null;
                        card.remove();
                        if (group && group.querySelectorAll('.archive-task-card').length === 0) {
                            group.remove();
                        }
                    }
                    var content = document.getElementById('taskListContent');
                    if (content.querySelectorAll('.archive-task-card').length === 0) {
                        content.innerHTML = '<div class="text-center py-5" style="color:#94a3b8;"><i class="bi bi-inbox fs-1 d-block mb-2"></i><p>Tidak ada task di bulan ini.</p></div>';
                    }
                    var countEl = document.querySelector('.month-card[data-month="' + currentArchiveMonth + '"] .month-count');
                    if (countEl) {
                        var m = ARCHIVE_MONTHS.find(function(x) { return x.key === currentArchiveMonth; });
                        countEl.textContent = (m ? m.total_tasks : 0) + ' Task';
                    }
                    showToast('success', 'Task Terhapus', res.message || 'Task berhasil dihapus dari database');
                } else {
                    showToast('error', 'Gagal', res.message || 'Gagal menghapus task');
                    btn.disabled = false;
                    btn.innerHTML = '<i class="bi bi-trash3 me-1"></i> Hapus';
                }
            })
            .catch(function() {
                closeDeleteTaskModal();
                showToast('error', 'Gagal', 'Terjadi kesalahan saat menghapus task');
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-trash3 me-1"></i> Hapus';
            });
    });
}

document.addEventListener('DOMContentLoaded', function() {
    setTimeout(function() {
        var cards = document.querySelectorAll('.month-card.fade-up');
        cards.forEach(function(card) {
            card.classList.remove('fade-up');
            void card.offsetHeight;
            card.classList.add('fade-up');
        });
    }, 50);
});
</script>
