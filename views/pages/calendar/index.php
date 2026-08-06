
<?php
$programs = $programs ?? [];
$kategoris = $kategoris ?? [];
$jenisKonten = $jenisKonten ?? [];
$users = $users ?? [];
?>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/main.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/themes/airbnb.css">
<style>
    #calendar { min-height: 600px; }


    @keyframes calendarPageIn {
        from {
            opacity: 0;
            transform: translateY(18px) scale(0.985);
        }
        to {
            opacity: 1;
            transform: none;
        }
    }
    @keyframes calendarHeaderIn {
        from { opacity: 0; transform: translateY(8px); }
        to { opacity: 1; transform: none; }
    }
    @keyframes calendarBodyIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }
    .calendar-page-card {
        animation: calendarPageIn 0.55s cubic-bezier(0.22, 0.61, 0.36, 1) both;
    }
    .calendar-page-card .card-header {
        animation: calendarHeaderIn 0.45s cubic-bezier(0.22, 0.61, 0.36, 1) 0.15s both;
    }
    .calendar-page-card .card-body {
        animation: calendarBodyIn 0.6s ease-out 0.3s both;
    }
    .fc-toolbar-title { font-size: 1.25rem !important; font-weight: 700 !important; }
    .fc-button-primary {
        background: var(--tvri-blue) !important;
        border-color: var(--tvri-blue) !important;
        font-family: var(--font-body) !important;
        font-size: 0.8125rem !important;
        padding: 6px 16px !important;
        border-radius: 8px !important;
    }
    .fc-button-primary:hover { background: var(--tvri-blue-600) !important; }
    .fc-button-primary:disabled { opacity: 0.5 !important; }

    .fc .fc-button:focus,
    .fc .fc-button:active:focus,
    .fc .fc-button:focus-visible {
        outline: none !important;
        box-shadow: none !important;
    }
    .fc .fc-button-group > .fc-button { margin: 0 2px !important; }


    .fc .fc-icon-custom-prev,
    .fc .fc-icon-custom-next {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 1.25em;
        height: 1.25em;
        font-size: 1.2em;
        line-height: 1;
    }
    .fc .fc-icon-custom-prev::before,
    .fc .fc-icon-custom-next::before {
        content: '';
        width: 1em;
        height: 1em;
        background-repeat: no-repeat;
        background-position: center;
        background-size: contain;
    }
    .fc .fc-icon-custom-prev::before {
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23ffffff' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='15 18 9 12 15 6'%3E%3C/polyline%3E%3C/svg%3E");
    }
    .fc .fc-icon-custom-next::before {
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23ffffff' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='9 18 15 12 9 6'%3E%3C/polyline%3E%3C/svg%3E");
    }
    .fc-daygrid-day-number { font-size: 0.875rem; font-weight: 500; color: var(--gray-600); }
    .fc-col-header-cell-cushion { font-weight: 600; color: var(--gray-500); text-decoration: none; }
    .fc-event {
        border-radius: 6px !important;
        padding: 2px 8px !important;
        font-size: 0.75rem !important;
        border: none !important;
        cursor: pointer !important;
        transition: all 0.15s ease;
    }
    .fc-event:hover {
        opacity: 0.9;
        filter: brightness(1.1);
        transform: scale(1.02);
    }
    .fc-day-today { background: var(--tvri-blue-50) !important; }
    [data-theme="dark"] .fc-day-today { background: rgba(0, 51, 153, 0.2) !important; }
    [data-theme="dark"] .fc { background: var(--dark-surface-2); }
    [data-theme="dark"] .fc td, [data-theme="dark"] .fc th { border-color: var(--dark-border); }
    [data-theme="dark"] .fc-daygrid-day-number, [data-theme="dark"] .fc-col-header-cell-cushion { color: var(--dark-text); }
    .fc-theme-standard td, .fc-theme-standard th { border-color: var(--gray-200); }

    .calendar-legend {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
        padding: 12px 16px;
    }
    .legend-item {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 0.75rem;
        color: var(--text-secondary);
    }
    .legend-dot {
        width: 10px;
        height: 10px;
        border-radius: 3px;
        flex-shrink: 0;
    }
    .fc-event-priority-urgent { box-shadow: 0 0 0 2px #ff1744 inset !important; }
    .fc-event-priority-high { box-shadow: 0 0 0 2px #ff9100 inset !important; }

    .platform-icon-mini {
        width: 16px;
        height: 16px;
        border-radius: 3px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin-right: 4px;
        flex-shrink: 0;
    }
    .platform-icon-mini img {
        width: 12px;
        height: 12px;
        object-fit: contain;
    }


    .fc-event-popover {
        position: absolute;
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-xl);
        padding: var(--space-4);
        width: 300px;
        z-index: 1000;
        display: none;
    }
    .fc-event-popover.show { display: block; }


    .day-memorial-note {
        font-size: 8.5px;
        font-weight: 600;
        line-height: 1.25;
        color: #b91c1c;
        background: #fef2f2;
        border-left: 2px solid #dc2626;
        border-radius: 3px;
        padding: 1px 4px;
        margin: 1px 3px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: calc(100% - 6px);
        cursor: pointer;
    }
    .day-memorial-note:hover {
        opacity: 0.85;
        border-left-color: #991b1b;
        box-shadow: 0 1px 3px rgba(0,0,0,0.12);
    }

    .day-memorial-notes {
        display: block;
    }




    .fc .fc-daygrid-body .fc-scrollgrid-sync-table {
        display: flex;
        flex-direction: column;
        height: 100%;
    }
    .fc .fc-daygrid-body .fc-scrollgrid-sync-table colgroup {
        display: none;
    }
    .fc .fc-daygrid-body tbody {
        display: flex;
        flex-direction: column;
        flex: 1 1 0%;
    }
    .fc .fc-daygrid-body tbody tr[role="row"] {
        display: flex;
        flex: 1 1 0%;
        min-height: 0;
    }
    .fc .fc-daygrid-body tbody td.fc-daygrid-day {
        display: flex;
        flex-direction: column;
        flex: 1 1 0%;
        min-width: 0;
        min-height: 0;
    }
    .fc .fc-daygrid-day-frame {
        height: 100% !important;
        overflow-y: hidden !important;
        scrollbar-width: thin;
    }

    .fc .fc-daygrid-day:hover .fc-daygrid-day-frame,
    .fc .fc-daygrid-day-frame:hover {
        overflow-y: auto !important;
    }
    .fc .fc-daygrid-day-frame::-webkit-scrollbar {
        width: 3px;
    }
    .fc .fc-daygrid-day-frame::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 3px;
    }

    .fc .fc-daygrid-day-top {
        position: sticky !important;
        top: 0;
        z-index: 3;
        background: var(--surface, #ffffff);
        padding-bottom: 2px;
    }
    [data-theme="dark"] .day-memorial-note {
        background: rgba(220, 38, 38, 0.15);
        color: #fca5a5;
        border-left-color: #ef4444;
    }

    body.modal-open {
        overflow: hidden;
    }


    .modal-close-btn {
        width: 34px;
        height: 34px;
        flex-shrink: 0;
        border: 1px solid #e2e8f0;
        border-radius: 50%;
        background: #f8fafc;
        color: #475569;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 15px;
        padding: 0;
        cursor: pointer;
        transition: background .15s ease, color .15s ease, border-color .15s ease;
    }
    .modal-close-btn:hover {
        background: #fee2e2;
        color: #b91c1c;
        border-color: #fecaca;
    }


    #noteDetailModal.show .modal-dialog {
        animation: noteDetailIn 0.38s cubic-bezier(0.34, 1.3, 0.5, 1) both;
    }
    @keyframes noteDetailIn {
        from {
            opacity: 0;
            transform: translateY(26px) scale(0.9);
        }
        70% {
            transform: translateY(-4px) scale(1.01);
        }
        to {
            opacity: 1;
            transform: none;
        }
    }
    #noteDetailModal.closing .modal-dialog {
        animation: noteDetailOut 0.2s ease-in both;
    }
    @keyframes noteDetailOut {
        from {
            opacity: 1;
            transform: none;
        }
        to {
            opacity: 0;
            transform: translateY(12px) scale(0.96);
        }
    }



    #noteModal.show .modal-dialog {
        animation: noteModalIn 0.38s cubic-bezier(0.34, 1.3, 0.5, 1) backwards;
    }
    @keyframes noteModalIn {
        from {
            opacity: 0;
            transform: translateY(26px) scale(0.9);
        }
        70% {
            transform: translateY(-4px) scale(1.01);
        }
        to {
            opacity: 1;
            transform: none;
        }
    }
    #noteModal.closing .modal-dialog {
        animation: noteModalOut 0.2s ease-in both;
    }
    @keyframes noteModalOut {
        from {
            opacity: 1;
            transform: none;
        }
        to {
            opacity: 0;
            transform: translateY(12px) scale(0.96);
        }
    }


    @keyframes calendarViewSlideLeft {
        from {
            opacity: 0;
            transform: translateX(28px);
        }
        to {
            opacity: 1;
            transform: none;
        }
    }
    @keyframes calendarViewSlideRight {
        from {
            opacity: 0;
            transform: translateX(-28px);
        }
        to {
            opacity: 1;
            transform: none;
        }
    }
    .fc-view-slide-left {
        animation: calendarViewSlideLeft 0.32s cubic-bezier(0.22, 0.61, 0.36, 1);
    }
    .fc-view-slide-right {
        animation: calendarViewSlideRight 0.32s cubic-bezier(0.22, 0.61, 0.36, 1);
    }


    #noteModal .modal-dialog {
        transition: transform 0.25s ease;
    }
    #noteModal.modal-date-open .modal-dialog {
        transform: translateY(-13vh);
    }


    .flatpickr-calendar {
        border-radius: 16px !important;
        border: 1px solid #d0d9e8 !important;
        box-shadow: 0 18px 44px -10px rgba(15, 23, 42, 0.3), 0 6px 18px -4px rgba(15, 23, 42, 0.12) !important;
        margin-top: 6px;
        overflow: hidden;
        z-index: 99999 !important;
    }
    .flatpickr-months {
        border-radius: 16px 16px 0 0 !important;
        overflow: hidden;
        position: relative;
        height: auto !important;
        min-height: 36px;
    }
    .flatpickr-current-month .flatpickr-monthDropdown-months {
        display: none !important;
    }
    .flatpickr-current-month {
        display: flex !important;
        flex-direction: row !important;
        align-items: center !important;
        justify-content: center !important;
        height: auto !important;
        padding: 6px 0 4px 0 !important;
        gap: 2px;
        white-space: nowrap;
    }
    .flatpickr-current-month .cur-month {
        display: inline !important;
        font-family: inherit;
        font-weight: 700;
        font-size: 15px;
        line-height: 1.2 !important;
        color: #1e293b;
        margin: 0;
        padding: 2px 6px;
        border-radius: 6px;
        cursor: default !important;
    }
    .flatpickr-current-month .numInputWrapper {
        display: inline-block !important;
        width: auto !important;
        height: auto !important;
        line-height: 1.2 !important;
        margin: 0;
        padding: 0;
        border-radius: 6px;
    }
    .flatpickr-current-month .numInputWrapper input.cur-year {
        font-family: inherit;
        font-weight: 700;
        font-size: 15px;
        line-height: 1.2 !important;
        color: #1e293b;
        cursor: default !important;
        pointer-events: none !important;
        padding: 2px 6px;
        margin: 0;
        width: 52px;
        background: transparent !important;
        border: none !important;
        box-shadow: none !important;
        outline: none !important;
        border-radius: 6px;
    }
    .flatpickr-months .flatpickr-month {
        height: auto !important;
        overflow: visible !important;
        padding: 0 !important;
    }
    .flatpickr-prev-month, .flatpickr-next-month {
        top: 50% !important;
        transform: translateY(-50%) !important;
        padding: 6px 10px !important;
        height: auto !important;
        border-radius: 8px;
    }
    .flatpickr-current-month .numInputWrapper span.arrowUp,
    .flatpickr-current-month .numInputWrapper span.arrowDown {
        display: none !important;
    }
    .flatpickr-day.selected,
    .flatpickr-day.startRange,
    .flatpickr-day.endRange,
    .flatpickr-day.selected.inRange,
    .flatpickr-day.startRange.inRange,
    .flatpickr-day.endRange.inRange,
    .flatpickr-day.week.selected {
        border-radius: 8px !important;
        background: #003399 !important;
        border-color: #003399 !important;
    }
    .flatpickr-day.today {
        border-color: #003399 !important;
    }
    .flatpickr-day.today:hover,
    .flatpickr-day.today:focus {
        background: #e0e7ff !important;
        border-color: #003399 !important;
    }
    .flatpickr-day:hover {
        border-radius: 8px !important;
        background: #f1f5f9 !important;
    }
    .flatpickr-day.inRange {
        background: #e0e7ff !important;
        border-color: #c7d2fe !important;
        border-radius: 0 !important;
    }
    .flatpickr-months .flatpickr-prev-month:hover svg,
    .flatpickr-months .flatpickr-next-month:hover svg {
        fill: #003399 !important;
    }

    .input-group-date {
        display: flex;
        align-items: stretch;
        width: 100%;
        position: relative;
    }
    .input-group-date.calendar-open {
        overflow: visible !important;
    }
    .input-group-date .form-control,
    .input-group-date input.flatpickr-input,
    .input-group-date input.flatpickr-alt-input {
        border-radius: 12px 0 0 12px !important;
        border-right: none !important;
        flex: 1 !important;
        min-width: 0 !important;
        width: auto !important;
        max-width: none !important;
    }
    .input-group-date input.flatpickr-input:not([readonly]),
    .input-group-date input.flatpickr-alt-input:not([readonly]) {
        cursor: text !important;
    }
    .input-group-date .btn-date-icon {
        border-radius: 0 12px 12px 0 !important;
        border: 1px solid var(--border, #cbd5e1);
        border-left: none;
        background: var(--surface, #ffffff);
        cursor: pointer;
        padding: 0 10px;
        color: #003399;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s ease;
        flex-shrink: 0;
    }
    .input-group-date .btn-date-icon:hover {
        background: #f1f5f9;
        color: #001a4d;
    }
    .input-group-date.input-focused {
        box-shadow: 0 0 0 3px rgba(0, 51, 153, 0.15);
        border-radius: 12px;
    }
    .input-group-date.input-focused .btn-date-icon {
        border-color: #003399 !important;
    }
    .input-group-date input.form-control:focus {
        box-shadow: none !important;
    }
    [data-theme="dark"] .input-group-date .btn-date-icon {
        background: #1e293b;
        border-color: #334155;
        color: #60a5fa;
    }
    [data-theme="dark"] .input-group-date .btn-date-icon:hover {
        background: #334155;
        color: #93c5fd;
    }
    [data-theme="dark"] .flatpickr-calendar {
        background: #1e293b !important;
        border-color: #334155 !important;
    }
    [data-theme="dark"] .flatpickr-months .flatpickr-month {
        color: #e2e8f0 !important;
    }
</style>

<div class="row">

    <div class="col-12">
        <div class="card calendar-page-card">
            <div class="card-header">
                <h5><i class="bi bi-calendar3" style="color: var(--tvri-blue);"></i> Kalender Konten</h5>
                <div class="d-flex gap-sm">
                    <button type="button" class="btn btn-sm btn-primary" onclick="openNoteModal()">
                        <i class="bi bi-plus"></i> Buat Noted
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div id="calendar"></div>
            </div>
        </div>
    </div>
</div>


<div id="eventPopover" class="fc-event-popover" style="display:none;">
    <div style="font-weight: var(--font-weight-semibold); font-size: var(--text-sm); margin-bottom: var(--space-2);" id="popoverTitle"></div>
    <div style="font-size: var(--text-xs); color: var(--text-secondary); display: flex; flex-direction: column; gap: 4px;">
        <div id="popoverStatus"></div>
        <div id="popoverPlatform"></div>
        <div id="popoverPIC"></div>
        <div id="popoverDate"></div>
    </div>
    <div style="margin-top: var(--space-3);">
        <a id="popoverLink" class="btn btn-sm btn-primary w-100" style="font-size: var(--text-xs);">
            <i class="bi bi-eye"></i> Lihat Detail
        </a>
    </div>
</div>


<div class="modal" id="noteModal" tabindex="-1" role="dialog" aria-hidden="true" onclick="if (event.target === this) closeNoteModal();">
    <div class="modal-dialog modal-dialog-centered" style="max-width:680px; width:90%; margin:1.75rem auto;">
        <div class="modal-content border-0 shadow-lg" style="border-radius:16px; padding:16px;">
            <div class="modal-header border-0 pb-2" style="display:flex; justify-content:space-between; align-items:center;">
                <h5 style="font-weight:700; color:#1e293b; margin:0;"><i class="bi bi-sticky me-2" style="color:#003399;"></i> Tambah Noted Kalender</h5>
                <button type="button" class="modal-close-btn" onclick="closeNoteModal()" aria-label="Tutup"><i class="bi bi-x-lg"></i></button>
            </div>
            <div class="modal-body pt-0" style="max-height:68vh; overflow-y:auto;">
                <form id="noteForm" method="POST" action="<?= BASE_URL ?>/calendar/note/store">
                    <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= Session::csrfToken() ?>">
                    <input type="hidden" name="id" id="noteIdInput" value="">

                    <div class="form-group mb-3">
                        <label class="form-label font-medium">Nama Noted / Hari Peringatan <span class="text-danger">*</span></label>
                        <textarea name="note" id="noteInput" class="form-control" rows="2" placeholder="Contoh: Hari Ulang Tahun TVRI ke-60" required minlength="3" maxlength="255" style="resize: vertical; min-height: 42px;"></textarea>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label font-medium">Tanggal <span class="text-danger">*</span></label>
                                <div class="input-group-date">
                                    <input type="text" name="note_date" id="noteDateInput" class="form-control" placeholder="dd/mm/yyyy" autocomplete="off" required>
                                    <button type="button" class="btn-date-icon" id="btnNoteDateIcon" tabindex="-1" title="Buka Kalender">
                                        <i class="bi bi-calendar3"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label font-medium">Warna Tampilan</label>
                                <select name="color" id="noteColorSelect" class="form-control">
                                    <option value="#dc3545">Merah</option>
                                    <option value="#0d6efd">Biru</option>
                                    <option value="#198754">Hijau</option>
                                    <option value="#fd7e14">Oranye</option>
                                    <option value="#6f42c1">Ungu</option>
                                    <option value="#0dcaf0">Cyan</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer border-0 pt-2">
                <button type="button" class="btn btn-ghost" onclick="closeNoteModal()">Batal</button>
                <button type="submit" form="noteForm" class="btn btn-primary"><i class="bi bi-check-circle me-1"></i> Simpan Noted</button>
            </div>
        </div>
    </div>
</div>


<div class="modal" id="noteDetailModal" tabindex="-1" role="dialog" aria-hidden="true" onclick="if (event.target === this) closeNoteDetailModal();">
    <div class="modal-dialog modal-dialog-centered" style="max-width:520px; width:90%; margin:1.75rem auto;">
        <div class="modal-content border-0 shadow-lg" style="border-radius:16px; padding:16px;">
            <div class="modal-header border-0 pb-2" style="display:flex; justify-content:space-between; align-items:center;">
                <h5 style="font-weight:700; color:#1e293b; margin:0;"><i class="bi bi-sticky me-2" style="color:#003399;"></i> Detail Noted</h5>
                <button type="button" class="modal-close-btn" onclick="closeNoteDetailModal()" aria-label="Tutup"><i class="bi bi-x-lg"></i></button>
            </div>
            <div class="modal-body pt-0" style="max-height:60vh; overflow-y:auto;" id="noteDetailBody"></div>
            <div class="modal-footer border-0 pt-2" id="noteDetailFooter"></div>
        </div>
    </div>
</div>


<div class="modal" id="deleteNoteConfirmModal" tabindex="-1" role="dialog" aria-hidden="true" onclick="if (event.target === this) closeDeleteNoteConfirm();">
    <div class="modal-dialog modal-dialog-centered" style="max-width:420px; width:90%; margin:1.75rem auto;">
        <div class="modal-content border-0 shadow-lg" style="border-radius:16px; padding:24px; text-align:center;">
            <div style="display:flex; justify-content:flex-end;">
                <button type="button" class="modal-close-btn" onclick="closeDeleteNoteConfirm()" aria-label="Tutup"><i class="bi bi-x-lg"></i></button>
            </div>
            <div style="margin-bottom:14px;">
                <div style="width:60px; height:60px; background:#fee2e2; color:#dc2626; border-radius:50%; display:inline-flex; align-items:center; justify-content:center;">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="3 6 5 6 21 6"></polyline>
                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                        <line x1="10" y1="11" x2="10" y2="17"></line>
                        <line x1="14" y1="11" x2="14" y2="17"></line>
                    </svg>
                </div>
            </div>
            <h5 style="font-weight:700; color:#1e293b; font-size:18px; margin-bottom:8px;">Konfirmasi Hapus</h5>
            <p id="deleteNoteConfirmMessage" style="font-size:13.5px; color:#64748b; margin-bottom:24px; line-height:1.5;">Yakin ingin menghapus noted ini?</p>
            <div class="d-flex gap-2 justify-content-center">
                <button type="button" class="btn" onclick="closeDeleteNoteConfirm()" style="flex:1; height:42px; font-weight:600; font-size:14px; background:#f1f5f9; color:#475569; border:none; border-radius:10px;">Batal</button>
                <button type="button" class="btn" onclick="submitDeleteNote()" style="flex:1; height:42px; font-weight:600; font-size:14px; background:#dc2626; color:#ffffff; border:none; border-radius:10px;"><i class="bi bi-trash me-1"></i> Hapus</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/id.js"></script>
<script>

var COMMEMORATIONS = <?= json_encode($commemorations ?? [], JSON_UNESCAPED_UNICODE) ?>;
var COMMEMORATION_COLORS = <?= json_encode($commemorationColors ?? [], JSON_UNESCAPED_UNICODE) ?>;
var CAN_MANAGE_BUILTIN = <?= !empty($canManageBuiltin) ? 'true' : 'false' ?>;
var NOTES_BY_DATE = <?= json_encode($notesByDate ?? [], JSON_UNESCAPED_UNICODE) ?>;

function memorialNotesFor(dateObj) {
    var md = String(dateObj.getMonth() + 1).padStart(2, '0') + '-' + String(dateObj.getDate()).padStart(2, '0');
    var val = COMMEMORATIONS[md];
    if (!val) return null;
    return Array.isArray(val) ? val : [val];
}

let calendar;
let lastViewStart = null;
var noteDatePicker = null;

function noteTintBg(color) {
    var m = /^#([0-9a-f]{3}|[0-9a-f]{6})$/i.exec(String(color || '').trim());
    if (!m) return '';
    var h = m[1];
    if (h.length === 3) {
        h = h.charAt(0) + h.charAt(0) + h.charAt(1) + h.charAt(1) + h.charAt(2) + h.charAt(2);
    }
    var r = parseInt(h.substring(0, 2), 16);
    var g = parseInt(h.substring(2, 4), 16);
    var b = parseInt(h.substring(4, 6), 16);
    return 'rgba(' + r + ',' + g + ',' + b + ',0.14)';
}

document.addEventListener('DOMContentLoaded', function() {
    const calendarEl = document.getElementById('calendar');

    calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        customButtons: {
            prevBtn: {
                icon: 'custom-prev',
                hint: 'Bulan sebelumnya',
                click: function() { calendar.prev(); }
            },
            nextBtn: {
                icon: 'custom-next',
                hint: 'Bulan berikutnya',
                click: function() { calendar.next(); }
            },
        },
        headerToolbar: {
            left: 'prevBtn,nextBtn today',
            center: 'title',
            right: ''
        },
        locale: 'id',
        firstDay: 1,
        height: 650,
        editable: true,
        droppable: true,
        eventStartEditable: true,
        eventDurationEditable: false,
        snapDuration: '01:00:00',

        events: function(fetchInfo, successCallback, failureCallback) {
            const params = new URLSearchParams({
                start: fetchInfo.startStr,
                end: fetchInfo.endStr,
            });

            fetch(BASE_URL + '/calendar/data?' + params.toString())
                .then(r => r.json())
                .then(data => {
                    successCallback(data);
                })
                .catch(failureCallback);
        },

        eventDidMount: function(info) {
            const props = info.event.extendedProps;
            if (props.platform_icon) {
                const el = info.el.querySelector('.fc-event-title');
                if (el) {
                    const platformHtml = props.platform_icon + ' ';
                    el.innerHTML = platformHtml + info.event.title;
                }
            }

            if (props.priority === 'urgent') {
                info.el.style.boxShadow = '0 0 0 2px #ff1744 inset';
            } else if (props.priority === 'high') {
                info.el.style.boxShadow = '0 0 0 2px #ff9100 inset';
            }
        },

        dayCellDidMount: function(info) {
            var mdKey = String(info.date.getMonth() + 1).padStart(2, '0') + '-' + String(info.date.getDate()).padStart(2, '0');
            var notes = NOTES_BY_DATE[mdKey];
            if (!notes || !notes.length) return;
            var frame = info.el.querySelector('.fc-daygrid-day-frame');
            var eventsContainer = frame ? frame.querySelector('.fc-daygrid-day-events') : null;
            var fullDate = info.date.getFullYear() + '-' + mdKey;

            var container = document.createElement('div');
            container.className = 'day-memorial-notes';
            notes.forEach(function(note) {
                var el = document.createElement('div');
                el.className = 'day-memorial-note';
                el.title = (note.builtin ? 'Hari Peringatan' : 'Noted') + ': ' + note.text;
                el.textContent = note.text;
                if (note.color) {
                    el.style.color = note.color;
                    el.style.borderLeftColor = note.color;
                    el.style.background = noteTintBg(note.color);
                }
                el.dataset.noteId = note.id || '';
                el.dataset.noteFullDate = fullDate;
                el.dataset.noteBuiltin = note.builtin ? '1' : '0';
                el.addEventListener('click', function(e) {
                    e.stopPropagation();
                    openNoteDetail(el);
                });
                container.appendChild(el);
            });
            if (eventsContainer) {
                frame.insertBefore(container, eventsContainer);
            } else if (frame) {
                frame.appendChild(container);
            } else {
                info.el.appendChild(container);
            }
        },

        eventDrop: function(info) {
            const event = info.event;
            const newStart = event.start.toISOString();

            if (!confirm('Pindahkan "' + event.title + '" ke tanggal ' + newStart.split('T')[0] + '?')) {
                info.revert();
                return;
            }

            $.post(BASE_URL + '/calendar/drag-drop', {
                id: event.id,
                start: newStart,
                _csrf_token: CSRF_TOKEN
            }, function(res) {
                if (res.success) {
                    showToast('success', 'Berhasil', 'Jadwal berhasil diubah ke ' + newStart.split('T')[0]);
                    calendar.refetchEvents();
                } else {
                    info.revert();
                    showToast('error', 'Gagal', res.message);
                }
            }).fail(function() {
                info.revert();
                showToast('error', 'Gagal', 'Terjadi kesalahan server');
            });
        },

        eventClick: function(info) {
            window.location.href = BASE_URL + '/planning/' + info.event.id;
        },

        loading: function(isLoading) {
            if (!isLoading) {
                document.getElementById('calendar').style.opacity = '1';
            }
        },

        noEventsText: 'Tidak ada konten pada periode ini',
        moreLinkText: function(num) { return '+ ' + num + ' lagi'; },

        dayMaxEvents: 3,
        dayMaxEventRows: true,

        datesSet: function(arg) {
            syncDayRowHeights();
            if (lastViewStart === null) {
                lastViewStart = arg.startStr;
                return;
            }
            var harness = document.querySelector('#calendar .fc-view-harness');
            if (harness) {
                var cls = (arg.startStr > lastViewStart) ? 'fc-view-slide-left' : 'fc-view-slide-right';
                harness.classList.remove('fc-view-slide-left', 'fc-view-slide-right');
                void harness.offsetWidth;
                harness.classList.add(cls);
                setTimeout(function() {
                    harness.classList.remove(cls);
                }, 400);
            }
            lastViewStart = arg.startStr;
        },
    });

    calendar.render();
    syncDayRowHeights();
});

