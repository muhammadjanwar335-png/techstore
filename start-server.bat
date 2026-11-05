@echo off
echo ========================================================
echo MENJALANKAN TECHSTORE DEVELOPMENT SERVER
echo ========================================================

cd /d "%~dp0"
set PHP_BIN="C:\xampp\php\php.exe"

if exist %PHP_BIN% (
    echo Menggunakan PHP dari XAMPP: %PHP_BIN%
    echo Server aktif di: http://localhost:8000/
    echo Tekan Ctrl+C untuk menghentikan server.
    echo.
    start http://localhost:8000/
    %PHP_BIN% -S localhost:8000
) else (
    echo Mencoba command php dari sistem PATH...
    start http://localhost:8000/
    php -S localhost:8000
)

pause
