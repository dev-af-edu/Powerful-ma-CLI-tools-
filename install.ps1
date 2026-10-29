Write-Host "Installing MA-Tools..." -ForegroundColor Cyan

# 1. Create the bin folder
$binPath = "$HOME\bin"
$myAppsPath = "$binPath\MyApps"
New-Item -ItemType Directory -Force -Path $binPath | Out-Null
New-Item -ItemType Directory -Force -Path $myAppsPath | Out-Null

# 2. Move PHP files to bin
$files = @("ma-open.php", "ma-web.php", "ma-todo.php", "ma-game.php")
foreach ($file in $files) {
    if (Test-Path $file) {
        Copy-Item $file "$binPath\" -Force
        Write-Host "Copied $file to $binPath" -ForegroundColor Green
    }
}

# 3. Create the .bat launchers
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

# 4. Add to PATH
$oldPath = [Environment]::GetEnvironmentVariable("Path", "User")
if ($oldPath -notlike "*$binPath*") {
    [Environment]::SetEnvironmentVariable("Path", "$oldPath;$binPath", "User")
    Write-Host "Added $binPath to your PATH." -ForegroundColor Green
}
if ($oldPath -notlike "*$myAppsPath*") {
    [Environment]::SetEnvironmentVariable("Path", "$oldPath;$myAppsPath", "User")
    Write-Host "Added $myAppsPath to your PATH." -ForegroundColor Green
}

Write-Host "`n✅ Installation Complete!" -ForegroundColor Cyan
Write-Host "Please restart your terminal (close and reopen PowerShell)." -ForegroundColor Yellow
Write-Host "Then try typing: ma-web" -ForegroundColor Cyan