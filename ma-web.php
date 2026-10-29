<?php
// ma-web.php — CLI Web Navigator & Search

if (php_sapi_name() !== 'cli') {
    exit("CLI only.\n");
}

$dbFile = __DIR__ . '\web.json';

function loadWebsites($file) {
    if (!file_exists($file)) {
        $default = [
            "google"   => "https://www.google.com",
            "chatgpt"  => "https://chatgpt.com",
            "deepseek" => "https://chat.deepseek.com",
            "youtube"  => "https://www.youtube.com",
            "github"   => "https://github.com"
        ];
        file_put_contents($file, json_encode($default, JSON_PRETTY_PRINT));
        return $default;
    }
    return json_decode(file_get_contents($file), true);
}

function saveWebsites($file, $websites) {
    file_put_contents($file, json_encode($websites, JSON_PRETTY_PRINT));
}

function openUrl($url) {
    pclose(popen("start \"\" \"$url\"", "r"));
}

$sites = loadWebsites($dbFile);
$cmd = strtolower($argv[1] ?? '');

$cCyan = "\033[36m";
$cGreen = "\033[32m";
$cYellow = "\033[33m";
$cRed = "\033[31m";
$cReset = "\033[0m";

if (empty($cmd)) {
    while (true) {
        echo "\033[2J\033[H";
        echo $cCyan . "=========================================\n";
        echo "           MA-WEB NAVIGATOR              \n";
        echo "=========================================\n" . $cReset;
        echo "Type a shortcut name to open it, or use these commands:\n\n";
        echo "  [name]       Open website (e.g., 'chatgpt')\n";
        echo "  search       Search Google\n";
        echo "  add          Add a new website shortcut\n";
        echo "  edit         Edit an existing shortcut\n";
        echo "  remove       Delete a shortcut\n";
        echo "  list         Show all shortcuts\n";
        echo "  exit         Close this menu\n\n";
        
        echo $cCyan . "Available shortcuts: " . $cReset;
        echo implode(", ", array_keys($sites)) . "\n\n";
        
        echo $cYellow . "ma-web> " . $cReset;
        $input = trim(fgets(STDIN));
        $parts = explode(' ', $input, 2);
        $action = strtolower($parts[0]);
        
        if ($action === 'exit' || $action === 'q') break;

        if ($action === 'list') {
            echo "\n";
            foreach ($sites as $key => $url) {
                echo " - " . $cGreen . str_pad($key, 12) . $cReset . " -> $url\n";
            }
            echo "\nPress Enter to continue...";
            fgets(STDIN);
            continue;
        }

        if ($action === 'add') {
            echo "Enter shortcut name (e.g., reddit): ";
            $name = strtolower(trim(fgets(STDIN)));
            echo "Enter URL (e.g., https://reddit.com): ";
            $url = trim(fgets(STDIN));
            if ($name && $url) {
                $sites[$name] = $url;
                saveWebsites($dbFile, $sites);
                echo $cGreen . "✅ Added '$name'!\n" . $cReset;
            }
            sleep(1);
            continue;
        }

        if ($action === 'edit') {
            echo "Enter shortcut name to edit: ";
            $name = strtolower(trim(fgets(STDIN)));
            if (isset($sites[$name])) {
                echo "Current URL: {$sites[$name]}\n";
                echo "Enter new URL: ";
                $url = trim(fgets(STDIN));
                if ($url) {
                    $sites[$name] = $url;
                    saveWebsites($dbFile, $sites);
                    echo $cGreen . "✅ Updated '$name'!\n" . $cReset;
                }
            } else {
                echo $cRed . "❌ Not found!\n" . $cReset;
            }
            sleep(1);
            continue;
        }

        if ($action === 'remove') {
            echo "Enter shortcut name to remove: ";
            $name = strtolower(trim(fgets(STDIN)));
            if (isset($sites[$name])) {
                unset($sites[$name]);
                saveWebsites($dbFile, $sites);
                echo $cGreen . "✅ Removed '$name'!\n" . $cReset;
            } else {
                echo $cRed . "❌ Not found!\n" . $cReset;
            }
            sleep(1);
            continue;
        }

        if ($action === 'search') {
            echo "Search Google for: ";
            $query = trim(fgets(STDIN));
            if ($query) {
                $url = "https://www.google.com/search?q=" . urlencode($query);
                echo $cGreen . "🚀 Searching Google for '$query'...\n" . $cReset;
                openUrl($url);
                sleep(1);
            }
            continue;
        }

        if (isset($sites[$action])) {
            echo $cGreen . "🚀 Opening " . ucfirst($action) . "...\n" . $cReset;
            openUrl($sites[$action]);
            sleep(1);
            continue;
        }

        $bestMatch = '';
        $shortestDistance = -1;
        foreach (array_keys($sites) as $site) {
            $distance = levenshtein($action, $site);
            if ($shortestDistance == -1 || $distance < $shortestDistance) {
                $shortestDistance = $distance;
                $bestMatch = $site;
            }
        }
        
        if ($shortestDistance <= 3 && $shortestDistance > 0) {
            echo $cYellow . "⚠️  Did you mean '$bestMatch'? (y/n): " . $cReset;
            $ans = strtolower(trim(fgets(STDIN)));
            if ($ans === 'y') {
                echo $cGreen . "🚀 Opening " . ucfirst($bestMatch) . "...\n" . $cReset;
                openUrl($sites[$bestMatch]);
                sleep(1);
            }
        } else {
            echo $cRed . "❌ Unknown command. Try 'search' or 'add'.\n" . $cReset;
            sleep(1);
        }
    }
    echo "Goodbye! 👋\n";
} else {
    if (isset($sites[$cmd])) {
        openUrl($sites[$cmd]);
        echo $cGreen . "🚀 Opening " . ucfirst($cmd) . "...\n" . $cReset;
    } else {
        echo $cRed . "Unknown shortcut '$cmd'. Run 'ma-web' to see the menu.\n" . $cReset;
    }
}