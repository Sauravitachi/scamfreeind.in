# WhatsApp Lead Capture Bot

Autonomous WhatsApp bot for the ScamFree India platform that listens to incoming messages and forwards them to the Laravel API for automatic lead capture and scam complaint registration.

## Quick Start

### Prerequisites
- Node.js 14+ 
- npm or yarn
- Running Laravel instance (see main README)
- WhatsApp account (without 2FA for testing)

### Installation

```bash
# Install dependencies
npm install

# Create .env file (optional)
cp .env.example .env
```

### Running the Bot

```bash
# Start the bot
npm start
# or
node index.js
```

On first run:
1. A QR code will appear in the terminal
2. Open WhatsApp on your phone
3. Go to Settings → Linked Devices → Link a Device
4. Scan the QR code with your phone
5. Bot will say "WhatsApp Client is ready!"

## Features

✅ **Autonomous Message Listening** - Monitors incoming WhatsApp messages in real-time  
✅ **Automatic Lead Forwarding** - Sends lead data to Laravel API  
✅ **Phone Number Validation** - Validates phone numbers before processing  
✅ **Smart Filtering** - Ignores group messages, status broadcasts, empty messages  
✅ **Error Handling** - Graceful error handling with detailed logging  
✅ **Automatic Reconnection** - Reconnects on connection loss  
✅ **Log Management** - Maintains both console and file logs  

## Configuration

### Environment Variables

Create a `.env` file in the `whatsapp-bot` directory:

```env
# API Configuration
WHATSAPP_API_URL=http://127.0.0.1:8000/api/whatsapp/lead

# Bot Settings
BOT_NAME=ScamFree India WhatsApp Bot
API_TIMEOUT=10000

# Logging
LOG_LEVEL=info
LOG_FILE=./bot.log

# Feature Flags
IGNORE_GROUP_MESSAGES=true
IGNORE_STATUS_UPDATES=true
IGNORE_EMPTY_MESSAGES=true

# Node Environment
NODE_ENV=development
```

### API Configuration

Update the API URL in `index.js` or set the `WHATSAPP_API_URL` environment variable:

```javascript
const API_URL = process.env.WHATSAPP_API_URL || 'http://127.0.0.1:8000/api/whatsapp/lead';
```

For production, set the actual domain:
```env
WHATSAPP_API_URL=https://yourdomain.com/api/whatsapp/lead
```

## Scripts

```bash
# Start the bot
npm start
npm run dev

# Test the API endpoint
npm test
npm run test:api

# Clear authentication (to re-login with different account)
npm run clear-auth
```

## Testing

### Test API Endpoint

```bash
# Run the built-in API test suite
npm test
```

This will test:
- Valid leads with different phone formats
- Invalid requests (missing phone, empty payload)
- Phone number variations
- Duplicate detection

### Manual Testing with cURL

```bash
curl -X POST http://127.0.0.1:8000/api/whatsapp/lead \
  -H "Content-Type: application/json" \
  -d '{
    "phone": "919876543210@c.us",
    "message": "Test message"
  }'
```

### Using Postman

1. Import `WhatsApp_Lead_Capture_API.postman_collection.json` into Postman
2. Set the `base_url` variable to `http://127.0.0.1:8000`
3. Run the test requests

## Logs

The bot creates two types of logs:

### Console Output

```
[2026-03-18T10:30:45.123Z] [INFO] ==================================================
[2026-03-18T10:30:45.123Z] [INFO] ScamFree India WhatsApp Bot - QR CODE RECEIVED
[2026-03-18T10:30:45.123Z] [INFO] Scan the QR code below with your WhatsApp phone:
[2026-03-18T10:30:45.123Z] [INFO] ==================================================
[QR Code display]
```

### File Logs

Logs are also written to `bot.log` in the `whatsapp-bot` directory:

```bash
# View logs in real-time
tail -f bot.log

# Or on Windows
Get-Content bot.log -Wait
```

## Troubleshooting

### Issue: "QR Code Not Appearing"

```bash
# Clear authentication cache and restart
npm run clear-auth
npm start
```

### Issue: "API Connection Refused"

```bash
# Verify Laravel is running
curl http://127.0.0.1:8000/api/whatsapp/lead -X GET

# Check Laravel logs
tail -f ../storage/logs/laravel.log
```

### Issue: "Messages Not Being Forwarded"

1. Check console logs for error messages
2. Verify API endpoint is correct in `index.js`
3. Ensure `WHATSAPP_API_URL` environment variable is set correctly
4. Test API endpoint manually with `npm test`