function syncDayRowHeights() {
    var rows = document.querySelectorAll('.fc-daygrid-row');
    if (!rows.length) return;
    var calEl = document.getElementById('calendar');
    if (!calEl) return;
    var availH = calEl.clientHeight;
    if (availH <= 0) return;
    var headerEl = document.querySelector('.fc-header-toolbar');
    var headerH = headerEl ? headerEl.offsetHeight : 0;
    var bodyH = availH - headerH - 4;
    if (bodyH <= 0) return;
    var rowH = Math.floor(bodyH / rows.length);
    if (rowH < 60) rowH = 60;
    rows.forEach(function(r) {
        r.style.height = rowH + 'px';
    });
}
window.addEventListener('resize', syncDayRowHeights);

const observer = new MutationObserver(function() {
    if (calendar) calendar.refetchEvents();
});
observer.observe(document.documentElement, { attributes: true, attributeFilter: ['data-theme'] });

var currentNoteDetail = null;

function syncModalLock() {
    var anyOpen = document.querySelector('.modal.show');
    if (anyOpen) document.body.classList.add('modal-open');
    else document.body.classList.remove('modal-open');
}

function resetNoteForm() {
    var form = document.getElementById('noteForm');
    if (!form) return;
    form.action = BASE_URL + '/calendar/note/store';
    var idInput = document.getElementById('noteIdInput');
    if (idInput) idInput.value = '';
    var input = document.getElementById('noteInput');
    if (input) input.value = '';
    if (noteDatePicker) noteDatePicker.setDate(new Date(), true);
    var colorSelect = document.getElementById('noteColorSelect');
    if (colorSelect) {
        colorSelect.value = '#dc3545';
        syncCustomDropdown('noteColorSelect');
    }
    var title = document.querySelector('#noteModal .modal-header h5');
    if (title) title.innerHTML = '<i class="bi bi-sticky me-2" style="color:#003399;"></i> Tambah Noted Kalender';
    var submitBtn = document.querySelector('#noteModal .modal-footer button[type="submit"]');
    if (submitBtn) submitBtn.innerHTML = '<i class="bi bi-check-circle me-1"></i> Simpan Noted';
}

