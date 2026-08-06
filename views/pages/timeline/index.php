<?php





$monthNames = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
$statusColors = ['Selesai' => 'success', 'Proses' => 'warning', 'Belum' => 'danger', 'Publish' => 'info', 'Belum Selesai' => 'danger'];
$statusIcons = ['Selesai' => 'bi-check-circle-fill', 'Proses' => 'bi-arrow-repeat', 'Belum' => 'bi-hourglass-split', 'Publish' => 'bi-send-fill', 'Belum Selesai' => 'bi-hourglass-split'];
$contentTypes = $contentTypes ?? [];
$picList = $picList ?? [];
$stats = $stats ?? ['total' => 0, 'selesai' => 0, 'publish' => 0, 'proses' => 0, 'belum' => 0];
?>

<style>
.timeline-header {
    background: linear-gradient(135deg, #002677 0%, #003399 50%, #0055cc 100%);
    border-radius: 16px;
    padding: 24px;
    margin-bottom: 24px;
    color: white;
    box-shadow: 0 10px 30px -5px rgba(0, 51, 153, 0.3);
    position: relative;
    overflow: hidden;
}
.timeline-header::after {
    content: '';
    position: absolute;
    top: -50%;
    right: -10%;
    width: 300px;
    height: 300px;
    background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0) 70%);
    pointer-events: none;
}
.timeline-header .stats {
    display: flex;
    gap: 16px;
    flex-wrap: wrap;
}
.timeline-header .stat-item {
    background: rgba(255,255,255,0.12);
    border: 1px solid rgba(255,255,255,0.2);
    border-radius: 14px;
    padding: 16px 20px;
    text-align: center;
    min-width: 110px;
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    flex: 1;
    transition: transform 0.2s ease, background 0.2s ease;
}
.timeline-header .stat-item:hover {
    transform: translateY(-2px);
    background: rgba(255,255,255,0.18);
}
.timeline-header .stat-item .number {
    font-size: 28px;
    font-weight: 800;
    line-height: 1.2;
    letter-spacing: -0.5px;
}
.timeline-header .stat-item .label {
    font-size: 11px;
    opacity: 0.9;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.8px;
    margin-top: 4px;
}

.timeline-filters {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
    margin-bottom: 24px;
    align-items: center;
    background: var(--surface);
    padding: 16px 20px;
    border-radius: 14px;
    border: 1px solid var(--border-light);
    box-shadow: var(--shadow-xs);
}
.timeline-filters select, .timeline-filters input {
    padding: 9px 14px;
    border: 1px solid var(--border);
    border-radius: 10px;
    font-size: 13px;
    background: var(--surface);
    color: var(--text-primary);
    transition: all 0.2s ease;
}
.timeline-filters select:focus, .timeline-filters input:focus {
    border-color: var(--tvri-blue);
    box-shadow: 0 0 0 3px rgba(0,51,153,0.1);
    outline: none;
}
.timeline-filters .btn-filter {
    padding: 9px 20px;
    border-radius: 10px;
    border: none;
    background: var(--tvri-blue-gradient);
    color: white;
    cursor: pointer;
    font-size: 13px;
    font-weight: 600;
    transition: all 0.2s ease;
    box-shadow: 0 2px 6px rgba(0,51,153,0.2);
}
.timeline-filters .btn-filter:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0,51,153,0.3);
}


.date-group {
    margin-bottom: 24px;
}
.date-header {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 14px 20px;
    background: var(--surface);
    border-radius: 14px 14px 0 0;
    border: 1px solid var(--border-light);
    border-bottom: none;
    cursor: pointer;
    user-select: none;
    transition: background 0.2s;
}
.date-header:hover {
    background: var(--surface-hover);
}
.date-header .date-badge {
    background: var(--tvri-blue-gradient);
    color: white;
    border-radius: 10px;
    padding: 5px 16px;
    font-size: 14px;
    font-weight: 700;
    min-width: 46px;
    text-align: center;
    box-shadow: 0 2px 8px rgba(0,51,153,0.25);
}
.date-header .date-info {
    flex: 1;
}
.date-header .date-info .day-name {
    font-weight: 700;
    font-size: 15px;
    color: var(--text-primary);
}
.date-header .date-info .day-date {
    font-size: 12px;
    color: var(--text-tertiary);
}
.date-header .date-status {
    display: flex;
    gap: 6px;
    align-items: center;
}
.date-header .date-status .badge-cal {
    font-size: 11px;
    padding: 3px 10px;
    border-radius: 12px;
    background: var(--surface-tertiary);
    color: var(--text-secondary);
    font-weight: 600;
}
.date-header .collapse-icon {
    color: var(--text-tertiary);
    font-size: 14px;
    transition: transform 0.2s;
}
.date-header.collapsed .collapse-icon {
    transform: rotate(-90deg);
}


