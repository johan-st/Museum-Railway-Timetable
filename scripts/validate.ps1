# PowerShell validation script for Museum Railway Timetable
# Run this with: .\scripts\validate.ps1

$errors = @()
$warnings = @()
$checks = 0

Write-Host "Validating Museum Railway Timetable Plugin...`n" -ForegroundColor Cyan

# 1. Check required files exist
Write-Host "1. Checking required files..." -ForegroundColor Yellow
$required_files = @(
    "museum-railway-timetable.php",
    "uninstall.php",
    "inc/assets.php",
    "inc/admin-page.php",
    "inc/admin-page/admin-list.php",
    "inc/import-lennakatten/loader.php",
    "inc/shortcodes.php",
    "inc/cpt.php",
    "inc/functions/helpers.php",
    "inc/functions/services.php",
    "assets/admin-base.css",
    "assets/admin-timetable.css",
    "assets/admin-timetable-overview.css",
    "assets/admin-meta-boxes.css",
    "assets/admin-dashboard.css",
    "assets/admin-ui.css",
    "assets/admin-responsive.css",
    "assets/admin.js",
    "languages/museum-railway-timetable.pot",
    "languages/museum-railway-timetable-sv_SE.po"
)

foreach ($file in $required_files) {
    $checks++
    if (Test-Path $file) {
        Write-Host "  OK: $file" -ForegroundColor Green
    } else {
        $errors += "Missing required file: $file"
        Write-Host "  ERROR: Missing: $file" -ForegroundColor Red
    }
}

# 2. Check ABSPATH protection
Write-Host "`n2. Checking ABSPATH protection..." -ForegroundColor Yellow
$php_files = Get-ChildItem -Path . -Recurse -Include *.php -Exclude validate.php | Where-Object { $_.FullName -notmatch "node_modules|vendor|\.git" }

