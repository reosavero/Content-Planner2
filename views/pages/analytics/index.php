<?php
$period = $period ?? 'month';
$platformId = $platformId ?? null;
$postingTrend = $postingTrend ?? [];
$platformComparison = $platformComparison ?? [];
$topContent = $topContent ?? [];
$bestTime = $bestTime ?? [];
$monthlyGrowth = $monthlyGrowth ?? [];
$platforms = $platforms ?? [];
?>
<style>
.chart-container { position: relative; height: 250px; }
.stat-card { text-align: center; padding: 20px; }
.stat-card .stat-icon { font-size: 2rem; margin-bottom: 8px; }
.stat-card .stat-value { font-size: 1.5rem; font-weight: 700; }
.stat-card .stat-label { font-size: var(--text-sm); color: var(--text-tertiary); }
</style>


<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="<?= BASE_URL ?>/analytics" class="row align-items-end">
            <div class="col-6 col-md-3">
                <div class="form-group mb-0">
                    <label class="form-label">Periode</label>
                    <select name="period" class="form-control" onchange="this.form.submit()">
                        <option value="week" <?= $period === 'week' ? 'selected' : '' ?>>7 Hari</option>
                        <option value="month" <?= $period === 'month' ? 'selected' : '' ?>>30 Hari</option>
                        <option value="quarter" <?= $period === 'quarter' ? 'selected' : '' ?>>90 Hari</option>
                        <option value="year" <?= $period === 'year' ? 'selected' : '' ?>>Tahun Ini</option>
                    </select>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="form-group mb-0">
                    <label class="form-label">Platform</label>
                    <select name="platform_id" class="form-control" onchange="this.form.submit()">
                        <option value="">Semua Platform</option>
                        <?php foreach ($platforms as $p): ?>
                            <option value="<?= $p['id'] ?>" <?= $platformId == $p['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($p['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="col-md-6 text-end">
                <button type="button" class="btn btn-outline btn-sm" onclick="window.location.href='<?= BASE_URL ?>/analytics/export/pdf'">
                    <i class="bi bi-file-pdf"></i> PDF
                </button>
                <button type="button" class="btn btn-outline btn-sm" onclick="window.location.href='<?= BASE_URL ?>/analytics/export/excel'">
                    <i class="bi bi-file-earmark-excel"></i> Excel
                </button>
            </div>
        </form>
    </div>
</div>

<div class="row g-4">

    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">Tren Posting</h6>
            </div>
            <div class="card-body">
                <canvas id="trendChart" height="250"></canvas>
            </div>
        </div>
    </div>


    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">Perbandingan Platform</h6>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    <?php if (empty($platformComparison)): ?>
                        <div class="list-group-item text-center text-tertiary py-4">Belum ada data</div>
                    <?php else: ?>
                        <?php foreach ($platformComparison as $p): ?>
                            <div class="list-group-item">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <i class="<?= iconClass($p['icon'] ?? 'bi-globe') ?>" style="color:<?= $p['color'] ?? 'var(--text-secondary)' ?>"></i>
                                        <span class="fw-medium ms-1"><?= htmlspecialchars($p['name']) ?></span>
                                    </div>
                                    <span class="badge badge-soft"><?= $p['total_posts'] ?? 0 ?> posts</span>
                                </div>
                                <div class="d-flex gap-3 mt-2 small text-tertiary">
                                    <span>👍 <?= $p['total_likes'] ?? 0 ?></span>
                                    <span>💬 <?= $p['total_comments'] ?? 0 ?></span>
                                    <span>🔄 <?= $p['total_shares'] ?? 0 ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>


    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">Waktu Posting Terbaik</h6>
            </div>
            <div class="card-body">
                <canvas id="bestTimeChart" height="200"></canvas>
            </div>
        </div>
    </div>


    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">Konten Terpopuler</h6>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    <?php if (empty($topContent)): ?>
                        <div class="list-group-item text-center text-tertiary py-4">Belum ada data</div>
                    <?php else: ?>
                        <?php foreach (array_slice($topContent, 0, 10) as $i => $c): ?>
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="me-2">
                                        <small class="text-tertiary">#<?= $i + 1 ?></small>
                                        <span class="fw-medium small"><?= htmlspecialchars(truncateText($c['judul'] ?? '', 40)) ?></span>
                                        <div class="d-flex gap-2 mt-1 small text-tertiary">
                                            <span>👍 <?= $c['engagement_like'] ?? 0 ?></span>
                                            <span>💬 <?= $c['engagement_comment'] ?? 0 ?></span>
                                            <span>👁️ <?= $c['view_count'] ?? 0 ?></span>
                                        </div>
                                    </div>
                                    <i class="<?= iconClass($c['platform_icon'] ?? 'bi-globe') ?>" style="color:<?= $c['platform_color'] ?? 'var(--text-secondary)' ?>"></i>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {

    var trendCtx = document.getElementById('trendChart');
    if (trendCtx) {
        new Chart(trendCtx, {
            type: 'line',
            data: {
                labels: <?= json_encode(array_column($postingTrend, 'date')) ?>,
                datasets: [{
                    label: 'Sukses',
                    data: <?= json_encode(array_map(fn($r) => (int)$r['success'], $postingTrend)) ?>,
                    borderColor: '#22c55e',
                    backgroundColor: 'rgba(34,197,94,0.1)',
                    fill: true
                }, {
                    label: 'Gagal',
                    data: <?= json_encode(array_map(fn($r) => (int)$r['failed'], $postingTrend)) ?>,
                    borderColor: '#ef4444',
                    backgroundColor: 'rgba(239,68,68,0.1)',
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'top' } },
                scales: { y: { beginAtZero: true } }
            }
        });
    }

    var timeCtx = document.getElementById('bestTimeChart');
    if (timeCtx) {
        new Chart(timeCtx, {
            type: 'bar',
            data: {
                labels: <?= json_encode(array_map(fn($r) => $r['hour'] . ':00', $bestTime)) ?>,
                datasets: [{
                    label: 'Success Rate (%)',
                    data: <?= json_encode(array_map(fn($r) => (float)$r['success_rate'], $bestTime)) ?>,
                    backgroundColor: '#003399'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, max: 100 } }
            }
        });
    }
});
</script>
