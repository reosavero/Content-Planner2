<?php





$viewMode = $viewMode ?? 'timeline';
$monthNames = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];


$month = $month ?? date('m');
$year = $year ?? date('Y');
$grouped = $grouped ?? [];
$calendarByDate = $calendarByDate ?? [];
$contentTypes = $contentTypes ?? [];
$picList = $picList ?? [];
$stats = $stats ?? ['total' => 0, 'selesai' => 0, 'menunggu' => 0, 'approved' => 0, 'schedule' => 0, 'belum' => 0];


$userRoleSlug = Session::get('user_role_slug');
$isAdminRole = in_array($userRoleSlug, ['admin', 'superadmin']);


$data = $data ?? [];
$filters = $filters ?? [];
$pagination = $pagination ?? [];
$platforms = $platforms ?? [];
$programs = $programs ?? [];
$kategoris = $kategoris ?? [];
?>



<?php if ($viewMode === 'timeline'): ?>



    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/themes/airbnb.css">

<style>
    .timeline-header {
        background: linear-gradient(135deg, #002677 0%, #003399 50%, #0055cc 100%);
        border-radius: 16px;
        padding: 24px;
        margin-bottom: 24px;
        margin-top: -20px;
        color: white;
        box-shadow: 0 10px 30px -5px rgba(0, 51, 153, 0.3);
        position: relative;
        overflow: hidden;
        transition: box-shadow 0.3s ease;
    }


    .stat-item {
        cursor: pointer;
    }
    .timeline-header.filter-active {
        position: sticky;
        top: 61px;
        z-index: 100;
        border-radius: 0 0 16px 16px;
        box-shadow: 0 10px 30px -5px rgba(0, 51, 153, 0.4);
        margin-top: -20px;
    }
    .stat-item.active-filter {
        background: rgba(255,255,255,0.25) !important;
        border-color: #ffffff !important;
        transform: translateY(-2px) scale(1.05);
        box-shadow: 0 4px 16px rgba(0,0,0,0.15);
    }
    @keyframes fadeUp {
        from {
            opacity: 0;
            transform: translateY(16px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    .task-card.fade-up,
    .task-card.fade-up-filter {
        animation: fadeUp 0.4s ease-out both;
    }
    .timeline-header::after {
        content: '';
        position: absolute;
        top: -50%;
        right: -10%;
        width: 300px;
        height: 300px;
        background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0) 70%);
        pointer-events: none;
    }
    .timeline-header .stats {
        display: flex;
        gap: 16px;
        flex-wrap: wrap;
    }
    .timeline-header .stat-item {
        background: rgba(255,255,255,0.12);
        border: 1px solid rgba(255,255,255,0.2);
        border-radius: 14px;
        padding: 16px 20px;
        text-align: center;
        min-width: 110px;
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        flex: 1;
        transition: transform 0.2s ease, background 0.2s ease;
    }
    .timeline-header .stat-item:hover {
        transform: translateY(-2px);
        background: rgba(255,255,255,0.18);
    }
    .timeline-header .stat-item .number {
        font-size: 28px;
        font-weight: 800;
        line-height: 1.2;
        letter-spacing: -0.5px;
    }
    .timeline-header .stat-item .label {
        font-size: 11px;
        opacity: 0.9;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        margin-top: 4px;
    }
    .timeline-filters {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
        margin-bottom: 24px;
        align-items: center;
        background: var(--surface);
        padding: 16px 20px;
        border-radius: 14px;
        border: 1px solid var(--border-light);
        box-shadow: var(--shadow-xs);
    }
    .timeline-filters select, .timeline-filters input {
        padding: 9px 14px;
        border: 1px solid var(--border);
        border-radius: 10px;
        font-size: 13px;
        background: var(--surface);
        color: var(--text-primary);
        transition: all 0.2s ease;
    }
    .timeline-filters select:focus, .timeline-filters input:focus {
        border-color: var(--tvri-blue);
        box-shadow: 0 0 0 3px rgba(0,51,153,0.1);
        outline: none;
    }
    .timeline-filters .btn-filter {
        padding: 9px 20px;
        border-radius: 10px;
        border: none;
        background: var(--tvri-blue-gradient);
        color: white;
        cursor: pointer;
        font-size: 13px;
        font-weight: 600;
        transition: all 0.2s ease;
        box-shadow: 0 2px 6px rgba(0,51,153,0.2);
    }
    .timeline-filters .btn-filter:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(0,51,153,0.3);
    }
    .date-group {
        margin-bottom: 28px !important;
    }
    .date-header {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 14px 20px;
        background: var(--surface);
        border-radius: 14px 14px 0 0;
        border: 1px solid var(--border-light);
        border-bottom: none;
        scroll-margin-top: 120px;
    }
    .date-header .date-badge {
        background: var(--tvri-blue-gradient);
        color: white;
        border-radius: 10px;
        padding: 5px 16px;
        font-size: 14px;
        font-weight: 700;
        box-shadow: 0 2px 8px rgba(0,51,153,0.25);
    }
    .date-header .day-name {
        font-size: 15px;
        font-weight: 700;
        color: var(--text-primary);
    }
    .task-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(290px, 1fr));
        gap: 16px;
        padding: 20px;
        background: var(--surface);
        border: 1px solid var(--border-light);
        border-radius: 0 0 14px 14px;
    }
    .task-card {
        background: var(--surface);
        border: 1px solid var(--border-light);
        border-radius: 14px;
        padding: 18px;
        transition: all 0.22s cubic-bezier(0.4, 0, 0.2, 1);
        cursor: pointer;
        position: relative;
    }
    .task-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 28px -6px rgba(0, 51, 153, 0.12);
        border-color: var(--tvri-blue-200);
    }
    .task-card .task-type {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 8px;
        font-size: 11px;
        font-weight: 700;
        background: var(--tvri-blue-50);
        color: var(--tvri-blue);
        margin-bottom: 10px;
    }
    .task-card .task-title {
        font-size: 14px;
        font-weight: 700;
        color: var(--text-primary);
        margin-bottom: 8px;
        line-height: 1.45;
    }
    .task-card .task-catatan {
        font-size: 11px;
        color: var(--text-primary);
        margin-top: 10px;
        padding: 10px 12px;
        background: #f0f4fa;
        border-left: 3px solid var(--tvri-blue);
        border-radius: 8px;
        line-height: 1.5;
        word-break: break-word;
        white-space: pre-line;
    }
    [data-theme="dark"] .task-card .task-catatan {
        background: #1e2438;
        border-left-color: #4d7cff;
    }
    .task-card .task-meta {
        font-size: 12px;
        color: var(--text-secondary);
        display: flex;
        gap: 12px;
        align-items: center;
        flex-wrap: wrap;
        margin-top: 10px;
    }
    .task-card .task-links {
        display: flex;
        gap: 6px;
        margin-top: 10px;
        flex-wrap: wrap;
    }
    .task-card .task-links a {
        font-size: 11px;
        padding: 4px 10px;
        background: var(--surface-tertiary);
        border-radius: 8px;
        color: var(--tvri-blue);
        font-weight: 600;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        transition: all 0.15s ease;
    }
    .task-card .task-links a:hover {
        background: var(--tvri-blue-50);
    }
    .add-task-card {
        border: 2px dashed #99b1d6 !important;
        background: rgba(0, 51, 153, 0.02) !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        cursor: pointer !important;
        min-height: 140px !important;
        border-radius: 14px !important;
        transition: all 0.22s ease !important;
    }
    .add-task-card:hover {
        border-color: var(--tvri-blue) !important;
        background: rgba(0, 51, 153, 0.06) !important;
        transform: translateY(-3px) !important;
    }
    .add-task-card i {
        font-size: 26px;
        display: block;
        margin-bottom: 6px;
        transition: transform 0.25s ease;
    }
    .add-task-card:hover i {
        transform: rotate(90deg) scale(1.1);
    }




    .task-card {
        position: relative;
    }
    .task-status-badge {
        position: absolute;
        top: 12px;
        right: 12px;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 10px;
        font-weight: 700;
        letter-spacing: 0.3px;
        text-transform: uppercase;
        line-height: 1.3;
        z-index: 2;
        pointer-events: none;
        box-shadow: 0 2px 6px rgba(0,0,0,0.08);
        border: 1px solid rgba(255,255,255,0.3);
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }
    .task-status-badge.status-belum {
        background: #fee2e2;
        color: #b91c1c;
        border-color: #fca5a5;
    }
    .task-status-badge.status-proses {
        background: #fef3c7;
        color: #92400e;
        border-color: #fcd34d;
    }
    .task-status-badge.status-menunggu {
        background: #ffedd5;
        color: #c2410c;
        border-color: #fdba74;
    }
    .task-status-badge.status-revisi {
        background: #fee2e2;
        color: #b91c1c;
        border-color: #fca5a5;
    }
    .task-status-badge.status-approved {
        background: #dcfce7;
        color: #166534;
        border-color: #86efac;
    }
    .task-status-badge.status-schedule {
        background: #dbeafe;
        color: #1e40af;
        border-color: #93c5fd;
    }
    .task-status-badge.status-selesai {
        background: #d1fae5;
        color: #065f46;
        border-color: #6ee7b7;
    }
    .task-status-badge.status-belum-selesai {
        background: #f3f4f6;
        color: #4b5563;
        border-color: #d1d5db;
    }

    /* Dark mode: warna kartu mengikuti status */
    [data-theme="dark"] .task-card.status-approved,
    [data-theme="dark"] .task-card.status-schedule,
    [data-theme="dark"] .task-card.status-selesai {
        background: rgba(16, 185, 129, 0.1) !important;
        border-color: rgba(16, 185, 129, 0.35) !important;
    }
    [data-theme="dark"] .task-card.status-menunggu {
        background: rgba(245, 158, 11, 0.12) !important;
        border-color: rgba(245, 158, 11, 0.35) !important;
    }
    [data-theme="dark"] .task-card.status-belum {
        background: rgba(220, 38, 38, 0.12) !important;
        border-color: rgba(220, 38, 38, 0.35) !important;
    }
    [data-theme="dark"] .task-card.status-belum-selesai {
        background: var(--surface-hover) !important;
        border-color: var(--border) !important;
    }

    /* Dark mode: badge status -> tint gelap (bukan chip terang) */
    [data-theme="dark"] .task-card .task-status-badge.status-belum,
    [data-theme="dark"] .task-card .task-status-badge.status-proses,
    [data-theme="dark"] .task-card .task-status-badge.status-revisi {
        background: rgba(220, 38, 38, 0.15) !important;
        color: #fca5a5 !important;
        border-color: rgba(220, 38, 38, 0.4) !important;
    }
    [data-theme="dark"] .task-card .task-status-badge.status-menunggu {
        background: rgba(245, 158, 11, 0.15) !important;
        color: #fcd34d !important;
        border-color: rgba(245, 158, 11, 0.4) !important;
    }
    [data-theme="dark"] .task-card .task-status-badge.status-approved,
    [data-theme="dark"] .task-card .task-status-badge.status-selesai {
        background: rgba(16, 185, 129, 0.15) !important;
        color: #34d399 !important;
        border-color: rgba(16, 185, 129, 0.4) !important;
    }
    [data-theme="dark"] .task-card .task-status-badge.status-schedule {
        background: rgba(59, 130, 246, 0.15) !important;
        color: #93c5fd !important;
        border-color: rgba(59, 130, 246, 0.4) !important;
    }
    [data-theme="dark"] .task-card .task-status-badge.status-belum-selesai {
        background: var(--surface-hover) !important;
        color: var(--text-secondary) !important;
        border-color: var(--border) !important;
    }
    /* Dark mode: border kartu tetap warna status saat hover (bukan biru terang) */
    [data-theme="dark"] .task-card.status-approved:hover,
    [data-theme="dark"] .task-card.status-schedule:hover,
    [data-theme="dark"] .task-card.status-selesai:hover {
        border-color: rgba(16, 185, 129, 0.5) !important;
    }
    [data-theme="dark"] .task-card.status-menunggu:hover {
        border-color: rgba(245, 158, 11, 0.5) !important;
    }
    [data-theme="dark"] .task-card.status-belum:hover {
        border-color: rgba(220, 38, 38, 0.5) !important;
    }
    [data-theme="dark"] .task-card.status-belum-selesai:hover {
        border-color: var(--border) !important;
    }




    .flatpickr-calendar {
        border-radius: 16px !important;
        border: 1px solid #d0d9e8 !important;
        box-shadow: 0 18px 44px -10px rgba(15, 23, 42, 0.3), 0 6px 18px -4px rgba(15, 23, 42, 0.12) !important;
        margin-top: 6px;
        overflow: hidden;
    }
    .flatpickr-months {
        border-radius: 16px 16px 0 0 !important;
        overflow: hidden;
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
        transition: background 0.15s ease;
    }
    .flatpickr-current-month .cur-month:hover {
        background: rgba(0, 51, 153, 0.08) !important;
    }
    .flatpickr-current-month .numInputWrapper {
        display: inline-block !important;
        width: auto !important;
        height: auto !important;
        line-height: 1.2 !important;
        margin: 0;
        padding: 0;
        border-radius: 6px;
        cursor: default !important;
        transition: background 0.15s ease;
    }
    .flatpickr-current-month .numInputWrapper:hover {
        background: rgba(0, 51, 153, 0.08) !important;
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
    .flatpickr-months {
        position: relative;
        height: auto !important;
        min-height: 36px;
    }
    .flatpickr-prev-month, .flatpickr-next-month {
        top: 50% !important;
        transform: translateY(-50%) !important;
        padding: 6px 10px !important;
        height: auto !important;
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
    .flatpickr-months .flatpickr-prev-month,
    .flatpickr-months .flatpickr-next-month {
        border-radius: 8px;
        transition: background 0.2s ease;
    }
    .flatpickr-months .flatpickr-prev-month:hover svg,
    .flatpickr-months .flatpickr-next-month:hover svg {
        fill: #003399 !important;
    }

    .input-group-date {
        display: flex !important;
        align-items: stretch !important;
        width: 100% !important;
        max-width: 100% !important;
        box-sizing: border-box !important;
        position: relative;
    }

    .input-group-date input[type="hidden"],
    .input-group-date input[style*="display: none"],
    .input-group-date input[style*="display:none"] {
        display: none !important;
        width: 0 !important;
        height: 0 !important;
        margin: 0 !important;
        padding: 0 !important;
        border: none !important;
    }

    .input-group-date.calendar-open {
        overflow: visible !important;
    }

    .flatpickr-calendar {
        z-index: 99999 !important;
    }
    .input-group-date .form-control,
    .input-group-date input.flatpickr-input,
    .input-group-date input.flatpickr-alt-input {
        border-radius: 8px 0 0 8px !important;
        border-right: none !important;
        flex: 1 1 0% !important;
        min-width: 0 !important;
        width: 0 !important;
        max-width: 100% !important;
        box-sizing: border-box !important;
    }
    .input-group-date input.flatpickr-alt-input.form-control {
        border-right: none !important;
    }
    .input-group-date input.flatpickr-input:not([readonly]),
    .input-group-date input.flatpickr-alt-input:not([readonly]) {
        cursor: text !important;
    }
    .input-group-date .btn-date-icon {
        border-radius: 0 !important;
        border: 1px solid var(--border, #cbd5e1);
        border-left: none;
        border-right: none;
        background: var(--surface, #ffffff);
        cursor: pointer;
        padding: 0 10px;
        color: #003399;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s ease;
        flex: 0 0 auto !important;
        box-sizing: border-box !important;
    }
    .input-group-date .btn-date-icon:hover {
        background: #f1f5f9;
        color: #001a4d;
    }

    .input-group-date.input-focused {
        box-shadow: 0 0 0 3px rgba(0, 51, 153, 0.15);
        border-radius: 8px;
    }
    .input-group-date.input-focused .btn-date-icon,
    .input-group-date.input-focused .btn-date-toggle {
        border-color: #003399 !important;
    }

    .input-group-date input.form-control:focus {
        box-shadow: none !important;
    }
    .input-group-date .btn-date-toggle {
        border-radius: 0 8px 8px 0;
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
        flex: 0 0 auto !important;
        box-sizing: border-box !important;
        font-size: 18px;
    }
    .input-group-date .btn-date-toggle:hover {
        background: #f1f5f9;
    }
    .input-group-date .btn-date-toggle.inactive {
        color: #94a3b8;
    }

    [data-theme="dark"] .input-group-date .btn-date-icon,
    [data-theme="dark"] .input-group-date .btn-date-toggle {
        background: #1e293b;
        border-color: #334155;
    }
    [data-theme="dark"] .input-group-date .btn-date-icon {
        color: #60a5fa;
    }
    [data-theme="dark"] .input-group-date .btn-date-icon:hover {
        background: #334155;
        color: #93c5fd;
    }
    [data-theme="dark"] .input-group-date .btn-date-toggle {
        color: #60a5fa;
    }
    [data-theme="dark"] .input-group-date .btn-date-toggle:hover {
        background: #334155;
    }
    [data-theme="dark"] .input-group-date .btn-date-toggle.inactive {
        color: #64748b;
    }

    [data-theme="dark"] .flatpickr-calendar {
        background: #1e293b !important;
        border-color: #334155 !important;
    }
    [data-theme="dark"] .flatpickr-months .flatpickr-month {
        background: #1e293b !important;
        color: #e2e8f0 !important;
    }
    [data-theme="dark"] .flatpickr-current-month .flatpickr-monthDropdown-months {
        background: #1e293b !important;
        color: #e2e8f0 !important;
    }
    [data-theme="dark"] .flatpickr-current-month .cur-month {
        color: #f1f5f9 !important;
    }
    [data-theme="dark"] .flatpickr-current-month .cur-month:hover {
        background: rgba(96, 165, 250, 0.15) !important;
    }
    [data-theme="dark"] .flatpickr-current-month .numInputWrapper:hover {
        background: rgba(96, 165, 250, 0.15) !important;
    }
    [data-theme="dark"] .flatpickr-current-month .numInputWrapper input.cur-year {
        color: #f1f5f9 !important;
    }
    [data-theme="dark"] .flatpickr-weekdays {
        background: #1e293b !important;
    }
    [data-theme="dark"] .flatpickr-weekday {
        color: #94a3b8 !important;
    }
    [data-theme="dark"] .flatpickr-day {
        color: #e2e8f0 !important;
    }
    [data-theme="dark"] .flatpickr-day:hover {
        background: #334155 !important;
    }
    [data-theme="dark"] .flatpickr-day.flatpickr-disabled,
    [data-theme="dark"] .flatpickr-day.flatpickr-disabled:hover {
        color: #475569 !important;
    }
    [data-theme="dark"] .flatpickr-day.today {
        border-color: #60a5fa !important;
    }
    [data-theme="dark"] .flatpickr-day.selected,
    [data-theme="dark"] .flatpickr-day.startRange,
    [data-theme="dark"] .flatpickr-day.endRange {
        background: #2563eb !important;
        border-color: #2563eb !important;
    }
    .month-nav-icon:hover {
        background: rgba(255, 255, 255, 0.35) !important;
        transform: translateY(-1px) scale(1.06);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }
    .month-nav-icon:active {
        transform: translateY(0) scale(0.98);
    }
    .multi-format-container {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        padding: 10px;
        background: #ffffff;
        border: 1px solid #cbd5e1;
        border-radius: 12px;
        max-height: 160px;
        overflow-y: auto;
    }
    .format-pill-btn {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 5px 12px;
        font-size: 12px;
        font-weight: 600;
        border-radius: 20px;
        border: 1px solid #cbd5e1;
        background: #f8fafc;
        color: #334155;
        cursor: pointer;
        transition: all 0.2s ease;
        user-select: none;
    }
    .format-pill-btn:hover {
        background: #e2e8f0;
        border-color: #94a3b8;
    }
    .format-pill-btn * {
        pointer-events: none;
    }
    .format-pill-btn.selected {
        background: linear-gradient(135deg, #002677 0%, #003399 100%);
        color: #ffffff;
        border-color: #002677;
        box-shadow: 0 2px 6px rgba(0, 51, 153, 0.25);
    }
    .format-pill-btn.selected .icon-add { display: none !important; }
    .format-pill-btn.selected .icon-check { display: inline-block !important; }


    .btn-task-link {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 14px;
        font-size: 12px;
        font-weight: 600;
        border-radius: 8px;
        text-decoration: none;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    }
    .btn-task-link.material {
        background: #eff6ff;
        color: #1d4ed8;
        border: 1px solid #bfdbfe;
    }
    .btn-task-link.material:hover {
        background: #dbeafe;
        color: #1e40af;
        transform: translateY(-1px);
        box-shadow: 0 4px 10px rgba(29,78,216,0.18);
    }
    .btn-task-link.edit {
        background: #f0fdf4;
        color: #15803d;
        border: 1px solid #bbf7d0;
    }
    .btn-task-link.edit:hover {
        background: #dcfce7;
        color: #166534;
        transform: translateY(-1px);
        box-shadow: 0 4px 10px rgba(21,128,61,0.18);
    }
    .btn-task-link.file {
        background: #faf5ff;
        color: #7e22ce;
        border: 1px solid #e9d5ff;
    }
    .btn-task-link.file:hover {
        background: #f3e8ff;
        color: #6b21a8;
        transform: translateY(-1px);
        box-shadow: 0 4px 10px rgba(126,34,206,0.18);
    }

    /* ---------- Dark mode: modal Tambah Task (section + pill format) ---------- */
    [data-theme="dark"] #modalTask .p-3.rounded-lg {
        background: var(--surface-secondary) !important;
        border-color: var(--border) !important;
    }
    [data-theme="dark"] #modalTask .multi-format-container {
        background: var(--surface-tertiary) !important;
        border-color: var(--border) !important;
    }
    [data-theme="dark"] .format-pill-btn {
        background: var(--surface-secondary) !important;
        border-color: var(--border) !important;
        color: var(--text-primary) !important;
    }
    [data-theme="dark"] .format-pill-btn:hover {
        background: var(--surface-hover) !important;
        border-color: var(--border-focus) !important;
    }
    [data-theme="dark"] .format-pill-btn.selected {
        background: linear-gradient(135deg, #1e3a8a 0%, #2f54eb 100%) !important;
        border-color: #2f54eb !important;
        color: #ffffff !important;
        box-shadow: 0 2px 8px rgba(47, 84, 235, 0.35) !important;
    }
    </style>

    <?php
    $prevMonth = (int)$month - 1;
    $prevYear = (int)$year;
    if ($prevMonth < 1) {
        $prevMonth = 12;
        $prevYear--;
    }

    $nextMonth = (int)$month + 1;
    $nextYear = (int)$year;
    if ($nextMonth > 12) {
        $nextMonth = 1;
        $nextYear++;
    }
    ?>


    <div class="timeline-header">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; flex-wrap: wrap; gap: 12px; width: 100%;">
            <div>
                <h3 style="margin: 0; font-size: 22px; font-weight: 700; color: #fff;">Timeline Konten Harian</h3>
                <p style="margin: 4px 0 0; opacity: 0.85; font-size: 13px; color: #fff;">Bulan <?= $monthNames[(int)$month] ?> <?= $year ?></p>
            </div>

            <div style="display: flex; align-items: center; gap: 6px; flex-wrap: nowrap; flex: 1; justify-content: flex-end; max-width: 450px;">
                <?php
                $prevKeyNum = (int)$prevYear * 12 + (int)$prevMonth;
                $minKeyNum = ($minYear !== null) ? (int)$minYear * 12 + (int)$minMonth : null;
                $prevDisabled = ($minKeyNum !== null && $prevKeyNum < $minKeyNum);
                ?>
                <?php if ($prevDisabled): ?>
                <span class="month-nav-icon" title="Bulan <?= $monthNames[$prevMonth] ?> <?= $prevYear ?> sudah masuk archive (maksimal bisa akses <?= $monthNames[(int)$minMonth] ?> <?= $minYear ?>)" style="width: 36px; height: 36px; border-radius: 8px; background: rgba(255,255,255,0.12); border: 1px solid rgba(255,255,255,0.2); color: rgba(255,255,255,0.4); display: flex; align-items: center; justify-content: center; text-decoration: none; flex-shrink: 0; cursor: not-allowed; opacity: 0.5;">
                    <i class="bi bi-chevron-left" style="font-size: 15px;"></i>
                </span>
                <?php else: ?>
                <a href="?view=timeline&bulan=<?= $prevMonth ?>&tahun=<?= $prevYear ?>" class="month-nav-icon" title="Bulan Sebelumnya (<?= $monthNames[$prevMonth] ?> <?= $prevYear ?>)" style="width: 36px; height: 36px; border-radius: 8px; background: rgba(255,255,255,0.18); border: 1px solid rgba(255,255,255,0.3); color: #fff; display: flex; align-items: center; justify-content: center; text-decoration: none; transition: all 0.2s ease; backdrop-filter: blur(8px); flex-shrink: 0;">
                    <i class="bi bi-chevron-left" style="font-size: 15px;"></i>
                </a>
                <?php endif; ?>
                <a href="?view=timeline&bulan=<?= $nextMonth ?>&tahun=<?= $nextYear ?>" class="month-nav-icon" title="Bulan Berikutnya (<?= $monthNames[$nextMonth] ?> <?= $nextYear ?>)" style="width: 36px; height: 36px; border-radius: 8px; background: rgba(255,255,255,0.18); border: 1px solid rgba(255,255,255,0.3); color: #fff; display: flex; align-items: center; justify-content: center; text-decoration: none; transition: all 0.2s ease; backdrop-filter: blur(8px); flex-shrink: 0;">
                    <i class="bi bi-chevron-right" style="font-size: 15px;"></i>
                </a>

                <div style="flex: 1 1 auto; max-width: 250px; min-width: 130px; margin-left: 2px;">
                    <div class="planning-search-box" style="position: relative; width: 100%; display: flex; align-items: center;">
                        <i class="bi bi-search search-icon" style="position: absolute; left: 11px; font-size: 13px; pointer-events: none; z-index: 10;"></i>
                        <input type="text" id="planningSearchInput" class="form-control planning-search-field" placeholder="Pencarian task"
                               onkeyup="filterPlanningCardsPerLetter(this.value)" oninput="filterPlanningCardsPerLetter(this.value)">
                    </div>
                </div>
            </div>
        </div>
        <div class="stats">
            <div class="stat-item" onclick="filterByStatus('all')" title="Tampilkan semua task">
                <div class="number"><?= $stats['total'] ?></div>
                <div class="label">Total Task</div>
            </div>
            <div class="stat-item" onclick="filterByStatus('Selesai','Publish')" title="Klik untuk filter task Selesai">
                <div class="number" style="color: #10b981;"><?= $stats['selesai'] ?></div>
                <div class="label">Selesai</div>
            </div>
            <div class="stat-item" onclick="filterByStatus('Scheduled')" title="Klik untuk filter task Schedule">
                <div class="number" style="color: #3b82f6;"><?= $stats['schedule'] ?></div>
                <div class="label">Schedule</div>
            </div>
            <div class="stat-item" onclick="filterByStatus('Approved')" title="Klik untuk filter task Approved">
                <div class="number" style="color: #22c55e;"><?= $stats['approved'] ?></div>
                <div class="label">Approved</div>
            </div>
            <div class="stat-item" onclick="filterByStatus('Pending Approval')" title="Klik untuk filter task Menunggu Approval">
                <div class="number" style="color: #f97316;"><?= $stats['menunggu'] ?></div>
                <div class="label">Menunggu Approval</div>
            </div>
            <div class="stat-item" onclick="filterByStatus('Belum','Assigned','In Progress','Proses','Need Revision','Belum Selesai')" title="Klik untuk filter task Belum">
                <div class="number" style="color: #dc2626;"><?= $stats['belum'] ?></div>
                <div class="label">Belum</div>
            </div>
        </div>
    </div>




    <?php

    $todayTs = strtotime(date('Y-m-d'));
    $hasVisibleDate = false;
    foreach ($grouped as $date => $group) {
        if (!empty($group['tasks']) || strtotime($date) >= $todayTs) {
            $hasVisibleDate = true;
            break;
        }
    }
    ?>
    <?php if (empty($grouped) || !$hasVisibleDate): ?>
        <div class="empty-state card p-5 text-center">
            <i class="bi bi-clipboard-data text-tertiary" style="font-size: 48px;"></i>
            <h4 class="mt-3">Belum ada task untuk bulan ini</h4>
            <p class="text-tertiary">Klik "Tambah Task" untuk mulai mengisi timeline</p>
        </div>
    <?php else: ?>
        <?php foreach ($grouped as $date => $group):
            $dayNum = date('d', strtotime($date));
            $dayName = $group['day_name'];
            $calSummary = $calendarByDate[$date] ?? null;

            if (empty($group['tasks']) && strtotime($date) < $todayTs) {
                continue;
            }
        ?>
        <div class="date-group">
            <div class="date-header">
                <span class="date-badge"><?= $dayNum ?></span>
                <span class="day-name"><?= htmlspecialchars($dayName) ?> (<?= date('d/m/Y', strtotime($date)) ?>)</span>
                <?php if ($calSummary): ?>
                    <span style="font-size:12px; color:#64748b; margin-left:auto; display:flex; gap:12px;">
                        <span>Feed: <b><?= $calSummary['feed_count'] ?></b></span>
                        <span>Reels: <b><?= $calSummary['reels_count'] ?></b></span>
                        <span>Story: <b><?= $calSummary['story_count'] ?></b></span>
                    </span>
                <?php endif; ?>
            </div>

            <div class="task-grid">
                <?php foreach ($group['tasks'] as $task):
                    $status = $task['status'];

                    $lockedStatus = in_array($status, ['Approved', 'Scheduled', 'Publish', 'Selesai', 'Pending Approval']);

                    $statusDisplay = match($status) {
                        'Belum', 'Assigned', 'In Progress', 'Proses', 'Need Revision' => 'Belum',
                        'Pending Approval' => 'Menunggu Approval',
                        'Approved' => 'Approved',
                        'Scheduled' => 'Schedule',
                        'Publish', 'Selesai' => 'Selesai',
                        'Belum Selesai' => 'Belum Selesai',
                        default => 'Belum'
                    };
                    $statusClass = match($status) {
                        'Belum', 'Assigned', 'In Progress', 'Proses', 'Need Revision' => 'status-belum',
                        'Pending Approval' => 'status-menunggu',
                        'Approved' => 'status-approved',
                        'Scheduled' => 'status-schedule',
                        'Publish', 'Selesai' => 'status-selesai',
                        'Belum Selesai' => 'status-belum-selesai',
                        default => 'status-belum'
                    };
                    $statusIcon = match($status) {
                        'Belum', 'Assigned', 'In Progress', 'Proses', 'Need Revision' => 'bi-hourglass-split',
                        'Pending Approval' => 'bi-clock-history',
                        'Approved' => 'bi-check-circle-fill',
                        'Scheduled' => 'bi-calendar-check',
                        'Publish', 'Selesai' => 'bi-check2-circle',
                        'Belum Selesai' => 'bi-exclamation-circle',
                        default => 'bi-hourglass-split'
                    };
                ?>
                <div class="task-card <?= $statusClass ?>" data-id="<?= $task['id'] ?>" data-status="<?= htmlspecialchars($task['status']) ?>" onclick="viewTask(event, <?= $task['id'] ?>)">


                    <span class="task-status-badge <?= $statusClass ?>"><i class="bi <?= $statusIcon ?>"></i> <?= htmlspecialchars($statusDisplay) ?></span>

                    <div style="flex: 1; display: flex; flex-direction: column; padding-right: 95px;">
                        <?php if (!empty($task['content_type'])): ?>
                            <div class="task-format-badges mb-2" style="display: flex; flex-wrap: wrap; gap: 4px;">
                                <?php foreach (array_filter(array_map('trim', explode(',', $task['content_type']))) as $fmt): ?>
                                    <span class="badge-format-tag" style="background: linear-gradient(135deg, #1a237e, #283593); color: #ffffff; font-weight: 600; font-size: 10px; padding: 3px 8px; border-radius: 6px; display: inline-flex; align-items: center; gap: 3px; box-shadow: 0 1px 3px rgba(26,35,126,0.15);">
                                        <i class="bi bi-tag-fill" style="font-size: 9px;"></i> <?= htmlspecialchars($fmt) ?>
                                    </span>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <div class="task-title" style="font-size: 15px; font-weight: 700; color: var(--text-primary); margin-bottom: 8px; line-height: 1.45; padding-top: 2px;">
                            <?= !empty($task['title']) ? htmlspecialchars($task['title']) : '(belum ada judul)' ?>
                        </div>
                    </div>

                    <div class="task-actions" style="margin-top: auto; padding-top: 10px; border-top: 1px solid var(--border-light); display: flex; justify-content: space-between; align-items: center; gap: 6px; flex-wrap: nowrap; min-height: 42px;">
                        <div class="task-meta-left" style="display: flex; align-items: center; gap: 5px; min-width: 0; flex: 1; overflow: hidden;">
                            <?php if ($task['pic_name']): ?>
                                <span class="meta-item" title="PIC: <?= htmlspecialchars($task['pic_name']) ?>" style="font-size: 11px; font-weight: 600; color: var(--text-secondary); display: inline-flex; align-items: center; gap: 4px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; flex-shrink: 1;">
                                    <i class="bi bi-person-fill" style="color: var(--tvri-blue); flex-shrink: 0;"></i> <?= htmlspecialchars($task['pic_name']) ?>
                                </span>
                            <?php endif; ?>
                        </div>

                        <?php if (!($isAdminRole && $lockedStatus)): ?>
                        <div class="task-buttons-right" style="display: flex; gap: 4px; align-items: center; flex-shrink: 0; margin-left: auto;">
                            <button type="button" class="btn-card-action edit" onclick="event.stopPropagation(); openModal('modalTask', <?= $task['id'] ?>, 'edit')" title="Edit Task">
                                <i class="bi bi-pencil-square"></i> Edit
                            </button>
                            <button type="button" class="btn-card-action delete" onclick="event.stopPropagation(); deleteTask(event, <?= $task['id'] ?>)" title="Hapus Task">
                                <i class="bi bi-trash"></i> Hapus
                            </button>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>


                <?php if ($date >= date('Y-m-d')): ?>
                <div class="task-card add-task-card" onclick="openModal('modalTask', null, 'create', '<?= $date ?>')">
                    <div style="text-align: center; color: var(--tvri-blue);">
                        <i class="bi bi-plus-circle"></i>
                        <span style="font-size: 13px; font-weight: 700;">Tambah Task</span>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>

<?php else: ?>




    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="" id="filterForm">
                <input type="hidden" name="view" value="table">
                <div class="row" style="align-items: flex-end;">
                    <div class="col-12 col-md-3">
                        <div class="form-group mb-0">
                            <label class="form-label">Cari</label>
                            <div class="search-input">
                                <i class="bi bi-search"></i>
                                <input type="text" name="search" class="form-control" placeholder="Judul konten..."
                                       value="<?= htmlspecialchars($filters['search'] ?? '') ?>">
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-2">
                        <div class="form-group mb-0">
                            <label class="form-label">Status Workflow</label>
                            <select name="status" class="form-control" onchange="this.form.submit()">
                                <option value="">Semua Status</option>
                                <option value="draft" <?= ($filters['status'] ?? '') === 'draft' ? 'selected' : '' ?>>Draft</option>
                                <option value="review" <?= ($filters['status'] ?? '') === 'review' ? 'selected' : '' ?>>Review</option>
                                <option value="approved" <?= ($filters['status'] ?? '') === 'approved' ? 'selected' : '' ?>>Approved</option>
                                <option value="revision" <?= ($filters['status'] ?? '') === 'revision' ? 'selected' : '' ?>>Revisi</option>
                                <option value="scheduled" <?= ($filters['status'] ?? '') === 'scheduled' ? 'selected' : '' ?>>Terjadwal</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-6 col-md-2">
                        <div class="form-group mb-0">
                            <label class="form-label">Platform</label>
                            <select name="platform_id" class="form-control" onchange="this.form.submit()">
                                <option value="">Semua Platform</option>
                                <?php foreach ($platforms as $p): ?>
                                    <option value="<?= $p['id'] ?>" <?= ($filters['platform_id'] ?? '') == $p['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($p['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-6 col-md-2">
                        <div class="form-group mb-0">
                            <label class="form-label">Program</label>
                            <select name="program_id" class="form-control" onchange="this.form.submit()">
                                <option value="">Semua Program</option>
                                <?php foreach ($programs as $p): ?>
                                    <option value="<?= $p['id'] ?>" <?= ($filters['program_id'] ?? '') == $p['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($p['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-6 col-md-2">
                        <div class="form-group mb-0">
                            <label class="form-label">Prioritas</label>
                            <select name="priority" class="form-control" onchange="this.form.submit()">
                                <option value="">Semua</option>
                                <option value="urgent" <?= ($filters['priority'] ?? '') === 'urgent' ? 'selected' : '' ?>>Urgent</option>
                                <option value="high" <?= ($filters['priority'] ?? '') === 'high' ? 'selected' : '' ?>>High</option>
                                <option value="medium" <?= ($filters['priority'] ?? '') === 'medium' ? 'selected' : '' ?>>Medium</option>
                                <option value="low" <?= ($filters['priority'] ?? '') === 'low' ? 'selected' : '' ?>>Low</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-12 col-md-1">
                        <div class="form-group mb-0" style="display: flex; gap: 4px;">
                            <button type="submit" class="btn btn-primary btn-sm w-100">
                                <i class="bi bi-funnel"></i>
                            </button>
                            <a href="?view=table" class="btn btn-ghost btn-sm" title="Reset filter">
                                <i class="bi bi-x"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>


    <div class="card table-section-card mb-4" style="margin-bottom: 28px;">
        <div class="card-header">
            <h5><i class="bi bi-table text-primary"></i> Master Tabel Planning Konten</h5>
            <div class="d-flex gap-2">
                <a href="<?= BASE_URL ?>/planning/create" class="btn btn-primary btn-sm">
                    <i class="bi bi-plus-lg me-1"></i> Buat Planning Baru
                </a>
            </div>
        </div>
        <div class="card-body p-0">
            <?php if (!empty($data)): ?>
                <div class="table-wrapper">
                    <table class="table">
                        <thead>
                            <tr>
                                <th style="width: 32px;">
                                    <input type="checkbox" id="selectAll" onchange="toggleSelectAll(this)">
                                </th>
                                <th>Judul Konten</th>
                                <th>Platform</th>
                                <th>Status Workflow</th>
                                <th>Program</th>
                                <th>Tanggal</th>
                                <th>Prioritas</th>
                                <th style="width: 100px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($data as $item): ?>
                                <tr>
                                    <td>
                                        <input type="checkbox" class="row-checkbox" value="<?= $item['id'] ?>" onchange="updateBulkActions()">
                                    </td>
                                    <td>
                                        <a href="<?= BASE_URL ?>/planning/<?= $item['id'] ?>" style="font-weight: 600; color: #0f172a; text-decoration: none;">
                                            <?= htmlspecialchars(truncateText($item['judul'], 60)) ?>
                                        </a>
                                    </td>
                                    <td>
                                        <span style="display: flex; align-items: center; gap: 6px; color: <?= htmlspecialchars($item['platform_color'] ?? '#64748b') ?>;">
                                            <i class="<?= iconClass($item['platform_icon'] ?? 'bi-globe') ?>"></i>
                                            <?= htmlspecialchars($item['platform_name'] ?? '-') ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge status-<?= $item['status'] ?? 'draft' ?>">
                                            <?= ucfirst($item['status'] ?? 'draft') ?>
                                        </span>
                                    </td>
                                    <td><?= htmlspecialchars($item['program_name'] ?? '-') ?></td>
                                    <td><?= !empty($item['tanggal_posting']) ? date('d/m/Y', strtotime($item['tanggal_posting'])) : '-' ?></td>
                                    <td>
                                        <span class="badge badge-outline"><?= ucfirst($item['priority'] ?? 'medium') ?></span>
                                    </td>
                                    <td>
                                        <div class="table-actions">
                                            <a href="<?= BASE_URL ?>/planning/<?= $item['id'] ?>" class="btn btn-icon btn-sm btn-ghost" title="Detail"><i class="bi bi-eye"></i></a>
                                            <a href="<?= BASE_URL ?>/planning/<?= $item['id'] ?>/edit" class="btn btn-icon btn-sm btn-ghost" title="Edit"><i class="bi bi-pencil"></i></a>
                                            <button class="btn btn-icon btn-sm btn-ghost" style="color:#e53935;" onclick="deletePlanning(event, <?= $item['id'] ?>)" title="Hapus"><i class="bi bi-trash"></i></button>
                                            <?php if ($isAdminRole && in_array($item['status'] ?? '', ['approved', 'scheduled'])): ?>
                                                <button type="button" class="btn btn-icon btn-sm btn-ghost" style="color:#22c55e;" onclick="openPublishModal(<?= $item['id'] ?>, '<?= htmlspecialchars(addslashes($item['judul'])) ?>')" title="Publish Now">
                                                    <i class="bi bi-send-fill"></i>
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>


                <div class="p-3 border-top d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div class="d-flex align-items-center gap-2 text-xs">
                        <span id="selectedCount">0 dipilih</span>
                        <button class="btn btn-sm btn-ghost text-danger" id="bulkDeleteBtn" style="display:none;" onclick="bulkAction('delete')"><i class="bi bi-trash me-1"></i> Hapus</button>
                        <button class="btn btn-sm btn-ghost" id="bulkReviewBtn" style="display:none;" onclick="bulkAction('review')"><i class="bi bi-send me-1"></i> Kirim Review</button>
                    </div>
                </div>
            <?php else: ?>
                <div class="empty-state p-5 text-center">
                    <i class="bi bi-calendar-plus text-tertiary" style="font-size: 48px;"></i>
                    <h5 class="mt-3">Belum Ada Planning Konten</h5>
                    <p>Buat perencanaan konten pertama Anda.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>




<div id="modalTask" class="custom-modal-backdrop" role="dialog" aria-modal="true">
    <div class="modal-content" style="max-width: 680px;">
        <div class="modal-header">
            <h3 id="modalTaskTitle" style="display: flex; align-items: center; gap: 10px; font-size: 18px; font-weight: 700; margin: 0;"><i class="bi bi-plus-circle" style="color: var(--tvri-blue, #003399); font-size: 20px; flex-shrink: 0;"></i><span>Tambah Task Harian</span></h3>
        </div>
        <div class="modal-body" style="padding: 20px 24px;">
            <form id="formTask" method="POST" action="<?= BASE_URL ?>/timeline/store" enctype="multipart/form-data">
                <?= Session::csrfField() ?>
                <input type="hidden" name="action" id="taskAction" value="store">
                <input type="hidden" name="id" id="taskId" value="">
                <input type="hidden" name="existing_file" id="existingFile" value="">


                <div class="p-3 rounded-lg mb-3" style="background: #f8fafc; border: 1px solid #e2e8f0;">
                    <label class="form-label" style="font-size: 11px; font-weight: 700; text-transform: uppercase; color: #1a237e; letter-spacing: 0.5px; margin-bottom: 10px; display: block;">
                        <i class="bi bi-pencil-square me-1"></i> Informasi Konten
                    </label>

                    <div class="form-group mb-3">
                        <label style="font-size: 12px; font-weight: 600; color: #334155; display: block; margin-bottom: 4px;">Judul / Nama Konten</label>
                        <textarea name="title" id="taskTitleInput" class="form-control" rows="2" placeholder="Contoh: Berita JHI..." required style="resize: vertical; min-height: 42px;"></textarea>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-12">
                            <div class="form-group mb-0">
                                <label style="font-size: 12px; font-weight: 600; color: #334155; display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                                    <span>Jenis Format</span>
                                    <span id="selectedFormatCount" style="font-size: 11px; font-weight: 700; color: #003399;">0 Dipilih</span>
                                </label>

                                <input type="hidden" name="content_type" id="taskContentTypeInput" value="" required>

                                <div class="multi-format-container" id="multiFormatContainer">
                                    <?php
                                    $formatOptions = ['Feed', 'Reels', 'Reels Berita', 'Story', 'Thumbnail', 'Flyer/Poster', 'Take Video', 'Skrip / Materi', 'YouTube', 'Image'];
                                    foreach ($formatOptions as $fmt):
                                    ?>
                                        <button type="button" class="format-pill-btn" data-value="<?= htmlspecialchars($fmt) ?>" onclick="toggleFormatPill(this, event)">
                                            <i class="bi bi-plus-lg icon-add"></i>
                                            <i class="bi bi-check-lg icon-check" style="display:none;"></i>
                                            <span><?= htmlspecialchars($fmt) ?></span>
                                        </button>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>


                <div class="p-3 rounded-lg mb-3" style="background: #f8fafc; border: 1px solid #e2e8f0;">
                    <label class="form-label" style="font-size: 11px; font-weight: 700; text-transform: uppercase; color: #1a237e; letter-spacing: 0.5px; margin-bottom: 12px; display: block;">
                        <i class="bi bi-clock-history me-1"></i> Penjadwalan & PIC
                    </label>

                    <input type="hidden" name="task_date" id="taskDateInput" value="<?= date('Y-m-d') ?>">

                    <div class="row g-3">
                        <div class="col-12">
                            <div class="form-group mb-0">
                                <label style="font-size: 12px; font-weight: 600; color: #334155; display: block; margin-bottom: 4px;">PIC (Penanggung Jawab)</label>
                                <div class="pic-select-container" style="max-width: 270px; width: 100%;">
                                    <select name="assigned_to" id="taskAssignedToSelect" class="form-control" required style="width: 100%; height: 38px; font-size: 13px;" onchange="var opt=this.options[this.selectedIndex]; document.getElementById('taskPicNameInput').value = opt.getAttribute('data-name') || '';">
                                        <option value="">-- Pilih User Magang / PIC --</option>
                                        <?php foreach ($usersList as $u): ?>
                                            <option value="<?= (int)$u['id'] ?>" data-name="<?= htmlspecialchars($u['name']) ?>"><?= htmlspecialchars($u['name']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <input type="hidden" name="pic_name" id="taskPicNameInput" value="">
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-sm-6">
                            <div class="form-group mb-0">
                                <label style="font-size: 12px; font-weight: 600; color: #334155; display: block; margin-bottom: 4px;">Urutan (Sort Order)</label>
                                <input type="number" name="sort_order" id="taskSortOrderInput" class="form-control" value="1" min="1" style="height: 38px; font-size: 13px;">
                            </div>
                        </div>

                        <div class="col-12 col-sm-6">
                            <div class="form-group mb-0">
                                <label style="font-size: 12px; font-weight: 600; color: #334155; display: block; margin-bottom: 4px;">Deadline</label>
                                <div class="input-group-date">
                                    <input type="text" name="deadline" id="taskDeadlineInput" class="form-control" placeholder="dd/mm/yyyy" autocomplete="off" style="height: 38px; font-size: 13px;">
                                    <input type="hidden" name="deadline_active" id="taskDeadlineActiveInput" value="0">
                                    <button type="button" class="btn-date-icon" id="btnDeadlineIcon" tabindex="-1" title="Buka Kalender" style="height: 38px;">
                                        <i class="bi bi-calendar3"></i>
                                    </button>
                                    <button type="button" class="btn-date-toggle inactive" id="btnDeadlineToggle" tabindex="-1" title="Aktifkan deadline" style="height: 38px;">
                                        <i class="bi bi-toggle-off"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>


                <div class="p-3 rounded-lg mb-3" style="background: #f8fafc; border: 1px solid #e2e8f0;">
                    <label class="form-label" style="font-size: 11px; font-weight: 700; text-transform: uppercase; color: #1a237e; letter-spacing: 0.5px; margin-bottom: 10px; display: block;">
                        <i class="bi bi-file-earmark-text me-1"></i> Catatan & Tautan
                    </label>

                    <div class="form-group mb-3">
                        <label style="font-size: 12px; font-weight: 600; color: #334155; display: block; margin-bottom: 4px;">Catatan / Deskripsi Instruksi</label>
                        <textarea name="catatan" id="taskCatatanInput" class="form-control" rows="3" placeholder="Instruksi pengerjaan / catatan..."></textarea>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <div class="form-group mb-0">
                                <label style="font-size: 12px; font-weight: 600; color: #334155; display: block; margin-bottom: 4px;">Link Materi</label>
                                <input type="url" name="material_link" id="taskMaterialLinkInput" class="form-control" placeholder="https://drive.google.com/...">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-0">
                                <label style="font-size: 12px; font-weight: 600; color: #334155; display: block; margin-bottom: 4px;">Link Edit</label>
                                <input type="url" name="edit_link" id="taskEditLinkInput" class="form-control" placeholder="https://canva.com/...">
                            </div>
                        </div>
                    </div>

                    <div class="form-group mb-0">
                        <label style="font-size: 12px; font-weight: 600; color: #334155; display: block; margin-bottom: 4px;">Upload File Lampiran</label>
                        <input type="file" name="file_attachment" class="form-control" style="font-size: 12px;">
                        <div id="fileInfo" style="display:none; margin-top:8px; align-items:center; gap:8px;">
                            <span style="font-size:11px; color:#64748b;">Lampiran Saat Ini:</span>
                            <a id="fileLink" href="#" target="_blank" rel="noopener" class="btn-task-link file" title="Unduh Lampiran Info Task">
                                <i class="bi bi-paperclip"></i><span id="fileLinkText">Buka File</span>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="modal-footer px-0 pb-0 pt-2" style="border-top: 1px solid #e2e8f0; margin-top: 16px;">
                    <button type="button" class="btn btn-ghost" onclick="closeModal('modalTask')">Batal</button>
                    <button type="submit" id="btnSubmitTask" class="btn btn-primary" style="border-radius: 10px; font-weight: 600; min-width: 140px;">
                        <i class="bi bi-check-circle me-1"></i> Simpan Task
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>




<div id="modalCalendar" class="custom-modal-backdrop" role="dialog" aria-modal="true">
    <div class="modal-content" style="max-width: 540px;">
        <div class="modal-header">
            <h3><i class="bi bi-calendar3 me-2"></i>Atur Ringkasan Kalender</h3>
        </div>
        <div class="modal-body" style="padding: 20px 24px;">
            <form method="POST" action="<?= BASE_URL ?>/timeline/save-calendar">
                <?= Session::csrfField() ?>
                <div class="p-3 rounded-lg" style="background: #f8fafc; border: 1px solid #e2e8f0;">
                    <div class="form-group mb-3">
                        <label style="font-size: 12px; font-weight: 600; color: #334155; display: block; margin-bottom: 4px;">Tanggal Target <span class="text-danger">*</span></label>
                        <div class="input-group-date">
                            <input type="text" name="calendar_date" id="cal_date" class="form-control" required placeholder="dd/mm/yyyy" autocomplete="off">
                            <button type="button" class="btn-date-icon" id="btnCalDateIcon" tabindex="-1" title="Buka Kalender">
                                <i class="bi bi-calendar3"></i>
                            </button>
                        </div>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <div class="form-group mb-0">
                                <label style="font-size: 12px; font-weight: 600; color: #334155; display: block; margin-bottom: 4px;">Jumlah Feed Target</label>
                                <input type="number" name="feed_count" class="form-control" value="0" min="0">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-0">
                                <label style="font-size: 12px; font-weight: 600; color: #334155; display: block; margin-bottom: 4px;">Jumlah Reels Target</label>
                                <input type="number" name="reels_count" class="form-control" value="0" min="0">
                            </div>
                        </div>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="form-group mb-0">
                                <label style="font-size: 12px; font-weight: 600; color: #334155; display: block; margin-bottom: 4px;">Jumlah Story Target</label>
                                <input type="number" name="story_count" class="form-control" value="0" min="0">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-0">
                                <label style="font-size: 12px; font-weight: 600; color: #334155; display: block; margin-bottom: 4px;">Jam Posting Target</label>
                                <input type="text" name="post_time" class="form-control" value="08.00 - 18.00">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer px-0 pb-0 pt-3" style="border-top: 1px solid #e2e8f0; margin-top: 16px;">
                    <button type="button" class="btn btn-ghost" onclick="closeModal('modalCalendar')">Batal</button>
                    <button type="submit" class="btn btn-primary" style="border-radius: 10px; font-weight: 600; min-width: 140px;">
                        <i class="bi bi-check-circle me-1"></i> Simpan Kalender
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>


<div id="modalDetail" class="custom-modal-backdrop" role="dialog" aria-modal="true" aria-labelledby="detailModalTitle">
    <div class="modal-content" style="max-width: 620px;">
        <div class="modal-header">
            <h3 id="detailModalTitle"><i class="bi bi-clipboard-check me-3" style="margin-right: 12px;"></i>Detail Task</h3>
        </div>
        <div id="detailModalBody" class="modal-body"></div>
        <div id="detailModalFooter" class="modal-footer"></div>
    </div>
</div>


<style>
    #modalConfirm .modal-content {
        max-width: 380px !important;
        padding: 12px !important;
        border-radius: 16px !important;
        box-shadow: 0 10px 40px rgba(0,0,0,0.15) !important;
        width: 92% !important;
        display: block !important;
        overflow: visible !important;
        transform: scale(0.88) translateY(15px) !important;
        opacity: 0;
        transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1), opacity 0.25s ease-out;
        border: none !important;
    }
    #modalConfirm.active .modal-content {
        transform: scale(1) translateY(0) !important;
        opacity: 1;
    }
</style>


<style>
    #modalTask .modal-content,
    #modalCalendar .modal-content,
    #modalDetail .modal-content,
    #modalConfirm .modal-content {
        transition: none !important;
    }
    #modalTask.active .modal-content,
    #modalTask.show .modal-content,
    #modalCalendar.active .modal-content,
    #modalCalendar.show .modal-content,
    #modalDetail.active .modal-content,
    #modalDetail.show .modal-content,
    #modalConfirm.active .modal-content,
    #modalConfirm.show .modal-content {
        animation: slideUpFade 0.35s cubic-bezier(0.34, 1.56, 0.64, 1) both !important;
    }
    #modalTask.closing .modal-content,
    #modalCalendar.closing .modal-content,
    #modalDetail.closing .modal-content,
    #modalConfirm.closing .modal-content {
        animation: slideDownFade 0.25s ease-in both !important;
    }
    @keyframes slideUpFade {
        from { opacity: 0; transform: translateY(30px) scale(0.97); }
        to { opacity: 1; transform: translateY(0) scale(1); }
    }
    @keyframes slideDownFade {
        from { opacity: 1; transform: translateY(0) scale(1); }
        to { opacity: 0; transform: translateY(20px) scale(0.97); }
    }
</style>
<div id="modalConfirm" class="custom-modal-backdrop" role="dialog" aria-modal="true">
    <div class="modal-content">
        <div class="modal-body text-center p-4">
            <div class="mb-3">
                <div style="width: 60px; height: 60px; background: #fee2e2; color: #dc2626; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center;">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="3 6 5 6 21 6"></polyline>
                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                        <line x1="10" y1="11" x2="10" y2="17"></line>
                        <line x1="14" y1="11" x2="14" y2="17"></line>
                    </svg>
                </div>
            </div>
            <h5 style="font-weight: 700; color: #1e293b; font-size: 18px; margin-bottom: 8px;">Konfirmasi Hapus</h5>
            <p id="confirmModalMessage" style="font-size: 13.5px; color: #64748b; margin-bottom: 24px; line-height: 1.5;">Yakin ingin menghapus task ini?</p>
            <div class="d-flex gap-2 justify-content-center">
                <button type="button" class="btn" onclick="closeModal('modalConfirm')" style="flex: 1; height: 42px; font-weight: 600; font-size: 14px; background: #f1f5f9; color: #475569; border: none; border-radius: 10px;">Batal</button>
                <button type="button" id="confirmModalOk" class="btn" style="flex: 1; height: 42px; font-weight: 600; font-size: 14px; background: #dc2626; color: #ffffff; border: none; border-radius: 10px;">Hapus Task</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/id.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {

    setTimeout(function() {
        var allCards = document.querySelectorAll('.task-card:not(.add-task-card)');
        allCards.forEach(function(card, i) {
            card.classList.add('fade-up');
            card.style.animationDelay = (i * 0.04) + 's';
        });
    }, 50);


    document.body.style.overflow = '';


    var formTask = document.getElementById('formTask');
    if (formTask) {
        formTask.addEventListener('submit', function() {
            var taskActionEl = document.getElementById('taskAction');
            var isUpdate = taskActionEl && taskActionEl.value === 'update';
            var overlay = document.getElementById('pageLoadingOverlay');
            if (overlay) {
                var overlayText = overlay.querySelector('.page-loading-text');
                if (overlayText) overlayText.textContent = isUpdate ? 'Mengupdate Task...' : 'Menyimpan Task...';
                overlay.classList.add('show');
            }
            var btnSub = document.getElementById('btnSubmitTask');
            if (btnSub) {
                btnSub.disabled = true;
                btnSub.innerHTML = '<span style="display:inline-block;width:14px;height:14px;border:2px solid #ffffff;border-top-color:transparent;border-radius:50%;animation:spin 0.7s linear infinite;vertical-align:-2px;margin-right:6px;"></span> ' + (isUpdate ? 'Mengupdate...' : 'Menyimpan...');
            }
        });
    }


    document.querySelectorAll('.custom-modal-backdrop').forEach(function(mb) {
        mb.addEventListener('click', function(e) {
            if (e.target === mb) {
                if (typeof e.preventDefault === 'function') e.preventDefault();
                if (typeof e.stopPropagation === 'function') e.stopPropagation();
                closeModal(mb.id);
            }
        });
    });


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

                var rect = wrapper.getBoundingClientRect();
                var spaceBelow = window.innerHeight - rect.bottom;
                var spaceAbove = rect.top;
                var menuHeight = 220;
                if (spaceBelow < menuHeight && spaceAbove > spaceBelow) {
                    wrapper.classList.add('open-above');
                } else {
                    wrapper.classList.remove('open-above');
                }
                wrapper.classList.add('open');
                var colWrapper = wrapper.closest('[class*="col-"]');
                if (colWrapper) {
                    colWrapper.style.zIndex = '99999';
                    colWrapper.style.position = 'relative';
                }
                var parentGroup = wrapper.closest('.form-group') || wrapper.parentElement;
                if (parentGroup) {
                    parentGroup.style.zIndex = '99999';
                    parentGroup.style.position = 'relative';
                }
                var parentBox = wrapper.closest('.p-3') || wrapper.closest('.modal-body');
                if (parentBox) {
                    parentBox.style.zIndex = '99999';
                    parentBox.style.position = 'relative';
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
        document.querySelectorAll('.form-group, [class*="col-"], .p-3, .modal-body').forEach(function(el) {
            if (el.style.zIndex === '99999' || el.style.zIndex === '9999') {
                el.style.zIndex = '';
                if (el.style.position === 'relative') el.style.position = '';
            }
        });
    }

    document.addEventListener('click', function() {
        closeAllCustomDropdowns();
    });

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

    function initAllCustomTaskDropdowns() {
        buildCustomDropdown('taskAssignedToSelect');
    }


    initAllCustomTaskDropdowns();

    function getNextSortOrderForDate(targetDate) {
        if (!targetDate) targetDate = new Date().toISOString().split('T')[0];
        var dateGroups = document.querySelectorAll('.date-group');
        var taskCount = 0;

        dateGroups.forEach(function(group) {
            var addBtn = group.querySelector('.add-task-card');
            if (addBtn && addBtn.getAttribute('onclick') && addBtn.getAttribute('onclick').indexOf(targetDate) !== -1) {
                var cards = group.querySelectorAll('.task-card:not(.add-task-card)');
                taskCount = cards.length;
            }
        });

        return taskCount + 1;
    }

    window.toggleFormatPill = function(btn, event) {
        if (event) {
            if (typeof event.preventDefault === 'function') event.preventDefault();
            if (typeof event.stopPropagation === 'function') event.stopPropagation();
        }
        btn.classList.toggle('selected');
        window.updateContentTypeInputValue();
    };

    window.updateContentTypeInputValue = function() {
        var selected = [];
        document.querySelectorAll('#multiFormatContainer .format-pill-btn.selected').forEach(function(btn) {
            selected.push(btn.getAttribute('data-value'));
        });
        var input = document.getElementById('taskContentTypeInput');
        if (input) {
            input.value = selected.join(', ');
            if (selected.length === 0) {
                input.setCustomValidity('Pilih minimal 1 Jenis Format');
            } else {
                input.setCustomValidity('');
            }
        }
        var countEl = document.getElementById('selectedFormatCount');
        if (countEl) {
            countEl.textContent = selected.length + ' Dipilih';
        }
    };

    window.setTaskFormatTypes = function(typesString) {
        var types = typesString ? typesString.split(',').map(function(s) { return s.trim(); }) : [];
        document.querySelectorAll('#multiFormatContainer .format-pill-btn').forEach(function(btn) {
            var val = btn.getAttribute('data-value');
            if (types.indexOf(val) !== -1) {
                btn.classList.add('selected');
            } else {
                btn.classList.remove('selected');
            }
        });
        window.updateContentTypeInputValue();
    };

    window.openModal = function(id, taskId, mode, date) {
        if (window.event) {
            if (typeof window.event.stopPropagation === 'function') window.event.stopPropagation();
        }
        var modal = document.getElementById(id);
        if (!modal) return;

        if (id === 'modalTask') {
            var form = document.getElementById('formTask');
            if (form) {
                form.action = '<?= BASE_URL ?>/timeline/store';
            }

            if (mode === 'edit' && taskId) {
                var titleEl = document.getElementById('modalTaskTitle');
                if (titleEl) titleEl.innerHTML = '<i class="bi bi-pencil-square" style="color: var(--tvri-blue, #003399); font-size: 20px; margin-right: 10px; flex-shrink: 0;"></i><span>Edit Task Konten</span>';
                var btnSub = document.getElementById('btnSubmitTask');
                if (btnSub) btnSub.innerHTML = '<i class="bi bi-check-circle me-1"></i> Update Task';
                if (form) form.action = '<?= BASE_URL ?>/timeline/update/' + taskId;

                var taskAct = document.getElementById('taskAction');
                if (taskAct) taskAct.value = 'update';
                var taskIdIn = document.getElementById('taskId');
                if (taskIdIn) taskIdIn.value = taskId;

                fetch('<?= BASE_URL ?>/timeline/get/' + taskId)
                    .then(function(r) { return r.json(); })
                    .then(function(res) {
                        if (res.success) {
                            var d = res.data;
                            if (document.getElementById('taskDateInput')) document.getElementById('taskDateInput').value = d.task_date || '';
                            if (document.getElementById('taskDeadlineInput')) {
                                document.getElementById('taskDeadlineInput').value = d.deadline || '';
                                var fp = document.getElementById('taskDeadlineInput')._flatpickr;
                                if (fp) { fp.setDate(d.deadline || '', true); }
                            }
                            var deadlineActive = d.deadline_active !== undefined ? parseInt(d.deadline_active) : 1;
                            var toggleBtn = document.getElementById('btnDeadlineToggle');
                            var toggleInput = document.getElementById('taskDeadlineActiveInput');
                            if (toggleBtn && toggleInput) {
                                if (deadlineActive === 0) {
                                    toggleBtn.classList.remove('active');
                                    toggleBtn.classList.add('inactive');
                                    toggleBtn.title = 'Aktifkan deadline';
                                    toggleBtn.innerHTML = '<i class="bi bi-toggle-off"></i>';
                                    toggleInput.value = '0';
                                } else {
                                    toggleBtn.classList.remove('inactive');
                                    toggleBtn.classList.add('active');
                                    toggleBtn.title = 'Nonaktifkan deadline';
                                    toggleBtn.innerHTML = '<i class="bi bi-toggle-on"></i>';
                                    toggleInput.value = '1';
                                }
                            }
                            if (document.getElementById('taskStatusSelect')) document.getElementById('taskStatusSelect').value = d.status || 'Belum';
                            if (document.getElementById('taskTitleInput')) document.getElementById('taskTitleInput').value = d.title || '';
                            if (document.getElementById('taskPicNameInput')) document.getElementById('taskPicNameInput').value = d.pic_name || '';
                            if (document.getElementById('taskAssignedToSelect')) {
                                document.getElementById('taskAssignedToSelect').value = d.assigned_to || '';
                                syncCustomDropdown('taskAssignedToSelect');
                            }
                            if (document.getElementById('taskSortOrderInput')) document.getElementById('taskSortOrderInput').value = (d.sort_order !== undefined && d.sort_order !== null) ? d.sort_order : 1;
                            if (document.getElementById('taskMaterialLinkInput')) document.getElementById('taskMaterialLinkInput').value = d.material_link || '';
                            if (document.getElementById('taskEditLinkInput')) document.getElementById('taskEditLinkInput').value = d.edit_link || '';
                            if (document.getElementById('taskCatatanInput')) document.getElementById('taskCatatanInput').value = d.catatan || '';

                            setTaskFormatTypes(d.content_type || '');

                            var existingFileInput = document.getElementById('existingFile');
                            if (existingFileInput) existingFileInput.value = d.file_attachment || '';

                            if (d.file_attachment) {
                                var fi = document.getElementById('fileInfo');
                                if (fi) fi.style.display = 'flex';
                                var fl = document.getElementById('fileLink');
                                if (fl) {
                                    fl.href = d.file_attachment;
                                    var flText = document.getElementById('fileLinkText');
                                    if (flText) {
                                        flText.textContent = d.file_attachment.indexOf('drive.google.com') !== -1 ? 'Lampiran Info Task' : d.file_attachment.split('/').pop();
                                    }
                                }
                            } else {
                                var fi = document.getElementById('fileInfo');
                                if (fi) fi.style.display = 'none';
                            }
                        }
                    })
                    .catch(function() {});
            } else {
                var titleEl = document.getElementById('modalTaskTitle');
                if (titleEl) titleEl.innerHTML = '<i class="bi bi-plus-circle" style="color: var(--tvri-blue, #003399); font-size: 20px; margin-right: 10px; flex-shrink: 0;"></i><span>Tambah Task Harian</span>';
                var btnSub = document.getElementById('btnSubmitTask');
                if (btnSub) btnSub.innerHTML = '<i class="bi bi-check-circle me-1"></i> Simpan Task Baru';
                if (form) {
                    form.action = '<?= BASE_URL ?>/timeline/store';
                    form.reset();
                    syncCustomDropdown('taskAssignedToSelect');
                    var fpDeadline = document.getElementById('taskDeadlineInput');
                    if (fpDeadline && fpDeadline._flatpickr) { fpDeadline._flatpickr.clear(); }
                    var toggleBtn = document.getElementById('btnDeadlineToggle');
                    var toggleInput = document.getElementById('taskDeadlineActiveInput');
                    if (toggleBtn && toggleInput) {
                        toggleBtn.classList.remove('active');
                        toggleBtn.classList.add('inactive');
                        toggleBtn.title = 'Aktifkan deadline';
                        toggleBtn.innerHTML = '<i class="bi bi-toggle-off"></i>';
                        toggleInput.value = '0';
                    }
                    var fpCal = document.getElementById('cal_date');
                    if (fpCal && fpCal._flatpickr) { fpCal._flatpickr.clear(); }
                }

                var taskAct = document.getElementById('taskAction');
                if (taskAct) taskAct.value = 'store';
                var taskIdIn = document.getElementById('taskId');
                if (taskIdIn) taskIdIn.value = '';
                var fi = document.getElementById('fileInfo');
                if (fi) fi.style.display = 'none';

                var targetDate = date || (document.getElementById('taskDateInput') ? document.getElementById('taskDateInput').value : new Date().toISOString().split('T')[0]);
                if (document.getElementById('taskDateInput')) {
                    document.getElementById('taskDateInput').value = targetDate;
                }

                var autoSortOrder = getNextSortOrderForDate(targetDate);
                if (document.getElementById('taskSortOrderInput')) {
                    document.getElementById('taskSortOrderInput').value = autoSortOrder;
                }

                setTaskFormatTypes('');
            }
        }

        if (id === 'modalCalendar') {
            var calInput = document.getElementById('cal_date');
            if (calInput) {
                if (!calInput.value) {
                    var today = new Date().toISOString().split('T')[0];
                    calInput.value = today;
                    if (calInput._flatpickr) { calInput._flatpickr.setDate(today, true); }
                }
            }
        }

        if (modal._closeTimer) { clearTimeout(modal._closeTimer); modal._closeTimer = null; }
        modal.classList.remove('closing');
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
    };

    window.closeModal = function(id) {
        if (window.event) {
            if (typeof window.event.stopPropagation === 'function') window.event.stopPropagation();
        }
        var animateClose = function(m) {
            if (!m || m.classList.contains('closing')) return;
            m.classList.add('closing');
            if (m._closeTimer) clearTimeout(m._closeTimer);
            m._closeTimer = setTimeout(function() {
                m.classList.remove('active');
                m.classList.remove('show');
                m.classList.remove('closing');
                m._closeTimer = null;
            }, 250);
        };
        if (id) {
            animateClose(document.getElementById(id));
        } else {
            document.querySelectorAll('.custom-modal-backdrop').forEach(animateClose);
        }
        document.body.style.overflow = '';
    };

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeModal();
        }
    });

    window.viewTask = function(event, id) {
        if (event) {
            if (typeof event.preventDefault === 'function') event.preventDefault();
            if (typeof event.stopPropagation === 'function') event.stopPropagation();
        }
        var modal = document.getElementById('modalDetail');
        var body = document.getElementById('detailModalBody');
        var footer = document.getElementById('detailModalFooter');

        body.innerHTML = '<div style="text-align:center;padding:40px 0;color:#64748b;"><div class="spinner" style="width:32px;height:32px;border:3px solid #e0e0e0;border-top-color:#1a237e;border-radius:50%;animation:spin 0.8s linear infinite;margin:0 auto 12px;"></div>Memuat detail task...</div>';
        footer.innerHTML = '<button type="button" class="btn btn-ghost" onclick="closeModal(\'modalDetail\')">Batal</button>';

        if (modal._closeTimer) { clearTimeout(modal._closeTimer); modal._closeTimer = null; }
        modal.classList.remove('closing');
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';

        fetch('<?= BASE_URL ?>/timeline/get/' + id)
            .then(function(r) { return r.json(); })
            .then(function(res) {
                if (!res.success) {
                    body.innerHTML = '<div style="text-align:center;padding:30px;color:#e53935;"><i class="bi bi-exclamation-triangle fs-2 d-block mb-2"></i>Gagal memuat detail task</div>';
                    return;
                }
                var d = res.data;
                var stColor = matchStatusColor(d.status);
                var stLabel = getStatusDisplayLabel(d.status);
                var showWorkflow = (d.status === 'Approved' || d.status === 'Scheduled' || d.status === 'Publish' || d.status === 'Selesai');
                var showScheduleInfo = showWorkflow && d.status !== 'Approved';
                var isLockedDetail = showWorkflow || d.status === 'Pending Approval';

                var html = '';
                html += '<div style="margin-bottom: 20px; padding-bottom: 16px; border-bottom: 1px solid #f1f5f9;">';
                html += '  <div class="d-flex align-items-center gap-2 mb-2">';
                html += '    <span style="background:#e8eaf6; color:#1a237e; font-size:11px; font-weight:700; padding:4px 10px; border-radius:6px; text-transform:uppercase;">' + escHtml(d.content_type || 'KONTEN') + '</span>';
                html += '    <span style="background:' + stColor + '20; color:' + stColor + '; font-size:10px; font-weight:700; padding:4px 10px; border-radius:20px; text-transform:uppercase; margin-left:auto; border:1px solid ' + stColor + '40;">' + stLabel + '</span>';
                html += '  </div>';
                html += '  <h4 style="font-size: 18px; font-weight: 700; color: #0f172a; margin: 0; line-height: 1.4;">' + escHtml(d.title || '(Belum ada judul)') + '</h4>';
                html += '</div>';

                html += '<div class="row g-3 mb-4">';
                html += '  <div class="col-6"><div style="background:#f8fafc; padding:12px 14px; border-radius:10px; border:1px solid #e2e8f0;"><span style="font-size:11px; color:#64748b; font-weight:600; text-transform:uppercase; display:block; margin-bottom:2px;"><i class="bi bi-person me-1"></i> PIC (Penanggung Jawab)</span><strong style="font-size:13px; color:#0f172a;">' + escHtml(d.pic_name || '-') + '</strong></div></div>';
                if (d.deadline) {
                    html += '  <div class="col-6"><div style="background:#fff5f5; padding:12px 14px; border-radius:10px; border:1px solid #fed7d7;"><span style="font-size:11px; color:#c53030; font-weight:600; text-transform:uppercase; display:block; margin-bottom:2px;"><i class="bi bi-clock me-1"></i> Deadline</span><strong style="font-size:13px; color:#9b2c2c;">' + formatDateDisplay(d.deadline) + '</strong></div></div>';
                }
                html += '</div>';

                if (d.catatan) {
                    html += '<div class="mb-4">';
                    html += '  <div class="d-flex justify-content-between align-items-center mb-2"><span style="font-size:12px; font-weight:700; color:#334155; text-transform:uppercase;"><i class="bi bi-info-circle me-1"></i> Catatan & Instruksi</span></div>';
                    html += '  <div style="background:#f1f5f9; border-left:4px solid #1a237e; border-radius:8px; padding:14px 16px; font-size:13px; color:#1e293b; line-height:1.6; white-space:pre-wrap;">' + escHtml(d.catatan) + '</div>';
                    html += '</div>';
                }

                if (d.material_link || d.edit_link || d.file_attachment) {
                    html += '<div class="mb-2">';
                    html += '  <span style="font-size:12px; font-weight:700; color:#334155; text-transform:uppercase; display:block; margin-bottom:8px;"><i class="bi bi-link-45deg me-1"></i> Tautan & Lampiran</span>';
                    html += '  <div class="d-flex align-items-center gap-2 flex-wrap">';
                    if (d.material_link) {
                        html += '    <a href="' + escHtml(d.material_link) + '" target="_blank" class="btn-task-link material" title="Buka Link Materi" onclick="event.stopPropagation()"><i class="bi bi-folder2-open"></i> Link Materi</a>';
                    }
                    if (d.edit_link) {
                        html += '    <a href="' + escHtml(d.edit_link) + '" target="_blank" class="btn-task-link edit" title="Buka Link Edit" onclick="event.stopPropagation()"><i class="bi bi-pencil-square"></i> Link Edit</a>';
                    }
                    if (d.file_attachment) {
                        html += '    <a href="' + escHtml(d.file_attachment) + '" target="_blank" class="btn-task-link file" title="Unduh Lampiran Info Task" onclick="event.stopPropagation()"><i class="bi bi-paperclip"></i> Lampiran Info Task</a>';
                    }
                    html += '  </div>';
                    html += '</div>';
                }

                if (showWorkflow) {
                    var resultFile = d.result_file_attachment || (d.result_link ? null : d.file_attachment);
                    var hasResult = d.description_update || d.user_notes || d.result_link || resultFile || d.screenshot_attachment || d.video_attachment;
                    html += '<div class="mb-4">';
                    html += '  <div class="d-flex justify-content-between align-items-center mb-2"><span style="font-size:12px; font-weight:700; color:#334155; text-transform:uppercase;"><i class="bi bi-file-earmark-check me-1"></i> Hasil Pekerjaan</span></div>';
                    if (hasResult) {
                        if (d.description_update) {
                            html += '  <div style="background:#f0fdf4; border-left:4px solid #16a34a; border-radius:8px; padding:12px 14px; font-size:13px; color:#14532d; line-height:1.6; white-space:pre-wrap; margin-bottom:8px;"><span style="font-weight:700; display:block; font-size:11px; text-transform:uppercase; color:#15803d; margin-bottom:4px;">Deskripsi Update</span>' + escHtml(d.description_update) + '</div>';
                        }
                        if (d.user_notes) {
                            html += '  <div style="background:#fffbeb; border-left:4px solid #f59e0b; border-radius:8px; padding:12px 14px; font-size:13px; color:#713f12; line-height:1.6; white-space:pre-wrap; margin-bottom:8px;"><span style="font-weight:700; display:block; font-size:11px; text-transform:uppercase; color:#b45309; margin-bottom:4px;">Catatan User</span>' + escHtml(d.user_notes) + '</div>';
                        }
                        html += '  <div class="d-flex align-items-center gap-2 flex-wrap mt-2">';
                        if (d.result_link) {
                            html += '    <a href="' + escHtml(d.result_link) + '" target="_blank" class="btn-task-link material" title="Buka Link Hasil" onclick="event.stopPropagation()"><i class="bi bi-link-45deg"></i> Link Hasil</a>';
                        }
                        if (resultFile) {
                            html += '    <a href="' + escHtml(resultFile) + '" target="_blank" class="btn-task-link file" title="Unduh File Hasil Pekerjaan" onclick="event.stopPropagation()"><i class="bi bi-file-earmark-arrow-down"></i> File Hasil Pekerjaan</a>';
                        }
                        if (d.screenshot_attachment) {
                            html += '    <a href="' + escHtml(d.screenshot_attachment) + '" target="_blank" class="btn-task-link file" title="Buka Screenshot" onclick="event.stopPropagation()"><i class="bi bi-image"></i> Screenshot</a>';
                        }
                        if (d.video_attachment) {
                            html += '    <a href="' + escHtml(d.video_attachment) + '" target="_blank" class="btn-task-link file" title="Buka Video" onclick="event.stopPropagation()"><i class="bi bi-film"></i> Video</a>';
                        }
                        html += '  </div>';
                    } else {
                        html += '  <div style="background:#f8fafc; border:1px dashed #cbd5e1; border-radius:8px; padding:14px; font-size:13px; color:#64748b; text-align:center;"><i class="bi bi-inbox me-1"></i> Belum ada hasil pekerjaan yang diunggah.</div>';
                    }
                    html += '</div>';

                    if (showScheduleInfo) {
                    html += '<div class="mb-4">';
                    html += '  <div class="d-flex justify-content-between align-items-center mb-2"><span style="font-size:12px; font-weight:700; color:#334155; text-transform:uppercase;"><i class="bi bi-calendar-event me-1"></i> Informasi Schedule / Upload</span></div>';
                    html += '  <div class="row g-2 schedule-info-row">';
                    html += '    <div class="col-6"><div style="background:#eff6ff; padding:10px 12px; border-radius:10px; border:1px solid #bfdbfe;"><span style="font-size:11px; color:#1d4ed8; font-weight:600; text-transform:uppercase; display:block; margin-bottom:2px;"><i class="bi bi-calendar3 me-1"></i> Tanggal Upload</span><strong style="font-size:13px; color:#1e3a8a;">' + formatDateDisplay(d.task_date) + '</strong></div></div>';
                    html += '    <div class="col-6"><div style="background:#eff6ff; padding:10px 12px; border-radius:10px; border:1px solid #bfdbfe;"><span style="font-size:11px; color:#1d4ed8; font-weight:600; text-transform:uppercase; display:block; margin-bottom:2px;"><i class="bi bi-clock me-1"></i> Jam Upload</span><strong style="font-size:13px; color:#1e3a8a;">' + escHtml(d.scheduled_time || '-') + '</strong></div></div>';
                    if (d.submitted_at) {
                        html += '    <div class="col-6"><div style="background:#f8fafc; padding:10px 12px; border-radius:10px; border:1px solid #e2e8f0;"><span style="font-size:11px; color:#64748b; font-weight:600; text-transform:uppercase; display:block; margin-bottom:2px;"><i class="bi bi-send me-1"></i> Dikirim</span><strong style="font-size:13px; color:#0f172a;">' + formatDateTime(d.submitted_at) + '</strong></div></div>';
                    }
                    if (d.approved_at) {
                        html += '    <div class="col-6"><div style="background:#f0fdf4; padding:10px 12px; border-radius:10px; border:1px solid #bbf7d0;"><span style="font-size:11px; color:#15803d; font-weight:600; text-transform:uppercase; display:block; margin-bottom:2px;"><i class="bi bi-check-circle me-1"></i> Disetujui</span><strong style="font-size:13px; color:#166534;">' + formatDateTime(d.approved_at) + '</strong></div></div>';
                    }
                    html += '  </div>';
                    html += '</div>';
                    }
                }

                body.innerHTML = html;

                footer.innerHTML = '<button type="button" class="btn btn-ghost" onclick="closeModal(\'modalDetail\')">Batal</button>' +
                    (isLockedDetail ? '' : '<button type="button" class="btn btn-primary" style="border-radius:10px; font-weight:600;" onclick="closeModal(\'modalDetail\'); openModal(\'modalTask\', ' + d.id + ', \'edit\')"><i class="bi bi-pencil me-1"></i> Edit Task Ini</button>');
            })
            .catch(function() {
                body.innerHTML = '<div style="text-align:center;padding:30px;color:#e53935;"><i class="bi bi-exclamation-triangle fs-2 d-block mb-2"></i>Gagal memuat detail task</div>';
            });
    };

    function matchStatusColor(status) {
        var map = {
            'Belum': '#dc2626',
            'Assigned': '#dc2626',
            'In Progress': '#dc2626',
            'Proses': '#dc2626',
            'Need Revision': '#dc2626',
            'Pending Approval': '#f97316',
            'Approved': '#22c55e',
            'Scheduled': '#3b82f6',
            'Publish': '#10b981',
            'Selesai': '#10b981',
            'Belum Selesai': '#dc2626'
        };
        return map[status] || '#dc2626';
    }

    function getStatusDisplayLabel(status) {
        var map = {
            'Belum': 'Belum',
            'Assigned': 'Belum',
            'In Progress': 'Belum',
            'Proses': 'Belum',
            'Need Revision': 'Belum',
            'Pending Approval': 'Menunggu Approval',
            'Approved': 'Approved',
            'Scheduled': 'Schedule',
            'Publish': 'Selesai',
            'Selesai': 'Selesai',
            'Belum Selesai': 'Belum Selesai'
        };
        return map[status] || 'Belum';
    }

    function getStatusIcon(status) {
        var map = {
            'Belum': 'bi-hourglass-split',
            'Assigned': 'bi-hourglass-split',
            'In Progress': 'bi-hourglass-split',
            'Proses': 'bi-hourglass-split',
            'Need Revision': 'bi-hourglass-split',
            'Pending Approval': 'bi-clock-history',
            'Approved': 'bi-check-circle-fill',
            'Scheduled': 'bi-calendar-check',
            'Publish': 'bi-check2-circle',
            'Selesai': 'bi-check2-circle',
            'Belum Selesai': 'bi-exclamation-circle'
        };
        return map[status] || 'bi-hourglass-split';
    }

    function formatDateDisplay(dStr) {
        if (!dStr) return '-';
        var p = dStr.split('-');
        if (p.length !== 3) return dStr;
        return p[2] + '/' + p[1] + '/' + p[0];
    }

    function formatDateTime(dt) {
        if (!dt) return '-';
        var parts = dt.split(' ');
        if (parts.length < 2) return formatDateDisplay(dt);
        return formatDateDisplay(parts[0]) + ' ' + parts[1].substring(0, 5);
    }

    window.updateStatus = function(event, id, status) {
        if (event) {
            if (typeof event.stopPropagation === 'function') event.stopPropagation();
        }
        var formData = new FormData();
        formData.append('id', id);
        formData.append('status', status);
        fetch('<?= BASE_URL ?>/timeline/update-status', { method: 'POST', body: formData })
            .then(function(r) { return r.json(); })
            .then(function(res) {
                if (res.success) {
                    showToast('success', 'Status Berhasil', 'Status task diperbarui');
                    var card = document.querySelector('.task-card[data-id="' + id + '"]');
                    if (card) {
                        var badge = card.querySelector('.task-status-badge');
                        if (badge) {
                            var label = getStatusDisplayLabel(status);
                            badge.className = 'task-status-badge';
                            badge.innerHTML = '<i class="bi ' + getStatusIcon(status) + '"></i> ' + label;
                            if (status === 'Belum' || status === 'Assigned' || status === 'In Progress' || status === 'Proses' || status === 'Need Revision') badge.classList.add('status-belum');
                            else if (status === 'Pending Approval') badge.classList.add('status-menunggu');
                            else if (status === 'Approved') badge.classList.add('status-approved');
                            else if (status === 'Scheduled') badge.classList.add('status-schedule');
                            else if (status === 'Publish' || status === 'Selesai') badge.classList.add('status-selesai');
                            else if (status === 'Belum Selesai') badge.classList.add('status-belum-selesai');
                            else badge.classList.add('status-belum');
                        }
                    }
                }
            });
    };

    window.deleteTask = function(event, id) {
        if (event) {
            if (typeof event.stopPropagation === 'function') event.stopPropagation();
        }
        document.getElementById('confirmModalMessage').textContent = 'Yakin ingin menghapus task ini?';
        var confirmBtn = document.getElementById('confirmModalOk');
        var newBtn = confirmBtn.cloneNode(true);
        confirmBtn.parentNode.replaceChild(newBtn, confirmBtn);
        newBtn.addEventListener('click', function() {
            closeModal('modalConfirm');
            fetch('<?= BASE_URL ?>/timeline/delete/' + id, { method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(function(r) { return r.json(); })
                .then(function(res) {
                    if (res.success) {
                        showToast('success', 'Berhasil', 'Task berhasil dihapus');
                        var card = document.querySelector('.task-card[data-id="' + id + '"]');
                        if (card) {
                            var group = card.closest('.date-group');
                            card.remove();
                            if (group && !group.querySelector('.task-card:not(.add-task-card)')) {
                                group.style.display = 'none';
                            }
                        }
                    } else {
                        showToast('error', 'Gagal', res.message || 'Gagal menghapus task');
                    }
                })
                .catch(function() {
                    showToast('error', 'Gagal', 'Terjadi kesalahan saat menghapus task');
                });
        });
        openModal('modalConfirm');
    };

    window.deletePlanning = function(event, id) {
        if (event) {
            if (typeof event.stopPropagation === 'function') event.stopPropagation();
        }
        if (!confirm('Yakin ingin menghapus konten ini?')) return;
        var formData = new FormData();
        formData.append('<?= CSRF_TOKEN_NAME ?>', '<?= Session::csrfToken() ?>');

        fetch('<?= BASE_URL ?>/planning/' + id + '/delete', {
            method: 'POST',
            body: formData
        })
        .then(function(r) { return r.json(); })
        .then(function(res) {
            if (res.success) {
                window.location.reload();
            } else {
                showToast('error', 'Gagal', res.message || 'Gagal menghapus planning');
            }
        });
    };

    function initDatePickers() {
        if (typeof flatpickr === 'undefined') return;

        function handleCalendarOpen(selectedDates, dateStr, instance) {
            var container = instance.element.closest('.input-group-date');
            if (container) container.classList.add('calendar-open');
            var modalBody = instance.element.closest('.modal-body');
            if (modalBody) {
                modalBody.style.overflowY = 'visible';
            }
        }
        function handleCalendarClose(selectedDates, dateStr, instance) {
            var container = instance.element.closest('.input-group-date');
            if (container) container.classList.remove('calendar-open');
            var modalBody = instance.element.closest('.modal-body');
            if (modalBody) {
                modalBody.style.overflowY = '';
            }
        }

        var commonConfig = {
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
            position: 'auto',
            onOpen: handleCalendarOpen,
            onClose: handleCalendarClose
        };

        var deadlineInput = document.getElementById('taskDeadlineInput');
        if (deadlineInput && !deadlineInput._flatpickr) {
            var fpDeadline = flatpickr(deadlineInput, commonConfig);
            if (fpDeadline.altInput) {
                fpDeadline.altInput.style.width = '';
                fpDeadline.altInput.style.flex = '1';
                fpDeadline.altInput.style.minWidth = '0';
            }
            var btnDeadline = document.getElementById('btnDeadlineIcon');
            if (btnDeadline) {
                btnDeadline.addEventListener('click', function(e) {
                    e.stopPropagation();
                    e.preventDefault();
                    fpDeadline.open();
                });
            }
        }

        var toggleBtn = document.getElementById('btnDeadlineToggle');
        var toggleInput = document.getElementById('taskDeadlineActiveInput');
        if (toggleBtn) {
            toggleBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                e.preventDefault();
                var isActive = toggleBtn.classList.contains('active');
                if (isActive) {
                    toggleBtn.classList.remove('active');
                    toggleBtn.classList.add('inactive');
                    toggleBtn.title = 'Aktifkan deadline';
                    toggleBtn.innerHTML = '<i class="bi bi-toggle-off"></i>';
                    if (toggleInput) toggleInput.value = '0';
                } else {
                    toggleBtn.classList.remove('inactive');
                    toggleBtn.classList.add('active');
                    toggleBtn.title = 'Nonaktifkan deadline';
                    toggleBtn.innerHTML = '<i class="bi bi-toggle-on"></i>';
                    if (toggleInput) toggleInput.value = '1';
                }
            });
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
        if (fpDeadline && fpDeadline.altInput) {
            setupFocusGroup(fpDeadline.altInput);
        }

        var calDateInput = document.getElementById('cal_date');
        if (calDateInput && !calDateInput._flatpickr) {
            var fpCal = flatpickr(calDateInput, commonConfig);
            if (fpCal.altInput) {
                fpCal.altInput.style.width = '';
                fpCal.altInput.style.flex = '1';
                fpCal.altInput.style.minWidth = '0';
                setupFocusGroup(fpCal.altInput);
            }
            var btnCal = document.getElementById('btnCalDateIcon');
            if (btnCal) {
                btnCal.addEventListener('click', function(e) {
                    e.stopPropagation();
                    e.preventDefault();
                    fpCal.open();
                });
            }
        }
    }

    initDatePickers();

    function escHtml(str) {
        if (!str) return '';
        return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }
});

