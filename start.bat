@echo off
title TVRI Jawa Timur - Social Media Planner
cls
echo ============================================
echo   TVRI Jawa Timur
echo   Social Media Planner v1.0.0
echo ============================================
echo.

where php >nul 2>nul
if %ERRORLEVEL% NEQ 0 (
    echo [!] PHP CLI tidak ditemukan di PATH.
    echo.
    echo Alternatif: Gunakan XAMPP / web server
    echo ============================================
    echo   1. Jalankan XAMPP Control Panel
    echo   2. Start Apache + MySQL
    echo   3. Copy folder ini ke:
    echo      C:\xampp\htdocs\tvri-planner\
    echo   4. Buka browser:
    echo      http://localhost/tvri-planner
    echo.
    echo   Atau jalankan: copy-to-htdocs.bat
    echo ============================================
    echo.
    pause
    exit /b 1
)

mysql -u root -e "SELECT 1" >nul 2>nul
if %ERRORLEVEL% NEQ 0 (
    echo [!] MySQL tidak terhubung. Jalankan MySQL terlebih dahulu.
    echo.
    pause
    exit /b 1
)

echo [*] Development Server
echo.
echo   Access: http://localhost:8000
echo   Login : superadmin / Admin@12345
echo.
echo   Press Ctrl+C to stop.
echo.
php -S localhost:8000 router.php
pause
