<?php
// config/supabase.php

define('SUPABASE_URL', 'https://fqroctuwdzuawgmjttjv.supabase.co');
define('SUPABASE_KEY', 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6ImZxcm9jdHV3ZHp1YXdnbWp0dGp2Iiwicm9sZSI6InNlcnZpY2Vfcm9sZSIsImlhdCI6MTc3NTMxMTkxMCwiZXhwIjoyMDkwODg3OTEwfQ.yIQgBLzL5GOfYrZKJe9WdSZ-dav5QQFMYJYNvkoJf0Q'); // Nutze Service Role für Backend-Zugriff ohne RLS Einschränkungen

/**
 * Hilfsfunktion für Supabase API Calls
 */
function supabase_request($endpoint, $method = 'GET', $data = null) {
    $url = SUPABASE_URL . $endpoint;
    
    $headers = [
        'apikey: ' . SUPABASE_KEY,
        'Authorization: Bearer ' . SUPABASE_KEY,
        'Content-Type: application/json',
        'Prefer: return=representation' // Gibt die geänderten Daten zurück
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        if ($data) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
    } elseif ($method === 'PATCH') {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
        if ($data) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
    } elseif ($method === 'DELETE') {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
    }

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode >= 400) {
        return ['error' => true, 'message' => $response, 'code' => $httpCode];
    }

    return json_decode($response, true);
}
?>