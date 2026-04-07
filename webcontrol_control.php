<?php
// webcontrol_control.php
define('DATA_FILE', 'webcontrol_data.json');
define('COMMANDS_FILE', 'webcontrol_commands.json');
define('SETTINGS_FILE', 'webcontrol_settings.json');

// Создаем файлы если не существуют
if (!file_exists(DATA_FILE)) {
    file_put_contents(DATA_FILE, json_encode(['server_offline' => true]));
}
if (!file_exists(COMMANDS_FILE)) {
    file_put_contents(COMMANDS_FILE, json_encode(['commands' => []]));
}
if (!file_exists(SETTINGS_FILE)) {
    file_put_contents(SETTINGS_FILE, json_encode(['auto_refresh' => true, 'refresh_interval' => 3]));
}

function get_server_data() {
    if (!file_exists(DATA_FILE)) {
        return ['server_offline' => true];
    }
    $data = json_decode(file_get_contents(DATA_FILE), true);
    return $data ?: ['server_offline' => true];
}

function get_settings() {
    if (!file_exists(SETTINGS_FILE)) {
        return ['auto_refresh' => true, 'refresh_interval' => 3];
    }
    $settings = json_decode(file_get_contents(SETTINGS_FILE), true);
    return $settings ?: ['auto_refresh' => true, 'refresh_interval' => 3];
}

function save_settings($settings) {
    file_put_contents(SETTINGS_FILE, json_encode($settings));
}

function send_command($command) {
    $url = 'https://vrime-client.ru/webcontrol_api.php';

    // Используем PUT запрос как ожидает мод
    $data = json_encode(['command' => $command]);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer webcontrol2024'
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if (curl_error($ch)) {
        $response = json_encode(['status' => 'error', 'message' => 'CURL Error: ' . curl_error($ch)]);
    }

    curl_close($ch);

    return $response;
}

// Handle POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');

    $action = $_POST['action'] ?? '';
    $response = ['status' => 'error', 'message' => 'Unknown action'];

    switch ($action) {
        case 'send_command':
            $command = $_POST['command'] ?? '';
            if ($command) {
                $result = send_command($command);
                $response = ['status' => 'success', 'message' => 'Command sent', 'result' => $result];
            } else {
                $response = ['status' => 'error', 'message' => 'Empty command'];
            }
            break;

        case 'save_settings':
            $settings = [
                'auto_refresh' => ($_POST['auto_refresh'] ?? '') === 'true',
                'refresh_interval' => intval($_POST['refresh_interval'] ?? 3)
            ];
            save_settings($settings);
            $response = ['status' => 'success', 'message' => 'Settings saved'];
            break;

        case 'get_data':
            $server_data = get_server_data();
            $response = ['status' => 'success', 'data' => $server_data];
            break;
    }

    echo json_encode($response);
    exit;
}