function openNoteModal() {
    resetNoteForm();
    var modal = document.getElementById('noteModal');
    if (!modal) return;
    modal.classList.remove('closing');
    modal.classList.add('show');
    modal.style.display = 'block';
    document.body.classList.add('modal-open');
    var form = document.getElementById('noteForm');
    var input = form ? form.querySelector('[name="note"]') : null;
    if (input) setTimeout(function() { input.focus(); }, 150);
}

function closeNoteModal() {
    var modal = document.getElementById('noteModal');
    if (!modal || modal.classList.contains('closing')) return;
    modal.classList.add('closing');
    modal.classList.remove('modal-date-open');
    setTimeout(function() {
        modal.classList.remove('show', 'closing');
        modal.style.display = 'none';
        syncModalLock();
    }, 250);
}

function openNoteDetail(el) {
    currentNoteDetail = {
        id: el.dataset.noteId || null,
        text: el.textContent.trim(),
        date: el.dataset.noteFullDate || '',
        color: el.style.color || '#b91c1c',
        builtin: el.dataset.noteBuiltin === '1'
    };
    var body = document.getElementById('noteDetailBody');
    var footer = document.getElementById('noteDetailFooter');
    if (!body || !footer) return;

    var dateLabel = currentNoteDetail.date;
    if (dateLabel) {
        var p = dateLabel.split('-');
        if (p.length === 3) dateLabel = p[2] + '/' + p[1] + '/' + p[0];
    }

    var badge = currentNoteDetail.builtin
        ? '<span style="font-size:11px; font-weight:600; color:#64748b; background:#f1f5f9; padding:2px 10px; border-radius:999px;">Hari Peringatan Bawaan</span>'
        : '<span style="font-size:11px; font-weight:600; color:#003399; background:#e8eefc; padding:2px 10px; border-radius:999px;">Noted Custom</span>';

    body.innerHTML =
        '<div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:12px; padding:16px;">' +
            '<div style="font-size:15px; font-weight:700; color:#1e293b; margin-bottom:8px;">' + escHtml(currentNoteDetail.text) + '</div>' +
            '<div style="font-size:13px; color:#475569; margin-bottom:6px;"><i class="bi bi-calendar3 me-2"></i>' + escHtml(dateLabel) + '</div>' +
        '</div>' +
        '<div style="text-align:center; margin-top:12px;">' + badge + '</div>';

    footer.innerHTML = '';
    if (!currentNoteDetail.builtin || CAN_MANAGE_BUILTIN) {
        var editBtn = document.createElement('button');
        editBtn.type = 'button';
        editBtn.className = 'btn btn-primary';
        editBtn.innerHTML = '<i class="bi bi-pencil me-1"></i> Edit';
        editBtn.addEventListener('click', function() { openEditNoteModal(); });
        footer.appendChild(editBtn);

        var delBtn = document.createElement('button');
        delBtn.type = 'button';
        delBtn.className = 'btn btn-danger';
        delBtn.style.background = '#dc2626';
        delBtn.style.color = '#fff';
        delBtn.innerHTML = '<i class="bi bi-trash me-1"></i> Hapus';
        delBtn.addEventListener('click', function() { deleteNoteDetail(); });
        footer.appendChild(delBtn);
    } else {
        var info = document.createElement('span');
        info.style.cssText = 'font-size:12px; color:#94a3b8;';
        info.textContent = 'Noted hari peringatan hanya dapat diubah/dihapus oleh Admin.';
        footer.appendChild(info);
    }

    var modal = document.getElementById('noteDetailModal');
    modal.classList.remove('closing');
    modal.classList.add('show');
    modal.style.display = 'block';
    document.body.classList.add('modal-open');
}

