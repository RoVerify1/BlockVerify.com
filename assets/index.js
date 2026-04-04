let lastPath = "";

function trackPageView(pageName) {
  console.log("Page view:", pageName);
  // Später: fetch zum Backend für Logs
}

export function navigate(page) {
  if(page === lastPath) return;
  lastPath = page;
  trackPageView(page);

  const content = document.getElementById("page-content");

  if(page === "home") {
    content.innerHTML = `
      <h1>Home</h1>
      <p>Willkommen bei deinem Roblox Dashboard!</p>
    `;
  } else if(page === "dashboard") {
    content.innerHTML = `
      <h1>Dashboard</h1>
      <p>Coins: 100</p>
      <p>Level: 5</p>
      <p>Items: Schwert, Schild</p>
    `;
  } else if(page === "staff") {
    content.innerHTML = `
      <h1>Staff Panel</h1>
      <p>Hier können Staff-Mitglieder Spieler verwalten.</p>
    `;
  } else if(page === "admin") {
    content.innerHTML = `
      <h1>Admin Panel</h1>
      <p>Hier siehst du alles und kannst Rollen verwalten.</p>
    `;
  }
}

// Default Page
navigate("home");
