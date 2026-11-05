@echo off
echo ========================================================
echo MENG-COPY TECHSTORE KE C:\xampp\htdocs\techstore ...
echo ========================================================

set TARGET="C:\xampp\htdocs\techstore"
set SOURCE="%~dp0"

if not exist "C:\xampp\htdocs" (
    echo [ERROR] Direktori C:\xampp\htdocs tidak ditemukan.
    echo Pastikan XAMPP telah terinstal di drive C:
    pause
    exit /b 1
)

echo Menyalin file dari %SOURCE% ke %TARGET% ...
xcopy /E /I /Y "%SOURCE%*" %TARGET%

if %ERRORLEVEL% EQU 0 (
    echo.
    echo [SUKSES] Seluruh file TechStore berhasil disalin ke C:\xampp\htdocs\techstore!
    echo Silakan jalankan Apache & MySQL di XAMPP, lalu buka:
    echo http://localhost/techstore/
) else (
    echo [INFO] Terjadi kendala hak akses administrator saat menyalin langsung.
    echo Silakan klik kanan file ini lalu pilih "Run as administrator".
)

echo.
pause
