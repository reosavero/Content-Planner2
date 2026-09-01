<?php
$data = $data ?? [];
$filters = $filters ?? [];
$modules = $modules ?? [];
$actions = $actions ?? [];
$users = $users ?? [];
$search_q = $search_q ?? '';
$pagination = $pagination ?? [
    'current_page' => 1,
    'total_pages' => 1,
    'total_records' => count($data),
    'per_page' => 25,
];
?>

<!-- Header & Filter Search -->
<div class="d-flex align-items-center justify-content-between gap-3 mb-4 flex-wrap">
    <div>
        <h4 class="fw-bold mb-1 d-flex align-items-center"><i class="bi bi-activity text-primary fs-4" style="margin-right:10px;"></i><span>Log Aktivitas</span></h4>
        <p class="text-tertiary mb-0" style="font-size:13px;">Riwayat seluruh aktivitas pengguna di aplikasi.</p>
    </div>
    <form method="GET" action="<?= BASE_URL ?>/activity-logs" class="d-flex gap-2 align-items-center" style="position: relative; min-width: 260px; max-width: 340px; width: 100%;">
        <i class="bi bi-search" style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--text-tertiary); font-size: 14px; pointer-events: none; z-index: 10;"></i>
        <input type="text" name="q" id="logSearchInput" class="form-control" placeholder="Cari log..." value="<?= htmlspecialchars($search_q) ?>" autocomplete="off" style="border-radius: 10px; font-size: 13px; height: 38px; padding-left: 38px !important;" onkeyup="filterLogsPerLetter(this.value)">
    </form>
</div>

<!-- Log Activity Table Card -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center py-3">
        <h6 class="mb-0 font-bold d-flex align-items-center gap-2" style="font-size: 14px;">
            <i class="bi bi-journal-text text-primary"></i> Daftar Log Aktivitas
        </h6>
        <span class="badge badge-secondary" style="font-size: 12px; font-weight: 500;">
            Total: <?= (int)$pagination['total_records'] ?> Log
        </span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0" style="font-size:var(--text-sm);">
                <thead>
                    <tr>
                        <th>Waktu</th>
                        <th>User</th>
                        <th>Aksi</th>
                        <th>Deskripsi</th>
                    </tr>
                </thead>
                <tbody id="logTableBody">
                    <?php require __DIR__ . '/_rows.php'; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php if ($pagination['total_pages'] > 1): ?>
        <div class="card-footer d-flex justify-content-between align-items-center py-3 flex-wrap gap-2">
            <div class="text-xs text-tertiary">
                Halaman <?= (int)$pagination['current_page'] ?> dari <?= (int)$pagination['total_pages'] ?> 
                (Menampilkan <?= min($pagination['total_records'], (($pagination['current_page'] - 1) * $pagination['per_page']) + 1) ?> - <?= min($pagination['total_records'], $pagination['current_page'] * $pagination['per_page']) ?> dari <?= (int)$pagination['total_records'] ?> log)
            </div>
            <ul class="pagination pagination-sm mb-0">
                <li class="page-item <?= $pagination['current_page'] <= 1 ? 'disabled' : '' ?>">
                    <a class="page-link" href="<?= BASE_URL ?>/activity-logs?page=<?= max(1, $pagination['current_page'] - 1) ?>&q=<?= urlencode($search_q) ?>">
                        <i class="bi bi-chevron-left"></i>
                    </a>
                </li>
                <?php
                $startP = max(1, $pagination['current_page'] - 2);
                $endP = min($pagination['total_pages'], $pagination['current_page'] + 2);

                if ($startP > 1) {
                    echo '<li class="page-item"><a class="page-link" href="' . BASE_URL . '/activity-logs?page=1&q=' . urlencode($search_q) . '">1</a></li>';
                    if ($startP > 2) {
                        echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                    }
                }

                for ($p = $startP; $p <= $endP; $p++):
                ?>
                    <li class="page-item <?= $p === (int)$pagination['current_page'] ? 'active' : '' ?>">
                        <a class="page-link" href="<?= BASE_URL ?>/activity-logs?page=<?= $p ?>&q=<?= urlencode($search_q) ?>"><?= $p ?></a>
                    </li>
                <?php endfor; ?>

                <?php if ($endP < $pagination['total_pages']): ?>
                    <?php if ($endP < $pagination['total_pages'] - 1): ?>
                        <li class="page-item disabled"><span class="page-link">...</span></li>
                    <?php endif; ?>
                    <li class="page-item"><a class="page-link" href="<?= BASE_URL ?>/activity-logs?page=<?= $pagination['total_pages'] ?>&q=<?= urlencode($search_q) ?>"><?= $pagination['total_pages'] ?></a></li>
                <?php endif; ?>

                <li class="page-item <?= $pagination['current_page'] >= $pagination['total_pages'] ? 'disabled' : '' ?>">
                    <a class="page-link" href="<?= BASE_URL ?>/activity-logs?page=<?= min($pagination['total_pages'], $pagination['current_page'] + 1) ?>&q=<?= urlencode($search_q) ?>">
                        <i class="bi bi-chevron-right"></i>
                    </a>
                </li>
            </ul>
        </div>
    <?php endif; ?>
</div>

<script>
function filterLogsPerLetter(query) {
    var q = query.toLowerCase().trim();

    var rows = document.querySelectorAll('#logTableBody tr[data-log]');
    var visibleCount = 0;

    rows.forEach(function(row) {
        var text = row.textContent.toLowerCase();
        if (q === '' || text.indexOf(q) !== -1) {
            row.style.display = '';
            visibleCount++;
        } else {
            row.style.display = 'none';
        }
    });

    var emptyRow = document.getElementById('logEmptyRow');
    if (emptyRow && rows.length > 0) {
        if (visibleCount === 0) {
            emptyRow.style.display = '';
            document.getElementById('logEmptyText').textContent = 'Tidak ditemukan log yang cocok dengan pencarian "' + query + '"';
        } else {
            emptyRow.style.display = 'none';
        }
    }
}

var logMaxId = <?= (int)($latest_log_id ?? 0) ?>;

function pollActivityLogs() {
    if (<?= (int)($pagination['current_page'] ?? 1) ?> !== 1) return;
    fetch(BASE_URL + '/activity-logs?after_id=' + logMaxId, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
        .then(function(r) { return r.json(); })
        .then(function(res) {
            if (res && res.success && res.data && res.data.has_new) {
                var html = res.data.rows_html ? res.data.rows_html : '';
                if (html.trim() !== '') {
                    if (res.data.max_id > logMaxId) logMaxId = res.data.max_id;
                    var tbody = document.getElementById('logTableBody');
                    if (tbody) {
                        tbody.insertAdjacentHTML('afterbegin', html);
                        var inp = document.getElementById('logSearchInput');
                        if (inp) filterLogsPerLetter(inp.value);
                    }
                }
            }
        })
        .catch(function() {});
}

setInterval(pollActivityLogs, 10000);
</script>