function closeNoteDetailModal() {
    var modal = document.getElementById('noteDetailModal');
    if (!modal || modal.classList.contains('closing')) return;
    modal.classList.add('closing');
    setTimeout(function() {
        modal.classList.remove('show', 'closing');
        modal.style.display = 'none';
        syncModalLock();
    }, 250);
}

function openEditNoteModal() {
    if (!currentNoteDetail || !currentNoteDetail.id) return;
    var form = document.getElementById('noteForm');
    if (!form) return;
    form.action = BASE_URL + '/calendar/note/update';
    var idInput = document.getElementById('noteIdInput');
    if (idInput) idInput.value = currentNoteDetail.id;
    var input = document.getElementById('noteInput');
    if (input) input.value = currentNoteDetail.text;
    if (noteDatePicker && currentNoteDetail.date) noteDatePicker.setDate(currentNoteDetail.date, true);
    var colorSelect = document.getElementById('noteColorSelect');
    if (colorSelect && currentNoteDetail.color) {
        colorSelect.value = currentNoteDetail.color;
        syncCustomDropdown('noteColorSelect');
    }
    var title = document.querySelector('#noteModal .modal-header h5');
    if (title) title.innerHTML = '<i class="bi bi-sticky me-2" style="color:#003399;"></i> Edit Noted Kalender';
    var submitBtn = document.querySelector('#noteModal .modal-footer button[type="submit"]');
    if (submitBtn) submitBtn.innerHTML = '<i class="bi bi-check-circle me-1"></i> Simpan Perubahan';
    var modal = document.getElementById('noteModal');
    modal.classList.remove('closing');
    modal.classList.add('show');
    modal.style.display = 'block';
    document.body.classList.add('modal-open');
    closeNoteDetailModal();
}

