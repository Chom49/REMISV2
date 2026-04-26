@echo off
title REMIS V2 - Setup
color 0A

echo.
echo  =====================================================
echo    REMIS V2 - Rental Management Information System
echo    Setup Script
echo  =====================================================
echo.

:: ── Find PHP ──────────────────────────────────────────
set PHP=
if exist "C:\xampp\php\php.exe"       set PHP=C:\xampp\php\php.exe
if exist "C:\wamp64\bin\php\php.exe"  set PHP=C:\wamp64\bin\php\php.exe
if "%PHP%"=="" where php >nul 2>&1 && set PHP=php

if "%PHP%"=="" (
    echo  [ERROR] PHP not found.
    echo  Please install XAMPP and try again.
    echo  Download: https://www.apachefriends.org
    echo.
    pause
    exit /b 1
)
echo  [OK] PHP found: %PHP%

:: ── Find or download Composer ─────────────────────────
set COMPOSER=
if exist "composer.phar" (
    set COMPOSER="%PHP%" composer.phar
    echo  [OK] Using bundled composer.phar
) else (
    where composer >nul 2>&1
    if not errorlevel 1 (
        set COMPOSER=composer
        echo  [OK] Composer found globally
    ) else (
        echo  [INFO] Downloading Composer...
        powershell -Command "Invoke-WebRequest -Uri 'https://getcomposer.org/composer.phar' -OutFile 'composer.phar'" >nul 2>&1
        if exist "composer.phar" (
            set COMPOSER="%PHP%" composer.phar
            echo  [OK] Composer downloaded
        ) else (
            echo  [ERROR] Could not download Composer.
            echo  Please install Composer from: https://getcomposer.org
            echo.
            pause
            exit /b 1
        )
    )
)

:: ── Copy .env ─────────────────────────────────────────
echo.
if not exist ".env" (
    copy ".env.example" ".env" >nul
    echo  [OK] .env file created from .env.example
) else (
    echo  [SKIP] .env already exists
)

:: ── Install PHP dependencies ──────────────────────────
echo.
echo  [INFO] Installing PHP dependencies (this may take a moment)...
%COMPOSER% install --no-interaction --optimize-autoloader
if errorlevel 1 (
    echo  [ERROR] Composer install failed. Check your internet connection.
    pause
    exit /b 1
)
echo  [OK] Dependencies installed

:: ── Generate app key ──────────────────────────────────
echo.
echo  [INFO] Generating application key...
"%PHP%" artisan key:generate --force
echo  [OK] Application key set

:: ── Run database migrations ───────────────────────────
echo.
echo  [INFO] Running database migrations...
echo  (Make sure MySQL is running in XAMPP and the database exists)
echo.
"%PHP%" artisan migrate --force
if errorlevel 1 (
    echo.
    echo  [WARN] Migration failed. This usually means:
    echo    1. MySQL is not running - Start it in XAMPP Control Panel
    echo    2. Database does not exist - Create it in phpMyAdmin
    echo    3. Check your DB settings in .env file
    echo.
    echo  After fixing, run this script again or open:
    echo  http://localhost/RemisV2/install.php
    echo.
    pause
    exit /b 1
)
echo  [OK] Database tables created

:: ── Storage link ──────────────────────────────────────
echo.
"%PHP%" artisan storage:link --force >nul 2>&1
echo  [OK] Storage linked

:: ── Done ──────────────────────────────────────────────
echo.
echo  =====================================================
echo    Setup Complete!
echo.
echo    Open your browser and go to:
echo    http://localhost/RemisV2
echo  =====================================================
echo.
pause
