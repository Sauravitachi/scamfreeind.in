@echo off
REM WhatsApp Lead Capture System - Windows Verification Script
REM This script helps verify that all components are working correctly

setlocal enabledelayedexpansion

cls
echo ==========================================
echo WhatsApp Lead Capture System Verification
echo ==========================================
echo.

REM Colors (simulated with echo)
REM We'll use symbols instead: [OK] and [FAIL]

set PASSED=0
set FAILED=0

echo.
echo [Step 1/5] Checking Directory Structure
echo ------------------------------------------

if exist "app\Http\Controllers\Api" (
    echo [OK] Laravel API Controllers directory exists
    set /a PASSED+=1
) else (
    echo [FAIL] Laravel API Controllers directory NOT found
    set /a FAILED+=1
)

if exist "app\Models" (
    echo [OK] Laravel Models directory exists
    set /a PASSED+=1
) else (
    echo [FAIL] Laravel Models directory NOT found
    set /a FAILED+=1
)

if exist "app\Services" (
    echo [OK] Laravel Services directory exists
    set /a PASSED+=1
) else (
    echo [FAIL] Laravel Services directory NOT found
    set /a FAILED+=1
)

if exist "whatsapp-bot" (
    echo [OK] WhatsApp Bot directory exists
    set /a PASSED+=1
) else (
    echo [FAIL] WhatsApp Bot directory NOT found
    set /a FAILED+=1
)

if exist "database\migrations" (
    echo [OK] Database Migrations directory exists
    set /a PASSED+=1
) else (
    echo [FAIL] Database Migrations directory NOT found
    set /a FAILED+=1
)

echo.
echo [Step 2/5] Checking Critical Files
echo ------------------------------------------

if exist "app\Http\Controllers\Api\WhatsAppLeadController.php" (
    echo [OK] WhatsAppLeadController file exists
    set /a PASSED+=1
) else (
    echo [FAIL] WhatsAppLeadController file NOT found
    set /a FAILED+=1
)

if exist "app\Models\ScamLead.php" (
    echo [OK] ScamLead Model file exists
    set /a PASSED+=1
) else (
    echo [FAIL] ScamLead Model file NOT found
    set /a FAILED+=1
)

if exist "app\Services\ScamLeadService.php" (
    echo [OK] ScamLeadService file exists
    set /a PASSED+=1
) else (
    echo [FAIL] ScamLeadService file NOT found
    set /a FAILED+=1
)

if exist "routes\api.php" (
    echo [OK] API Routes file exists
    set /a PASSED+=1
) else (
    echo [FAIL] API Routes file NOT found
    set /a FAILED+=1
)

if exist "whatsapp-bot\index.js" (
    echo [OK] WhatsApp Bot Entry Point exists
    set /a PASSED+=1
) else (
    echo [FAIL] WhatsApp Bot Entry Point NOT found
    set /a FAILED+=1
)

if exist "whatsapp-bot\package.json" (
    echo [OK] Bot Package Config exists
    set /a PASSED+=1
) else (
    echo [FAIL] Bot Package Config NOT found
    set /a FAILED+=1
)

echo.
echo [Step 3/5] Checking NPM Dependencies
echo ------------------------------------------

if exist "whatsapp-bot\node_modules" (
    echo [OK] Node modules installed
    set /a PASSED+=1
    
    if exist "whatsapp-bot\node_modules\whatsapp-web.js\package.json" (
        echo [OK] whatsapp-web.js is installed
        set /a PASSED+=1
    ) else (
        echo [FAIL] whatsapp-web.js is NOT installed
        set /a FAILED+=1
    )
    
    if exist "whatsapp-bot\node_modules\axios\package.json" (
        echo [OK] axios is installed
        set /a PASSED+=1
    ) else (
        echo [FAIL] axios is NOT installed
        set /a FAILED+=1
    )
    
    if exist "whatsapp-bot\node_modules\qrcode-terminal\package.json" (
        echo [OK] qrcode-terminal is installed
        set /a PASSED+=1
    ) else (
        echo [FAIL] qrcode-terminal is NOT installed
        set /a FAILED+=1
    )
) else (
    echo [FAIL] Node modules NOT installed in whatsapp-bot
    echo         Run: cd whatsapp-bot ^&^& npm install
    set /a FAILED+=1
)

echo.
echo [Step 4/5] Checking Port Availability
echo ------------------------------------------

REM Check if Laravel port is available (port 8000)
netstat -ano | findstr ":8000 " >nul 2>&1
if %ERRORLEVEL% equ 0 (
    echo [OK] Port 8000 is in use (Laravel dev server running)
    set /a PASSED+=1
) else (
    echo [WARN] Port 8000 is not in use (Laravel server may not be running)
)

REM Note: Port 3000 check is optional since bot may not be running yet

echo.
echo [Step 5/5] Checking Database
echo ------------------------------------------

REM Check if migrations have been run by looking for the migration file
if exist "database\migrations\2025_12_22_121311_create_scam_leads_table.php" (
    echo [OK] ScamLead migration file exists
    set /a PASSED+=1
) else (
    echo [FAIL] ScamLead migration file NOT found
    set /a FAILED+=1
)

echo.
echo ==========================================
echo Verification Summary
echo ==========================================
echo Passed: !PASSED!
echo Failed: !FAILED!
echo.

if !FAILED! equ 0 (
    echo SUCCESS: All critical components are in place!
    echo.
    echo Ready to start the system:
    echo 1. Terminal 1: php artisan serve
    echo 2. Terminal 2: cd whatsapp-bot ^&^& node index.js
    exit /b 0
) else (
    echo WARNING: Some checks failed. Please review the output above.
    echo.
    echo Common issues:
    echo - Missing npm modules: Run "cd whatsapp-bot && npm install"
    echo - Laravel not running: Run "php artisan serve"
    exit /b 1
)

endlocal
