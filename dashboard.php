<?php
session_start();
require_once 'includes/functions.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

// Hole aktuelle Daten von Supabase, um Sync zu gewährleisten
$users = supabase_request("/rest/v1/users?id=eq." . $_SESSION['user_id] . "&select=*");
$user = $users[0] ?? null;

if (!$user) {
    session_destroy();
    header("Location: index.php");
    exit;
}

// Session Update
$_SESSION['is_verified'] = $user['is_verified'];
$_SESSION['roblox_username'] = $user['roblox_username'];
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="dark-mode">
    <nav>
        <div class="logo">RobloxVerify</div>
        <a href="logout.php" class="btn-logout">Logout</a>
    </nav>

    <div class="container">
        <div class="dashboard-grid">
            <div class="card">
                <h2>Hi, <?= htmlspecialchars($user['username']) ?></h2>
                <p>Status: 
                    <span class="badge <?= $user['is_verified'] ? 'verified' : 'unverified' ?>">
                        <?= $user['is_verified'] ? '✅ Verifiziert' : '❌ Nicht Verifiziert' ?>
                    </span>
                </p>
            </div>

            <?php if (!$user['is_verified']): ?>
            <div class="card">
                <h3>Verifizierung</h3>
                
                <?php if (empty($user['roblox_username'])): ?>
                    <form id="set-roblox-form">
                        <label>Roblox Username:</label>
                        <input type="text" id="rbx-username" placeholder="Dein Name" required>
                        <button type="submit">Speichern</button>
                    </form>
                <?php else: ?>
                    <div class="verify-step">
                        <p>Kopiere diesen Code in deine Roblox Bio:</p>
                        <div class="code-box"><?= htmlspecialchars($user['verification_code']) ?></div>
                        <button id="check-verify-btn">Jetzt prüfen</button>
                        <p id="verify-status"></p>
                    </div>
                <?php endif; ?>
            </div>
            <?php else: ?>
            <div class="card success-card">
                <h3>🎉 Erfolgreich!</h3>
                <p>Roblox ID: <?= htmlspecialchars($user['roblox_id']) ?></p>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="assets/js/dashboard.js"></script>
    <script>
        // JS Variablen für Frontend Logik
    </script>
</body>
</html>