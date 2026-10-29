<?php
// ma-todo.php — CLI Todo List

if (php_sapi_name() !== 'cli') {
    exit("This script can only be run from the command line.\n");
}

$dataFile = __DIR__ . '\todos.json';

function loadTasks($file) {
    if (!file_exists($file)) return [];
    $raw = file_get_contents($file);
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}

function saveTasks($file, $tasks) {
    file_put_contents($file, json_encode(array_values($tasks), JSON_PRETTY_PRINT));
}

function showMenu() {
    echo "\n┌─────────────────────────────────────────────┐\n";
    echo "│              MA-TODO  v1.0                  │\n";
    echo "├─────────────────────────────────────────────┤\n";
    echo "│  ma-todo                   show this menu   │\n";
    echo "│  ma-todo list              list all tasks   │\n";
    echo "│  ma-todo add \"text\"        add a task       │\n";
    echo "│  ma-todo done <id>         mark complete    │\n";
    echo "│  ma-todo undone <id>       mark incomplete  │\n";
    echo "│  ma-todo edit <id> \"text\"  rename task      │\n";
    echo "│  ma-todo remove <id>       delete task      │\n";
    echo "│  ma-todo clear             delete done      │\n";
    echo "│  ma-todo clear-all         delete all       │\n";
    echo "└─────────────────────────────────────────────┘\n";
}

function renderList($tasks) {
    if (empty($tasks)) {
        echo "  (no tasks yet — try: ma-todo add \"my first task\")\n";
        return;
    }
    $done = 0;
    foreach ($tasks as $t) if (!empty($t['done'])) $done++;
    $total = count($tasks);
    $open  = $total - $done;

    echo "\n";
    foreach ($tasks as $i => $t) {
        $id    = $i + 1;
        $box   = !empty($t['done']) ? '[x]' : '[ ]';
        $text  = $t['text'];
        $date  = $t['created'] ?? '';
        if (!empty($t['done'])) {
            echo "  \033[90m$id. $box $text  ($date)\033[0m\n";
        } else {
            echo "  $id. $box $text  \033[90m($date)\033[0m\n";
        }
    }
    echo "\n  Total: $total  |  Open: $open  |  Done: $done\n\n";
}

$tasks = loadTasks($dataFile);
$cmd = $argv[1] ?? 'menu';

switch ($cmd) {
    case 'menu':
    case 'help':
        showMenu();
        renderList($tasks);
        break;
    case 'list':
        renderList($tasks);
        break;
    case 'add':
        if (!isset($argv[2]) || trim($argv[2]) === '') {
            exit("Error: task text is required.\n");
        }
        $tasks[] = ['text' => trim($argv[2]), 'done' => false, 'created' => date('Y-m-d H:i')];
        saveTasks($dataFile, $tasks);
        echo "Added: " . trim($argv[2]) . "\n";
        break;
    case 'done':
        $id = (int)($argv[2] ?? 0);
        if (isset($tasks[$id - 1])) {
            $tasks[$id - 1]['done'] = true;
            saveTasks($dataFile, $tasks);
            echo "Done: " . $tasks[$id - 1]['text'] . "\n";
        }
        break;
    case 'undone':
        $id = (int)($argv[2] ?? 0);
        if (isset($tasks[$id - 1])) {
            $tasks[$id - 1]['done'] = false;
            saveTasks($dataFile, $tasks);
            echo "Reopened: " . $tasks[$id - 1]['text'] . "\n";
        }
        break;
    case 'edit':
        $id = (int)($argv[2] ?? 0);
        if (isset($tasks[$id - 1]) && isset($argv[3])) {
            $tasks[$id - 1]['text'] = trim($argv[3]);
            saveTasks($dataFile, $tasks);
            echo "Updated #$id\n";
        }
        break;
    case 'remove':
        $id = (int)($argv[2] ?? 0);
        if (isset($tasks[$id - 1])) {
            $removed = $tasks[$id - 1]['text'];
            array_splice($tasks, $id - 1, 1);
            saveTasks($dataFile, $tasks);
            echo "Removed: $removed\n";
        }
        break;
    case 'clear':
        $before = count($tasks);
        $tasks = array_values(array_filter($tasks, fn($t) => empty($t['done'])));
        saveTasks($dataFile, $tasks);
        echo "Cleared " . ($before - count($tasks)) . " completed task(s).\n";
        break;
    case 'clear-all':
        saveTasks($dataFile, []);
        echo "All tasks deleted.\n";
        break;
    default:
        echo "Unknown command: $cmd\n";
        showMenu();
        break;
}