function deleteNoteDetail() {
    if (!currentNoteDetail || !currentNoteDetail.id) return;
    var msg = document.getElementById('deleteNoteConfirmMessage');
    if (msg) msg.textContent = 'Yakin ingin menghapus noted "' + currentNoteDetail.text + '"?\nTindakan ini tidak dapat dibatalkan.';
    var modal = document.getElementById('deleteNoteConfirmModal');
    if (!modal) return;
    modal.classList.remove('closing');
    modal.classList.add('show');
    modal.style.display = 'block';
    document.body.classList.add('modal-open');
}

function submitDeleteNote() {
    if (!currentNoteDetail || !currentNoteDetail.id) return;
    closeDeleteNoteConfirm();
    var f = document.createElement('form');
    f.method = 'POST';
    f.action = BASE_URL + '/calendar/note/delete';
    var csrf = document.createElement('input');
    csrf.type = 'hidden';
    csrf.name = '_csrf_token';
    csrf.value = CSRF_TOKEN;
    var idInput = document.createElement('input');
    idInput.type = 'hidden';
    idInput.name = 'id';
    idInput.value = currentNoteDetail.id;
    f.appendChild(csrf);
    f.appendChild(idInput);
    document.body.appendChild(f);
    f.submit();
}

function closeDeleteNoteConfirm() {
    var modal = document.getElementById('deleteNoteConfirmModal');
    if (!modal || modal.classList.contains('closing')) return;
    modal.classList.add('closing');
    setTimeout(function() {
        modal.classList.remove('show', 'closing');
        modal.style.display = 'none';
        syncModalLock();
    }, 250);
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeNoteModal();
        closeNoteDetailModal();
        closeDeleteNoteConfirm();
    }
});

