<?php
$data = $data ?? [];
$search_q = $search_q ?? '';
$rows_only = $rows_only ?? false;
?>
<?php if (!$rows_only): ?>
<tr id="logEmptyRow" style="<?= empty($data) ? '' : 'display:none;' ?>">
    <td colspan="4">
        <div class="empty-state">
            <i class="bi bi-activity" style="font-size:2rem;color:var(--text-tertiary);"></i>
            <p id="logEmptyText" style="color:var(--text-tertiary);margin-top:8px;">Belum ada log aktivitas</p>
        </div>
    </td>
</tr>
<?php endif; ?>
<?php foreach ($data as $log): ?>
    <tr data-log>
        <td>
            <small class="text-tertiary"><?= date('d M Y H:i:s', strtotime($log['created_at'])) ?></small>
        </td>
        <td>
            <div class="d-flex align-items-center gap-2">
                <?php if (!empty($log['user_avatar'])): ?>
                    <img src="<?= BASE_URL ?>/uploads/<?= $log['user_avatar'] ?>" alt="" style="width:24px;height:24px;border-radius:50%;object-fit:cover;">
                <?php else: ?>
                    <div style="width:24px;height:24px;border-radius:50%;background:var(--tvri-blue);color:white;display:flex;align-items:center;justify-content:center;font-size:10px;font-weight:600;">
                        <?= strtoupper(substr($log['user_name'] ?? 'U', 0, 1)) ?>
                    </div>
                <?php endif; ?>
                <small><?= htmlspecialchars($log['user_name'] ?? 'System') ?></small>
            </div>
        </td>
        <td>
            <?php
            $action = $log['action'] ?? '';
            $actionClass = match($action) {
                'create' => 'badge-success',
                'update' => 'badge-info',
                'delete' => 'badge-danger',
                'login' => 'badge-primary',
                'logout' => 'badge-secondary',
                'approve' => 'badge-success',
                'reject' => 'badge-danger',
                'request_revision' => 'badge-warning',
                'submit_approval' => 'badge-info',
                'update_approval' => 'badge-warning',
                'submit_review' => 'badge-info',
                'upload' => 'badge-primary',
                'upload_social' => 'badge-primary',
                'auto_post' => 'badge-success',
                'auto_post_failed' => 'badge-danger',
                'publish' => 'badge-success',
                'recheck' => 'badge-info',
                'import' => 'badge-primary',
                'register' => 'badge-primary',
                'update_status' => 'badge-info',
                'update_schedule' => 'badge-info',
                'run_now' => 'badge-warning',
                'cancel' => 'badge-secondary',
                'retry' => 'badge-warning',
                'approve_user' => 'badge-success',
                'reject_user' => 'badge-danger',
                'delete_user' => 'badge-danger',
                'change_password' => 'badge-warning',
                'restore' => 'badge-warning',
                default => 'badge-soft'
            };
            if (str_starts_with($action, 'publish_failed_')) $actionClass = 'badge-danger';
            elseif (str_starts_with($action, 'publish_')) $actionClass = 'badge-success';
            elseif (str_starts_with($action, 'connect_') || str_starts_with($action, 'reconnect_')) $actionClass = 'badge-primary';
            elseif (str_starts_with($action, 'disconnect_')) $actionClass = 'badge-secondary';
            ?>
            <span class="badge <?= $actionClass ?>"><?= htmlspecialchars(ucfirst($action) ?: '-') ?></span>
        </td>
        <td>
            <small class="text-tertiary"><?= htmlspecialchars($log['description'] ?? '-') ?></small>
        </td>
    </tr>
<?php endforeach; ?>
