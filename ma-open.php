<?php
// ma-open.php — App Launcher Manager

if (php_sapi_name() !== 'cli') {
    exit("CLI only.\n");
}

$dbFile = __DIR__ . '\apps.json';
$appsFolder = __DIR__ . '\MyApps';

if (!is_dir($appsFolder)) {
    mkdir($appsFolder, 0777, true);
}

function loadApps($file) {
    if (!file_exists($file)) {
        $default = [
            "chrome"  => ["path" => "chrome", "name" => "Google Chrome"],
            "notepad" => ["path" => "notepad", "name" => "Notepad"],
            "calc"    => ["path" => "calc", "name" => "Calculator"],
            "code"    => ["path" => "code", "name" => "VS Code"]
        ];
        file_put_contents($file, json_encode($default, JSON_PRETTY_PRINT));
        return $default;
    }
    return json_decode(file_get_contents($file), true);
}

function saveApps($file, $apps) {
    file_put_contents($file, json_encode($apps, JSON_PRETTY_PRINT));
}

function createShortcut($command, $path, $folder) {
    $batFile = $folder . '\\' . $command . '.bat';
    if (strpos($path, '\\') === false && strpos($path, '.exe') === false) {
        $path = $path . '.exe';
    }
    $content = "@echo off\nstart \"\" /b \"$path\"";
    file_put_contents($batFile, $content);
}

$apps = loadApps($dbFile);
$cmd = strtolower($argv[1] ?? 'menu');

$cCyan = "\033[36m";
$cGreen = "\033[32m";
$cRed = "\033[31m";
$cReset = "\033[0m";

switch ($cmd) {
    case 'menu':
    case 'help':
        echo $cCyan . "=== MA-OPEN MANAGER ===" . $cReset . "\n\n";
        echo "Commands:\n";
        echo "  ma-open list\n";
        echo "  ma-open add <cmd> <path>\n";
        echo "  ma-open edit <cmd> <path>\n";
        echo "  ma-open remove <cmd>\n\n";
        echo "To launch, just type the app's name! (e.g., 'chrome')\n";
        break;

    case 'list':
        echo $cCyan . "=== INSTALLED APPS ===" . $cReset . "\n";
        foreach ($apps as $key => $data) {
            echo " - " . $cGreen . str_pad($key, 12) . $cReset . " -> " . $data['name'] . " (" . $data['path'] . ")\n";
            createShortcut($key, $data['path'], $appsFolder);
        }
        break;

    case 'add':
        $newCmd = strtolower($argv[2] ?? '');
        $newPath = $argv[3] ?? '';
        if (empty($newCmd) || empty($newPath)) {
            echo $cRed . "Error: Usage is 'ma-open add <command> <path>'" . $cReset . "\n";
            exit(1);
        }
        if (isset($apps[$newCmd])) {
            echo $cRed . "Error: Command '$newCmd' already exists. Use 'edit' instead." . $cReset . "\n";
            exit(1);
        }
        $apps[$newCmd] = ["path" => $newPath, "name" => ucfirst($newCmd)];
        saveApps($dbFile, $apps);
        createShortcut($newCmd, $newPath, $appsFolder);
        echo $cGreen . "✅ Added! You can now type '$newCmd' to open it." . $cReset . "\n";
        break;

    case 'edit':
        $editCmd = strtolower($argv[2] ?? '');
        $editPath = $argv[3] ?? '';
        if (empty($editCmd) || empty($editPath)) {
            echo $cRed . "Error: Usage is 'ma-open edit <command> <path>'" . $cReset . "\n";
            exit(1);
        }
        if (!isset($apps[$editCmd])) {
            echo $cRed . "Error: Command '$editCmd' not found." . $cReset . "\n";
            exit(1);
        }
        $apps[$editCmd]['path'] = $editPath;
        saveApps($dbFile, $apps);
        createShortcut($editCmd, $editPath, $appsFolder);
        echo $cGreen . "✅ Updated! '$editCmd' now points to the new path." . $cReset . "\n";
        break;

    case 'remove':
        $remCmd = strtolower($argv[2] ?? '');
        if (!isset($apps[$remCmd])) {
            echo $cRed . "Error: Command '$remCmd' not found." . $cReset . "\n";
            exit(1);
        }
        unset($apps[$remCmd]);
        saveApps($dbFile, $apps);
        $batFile = $appsFolder . '\\' . $remCmd . '.bat';
        if (file_exists($batFile)) unlink($batFile);
        echo $cGreen . "✅ Removed '$remCmd'." . $cReset . "\n";
        break;

    default:
        echo $cRed . "Unknown command '$cmd'. Type 'ma-open' to see the menu." . $cReset . "\n";
        break;
}