function initNoteDatePicker() {
    if (typeof flatpickr === 'undefined') return;
    var input = document.getElementById('noteDateInput');
    if (!input || input._flatpickr) return;

    function handleOpen(selectedDates, dateStr, instance) {
        var container = instance.element.closest('.input-group-date');
        if (container) container.classList.add('calendar-open');
        var modalBody = instance.element.closest('.modal-body');
        if (modalBody) modalBody.style.overflowY = 'visible';
    }
    function handleClose(selectedDates, dateStr, instance) {
        var container = instance.element.closest('.input-group-date');
        if (container) container.classList.remove('calendar-open');
        var modalBody = instance.element.closest('.modal-body');
        if (modalBody) modalBody.style.overflowY = '';
        var modal = document.getElementById('noteModal');
        if (modal) modal.classList.remove('modal-date-open');
    }

    var fp = flatpickr(input, {
        locale: 'id',
        dateFormat: 'Y-m-d',
        altFormat: 'd/m/Y',
        altInput: true,
        altInputClass: 'form-control',
        allowInput: true,
        disableMobile: true,
        clickOpens: false,
        animate: true,
        monthSelectorType: 'static',
        static: false,
        position: 'below',
        defaultDate: new Date(),
        onOpen: handleOpen,
        onClose: handleClose
    });
    noteDatePicker = fp;
    if (fp.altInput) {
        fp.altInput.style.width = '';
        fp.altInput.style.flex = '1';
        fp.altInput.style.minWidth = '0';
    }

    function setupFocusGroup(altInput) {
        if (!altInput) return;
        var group = altInput.closest('.input-group-date');
        if (!group) return;
        altInput.addEventListener('focus', function() {
            group.classList.add('input-focused');
        });
        altInput.addEventListener('blur', function() {
            group.classList.remove('input-focused');
        });
    }
    if (fp.altInput) setupFocusGroup(fp.altInput);
    var btn = document.getElementById('btnNoteDateIcon');
    if (btn) {
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            e.preventDefault();
            var modal = document.getElementById('noteModal');
            if (modal) modal.classList.add('modal-date-open');
            if (fp.isOpen) {
                fp.close();
                return;
            }
            setTimeout(function() {
                fp.open();
            }, 280);
        });
    }

    if (fp.calendarContainer && typeof fp._positionElement === 'function') {
        document.addEventListener('scroll', function() {
            if (fp.isOpen) fp._positionElement();
        }, true);
        window.addEventListener('resize', function() {
            if (fp.isOpen) fp._positionElement();
        });
    }
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initNoteDatePicker);
} else {
    initNoteDatePicker();
}

