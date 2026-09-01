<?php
$tasks = $tasks ?? [];
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
