<?php
// api/game_check.php
require_once '../config/supabase.php';

$userId = $_GET['userid'] ?? 0;
$key = $_GET['key'] ?? '';

if ($key !== 'GeheimesSecret123') {
    http_response_code(403);
    echo json_encode(["error" => "Unauthorized"]);
    exit;
}

if ($userId > 0) {
    // Suche in Supabase nach roblox_id
    // Hinweis: Supabase Filter Syntax: ?column=eq.value
    $users = supabase_request("/rest/v1/users?roblox_id=eq." . intval($userId) . "&select=is_verified");
    
    if (!empty($users) && isset($users[0])) {
        echo json_encode(["is_verified" => $users[0]['is_verified']]);
    } else {
        echo json_encode(["is_verified" => false]);
    }
} else {
    echo json_encode(["error" => "Invalid ID"]);
}
?>