foreach ($file in $php_files) {
    $checks++
    $content = Get-Content $file.FullName -Raw
    $relativePath = $file.FullName.Replace((Get-Location).Path + "\", "")
    
    # Skip uninstall.php (has different check)
    if ($relativePath -eq "uninstall.php") {
        if ($content -match "WP_UNINSTALL_PLUGIN") {
            Write-Host "  OK: $relativePath (has WP_UNINSTALL_PLUGIN check)" -ForegroundColor Green
        } else {
            $warnings += "uninstall.php should check WP_UNINSTALL_PLUGIN"
            Write-Host "  WARNING: $relativePath (missing WP_UNINSTALL_PLUGIN check)" -ForegroundColor Yellow
        }
        continue
    }
    
    $hasAbspath = $content -match "if\s*\(!defined\s*\(.*ABSPATH.*\)\)"
    
    if ($hasAbspath) {
        Write-Host "  OK: $relativePath" -ForegroundColor Green
    } else {
        $warnings += "Missing ABSPATH check in $relativePath"
        Write-Host "  WARNING: $relativePath (missing ABSPATH check)" -ForegroundColor Yellow
    }
}

# 3. Check for inline styles (CSS custom properties like --service-count are allowed)
Write-Host "`n3. Checking for inline styles..." -ForegroundColor Yellow
foreach ($file in $php_files) {
    $checks++
    $content = Get-Content $file.FullName -Raw
    $relativePath = $file.FullName.Replace((Get-Location).Path + "\", "")
    
    if ($content -match 'style\s*=') {
        # Allow style="--var: value" (CSS custom properties for dynamic grid columns etc.)
        if ($content -match 'style\s*=\s*[^>]*--[a-zA-Z-]+\s*:') {
            Write-Host "  OK: $relativePath (CSS custom property only)" -ForegroundColor Green
        } else {
            $warnings += "Inline style found in $relativePath"
            Write-Host "  WARNING: $relativePath (contains inline styles)" -ForegroundColor Yellow
        }
    } else {
        Write-Host "  OK: $relativePath" -ForegroundColor Green
    }
}

# 4. Check plugin header
Write-Host "`n4. Checking plugin header..." -ForegroundColor Yellow
$checks++
if (Test-Path "museum-railway-timetable.php") {
    $main_file = Get-Content "museum-railway-timetable.php" -Raw
    $required_headers = @("Plugin Name", "Description", "Version", "Text Domain")
    
    foreach ($header in $required_headers) {
        if ($main_file -match "$header\s*:") {
            Write-Host "  OK: Header: $header" -ForegroundColor Green
        } else {
            $errors += "Missing plugin header: $header"
            Write-Host "  ERROR: Missing header: $header" -ForegroundColor Red
        }
    }
} else {
    $errors += "Main plugin file missing"
    Write-Host "  ERROR: Main plugin file missing" -ForegroundColor Red
}

# 5. Check CSS files
Write-Host "`n5. Checking CSS files..." -ForegroundColor Yellow
$cssFiles = @(
    "assets/admin-base.css",
    "assets/admin-timetable.css",
    "assets/admin-timetable-overview.css",
    "assets/admin-meta-boxes.css",
    "assets/admin-dashboard.css",
    "assets/admin-ui.css",
    "assets/admin-responsive.css"
)
foreach ($cssFile in $cssFiles) {
    $checks++
    if (Test-Path $cssFile) {
        $css = Get-Content $cssFile -Raw
        if ($css.Length -gt 0) {
            Write-Host "  OK: $cssFile" -ForegroundColor Green
        } else {
            $errors += "CSS file is empty: $cssFile"
            Write-Host "  ERROR: $cssFile is empty" -ForegroundColor Red
        }
    } else {
        $errors += "CSS file missing: $cssFile"
        Write-Host "  ERROR: $cssFile missing" -ForegroundColor Red
    }
}

# 6. Check JS files
Write-Host "`n6. Checking JavaScript files..." -ForegroundColor Yellow
$admin_js_files = @(
    "assets/admin-utils.js",
    "assets/admin-route-ui.js",
    "assets/admin-stoptimes-ui.js",
    "assets/admin-timetable-services-ui.js",
    "assets/admin.js"
)
foreach ($jsFile in $admin_js_files) {
    $checks++
    if (Test-Path $jsFile) {
        $js = Get-Content $jsFile -Raw
        if ($js.Length -gt 0) {
            Write-Host "  OK: $jsFile" -ForegroundColor Green
        } else {
            $errors += "JS file is empty: $jsFile"
            Write-Host "  ERROR: $jsFile is empty" -ForegroundColor Red
        }
    } else {
        $errors += "JS file missing: $jsFile"
        Write-Host "  ERROR: $jsFile missing" -ForegroundColor Red
    }
}

# Summary
Write-Host "`n" + ("=" * 60) -ForegroundColor Cyan
Write-Host "Validation Summary" -ForegroundColor Cyan
Write-Host ("=" * 60) -ForegroundColor Cyan
Write-Host "Total checks: $checks" -ForegroundColor White
Write-Host "Errors: $($errors.Count)" -ForegroundColor $(if ($errors.Count -eq 0) { "Green" } else { "Red" })
Write-Host "Warnings: $($warnings.Count)" -ForegroundColor $(if ($warnings.Count -eq 0) { "Green" } else { "Yellow" })
Write-Host ""

if ($errors.Count -gt 0) {
    Write-Host "ERRORS FOUND:" -ForegroundColor Red
    foreach ($error in $errors) {
        Write-Host "  - $error" -ForegroundColor Red
    }
    Write-Host ""
}

if ($warnings.Count -gt 0) {
    Write-Host "WARNINGS:" -ForegroundColor Yellow
    foreach ($warning in $warnings) {
        Write-Host "  - $warning" -ForegroundColor Yellow
    }
    Write-Host ""
}

if ($errors.Count -eq 0 -and $warnings.Count -eq 0) {
    Write-Host "SUCCESS: All validations passed! Project is ready to deploy." -ForegroundColor Green
    exit 0
} elseif ($errors.Count -eq 0) {
    Write-Host "WARNING: Project has warnings but no errors. Review warnings before deploying." -ForegroundColor Yellow
    exit 0
} else {
    Write-Host "ERROR: Validation failed! Please fix errors before deploying." -ForegroundColor Red
    exit 1
}