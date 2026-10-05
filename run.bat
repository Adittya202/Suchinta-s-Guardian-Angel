@echo off
title Suchinta's Guardian Angel - Launcher
cd /d "%~dp0"

echo ========================================================
echo       Suchinta's Guardian Angel - App Launcher
echo ========================================================
echo.

:: 1. Check if XAMPP Apache is currently active on port 80
netstat -aon 2>nul | findstr /R ":80 " >nul
if %ERRORLEVEL% EQU 0 (
    echo [OK] XAMPP Apache detected running on port 80!
    set "APP_URL=http://localhost/suchintas-guardian-angel/"
    goto :LAUNCH_BROWSER
)

:: 2. Otherwise, use PHP built-in server on port 8000
set "PHP_CMD=php"
where php >nul 2>nul
if %ERRORLEVEL% NEQ 0 (
    if exist "C:\xampp\php\php.exe" set "PHP_CMD=C:\xampp\php\php.exe"
)

echo Starting PHP server on http://localhost:8000 ...
start "Suchinta Guardian Angel Server" /min "%PHP_CMD%" -S localhost:8000
timeout /t 1 /nobreak >nul 2>nul || ping 127.0.0.1 -n 2 >nul
set "APP_URL=http://localhost:8000/index.html"

:LAUNCH_BROWSER
echo Opening Suchinta's Guardian Angel in your browser...

:: Check if Chrome is in PATH
where chrome >nul 2>nul
if %ERRORLEVEL% EQU 0 (
    start chrome "%APP_URL%"
    goto :DONE
)

:: Check standard Chrome locations
if exist "%ProgramFiles%\Google\Chrome\Application\chrome.exe" (
    start "" "%ProgramFiles%\Google\Chrome\Application\chrome.exe" "%APP_URL%"
    goto :DONE
)

if exist "%ProgramFiles(x86)%\Google\Chrome\Application\chrome.exe" (
    start "" "%ProgramFiles(x86)%\Google\Chrome\Application\chrome.exe" "%APP_URL%"
    goto :DONE
)

if exist "%LocalAppData%\Google\Chrome\Application\chrome.exe" (
    start "" "%LocalAppData%\Google\Chrome\Application\chrome.exe" "%APP_URL%"
    goto :DONE
)

:: Default browser fallback
start "" "%APP_URL%"

:DONE
echo.
echo ========================================================
echo App URL:     %APP_URL%
echo Admin Gate:  %APP_URL%admin.php
echo ========================================================
echo.
echo You can close this window at any time.
timeout /t 5 >nul
