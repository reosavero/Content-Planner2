<?php
$planning = $planning ?? [];
$platforms = $platforms ?? [];
$programs = $programs ?? [];
$kategoris = $kategoris ?? [];
$jenisKonten = $jenisKonten ?? [];
$users = $users ?? [];
?>

<div class="card max-w-4xl mx-auto">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h4 class="m-0"><i class="bi bi-pencil-square text-primary"></i> Edit Planning Konten</h4>
        <a href="<?= BASE_URL ?>/planning/<?= $planning['id'] ?>" class="btn btn-ghost btn-sm">
            <i class="bi bi-arrow-left"></i> Kembali ke Detail
        </a>
    </div>
    <div class="card-body">
        <form method="POST" action="<?= BASE_URL ?>/planning/<?= $planning['id'] ?>/update">
            <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= Session::csrfToken() ?>">

            
            <div class="form-section mb-4">
                <h5 class="form-section-title text-sm font-semibold text-tertiary uppercase tracking-wider mb-3">
                    <i class="bi bi-info-circle me-1"></i> Informasi Utama
                </h5>
                
                <div class="form-group mb-3">
                    <label class="form-label font-medium">Judul Konten <span class="text-danger">*</span></label>
                    <input type="text" name="judul" class="form-control" value="<?= htmlspecialchars($planning['judul'] ?? '') ?>" required minlength="3" maxlength="255">
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label font-medium">Platform Sosmed <span class="text-danger">*</span></label>
                            <select name="platform_id" class="form-control" required>
                                <option value="">-- Pilih Platform --</option>
                                <?php foreach ($platforms as $p): ?>
                                    <option value="<?= $p['id'] ?>" <?= ($planning['platform_id'] ?? '') == $p['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($p['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label font-medium">Kategori Konten <span class="text-danger">*</span></label>
                            <select name="kategori_id" class="form-control" required>
                                <option value="">-- Pilih Kategori --</option>
                                <?php foreach ($kategoris as $k): ?>
                                    <option value="<?= $k['id'] ?>" <?= ($planning['kategori_id'] ?? '') == $k['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($k['name']) ?>
                                    </option>
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
                                    <option value="<?= $pr['id'] ?>" <?= ($planning['program_id'] ?? '') == $pr['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($pr['name']) ?>
                                    </option>
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
                                    <option value="<?= $jk['id'] ?>" <?= ($planning['jenis_konten_id'] ?? '') == $jk['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($jk['name']) ?>
                                    </option>
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
                            <input type="date" name="tanggal_posting" class="form-control" value="<?= htmlspecialchars($planning['tanggal_posting'] ?? date('Y-m-d')) ?>" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="form-label font-medium">Jam Posting <span class="text-danger">*</span></label>
                            <input type="time" name="jam_posting" class="form-control" value="<?= htmlspecialchars($planning['jam_posting'] ? date('H:i', strtotime($planning['jam_posting'])) : '09:00') ?>" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="form-label font-medium">Deadline Penyelesaian</label>
                            <input type="date" name="deadline" class="form-control" value="<?= htmlspecialchars($planning['deadline'] ?? '') ?>">
                        </div>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="form-label font-medium">Format Media</label>
                            <select name="media_type" class="form-control">
                                <?php $mt = $planning['media_type'] ?? 'image'; ?>
                                <option value="image" <?= $mt === 'image' ? 'selected' : '' ?>>Gambar / Foto (Single Post)</option>
                                <option value="reels" <?= $mt === 'reels' ? 'selected' : '' ?>>Reels Video Vertical</option>
                                <option value="shorts" <?= $mt === 'shorts' ? 'selected' : '' ?>>YouTube Shorts</option>
                                <option value="video" <?= $mt === 'video' ? 'selected' : '' ?>>Video Regular (Horizontal)</option>
                                <option value="story" <?= $mt === 'story' ? 'selected' : '' ?>>Instagram / FB Story</option>
                                <option value="carousel" <?= $mt === 'carousel' ? 'selected' : '' ?>>Carousel (Multi Image)</option>
                                <option value="text" <?= $mt === 'text' ? 'selected' : '' ?>>Teks / Skrip Artikel</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="form-label font-medium">Prioritas Pengerjaan</label>
                            <select name="priority" class="form-control">
                                <?php $pr = $planning['priority'] ?? 'medium'; ?>
                                <option value="low" <?= $pr === 'low' ? 'selected' : '' ?>>Low (Santai)</option>
                                <option value="medium" <?= $pr === 'medium' ? 'selected' : '' ?>>Medium (Normal)</option>
                                <option value="high" <?= $pr === 'high' ? 'selected' : '' ?>>High (Penting)</option>
                                <option value="urgent" <?= $pr === 'urgent' ? 'selected' : '' ?>>Urgent (Segera)</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="form-label font-medium">PIC (Penanggung Jawab)</label>
                            <select name="pic_id" class="form-control">
                                <option value="">-- Pilih PIC User --</option>
                                <?php foreach ($users as $u): ?>
                                    <option value="<?= $u['id'] ?>" <?= ($planning['pic_id'] ?? '') == $u['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($u['name']) ?>
                                    </option>
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
                    <textarea name="caption" class="form-control" rows="4"><?= htmlspecialchars($planning['caption'] ?? '') ?></textarea>
                </div>

                <div class="form-group mb-3">
                    <label class="form-label font-medium">Hashtags</label>
                    <input type="text" name="hashtag_text" class="form-control" value="<?= htmlspecialchars($planning['hashtag_text'] ?? '') ?>">
                </div>

                <div class="form-group mb-3">
                    <label class="form-label font-medium">Catatan / Instruksi Khusus</label>
                    <textarea name="catatan" class="form-control" rows="3"><?= htmlspecialchars($planning['catatan'] ?? '') ?></textarea>
                </div>
            </div>

            
            <div class="d-flex justify-content-end gap-2 border-top pt-3">
                <a href="<?= BASE_URL ?>/planning/<?= $planning['id'] ?>" class="btn btn-ghost">Batal</a>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-circle me-1"></i> Update Planning
                </button>
            </div>
        </form>
    </div>
</div>
