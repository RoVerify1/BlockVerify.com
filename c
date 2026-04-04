<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - RobloxVerify</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container" id="dashboard-content">
        <div class="card">
            <div style="display:flex; justify-content:space-between; align-items:center;">
                <h2>Dashboard</h2>
                <button id="logout-btn" style="width:auto; padding:5px 10px; background:#dc3545;">Logout</button>
            </div>
            
            <p>Angemeldet als: <strong id="user-display">...</strong></p>
            <div id="status-display"></div>
            <p id="roblox-display"></p>

            <hr style="border-color:#333; margin: 20px 0;">

            <div id="verify-section">
                <h3>Roblox Verifizierung</h3>
                <p>Klicke unten, um einen Code zu erhalten. Füge ihn in deine Roblox "About Me" Sektion ein.</p>
                
                <button id="gen-code-btn" class="btn-primary">Code Generieren</button>
                
                <div id="code-instruction" class="hidden">
                    <p>Dein Code:</p>
                    <div id="verification-code-display">-----</div>
                    
                    <input type="text" id="roblox-input" placeholder="Dein Roblox Username">
                    <button id="verify-btn">Jetzt Verifizieren</button>
                </div>
            </div>
        </div>
    </div>
    <script type="module" src="app.js"></script>
</body>
</html>