.task-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(290px, 1fr));
    gap: 16px;
    padding: 20px;
    border: 1px solid var(--border-light);
    border-top: none;
    border-radius: 0 0 14px 14px;
    background: var(--surface);
}
.task-card {
    background: var(--surface);
    border: 1px solid var(--border-light);
    border-radius: 14px;
    padding: 18px;
    position: relative;
    cursor: pointer;
    transition: all 0.22s cubic-bezier(0.4, 0, 0.2, 1);
}
.task-card:hover {
    box-shadow: 0 12px 28px -6px rgba(0,51,153,0.12);
    border-color: var(--tvri-blue-200);
    transform: translateY(-4px);
}
.task-card .task-type {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 8px;
    font-size: 11px;
    font-weight: 700;
    background: var(--tvri-blue-50);
    color: var(--tvri-blue);
    margin-bottom: 10px;
}
.task-card .task-title {
    font-size: 14px;
    font-weight: 700;
    color: var(--text-primary);
    margin-bottom: 8px;
    line-height: 1.45;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
.task-card .task-title.empty {
    color: var(--text-tertiary);
    font-style: italic;
    font-weight: 400;
}
.task-card .task-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    margin-bottom: 8px;
}
.task-card .task-meta .meta-item {
    font-size: 11px;
    color: #777;
    display: flex;
    align-items: center;
    gap: 3px;
}
.task-card .task-meta .meta-item i {
    font-size: 12px;
}
.task-card .status-badge {
    position: absolute;
    top: 10px;
    right: 10px;
    font-size: 10px;
    padding: 3px 10px;
    border-radius: 12px;
    font-weight: 600;
}
.task-card .task-links {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
    margin-top: 6px;
}
.task-card .task-links a {
    font-size: 11px;
    color: #3f51b5;
    text-decoration: none;
    display: flex;
    align-items: center;
    gap: 3px;
    padding: 2px 8px;
    background: #e8eaf6;
    border-radius: 4px;
    transition: background 0.2s;
}
.task-card .task-links a:hover {
    background: #c5cae9;
}
.task-card .task-catatan {
    font-size: 11px;
    color: #444;
    margin-top: 8px;
    padding: 8px 10px;
    background: #f0f4f9;
    border-left: 3px solid #1a237e;
    border-radius: 6px;
    line-height: 1.5;
    word-break: break-word;
    white-space: pre-line;
}


.status-select {
    font-size: 11px;
    padding: 3px 8px;
    border: 1px solid #ddd;
    border-radius: 6px;
    background: white;
    cursor: pointer;
}


.empty-state {
    text-align: center;
    padding: 60px 20px;
    color: #999;
}
.empty-state i {
    font-size: 48px;
    margin-bottom: 16px;
    color: #ddd;
}
.empty-state h4 {
    color: #666;
    margin-bottom: 8px;
}


.modal-backdrop {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    width: 100vw;
    height: 100vh;
    background: rgba(15, 23, 42, 0.6);
    z-index: 10000;
    justify-content: center;
    align-items: center;
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
    opacity: 0;
    transition: opacity 0.25s cubic-bezier(0.4, 0, 0.2, 1);
}
.modal-backdrop.active {
    display: flex !important;
    opacity: 1;
}
.modal-content {
    background: #ffffff;
    border-radius: 20px;
    width: 92%;
    max-width: 640px;
    max-height: 88vh;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.35);
    transform: scale(0.88) translateY(15px);
    opacity: 0;
    transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1), opacity 0.25s ease-out;
    margin-top: 30px;
    position: relative;
    border: 1px solid rgba(255, 255, 255, 0.2);
}
.modal-backdrop.active .modal-content {
    transform: scale(1) translateY(0);
    opacity: 1;
}
.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px 24px 0;
    flex-shrink: 0;
    border-top-left-radius: 20px;
    border-top-right-radius: 20px;
}
.modal-header h3 {
    font-size: 18px;
    font-weight: 600;
    color: #1a237e;
    margin: 0;
}
.modal-close {
    width: 32px;
    height: 32px;
    border: none;
    background: #f5f5f5;
    border-radius: 8px;
    cursor: pointer;
    font-size: 18px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #666;
    transition: background 0.2s;
}
.modal-close:hover {
    background: #e0e0e0;
}
.modal-body {
    padding: 20px 24px;
    flex: 1;
    overflow-y: auto;
    min-height: 0;
}
.modal-footer {
    padding: 0 24px 20px;
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    flex-shrink: 0;
    border-bottom-left-radius: 20px;
    border-bottom-right-radius: 20px;
}
.form-group {
    margin-bottom: 16px;
}
.form-group label {
    display: block;
    font-size: 12px;
    font-weight: 600;
    color: #444;
    margin-bottom: 4px;
}
.form-group input, .form-group select, .form-group textarea {
    width: 100%;
    padding: 9px 12px;
    border: 1px solid #cbd5e1;
    border-radius: 12px;
    font-size: 13px;
    font-family: inherit;
    transition: all 0.2s ease;
    box-sizing: border-box;
    background: #ffffff;
    color: #0f172a;
}
.form-group select {
    appearance: none;
    -webkit-appearance: none;
    -moz-appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%23003399' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 12px center;
    background-size: 14px 14px;
    padding-right: 36px;
    cursor: pointer;
}
.form-group input:focus, .form-group select:focus, .form-group textarea:focus {
    border-color: #003399;
    outline: none;
    box-shadow: 0 0 0 3px rgba(0,51,153,0.12);
}
.form-group textarea {
    min-height: 60px;
    resize: vertical;
}
.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
}
.btn-primary {
    background: #1a237e;
    color: white;
    border: none;
    padding: 10px 24px;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.2s;
}
.btn-primary:hover {
    background: #283593;
}
.btn-outline {
    background: transparent;
    color: #555;
    border: 1px solid #ddd;
    padding: 10px 24px;
    border-radius: 8px;
    font-size: 14px;
    cursor: pointer;
    transition: all 0.2s;
}
.btn-outline:hover {
    background: #f5f5f5;
    border-color: #ccc;
}


.month-nav {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 16px;
}
.month-nav h2 {
    font-size: 20px;
    font-weight: 700;
    color: #1a237e;
    margin: 0;
}
.month-nav .nav-buttons {
    display: flex;
    gap: 8px;
}
.month-nav .nav-btn {
    width: 36px;
    height: 36px;
    border: 1px solid #ddd;
    border-radius: 8px;
    background: white;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    color: #555;
    transition: all 0.2s;
}
.month-nav .nav-btn:hover {
    background: #f5f5f5;
    border-color: #bbb;
}

.file-info {
    font-size: 11px;
    color: #666;
    margin-top: 4px;
    display: flex;
    align-items: center;
    gap: 6px;
}
.file-info a {
    color: #3f51b5;
    text-decoration: none;
}