function filterPlanningCardsPerLetter(query) {
    var q = query.toLowerCase().trim();

    if (q !== '' && activeStatusFilter !== 'all') {
        activeStatusFilter = 'all';
        document.querySelectorAll('.stat-item').forEach(function(item) {
            item.classList.remove('active-filter');
        });
        var tlHeader = document.querySelector('.timeline-header');
        if (tlHeader) tlHeader.classList.remove('filter-active');
        var pgHeader = document.querySelector('.page-header');
        if (pgHeader) pgHeader.style.display = '';
    }

    var dateGroups = document.querySelectorAll('.date-group');

    dateGroups.forEach(function(group) {
        var dateHeader = group.querySelector('.date-header');
        var headerText = dateHeader ? dateHeader.textContent.toLowerCase() : '';
        var isHeaderMatch = (q !== '' && headerText.indexOf(q) !== -1);

        var cards = group.querySelectorAll('.task-card:not(.add-task-card)');
        var visibleTaskCount = 0;

        cards.forEach(function(card) {
            card.classList.remove('fade-up');
            card.classList.remove('fade-up-filter');
            var text = card.textContent.toLowerCase();
            if (q === '' || isHeaderMatch || text.indexOf(q) !== -1) {
                card.style.display = '';
                visibleTaskCount++;
            } else {
                card.style.display = 'none';
            }
        });

        if (q === '' || isHeaderMatch || visibleTaskCount > 0) {
            group.style.display = '';
        } else {
            group.style.display = 'none';
        }
    });
}