### Issue: "WhatsApp Client Disconnected"

The client will attempt to reconnect automatically. If it keeps disconnecting:

1. Check internet connection stability
2. Clear authentication: `npm run clear-auth`
3. Re-authenticate by scanning QR code again

### Debug Mode

Set environment variable to see more detailed logs:

```bash
NODE_DEBUG=axios node index.js
```

## Performance Considerations

- **Message Processing**: ~100-500ms per message (depending on API response time)
- **Concurrent Messages**: Handles multiple concurrent messages
- **Memory Usage**: ~200-300MB base + message buffer
- **API Timeout**: Default 10 seconds (configurable)

## Architecture Flow

```
WhatsApp Message
    ↓
whatsapp-web.js Client
    ↓
Message Event Handler
    ↓
Phone Extraction & Validation
    ↓
Message Filtering
    ↓
HTTP POST to Laravel API
    ↓
Response Handling
    ↓
Logging (Console + File)
```

## Production Deployment

### Using PM2

```bash
# Install PM2 globally
npm install -g pm2

# Start bot with PM2
pm2 start index.js --name "whatsapp-bot"

# Save PM2 configuration
pm2 save

# Enable startup on reboot
pm2 startup
```

### Using systemd (Linux)

Create `/etc/systemd/system/whatsapp-bot.service`:

```ini
[Unit]
Description=WhatsApp Lead Capture Bot
After=network.target

[Service]
Type=simple
User=www-data
WorkingDirectory=/path/to/whatsapp-bot
ExecStart=/usr/bin/node index.js
Restart=always
RestartSec=10

[Install]
WantedBy=multi-user.target
```

Enable and start:
```bash
sudo systemctl enable whatsapp-bot
sudo systemctl start whatsapp-bot
sudo systemctl status whatsapp-bot
```

### Environment Variables (Production)

Set environment variables before running:

```bash
export WHATSAPP_API_URL=https://yourdomain.com/api/whatsapp/lead
export NODE_ENV=production
export API_TIMEOUT=15000

npm start
```

## API Payload Format

### Incoming Payload (from WhatsApp)

```javascript
{
    phone: "919876543210@c.us",
    message: "I want to report a scam"
}
```

### Processing

- Phone is cleaned: `919876543210@c.us` → `919876543210`
- Phone is validated (must be numeric, 10+ digits)
- Message is trimmed

### API Request to Laravel

```json
{
    "phone": "919876543210@c.us",
    "message": "I want to report a scam"
}
```

### Success Response (200)

```json
{
    "status": "success",
    "message": "Lead captured successfully."
}
```

### Error Response (500)

```json
{
    "status": "error",
    "message": "Failed to capture lead.",
    "error": "Phone number validation failed"
}
```

## Security Considerations

1. **Phone Number Privacy**: Phone numbers are stored in the database
2. **Message Content**: Stored in `customer_description` field as plaintext
3. **API Authentication**: Currently no authentication (add in production)
4. **Rate Limiting**: Not implemented (add in production)
5. **Data Validation**: All inputs are validated server-side

### Recommended Production Security

1. Add API authentication token:
```javascript
headers: {
    'Authorization': `Bearer ${process.env.API_TOKEN}`,
    'Content-Type': 'application/json'
}
```

2. Enable rate limiting on Laravel:
```php
Route::post('/whatsapp/lead', [...])
    ->middleware('throttle:60,1');
```

3. Use HTTPS in production
4. Implement webhook signing/verification

## File Structure

```
whatsapp-bot/
├── index.js                    # Main bot entry point
├── package.json               # Dependencies and scripts
├── test-api.js                # API test suite
├── .env.example               # Environment variables template
├── .gitignore                 # Git ignore rules
├── bot.log                    # Bot logs (created at runtime)
└── .wwebjs_auth/              # WhatsApp auth data (auto-generated)
    └── (session files)
```

## Dependencies

- **whatsapp-web.js** ^1.34.6 - WhatsApp client library
- **axios** ^1.13.6 - HTTP client for API requests
- **qrcode-terminal** ^0.12.0 - QR code generation in terminal

## Updating Dependencies

```bash
# Check for outdated packages
npm outdated

# Update all packages
npm update

# Update specific package
npm install whatsapp-web.js@latest
```

## Support

For issues or questions:
1. Check the troubleshooting section above
2. Review logs: `tail -f bot.log`
3. Test API manually: `npm test`
4. Check main project README: `../WHATSAPP_BOT_SETUP.md`

## License

ISC

## Author

ScamFree India
