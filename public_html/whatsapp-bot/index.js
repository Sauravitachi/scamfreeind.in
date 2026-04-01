const { Client, LocalAuth } = require('whatsapp-web.js');
const qrcode = require('qrcode-terminal');
const axios = require('axios');
const fs = require('fs');
const path = require('path');

const API_URL = 'http://192.168.1.13/api/whatsapp/lead';
const BOT_NAME = 'ScamFree India WhatsApp Bot';
const LOG_FILE = path.join(__dirname, 'bot.log');
const TIMEOUT = 15000;

function log(level, message, data = null) {
    const timestamp = new Date().toISOString();
    const logEntry = `[${timestamp}] [${level}] ${message}`;

    console.log(logEntry, data || '');

    try {
        fs.appendFileSync(
            LOG_FILE,
            logEntry + (data ? ' ' + JSON.stringify(data) : '') + '\n'
        );
    } catch (e) { }
}

const client = new Client({
    authStrategy: new LocalAuth(),
    puppeteer: {
        headless: true,
        args: ['--no-sandbox', '--disable-setuid-sandbox'],
    },
});

client.on('qr', (qr) => {
    log('INFO', 'Scan QR Code');
    qrcode.generate(qr, { small: true });
});

// READY
client.on('ready', () => {
    log('INFO', '✅ Bot Ready!');
});

client.on('message', async (msg) => {
    try {
        if (msg.from === 'status@broadcast') return;
        if (msg.from.includes('@g.us')) return;
        if (msg.fromMe) return;

        // Handle media messages even if they don't have a text body (voice, image, etc.)
        let messageBody = msg.body || '';
        if (!messageBody && msg.hasMedia) {
            messageBody = `[Media: ${msg.type || 'Media'}]`;
        }

        // If it's still empty and not media, check if it's a dynamic reply or list response
        if (!messageBody) {
             if (msg.selectedButtonId || msg.selectedRowId) {
                messageBody = `[Button/List Selection: ${msg.selectedButtonId || msg.selectedRowId}]`;
             } else {
                log('DEBUG', 'Ignoring message with no body and no media', { type: msg.type });
                return;
             }
        }

        const contact = await msg.getContact();
        let phone = contact.number;

        if (!phone) {
            phone = msg.from;
        }

        phone = phone.replace(/\D/g, '');

        // Clean WhatsApp 91 prefix for India numbers
        if (phone.length > 10 && phone.startsWith('91')) {
            phone = phone.slice(-10);
        }

        const name = contact.pushname || contact.name || 'Unknown';

        // Relaxed phone check: allow 10-15 digit numbers (to support international or long formats)
        if (!phone || phone.length < 10) {
            log('WARN', 'Invalid phone number format or too short', { phone, type: msg.type });
            return;
        }

        log('INFO', `📩 Incoming ${msg.type} from ${phone}: ${messageBody.substring(0, 50)}${messageBody.length > 50 ? '...' : ''}`);

        await axios.post(API_URL, {
            phone: phone,
            message: messageBody,
            name: name
        }, { timeout: TIMEOUT });

        log('INFO', `✅ Lead stored: ${phone}`);

    } catch (err) {
        log('ERROR', 'Message processing error', err.message);
    }
});
process.on('unhandledRejection', (err) => {
    log('ERROR', 'Unhandled Rejection', err);
});
log('INFO', `Starting ${BOT_NAME}`);
client.initialize();