var activeStatusFilter = 'all';

function filterByStatus() {
    var statuses = Array.from(arguments);

    var filterKey = statuses.join(',');
    if (activeStatusFilter === filterKey) {
        statuses = ['all'];
    }
    activeStatusFilter = (statuses.length === 1 && statuses[0] === 'all') ? 'all' : filterKey;

    var timelineHeader = document.querySelector('.timeline-header');
    document.querySelectorAll('.stat-item').forEach(function(item) {
        item.classList.remove('active-filter');
    });

    if (timelineHeader) {
        if (activeStatusFilter === 'all') {
            timelineHeader.classList.remove('filter-active');
        } else {
            timelineHeader.classList.add('filter-active');
        }
    }

    var pageHeader = document.querySelector('.page-header');
    if (pageHeader) {
        pageHeader.style.display = (activeStatusFilter === 'all') ? '' : 'none';
    }
    if (activeStatusFilter !== 'all') {
        document.querySelectorAll('.stat-item').forEach(function(item) {
            var onclick = item.getAttribute('onclick') || '';
            if (onclick.indexOf(filterByStatus.name) !== -1) {
                var match = true;
                statuses.forEach(function(s) {
                    if (onclick.indexOf("'" + s + "'") === -1 && onclick.indexOf('"' + s + '"') === -1) {
                        match = false;
                    }
                });
                if (match) {
                    item.classList.add('active-filter');
                }
            }
        });
    }

    var searchInput = document.getElementById('planningSearchInput');
    if (searchInput) searchInput.value = '';

    var dateGroups = document.querySelectorAll('.date-group');
    var firstVisibleCard = null;
    var firstDateHeader = null;

    dateGroups.forEach(function(group) {
        var cards = group.querySelectorAll('.task-card:not(.add-task-card)');
        var visibleTaskCount = 0;
        var firstCardInGroup = null;

        cards.forEach(function(card) {
            card.classList.remove('fade-up');
            card.classList.remove('fade-up-filter');
            card.style.animationDelay = '';
        });

        void group.offsetHeight;

        cards.forEach(function(card) {
            if (activeStatusFilter === 'all') {
                card.style.display = '';
                visibleTaskCount++;
                if (!firstCardInGroup) firstCardInGroup = card;
                card.classList.add('fade-up-filter');
                card.style.animationDelay = (visibleTaskCount * 0.04) + 's';
            } else {
                var cardStatus = card.getAttribute('data-status') || '';
                var match = statuses.some(function(s) { return cardStatus === s; });

                if (match) {
                    card.style.display = '';
                    visibleTaskCount++;
                    if (!firstCardInGroup) firstCardInGroup = card;
                    if (!firstVisibleCard) {
                        firstVisibleCard = card;
                        firstDateHeader = group.querySelector('.date-header');
                    }
                    card.classList.add('fade-up-filter');
                    card.style.animationDelay = (visibleTaskCount * 0.08) + 's';
                } else {
                    card.style.display = 'none';
                }
            }
        });

        if (activeStatusFilter === 'all' || visibleTaskCount > 0) {
            group.style.display = '';
        } else {
            group.style.display = 'none';
        }
    });

    var scrollTargetEl = firstDateHeader || firstVisibleCard;
    if (scrollTargetEl) {
        var header = document.querySelector('.timeline-header');
        var headerHeight = header ? header.offsetHeight : 0;
        var scrollOffset = 61 + headerHeight + 12;
        var rect = scrollTargetEl.getBoundingClientRect();
        var scrollTarget = window.scrollY + rect.top - scrollOffset;
        window.scrollTo({ top: Math.max(0, scrollTarget), behavior: 'smooth' });
    }
}


