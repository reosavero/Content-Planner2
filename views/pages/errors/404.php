<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Halaman Tidak Ditemukan</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/tvri-theme.css?v=<?= APP_VERSION ?>">
    <style>
        body {
            background: var(--gray-100);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            font-family: var(--font-family);
        }
        .error-container {
            text-align: center;
            max-width: 480px;
        }
        .error-code {
            font-size: 8rem;
            font-weight: 800;
            background: var(--tvri-blue-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            line-height: 1;
            margin-bottom: 16px;
        }
        .error-icon {
            font-size: 3rem;
            color: var(--gray-300);
            margin-bottom: 16px;
        }
        h1 {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--gray-800);
            margin-bottom: 8px;
        }
        p {
            color: var(--gray-500);
            margin-bottom: 24px;
        }
        .btn-home {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 28px;
            background: var(--tvri-blue-gradient);
            color: #fff;
            border: none;
            border-radius: 12px;
            font-family: var(--font-family);
            font-weight: 600;
            font-size: 0.875rem;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.2s ease;
        }
        .btn-home:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(0, 51, 153, 0.3);
            color: #fff;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-code">404</div>
        <div class="error-icon">
            <i class="bi bi-compass"></i>
        </div>
        <h1>Halaman Tidak Ditemukan</h1>
        <p>Maaf, halaman yang Anda cari tidak tersedia atau telah dipindahkan.</p>
        <a href="<?= BASE_URL ?>/dashboard" class="btn-home">
            <i class="bi bi-arrow-left"></i> Kembali ke Dashboard
        </a>
    </div>
</body>
</html>
