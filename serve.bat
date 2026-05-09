@echo off
REM PHP built-in server (needs PHP on PATH). MySQL must still be running separately.
cd /d "%~dp0"

where php >nul 2>&1
if errorlevel 1 (
  echo PHP was not found in PATH.
  echo Try: set PATH=%%PATH%%;C:\xampp\php
  echo Then run this again, or start Apache from XAMPP instead.
  pause
  exit /b 1
)

echo Starting http://127.0.0.1:8080  ^(Ctrl+C to stop^)
echo Import database.sql first. Open install.php to verify DB.
php -S 127.0.0.1:8080 -t "%~dp0"
