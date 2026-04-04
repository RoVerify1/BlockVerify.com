<?php
session_start();
require_once 'includes/functions.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Nicht eingeloggt']);
    exit;
}

$action = $_POST['action'] ?? '';
$userId = $_SESSION['user_id'];

// Rate Limiting
if (!isset($_SESSION['last_verify_attempt'])) $_SESSION['last_verify_attempt'] = 0;
if (time() - $_SESSION['last_verify_attempt'] < 10) {
    echo json_encode(['success' => false, 'message' => 'Warte 10 Sekunden.']);
    exit;
}
$_SESSION['last_verify_attempt'] = time();

if ($action === 'set_username') {
    $rbxUser = trim($_POST['username'] ?? '');
    if (empty($rbxUser)) {
        echo json_encode(['success' => false, 'message' => 'Fehler']);
        exit;
    }
    
    $code = generateCode();
    
    // Update Supabase
    $result = supabase_request("/rest/v1/users?id=eq.$userId", "PATCH", [
        "roblox_username" => $rbxUser,
        "verification_code" => $code
    ]);

    if (isset($result['error'])) {
        echo json_encode(['success' => false, 'message' => 'DB Fehler']);
    } else {
        echo json_encode(['success' => true]);
    }
    exit;
}

if ($action === 'check_verification') {
    // Hole User Daten
    $users = supabase_request("/rest/v1/users?id=eq.$userId&select=*");
    $userData = $users[0] ?? null;

    if (!$userData || empty($userData['verification_code'])) {
        echo json_encode(['success' => false, 'message' => 'Fehler']);
        exit;
    }

    $rbxUsername = $userData['roblox_username'];
    $expectedCode = $userData['verification_code'];

    // Python Script aufrufen
    $command = escapeshellcmd("python3 api/check_roblox.py " . escapeshellarg($rbxUsername) . " " . escapeshellarg($expectedCode));
    $output = shell_exec($command);
    $pyResult = json_decode($output, true);

    if ($pyResult && $pyResult['verified'] === true) {
        $robloxId = $pyResult['roblox_id'] ?? 0;
        
        // Update DB: Verified = true
        supabase_request("/rest/v1/users?id=eq.$userId", "PATCH", [
            "is_verified" => true,
            "roblox_id" => $robloxId
        ]);
        
        sendDiscordWebhook("Verified: " . $_SESSION['username'], "✅ Erfolg", 5763719);
        echo json_encode(['success' => true, 'message' => 'Erfolgreich!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Code nicht gefunden.']);
    }
    exit;
}
?>