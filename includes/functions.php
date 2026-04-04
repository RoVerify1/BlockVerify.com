<?php
// includes/functions.php
require_once __DIR__ . '/../config/supabase.php';

function sendDiscordWebhook($message, $title, $color = 5814783) {
    $webhookUrl = "DEINE_DISCORD_WEBHOOK_URL"; 
    if (empty($webhookUrl) || strpos($webhookUrl, "DEINE_") === 0) return;

    $data = [
        "embeds" => [[
            "title" => $title,
            "description" => $message,
            "color" => $color,
            "timestamp" => date('c')
        ]]
    ];

    $ch = curl_init($webhookUrl);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_exec($ch);
    curl_close($ch);
}

function generateCode() {
    return strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));
}
?>