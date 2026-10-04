@echo off
REM ==============================================================================
REM Sevam - Local Host Launcher for Windows
REM ==============================================================================

title Sevam Food Donation Platform - Local Server
cd /d "%~dp0"

echo ==========================================================
echo            SEVAM Food Donation Platform Launcher          
echo ==========================================================
echo.

where docker >nul 2>nul
if %errorlevel% equ 0 (
    echo [1] Start using Docker Compose (Recommended - Zero Configuration)
    echo [2] Start using Local PHP and MySQL / XAMPP
    echo.
    set /p CHOICE="Select an option (1 or 2, default 1): "
    if "%CHOICE%"=="" set CHOICE=1
    if "%CHOICE%"=="1" (
        echo.
        echo Starting Docker containers...
        docker compose up -d --build
        echo.
        echo ----------------------------------------------------------
        echo Sevam is now live at: http://localhost:8000
        echo Admin Login:    admin / admin123
        echo Provider Login: annapurna_kitchen / provider123
        echo NGO Login:      hope_foundation / group123
        echo ----------------------------------------------------------
        echo To stop the site run: docker compose down
        pause
        exit /b 0
    )
)

where php >nul 2>nul
if %errorlevel% neq 0 (
    echo ERROR: PHP was not found in your system PATH.
    echo If using XAMPP, add C:\xampp\php to your environment PATH,
    echo or copy this folder directly into C:\xampp\htdocs\sevam
    echo.
    pause
    exit /b 1
)

set DB_HOST=127.0.0.1
set DB_PORT=3306
set DB_NAME=sevam
set DB_USER=root
set DB_PASS=

echo Starting Sevam local server on http://localhost:8000...
echo Make sure MySQL/MariaDB is running and 'sevam.sql' is imported into database 'sevam'.
echo.
echo Press Ctrl+C to stop the server.
echo.

php -S 0.0.0.0:8000 -t "%~dp0" "%~dp0router.php"
pause
