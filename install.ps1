Write-Host "Installing MA-Tools..." -ForegroundColor Cyan

# 1. Check if PHP is installed
if (-not (Get-Command "php" -ErrorAction SilentlyContinue)) {
    Write-Host "❌ Error: PHP is not installed or not in your PATH!" -ForegroundColor Red
    exit
}

# 2. Create folders
$binPath = "$HOME\bin"
$myAppsPath = "$binPath\MyApps"
New-Item -ItemType Directory -Force -Path $binPath | Out-Null
New-Item -ItemType Directory -Force -Path $myAppsPath | Out-Null

# 3. Copy PHP files
$files = @("ma-open.php", "ma-web.php", "ma-todo.php", "ma-game.php")
foreach ($file in $files) {
    if (Test-Path $file) {
        Copy-Item $file "$binPath\" -Force
        Write-Host "Copied $file to $binPath" -ForegroundColor Green
    }
}

# 4. Create the .bat launchers
$launchers = @{
    "ma-open" = "ma-open.php"
    "ma-web"  = "ma-web.php"
    "ma-todo" = "ma-todo.php"
    "ma-game" = "ma-game.php"
}

foreach ($cmd in $launchers.Keys) {
    $phpFile = $launchers[$cmd]
    $content = "@echo off`nphp `"%~dp0$phpFile`" %*"
    Set-Content -Path "$binPath\$cmd.bat" -Value $content -Encoding ASCII
    Write-Host "Created $cmd.bat launcher" -ForegroundColor Green
}

# 5. Update PATH (Put it at the FRONT so Windows sees it first)
$oldPath = [Environment]::GetEnvironmentVariable("Path", "User")

# Remove them if they exist so we don't create duplicates
$cleanPath = ($oldPath -split ';' | Where-Object { $_ -notmatch '\\bin' -and $_ -notmatch 'MyApps' }) -join ';'

$newPath = "$binPath;$myAppsPath;$cleanPath"
[Environment]::SetEnvironmentVariable("Path", $newPath, "User")

# 6. Force the CURRENT window to see the changes immediately
$env:Path = [System.Environment]::GetEnvironmentVariable("Path", "Machine") + ";" + [System.Environment]::GetEnvironmentVariable("Path", "User")

Write-Host "`n✅ Installation Complete!" -ForegroundColor Cyan
Write-Host "You can now type: ma-web or ma-todo" -ForegroundColor Cyan
