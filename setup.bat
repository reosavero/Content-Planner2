@echo off
title TVRI Planner - Database Setup
cls
echo ============================================
echo   TVRI Jawa Timur - Social Media Planner
echo   Database Setup v2.0
echo ============================================
echo.
echo This script will create the database and run all migrations.
echo.
echo Make sure XAMPP/WAMP MySQL is running.
echo.

where mysql >nul 2>nul
if %ERRORLEVEL% NEQ 0 (
    echo ERROR: MySQL client not found in PATH.
    echo Please add MySQL bin directory to PATH or run XAMPP.
    echo.
    pause
    exit /b 1
)

echo [1/4] Creating database...
mysql -u root -e "CREATE DATABASE IF NOT EXISTS db_planning_tvri CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
if %ERRORLEVEL% NEQ 0 (
    echo ERROR: Failed to create database. Check MySQL connection.
    pause
    exit /b 1
)
echo   Database 'db_planning_tvri' created successfully.
echo.

echo [2/4] Running main tables migration...
mysql -u root db_planning_tvri < "database\migrations\001_create_tables.sql"
if %ERRORLEVEL% NEQ 0 (
    echo ERROR: Migration 001 failed.
    pause
    exit /b 1
)
echo   Tables created successfully.
echo.

echo [3/4] Running notification enhancement migration...
mysql -u root db_planning_tvri < "database\migrations\002_add_notification_group_key.sql"
echo   Notification schema updated.
echo.

echo [4/4] Running new features migration...
mysql -u root db_planning_tvri < "database\migrations\003_add_new_roles.sql"
echo   New roles, tables, and features installed.
echo.

echo [5/5] Running seed data...
mysql -u root db_planning_tvri < "database\seeds\001_seed_initial_data.sql"
echo   Seed data inserted.
echo.

echo ============================================
echo   Setup Complete!
echo ============================================
echo.
echo   Database: db_planning_tvri
echo   Roles:    super_admin, admin_sosmed, admin_kmb, editor, kontributor, magang
echo   Login:    superadmin / Admin@12345
echo.
echo   New Features:
echo   - Excel Import/Export
echo   - Timeline Content Roadmap
echo   - Admin KMB Role
echo   - Magang (Intern) Role
echo   - Enhanced Approval with Re-Check
echo   - Auto Posting Engine
echo   - Social Media API Integration
echo.
echo   Run start.bat to launch the application.
echo.
pause
