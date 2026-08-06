<!DOCTYPE html>
<html lang="id" data-theme="<?= Session::get('theme', 'light') ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="theme-color" content="#005BAC" />
    <meta name="apple-mobile-web-app-capable" content="yes" />
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent" />
    <meta name="csrf-token" content="<?= Session::csrfToken() ?>">
    <title><?= $title ?? 'Content Planner - TVRI Jawa Timur' ?></title>
    <link rel="shortcut icon" href="<?= BASE_URL ?>/assets/img/favicon-tvri.svg">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/bootstrap-icons/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/tvri-theme.css?v=<?= APP_VERSION ?>">
    <style>
        html { overflow: hidden; height: 100%; }
        body { overflow: auto; height: 100%; -webkit-overflow-scrolling: touch; overscroll-behavior: none; opacity: 1; font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; font-weight: 450; }
        button, a, [role="button"] { -webkit-user-select: none; user-select: none; }
        
        
        .auth-loading {
            position: fixed;
            inset: 0;
            z-index: 9999;
            display: none;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #003B71, #005BAC, #0077D6);
        }
        .auth-loading.show { display: flex; }
        .auth-loading .spinner {
            width: 48px;
            height: 48px;
            border: 4px solid rgba(255,255,255,0.3);
            border-top-color: #ffffff;
            border-radius: 50%;
            animation: authSpin 0.8s linear infinite;
            margin: 0 auto 16px;
        }
        .auth-loading p {
            color: rgba(255,255,255,0.7);
            font-size: 14px;
            text-align: center;
        }
        @keyframes authSpin {
            to { transform: rotate(360deg); }
        }
        
        
        .auth-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #003B71 0%, #005BAC 50%, #0077D6 100%);
            padding: 20px;
            position: relative;
            overflow: hidden;
        }
        .auth-wrapper {
            width: 100%;
            max-width: 440px;
            position: relative;
            z-index: 2;
        }
        .auth-header {
            text-align: center;
            margin-bottom: 32px;
        }
        .auth-header-logo {
            display: block;
            margin: 0 auto 16px;
            width: 90px;
            height: auto;
        }
        .auth-header-title {
            font-size: 28px;
            font-weight: 800;
            color: #ffffff;
            margin: 0 0 4px 0;
            letter-spacing: -0.5px;
        }
        .auth-header-tagline {
            font-size: 14px;
            color: rgba(255,255,255,0.8);
            margin: 0 0 2px 0;
        }
        .auth-header-location {
            font-size: 13px;
            color: rgba(255,255,255,0.6);
            margin: 0;
            font-weight: 500;
        }
        .auth-card {
            background: rgba(255,255,255,0.95);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-radius: 20px;
            padding: 32px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.2), 0 8px 24px rgba(0,0,0,0.1);
            border: 1px solid rgba(255,255,255,0.3);
        }
        .auth-card-header {
            margin-bottom: 24px;
            text-align: center;
        }
        .auth-title {
            font-size: 22px;
            font-weight: 600;
            color: #1a1d29;
            margin: 0 0 4px 0;
        }
        .auth-subtitle {
            font-size: 14px;
            color: #6b6b76;
            margin: 0;
        }
        .auth-link {
            color: #005BAC;
            font-size: 13px;
            text-decoration: none;
            font-weight: 500;
        }
        .auth-link:hover {
            text-decoration: underline;
        }
        
        
        .auth-bubbles {
            position: absolute;
            inset: 0;
            pointer-events: none;
            overflow: hidden;
        }
        .auth-bubble {
            position: absolute;
            border-radius: 50%;
            background: rgba(255,255,255,0.05);
        }
        .auth-bubble-1 { width: 400px; height: 400px; top: -100px; right: -100px; }
        .auth-bubble-2 { width: 300px; height: 300px; bottom: -50px; left: -80px; }
        .auth-bubble-3 { width: 200px; height: 200px; top: 30%; right: -60px; }
        .auth-bubble-4 { width: 150px; height: 150px; bottom: 20%; left: 10%; }
        .auth-bubble-5 { width: 100px; height: 100px; top: 15%; left: 5%; }
        .auth-bubble-6 { width: 80px; height: 80px; bottom: 10%; right: 15%; }
        .auth-bubble-7 { width: 60px; height: 60px; top: 40%; left: 30%; }
        
        
        .form-group {
            margin-bottom: 20px;
        }
        .form-label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 6px;
        }
        .input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
            width: 100%;
        }
        .input-wrapper .input-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
            pointer-events: none;
            flex-shrink: 0;
            z-index: 2;
            transition: color 0.2s ease;
        }
        .input-wrapper:focus-within .input-icon {
            color: #005BAC;
        }
        .input-wrapper .form-control,
        .form-group input.form-control {
            width: 100%;
            height: 46px;
            padding: 12px 42px 12px 42px !important;
            font-size: 14px;
            font-family: inherit;
            color: #1f2937;
            background: #f9fafb;
            border: 1.5px solid #e5e7eb;
            border-radius: 12px;
            outline: none;
            transition: all 0.2s ease;
            box-sizing: border-box;
        }
        .input-wrapper .form-control:focus,
        .form-group input.form-control:focus {
            border-color: #005BAC;
            background: #ffffff;
            box-shadow: 0 0 0 3px rgba(0, 91, 172, 0.1);
        }
        .input-wrapper .password-toggle {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #9ca3af;
            cursor: pointer;
            padding: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 2;
            border-radius: 6px;
            transition: color 0.2s ease;
        }
        .input-wrapper .password-toggle:hover {
            color: #1f2937;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 12px 24px;
            font-size: 14px;
            font-weight: 600;
            font-family: inherit;
            border: none;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.2s ease;
            width: 100%;
            text-decoration: none;
        }
        .btn-primary {
            background: linear-gradient(135deg, #003399, #005BAC);
            color: #ffffff;
        }
        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 8px 24px rgba(0,51,153,0.3);
        }
        .btn-primary:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
        }
        .btn-loader { display: none; align-items: center; gap: 6px; }
        .spin { animation: spin 1s linear infinite; }
        @keyframes spin { to { transform: rotate(360deg); } }
        
        
        .sonner-close { display: none !important; }
        
        
        @media (max-width: 480px) {
            .auth-card { padding: 24px 20px; }
            .auth-header-title { font-size: 24px; }
            .auth-wrapper { max-width: 100%; }
        }
    </style>