.detail-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
}
.detail-field {
    display: flex;
    flex-direction: column;
    gap: 4px;
}
.detail-label {
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: #888;
}
.detail-value {
    font-size: 14px;
    color: #1a1a2e;
    word-break: break-word;
}
.detail-value a {
    color: #3f51b5;
    text-decoration: none;
}
.detail-value a:hover {
    text-decoration: underline;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}
.multi-format-container {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    padding: 10px;
    background: #ffffff;
    border: 1px solid #cbd5e1;
    border-radius: 12px;
    max-height: 160px;
    overflow-y: auto;
}
.format-pill-btn {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 5px 12px;
    font-size: 12px;
    font-weight: 600;
    border-radius: 20px;
    border: 1px solid #cbd5e1;
    background: #f8fafc;
    color: #334155;
    cursor: pointer;
    transition: all 0.2s ease;
    user-select: none;
}
.format-pill-btn:hover {
    background: #e2e8f0;
    border-color: #94a3b8;
}
.format-pill-btn * {
    pointer-events: none;
}
.format-pill-btn.selected {
    background: linear-gradient(135deg, #002677 0%, #003399 100%);
    color: #ffffff;
    border-color: #002677;
    box-shadow: 0 2px 6px rgba(0, 51, 153, 0.25);
}
.format-pill-btn.selected .icon-add { display: none !important; }
.format-pill-btn.selected .icon-check { display: inline-block !important; }
</style>


<div class="month-nav">
    <h2><i class="bi bi-calendar-week" style="margin-right: 8px;"></i> <?= $userRole === 'magang' ? 'Tugas Saya' : 'Timeline' ?> <?= $monthNames[(int)$month] ?> <?= $year ?></h2>
    <div class="nav-buttons">
        <a href="?bulan=<?= max(1, (int)$month - 1 < 1 ? 12 : (int)$month - 1) ?>&tahun=<?= (int)$month - 1 < 1 ? $year - 1 : $year ?>" class="nav-btn" title="Bulan Sebelumnya"><i class="bi bi-chevron-left"></i></a>
        <a href="?bulan=<?= date('m') ?>&tahun=<?= date('Y') ?>" class="nav-btn" title="Bulan Ini"><i class="bi bi-dot"></i></a>
        <a href="?bulan=<?= min(12, (int)$month + 1 > 12 ? 1 : (int)$month + 1) ?>&tahun=<?= (int)$month + 1 > 12 ? $year + 1 : $year ?>" class="nav-btn" title="Bulan Berikutnya"><i class="bi bi-chevron-right"></i></a>
    </div>
</div>


<div class="timeline-header">
    <div class="stats">
        <div class="stat-item">
            <div class="number" style="color: #fff;"><?= number_format($stats['total'] ?? 0) ?></div>
            <div class="label">Total Task</div>
        </div>
        <div class="stat-item">
            <div class="number" style="color: #81c784;"><?= number_format(($stats['selesai'] ?? 0) + ($stats['publish'] ?? 0)) ?></div>
            <div class="label">Selesai</div>
        </div>
        <div class="stat-item">
            <div class="number" style="color: #ffb74d;"><?= number_format($stats['proses'] ?? 0) ?></div>
            <div class="label">Proses</div>
        </div>
        <div class="stat-item">
            <div class="number" style="color: #e57373;"><?= number_format($stats['belum'] ?? 0) ?></div>
            <div class="label">Belum</div>
        </div>
    </div>
</div>


<form method="GET" class="timeline-filters">
    <input type="hidden" name="bulan" value="<?= $month ?>">
    <input type="hidden" name="tahun" value="<?= $year ?>">

    <select name="jenis">
        <option value="">Semua Jenis</option>
        <?php foreach ($contentTypes as $ct): ?>
            <option value="<?= htmlspecialchars($ct['content_type']) ?>" <?= $filterType === $ct['content_type'] ? 'selected' : '' ?>><?= htmlspecialchars($ct['content_type']) ?></option>
        <?php endforeach; ?>
    </select>

    <select name="status">
        <option value="">Semua Status</option>
        <option value="Belum" <?= $filterStatus === 'Belum' ? 'selected' : '' ?>>Belum</option>
        <option value="Proses" <?= $filterStatus === 'Proses' ? 'selected' : '' ?>>Proses</option>
        <option value="Selesai" <?= $filterStatus === 'Selesai' ? 'selected' : '' ?>>Selesai</option>
    </select>

    <select name="pic">
        <option value="">Semua PIC</option>
        <?php foreach ($picList as $p): ?>
            <option value="<?= htmlspecialchars($p['pic_name']) ?>" <?= $filterPic === $p['pic_name'] ? 'selected' : '' ?>><?= htmlspecialchars($p['pic_name']) ?></option>
        <?php endforeach; ?>
    </select>

    <button type="submit" class="btn-filter"><i class="bi bi-funnel"></i> Filter</button>
    <a href="?bulan=<?= $month ?>&tahun=<?= $year ?>" class="btn-filter" style="background: #757575;">Reset</a>

    <div style="margin-left: auto; display: flex; gap: 8px;">
        <?php if ($userRole !== 'magang'): ?>
        <button type="button" class="btn-filter" style="background: #4caf50;" onclick="openModal('modalCalendar', null, 'generate')">
            <i class="bi bi-magic"></i> Generate Kalender
        </button>
        <?php endif; ?>
        <button type="button" class="btn-filter" style="background: #1a237e;" onclick="openModal('modalTask', null, 'create')">
            <i class="bi bi-plus-lg"></i> Tambah Task
        </button>
    </div>
</form>


<?php if (empty($grouped)): ?>
    <div class="empty-state">
        <i class="bi bi-clipboard-data"></i>
        <h4>Belum ada task untuk bulan ini</h4>
        <?php if ($userRole !== 'magang'): ?>
        <p style="color: #aaa;">Klik "Tambah Task" untuk mulai mengisi timeline</p>
        <?php endif; ?>
    </div>
<?php else: ?>
    <?php foreach ($grouped as $date => $group):
        $cal = $calendarByDate[$date] ?? null;
        $allDone = true;
        foreach ($group['tasks'] as $t) { if ($t['status'] !== 'Selesai' && $t['status'] !== 'Publish') { $allDone = false; break; } }
    ?>
    <div class="date-group">
        <div class="date-header <?= $allDone ? '' : '' ?>" onclick="toggleGroup(this)">
            <div class="date-badge"><?= date('d', strtotime($date)) ?></div>
            <div class="date-info">
                <div class="day-name"><?= htmlspecialchars($group['day_name']) ?></div>
                <div class="day-date"><?= date('d F Y', strtotime($date)) ?> · <?= count($group['tasks']) ?> task</div>
            </div>
            <div class="date-status">
                <?php if ($cal): ?>
                    <?php if ($cal['feed_count'] > 0): ?><span class="badge-cal"><i class="bi bi-file-image"></i> <?= $cal['feed_count'] ?> Feed</span><?php endif; ?>
                    <?php if ($cal['reels_count'] > 0): ?><span class="badge-cal"><i class="bi bi-camera-reels"></i> <?= $cal['reels_count'] ?> Reels</span><?php endif; ?>
                    <?php if ($cal['story_count'] > 0): ?><span class="badge-cal"><i class="bi bi-stars"></i> <?= $cal['story_count'] ?> Story</span><?php endif; ?>
                    <?php if ($cal['status']): ?><span class="badge-cal" style="background: <?= $cal['status'] === 'Selesai' ? '#c8e6c9' : '#fff9c4' ?>; color: <?= $cal['status'] === 'Selesai' ? '#2e7d32' : '#f57f17' ?>;"><?= $cal['status'] ?></span><?php endif; ?>
                <?php endif; ?>
                <?php if ($allDone): ?><span class="badge-cal" style="background: #c8e6c9; color: #2e7d32;"><i class="bi bi-check-lg"></i> All Done</span><?php endif; ?>
            </div>
            <i class="bi bi-chevron-down collapse-icon"></i>
        </div>
        <div class="task-grid">
            <?php foreach ($group['tasks'] as $task):
                $status = $task['status'];
                $color = $statusColors[$status] ?? 'secondary';
                $icon = $statusIcons[$status] ?? 'bi-circle';
            ?>
            <div class="task-card" data-id="<?= $task['id'] ?>" onclick="viewTask(event, <?= $task['id'] ?>)">
                <div style="flex: 1; display: flex; flex-direction: column;">
                    <?php if (!empty($task['content_type'])): ?>
                        <div class="task-format-badges mb-2" style="display: flex; flex-wrap: wrap; gap: 4px;">
                            <?php foreach (array_filter(array_map('trim', explode(',', $task['content_type']))) as $fmt): ?>
                                <span class="badge-format-tag" style="background: linear-gradient(135deg, #1a237e, #283593); color: #ffffff; font-weight: 600; font-size: 10px; padding: 3px 8px; border-radius: 6px; display: inline-flex; align-items: center; gap: 3px; box-shadow: 0 1px 3px rgba(26,35,126,0.15);">
                                    <i class="bi bi-tag-fill" style="font-size: 9px;"></i> <?= htmlspecialchars($fmt) ?>
                                </span>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <div class="task-title <?= empty($task['title']) ? 'empty' : '' ?>" style="font-size: 15px; font-weight: 700; color: var(--text-primary); margin-bottom: 8px; line-height: 1.45;">
                        <?= !empty($task['title']) ? htmlspecialchars($task['title']) : '(belum ada judul)' ?>
                    </div>
                </div>

                <div class="task-actions" style="margin-top: auto; padding-top: 10px; border-top: 1px solid var(--border-light); display: flex; justify-content: space-between; align-items: center; gap: 6px; flex-wrap: nowrap; min-height: 42px;">
                    <div class="task-meta-left" style="display: flex; align-items: center; gap: 5px; min-width: 0; flex: 1; overflow: hidden;">
                        <?php if ($task['pic_name']): ?>
                            <span class="meta-item" title="PIC: <?= htmlspecialchars($task['pic_name']) ?>" style="font-size: 11px; font-weight: 600; color: var(--text-secondary); display: inline-flex; align-items: center; gap: 4px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; flex-shrink: 1;">
                                <i class="bi bi-person-fill" style="color: var(--tvri-blue); flex-shrink: 0;"></i> <?= htmlspecialchars($task['pic_name']) ?>
                            </span>
                        <?php endif; ?>
                    </div>

                    <div class="task-buttons-right" style="display: flex; gap: 4px; align-items: center; flex-shrink: 0; margin-left: auto;">
                        <?php if ($userRole !== 'magang'): ?>
                        <button type="button" class="btn-card-action edit" onclick="event.stopPropagation(); openModal('modalTask', <?= $task['id'] ?>, 'edit')" title="Edit Task">
                            <i class="bi bi-pencil-square"></i> Edit
                        </button>
                        <button type="button" class="btn-card-action delete" onclick="event.stopPropagation(); deleteTask(<?= $task['id'] ?>)" title="Hapus Task">
                            <i class="bi bi-trash"></i> Hapus
                        </button>
                        <?php else: ?>
                        <button type="button" class="btn-card-action edit" style="background:#e0f2fe; color:#0369a1; border-color:#bae6fd;" onclick="event.stopPropagation(); document.getElementById('fileUpload_<?= $task['id'] ?>').click()" title="Upload File Hasil">
                            <i class="bi bi-upload"></i> Upload
                            <form id="uploadForm_<?= $task['id'] ?>" method="post" enctype="multipart/form-data" action="<?= BASE_URL ?>/timeline/upload-file" style="display:none;">
                                <input type="hidden" name="task_id" value="<?= $task['id'] ?>">
                                <input type="file" name="file_attachment" id="fileUpload_<?= $task['id'] ?>" onchange="document.getElementById('uploadForm_<?= $task['id'] ?>').submit()" style="display:none;">
                            </form>
                        </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>


            <div class="task-card" style="border: 2px dashed #c5cae9; background: #f8f9fc; display: flex; align-items: center; justify-content: center; cursor: pointer; min-height: 120px; transition: all 0.2s;" onclick="openModal('modalTask', null, 'create', '<?= $date ?>')">
                <div style="text-align: center; color: #3f51b5;">
                    <i class="bi bi-plus-circle" style="font-size: 24px; display: block; margin-bottom: 6px;"></i>
                    <span style="font-size: 13px; font-weight: 600;">Tambah Task</span>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
<?php endif; ?>



<div class="modal-backdrop" id="modalTask">
    <div class="modal-content">
        <form method="POST" action="" id="formTask" enctype="multipart/form-data" style="margin:0;">
            <div class="modal-header">
                <h3 id="modalTaskTitle">Tambah Task Baru</h3>
                <button type="button" class="modal-close" onclick="closeModal('modalTask')">&times;</button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="action" id="taskAction" value="store">
                <input type="hidden" name="task_id" id="taskId" value="">
                <input type="hidden" name="existing_file" id="existingFile" value="">

                <input type="hidden" name="task_date" id="task_date" value="<?= date('Y-m-d') ?>">

                <div class="form-group">
                    <label>Deadline</label>
                    <input type="date" name="deadline" id="deadline">
                </div>

                <div class="form-group mb-3">
                    <label style="font-size: 12px; font-weight: 600; color: #334155; display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                        <span>Jenis Format <span class="text-danger">*</span> <small class="text-tertiary fw-normal">(Bisa pilih lebih dari 1)</small></span>
                        <span id="timelineSelectedFormatCount" style="font-size: 11px; font-weight: 700; color: #003399;">0 Dipilih</span>
                    </label>

                    <input type="hidden" name="content_type" id="content_type" value="" required>

                    <div class="multi-format-container" id="timelineMultiFormatContainer">
                        <?php
                        $formatOptions = ['Feed', 'Reels', 'Reels Berita', 'Story', 'Thumbnail', 'Flyer/Poster', 'Take Video', 'Skrip / Materi', 'Teresterial', 'Carousel', 'Single Post', 'Image'];
                        foreach ($formatOptions as $fmt):
                        ?>
                            <button type="button" class="format-pill-btn" data-value="<?= htmlspecialchars($fmt) ?>" onclick="toggleTimelineFormatPill(this, event)">
                                <i class="bi bi-plus-lg icon-add"></i>
                                <i class="bi bi-check-lg icon-check" style="display:none;"></i>
                                <span><?= htmlspecialchars($fmt) ?></span>
                            </button>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="form-group">
                    <label>Judul / Nama Konten *</label>
                    <input type="text" name="title" id="title" class="form-control" placeholder="Contoh: Berita JHI..." autocomplete="off">
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>PIC (Ditugaskan ke User Magang) *</label>
                        <select name="assigned_to" id="assigned_to" required onchange="var opt=this.options[this.selectedIndex]; document.getElementById('pic_name').value = opt.getAttribute('data-name') || '';">
                            <option value="">- Pilih User Magang / PIC -</option>
                            <?php foreach ($usersList ?? [] as $u): ?>
                                <option value="<?= $u['id'] ?>" data-name="<?= htmlspecialchars($u['name']) ?>"><?= htmlspecialchars($u['name'] . ' (' . $u['email'] . ')') ?></option>
                            <?php endforeach; ?>
                        </select>
                        <input type="hidden" name="pic_name" id="pic_name" value="">
                    </div>
                    <div class="form-group">
                        <label>Sort Order</label>
                        <input type="number" name="sort_order" id="sort_order" value="0" min="0">
                    </div>
                </div>

                <div class="form-group">
                    <label>Link Materi</label>
                    <input type="url" name="material_link" id="material_link" placeholder="https://...">
                </div>

                <div class="form-group">
                    <label>Link Edit</label>
                    <input type="url" name="edit_link" id="edit_link" placeholder="https://...">
                </div>

                <div class="form-group">
                    <label>Catatan / Deskripsi</label>
                    <textarea name="catatan" id="catatan" rows="3" placeholder="Instruksi detail / catatan..."></textarea>
                </div>

                <div class="form-group">
                    <label>Referensi</label>
                    <textarea name="referensi" id="referensi" rows="2" placeholder="Link referensi..."></textarea>
                </div>

                <div class="form-group">
                    <label>Upload File</label>
                    <input type="file" name="file_attachment" id="file_attachment" accept=".jpg,.jpeg,.png,.gif,.pdf,.doc,.docx,.xlsx,.pptx,.zip">
                    <div class="file-info" id="fileInfo" style="display:none;">
                        <i class="bi bi-paperclip"></i> File terupload: <a href="#" id="fileLink" target="_blank"></a>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-outline" onclick="closeModal('modalTask')">Batal</button>
                <button type="submit" class="btn-primary" id="btnSubmitTask">Simpan</button>
            </div>
        </form>
    </div>
</div>



<div class="modal-backdrop" id="modalCalendar">
    <div class="modal-content" style="max-width: 500px;">
        <div class="modal-header">
            <h3>Atur Kalender Harian</h3>
            <button type="button" class="modal-close" onclick="closeModal('modalCalendar')">&times;</button>
        </div>
        <div class="modal-body">
            <form method="POST" action="<?= BASE_URL ?>/timeline/save-calendar" id="formCalendar">
                <div class="form-group">
                    <label>Tanggal *</label>
                    <input type="date" name="calendar_date" id="cal_date" required>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Jumlah Feed</label>
                        <input type="number" name="feed_count" id="cal_feed" value="1" min="0">
                    </div>
                    <div class="form-group">
                        <label>Jumlah Reels</label>
                        <input type="number" name="reels_count" id="cal_reels" value="1" min="0">
                    </div>
                    <div class="form-group">
                        <label>Jumlah Story</label>
                        <input type="number" name="story_count" id="cal_story" value="1" min="0">
                    </div>
                </div>
                <div class="form-group">
                    <label>Nama Konten (pisahkan dengan koma)</label>
                    <textarea name="content_names" id="cal_content" rows="2" placeholder="Feed Live Score, Reels Berita, Flyer Nobar..."></textarea>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Status</label>
                        <select name="status">
                            <option value="">-</option>
                            <option value="Selesai">Selesai</option>
                            <option value="Belum">Belum</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Jam Posting</label>
                        <input type="text" name="post_time" id="cal_time" value="08.00 - 18.00">
                    </div>
                </div>
                <div style="margin-top: 16px;">
                    <button type="submit" class="btn-primary" style="width:100%;">Simpan Kalender</button>
                </div>
            </form>

            <hr style="margin: 20px 0; border: none; border-top: 1px solid #eee;">

            <form method="POST" action="<?= BASE_URL ?>/timeline/generate-calendar">
                <p style="font-size: 13px; color: #666; margin-bottom: 12px;">
                    <i class="bi bi-magic"></i> Generate otomatis semua tanggal di bulan ini yang belum ada entry kalender.
                </p>
                <input type="hidden" name="bulan" value="<?= $month ?>">
                <input type="hidden" name="tahun" value="<?= $year ?>">
                <button type="submit" class="btn-outline" style="width:100%;">Generate Kalender Bulan <?= $monthNames[(int)$month] ?> <?= $year ?></button>
            </form>
        </div>
    </div>
</div>



<div class="modal-backdrop" id="modalDetail">
    <div class="modal-content" style="max-width: 600px;">
        <div class="modal-header">
            <h3 id="detailModalTitle"><i class="bi bi-clipboard-check" style="margin-right: 12px;"></i>Detail Task</h3>
            <button type="button" class="modal-close" onclick="closeModal('modalDetail')">&times;</button>
        </div>
        <div class="modal-body" id="detailModalBody">

            <div style="text-align:center;padding:40px 0;color:#999;">
                <div class="spinner" style="width:32px;height:32px;border:3px solid #e0e0e0;border-top-color:#1a237e;border-radius:50%;animation:spin 0.8s linear infinite;margin:0 auto 12px;"></div>
                Memuat detail...
            </div>
        </div>
        <div class="modal-footer" id="detailModalFooter">
            <button type="button" class="btn-outline" onclick="closeModal('modalDetail')">Tutup</button>
        </div>
    </div>
</div>

<script>


window.toggleTimelineFormatPill = function(btn, event) {
    if (event) {
        if (typeof event.preventDefault === 'function') event.preventDefault();
        if (typeof event.stopPropagation === 'function') event.stopPropagation();
    }
    btn.classList.toggle('selected');
    window.updateTimelineContentTypeInputValue();
};

window.updateTimelineContentTypeInputValue = function() {
    var selected = [];
    document.querySelectorAll('#timelineMultiFormatContainer .format-pill-btn.selected').forEach(function(btn) {
        selected.push(btn.getAttribute('data-value'));
    });
    var input = document.getElementById('content_type');
    if (input) {
        input.value = selected.join(', ');
        if (selected.length === 0) {
            input.setCustomValidity('Pilih minimal 1 Jenis Format');
        } else {
            input.setCustomValidity('');
        }
    }
    var countEl = document.getElementById('timelineSelectedFormatCount');
    if (countEl) {
        countEl.textContent = selected.length + ' Dipilih';
    }
};

window.setTimelineTaskFormatTypes = function(typesString) {
    var types = typesString ? typesString.split(',').map(function(s) { return s.trim(); }) : [];
    document.querySelectorAll('#timelineMultiFormatContainer .format-pill-btn').forEach(function(btn) {
        var val = btn.getAttribute('data-value');
        if (types.indexOf(val) !== -1) {
            btn.classList.add('selected');
        } else {
            btn.classList.remove('selected');
        }
    });
    window.updateTimelineContentTypeInputValue();
};

document.addEventListener('DOMContentLoaded', function() {




    window.openModal = function(id, taskId, mode, date) {
        var modal = document.getElementById(id);
        if (!modal) return;

        if (id === 'modalTask') {
            var form = document.getElementById('formTask');
            form.action = '<?= BASE_URL ?>/timeline/store';

            if (mode === 'edit' && taskId) {
                document.getElementById('modalTaskTitle').textContent = 'Edit Task';
                document.getElementById('taskAction').value = 'update';
                document.getElementById('btnSubmitTask').textContent = 'Update';
                form.action = '<?= BASE_URL ?>/timeline/update/' + taskId;

                fetch('<?= BASE_URL ?>/timeline/get/' + taskId)
                    .then(function(r) { return r.json(); })
                    .then(function(res) {
                        if (res.success) {
                            var d = res.data;
                            document.getElementById('task_date').value = d.task_date || '';
                            document.getElementById('deadline').value = d.deadline || '';
                            setTimelineTaskFormatTypes(d.content_type || '');
                            var statusSelect = document.querySelector('#formTask [name="status"]');
                            if (statusSelect) statusSelect.value = d.status || 'Belum';
                            document.getElementById('title').value = d.title || '';
                            document.getElementById('pic_name').value = d.pic_name || '';
                            document.getElementById('assigned_to').value = d.assigned_to || '';
                            document.getElementById('sort_order').value = d.sort_order || 0;
                            document.getElementById('material_link').value = d.material_link || '';
                            document.getElementById('edit_link').value = d.edit_link || '';
                            document.getElementById('catatan').value = d.catatan || '';
                            document.getElementById('referensi').value = d.referensi || '';
                            document.getElementById('taskId').value = d.id || '';
                            document.getElementById('existingFile').value = d.file_attachment || '';

                            if (d.file_attachment) {
                                var fi = document.getElementById('fileInfo');
                                fi.style.display = 'flex';
                                document.getElementById('fileLink').href = d.file_attachment;
                                document.getElementById('fileLink').textContent = window.fileDisplayName(d.file_attachment);
                            } else {
                                fi.style.display = 'none';
                            }
                        }
                    })
                    .catch(function() {});
            } else {
                document.getElementById('modalTaskTitle').textContent = 'Tambah Task Baru';
                document.getElementById('taskAction').value = 'store';
                document.getElementById('btnSubmitTask').textContent = 'Simpan';
                form.action = '<?= BASE_URL ?>/timeline/store';
                setTimelineTaskFormatTypes('');

                form.reset();
                document.getElementById('taskId').value = '';
                document.getElementById('existingFile').value = '';
                document.getElementById('fileInfo').style.display = 'none';

                if (date) {
                    document.getElementById('task_date').value = date;
                } else {
                    document.getElementById('task_date').value = new Date().toISOString().split('T')[0];
                }
            }
        }

        if (id === 'modalCalendar') {
            if (!document.getElementById('cal_date').value) {
                document.getElementById('cal_date').value = new Date().toISOString().split('T')[0];
            }
        }

        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
    };

    window.closeModal = function(id) {
        var el = document.getElementById(id);
        if (el) {
            el.classList.remove('active');
            el.classList.remove('show');
        }
        document.body.style.overflow = '';
        document.body.style.pointerEvents = '';
        document.body.style.userSelect = '';
    };

    window.viewTask = function(event, id) {
        if (event) {
            event.preventDefault();
            event.stopPropagation();
        }

        var modal = document.getElementById('modalDetail');
        var body = document.getElementById('detailModalBody');
        var footer = document.getElementById('detailModalFooter');

        body.innerHTML = '<div style="text-align:center;padding:40px 0;color:#999;">' +
            '<div class="spinner" style="width:32px;height:32px;border:3px solid #e0e0e0;border-top-color:#1a237e;border-radius:50%;animation:spin 0.8s linear infinite;margin:0 auto 12px;"></div>' +
            'Memuat detail...</div>';
        footer.innerHTML = '<button type="button" class="btn-outline" onclick="closeModal(\'modalDetail\')">Tutup</button>';

        modal.classList.add('active');
        document.body.style.overflow = 'hidden';

        fetch('<?= BASE_URL ?>/timeline/get/' + id)
            .then(function(r) { return r.json(); })
            .then(function(res) {
                if (!res.success) {
                    body.innerHTML = '<div style="text-align:center;padding:40px 0;color:#e53935;">' +
                        '<i class="bi bi-exclamation-circle" style="font-size:32px;display:block;margin-bottom:12px;"></i>' +
                        'Gagal memuat detail task</div>';
                    return;
                }
                var d = res.data;

                var html = '<div class="detail-grid">';
                html += '<div class="detail-field"><span class="detail-label">Jenis Konten</span><span class="detail-value">' + escHtml(d.content_type || '-') + '</span></div>';
                html += '<div class="detail-field" style="grid-column: 1 / -1;"><span class="detail-label">Judul / Nama Konten</span><span class="detail-value" style="font-size:15px;font-weight:600;">' + escHtml(d.title || '(belum ada judul)') + '</span></div>';
                html += '<div class="detail-field"><span class="detail-label">PIC</span><span class="detail-value"><i class="bi bi-person"></i> ' + escHtml(d.pic_name || '-') + '</span></div>';
                if (d.deadline) {
                    html += '<div class="detail-field"><span class="detail-label">Deadline</span><span class="detail-value"><i class="bi bi-clock"></i> ' + d.deadline + '</span></div>';
                }
                if (d.material_link) {
                    html += '<div class="detail-field"><span class="detail-label">Link Materi</span><span class="detail-value"><a href="' + escHtml(d.material_link) + '" target="_blank" onclick="event.stopPropagation()"><i class="bi bi-folder2-open"></i> ' + escHtml(d.material_link) + '</a></span></div>';
                }
                if (d.edit_link) {
                    html += '<div class="detail-field"><span class="detail-label">Link Edit</span><span class="detail-value"><a href="' + escHtml(d.edit_link) + '" target="_blank" onclick="event.stopPropagation()"><i class="bi bi-pencil-square"></i> ' + escHtml(d.edit_link) + '</a></span></div>';
                }
                if (d.file_attachment) {
                    html += '<div class="detail-field"><span class="detail-label">File Upload</span><span class="detail-value"><a href="' + escHtml(d.file_attachment) + '" target="_blank" onclick="event.stopPropagation()"><i class="bi bi-paperclip"></i> ' + escHtml(window.fileDisplayName(d.file_attachment)) + '</a></span></div>';
                }
                if (d.catatan) {
                    html += '<div class="detail-field" style="grid-column: 1 / -1;"><span class="detail-label">Catatan / Deskripsi</span><span class="detail-value" style="white-space:pre-wrap;">' + escHtml(d.catatan) + '</span></div>';
                }
                if (d.referensi) {
                    html += '<div class="detail-field" style="grid-column: 1 / -1;"><span class="detail-label">Referensi</span><span class="detail-value" style="white-space:pre-wrap;">' + escHtml(d.referensi) + '</span></div>';
                }
                html += '</div>';

                body.innerHTML = html;

                var userRole = '<?= $userRole ?>';

                if (userRole === 'magang') {
                    footer.innerHTML = '' +
                        '<button type="button" class="btn-outline" onclick="closeModal(\'modalDetail\')">Tutup</button>' +
                        '<button type="button" class="btn-primary" style="background:#3f51b5;" onclick="closeModal(\'modalDetail\'); openModal(\'modalTask\', ' + d.id + ', \'edit\')">' +
                            '<i class="bi bi-pencil"></i> Edit' +
                        '</button>' +
                        '<button type="button" class="btn-primary" style="background:#1565c0;" onclick="document.getElementById(\'detailUpload_\' + ' + d.id + ').click()">' +
                            '<i class="bi bi-upload"></i> Upload File' +
                        '</button>' +
                        '<form method="post" enctype="multipart/form-data" action="<?= BASE_URL ?>/timeline/upload-file" style="display:none;">' +
                            '<input type="hidden" name="task_id" value="' + d.id + '">' +
                            '<input type="file" name="file_attachment" id="detailUpload_' + d.id + '" onchange="this.form.submit()" style="display:none;">' +
                        '</form>';
                } else {
                    footer.innerHTML = '' +
                        '<button type="button" class="btn-outline" onclick="closeModal(\'modalDetail\')">Tutup</button>' +
                        '<button type="button" class="btn-primary" style="background:#3f51b5;" onclick="closeModal(\'modalDetail\'); openModal(\'modalTask\', ' + d.id + ', \'edit\')">' +
                            '<i class="bi bi-pencil"></i> Edit' +
                        '</button>' +
                        '<button type="button" class="btn-primary" style="background:#e53935;" onclick="if(confirm(\'Hapus task ini?\')){closeModal(\'modalDetail\');deleteTask(' + d.id + ')}">' +
                            '<i class="bi bi-trash3"></i> Hapus' +
                        '</button>';
                }
            })
            .catch(function(e) {
                body.innerHTML = '<div style="text-align:center;padding:40px 0;color:#e53935;">' +
                    '<i class="bi bi-exclamation-circle" style="font-size:32px;display:block;margin-bottom:12px;"></i>' +
                    'Gagal memuat detail task.<br><small style="color:#999;">Periksa koneksi internet Anda.</small></div>';
                footer.innerHTML = '<button type="button" class="btn-outline" onclick="closeModal(\'modalDetail\')">Tutup</button>';
            });
    };

    window.escHtml = function(str) {
        if (!str) return '';
        return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    };

    window.fileDisplayName = function(url) {
        if (!url) return '';
        if (url.indexOf('drive.google.com') !== -1) return 'Lihat di Google Drive';
        return String(url).split('/').pop();
    };

    window.getStatusColor = function(status) {
        var map = {'Selesai':'#4caf50','Proses':'#ff9800','Belum':'#f44336','Publish':'#2196f3','Belum Selesai':'#f44336'};
        return map[status] || '#999';
    };

    document.querySelectorAll('.modal-backdrop').forEach(function(el) {
        el.addEventListener('click', function(e) {
            if (e.target === this) closeModal(this.id);
        });
    });

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            document.querySelectorAll('.modal-backdrop.active').forEach(function(el) {
                closeModal(el.id);
            });
        }
    });

    window.toggleGroup = function(el) {
        var grid = el.nextElementSibling;
        if (grid) {
            grid.style.display = grid.style.display === 'none' ? 'grid' : 'none';
            el.classList.toggle('collapsed');
        }
    };

    window.updateStatus = function(id, status) {
        var formData = new FormData();
        formData.append('id', id);
        formData.append('status', status);

        fetch('<?= BASE_URL ?>/timeline/update-status', {
            method: 'POST',
            body: formData
        })
        .then(function(r) { return r.json(); })
        .then(function(res) {
            if (res.success) {
                var card = document.querySelector('.task-card[data-id="' + id + '"]');
                if (card) {
                    var badge = card.querySelector('.status-badge');
                    if (badge) {
                        var colors = {'Selesai': '#4caf50', 'Proses': '#ff9800', 'Belum': '#f44336', 'Publish': '#2196f3', 'Belum Selesai': '#f44336'};
                        var icons = {'Selesai': 'bi-check-circle-fill', 'Proses': 'bi-arrow-repeat', 'Belum': 'bi-hourglass-split', 'Publish': 'bi-send-fill', 'Belum Selesai': 'bi-hourglass-split'};
                        badge.style.background = colors[status] || '#999';
                        badge.innerHTML = '<i class="' + (icons[status] || 'bi-circle') + '" style="font-size:9px;"></i> ' + status;
                    }
                }
            }
        })
        .catch(function() {});
    };

    window.deleteTask = function(id) {
        if (!confirm('Hapus task ini?')) return;

        fetch('<?= BASE_URL ?>/timeline/delete/' + id, {
            method: 'POST',
            body: new URLSearchParams({_method: 'DELETE'})
        })
        .then(function() {
            window.location.reload();
        })
        .catch(function() {
            window.location.reload();
        });
    };

    var calDate = document.getElementById('cal_date');
    if (calDate) {
        calDate.addEventListener('change', function() {
            var date = this.value;
            if (!date) return;

            fetch('<?= BASE_URL ?>/timeline/get-calendar?date=' + date)
                .then(function(r) { return r.json(); })
                .then(function(res) {
                    if (res.success && res.data) {
                        var d = res.data;
                        document.getElementById('cal_feed').value = d.feed_count || 0;
                        document.getElementById('cal_reels').value = d.reels_count || 0;
                        document.getElementById('cal_story').value = d.story_count || 0;
                        document.getElementById('cal_content').value = d.content_names || '';
                        document.querySelector('#formCalendar [name="status"]').value = d.status || '';
                        document.getElementById('cal_time').value = d.post_time || '08.00 - 18.00';
                    }
                })
                .catch(function() {});
        });
    }

});
</script>
