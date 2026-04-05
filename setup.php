<?php
// setup.php - Discord Server Setup Skript

// KONFIGURATION
$botToken = 'MTQ5MDQ2OTgyMjMzNTU1Mzc0Nw.GRa7GU.I2joRgyVj1_-JxXOzhbW7h6neADzJ2VYRVP64s'; // <-- HIER TOKEN EINFÜGEN
$guildId = '1490451930324009183';         // <-- HIER SERVER ID EINFÜGEN

// Sicherheits-Check: Nur ausführen, wenn ein Passwort mitgesendet wird
// Ändere 'meinGeheimesPasswort' zu etwas Sicherem!
if (!isset($_GET['pw']) || $_GET['pw'] !== 'meinGeheimesPasswort') {
    die("Zugriff verweigert. Bitte Passwort angeben.");
}

header('Content-Type: text/html; charset=utf-8');
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>RoverGate Server Setup</title>
    <style>
        body { font-family: sans-serif; background-color: #2c2f33; color: white; text-align: center; padding: 50px; }
        .box { background-color: #23272a; padding: 20px; border-radius: 10px; display: inline-block; max-width: 600px; }
        h1 { color: #7289da; }
        .status { margin-top: 20px; padding: 10px; border-radius: 5px; }
        .success { background-color: #43b581; }
        .error { background-color: #f04747; }
        .loading { color: #faa61a; }
    </style>
</head>
<body>

<div class="box">
    <h1>🚀 RoverGate Server Setup</h1>
    <p>Erstelle Kategorien, Channels und Rollen automatisch...</p>
    
    <div id="status" class="status loading">
        ⏳ Verbinde mit Discord API...
    </div>
</div>

<?php

// Funktion zum Senden von Requests an Discord
function discordRequest($method, $endpoint, $data = null) {
    global $botToken;
    $url = "https://discord.com/api/v10/" . $endpoint;
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bot " . $botToken,
        "Content-Type: application/json"
    ]);
    
    if ($data) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return [
        'code' => $httpCode,
        'body' => json_decode($response, true)
    ];
}

// Haupt-Logik
try {
    echo "<script>document.getElementById('status').innerHTML = '🔨 Erstelle Rollen...';</script>";
    flush();

    // 1. Rollen erstellen oder IDs holen
    // Hinweis: Um es einfach zu halten, erstellen wir die Rollen neu, falls sie nicht existieren.
    // In einer perfekten Welt würde man erst prüfen, ob sie da sind.
    
    $rolesToCreate = [
        ['name' => 'Unverified', 'color' => 16711680], // Rot
        ['name' => 'Verified', 'color' => 65280],      // Grün
        ['name' => 'Mod', 'color' => 255],             // Blau
        ['name' => 'Admin', 'color' => 16753920]       // Orange
    ];

    $roleIds = [];
    foreach ($rolesToCreate as $role) {
        // Prüfen ob Rolle existiert (vereinfacht: wir erstellen sie einfach und ignorieren Fehler wenn sie da ist)
        $res = discordRequest('POST', "guilds/$guildId/roles", [
            'name' => $role['name'],
            'color' => $role['color'],
            'reason' => 'RoverGate Setup'
        ]);
        
        if (isset($res['body']['id'])) {
            $roleIds[$role['name']] = $res['body']['id'];
        } else {
            // Falls Rolle schon existiert, müssen wir sie suchen (vereinfachter Hack: wir nehmen an, es klappt oder wir brechen ab)
            // Für dieses Beispiel gehen wir davon aus, dass es klappt oder die Rolle schon da ist.
            // Um es robust zu machen, müssten wir hier GET /guilds/$guildId/roles machen und ID suchen.
        }
        usleep(500000); // 0.5 Sekunde warten gegen Rate Limits
    }

    // Hole aktuelle Rollen-IDs falls sie schon existierten (wichtig!)
    $allRoles = discordRequest('GET', "guilds/$guildId/roles");
    foreach ($allRoles['body'] as $r) {
        if (in_array($r['name'], array_keys($roleIds)) || in_array($r['name'], ['Unverified', 'Verified', 'Mod', 'Admin'])) {
            $roleIds[$r['name']] = $r['id'];
        }
    }

    $everyoneId = $guildId; // Everyone ID ist gleich der Guild ID
    $unverifiedId = $roleIds['Unverified'] ?? null;
    $verifiedId = $roleIds['Verified'] ?? null;
    $modId = $roleIds['Mod'] ?? null;
    $adminId = $roleIds['Admin'] ?? null;

    if (!$unverifiedId || !$verifiedId) {
        throw new Exception("Konnten Rollen-IDs nicht ermitteln. Bitte lösche alte Rollen und versuche es erneut.");
    }

    echo "<script>document.getElementById('status').innerHTML = '📂 Erstelle Kategorien & Channels...';</script>";
    flush();

    // Hilfsfunktion Channel erstellen
    function createChannel($guildId, $name, $type, $parentId, $overwrites, $topic = null) {
        $data = [
            'name' => $name,
            'type' => $type, // 0 = Text, 4 = Kategorie
            'permission_overwrites' => $overwrites
        ];
        if ($parentId) $data['parent_id'] = $parentId;
        if ($topic) $data['topic'] = $topic;

        return discordRequest('POST', "guilds/$guildId/channels", $data);
    }

    // --- KATEGORIEN ERSTELLEN ---
    
    // 1. INFO
    $catInfo = createChannel($guildId, '🟦 INFO', 4, null, []);
    $catInfoId = $catInfo['body']['id'];
    sleep(1);

    // 2. VERIFY
    $catVerify = createChannel($guildId, '🟩 VERIFY', 4, null, []);
    $catVerifyId = $catVerify['body']['id'];
    sleep(1);

    // 3. COMMUNITY
    $catComm = createChannel($guildId, '🟨 COMMUNITY', 4, null, []);
    $catCommId = $catComm['body']['id'];
    sleep(1);

    // 4. ADMIN
    $catAdmin = createChannel($guildId, '🟥 ADMIN', 4, null, []);
    $catAdminId = $catAdmin['body']['id'];
    sleep(1);

    // 5. PRODUCT
    $catProd = createChannel($guildId, '🟪 WEBSITE / PRODUCT', 4, null, []);
    $catProdId = $catProd['body']['id'];
    sleep(1);

    // --- CHANNELS ERSTELLEN ---

    // Helper für Overwrites
    // allow/deny sind Arrays von Permission-Flags (als String oder Int). 
    // Discord API erwartet Strings für Allow/Deny in Overwrites.
    // VIEW_CHANNEL = 1024, SEND_MESSAGES = 2048
    
    $permView = "1024";
    $permSend = "2048";
    $permManage = "16"; // Manage Channels

    // INFO Channels (Alle lesen, keiner schreiben außer Admin)
    $infoOverwrites = [
        ['id' => $everyoneId, 'type' => 0, 'allow' => [$permView], 'deny' => [$permSend]],
        ['id' => $adminId, 'type' => 0, 'allow' => [$permView, $permSend, $permManage]]
    ];
    
    createChannel($guildId, 'welcome', 0, $catInfoId, $infoOverwrites, 'Begrüßung');
    sleep(1);
    createChannel($guildId, 'rules', 0, $catInfoId, $infoOverwrites, 'Regeln');
    sleep(1);
    createChannel($guildId, 'how-to-verify', 0, $catInfoId, $infoOverwrites, 'Anleitung');
    sleep(1);
    createChannel($guildId, 'resources', 0, $catInfoId, $infoOverwrites, 'Links');
    sleep(1);

    // VERIFY Channels
    $verifyOverwrites = [
        ['id' => $everyoneId, 'type' => 0, 'allow' => [$permView], 'deny' => [$permSend]],
        ['id' => $unverifiedId, 'type' => 0, 'allow' => [$permView, $permSend]],
        ['id' => $adminId, 'type' => 0, 'allow' => [$permView, $permSend, $permManage]]
    ];
    
    createChannel($guildId, 'verify', 0, $catVerifyId, $verifyOverwrites, 'Klicke hier zum Verifizieren');
    sleep(1);
    createChannel($guildId, 'verify-help', 0, $catVerifyId, $verifyOverwrites, 'Hilfe bei Problemen');
    sleep(1);

    // COMMUNITY Channels (Nur Verified sehen/schreiben)
    $commOverwrites = [
        ['id' => $everyoneId, 'type' => 0, 'deny' => [$permView]],
        ['id' => $unverifiedId, 'type' => 0, 'deny' => [$permView]],
        ['id' => $verifiedId, 'type' => 0, 'allow' => [$permView, $permSend]],
        ['id' => $modId, 'type' => 0, 'allow' => [$permView, $permSend]],
        ['id' => $adminId, 'type' => 0, 'allow' => [$permView, $permSend, $permManage]]
    ];

    createChannel($guildId, 'chat', 0, $catCommId, $commOverwrites, 'General Chat');
    sleep(1);
    createChannel($guildId, 'media', 0, $catCommId, $commOverwrites, 'Bilder & Videos');
    sleep(1);
    createChannel($guildId, 'off-topic', 0, $catCommId, $commOverwrites, 'Offtopic');
    sleep(1);

    // ADMIN Channels (Nur Mod/Admin)
    $adminOverwrites = [
        ['id' => $everyoneId, 'type' => 0, 'deny' => [$permView]],
        ['id' => $unverifiedId, 'type' => 0, 'deny' => [$permView]],
        ['id' => $verifiedId, 'type' => 0, 'deny' => [$permView]],
        ['id' => $modId, 'type' => 0, 'allow' => [$permView, $permSend]],
        ['id' => $adminId, 'type' => 0, 'allow' => [$permView, $permSend, $permManage]]
    ];

    createChannel($guildId, 'mod-chat', 0, $catAdminId, $adminOverwrites, 'Intern');
    sleep(1);
    createChannel($guildId, 'logs', 0, $catAdminId, $adminOverwrites, 'Logs');
    sleep(1);
    createChannel($guildId, 'reports', 0, $catAdminId, $adminOverwrites, 'Meldungen');
    sleep(1);

    // PRODUCT Channel
    $prodOverwrites = [
        ['id' => $everyoneId, 'type' => 0, 'deny' => [$permView]],
        ['id' => $unverifiedId, 'type' => 0, 'deny' => [$permView]],
        ['id' => $verifiedId, 'type' => 0, 'allow' => [$permView], 'deny' => [$permSend]], // Nur Lesen
        ['id' => $adminId, 'type' => 0, 'allow' => [$permView, $permSend, $permManage]]
    ];

    createChannel($guildId, 'website', 0, $catProdId, $prodOverwrites, 'RoverGate Infos');
    sleep(1);

    echo "<script>document.getElementById('status').className = 'status success'; document.getElementById('status').innerHTML = '✅ Fertig! Alle Channels und Rollen wurden erstellt.';</script>";

} catch (Exception $e) {
    echo "<script>document.getElementById('status').className = 'status error'; document.getElementById('status').innerHTML = '❌ Fehler: " . addslashes($e->getMessage()) . "';</script>";
}

?>

</body>
</html>
