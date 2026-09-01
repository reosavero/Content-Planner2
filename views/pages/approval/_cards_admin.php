<?php
$data = $data ?? [];
$dayNamesIndo = ['Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'];
$groupedTasks = [];
foreach ($data as $item) {
    $taskDate = $item['task_date'] ?? date('Y-m-d');
    if (!isset($groupedTasks[$taskDate])) {
        $groupedTasks[$taskDate] = ['date' => $taskDate, 'tasks' => []];
    }
    $groupedTasks[$taskDate]['tasks'][] = $item;
}
ksort($groupedTasks);
foreach ($groupedTasks as $taskDate => &$group) {
    usort($group['tasks'], function($a, $b) {
        $aPending = in_array($a['status'] ?? '', ['Pending Approval', 'Need Revision']) ? 0 : 1;
        $bPending = in_array($b['status'] ?? '', ['Pending Approval', 'Need Revision']) ? 0 : 1;
        if ($aPending !== $bPending) return $aPending <=> $bPending;
        return strcmp($b['submitted_at'] ?? '', $a['submitted_at'] ?? '');
    });
}
unset($group);
?>

<?php if (!empty($groupedTasks)): ?>
    <?php foreach ($groupedTasks as $taskDate => $group):
        $dayNum = date('d', strtotime($taskDate));
        $dayName = $dayNamesIndo[date('l', strtotime($taskDate))] ?? date('l', strtotime($taskDate));
        $taskCount = count($group['tasks']);
    ?>
    <div class="approval-date-group">
        <div class="approval-date-header">
            <span class="date-badge"><?= $dayNum ?></span>
            <span class="day-name"><?= htmlspecialchars($dayName) ?> (<?= date('d/m/Y', strtotime($taskDate)) ?>)</span>
            <span class="task-count"><?= $taskCount ?> task</span>
        </div>
        <div class="approval-task-grid">
            <?php foreach ($group['tasks'] as $item):
                $isPending = in_array($item['status'] ?? '', ['Pending Approval', 'Need Revision']);
                $isApproved = in_array($item['status'] ?? '', ['Approved', 'Selesai', 'Publish', 'Schedule']);
                $cardClass = '';
                if ($isApproved) {
                    $cardClass = 'is-approved';
                } elseif (($item['status'] ?? '') === 'Pending Approval') {
                    $cardClass = 'is-pending';
                } elseif (($item['status'] ?? '') === 'Need Revision') {
                    $cardClass = 'is-revision';
                } elseif (in_array($item['status'] ?? '', ['Belum', 'Assigned', 'Proses', 'Belum Selesai', 'In Progress', ''])) {
                    $cardClass = 'is-belum';
                }
                if (($item['status'] ?? '') === 'Need Revision') {
                    $badgeClass = 'revision'; $badgeIcon = 'bi-arrow-counterclockwise'; $badgeLabel = 'Perlu Revisi';
                } elseif ($isApproved) {
                    $badgeClass = 'approved'; $badgeIcon = 'bi-check-circle-fill'; $badgeLabel = $item['status'];
                } else {
                    $badgeClass = 'pending'; $badgeIcon = 'bi-clock-history'; $badgeLabel = 'Menunggu Approval';
                }
                $submittedDisplay = '';
                if (!empty($item['submitted_at'])) {
                    $submittedDisplay = date('d/m/Y H:i', strtotime($item['submitted_at']));
                } elseif (!empty($item['created_at'])) {
                    $submittedDisplay = date('d/m/Y H:i', strtotime($item['created_at']));
                }
            ?>
                <div class="approval-task-card <?= $cardClass ?>" onclick="openReviewModal(<?= (int)$item['id'] ?>)">
                    <div>
                        <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                            <div class="approval-card-title" style="margin-bottom:0; flex:1;">
                                <?= htmlspecialchars($item['title'] ?: '(Belum ada judul)') ?>
                            </div>
                            <span class="approval-badge-status <?= $badgeClass ?>" style="font-size:10px; padding:3px 8px; flex-shrink:0;">
                                <i class="bi <?= $badgeIcon ?>"></i>
                                <?= $badgeLabel ?>
                            </span>
                        </div>
                    </div>

                    <div class="approval-card-bottom">
                        <span class="text-tertiary"><i class="bi bi-person me-1 text-primary"></i> <?= htmlspecialchars($item['assignee_name'] ?: 'User Magang') ?></span>
                        <?php if ($submittedDisplay !== ''): ?>
                            <span><i class="bi bi-clock me-1"></i> Dikirim: <?= $submittedDisplay ?></span>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endforeach; ?>

    <div id="approvalSearchEmpty" class="card" style="display:none;">
        <div class="card-body text-center py-5">
            <i class="bi bi-search fs-1 text-tertiary d-block mb-2"></i>
            <h5 class="fw-bold">Hasil pencarian tidak ditemukan</h5>
            <p class="text-tertiary mb-0" style="font-size:13px;">Tidak ada task yang cocok dengan kata kunci pencarian Anda.</p>
        </div>
    </div>

<?php else: ?>
    <div class="card">
        <div class="card-body text-center py-5">
            <i class="bi bi-check2-square fs-1 text-tertiary d-block mb-2"></i>
            <h5 class="fw-bold">Belum Ada Task Menunggu Approval</h5>
            <p class="text-tertiary mb-0" style="font-size:13px;">Task akan tampil di sini secara otomatis saat user magang mengirimkan hasil pekerjaan.</p>
        </div>
    </div>
<?php endif; ?>