function escHtml(str) {
    if (!str) return '';
    return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}

function buildCustomDropdown(selectId) {
    var selectEl = document.getElementById(selectId);
    if (!selectEl) return;

    selectEl.style.display = 'none';

    var existingWrapper = selectEl.parentElement.querySelector('.custom-select-wrapper');
    if (existingWrapper) {
        existingWrapper.remove();
    }

    var wrapper = document.createElement('div');
    wrapper.className = 'custom-select-wrapper';

    var trigger = document.createElement('div');
    trigger.className = 'custom-select-trigger';

    var selectedOption = selectEl.options[selectEl.selectedIndex] || selectEl.options[0];
    var selectedText = selectedOption ? selectedOption.textContent : '-- Pilih --';

    trigger.innerHTML = '<span>' + escHtml(selectedText) + '</span><i class="bi bi-chevron-down"></i>';

    var menu = document.createElement('div');
    menu.className = 'custom-select-menu';

    Array.from(selectEl.options).forEach(function(opt, idx) {
        var optDiv = document.createElement('div');
        optDiv.className = 'custom-select-option' + (idx === selectEl.selectedIndex ? ' selected' : '');
        optDiv.dataset.value = opt.value;
        optDiv.textContent = opt.textContent;

        optDiv.addEventListener('click', function(e) {
            e.stopPropagation();
            selectEl.value = opt.value;
            selectEl.dispatchEvent(new Event('change', { bubbles: true }));

            trigger.querySelector('span').textContent = opt.textContent;

            menu.querySelectorAll('.custom-select-option').forEach(function(o) { o.classList.remove('selected'); });
            optDiv.classList.add('selected');

            wrapper.classList.remove('open');
        });

        menu.appendChild(optDiv);
    });

    trigger.addEventListener('click', function(e) {
        e.stopPropagation();
        var isOpen = wrapper.classList.contains('open');
        closeAllCustomDropdowns();
        if (!isOpen) {
            wrapper.classList.remove('open-above');
            wrapper.classList.add('open');
            var parentGroup = wrapper.closest('.form-group') || wrapper.closest('.col-md-6') || wrapper.parentElement;
            if (parentGroup) {
                parentGroup.style.zIndex = '9999';
                parentGroup.style.position = 'relative';
            }
            var modalBody = wrapper.closest('.modal-body');
            if (modalBody) {
                modalBody.style.setProperty('overflow-y', 'visible', 'important');
                modalBody.style.setProperty('overflow-x', 'visible', 'important');
            }
            var modalContent = wrapper.closest('.modal-content');
            if (modalContent) {
                modalContent.style.setProperty('overflow', 'visible', 'important');
            }
            var menuEl = wrapper.querySelector('.custom-select-menu');
            if (menuEl) {
                var rect = wrapper.getBoundingClientRect();
                var spaceBelow = window.innerHeight - rect.bottom;
                var maxH = Math.min(220, Math.max(120, spaceBelow - 12));
                menuEl.style.maxHeight = maxH + 'px';
                menuEl.style.position = 'absolute';
                menuEl.style.zIndex = '999999';
            }
        }
    });

    wrapper.appendChild(trigger);
    wrapper.appendChild(menu);

    selectEl.parentNode.insertBefore(wrapper, selectEl.nextSibling);
}