</script>

<script>




let publishInProgress = false;

function openPublishModal(planningId, judul) {
    document.getElementById('publishPlanningId').value = planningId;
    document.getElementById('publishModalTitle').textContent = judul;
    document.getElementById('publishResult').innerHTML = '';
    document.querySelectorAll('.publish-platform-btn').forEach(b => {
        b.disabled = false;
        b.classList.remove('btn-success', 'btn-danger');
    });
    publishInProgress = false;
    document.getElementById('publishModal').style.display = 'flex';
}

function closePublishModal() {
    document.getElementById('publishModal').style.display = 'none';
}

function publishNow(platform) {
    if (publishInProgress) return;
    if (!confirm('Publish konten ini ke ' + platform.charAt(0).toUpperCase() + platform.slice(1) + '?')) return;

    publishInProgress = true;
    const planningId = document.getElementById('publishPlanningId').value;
    const btn = document.getElementById('publishBtn_' + platform);
    const resultDiv = document.getElementById('publishResult');

    btn.disabled = true;
    btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Publishing...';
    resultDiv.innerHTML = '<div class="alert alert-info" style="margin-top:12px;padding:10px;font-size:0.8rem;"><i class="bi bi-hourglass-split"></i> Sedang mengirim ke ' + platform + '...</div>';

    $.ajax({
        url: BASE_URL + '/planning/' + planningId + '/publish-now',
        type: 'POST',
        data: {
            platform: platform,
            [document.querySelector('meta[name=csrf-token]')?.getAttribute('content') ? '_csrf_token' : '_csrf_token']: CSRF_TOKEN
        },
        dataType: 'json',
        success: function(res) {
            publishInProgress = false;
            if (res.success) {
                btn.classList.add('btn-success');
                btn.innerHTML = '<i class="bi bi-check-circle-fill"></i> Berhasil';
                resultDiv.innerHTML = '<div class="alert alert-success" style="margin-top:12px;padding:10px;font-size:0.8rem;"><i class="bi bi-check-circle-fill"></i> ' + res.message + (res.post_id ? '<br><small>Post ID: ' + res.post_id + '</small>' : '') + '</div>';
                setTimeout(() => location.reload(), 2000);
            } else {
                btn.classList.add('btn-danger');
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-x-circle"></i> Gagal � Coba Lagi';
                resultDiv.innerHTML = '<div class="alert alert-danger" style="margin-top:12px;padding:10px;font-size:0.8rem;"><i class="bi bi-exclamation-triangle"></i> ' + res.message + '</div>';
            }
        },
        error: function(xhr) {
            publishInProgress = false;
            btn.classList.add('btn-danger');
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-x-circle"></i> Error � Coba Lagi';
            const msg = xhr.responseJSON ? xhr.responseJSON.message : 'Terjadi kesalahan server (HTTP ' + xhr.status + ')';
            resultDiv.innerHTML = '<div class="alert alert-danger" style="margin-top:12px;padding:10px;font-size:0.8rem;"><i class="bi bi-exclamation-triangle"></i> ' + msg + '</div>';
        }
    });
}
</script>


