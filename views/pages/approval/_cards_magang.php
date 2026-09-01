<?php
$groupedTasks = $groupedTasks ?? [];
?>
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
            <?php foreach ($group['tasks'] as $task):
                $isPending = ($task['status'] === 'Pending Approval');
                $isApproved = in_array($task['status'], ['Approved', 'Selesai', 'Publish', 'Schedule']);
                $isRevision = (($task['status'] ?? '') === 'Need Revision');
                $cardClass = $isApproved ? 'is-approved' : ($isPending ? 'is-pending' : ($isRevision ? 'is-revision' : ''));
                $badgeClass = $isPending ? 'pending' : ($isRevision ? 'revision' : 'approved');
                $badgeIcon = $isPending ? 'bi-clock-history' : ($isRevision ? 'bi-exclamation-triangle-fill' : 'bi-check-circle-fill');
                $badgeText = $isPending ? 'Menunggu Approval' : ($isRevision ? 'Perlu Revisi' : 'Approved');
            ?>
                <div class="task-card-approval <?= $cardClass ?>" onclick="openTaskSubmitModal(<?= (int)$task['id'] ?>)">
                    <div>
                        <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                            <div class="task-title" style="margin-bottom:0; flex:1;">
                                <?= htmlspecialchars($task['title'] ?: '(Belum ada judul)') ?>
                            </div>
                            <span class="magang-badge-status <?= $badgeClass ?>" style="font-size:10px; padding:3px 8px; flex-shrink:0;">
                                <i class="bi <?= $badgeIcon ?>"></i>
                                <?= $badgeText ?>
                            </span>
                        </div>
                    </div>

                    <div class="task-meta">
                        <span><i class="bi bi-person me-1"></i> <?= htmlspecialchars($task['assigned_by_name'] ?: 'Admin') ?></span>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endforeach; ?>
<?php else: ?>
    <div class="card">
        <div class="card-body">
            <div class="empty-state p-5 text-center">
                <i class="bi bi-inbox fs-1 text-tertiary"></i>
                <h5 class="mt-2">Belum Ada Task Dikirim</h5>
                <p class="text-tertiary">Task yang Anda kirim dari Halaman Dashboard untuk approval Admin akan tampil di sini.</p>
            </div>
        </div>
    </div>
<?php endif; ?>
