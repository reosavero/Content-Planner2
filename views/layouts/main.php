<!DOCTYPE html>
<html lang="id" data-theme="<?= Session::get('theme', 'light') ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="<?= Session::csrfToken() ?>">
    <meta name="description" content="TVRI Jawa Timur - Content Planner">
    <meta name="theme-color" content="#003399">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <title><?= $title ?? 'Content Planner' ?></title>

    
    <link rel="shortcut icon" href="<?= BASE_URL ?>/assets/img/favicon-tvri.svg">
    <link rel="apple-touch-icon" href="<?= BASE_URL ?>/assets/img/favicon-tvri.svg">

    
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/bootstrap-icons/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

    
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/tvri-theme.css?v=<?= time() ?>">

    
    <?php if (!empty($cssFiles)): ?>
        <?php foreach ($cssFiles as $css): ?>
            <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/<?= $css ?>?v=<?= APP_VERSION ?>">
        <?php endforeach; ?>
    <?php endif; ?>
</head>
<body class="app-loaded">

    
    <div class="sidebar-backdrop" id="sidebarBackdrop" onclick="toggleSidebar()"></div>

    
    
    
    <aside class="sidebar" id="sidebar">
        
        <div class="sidebar-header">
            <img src="<?= BASE_URL ?>/assets/img/tvri3.svg" alt="TVRI Jatim" class="sidebar-logo">
            <span class="sidebar-brand">Content Planner</span>
            <button class="sidebar-close d-lg-none" onclick="toggleSidebar()" aria-label="Tutup sidebar">
                <i class="bi bi-x"></i>
            </button>
        </div>


        
        <nav class="sidebar-nav" id="sidebarNav">
            <?php
            $currentUrl = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
            $baseUrlPath = parse_url(BASE_URL, PHP_URL_PATH) ?: '';
            
            $isActive = function($pattern) use ($currentUrl, $baseUrlPath) {
                $fullPattern = rtrim($baseUrlPath . $pattern, '/');
                if ($fullPattern === '' || $fullPattern === '/') {
                    return $currentUrl === '/' || $currentUrl === $baseUrlPath;
                }
                return $currentUrl === $fullPattern || str_starts_with($currentUrl, $fullPattern . '/');
            };
            $userRole = Session::get('user_role_slug', '');
            $userId = Session::get('user_id', 0);

            
            $draftCount = Database::fetchColumn(
                "SELECT COUNT(*) FROM planning_konten WHERE status='draft' AND deleted_at IS NULL AND created_by=?",
                [$userId]
            ) ?: 0;

            $reviewCount = 0;
            if (in_array($userRole, ['superadmin', 'admin'])) {
                
                $reviewCount = Database::fetchColumn(
                    "SELECT COUNT(*) FROM timeline_tasks WHERE status = 'Pending Approval'"
                ) ?: 0;
            }

            $recheckCount = 0;
            if ($userRole === 'superadmin') {
                $recheckCount = Database::fetchColumn(
                    "SELECT COUNT(*) FROM planning_konten WHERE status='approved' AND recheck_needed=1 AND deleted_at IS NULL"
                ) ?: 0;
            }

            
            $sections = [];

            if ($userRole === 'magang') {
                $magangPendingCount = Database::fetchColumn(
                    "SELECT COUNT(*) FROM timeline_tasks WHERE (assignee_id = ? OR assigned_to = ? OR LOWER(TRIM(pic_name)) = LOWER(TRIM(?))) AND status = 'Pending Approval'",
                    [$userId, $userId, Session::get('user_name')]
                ) ?: 0;

                
                $sections[] = [
                    'title' => '',
                    'items' => [
                        ['label' => 'Dashboard Tugas', 'icon' => 'bi-grid-1x2-fill', 'url' => '/dashboard'],
                        ['label' => 'Status Approval', 'icon' => 'bi-check2-square', 'url' => '/approval', 'badge' => $magangPendingCount, 'badge_plain' => true],
                        ['label' => 'Archive', 'icon' => 'bi-archive-fill', 'url' => '/archive'],
                    ]
                ];
            } else {
                
                $sections[] = [
                    'title' => '',
                    'items' => [
                        ['label' => 'Dashboard', 'icon' => 'bi-grid-1x2-fill', 'url' => '/dashboard'],
                    ]
                ];

                $kontenItems = [
                    ['label' => 'Planning Konten', 'icon' => 'bi-calendar-check-fill', 'url' => '/planning'],
                ];

                if (in_array($userRole, ['superadmin', 'admin'])) {
                    $kontenItems[] = ['label' => 'Approval', 'icon' => 'bi-check2-square', 'url' => '/approval', 'badge' => $reviewCount + $recheckCount, 'badge_plain' => true];

                    $kontenItems[] = ['label' => 'Scheduler', 'icon' => 'bi-clock-history', 'url' => '/scheduler'];
                    $kontenItems[] = ['label' => 'Kalender', 'icon' => 'bi-calendar3', 'url' => '/calendar'];
                    $kontenItems[] = ['label' => 'Posting', 'icon' => 'bi-send-fill', 'url' => '/posting'];
                }

                $sections[] = [
                    'title' => '',
                    'items' => $kontenItems
                ];
            }

            
            if (in_array($userRole, ['superadmin', 'admin'])) {
                $pendingInternCount = Database::fetchColumn(
                    "SELECT COUNT(*) FROM users u JOIN roles r ON r.id = u.role_id WHERE u.deleted_at IS NULL AND r.slug = 'magang' AND u.approval_status = 'pending'"
                ) ?: 0;

                $sections[] = [
                    'title' => '',
                    'items' => [
                        ['label' => 'Integrasi Sosmed', 'icon' => 'bi-share-fill', 'url' => '/social-media'],
                        ['label' => 'Kelola User', 'icon' => 'bi-person-vcard-fill', 'url' => '/intern-users', 'badge' => $pendingInternCount],
                        ['label' => 'Archive', 'icon' => 'bi-archive-fill', 'url' => '/archive'],
                        ['label' => 'Kategori', 'icon' => 'bi-tags-fill', 'url' => '/master/kategori'],
                        ['label' => 'Platform', 'icon' => 'bi-share', 'url' => '/master/platform'],
                    ]
                ];
            }

            
            if ($userRole === 'superadmin') {
                $sections[] = [
                    'title' => '',
                    'items' => [
                        ['label' => 'Roles', 'icon' => 'bi-shield-fill', 'url' => '/roles'],
                        ['label' => 'Activity Log', 'icon' => 'bi-activity', 'url' => '/activity-logs'],
                        ['label' => 'Pengaturan', 'icon' => 'bi-gear-fill', 'url' => '/settings'],
                    ]
                ];
            }
            ?>
            
            <?php foreach ($sections as $sectionIdx => $section): 
                
                $hasActiveItem = !empty($section['items']) && array_reduce($section['items'], function($carry, $item) use ($isActive) {
                    return $carry || $isActive($item['url']);
                }, false);
                ?>
                <?php if (!empty($section['title'])): ?>
                    <div class="sidebar-section" data-section="<?= $sectionIdx ?>">
                        <button class="sidebar-section-toggle" type="button" aria-expanded="<?= $hasActiveItem ? 'true' : 'false' ?>" aria-controls="section-<?= $sectionIdx ?>-items">
                            <span class="sidebar-section-title"><?= $section['title'] ?></span>
                            <i class="bi bi-chevron-down sidebar-section-arrow"></i>
                        </button>
                        <div class="sidebar-section-items" id="section-<?= $sectionIdx ?>-items">
                            <?php foreach ($section['items'] as $item): ?>
                                <a href="<?= BASE_URL . $item['url'] ?>"
                                   class="sidebar-item <?= $isActive($item['url']) ? 'active' : '' ?>"
                                   data-url="<?= $item['url'] ?>">
                                    <i class="bi <?= $item['icon'] ?>"></i>
                                    <span class="sidebar-item-label"><?= $item['label'] ?></span>
                                    <?php if (!empty($item['badge']) && $item['badge'] > 0): ?>
                                        <span class="sidebar-badge <?= !empty($item['badge_plain']) ? 'sidebar-badge-plain' : '' ?> <?= $item['label'] === 'Approval' && $recheckCount > 0 ? 'sidebar-badge-warning' : '' ?>">
                                            <?= $item['badge'] > 99 ? '99+' : $item['badge'] ?>
                                        </span>
                                    <?php endif; ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php else: ?>
                    
                    <?php foreach ($section['items'] as $item): ?>
                        <a href="<?= BASE_URL . $item['url'] ?>"
                           class="sidebar-item <?= $isActive($item['url']) ? 'active' : '' ?>"
                           data-url="<?= $item['url'] ?>">
                            <i class="bi <?= $item['icon'] ?>"></i>
                            <span class="sidebar-item-label"><?= $item['label'] ?></span>
                            <?php if (!empty($item['badge']) && $item['badge'] > 0): ?>
                                <span class="sidebar-badge <?= !empty($item['badge_plain']) ? 'sidebar-badge-plain' : '' ?> <?= $item['label'] === 'Approval' && $recheckCount > 0 ? 'sidebar-badge-warning' : '' ?>">
                                    <?= $item['badge'] > 99 ? '99+' : $item['badge'] ?>
                                </span>
                            <?php endif; ?>
                        </a>
                    <?php endforeach; ?>
                <?php endif; ?>
            <?php endforeach; ?>
        </nav>

        
        <div class="sidebar-footer">
            <div class="sidebar-user" onclick="window.location.href='<?= BASE_URL ?>/profile'" role="button" tabindex="0">
                <div class="sidebar-user-avatar-wrapper">
                    <?php $avatar = Session::get('user_avatar'); ?>
                    <?php if ($avatar): ?>
                        <img src="<?= BASE_URL ?>/uploads/<?= $avatar ?>" alt="" class="sidebar-user-avatar">
                    <?php else: ?>
                        <div class="sidebar-user-avatar sidebar-user-avatar-initials">
                            <?= strtoupper(substr(Session::get('user_name', 'U'), 0, 1)) ?>
                        </div>
                    <?php endif; ?>
                    <span class="sidebar-user-status" title="Online"></span>
                </div>
                <div class="sidebar-user-info">
                    <div class="sidebar-user-name"><?= htmlspecialchars(Session::get('user_name', '')) ?></div>
                    <div class="sidebar-user-role"><?= htmlspecialchars(Session::get('user_role_name', ucwords(str_replace('_', ' ', $userRole)))) ?></div>
                </div>
            </div>
            <a href="<?= BASE_URL ?>/logout" class="sidebar-logout-btn" title="Logout">
                <i class="bi bi-box-arrow-right"></i> Logout
            </a>
        </div>
    </aside>

    
    
    
    <div class="main-content" id="mainContent">
        
        
        <header class="topbar" id="topbar">
            <div class="topbar-left">
                <button class="topbar-btn sidebar-toggle-btn d-lg-none" onclick="toggleSidebar()" aria-label="Toggle Sidebar">
                    <i class="bi bi-list"></i>
                </button>
                <div class="topbar-page-title">
                    <h5 class="topbar-title-text"><?= $topbarTitle ?? htmlspecialchars($title ?? 'Dashboard') ?></h5>
                    <?php if (!empty($topbarSubtitle)): ?>
                        <span class="topbar-title-sub"><?= htmlspecialchars($topbarSubtitle) ?></span>
                    <?php endif; ?>
                </div>
            </div>

            <div class="topbar-right ms-auto" style="margin-left: auto !important;">
                
                <button class="topbar-btn" onclick="toggleTheme()" title="Ganti tema (Night Mode)">
                    <i class="bi bi-moon-stars" id="themeIcon"></i>
                </button>

                
                <div class="topbar-notif-wrapper">
                    <button class="topbar-btn" onclick="var dd=document.getElementById('notifDropdown');dd.classList.toggle('show');if(typeof loadNotifications==='function'&&dd.classList.contains('show'))loadNotifications();" title="Notifikasi">
                        <i class="bi bi-bell"></i>
                        <span class="topbar-notif-dot" id="notifDot" style="display:none;"></span>
                    </button>
                    <div class="topbar-notif-dropdown" id="notifDropdown">
                        <div class="topbar-notif-header">
                            <span class="font-semibold">Notifikasi</span>
                            <span class="text-tertiary font-medium text-xs" id="notifCountLabel">(0)</span>
                            <button onclick="markAllNotifRead()" class="btn btn-sm btn-ghost" style="padding:2px 8px;font-size:var(--text-xs);margin-left:auto;" title="Tandai semua dibaca">
                                <i class="bi bi-check-all"></i>
                            </button>
                        </div>
                        <div id="notifList" class="topbar-notif-list">
                            <div class="empty-state py-4" style="min-height:120px;">
                                <i class="bi bi-bell-slash" style="font-size:1.5rem;color:var(--text-tertiary);"></i>
                                <p style="font-size:var(--text-xs);color:var(--text-tertiary);margin-top:8px;">Tidak ada notifikasi</p>
                            </div>
                        </div>
                        <div class="topbar-notif-footer">
                            <a href="<?= BASE_URL ?>/activity-logs" class="text-xs">Lihat semua</a>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        
        <div class="page-content" id="pageContent">
            
            <?php
                
                $hidePageHeader = false;
                $roleSlug = Session::get('user_role_slug');
                if ($roleSlug === 'magang') {
                    $currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
                    $magangPaths = ['/dashboard', '/approval', '/archive'];
                    foreach ($magangPaths as $p) {
                        if (strpos($currentPath, $p) === 0) {
                            $hidePageHeader = true;
                            break;
                        }
                    }
                }
            ?>
            <?php if ((!empty($title) || !empty($pageActions)) && !$hidePageHeader): ?>
            <div class="page-header">
                <div class="page-header-content">
                    <?php if (!empty($subtitle)): ?>
                        <div class="page-header-meta"><?= htmlspecialchars($subtitle) ?></div>
                    <?php endif; ?>
                </div>
                <?php if (!empty($pageActions)): ?>
                    <div class="page-actions">
                        <?php foreach ($pageActions as $action): ?>
                            <button class="btn btn-<?= $action['variant'] ?? 'primary' ?> btn-sm"
                                    onclick="<?= htmlspecialchars($action['onclick'] ?? '') ?>"
                                    <?= !empty($action['title']) ? 'title="' . htmlspecialchars($action['title']) . '"' : '' ?>>
                                <?php if (!empty($action['icon'])): ?><i class="bi <?= $action['icon'] ?>"></i><?php endif; ?>
                                <?= htmlspecialchars($action['label'] ?? '') ?>
                            </button>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            
            <?= $content ?? '' ?>
            
        </div>

        
    </div>

    
    <div class="toast-container" id="toastContainer">
        <?php $flashMessages = Session::getFlash(); ?>
        <?php if (!empty($flashMessages)): ?>
            <?php $_fi = 0; ?>
            <?php foreach ($flashMessages as $flash): 
                $type = $flash['type'] === 'error' ? 'error' : ($flash['type'] === 'danger' ? 'error' : $flash['type']);
                $icon = $type === 'success' ? 'check-circle-fill' : ($type === 'error' ? 'x-circle-fill' : ($type === 'warning' ? 'exclamation-triangle-fill' : 'info-circle-fill'));
            ?>
                <div class="sonner-toast sonner-toast-<?= $type ?>" id="flashToast-<?= $_fi ?>">
                    <span class="sonner-icon">
                        <i class="bi bi-<?= $icon ?>"></i>
                    </span>
                    <div class="sonner-content">
                        <div class="sonner-message"><?= htmlspecialchars($flash['message']) ?></div>
                    </div>
                </div>
            <?php $_fi++; ?>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <?php if (!empty($flashMessages)): ?>
    <script>
    (function() {
        var toasts = document.querySelectorAll('#toastContainer .sonner-toast');
        for (var i = 0; i < toasts.length; i++) {
            (function(t) {
                setTimeout(function() {
                    if (!t || !t.parentNode) return;
                    t.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
                    t.style.opacity = '0';
                    t.style.transform = 'translateY(-24px) scale(0.95)';
                    setTimeout(function() {
                        if (t && t.parentNode) t.parentNode.removeChild(t);
                    }, 350);
                }, 3500);
            })(toasts[i]);
        }
    })();
    </script>
    <?php endif; ?>

    
    <div class="loading-overlay" id="loadingOverlay">
        <div class="loading-spinner">
            <div class="spinner"></div>
        </div>
    </div>

    
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script>
        window.BASE_URL = '<?= BASE_URL ?>';
        window.CSRF_TOKEN = '<?= Session::csrfToken() ?>';
        window.USER_ID = <?= $userId ?>;
        window.USER_ROLE = '<?= $userRole ?>';
        window.IS_ADMIN = <?= in_array($userRole, ['superadmin', 'admin']) ? 'true' : 'false' ?>;
        window.IS_MOBILE = window.innerWidth < 1024;
    </script>
    <script src="<?= BASE_URL ?>/assets/js/tvri-app.js?v=<?= APP_VERSION ?>"></script>
    
    <?php if (!empty($jsFiles)): ?>
        <?php foreach ($jsFiles as $js): ?>
            <script src="<?= BASE_URL ?>/assets/js/<?= $js ?>?v=<?= APP_VERSION ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>