$server_data = get_server_data();
$settings = get_settings();
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Minecraft Web Control v2.0</title>

    <!-- Bootstrap 5.3 + Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        :root {
            --bs-primary: #0d6efd;
            --bs-secondary: #6c757d;
            --bs-success: #198754;
            --bs-danger: #dc3545;
            --bs-warning: #ffc107;
            --bs-info: #0dcaf0;
            --bs-dark: #1a1a1a;
            --bs-darker: #0f0f0f;
        }

        body {
            background: linear-gradient(135deg, var(--bs-darker) 0%, var(--bs-dark) 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
            background: linear-gradient(45deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .card {
            background: rgba(33, 37, 41, 0.95);
            border: 1px solid #444;
            backdrop-filter: blur(10px);
            transition: all 0.3s ease;
        }

        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.3);
            border-color: var(--bs-primary);
        }

        .stat-card {
            text-align: center;
            padding: 1.5rem;
            border-radius: 15px;
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .player-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(45deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            color: white;
            margin-right: 10px;
        }

        .online-indicator {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: var(--bs-success);
            display: inline-block;
            margin-right: 5px;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.5; }
            100% { opacity: 1; }
        }

        .action-btn {
            margin: 2px;
            transition: all 0.2s ease;
        }

        .action-btn:hover {
            transform: scale(1.1);
        }

        .console-output {
            background: #000;
            color: #00ff00;
            font-family: 'Courier New', monospace;
            font-size: 0.9rem;
            height: 400px;
            overflow-y: auto;
        }

        .inventory-slot {
            width: 60px;
            height: 60px;
            border: 2px solid #444;
            border-radius: 5px;
            display: inline-flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            margin: 2px;
            background: rgba(255,255,255,0.1);
            transition: all 0.2s ease;
            cursor: pointer;
            position: relative;
        }

        .inventory-slot:hover {
            border-color: var(--bs-primary);
            transform: scale(1.05);
        }

        .inventory-slot.has-item {
            background: rgba(76, 175, 80, 0.2);
            border-color: #4CAF50;
        }

        .inventory-slot.empty-slot {
            background: rgba(255,255,255,0.05);
            border-color: #666;
        }

        .item-name {
            font-size: 0.7rem;
            text-align: center;
            max-width: 55px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .item-count {
            position: absolute;
            bottom: 2px;
            right: 2px;
            background: rgba(0,0,0,0.7);
            color: white;
            padding: 1px 4px;
            border-radius: 3px;
            font-size: 0.7rem;
        }

        .slot-number {
            position: absolute;
            top: 2px;
            left: 2px;
            background: rgba(0,0,0,0.7);
            color: #ccc;
            padding: 1px 4px;
            border-radius: 3px;
            font-size: 0.6rem;
        }

        .tab-pane {
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .health-bar {
            height: 8px;
            background: #444;
            border-radius: 4px;
            overflow: hidden;
        }

        .health-fill {
            height: 100%;
            background: linear-gradient(90deg, #dc3545, #ffc107, #198754);
            transition: width 0.3s ease;
        }

        .chat-message {
            border-left: 3px solid var(--bs-primary);
            padding-left: 10px;
            margin-bottom: 5px;
            animation: slideIn 0.3s ease;
        }

        @keyframes slideIn {
            from { opacity: 0; transform: translateX(-10px); }
            to { opacity: 1; transform: translateX(0); }
        }

        .server-status-online {
            color: var(--bs-success);
            animation: glow 2s infinite;
        }

        @keyframes glow {
            0% { text-shadow: 0 0 5px currentColor; }
            50% { text-shadow: 0 0 20px currentColor; }
            100% { text-shadow: 0 0 5px currentColor; }
        }

        .toast {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
        }

        .command-format {
            font-family: 'Courier New', monospace;
            background: rgba(0,0,0,0.3);
            padding: 2px 5px;
            border-radius: 3px;
            font-size: 0.9em;
        }

        .inventory-grid {
            display: grid;
            grid-template-columns: repeat(9, 1fr);
            gap: 5px;
            margin-bottom: 15px;
        }

        .armor-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 5px;
            margin-bottom: 15px;
        }

        .quick-actions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 10px;
            margin-bottom: 20px;
        }

        .effect-badge {
            margin: 2px;
            font-size: 0.75rem;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <i class="bi bi-minecraft me-2"></i>
                Minecraft Web Control v2.0
            </a>
            <div class="navbar-text">
                <span id="server-status" class="badge bg-danger">Offline</span>
                <small class="text-muted ms-2" id="last-update">Last update: Never</small>
            </div>
        </div>
    </nav>

    <div class="container-fluid py-4">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-lg-3">
                <!-- Server Stats -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-graph-up me-2"></i>Server Statistics
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-6 mb-3">
                                <div class="stat-number text-primary" id="online-players">0</div>
                                <small class="text-muted">Players Online</small>
                            </div>
                            <div class="col-6 mb-3">
                                <div class="stat-number text-info" id="server-tps">20.0</div>
                                <small class="text-muted">Server TPS</small>
                            </div>
                            <div class="col-6">
                                <div class="stat-number text-warning" id="memory-usage">0MB</div>
                                <small class="text-muted">Memory</small>
                            </div>
                            <div class="col-6">
                                <div class="stat-number text-success" id="uptime">0s</div>
                                <small class="text-muted">Uptime</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-lightning me-2"></i>Quick Actions
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <button class="btn btn-outline-warning" data-bs-toggle="modal" data-bs-target="#broadcastModal">
                                <i class="bi bi-broadcast me-2"></i>Broadcast
                            </button>
                            <button class="btn btn-outline-info" onclick="sendCommand('list')">
                                <i class="bi bi-list-ul me-2"></i>List Players
                            </button>
                            <button class="btn btn-outline-success" onclick="sendCommand('save-all')">
                                <i class="bi bi-save me-2"></i>Save World
                            </button>
                            <div class="btn-group">
                                <button class="btn btn-outline-primary" onclick="sendCommand('time set day')">Day</button>
                                <button class="btn btn-outline-primary" onclick="sendCommand('time set night')">Night</button>
                            </div>
                            <div class="btn-group">
                                <button class="btn btn-outline-info" onclick="sendCommand('weather clear')">Clear</button>
                                <button class="btn btn-outline-info" onclick="sendCommand('weather rain')">Rain</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- World Info -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-globe me-2"></i>World Information
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-2">
                            <small class="text-muted">Time:</small>
                            <div id="world-time" class="fw-bold">Day</div>
                        </div>
                        <div class="mb-2">
                            <small class="text-muted">Weather:</small>
                            <div id="world-weather" class="fw-bold">Clear</div>
                        </div>
                        <div class="mb-2">
                            <small class="text-muted">Difficulty:</small>
                            <div id="world-difficulty" class="fw-bold">Normal</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-lg-9">
                <ul class="nav nav-tabs" id="controlTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="players-tab" data-bs-toggle="tab" data-bs-target="#players" type="button">
                            <i class="bi bi-people me-2"></i>Players
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="inventory-tab" data-bs-toggle="tab" data-bs-target="#inventory" type="button">
                            <i class="bi bi-backpack me-2"></i>Inventory
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="console-tab" data-bs-toggle="tab" data-bs-target="#console" type="button">
                            <i class="bi bi-terminal me-2"></i>Console
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="chat-tab" data-bs-toggle="tab" data-bs-target="#chat" type="button">
                            <i class="bi bi-chat-dots me-2"></i>Chat
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="server-tab" data-bs-toggle="tab" data-bs-target="#server" type="button">
                            <i class="bi bi-hdd me-2"></i>Server
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="settings-tab" data-bs-toggle="tab" data-bs-target="#settings" type="button">
                            <i class="bi bi-gear me-2"></i>Settings
                        </button>
                    </li>
                </ul>

                <div class="tab-content mt-3" id="controlTabsContent">
                    <!-- Players Tab -->
                    <div class="tab-pane fade show active" id="players" role="tabpanel">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="card-title mb-0">Online Players</h5>
                                <div class="input-group" style="width: 300px;">
                                    <input type="text" class="form-control" placeholder="Search players..." id="playerSearch">
                                    <button class="btn btn-outline-secondary" type="button">
                                        <i class="bi bi-search"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-dark table-hover" id="playersTable">
                                        <thead>
                                            <tr>
                                                <th>Player</th>
                                                <th>Health</th>
                                                <th>Hunger</th>
                                                <th>Level</th>
                                                <th>Position</th>
                                                <th>Ping</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody id="playersTableBody">
                                            <!-- Dynamic content -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Inventory Tab -->
                    <div class="tab-pane fade" id="inventory" role="tabpanel">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Player Inventory Management</h5>
                            </div>
                            <div class="card-body">
                                <div class="row mb-4">
                                    <div class="col-md-6">
                                        <label class="form-label">Select Player</label>
                                        <select class="form-select" id="inventoryPlayerSelect" onchange="onInventoryPlayerSelect()">
                                            <option value="">Choose player...</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Quick Actions</label>
                                        <div class="btn-group w-100">
                                            <button class="btn btn-outline-warning" onclick="clearPlayerInventory()">
                                                <i class="bi bi-trash me-2"></i>Clear Inventory
                                            </button>
                                            <button class="btn btn-outline-success" onclick="healPlayer()">
                                                <i class="bi bi-heart me-2"></i>Heal Player
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <div id="inventoryContent" style="display: none;">
                                    <h6>Main Inventory</h6>
                                    <div class="inventory-grid mb-4" id="mainInventory">
                                        <!-- Dynamic inventory slots -->
                                    </div>

                                    <h6>Armor</h6>
                                    <div class="armor-grid mb-4" id="armorInventory">
                                        <!-- Dynamic armor slots -->
                                    </div>

                                    <h6>Give Items</h6>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <input type="text" class="form-control mb-2" placeholder="Item ID" id="giveItemId">
                                        </div>
                                        <div class="col-md-2">
                                            <input type="number" class="form-control mb-2" placeholder="Count" id="giveItemCount" value="1">
                                        </div>
                                        <div class="col-md-6">
                                            <button class="btn btn-success w-100" onclick="giveItemToPlayer()">
                                                <i class="bi bi-plus-circle me-2"></i>Give Item
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Console Tab -->
                    <div class="tab-pane fade" id="console" role="tabpanel">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="card-title mb-0">Server Console</h5>
                                <div>
                                    <button class="btn btn-outline-secondary btn-sm" onclick="clearConsole()">
                                        <i class="bi bi-trash me-2"></i>Clear
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="console-output p-3 rounded mb-3" id="consoleOutput">
                                    <div class="text-muted">Console output will appear here...</div>
                                </div>
                                <div class="input-group">
                                    <input type="text" class="form-control" placeholder="Enter server command..." id="consoleInput">
                                    <button class="btn btn-primary" onclick="sendConsoleCommand()">
                                        <i class="bi bi-send me-2"></i>Send
                                    </button>
                                </div>
                                <div class="mt-2">
                                    <small class="text-muted">Format: <span class="command-format">player.kick.PlayerName</span> or <span class="command-format">server.broadcast.Message</span></small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Chat Tab -->
                    <div class="tab-pane fade" id="chat" role="tabpanel">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="card-title mb-0">Chat Management</h5>
                                <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#chatMessageModal">
                                    <i class="bi bi-send me-2"></i>Send Message
                                </button>
                            </div>
                            <div class="card-body">
                                <div class="chat-messages p-3 rounded mb-3" style="height: 400px; overflow-y: auto; background: #1a1a1a;" id="chatMessages">
                                    <div class="text-muted">Chat messages will appear here...</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Server Tab -->
                    <div class="tab-pane fade" id="server" role="tabpanel">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card mb-4">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">World Management</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label class="form-label">Time Control</label>
                                            <div class="btn-group w-100">
                                                <button class="btn btn-outline-primary" onclick="sendCommand('world.time.day')">Day</button>
                                                <button class="btn btn-outline-primary" onclick="sendCommand('world.time.noon')">Noon</button>
                                                <button class="btn btn-outline-primary" onclick="sendCommand('world.time.night')">Night</button>
                                                <button class="btn btn-outline-primary" onclick="sendCommand('world.time.midnight')">Midnight</button>
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Weather Control</label>
                                            <div class="btn-group w-100">
                                                <button class="btn btn-outline-info" onclick="sendCommand('world.weather.clear')">Clear</button>
                                                <button class="btn btn-outline-info" onclick="sendCommand('world.weather.rain')">Rain</button>
                                                <button class="btn btn-outline-info" onclick="sendCommand('world.weather.thunder')">Storm</button>
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Difficulty</label>
                                            <div class="btn-group w-100">
                                                <button class="btn btn-outline-warning" onclick="sendCommand('world.difficulty.peaceful')">Peaceful</button>
                                                <button class="btn btn-outline-warning" onclick="sendCommand('world.difficulty.easy')">Easy</button>
                                                <button class="btn btn-outline-warning" onclick="sendCommand('world.difficulty.normal')">Normal</button>
                                                <button class="btn btn-outline-warning" onclick="sendCommand('world.difficulty.hard')">Hard</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card mb-4">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">Server Control</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="d-grid gap-2">
                                            <button class="btn btn-outline-success" onclick="sendCommand('server.save_all')">
                                                <i class="bi bi-save me-2"></i>Save World
                                            </button>
                                            <button class="btn btn-outline-warning" onclick="sendCommand('server.reload')">
                                                <i class="bi bi-arrow-clockwise me-2"></i>Reload
                                            </button>
                                            <button class="btn btn-outline-info" data-bs-toggle="modal" data-bs-target="#broadcastModal">
                                                <i class="bi bi-broadcast me-2"></i>Broadcast Message
                                            </button>
                                            <button class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#stopServerModal">
                                                <i class="bi bi-power me-2"></i>Stop Server
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Advanced Commands -->
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">Advanced Commands</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="quick-actions-grid">
                                            <button class="btn btn-outline-info btn-sm" onclick="sendCommand('entity.clear_items')">Clear Items</button>
                                            <button class="btn btn-outline-info btn-sm" onclick="sendCommand('entity.clear_mobs')">Clear Mobs</button>
                                            <button class="btn btn-outline-warning btn-sm" onclick="sendCommand('admin.whitelist_on')">Whitelist On</button>
                                            <button class="btn btn-outline-warning btn-sm" onclick="sendCommand('admin.whitelist_off')">Whitelist Off</button>
                                            <button class="btn btn-outline-secondary btn-sm" onclick="sendCommand('effect.clear_all')">Clear Effects</button>
                                            <button class="btn btn-outline-secondary btn-sm" onclick="sendCommand('world.seed')">Get Seed</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Settings Tab -->
                    <div class="tab-pane fade" id="settings" role="tabpanel">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Panel Settings</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Auto Refresh</label>
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" id="autoRefresh" checked>
                                                <label class="form-check-label" for="autoRefresh">Enable auto refresh</label>
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Refresh Interval</label>
                                            <select class="form-select" id="refreshInterval">
                                                <option value="1">1 second</option>
                                                <option value="2">2 seconds</option>
                                                <option value="3" selected>3 seconds</option>
                                                <option value="5">5 seconds</option>
                                                <option value="10">10 seconds</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">API Token</label>
                                            <input type="text" class="form-control" value="webcontrol2024" readonly>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Mod Version</label>
                                            <input type="text" class="form-control" value="2.0" readonly>
                                        </div>
                                    </div>
                                </div>
                                <button class="btn btn-primary" onclick="saveSettings()">
                                    <i class="bi bi-save me-2"></i>Save Settings
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modals -->
    <!-- Broadcast Modal -->
    <div class="modal fade" id="broadcastModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content bg-dark">
                <div class="modal-header">
                    <h5 class="modal-title">Broadcast Message</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Message</label>
                        <textarea class="form-control" id="broadcastMessage" placeholder="Enter your broadcast message..." rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="sendBroadcast()">Broadcast</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Player Actions Modal -->
    <div class="modal fade" id="playerActionsModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content bg-dark">
                <div class="modal-header">
                    <h5 class="modal-title">Player Actions - <span id="modalPlayerName"></span></h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Basic Actions</h6>
                            <div class="d-grid gap-2">
                                <button class="btn btn-outline-warning" onclick="kickPlayer()">
                                    <i class="bi bi-door-open me-2"></i>Kick Player
                                </button>
                                <button class="btn btn-outline-danger" onclick="banPlayer()">
                                    <i class="bi bi-ban me-2"></i>Ban Player
                                </button>
                                <button class="btn btn-outline-info" onclick="mutePlayer()">
                                    <i class="bi bi-mic-mute me-2"></i>Toggle Mute
                                </button>
                                <button class="btn btn-outline-success" onclick="healPlayerModal()">
                                    <i class="bi bi-heart me-2"></i>Heal Player
                                </button>
                                <button class="btn btn-outline-primary" onclick="feedPlayer()">
                                    <i class="bi bi-egg-fried me-2"></i>Feed Player
                                </button>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h6>Teleportation</h6>
                            <div class="mb-3">
                                <label class="form-label">Teleport to Player</label>
                                <select class="form-select" id="teleportTargetSelect">
                                    <option value="">Select player...</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Or Teleport to Coordinates</label>
                                <div class="row g-2">
                                    <div class="col-4">
                                        <input type="number" class="form-control" placeholder="X" id="teleportX">
                                    </div>
                                    <div class="col-4">
                                        <input type="number" class="form-control" placeholder="Y" id="teleportY">
                                    </div>
                                    <div class="col-4">
                                        <input type="number" class="form-control" placeholder="Z" id="teleportZ">
                                    </div>
                                </div>
                            </div>
                            <button class="btn btn-primary w-100" onclick="teleportPlayer()">
                                <i class="bi bi-geo-alt me-2"></i>Teleport
                            </button>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-12">
                            <h6>Game Mode</h6>
                            <div class="btn-group w-100">
                                <button class="btn btn-outline-primary" onclick="setGameMode('survival')">Survival</button>
                                <button class="btn btn-outline-primary" onclick="setGameMode('creative')">Creative</button>
                                <button class="btn btn-outline-primary" onclick="setGameMode('adventure')">Adventure</button>
                                <button class="btn btn-outline-primary" onclick="setGameMode('spectator')">Spectator</button>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-12">
                            <h6>Advanced Actions</h6>
                            <div class="btn-group w-100">
                                <button class="btn btn-outline-warning" onclick="toggleFlight()">Toggle Flight</button>
                                <button class="btn btn-outline-warning" onclick="toggleGodMode()">Toggle God</button>
                                <button class="btn btn-outline-info" onclick="strikeLightning()">Lightning</button>
                                <button class="btn btn-outline-info" onclick="createExplosion()">Explosion</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Stop Server Modal -->
    <div class="modal fade" id="stopServerModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content bg-dark">
                <div class="modal-header">
                    <h5 class="modal-title text-danger">Stop Server</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to stop the server? This will disconnect all players.</p>
                    <div class="mb-3">
                        <label class="form-label">Optional Message</label>
                        <input type="text" class="form-control" id="stopMessage" placeholder="Shutdown message...">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" onclick="stopServer()">Stop Server</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Chat Message Modal -->
    <div class="modal fade" id="chatMessageModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content bg-dark">
                <div class="modal-header">
                    <h5 class="modal-title">Send Chat Message</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Message</label>
                        <textarea class="form-control" id="chatMessage" placeholder="Enter your message..." rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="sendChatMessage()">Send Message</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Global variables
        let currentPlayerData = null;
        let autoRefresh = true;
        let refreshInterval = 3000;
        let currentSelectedPlayer = null;

        // Initialize dashboard
        function initDashboard() {
            loadSettings();
            fetchServerData();

            // Set up auto-refresh
            setInterval(() => {
                if (autoRefresh) {
                    fetchServerData();
                }
            }, refreshInterval);

            // Set up event listeners
            document.getElementById('consoleInput').addEventListener('keypress', (e) => {
                if (e.key === 'Enter') sendConsoleCommand();
            });

            document.getElementById('autoRefresh').addEventListener('change', (e) => {
                autoRefresh = e.target.checked;
            });

            document.getElementById('refreshInterval').addEventListener('change', (e) => {
                refreshInterval = parseInt(e.target.value) * 1000;
            });

            // Initialize tab switching
            initTabHandling();
        }

        // Initialize tab handling
        function initTabHandling() {
            const tabEl = document.querySelector('button[data-bs-target="#inventory"]');
            if (tabEl) {
                tabEl.addEventListener('shown.bs.tab', function (event) {
                    // When inventory tab is shown, refresh player list
                    updateInventoryPlayerSelect();
                });
            }
        }

        // Fetch server data
        async function fetchServerData() {
            try {
                const response = await fetch('webcontrol_control.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'action=get_data'
                });

                if (response.ok) {
                    const result = await response.json();
                    if (result.status === 'success') {
                        currentPlayerData = result.data;
                        updateDashboard(result.data);
                    }
                }
            } catch (error) {
                console.error('Error fetching server data:', error);
            }
        }

        // Update dashboard with new data
        function updateDashboard(data) {
            updateServerStatus(data);
            updatePlayersTable(data);
            updateWorldInfo(data);
            updateConsole(data);
            updateChat(data);
            updateInventoryPlayerSelect(); // Always update inventory player select
        }

        // Update server status
        function updateServerStatus(data) {
            const server = data.server_data?.server_status || {};
            const players = data.server_data?.players || [];

            document.getElementById('online-players').textContent = players.length;
            document.getElementById('server-tps').textContent = server.tps || '20.0';
            document.getElementById('memory-usage').textContent = `${server.memory_used || 0}MB`;
            document.getElementById('uptime').textContent = formatUptime(server.uptime);

            const statusElement = document.getElementById('server-status');
            if (data.server_offline || (Date.now() - data.last_update * 1000 > 120000)) {
                statusElement.className = 'badge bg-danger';
                statusElement.textContent = 'Offline';
            } else {
                statusElement.className = 'badge bg-success server-status-online';
                statusElement.textContent = 'Online';
            }

            const lastUpdate = data.last_update ? new Date(data.last_update * 1000).toLocaleTimeString() : 'Never';
            document.getElementById('last-update').textContent = `Last update: ${lastUpdate}`;
        }

        // Update players table
        function updatePlayersTable(data) {
            const tbody = document.getElementById('playersTableBody');
            const players = data.server_data?.players || [];

            tbody.innerHTML = '';

            players.forEach(player => {
                // Update table
                const healthPercent = (player.health / player.max_health) * 100;
                const hungerPercent = (player.hunger / 20) * 100;

                const row = `
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="player-avatar">${player.name.charAt(0).toUpperCase()}</div>
                                <div>
                                    <div class="fw-bold">${player.name}</div>
                                    <small class="text-muted">${player.ip}</small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="health-bar">
                                <div class="health-fill" style="width: ${healthPercent}%"></div>
                            </div>
                            <small>${player.health.toFixed(1)}/${player.max_health}</small>
                        </td>
                        <td>
                            <div class="health-bar">
                                <div class="health-fill" style="width: ${hungerPercent}%"></div>
                            </div>
                            <small>${player.hunger}/20</small>
                        </td>
                        <td>
                            <span class="badge bg-info">${player.level}</span>
                        </td>
                        <td>
                            <div class="text-muted small">${player.position}</div>
                            <div class="text-muted small">Dim: ${player.dimension}</div>
                        </td>
                        <td>
                            <span class="badge ${player.ping < 100 ? 'bg-success' : player.ping < 200 ? 'bg-warning' : 'bg-danger'}">${player.ping}ms</span>
                        </td>
                        <td>
                            <div class="btn-group">
                                <button class="btn btn-sm btn-outline-primary action-btn" onclick="openPlayerActions('${player.name}')" title="Actions">
                                    <i class="bi bi-gear"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-info action-btn" onclick="viewInventory('${player.name}')" title="Inventory">
                                    <i class="bi bi-backpack"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-warning action-btn" onclick="kickPlayerPrompt('${player.name}')" title="Kick">
                                    <i class="bi bi-door-open"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                `;
                tbody.innerHTML += row;
            });

            if (players.length === 0) {
                tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted">No players online</td></tr>';
            }
        }

        // Update inventory player select
        function updateInventoryPlayerSelect() {
            const playerSelect = document.getElementById('inventoryPlayerSelect');
            const teleportSelect = document.getElementById('teleportTargetSelect');
            const players = currentPlayerData?.server_data?.players || [];

            // Save current selection
            const currentSelection = playerSelect.value;
            const currentTeleportSelection = teleportSelect.value;

            playerSelect.innerHTML = '<option value="">Choose player...</option>';
            teleportSelect.innerHTML = '<option value="">Select player...</option>';

            players.forEach(player => {
                const option = `<option value="${player.name}">${player.name}</option>`;
                playerSelect.innerHTML += option;
                teleportSelect.innerHTML += option;
            });

            // Restore selection if player still exists
            if (currentSelection && players.some(p => p.name === currentSelection)) {
                playerSelect.value = currentSelection;
            }

            if (currentTeleportSelection && players.some(p => p.name === currentTeleportSelection)) {
                teleportSelect.value = currentTeleportSelection;
            }
        }

        // Update world info
        function updateWorldInfo(data) {
            const server = data.server_data?.server_status || {};
            document.getElementById('world-time').textContent = getTimeOfDay(server.world_time);
            document.getElementById('world-weather').textContent = capitalizeFirst(server.weather || 'clear');
            document.getElementById('world-difficulty').textContent = capitalizeFirst(server.difficulty || 'normal');
        }

        // Update console
        function updateConsole(data) {
            const consoleOutput = document.getElementById('consoleOutput');
            const logs = data.server_data?.console_logs || [];
            const commandOutputs = data.server_data?.command_outputs || [];

            if (logs.length > 0 || commandOutputs.length > 0) {
                consoleOutput.innerHTML = '';

                // Add command outputs first
                commandOutputs.forEach(output => {
                    const div = document.createElement('div');
                    div.textContent = output;
                    if (output.startsWith('> ')) {
                        div.style.color = '#ffff00'; // Yellow for commands
                        div.style.fontWeight = 'bold';
                    } else if (output.includes('❌')) {
                        div.style.color = '#ff5555'; // Red for errors
                    } else {
                        div.style.color = '#55ff55'; // Green for normal output
                    }
                    consoleOutput.appendChild(div);
                });

                // Add regular logs
                logs.forEach(log => {
                    const div = document.createElement('div');
                    div.textContent = log;
                    div.style.color = '#00ff00'; // Green for regular logs
                    consoleOutput.appendChild(div);
                });

                consoleOutput.scrollTop = consoleOutput.scrollHeight;
            }
        }

        // Update chat
        function updateChat(data) {
            const chatMessages = document.getElementById('chatMessages');
            const messages = data.server_data?.chat_messages || [];

            if (messages.length > 0) {
                chatMessages.innerHTML = '';
                messages.forEach(msg => {
                    const div = document.createElement('div');
                    div.className = 'chat-message';
                    div.textContent = msg;
                    chatMessages.appendChild(div);
                });
                chatMessages.scrollTop = chatMessages.scrollHeight;
            }
        }

        // Send command to server
        async function sendCommand(command) {
            try {
                console.log('Sending command:', command);

                const response = await fetch('webcontrol_control.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=send_command&command=${encodeURIComponent(command)}`
                });

                const result = await response.json();
                console.log('Command result:', result);

                showNotification(result.status === 'success' ? 'Command sent successfully' : 'Failed to send command',
                               result.status === 'success' ? 'success' : 'error');

                // Refresh data
                fetchServerData();

            } catch (error) {
                console.error('Error sending command:', error);
                showNotification('Error sending command: ' + error.message, 'error');
            }
        }

        // Player management functions
        function openPlayerActions(playerName) {
            currentSelectedPlayer = playerName;
            document.getElementById('modalPlayerName').textContent = playerName;
            new bootstrap.Modal(document.getElementById('playerActionsModal')).show();
        }

        function kickPlayer() {
            if (currentSelectedPlayer) {
                sendCommand(`player.kick.${currentSelectedPlayer}`);
                bootstrap.Modal.getInstance(document.getElementById('playerActionsModal')).hide();
            }
        }

        function kickPlayerPrompt(playerName) {
            if (confirm(`Are you sure you want to kick ${playerName}?`)) {
                sendCommand(`player.kick.${playerName}`);
            }
        }

        function banPlayer() {
            if (currentSelectedPlayer) {
                sendCommand(`player.ban.${currentSelectedPlayer}`);
                bootstrap.Modal.getInstance(document.getElementById('playerActionsModal')).hide();
            }
        }

        function mutePlayer() {
            if (currentSelectedPlayer) {
                sendCommand(`player.mute.${currentSelectedPlayer}`);
            }
        }

        function healPlayerModal() {
            if (currentSelectedPlayer) {
                sendCommand(`player.heal.${currentSelectedPlayer}`);
            }
        }

        function feedPlayer() {
            if (currentSelectedPlayer) {
                sendCommand(`player.feed.${currentSelectedPlayer}`);
            }
        }

        function toggleFlight() {
            if (currentSelectedPlayer) {
                sendCommand(`player.fly.${currentSelectedPlayer}`);
            }
        }

        function toggleGodMode() {
            if (currentSelectedPlayer) {
                sendCommand(`player.god.${currentSelectedPlayer}`);
            }
        }

        function strikeLightning() {
            if (currentSelectedPlayer) {
                sendCommand(`player.lightning.${currentSelectedPlayer}`);
            }
        }

        function createExplosion() {
            if (currentSelectedPlayer) {
                sendCommand(`player.explode.${currentSelectedPlayer}`);
            }
        }

        function setGameMode(mode) {
            if (currentSelectedPlayer) {
                sendCommand(`player.gamemode.${currentSelectedPlayer}.${mode}`);
            }
        }

        function teleportPlayer() {
            if (!currentSelectedPlayer) return;

            const targetPlayer = document.getElementById('teleportTargetSelect').value;
            const x = document.getElementById('teleportX').value;
            const y = document.getElementById('teleportY').value;
            const z = document.getElementById('teleportZ').value;

            if (targetPlayer) {
                sendCommand(`player.teleport.${currentSelectedPlayer}.${targetPlayer}`);
            } else if (x && y && z) {
                sendCommand(`player.teleport_to_coords.${currentSelectedPlayer}.${x}.${y}.${z}`);
            }

            bootstrap.Modal.getInstance(document.getElementById('playerActionsModal')).hide();
        }

        // Inventory management
        function onInventoryPlayerSelect() {
            const playerName = document.getElementById('inventoryPlayerSelect').value;
            if (playerName) {
                loadPlayerInventory(playerName);
            } else {
                document.getElementById('inventoryContent').style.display = 'none';
            }
        }

        function viewInventory(playerName) {
            document.getElementById('inventoryPlayerSelect').value = playerName;
            document.getElementById('inventory-tab').click();
            loadPlayerInventory(playerName);
        }

        function loadPlayerInventory(playerName) {
            const player = currentPlayerData?.server_data?.players?.find(p => p.name === playerName);
            if (!player) {
                document.getElementById('inventoryContent').style.display = 'none';
                return;
            }

            const mainInventory = document.getElementById('mainInventory');
            const armorInventory = document.getElementById('armorInventory');
            const inventoryContent = document.getElementById('inventoryContent');

            // Show inventory content
            inventoryContent.style.display = 'block';

            // Load main inventory (36 slots) - show only non-empty slots
            mainInventory.innerHTML = '';
            const inventoryItems = player.inventory?.items || [];

            // Create slots for all 36 positions, but only display non-empty ones with proper styling
            for (let i = 0; i < 36; i++) {
                const item = inventoryItems.find(invItem => invItem.slot === i);
                const slot = document.createElement('div');

                if (item) {
                    slot.className = 'inventory-slot has-item';
                    slot.title = `${item.name} x${item.count}`;
                    slot.innerHTML = `
                        <span class="slot-number">${i}</span>
                        <div class="item-name">${item.name}</div>
                        <span class="item-count">${item.count}</span>
                    `;
                    slot.onclick = () => clearInventorySlot(playerName, i);
                } else {
                    slot.className = 'inventory-slot empty-slot';
                    slot.innerHTML = `
                        <span class="slot-number">${i}</span>
                        <div class="item-name text-muted">Empty</div>
                    `;
                }

                mainInventory.appendChild(slot);
            }

            // Load armor (4 slots)
            armorInventory.innerHTML = '';
            const armorSlots = ['Helmet', 'Chestplate', 'Leggings', 'Boots'];
            const armorItems = player.armor || [];

            armorSlots.forEach((armorType, index) => {
                const armor = armorItems.find(a => a.slot === index);
                const slot = document.createElement('div');

                if (armor) {
                    slot.className = 'inventory-slot has-item';
                    slot.title = armor.name;
                    slot.innerHTML = `
                        <span class="slot-number">${index}</span>
                        <div class="item-name">${armor.name}</div>
                        <i class="bi bi-shield"></i>
                    `;
                } else {
                    slot.className = 'inventory-slot empty-slot';
                    slot.innerHTML = `
                        <span class="slot-number">${index}</span>
                        <div class="item-name text-muted">${armorType}</div>
                    `;
                }

                armorInventory.appendChild(slot);
            });
        }

        function clearPlayerInventory() {
            const player = document.getElementById('inventoryPlayerSelect').value;
            if (player) {
                if (confirm(`Are you sure you want to clear ${player}'s entire inventory?`)) {
                    sendCommand(`inventory.clear_all.${player}`);
                }
            } else {
                showNotification('Please select a player first', 'warning');
            }
        }

        function clearInventorySlot(playerName, slot) {
            if (confirm(`Clear slot ${slot} for ${playerName}?`)) {
                sendCommand(`inventory.clear_slot.${playerName}.${slot}`);
            }
        }

        function healPlayer() {
            const player = document.getElementById('inventoryPlayerSelect').value;
            if (player) {
                sendCommand(`player.heal.${player}`);
            } else {
                showNotification('Please select a player first', 'warning');
            }
        }

        function giveItemToPlayer() {
            const player = document.getElementById('inventoryPlayerSelect').value;
            const item = document.getElementById('giveItemId').value;
            const count = document.getElementById('giveItemCount').value;

            if (player && item) {
                sendCommand(`inventory.give.${player}.${item}.${count}`);
                document.getElementById('giveItemId').value = '';
                document.getElementById('giveItemCount').value = '1';
            } else {
                showNotification('Please select a player and enter item ID', 'warning');
            }
        }

        // Console functions
        function sendConsoleCommand() {
            const command = document.getElementById('consoleInput').value;
            if (command) {
                sendCommand(command);
                document.getElementById('consoleInput').value = '';
            }
        }

        function clearConsole() {
            document.getElementById('consoleOutput').innerHTML = '<div class="text-muted">Console cleared</div>';
        }

        // Chat functions
        function sendChatMessage() {
            const message = document.getElementById('chatMessage').value;
            if (message) {
                sendCommand(`chat.say.${message}`);
                document.getElementById('chatMessage').value = '';
                bootstrap.Modal.getInstance(document.getElementById('chatMessageModal')).hide();
            }
        }

        // Server functions
        function sendBroadcast() {
            const message = document.getElementById('broadcastMessage').value;
            if (message) {
                sendCommand(`server.broadcast.${message}`);
                bootstrap.Modal.getInstance(document.getElementById('broadcastModal')).hide();
            }
        }

        function stopServer() {
            const message = document.getElementById('stopMessage').value;
            if (message) {
                sendCommand(`chat.say.${message}`);
            }
            sendCommand('server.stop');
            bootstrap.Modal.getInstance(document.getElementById('stopServerModal')).hide();
        }

        // Settings
        function loadSettings() {
            const settings = <?php echo json_encode($settings); ?>;
            document.getElementById('autoRefresh').checked = settings.auto_refresh;
            document.getElementById('refreshInterval').value = settings.refresh_interval;
            autoRefresh = settings.auto_refresh;
            refreshInterval = settings.refresh_interval * 1000;
        }

        async function saveSettings() {
            const settings = {
                auto_refresh: document.getElementById('autoRefresh').checked,
                refresh_interval: parseInt(document.getElementById('refreshInterval').value)
            };

            try {
                const response = await fetch('webcontrol_control.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=save_settings&auto_refresh=${settings.auto_refresh}&refresh_interval=${settings.refresh_interval}`
                });

                const result = await response.json();
                showNotification(result.status === 'success' ? 'Settings saved' : 'Failed to save settings',
                               result.status === 'success' ? 'success' : 'error');

            } catch (error) {
                console.error('Error saving settings:', error);
                showNotification('Error saving settings', 'error');
            }
        }

        // Utility functions
        function formatUptime(timestamp) {
            if (!timestamp) return '0s';
            const seconds = Math.floor((Date.now() - timestamp) / 1000);
            const hours = Math.floor(seconds / 3600);
            const minutes = Math.floor((seconds % 3600) / 60);
            const secs = seconds % 60;

            if (hours > 0) return `${hours}h ${minutes}m`;
            if (minutes > 0) return `${minutes}m ${secs}s`;
            return `${secs}s`;
        }

        function getTimeOfDay(time) {
            if (!time) return 'Unknown';
            const timeOfDay = (time % 24000);
            if (timeOfDay < 1000) return 'Sunrise';
            if (timeOfDay < 6000) return 'Day';
            if (timeOfDay < 12000) return 'Noon';
            if (timeOfDay < 13000) return 'Sunset';
            if (timeOfDay < 18000) return 'Night';
            return 'Midnight';
        }

        function capitalizeFirst(str) {
            return str.charAt(0).toUpperCase() + str.slice(1);
        }

        function showNotification(message, type = 'info') {
            // Create toast notification
            const toast = document.createElement('div');
            toast.className = `toast align-items-center text-bg-${type} border-0`;
            toast.innerHTML = `
                <div class="d-flex">
                    <div class="toast-body">
                        ${message}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            `;

            document.body.appendChild(toast);
            const bsToast = new bootstrap.Toast(toast);
            bsToast.show();

            // Remove toast after hide
            toast.addEventListener('hidden.bs.toast', () => {
                toast.remove();
            });
        }

        // Initialize when page loads
        document.addEventListener('DOMContentLoaded', initDashboard);
    </script>
</body>
</html>