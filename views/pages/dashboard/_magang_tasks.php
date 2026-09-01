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