<div id="publishModal" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);z-index:9999;align-items:center;justify-content:center;">
    <div style="background:var(--card-bg,#fff);border-radius:var(--radius-lg,12px);max-width:520px;width:90%;padding:28px;box-shadow:0 12px 40px rgba(0,0,0,0.2);">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;">
            <h5 style="margin:0;font-weight:700;color:var(--gray-900);"><i class="bi bi-send-fill text-primary"></i> Publish Now</h5>
            <button onclick="closePublishModal()" style="background:none;border:none;font-size:1.4rem;cursor:pointer;color:var(--gray-400);">&times;</button>
        </div>

        <p style="font-size:0.85rem;color:var(--gray-600);margin-bottom:6px;">Konten:</p>
        <p style="font-weight:600;font-size:0.95rem;color:var(--gray-900);margin-bottom:20px;" id="publishModalTitle"></p>

        <input type="hidden" id="publishPlanningId" value="">

        <p style="font-size:0.8rem;color:var(--gray-500);margin-bottom:12px;">Pilih platform untuk publish:</p>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:16px;">

            <button id="publishBtn_facebook" class="btn btn-outline publish-platform-btn" onclick="publishNow('facebook')" style="padding:16px;border-radius:var(--radius-md,8px);text-align:center;">
                <i class="bi bi-facebook" style="font-size:1.5rem;color:#1877F2;"></i>
                <div style="font-weight:600;font-size:0.8rem;margin-top:6px;">Facebook</div>
            </button>

            <button id="publishBtn_instagram" class="btn btn-outline publish-platform-btn" onclick="publishNow('instagram')" style="padding:16px;border-radius:var(--radius-md,8px);text-align:center;">
                <i class="bi bi-instagram" style="font-size:1.5rem;color:#E4405F;"></i>
                <div style="font-weight:600;font-size:0.8rem;margin-top:6px;">Instagram</div>
            </button>

            <button disabled class="btn btn-outline publish-platform-btn" style="padding:16px;border-radius:var(--radius-md,8px);text-align:center;opacity:0.5;">
                <i class="bi bi-tiktok" style="font-size:1.5rem;color:#000;"></i>
                <div style="font-weight:600;font-size:0.8rem;margin-top:6px;color:var(--gray-400);">TikTok<br><small>Belum aktif</small></div>
            </button>

            <button disabled class="btn btn-outline publish-platform-btn" style="padding:16px;border-radius:var(--radius-md,8px);text-align:center;opacity:0.5;">
                <i class="bi bi-youtube" style="font-size:1.5rem;color:#FF0000;"></i>
                <div style="font-weight:600;font-size:0.8rem;margin-top:6px;color:var(--gray-400);">YouTube<br><small>Belum aktif</small></div>
            </button>
        </div>

        <div id="publishResult"></div>

        <div style="text-align:right;margin-top:16px;">
            <button onclick="closePublishModal()" class="btn btn-outline btn-sm">Tutup</button>
        </div>
    </div>
</div>


<div id="pageLoadingOverlay" class="page-loading-overlay" aria-hidden="true">
    <div class="page-loading-box">
        <div class="page-loading-spinner"></div>
        <div class="page-loading-text">Menyimpan Task...</div>
    </div>
</div>