function closeAllCustomDropdowns() {
    document.querySelectorAll('.custom-select-wrapper.open').forEach(function(w) {
        w.classList.remove('open');
        w.classList.remove('open-above');
    });
    document.querySelectorAll('.form-group, .col-md-6, .p-3').forEach(function(el) {
        if (el.style.zIndex === '9999') {
            el.style.zIndex = '';
            if (el.style.position === 'relative') el.style.position = '';
        }
    });
    document.querySelectorAll('.modal-body').forEach(function(mb) {
        mb.style.removeProperty('overflow-y');
        mb.style.removeProperty('overflow-x');
    });
    document.querySelectorAll('.modal-content').forEach(function(mc) {
        mc.style.removeProperty('overflow');
    });
    document.querySelectorAll('.custom-select-menu').forEach(function(m) {
        if (m.style.position === 'absolute' && m.style.zIndex === '999999') {
            m.style.position = '';
            m.style.maxHeight = '';
            m.style.zIndex = '';
        }
    });
}

function syncCustomDropdown(selectId) {
    var selectEl = document.getElementById(selectId);
    if (!selectEl) return;
    var wrapper = selectEl.parentElement.querySelector('.custom-select-wrapper');
    if (!wrapper) {
        buildCustomDropdown(selectId);
        return;
    }
    var selectedOption = selectEl.options[selectEl.selectedIndex];
    if (selectedOption) {
        wrapper.querySelector('.custom-select-trigger span').textContent = selectedOption.textContent;
        wrapper.querySelectorAll('.custom-select-option').forEach(function(optDiv) {
            if (optDiv.dataset.value === selectEl.value) {
                optDiv.classList.add('selected');
            } else {
                optDiv.classList.remove('selected');
            }
        });
    }
}

buildCustomDropdown('noteColorSelect');

document.addEventListener('click', function() {
    closeAllCustomDropdowns();
});
</script>
