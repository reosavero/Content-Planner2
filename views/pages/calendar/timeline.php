
<style>
.timeline-roadmap {
    position: relative;
    padding: var(--space-4) 0;
}
.timeline-roadmap::before {
    content: '';
    position: absolute;
    left: 24px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: var(--border);
}
.timeline-week {
    position: relative;
    padding-left: 60px;
    margin-bottom: var(--space-6);
}
.timeline-week-marker {
    position: absolute;
    left: 12px;
    top: 0;
    width: 28px;
    height: 28px;
    border-radius: 50%;
    background: var(--tvri-blue);
    border: 3px solid var(--surface);
    box-shadow: var(--shadow-md);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 12px;
    font-weight: 700;
    z-index: 1;
}
.timeline-week-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: var(--space-3);
}
.timeline-week-title {
    font-weight: var(--font-weight-semibold);
    font-size: var(--text-md);
    color: var(--text-primary);
}
.timeline-week-badge {
    font-size: var(--text-xs);
    padding: 2px 10px;
    border-radius: var(--radius-full);
    background: var(--tvri-blue-50);
    color: var(--tvri-blue);
    font-weight: var(--font-weight-medium);
}
.timeline-content-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: var(--space-3);
}
.timeline-content-card {
    background: var(--surface);
    border: 1px solid var(--border-light);
    border-radius: var(--radius-md);
    padding: var(--space-3) var(--space-4);
    transition: all var(--transition-fast);
    cursor: pointer;
    position: relative;
    overflow: hidden;
}
.timeline-content-card:hover {
    border-color: var(--tvri-blue-200);
    box-shadow: var(--shadow-sm);
    transform: translateY(-1px);
}
.timeline-content-card::before {
    content: '';
    position: absolute;
    left: 0;
    top: 0;
    bottom: 0;
    width: 3px;
}
.timeline-content-card.status-draft::before { background: #6c757d; }
.timeline-content-card.status-review::before { background: #ffc107; }
.timeline-content-card.status-approved::before { background: #0d6efd; }
.timeline-content-card.status-scheduled::before { background: #00b0ff; }
.timeline-content-card.status-success::before { background: #00c853; }
.timeline-content-card.status-failed::before { background: #ff1744; }
.timeline-content-card.status-revision::before { background: #ff9100; }

.timeline-card-title {
    font-size: var(--text-sm);
    font-weight: var(--font-weight-medium);
    color: var(--text-primary);
    margin-bottom: 4px;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
.timeline-card-meta {
    display: flex;
    align-items: center;
    gap: var(--space-2);
    font-size: var(--text-xs);
    color: var(--text-tertiary);
    margin-top: 4px;
    flex-wrap: wrap;
}
.timeline-empty {
    text-align: center;
    padding: var(--space-8);
    color: var(--text-tertiary);
}
.timeline-empty i {
    font-size: 2.5rem;
    margin-bottom: var(--space-3);
}
</style>

<div class="row">

    <div class="col-12 col-lg-3">
        <div class="card">
            <div class="card-header">
                <h5><i class="bi bi-funnel"></i> Filter</h5>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label class="form-label">PIC</label>
                    <select class="form-control" id="filterPic" onchange="loadTimeline()">
                        <option value="">Semua PIC</option>
                        <?php foreach ($pics as $pic): ?>
                            <option value="<?= $pic['id'] ?>"><?= htmlspecialchars($pic['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Status</label>
                    <select class="form-control" id="filterStatus" onchange="loadTimeline()">
                        <option value="">Semua Status</option>
                        <option value="draft">Draft</option>
                        <option value="review">Review</option>
                        <option value="approved">Approved</option>
                        <option value="scheduled">Terjadwal</option>
                        <option value="success">Berhasil</option>
                        <option value="failed">Gagal</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Periode</label>
                    <select class="form-control" id="filterPeriod" onchange="loadTimeline()">
                        <option value="4">4 Minggu</option>
                        <option value="8">8 Minggu</option>
                        <option value="12">12 Minggu</option>
                    </select>
                </div>
                <button class="btn btn-primary btn-sm w-100 mt-2" onclick="loadTimeline()">
                    <i class="bi bi-search"></i> Terapkan Filter
                </button>
            </div>
        </div>


        <div class="card mt-3">
            <div class="card-header">
                <h5><i class="bi bi-info-circle"></i> Legend</h5>
            </div>
            <div class="card-body" style="display: flex; flex-direction: column; gap: var(--space-2);">
                <div style="display: flex; align-items: center; gap: var(--space-2); font-size: var(--text-xs);">
                    <span style="width: 12px; height: 12px; border-radius: 2px; background: #6c757d;"></span> Draft
                </div>
                <div style="display: flex; align-items: center; gap: var(--space-2); font-size: var(--text-xs);">
                    <span style="width: 12px; height: 12px; border-radius: 2px; background: #ffc107;"></span> Review
                </div>
                <div style="display: flex; align-items: center; gap: var(--space-2); font-size: var(--text-xs);">
                    <span style="width: 12px; height: 12px; border-radius: 2px; background: #0d6efd;"></span> Approved
                </div>
                <div style="display: flex; align-items: center; gap: var(--space-2); font-size: var(--text-xs);">
                    <span style="width: 12px; height: 12px; border-radius: 2px; background: #00b0ff;"></span> Terjadwal
                </div>
                <div style="display: flex; align-items: center; gap: var(--space-2); font-size: var(--text-xs);">
                    <span style="width: 12px; height: 12px; border-radius: 2px; background: #00c853;"></span> Berhasil
                </div>
                <div style="display: flex; align-items: center; gap: var(--space-2); font-size: var(--text-xs);">
                    <span style="width: 12px; height: 12px; border-radius: 2px; background: #ff1744;"></span> Gagal
                </div>
                <div style="display: flex; align-items: center; gap: var(--space-2); font-size: var(--text-xs);">
                    <span style="width: 12px; height: 12px; border-radius: 2px; background: #ff9100;"></span> Revisi
                </div>
            </div>
        </div>
    </div>


    <div class="col-12 col-lg-9">
        <div class="card">
            <div class="card-header">
                <h5><i class="bi bi-calendar-range" style="color: var(--tvri-blue);"></i> Content Roadmap</h5>
                <div class="d-flex gap-sm">
                    <a href="<?= BASE_URL ?>/calendar" class="btn btn-sm btn-outline">
                        <i class="bi bi-calendar3"></i> Kalender
                    </a>
                    <a href="<?= BASE_URL ?>/planning/create" class="btn btn-sm btn-primary">
                        <i class="bi bi-plus"></i> Buat
                    </a>
                </div>
            </div>
            <div class="card-body" id="timelineContainer">
                <div class="text-center py-5">
                    <div class="spinner"></div>
                    <p class="mt-3 text-tertiary">Memuat timeline...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let timelineData = [];

document.addEventListener('DOMContentLoaded', function() {
    loadTimeline();
});

function loadTimeline() {
    const container = document.getElementById('timelineContainer');
    container.innerHTML = '<div class="text-center py-5"><div class="spinner"></div><p class="mt-3 text-tertiary">Memuat...</p></div>';

    const picId = document.getElementById('filterPic').value;
    const status = document.getElementById('filterStatus').value;
    const weeks = parseInt(document.getElementById('filterPeriod').value);

    const endDate = new Date();
    endDate.setDate(endDate.getDate() + (weeks * 7));

    const params = new URLSearchParams({
        start: new Date().toISOString().split('T')[0],
        end: endDate.toISOString().split('T')[0],
        pic_id: picId,
        status: status
    });

    fetch(BASE_URL + '/timeline/data?' + params.toString())
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                renderTimeline(res.data, weeks);
            } else {
                container.innerHTML = '<div class="empty-state"><i class="bi bi-exclamation-triangle"></i><p>Gagal memuat data</p></div>';
            }
        })
        .catch(() => {
            container.innerHTML = '<div class="empty-state"><i class="bi bi-exclamation-triangle"></i><p>Error jaringan</p></div>';
        });
}

function renderTimeline(data, weeks) {
    const container = document.getElementById('timelineContainer');

    if (!data || data.length === 0) {
        container.innerHTML = '<div class="empty-state"><i class="bi bi-calendar-range"></i><h5>Tidak Ada Konten</h5><p>Belum ada konten di periode ini.</p><a href="<?= BASE_URL ?>/planning/create" class="btn btn-primary btn-sm"><i class="bi bi-plus"></i> Buat Planning</a></div>';
        return;
    }

    const weeksMap = {};
    data.forEach(item => {
        const date = new Date(item.tanggal_posting);
        const weekStart = new Date(date);
        weekStart.setDate(date.getDate() - date.getDay() + 1);
        const weekKey = weekStart.toISOString().split('T')[0];

        if (!weeksMap[weekKey]) {
            weeksMap[weekKey] = {
                start: weekStart,
                items: [],
                platforms: new Set()
            };
        }
        weeksMap[weekKey].items.push(item);
        if (item.platform) weeksMap[weekKey].platforms.add(item.platform);
    });

    const sortedWeeks = Object.keys(weeksMap).sort();

    let html = '<div class="timeline-roadmap">';

    sortedWeeks.forEach((weekKey, idx) => {
        const week = weeksMap[weekKey];
        const weekEnd = new Date(week.start);
        weekEnd.setDate(weekEnd.getDate() + 6);

        const weekLabel = week.start.toLocaleDateString('id-ID', { day: 'numeric', month: 'short' }) +
                         ' - ' + weekEnd.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });

        const done = week.items.filter(i => i.status === 'success').length;
        const pending = week.items.filter(i => i.status !== 'success' && i.status !== 'cancelled').length;
        const total = week.items.length;
        const progress = total > 0 ? Math.round((done / total) * 100) : 0;

        html += '<div class="timeline-week">';
        html += '<div class="timeline-week-marker">' + (idx + 1) + '</div>';
        html += '<div class="timeline-week-header">';
        html += '<div><div class="timeline-week-title">' + weekLabel + '</div>';
        html += '<div style="font-size: var(--text-xs); color: var(--text-tertiary);">';
        html += 'Platform: ' + [...week.platforms].join(', ');
        html += '</div></div>';
        html += '<div style="text-align: right;">';
        html += '<div class="timeline-week-badge">' + done + '/' + total + ' selesai</div>';
        html += '<div class="progress-bar mt-1" style="height: 4px; width: 100px;">';
        html += '<div class="progress-bar-fill" style="width: ' + progress + '%;"></div>';
        html += '</div></div></div>';

        html += '<div class="timeline-content-grid">';

        week.items.forEach(item => {
            const platformIcon = item.platform_icon ? '<i class="bi ' + item.platform_icon + '" style="color:' + item.platform_color + '"></i>' : '';
            const statusLabels = {
                'draft': 'Draft', 'review': 'Review', 'approved': 'Approved',
                'scheduled': 'Terjadwal', 'posting': 'Posting', 'success': 'Berhasil',
                'failed': 'Gagal', 'revision': 'Revisi', 'cancelled': 'Dibatalkan'
            };

            html += '<a href="<?= BASE_URL ?>/planning/' + item.id + '" class="timeline-content-card status-' + item.status + '">';
            html += '<div class="timeline-card-title">' + escapeHtml(item.judul) + '</div>';
            html += '<div class="timeline-card-meta">';
            if (platformIcon) html += platformIcon + ' ' + (item.platform || '') + ' <span style="opacity: 0.3;">|</span> ';
            html += '<span class="badge badge-' + item.status + '">' + (statusLabels[item.status] || item.status) + '</span>';
            if (item.pic_name) html += ' <span style="opacity: 0.3;">|</span> ' + item.pic_name;
            html += '</div>';
            html += '</a>';
        });

        html += '</div></div>';
    });

    html += '</div>';
    container.innerHTML = html;
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
</script>
