<?php
$tasks = $tasks ?? [];
$platforms = $platforms ?? [];
$search = $search ?? '';
?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/themes/airbnb.css">

<style>

.scheduler-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 16px;
}


.scheduler-date-group {
    margin-bottom: 24px;
}
.scheduler-date-header {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 14px 20px;
    background: var(--surface, #fff);
    border-radius: 14px 14px 0 0;
    border: 1px solid var(--border-light, #e2e8f0);
    border-bottom: none;
}
.scheduler-date-header .date-badge {
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
.scheduler-date-header .day-name {
    font-size: 15px;
    font-weight: 700;
    color: var(--text-primary, #0f172a);
}
.scheduler-date-header .task-count {
    margin-left: auto;
    font-size: 12px;
    color: #64748b;
    font-weight: 600;
}
.scheduler-task-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 16px;
    padding: 20px;
    background: var(--surface, #fff);
    border: 1px solid var(--border-light, #e2e8f0);
    border-radius: 0 0 14px 14px;
}


.scheduler-card {
    background: #ffffff;
    border-radius: 12px;
    border: 1px solid #e2e8f0;
    padding: 16px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.03);
    cursor: pointer;
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}

.scheduler-card:hover {
    border-color: #003399;
    box-shadow: 0 6px 16px rgba(0,51,153,0.08);
    transform: translateY(-2px);
}


.scheduler-card {
    opacity: 0;
}
.scheduler-card.fade-up {
    animation: fadeUp 0.4s ease-out both;
}
@keyframes fadeUp {
    from { opacity: 0; transform: translateY(16px); }
    to { opacity: 1; transform: translateY(0); }
}

.scheduler-card-title {
    font-size: 14px;
    font-weight: 700;
    color: #0f172a;
    line-height: 1.4;
    word-break: break-word;
}


.modal {
    display: none;
    position: fixed;
    top: 0; left: 0; right: 0; bottom: 0;
    background: rgba(15, 23, 42, 0.6);
    backdrop-filter: blur(4px);
    z-index: 1050;
    align-items: center;
    justify-content: center;
    padding: 20px;
}
.modal.show { display: flex; }
.modal-content {
    background: #ffffff;
    border-radius: 16px;
    max-width: 600px;
    width: 100%;
    max-height: 90vh;
    display: flex;
    flex-direction: column;
    box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1), 0 8px 10px -6px rgba(0,0,0,0.1);
    overflow: hidden;
}
.modal-header {
    padding: 16px 20px;
    border-bottom: 1px solid #e2e8f0;
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.modal-body {
    padding: 20px;
    overflow-y: auto;
}
.modal-footer {
    padding: 16px 20px;
    border-top: 1px solid #e2e8f0;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
}
.modal-close {
    background: transparent;
    border: none;
    font-size: 20px;
    color: #64748b;
    cursor: pointer;
    border-radius: 6px;
    width: 32px; height: 32px;
    display: inline-flex; align-items: center; justify-content: center;
}
.modal-close:hover { background: #f1f5f9; color: #0f172a; }


#schedulerModal .modal-content {
    transition: none !important;
}
#schedulerModal.show .modal-content {
    animation: slideUpFade 0.35s cubic-bezier(0.34, 1.56, 0.64, 1) both !important;
}
#schedulerModal.closing .modal-content {
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

.platform-checkbox-group {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin-top: 8px;
}

.platform-chip {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 7px 14px;
    border-radius: 8px;
    border: 1px solid #cbd5e1;
    background: #f8fafc;
    font-size: 12px;
    font-weight: 600;
    color: #334155;
    cursor: pointer;
    user-select: none;
    transition: all 0.2s ease;
}
.platform-chip input[type="checkbox"] { display: none; }
.platform-chip:hover { background: #f1f5f9; }
.platform-chip.checked {
    background: #eff6ff;
    border-color: #3b82f6;
    color: #1d4ed8;
    box-shadow: 0 2px 6px rgba(59,130,246,0.15);
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
    transition: all 0.2s ease;
}
.btn-task-link.material { background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; }
.btn-task-link.edit { background: #f0fdf4; color: #15803d; border: 1px solid #bbf7d0; }
.btn-task-link.file { background: #faf5ff; color: #7e22ce; border: 1px solid #e9d5ff; }

.search-input-wrapper {
    position: relative;
    width: 100%;
}
.search-input-wrapper i {
    position: absolute;
    left: 14px;
    top: 50%;
    transform: translateY(-50%);
    color: #94a3b8;
    font-size: 15px;
}
.search-input-wrapper input {
    padding-left: 40px !important;
    border-radius: 10px;
    height: 42px;
    font-size: 13px;
    border: 1px solid #cbd5e1;
}


.input-group-date {
    display: flex;
    align-items: stretch;
    border-radius: 12px;
    border: 1px solid var(--border, #cbd5e1);
    overflow: hidden;
    background: var(--surface, #ffffff);
    transition: border-color 0.2s ease, box-shadow 0.2s ease;
}
.input-group-date:focus-within {
    border-color: #003399;
    box-shadow: 0 0 0 3px rgba(0,51,153,0.1);
}
.input-group-date input {
    border: none !important;
    outline: none !important;
    box-shadow: none !important;
    border-radius: 0 !important;
    flex: 1;
    min-width: 0;
}
.input-group-date input.form-control:focus {
    box-shadow: none !important;
}
.input-group-date .btn-date-icon {
    border: none;
    background: transparent;
    cursor: pointer;
    padding: 0 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #64748b;
    border-left: 1px solid #e2e8f0;
    transition: background 0.2s ease, color 0.2s ease;
}
.input-group-date .btn-date-icon:hover {
    background: #f1f5f9;
    color: #003399;
}
.input-group-date.calendar-open .btn-date-icon {
    color: #003399;
}


.flatpickr-calendar {
    z-index: 99999 !important;
    border-radius: 16px !important;
    box-shadow: 0 16px 24px -6px rgba(0,0,0,0.18), 0 8px 12px -8px rgba(0,0,0,0.1) !important;
    border: 1px solid #e2e8f0 !important;
    overflow: hidden;
    font-family: inherit !important;
}
.flatpickr-months {
    padding: 8px 8px 4px !important;
}
.flatpickr-current-month .flatpickr-monthDropdown-months {
    font-weight: 700 !important;
    font-size: 15px !important;
    padding: 2px 6px !important;
    border-radius: 6px !important;
}
.flatpickr-current-month .flatpickr-monthDropdown-months:hover {
    background: rgba(0,51,153,0.08);
}
.flatpickr-current-month .numInputWrapper {
    padding: 2px 6px !important;
    border-radius: 6px !important;
}
.flatpickr-current-month .numInputWrapper:hover {
    background: rgba(0,51,153,0.08);
}
.flatpickr-current-month .numInputWrapper input.cur-year {
    font-weight: 700 !important;
    font-size: 15px !important;
}
.flatpickr-day.selected,
.flatpickr-day.startRange,
.flatpickr-day.endRange,
.flatpickr-day.selected.inRange,
.flatpickr-day.startRange.inRange,
.flatpickr-day.endRange.inRange,
.flatpickr-day.week.selected {
    background: #003399 !important;
    border-color: #003399 !important;
    box-shadow: none !important;
}
.flatpickr-day.today {
    border-color: #003399 !important;
}
.flatpickr-day.today:hover,
.flatpickr-day.today:focus {
    border-color: #003399 !important;
    background: rgba(0,51,153,0.1) !important;
}
.flatpickr-day:hover {
    background: #eef2ff !important;
    border-color: #c7d2fe;
}
.flatpickr-day.inRange {
    background: #eef2ff !important;
    border-color: #c7d2fe !important;
    box-shadow: -6px 0 0 #eef2ff, 6px 0 0 #eef2ff !important;
}
.flatpickr-months .flatpickr-prev-month,
.flatpickr-months .flatpickr-next-month {
    padding: 8px !important;
    border-radius: 8px !important;
    transition: background 0.2s ease;
}
.flatpickr-months .flatpickr-prev-month:hover svg,
.flatpickr-months .flatpickr-next-month:hover svg {
    fill: #003399 !important;
}
.flatpickr-months .flatpickr-prev-month:hover,
.flatpickr-months .flatpickr-next-month:hover {
    background: rgba(0,51,153,0.08);
}


[data-theme="dark"] .flatpickr-calendar {
    background: #1e293b !important;
    border-color: #334155 !important;
}
[data-theme="dark"] .flatpickr-months .flatpickr-month {
    color: #e2e8f0 !important;
    fill: #e2e8f0 !important;
}
[data-theme="dark"] .flatpickr-current-month .flatpickr-monthDropdown-months {
    color: #e2e8f0 !important;
}
[data-theme="dark"] .flatpickr-current-month .cur-month {
    color: #e2e8f0 !important;
}
[data-theme="dark"] .flatpickr-current-month .numInputWrapper span.arrowUp,
[data-theme="dark"] .flatpickr-current-month .numInputWrapper span.arrowDown {
    border-bottom-color: #64748b !important;
    border-top-color: #64748b !important;
}
[data-theme="dark"] .flatpickr-current-month .numInputWrapper:hover {
    background: rgba(255,255,255,0.08);
}
[data-theme="dark"] .flatpickr-current-month .numInputWrapper input.cur-year {
    color: #e2e8f0 !important;
}
[data-theme="dark"] .flatpickr-weekdays {
    background: transparent !important;
}
[data-theme="dark"] .flatpickr-weekday {
    color: #94a3b8 !important;
}
[data-theme="dark"] .flatpickr-day {
    color: #cbd5e1 !important;
}
[data-theme="dark"] .flatpickr-day:hover {
    background: #334155 !important;
    border-color: #475569;
}
[data-theme="dark"] .flatpickr-day.flatpickr-disabled,
[data-theme="dark"] .flatpickr-day.flatpickr-disabled:hover {
    color: #475569 !important;
}
[data-theme="dark"] .flatpickr-day.today {
    border-color: #60a5fa !important;
}
[data-theme="dark"] .flatpickr-day.selected,
[data-theme="dark"] .flatpickr-day.startRange,
[data-theme="dark"] .flatpickr-day.endRange {
    background: #1d4ed8 !important;
    border-color: #1d4ed8 !important;
}
[data-theme="dark"] .flatpickr-day.inRange {
    background: #1e3a5f !important;
    border-color: #1e40af !important;
}


.input-group-date input.flatpickr-input,
.input-group-date input.flatpickr-alt-input {
    flex: 1 !important;
    width: auto !important;
    max-width: none !important;
}
.input-group-date input.flatpickr-alt-input.form-control {
    border-radius: 12px 0 0 12px !important;
}
.input-group-date input.flatpickr-input:not([readonly]),
.input-group-date input.flatpickr-alt-input:not([readonly]) {
    background: transparent !important;
    cursor: text;
}
</style>


<div class="d-flex align-items-center justify-content-between gap-3 mb-4 flex-wrap">
    <div>
        <h4 class="fw-bold mb-1 d-flex align-items-center"><i class="bi bi-calendar2-check text-primary fs-4" style="margin-right:10px;"></i><span>Scheduler Postingan</span></h4>
        <p class="text-tertiary mb-0" style="font-size:13px;">Klik card task untuk mengatur jadwal posting dan media sosial tujuan.</p>
    </div>
    <div style="min-width:260px; max-width:340px; width:100%;">
        <div class="search-input-wrapper">
            <i class="bi bi-search"></i>
            <input type="text" class="form-control" placeholder="Pencarian task" value="<?= htmlspecialchars($search) ?>" onkeyup="filterScheduler(this.value)" oninput="filterScheduler(this.value)">
        </div>
    </div>
</div>


<?php
$dayNamesIndo = ['Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'];
$groupedTasks = [];
foreach ($tasks as $item) {
    $taskDate = $item['task_date'] ?? date('Y-m-d');
    if (!isset($groupedTasks[$taskDate])) {
        $groupedTasks[$taskDate] = ['date' => $taskDate, 'tasks' => []];
    }
    $groupedTasks[$taskDate]['tasks'][] = $item;
}
ksort($groupedTasks);
?>

<?php if (!empty($groupedTasks)): ?>
    <?php foreach ($groupedTasks as $taskDate => $group):
        $dayNum = date('d', strtotime($taskDate));
        $dayName = $dayNamesIndo[date('l', strtotime($taskDate))] ?? date('l', strtotime($taskDate));
        $taskCount = count($group['tasks']);
    ?>
    <div class="scheduler-date-group">
        <div class="scheduler-date-header">
            <span class="date-badge"><?= $dayNum ?></span>
            <span class="day-name"><?= htmlspecialchars($dayName) ?> (<?= date('d/m/Y', strtotime($taskDate)) ?>)</span>
            <span class="task-count"><?= $taskCount ?> task</span>
        </div>
        <div class="scheduler-task-grid">
            <?php foreach ($group['tasks'] as $item): ?>
                <div class="scheduler-card" id="task-card-<?= $item['id'] ?>" onclick="openSchedulerModal(<?= (int)$item['id'] ?>)">

                    <div class="scheduler-card-title mb-3">
                        <?= htmlspecialchars($item['title'] ?: '(Belum ada judul)') ?>
                    </div>


                    <?php
                    $cardTypes = array_values(array_filter(array_map('trim', explode(',', $item['content_type'] ?? ''))));
                    ?>
                    <div class="d-flex align-items-center justify-content-between gap-2 pt-2 border-top" style="font-size:12px; color:#64748b;">
                        <div>
                            <i class="bi bi-person me-1 text-primary"></i> <?= htmlspecialchars($item['assignee_name'] ?: 'User Magang') ?>
                        </div>
                        <?php if ($cardTypes): ?>
                            <div style="display:flex; flex-wrap:wrap; justify-content:flex-end; gap:4px;">
                                <?php foreach ($cardTypes as $ct): ?>
                                    <span class="badge" style="background:linear-gradient(135deg, #1a237e, #283593); color:#fff; font-weight:600; font-size:11px; padding:4px 10px; border-radius:6px; display:inline-flex; align-items:center; gap:4px; box-shadow:0 2px 6px rgba(26,35,126,0.15);"><i class="bi bi-tag-fill"></i><?= htmlspecialchars($ct) ?></span>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <span class="badge" style="background:#64748b; color:#fff; font-size:11px; padding:4px 10px; border-radius:6px;">Task</span>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endforeach; ?>

    <div id="schedulerSearchEmpty" class="card" style="display:none;">
        <div class="card-body text-center py-5">
            <i class="bi bi-search fs-1 text-tertiary d-block mb-2"></i>
            <h5 class="fw-bold">Hasil pencarian tidak ditemukan</h5>
            <p class="text-tertiary mb-0" style="font-size:13px;">Tidak ada task yang cocok dengan kata kunci pencarian Anda.</p>
        </div>
    </div>

<?php else: ?>
    <div class="card">
        <div class="card-body text-center py-5">
            <i class="bi bi-calendar-check fs-1 text-tertiary d-block mb-2"></i>
            <h5 class="fw-bold">Belum Ada Task di Scheduler</h5>
            <p class="text-tertiary mb-0" style="font-size:13px;">Task yang telah disetujui di halaman Approval akan muncul di sini untuk dijadwalkan dan diupload.</p>
        </div>
    </div>
<?php endif; ?>


<div class="modal" id="schedulerModal" onclick="if (event.target === this) closeSchedulerModal();">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="fw-bold mb-0" style="font-size:16px;"><i class="bi bi-clock-history text-primary me-2"></i> Pengaturan Scheduler & Upload</h5>
            <button type="button" class="modal-close" onclick="closeSchedulerModal()">&times;</button>
        </div>
        <div class="modal-body" id="schedulerModalBody">

        </div>
        <div class="modal-footer" id="schedulerModalFooter">

        </div>
    </div>
</div>

<script>

const schedulerTasks = <?= json_encode($tasks ?? [], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
const availablePlatforms = <?= json_encode($platforms ?? [], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
const availableHashtags = <?= json_encode($hashtags ?? [], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
const availableTags = <?= json_encode($allTags ?? [], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
let activeTaskId = null;

function esc(s) {
    if (!s) return '';
    return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
}

function toggleHashtagToCaption(tagStr) {
    const captionEl = document.getElementById('modal-caption');
    if (!captionEl) return;
    let val = captionEl.value;
    if (!tagStr.startsWith('#')) {
        tagStr = '#' + tagStr;
    }
    if (val.includes(tagStr)) {
        val = val.replace(new RegExp('\\s*' + tagStr.replace(/[-\/\\^$*+?.()|[\]{}]/g, '\\$&'), 'g'), '').trim();
    } else {
        val = val ? (val + ' ' + tagStr) : tagStr;
    }
    captionEl.value = val;
}

function filterScheduler(q) {
    const term = q.toLowerCase().trim();
    const cards = document.querySelectorAll('.scheduler-card');
    let visibleCount = 0;
    cards.forEach(card => {
        const txt = card.textContent.toLowerCase();
        const show = (term === '' || txt.indexOf(term) !== -1);
        card.style.display = show ? 'flex' : 'none';
        if (show) visibleCount++;
    });

    document.querySelectorAll('.scheduler-date-group').forEach(group => {
        const groupCards = group.querySelectorAll('.scheduler-card');
        const visible = Array.prototype.some.call(groupCards, c => c.style.display !== 'none');
        group.style.display = visible ? '' : 'none';
    });

    const emptyEl = document.getElementById('schedulerSearchEmpty');
    if (emptyEl) {
        emptyEl.style.display = (visibleCount === 0 && cards.length > 0) ? 'block' : 'none';
    }
}

function openSchedulerModal(taskId) {
    const item = schedulerTasks.find(t => t.id == taskId);
    if (!item) return;

    activeTaskId = taskId;
    const modal = document.getElementById('schedulerModal');
    const body = document.getElementById('schedulerModalBody');
    const footer = document.getElementById('schedulerModalFooter');

    const savedPlatforms = item.target_platforms ? item.target_platforms.split(',') : [];

    let html = '';

    var renderTypeBadges = function(typesStr) {
        if (!typesStr) return '<span class="badge" style="background:#64748b; color:#fff; font-size:11px; padding:4px 10px; border-radius:6px;">Task</span>';
        var arr = typesStr.split(',').map(function(s){ return s.trim(); }).filter(Boolean);
        return arr.map(function(t) {
            return '<span class="badge" style="background:linear-gradient(135deg, #1a237e, #283593); color:#fff; font-weight:600; font-size:11px; padding:4px 10px; border-radius:6px; display:inline-flex; align-items:center; gap:4px; margin-right:4px; margin-bottom:4px; box-shadow:0 2px 6px rgba(26,35,126,0.15);"><i class="bi bi-tag-fill"></i>' + esc(t) + '</span>';
        }).join('');
    };

    html += '<div class="mb-4 p-3" style="background:#f8fafc; border-radius:10px; border:1px solid #e2e8f0;">';
    html += '  <div class="mb-2">';
    html += '    <label class="form-label mb-1 text-tertiary fw-semibold" style="font-size:11px;">JUDUL POSTINGAN (DAPAT DIEDIT)</label>';
    html += '    <textarea class="form-control fw-bold" id="modal-title" rows="1" style="border-radius:10px; font-size:14px; border:1px solid #cbd5e1; resize:vertical; min-height:38px;">' + esc(item.title) + '</textarea>';
    html += '  </div>';
    html += '  <div class="d-flex align-items-center gap-3 text-tertiary flex-wrap" style="font-size:12px;">';
    html += '    <span><i class="bi bi-person text-primary me-1"></i> ' + esc(item.assignee_name || 'User Magang') + '</span>';
    html += '  </div>';
    html += '  <div class="mt-2">';
    html += '    <div class="text-tertiary fw-semibold" style="font-size:10px; text-transform:uppercase; letter-spacing:0.5px; margin-bottom:4px;">Jenis</div>';
    html += '    <div>' + renderTypeBadges(item.content_type) + '</div>';
    html += '  </div>';

    const hasMedia = item.result_file_attachment;
    if (hasMedia) {
        html += '  <div class="mt-3 pt-2 border-top">';
        html += '    <div class="fw-bold text-xs text-tertiary mb-2"><i class="bi bi-folder-check me-1"></i> HASIL PEKERJAAN:</div>';
        html += '    <div class="d-flex align-items-center gap-2 flex-wrap">';
        const fUrl = item.result_file_attachment.startsWith('http') ? item.result_file_attachment : ('<?= BASE_URL ?>/uploads/' + item.result_file_attachment.replace(/^\//,''));
        html += '      <a href="' + esc(fUrl) + '" target="_blank" class="btn-task-link file"><i class="bi bi-file-earmark-arrow-down me-1"></i> File Hasil Pekerjaan</a>';
        html += '    </div>';
        html += '  </div>';
    }
    html += '</div>';

    html += '<div class="mb-2">';
    html += '  <label class="form-label mb-1 text-tertiary fw-semibold" style="font-size:11px;">CENTANG MEDIA SOSIAL TUJUAN</label>';
    html += '  <div class="platform-checkbox-group">';

    availablePlatforms.forEach(p => {
        const isChecked = savedPlatforms.includes(String(p.id)) || savedPlatforms.includes(p.slug) || (savedPlatforms.length === 0 && p.id == item.platform_id);
        html += '    <label class="platform-chip ' + (isChecked ? 'checked' : '') + '" onclick="toggleModalChip(this)">';
        html += '      <input type="checkbox" name="modal_platforms[]" value="' + p.id + '" ' + (isChecked ? 'checked' : '') + '>';
        html += '      <i class="' + esc(p.icon || 'bi-globe') + '"></i>';
        html += '      <span>' + esc(p.name) + '</span>';
        html += '    </label>';
    });

    html += '  </div>';
    html += '</div>';

    html += '<div class="mb-3">';
    html += '  <label class="form-label mb-1 text-tertiary fw-semibold" style="font-size:11px;">CAPTION</label>';
    html += '  <textarea class="form-control" id="modal-caption" rows="4" placeholder="Tulis caption untuk postingan..." style="border-radius:10px; font-size:13px; resize:vertical;">' + esc(item.caption || '') + '</textarea>';
    html += '</div>';

    if (availableHashtags.length > 0 || availableTags.length > 0) {
        html += '<div class="mb-3">';
        html += '  <label class="form-label mb-1 text-tertiary fw-semibold" style="font-size:11px;"><i class="bi bi-hash text-primary me-1"></i> SISIPKAN HASHTAG / TAGS (KLIK UNTUK OTOMATIS MASUK KE CAPTION)</label>';
        html += '  <div class="d-flex flex-wrap gap-1 p-2" style="background:#f8fafc; border-radius:10px; border:1px solid #e2e8f0; max-height:120px; overflow-y:auto;">';
        availableHashtags.forEach(h => {
            const hName = h.name.startsWith('#') ? h.name : ('#' + h.name);
            html += '    <button type="button" class="btn btn-sm btn-outline-primary py-1 px-2 text-xs rounded-pill" onclick="toggleHashtagToCaption(\'' + esc(hName) + '\')">';
            html += '      ' + esc(hName);
            html += '    </button>';
        });
        availableTags.forEach(t => {
            const tName = '#' + t.name.replace(/\s+/g, '');
            html += '    <button type="button" class="btn btn-sm btn-outline-secondary py-1 px-2 text-xs rounded-pill" onclick="toggleHashtagToCaption(\'' + esc(tName) + '\')">';
            html += '      ' + esc(tName);
            html += '    </button>';
        });
        html += '  </div>';
        html += '</div>';
    }

    var schedulerActive = item.scheduler_active == 1;
    html += '<div class="d-flex align-items-center justify-content-between p-3" style="background:#f8fafc; border-radius:10px; border:1px solid #e2e8f0;">';
    html += '  <div>';
    html += '    <div class="fw-semibold text-dark" style="font-size:13px;">Upload Scheduler</div>';
    html += '    <div class="text-tertiary" style="font-size:11px;">Aktifkan untuk mengupload postingan secara terjadwal</div>';
    html += '  </div>';
    html += '  <input type="hidden" id="modal-scheduler-active" value="' + (schedulerActive ? '1' : '0') + '">';
    html += '  <button type="button" class="btn ' + (schedulerActive ? 'btn-primary' : 'btn-outline-secondary') + ' d-flex align-items-center gap-2 px-3 py-2" id="modal-scheduler-toggle" style="border-radius:10px; font-size:13px; font-weight:600;" onclick="toggleSchedulerActive()">';
    html += '    <i class="bi ' + (schedulerActive ? 'bi-toggle-on' : 'bi-toggle-off') + ' fs-5"></i>';
    html += '    <span>' + (schedulerActive ? 'Aktif' : 'Nonaktif') + '</span>';
    html += '  </button>';
    html += '</div>';

    const today = new Date().toISOString().split('T')[0];
    const taskDate = item.task_date || today;
    const taskTime = item.scheduled_time || '09:00';

    html += '<div class="row g-3 mb-2 mt-1" id="modal-datetime-wrapper" style="' + (schedulerActive ? '' : 'display:none;') + '">';
    html += '  <div class="col-6">';
    html += '    <label class="form-label mb-1 text-tertiary fw-semibold" style="font-size:11px;">TANGGAL UPLOAD</label>';
    html += '    <div class="input-group-date">';
    html += '      <input type="text" class="form-control" id="modal-date" placeholder="dd/mm/yyyy" autocomplete="off">';
    html += '      <button type="button" class="btn-date-icon" id="btnModalDateIcon" tabindex="-1" title="Buka Kalender">';
    html += '        <i class="bi bi-calendar3"></i>';
    html += '      </button>';
    html += '    </div>';
    html += '  </div>';
    html += '  <div class="col-6">';
    html += '    <label class="form-label mb-1 text-tertiary fw-semibold" style="font-size:11px;">JAM UPLOAD</label>';
    html += '    <div class="input-group-date">';
    html += '      <input type="text" class="form-control" id="modal-time" placeholder="HH:MM" autocomplete="off">';
    html += '      <button type="button" class="btn-date-icon" id="btnModalTimeIcon" tabindex="-1" title="Pilih Waktu">';
    html += '        <i class="bi bi-clock"></i>';
    html += '      </button>';
    html += '    </div>';
    html += '  </div>';
    html += '</div>';

    body.innerHTML = html;

    if (typeof flatpickr !== 'undefined') {
        var modalDateInput = document.getElementById('modal-date');
        if (modalDateInput && !modalDateInput._flatpickr) {
            var fpModalDate = flatpickr(modalDateInput, {
                locale: 'id',
                dateFormat: 'Y-m-d',
                altFormat: 'd/m/Y',
                altInput: true,
                altInputClass: 'form-control',
                allowInput: true,
                disableMobile: true,
                clickOpens: false,
                animate: true,
                monthSelectorType: 'static',
                static: false,
                position: 'auto',
                defaultDate: taskDate,
                onOpen: function(selectedDates, dateStr, instance) {
                    var container = instance.element.closest('.input-group-date');
                    if (container) container.classList.add('calendar-open');
                    var modalBody = instance.element.closest('.modal-body');
                    if (modalBody) modalBody.style.overflowY = 'visible';
                },
                onClose: function(selectedDates, dateStr, instance) {
                    var container = instance.element.closest('.input-group-date');
                    if (container) container.classList.remove('calendar-open');
                    var modalBody = instance.element.closest('.modal-body');
                    if (modalBody) modalBody.style.overflowY = '';
                }
            });
            if (fpModalDate.altInput) {
                fpModalDate.altInput.style.width = '';
                fpModalDate.altInput.style.flex = '1';
                fpModalDate.altInput.style.minWidth = '0';
            }
            var btnModalDateIcon = document.getElementById('btnModalDateIcon');
            if (btnModalDateIcon) {
                btnModalDateIcon.addEventListener('click', function(e) {
                    e.stopPropagation();
                    e.preventDefault();
                    fpModalDate.open();
                });
            }
        }

        var modalTimeInput = document.getElementById('modal-time');
        if (modalTimeInput && !modalTimeInput._flatpickr) {
            var fpModalTime = flatpickr(modalTimeInput, {
                enableTime: true,
                noCalendar: true,
                dateFormat: 'H:i',
                altFormat: 'H:i',
                altInput: true,
                altInputClass: 'form-control',
                allowInput: true,
                disableMobile: true,
                clickOpens: false,
                animate: true,
                static: false,
                position: 'auto',
                time_24hr: true,
                defaultDate: taskTime,
                onOpen: function(selectedDates, dateStr, instance) {
                    var container = instance.element.closest('.input-group-date');
                    if (container) container.classList.add('calendar-open');
                    var modalBody = instance.element.closest('.modal-body');
                    if (modalBody) modalBody.style.overflowY = 'visible';
                },
                onClose: function(selectedDates, dateStr, instance) {
                    var container = instance.element.closest('.input-group-date');
                    if (container) container.classList.remove('calendar-open');
                    var modalBody = instance.element.closest('.modal-body');
                    if (modalBody) modalBody.style.overflowY = '';
                }
            });
            if (fpModalTime.altInput) {
                fpModalTime.altInput.style.width = '';
                fpModalTime.altInput.style.flex = '1';
                fpModalTime.altInput.style.minWidth = '0';
            }
            var btnModalTimeIcon = document.getElementById('btnModalTimeIcon');
            if (btnModalTimeIcon) {
                btnModalTimeIcon.addEventListener('click', function(e) {
                    e.stopPropagation();
                    e.preventDefault();
                    fpModalTime.open();
                });
            }
        }
    }

    let footerHtml = '';
    footerHtml += '<button type="button" class="btn btn-primary btn-sm px-4 fw-bold" id="modal-upload-btn" style="margin-left:auto;" onclick="uploadFromModal(' + taskId + ')"><i class="bi bi-cloud-upload-fill me-1"></i> ' + (schedulerActive ? 'Upload Sesuai Scheduler' : 'Upload Sekarang') + '</button>';

    footer.innerHTML = footerHtml;

    if (modal._closeTimer) { clearTimeout(modal._closeTimer); modal._closeTimer = null; }
    modal.classList.remove('closing');
    modal.classList.add('show');
    document.body.style.overflow = 'hidden';
}

function closeSchedulerModal() {
    const modal = document.getElementById('schedulerModal');
    if (!modal || modal.classList.contains('closing')) return;
    modal.classList.add('closing');
    if (modal._closeTimer) clearTimeout(modal._closeTimer);
    modal._closeTimer = setTimeout(function() {
        modal.classList.remove('show');
        modal.classList.remove('closing');
        modal._closeTimer = null;
    }, 250);
    document.body.style.overflow = '';
}

function toggleSchedulerActive() {
    const btn = document.getElementById('modal-scheduler-toggle');
    const input = document.getElementById('modal-scheduler-active');
    const wrapper = document.getElementById('modal-datetime-wrapper');
    const uploadBtn = document.getElementById('modal-upload-btn');
    if (!btn || !input) return;
    const isActive = input.value === '1';
    if (isActive) {
        input.value = '0';
        btn.className = 'btn btn-outline-secondary d-flex align-items-center gap-2 px-3 py-2';
        btn.innerHTML = '<i class="bi bi-toggle-off fs-5"></i><span>Nonaktif</span>';
        if (wrapper) wrapper.style.display = 'none';
        if (uploadBtn) {
            const icon = uploadBtn.querySelector('i');
            uploadBtn.innerHTML = (icon ? icon.outerHTML : '<i class="bi bi-cloud-upload-fill me-1"></i>') + ' Upload Sekarang';
        }
    } else {
        input.value = '1';
        btn.className = 'btn btn-primary d-flex align-items-center gap-2 px-3 py-2';
        btn.innerHTML = '<i class="bi bi-toggle-on fs-5"></i><span>Aktif</span>';
        if (wrapper) wrapper.style.display = '';
        if (uploadBtn) {
            const icon = uploadBtn.querySelector('i');
            uploadBtn.innerHTML = (icon ? icon.outerHTML : '<i class="bi bi-cloud-upload-fill me-1"></i>') + ' Upload Sesuai Scheduler';
        }
    }
}

function toggleModalChip(el) {
    const cb = el.querySelector('input[type="checkbox"]');
    setTimeout(() => {
        if (cb.checked) {
            el.classList.add('checked');
        } else {
            el.classList.remove('checked');
        }
    }, 50);
}

function getModalPlatforms() {
    const checkboxes = document.querySelectorAll('input[name="modal_platforms[]"]:checked');
    const values = [];
    checkboxes.forEach(cb => values.push(cb.value));
    return values;
}

function saveScheduleFromModal(taskId) {
    const titleVal = document.getElementById('modal-title') ? document.getElementById('modal-title').value : '';
    const dateVal = document.getElementById('modal-date').value;
    const timeVal = document.getElementById('modal-time').value;
    const platforms = getModalPlatforms();
    const caption = document.getElementById('modal-caption').value;
    const schedulerActive = document.getElementById('modal-scheduler-active') ? document.getElementById('modal-scheduler-active').value : '1';

    const formData = new FormData();
    formData.append('task_id', taskId);
    formData.append('title', titleVal);
    formData.append('task_date', dateVal);
    formData.append('scheduled_time', timeVal);
    formData.append('caption', caption);
    formData.append('scheduler_active', schedulerActive);
    platforms.forEach(p => formData.append('platforms[]', p));

    fetch(BASE_URL + '/scheduler/update-schedule', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                showToast('success', 'Tersimpan', res.message);
                closeSchedulerModal();
                setTimeout(() => { location.reload(); }, 600);
            } else {
                showToast('error', 'Gagal', res.message);
            }
        })
        .catch(() => showToast('error', 'Error', 'Terjadi kesalahan server'));
}

function uploadFromModal(taskId) {
    const titleVal = document.getElementById('modal-title') ? document.getElementById('modal-title').value : '';
    const dateVal = document.getElementById('modal-date').value;
    const timeVal = document.getElementById('modal-time').value;
    const platforms = getModalPlatforms();
    const caption = document.getElementById('modal-caption').value;
    const schedulerActive = document.getElementById('modal-scheduler-active') ? document.getElementById('modal-scheduler-active').value : '1';

    if (platforms.length === 0) {
        showToast('warning', 'Pilih Media', 'Mohon centang minimal satu media sosial untuk diupload!');
        return;
    }

    const overlay = document.getElementById('pageLoadingOverlay');
    if (overlay) {
        const overlayText = overlay.querySelector('.page-loading-text');
        if (overlayText) overlayText.textContent = schedulerActive === '1' ? 'Upload Sesuai Scheduler...' : 'Upload Sekarang...';
        overlay.classList.add('show');
    }

    const formData = new FormData();
    formData.append('task_id', taskId);
    formData.append('title', titleVal);
    formData.append('task_date', dateVal);
    formData.append('scheduled_time', timeVal);
    formData.append('caption', caption);
    formData.append('scheduler_active', schedulerActive);
    platforms.forEach(p => formData.append('platforms[]', p));

    fetch(BASE_URL + '/scheduler/upload-task', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                showToast('success', 'Berhasil Upload!', res.message);
                closeSchedulerModal();
                if (overlay) overlay.classList.remove('show');
                const card = document.getElementById('task-card-' + taskId);
                if (card) {
                    card.style.transition = 'all 0.4s ease';
                    card.style.opacity = '0';
                    card.style.transform = 'scale(0.9)';
                    setTimeout(() => { card.remove(); updateSchedulerGroupCounts(); }, 400);
                }
            } else {
                showToast('error', 'Gagal Upload', res.message);
                if (overlay) overlay.classList.remove('show');
            }
        })
        .catch(() => {
            showToast('error', 'Error', 'Terjadi kesalahan server');
            if (overlay) overlay.classList.remove('show');
        });
}

function updateSchedulerGroupCounts() {
    document.querySelectorAll('.scheduler-date-group').forEach(group => {
        const cards = group.querySelectorAll('.scheduler-card');
        const countEl = group.querySelector('.task-count');
        if (cards.length === 0) {
            group.remove();
            return;
        }
        if (countEl) countEl.textContent = cards.length + ' task';
    });
}

document.addEventListener('DOMContentLoaded', function() {
    setTimeout(function() {
        var cards = document.querySelectorAll('.scheduler-card');
        cards.forEach(function(card, i) {
            card.classList.add('fade-up');
            card.style.animationDelay = (i * 0.05) + 's';
        });
    }, 50);
});
</script>

<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/id.js"></script>


<div id="pageLoadingOverlay" class="page-loading-overlay" aria-hidden="true">
    <div class="page-loading-box">
        <div class="page-loading-spinner"></div>
        <div class="page-loading-text">Memproses Upload...</div>
    </div>
</div>
