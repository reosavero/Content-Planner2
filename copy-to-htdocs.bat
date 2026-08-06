@echo off
title Copy TVRI Planner to XAMPP htdocs
cls
echo ============================================
echo   Copy TVRI Planner ke XAMPP htdocs
echo ============================================
echo.

if not exist "C:\xampp\htdocs" (
    echo [ERROR] Folder C:\xampp\htdocs tidak ditemukan.
    echo         Pastikan XAMPP sudah terinstall.
    echo.
    pause
    exit /b 1
)

if exist "C:\xampp\htdocs\tvri-planner" (
    echo [INFO] Folder tvri-planner sudah ada di htdocs.
    echo        Akan menimpa file yang ada...
    echo.
)

echo [1/3] Menyalin file ke C:\xampp\htdocs\tvri-planner\ ...
xcopy /E /I /Y "%~dp0*" "C:\xampp\htdocs\tvri-planner\" >nul

echo [2/3] Membuat folder uploads dan logs...
if not exist "C:\xampp\htdocs\tvri-planner\uploads\images" mkdir "C:\xampp\htdocs\tvri-planner\uploads\images"
if not exist "C:\xampp\htdocs\tvri-planner\uploads\videos" mkdir "C:\xampp\htdocs\tvri-planner\uploads\videos"
if not exist "C:\xampp\htdocs\tvri-planner\uploads\thumbnails" mkdir "C:\xampp\htdocs\tvri-planner\uploads\thumbnails"
if not exist "C:\xampp\htdocs\tvri-planner\uploads\documents" mkdir "C:\xampp\htdocs\tvri-planner\uploads\documents"
if not exist "C:\xampp\htdocs\tvri-planner\uploads\avatars" mkdir "C:\xampp\htdocs\tvri-planner\uploads\avatars"
if not exist "C:\xampp\htdocs\tvri-planner\logs" mkdir "C:\xampp\htdocs\tvri-planner\logs"
if not exist "C:\xampp\htdocs\tvri-planner\database\backups" mkdir "C:\xampp\htdocs\tvri-planner\database\backups"

echo [3/3] Selesai!
echo.
echo ============================================
echo   COPY COMPLETE!
echo ============================================
echo.
echo   Langkah selanjutnya:
echo   1. Buka XAMPP Control Panel
echo   2. Start Apache dan MySQL
echo   3. Setup database (jika belum):
echo      http://localhost/tvri-planner/setup
echo      atau import manual:
echo      database/install_db_tvri.sql via phpMyAdmin
echo.
echo   4. Buka browser:
echo      http://localhost/tvri-planner
echo.
echo   Login: superadmin / Admin@12345
echo.
pause
