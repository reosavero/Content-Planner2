# 📋 Panduan Pemetaan Halaman Website & Sheet Excel `TIMELINE JULI 2026.xlsx`

Dokumen ini menjelaskan hubungan antara **Halaman Website Dashboard Social Media Planner TVRI Jawa Timur** dengan **Sheet (Halaman/Tab)** pada file Excel `TIMELINE JULI 2026.xlsx`.

---

## 🗺️ Pemetaan Utama Halaman Website vs Sheet Excel

| Halaman Website | URL | Sheet Excel Terkait | Deskripsi Data & Kolom yang Digunakan |
| :--- | :--- | :--- | :--- |
| **Timeline (Card View)** | `/timeline` | `CONTENT PLAN JULI` | • **Kolom A (Konten)** ➔ Judul / Nama Konten<br>• **Kolom B & C** ➔ Tanggal Produksi & Hari<br>• **Kolom D** ➔ Jenis Konten (Feed, Reels, Story, dll.)<br>• **Kolom E** ➔ Status Task (Belum, Proses, Selesai)<br>• **Kolom F** ➔ PIC Penanggung Jawab<br>• **Kolom G & H** ➔ Link Materi & Link Edit<br>• **Kolom I** ➔ Catatan / Deskripsi Task<br>• **Kolom J & K** ➔ Link Referensi & Deadline |
| **Planning Konten** | `/planning` | `CONTENT PLAN JULI` | • Rekapitulasi perencanaan konten per program TVRI, jenis media, kategori, dan tenggat waktu (deadline). |
| **Kalender Konten** | `/calendar` | `CONTENT UPLOAD JULI` | • **Summary Harian** ➔ Jumlah Feed, Reels, dan Story per tanggal.<br>• **Post Time** ➔ Jam posting (misal: 08.00 - 18.00 WIB).<br>• **Status Kalender** ➔ Status keterisian postingan harian. |
| **Scheduler & Auto Post** | `/scheduler` | `CONTENT UPLOAD JULI` | • Jadwal penayangan otomatis postingan berdasarkan jam tayang dan tanggal publikasi. |
| **Posting Medsos** | `/posting` | `CONTENT MEDSOS (FB,X,YT,TT)` | • **Distribusi Platform** ➔ Daftar postingan khusus untuk TikTok, YouTube Shorts, Facebook, dan Twitter/X.<br>• **Jam & Tanggal Posting Medsos** per platform. |
| **Analytics & Distribusi** | `/analytics` | `CONTENT MEDSOS (FB,X,YT,TT)` & `CONTENT UPLOAD JULI` | • Statistik jumlah konten terpublikasi per platform dan tren posting bulanan. |
| **Master Template & Links** | `/master/template` | `ADMIN + LINK TEMPLATE` | • **Folder & Template** ➔ Link Drive materi, Canva template, dan acuan aset grafis/video. |
| **Users & Roles** | `/users` | `ADMIN + LINK TEMPLATE` | • **Daftar PIC & Tim** ➔ Penugasan user magang, admin, dan super admin. |

---

## 📌 Rincian Struktur Sheet Excel `TIMELINE JULI 2026.xlsx`

### 1. Sheet `CONTENT PLAN JULI` ➔ Halaman Timeline & Planning
> **Fungsi Utama**: Sumber data untuk tampilan **Card Timeline Harian** dan **Detail Konten**.

* **Kolom A (KONTEN)**: Judul utama konten (misal: *Berita JHI (01 JULI)*, *EDIT FLYER ASSALAM*, *TAKE VIDEO Konten Receh*).
* **Kolom B (TGL PRODUKSI)**: Tanggal pelaksanaan task.
* **Kolom C (HARI)**: Hari pelaksanaan (Senin - Minggu).
* **Kolom D (JENIS)**: Jenis format media (*Feed*, *Reels*, *Story*, *Flyer/Poster*, *Thumbnail*, *Teresterial*, dll.).
* **Kolom E (STATUS)**: Status pengerjaan (*Belum*, *Proses*, *Selesai*).
* **Kolom F (PIC)**: Nama penanggung jawab (*Dinda*, *Devan*, *Ryu*, *Arthur*, *Putra*, *Farhan*, dll.).
* **Kolom G (LINK MATERI)**: Link Google Drive / folder materi mentah.
* **Kolom H (LINK EDIT)**: Link Canva / file project editan.
* **Kolom I (CATATAN)**: Instruksi detail & deskripsi pengerjaan (misal: *Ubah ukuran video jadi portrait*, *Jadwal jam WIB pertandingan*, dll.).
* **Kolom J (REFERENSI)**: Link acuan referensi visual/video.
* **Kolom K (DEADLINE)**: Tenggat waktu penyelesaian task.

---

### 2. Sheet `CONTENT UPLOAD JULI` ➔ Halaman Kalender & Scheduler
> **Fungsi Utama**: Sumber data untuk **Kalender Harian** dan **Monitoring Jumlah Postingan**.

* **Hari & Tanggal Posting**: Menentukan tanggal pada kalender harian.
* **Jumlah Feed / Reels / Story**: Menentukan badge kuantitas konten per tanggal.
* **Jam Posting**: Menentukan window waktu publikasi (misal: *08.00 - 18.00 WIB*).
* **Status Publikasi**: Penanda apakah seluruh konten di tanggal tersebut sudah *All Done* atau masih dalam proses.

---

### 3. Sheet `CONTENT MEDSOS (FB,X,YT,TT)` ➔ Halaman Posting & Akun Medsos
> **Fungsi Utama**: Sumber data untuk **Jadwal & Distribusi per Platform Sosial Media**.

* **Tiktok & YouTube Shorts**: Rekapitulasi video pendek yang siap diunggah ke TikTok & YT Shorts.
* **Facebook & Twitter (X)**: Rekapitulasi feed, artikel, dan promo tayangan untuk FB & X.
* **PIC & Status Posting**: Penanggung jawab pengunggahan per akun platform.

---

### 4. Sheet `ADMIN + LINK TEMPLATE` ➔ Halaman Master Data & Management Tim
> **Fungsi Utama**: Sumber acuan untuk **Tim Penanggung Jawab & Template**.

* **Daftar Admin & PIC**: Pemetaan user ke tugas masing-masing.
* **Link Template & Asset**: Link acuan ke template Canva, Drive asset, dan panduan desain TVRI Jatim.
