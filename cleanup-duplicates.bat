@echo off
REM Cleanup duplicate ekskul entries
cd C:\Users\ASUSF\ekskul-sma

echo ===== Cleaning up duplicate ekskul entries =====
echo.
php artisan db:seed --class=CleanupDuplicateEkskul

echo.
echo ===== Clearing caches =====
php artisan cache:clear
php artisan view:clear

echo.
echo ===== Done! =====
echo Refresh browser to see changes.
pause
