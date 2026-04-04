<?php
session_start();
require_once 'includes/functions.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = "Bitte alle Felder ausfüllen.";
    } else {
        if ($action === 'register') {
            // 1. Prüfen ob User existiert
            $check = supabase_request("/rest/v1/users?username=eq." . urlencode($username) . "&select=username");
            
            // Wenn Array nicht leer ist, existiert User
            if (!empty($check) && isset($check[0])) {
                $error = "Username bereits vergeben.";
            } else {
                // 2. Hashen und Einfügen
                $hash = password_hash($password, PASSWORD_BCRYPT);
                $result = supabase_request("/rest/v1/users", "POST", [
                    "username" => $username,
                    "password_hash" => $hash
                ]);

                if (isset($result['error'])) {
                    $error = "Fehler bei der Registrierung: " . $result['message'];
                } else {
                    sendDiscordWebhook("Neuer User: " . htmlspecialchars($username), "📝 Registrierung", 3066993);
                    $success = "Erfolgreich registriert! Bitte einloggen.";
                }
            }

        } elseif ($action === 'login') {
            // 1. User holen
            $users = supabase_request("/rest/v1/users?username=eq." . urlencode($username) . "&select=*");

            if (!empty($users) && isset($users[0])) {
                $user = $users[0];
                
                // 2. Passwort prüfen
                if (password_verify($password, $user['password_hash'])) {
                    session_regenerate_id(true);
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['is_verified'] = $user['is_verified'];
                    $_SESSION['roblox_username'] = $user['roblox_username'];
                    
                    sendDiscordWebhook("Login: " . htmlspecialchars($username), "🔑 Login", 5814783);
                    header("Location: dashboard.php");
                    exit;
                } else {
                    $error = "Falsches Passwort.";
                }
            } else {
                $error = "User nicht gefunden.";
            }
        }
    }
}
?>
<!-- HTML Teil bleibt identisch zum vorherigen Beispiel -->
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="dark-mode">
    <div class="container">
        <div class="card">
            <h1>Roblox Verify (Supabase)</h1>
            <?php if ($error): ?><div class="alert error"><?= $error ?></div><?php endif; ?>
            <?php if ($success): ?><div class="alert success"><?= $success ?></div><?php endif; ?>
            
            <div class="tabs">
                <button onclick="showForm('login')" class="active" id="tab-login">Login</button>
                <button onclick="showForm('register')" id="tab-register">Registrieren</button>
            </div>

            <form id="login-form" method="POST">
                <input type="hidden" name="action" value="login">
                <input type="text" name="username" placeholder="Benutzername" required>
                <input type="password" name="password" placeholder="Passwort" required>
                <button type="submit">Einloggen</button>
            </form>

            <form id="register-form" method="POST" style="display:none;">
                <input type="hidden" name="action" value="register">
                <input type="text" name="username" placeholder="Benutzername" required>
                <input type="password" name="password" placeholder="Passwort" required>
                <button type="submit">Registrieren</button>
            </form>
        </div>
    </div>
    <script src="assets/js/dashboard.js"></script>
</body>
</html>