# Deploy Museum Railway Timetable plugin to Local WordPress
# Usage: .\local\deploy.ps1 [-OpenBrowser]
# Run: .\local\deploy.ps1 -OpenBrowser   # to also open localhost after deploy

param(
    [switch]$OpenBrowser = $false
)

$ErrorActionPreference = "Stop"
$scriptDir = Split-Path -Parent $MyInvocation.MyCommand.Path
$projectRoot = Split-Path -Parent $scriptDir
$configPath = Join-Path $scriptDir "deploy.config.json"

# Default paths - Local by Flywheel typical structure
$defaultLocalPath = "$env:USERPROFILE\Local Sites\test\app\public\wp-content\plugins\museum-railway-timetable"
$defaultUrl = "http://test.local"

# Load config if exists
$localPath = $defaultLocalPath
$localUrl = $defaultUrl

if (Test-Path $configPath) {
    try {
        $config = Get-Content $configPath -Raw | ConvertFrom-Json
        if ($config.localPath) { $localPath = $config.localPath }
        if ($config.localUrl) { $localUrl = $config.localUrl }
    } catch {
        Write-Host "Warning: Could not parse deploy.config.json, using defaults" -ForegroundColor Yellow
    }
}

# Plugin files/folders to copy (exclude dev files)
$pluginItems = @(
    "museum-railway-timetable.php",
    "uninstall.php",
    "inc",
    "assets",
    "languages"
)

Write-Host "`nDeploying Museum Railway Timetable to Local..." -ForegroundColor Cyan
Write-Host "  Source: $projectRoot" -ForegroundColor Gray
Write-Host "  Target: $localPath" -ForegroundColor Gray
Write-Host ""

# Ensure target directory exists
$targetParent = Split-Path -Parent $localPath
if (-not (Test-Path $targetParent)) {
    Write-Host "ERROR: Local plugins folder not found: $targetParent" -ForegroundColor Red
    Write-Host "Create a site in Local first, or edit local/deploy.config.json with your site path." -ForegroundColor Yellow
    Write-Host "Example path: $env:USERPROFILE\Local Sites\YOUR-SITE-NAME\app\public\wp-content\plugins\museum-railway-timetable" -ForegroundColor Gray
    exit 1
}

# Create target folder
if (-not (Test-Path $localPath)) {
    New-Item -ItemType Directory -Path $localPath -Force | Out-Null
    Write-Host "Created plugin folder" -ForegroundColor Green
}

# Copy files
$copied = 0
foreach ($item in $pluginItems) {
    $src = Join-Path $projectRoot $item
    $dst = Join-Path $localPath $item

    if (-not (Test-Path $src)) {
        Write-Host "  Skip (missing): $item" -ForegroundColor Yellow
        continue
    }

    if (Test-Path $dst) {
        Remove-Item $dst -Recurse -Force -ErrorAction SilentlyContinue
    }

    Copy-Item -Path $src -Destination $dst -Recurse -Force
    Write-Host "  Copied: $item" -ForegroundColor Green
    $copied++
}

Write-Host "`nDeploy complete! ($copied items)" -ForegroundColor Green

if ($OpenBrowser) {
    Write-Host "Opening $localUrl in browser..." -ForegroundColor Cyan
    Start-Process $localUrl
}