</head>
<body>
    
    <div class="auth-loading" id="authLoading">
        <div class="text-center">
            <div class="spinner"></div>
            <p>Memuat...</p>
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

    <div class="auth-container">
        
        <div class="auth-bubbles">
            <div class="auth-bubble auth-bubble-1"></div>
            <div class="auth-bubble auth-bubble-2"></div>
            <div class="auth-bubble auth-bubble-3"></div>
            <div class="auth-bubble auth-bubble-4"></div>
            <div class="auth-bubble auth-bubble-5"></div>
            <div class="auth-bubble auth-bubble-6"></div>
            <div class="auth-bubble auth-bubble-7"></div>
        </div>

        
        <div class="auth-wrapper">
            
            <div class="auth-header">
                <img src="<?= BASE_URL ?>/assets/img/tvri3.svg" alt="Logo TVRI" class="auth-header-logo">
                <h1 class="auth-header-title">Content Planner</h1>
                <p class="auth-header-tagline">Website Content Planner &amp; Auto Post Social Media</p>
                <p class="auth-header-location">TVRI Jawa Timur</p>
            </div>

            
            <div class="auth-card">
                
                <div class="auth-card-header">
                    <h3 class="auth-title">Masuk ke Akun</h3>
                    <p class="auth-subtitle">Silakan masuk untuk melanjutkan</p>
                </div>

                
                <?= $content ?? '' ?>
            </div>

        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script>
        const BASE_URL = '<?= BASE_URL ?>';
        const CSRF_TOKEN = '<?= Session::csrfToken() ?>';
        
        function dismissSonnerToast(btn) {
            var toast = btn.closest('.sonner-toast');
            dismissSonnerToastEl(toast);
        }
        
        function dismissSonnerToastEl(toast) {
            if (!toast || !toast.parentNode) return;
            toast.classList.add('toast-out');
            toast.style.transition = 'all 0.3s cubic-bezier(0.16, 1, 0.3, 1)';
            toast.style.opacity = '0';
            toast.style.transform = 'translateY(-24px) scale(0.95)';
            setTimeout(function() {
                if (toast && toast.parentNode) {
                    toast.parentNode.removeChild(toast);
                }
            }, 300);
        }
        
        function initAuthToasts() {
            var toasts = document.querySelectorAll('.toast-container .sonner-toast, .toast-container .toast, .sonner-toast');
            toasts.forEach(function(toast) {
                if (toast.dataset.dismissScheduled) return;
                toast.dataset.dismissScheduled = 'true';
                setTimeout(function() {
                    dismissSonnerToastEl(toast);
                }, 3500);
            });
        }
        
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', function() {
                var loading = document.getElementById('authLoading');
                if (loading) {
                    loading.classList.add('show');
                    setTimeout(function() { loading.classList.remove('show'); }, 500);
                }
                initAuthToasts();
            });
        } else {
            var loading = document.getElementById('authLoading');
            if (loading) {
                loading.classList.add('show');
                setTimeout(function() { loading.classList.remove('show'); }, 500);
            }
            initAuthToasts();
        }

        window.addEventListener('load', function() {
            var loading = document.getElementById('authLoading');
            if (loading) loading.classList.remove('show');
            initAuthToasts();
        });
    </script>
</body>
</html>
