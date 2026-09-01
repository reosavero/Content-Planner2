<?php
$platforms = $platforms ?? [];
$programs = $programs ?? [];
$kategoris = $kategoris ?? [];
$jenisKonten = $jenisKonten ?? [];
$users = $users ?? [];
?>

<div class="card max-w-4xl mx-auto">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h4 class="m-0"><i class="bi bi-plus-circle text-primary"></i> Buat Planning Konten Baru</h4>
        <a href="<?= BASE_URL ?>/planning" class="btn btn-ghost btn-sm">
            <i class="bi bi-arrow-left"></i> Kembali ke Daftar
        </a>
    </div>
    <div class="card-body">
        <form method="POST" action="<?= BASE_URL ?>/planning/store" enctype="multipart/form-data">
            <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= Session::csrfToken() ?>">

            
            <div class="form-section mb-4">
                <h5 class="form-section-title text-sm font-semibold text-tertiary uppercase tracking-wider mb-3">
                    <i class="bi bi-info-circle me-1"></i> Informasi Utama
                </h5>
                
                <div class="form-group mb-3">
                    <label class="form-label font-medium">Judul Konten <span class="text-danger">*</span></label>
                    <input type="text" name="judul" class="form-control" placeholder="Contoh: Liputan Khusus Festival Budaya Jatim" required minlength="3" maxlength="255">
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label font-medium">Platform Sosmed <span class="text-danger">*</span></label>
                            <div class="form-hint text-xs text-tertiary mb-2">Pilih satu atau lebih platform target posting</div>
                            <div class="row g-2">
                                <?php foreach ($platforms as $p): ?>
                                    <div class="col-6 col-md-4 col-lg-3">
                                        <label class="form-check form-check-inline d-block">
                                            <input class="form-check-input" type="checkbox" name="platform_ids[]" value="<?= $p['id'] ?>" style="accent-color: <?= $p['color'] ?>;">
                                            <span class="form-check-label d-flex align-items-center gap-1" style="color: <?= $p['color'] ?>;">
                                                <i class="bi <?= $p['icon'] ?>" style="font-size: 1.1em;"></i>
                                                <span class="text-truncate" style="max-width: 100px;"><?= htmlspecialchars($p['name']) ?></span>
                                            </span>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <input type="hidden" name="platform_id" value="">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label font-medium">Kategori Konten <span class="text-danger">*</span></label>
                            <select name="kategori_id" class="form-control" required>
                                <option value="">-- Pilih Kategori --</option>
                                <?php foreach ($kategoris as $k): ?>
                                    <option value="<?= $k['id'] ?>"><?= htmlspecialchars($k['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label font-medium">Program TVRI</label>
                            <select name="program_id" class="form-control">
                                <option value="">-- Pilih Program TV (Opsional) --</option>
                                <?php foreach ($programs as $pr): ?>
                                    <option value="<?= $pr['id'] ?>"><?= htmlspecialchars($pr['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label font-medium">Jenis Konten</label>
                            <select name="jenis_konten_id" class="form-control">
                                <option value="">-- Pilih Jenis Format --</option>
                                <?php foreach ($jenisKonten as $jk): ?>
                                    <option value="<?= $jk['id'] ?>"><?= htmlspecialchars($jk['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            
            <div class="form-section mb-4">
                <h5 class="form-section-title text-sm font-semibold text-tertiary uppercase tracking-wider mb-3">
                    <i class="bi bi-clock-history me-1"></i> Penjadwalan & Tim
                </h5>

                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="form-label font-medium">Tanggal Posting <span class="text-danger">*</span></label>
                            <input type="date" name="tanggal_posting" class="form-control" value="<?= date('Y-m-d') ?>" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="form-label font-medium">Jam Posting <span class="text-danger">*</span></label>
                            <input type="time" name="jam_posting" class="form-control" value="09:00" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="form-label font-medium">Deadline Penyelesaian</label>
                            <input type="date" name="deadline" class="form-control">
                        </div>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="form-label font-medium">Format Media</label>
                            <select name="media_type" class="form-control">
                                <option value="image">Gambar / Foto (Single Post)</option>
                                <option value="reels">Reels Video Vertical</option>
                                <option value="shorts">YouTube Shorts</option>
                                <option value="video">Video Regular (Horizontal)</option>
                                <option value="story">Instagram / FB Story</option>
                                <option value="carousel">Carousel (Multi Image)</option>
                                <option value="text">Teks / Skrip Artikel</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="form-label font-medium">Prioritas Pengerjaan</label>
                            <select name="priority" class="form-control">
                                <option value="medium" selected>Medium (Normal)</option>
                                <option value="low">Low (Santai)</option>
                                <option value="high">High (Penting)</option>
                                <option value="urgent">Urgent (Segera)</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="form-label font-medium">PIC (Penanggung Jawab)</label>
                            <select name="pic_id" class="form-control">
                                <option value="">-- Pilih PIC User --</option>
                                <?php foreach ($users as $u): ?>
                                    <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            
            <div class="form-section mb-4">
                <h5 class="form-section-title text-sm font-semibold text-tertiary uppercase tracking-wider mb-3">
                    <i class="bi bi-file-earmark-text me-1"></i> Naskah & Catatan
                </h5>

                <div class="form-group mb-3">
                    <label class="form-label font-medium">Draf Caption / Script Naskah</label>
                    <textarea name="caption" class="form-control" rows="4" placeholder="Tuliskan rancangan caption atau naskah narration di sini..."></textarea>
                </div>

                <div class="form-group mb-3">
                    <label class="form-label font-medium">Hashtags</label>
                    <input type="text" name="hashtag_text" class="form-control" placeholder="Contoh: #TVRIJatim #JatimHariIni #BeritaJatim">
                </div>

                <div class="form-group mb-3">
                    <label class="form-label font-medium">Catatan / Instruksi Khusus</label>
                    <textarea name="catatan" class="form-control" rows="3" placeholder="Instruksi untuk editor visual/video, link materi mentah, dll..."></textarea>
                </div>
            </div>

            
            <div class="d-flex justify-content-end gap-2 border-top pt-3">
                <a href="<?= BASE_URL ?>/planning" class="btn btn-ghost">Batal</a>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-circle me-1"></i> Simpan Planning
                </button>
            </div>
        <script>
document.addEventListener("DOMContentLoaded", function() {
    const checkboxes = document.querySelectorAll("input[name=\"platform_ids[]\"]");
    const hiddenInput = document.querySelector("input[name=\"platform_id\"]");
    
    function syncPlatformId() {
        const checked = Array.from(checkboxes).filter(c => c.checked).map(c => c.value);
        hiddenInput.value = checked[0] || "";
    }
    
    checkboxes.forEach(cb => cb.addEventListener("change", syncPlatformId));
    
    // Initial sync
    syncPlatformId();
});
</script>

</form>
    </div>
</div>
