# WhatsApp Lead Capture System - Setup Guide

## Overview
This document provides a complete setup and verification guide for the automated WhatsApp Lead Capture System that integrates a Node.js WhatsApp bot with the Laravel backend.

## System Components

### 1. **Backend (Laravel)**
- **Controller**: `app/Http/Controllers/Api/WhatsAppLeadController.php`
- **Model**: `app/Models/ScamLead.php`
- **Service**: `app/Services/ScamLeadService.php`
- **Route**: `POST /api/whatsapp/lead`
- **Migration**: `database/migrations/2025_12_22_121311_create_scam_leads_table.php`

### 2. **Backend (Node.js)**
- **Location**: `whatsapp-bot/`
- **Entry Point**: `whatsapp-bot/index.js`
- **Dependencies**: whatsapp-web.js, qrcode-terminal, axios

---

## Architecture Flow

```
WhatsApp Message
    ↓
Node.js Bot (whatsapp-web.js)
    ↓
Extract: phone, message, timestamp
    ↓
HTTP POST to Laravel API
    ↓
WhatsAppLeadController::store()
    ↓
Validates & Cleans Phone Number
    ↓
ScamLeadService::registerLeadFromExternalSource()
    ↓
Save to ScamLead Table
    ↓
Check for Duplicates & Existing Customers
    ↓
Database Stored (JSON Response)
```

---

## Prerequisites

### Laravel Setup
- PHP 8.1+
- Laravel 10+
- MySQL 5.7+ or compatible
- Composer installed

### Node.js Setup
- Node.js 14+
- npm or yarn

### System Requirements
- Port 3000+ (for potential bot variations)
- Port 8000 (Laravel development server)
- Stable internet connection
- WhatsApp account without 2FA (for testing)

---

## Step-by-Step Setup

### Step 1: Ensure Database Migration is Run

```bash
# Navigate to project root
cd c:\Users\aDMIN\Desktop\scamfreeindia

# Run all pending migrations
php artisan migrate

# Or specifically the scam_leads table
php artisan migrate --path=database/migrations/2025_12_22_121311_create_scam_leads_table.php
```

**Verify**: Check if `scam_leads` table exists in your database with the following columns:
- `id`
- `phone_number`
- `customer_description`
- `source` (should contain "whatsapp_bot")
- `country_code`
- `dial_code`
- `is_duplicate`
- `existing_customer_id`
- `timestamps`

### Step 2: Verify Laravel API Setup

```bash
# Test the API endpoint
curl -X POST http://localhost:8000/api/whatsapp/lead \
  -H "Content-Type: application/json" \
  -d '{
    "phone": "9876543210",
    "message": "Test message from curl"
  }'

# Expected Response:
# {
#   "status": "success",
#   "message": "Lead captured successfully."
# }
```

### Step 3: Install Node.js Dependencies

```bash
cd whatsapp-bot

# Install npm packages
npm install

# Verify installation
npm list
```

**Expected Packages**:
- axios: ^1.13.6
- qrcode-terminal: ^0.12.0
- whatsapp-web.js: ^1.34.6

### Step 4: Start Laravel Development Server

```bash
# Terminal 1: Start Laravel
php artisan serve

# Output should show:
# INFO  Server running on [http://127.0.0.1:8000]
```

The API endpoint should be accessible at: `http://127.0.0.1:8000/api/whatsapp/lead`

### Step 5: Start Node.js WhatsApp Bot

```bash
# Terminal 2: Start the bot
cd whatsapp-bot
node index.js

# You should see:
# QR RECEIVED. Scan the QR code below:
# [QR Code displayed in terminal]
```

### Step 6: Authenticate WhatsApp

1. **Scan QR Code**: Open WhatsApp on your mobile phone
2. **Navigate**: Settings → Linked Devices → Link a Device
3. **Scan**: Point your phone at the QR code in the terminal
4. **Wait**: Bot will say "WhatsApp Client is ready!"

---

## Verification Plan

### Manual Verification Steps

#### 1. **Check Bot Initialization**
```
Expected Console Output:
QR RECEIVED. Scan the QR code below:
[ASCII QR Code]
WhatsApp Client is ready!
```

#### 2. **Send Test Message**
- Open WhatsApp on your phone
- Send any message to the authenticated WhatsApp account
- Watch the bot console

#### 3. **Verify Bot Received Message**
```
Expected Console Output:
Received message from: 919876543210@c.us
Message: Test message
Successfully forwarded lead for 919876543210@c.us
```

#### 4. **Verify Laravel Received Lead**
Check Laravel logs:
```bash
# Check the log file
tail -f storage/logs/laravel.log
```

Expected output:
```
[2026-03-18 HH:MM:SS] local.INFO: WhatsApp Webhook Received: {"phone":"919876543210@c.us","message":"Test message"}
```

#### 5. **Verify Database Storage**
```bash
# Using Laravel tinker
php artisan tinker

# Check the latest lead
>>> \App\Models\ScamLead::latest()->first();

# Should display:
# => ScamLead {
#      id: 1,
#      phone_number: "9876543210",
#      customer_description: "Test message",
#      source: "whatsapp_bot",
#      country_code: "in",
#      ...
#    }
```

Or using MySQL directly:
```sql
SELECT * FROM scam_leads WHERE source = 'whatsapp_bot' ORDER BY created_at DESC LIMIT 1;
```

---

## Configuration Options

### Bot Configuration (whatsapp-bot/index.js)

#### Change API URL
```javascript
const API_URL = 'http://127.0.0.1:8000/api/whatsapp/lead';
// Change to your actual server URL in production
```

#### Customize Message Filters
```javascript
// Currently ignores:
// - Status broadcasts (status@broadcast)
// - Group messages (@g.us)

// To add more filters:
if (msg.from !== 'status@broadcast' && 
    !msg.from.includes('@g.us') &&
    !msg.from.includes('support')) {  // <- Add custom filter
    // Process message
}
```

#### Puppeteer Options
```javascript
puppeteer: {
    headless: true,
    args: [
        '--no-sandbox',
        '--disable-setuid-sandbox',
        // Add more options as needed
    ]
}
```

### Laravel API Response Customization

In `WhatsAppLeadController::store()`:
- Modify validation rules if needed
- Adjust phone number cleaning logic
- Change source identifier from 'whatsapp_bot'

---

## Troubleshooting

### Issue 1: QR Code Not Appearing
**Problem**: Bot starts but no QR code is displayed

**Solutions**:
```bash
# Clear authentication cache
rm -rf whatsapp-bot/.wwebjs_auth

# Or on Windows:
rmdir /s whatsapp-bot\.wwebjs_auth

# Restart bot
node index.js
```

### Issue 2: "API Connection Refused"
**Problem**: Bot can't connect to Laravel API

**Check**:
```bash
# Verify Laravel is running
curl http://127.0.0.1:8000/api/whatsapp/lead -X GET

# Should return 405 Method Not Allowed (expected for GET)
# If connection refused, start Laravel: php artisan serve
```

### Issue 3: Messages Not Being Forwarded
**Problem**: Bot receives messages but doesn't forward them

**Debugging**:
```javascript
// Add detailed logging in index.js
client.on('message', async msg => {
    console.log('Raw Message Object:', JSON.stringify({
        from: msg.from,
        body: msg.body,
        type: msg.type,
        timestamp: msg.timestamp
    }, null, 2));
    // ... rest of code
});
```

### Issue 4: "PHP Parse error: unexpected T_NS_SEPARATOR"
**Problem**: Tinker shell has syntax errors (as shown in your log)

**Solution**: This is from tinker debugging. Restart the application:
```bash
php artisan serve --force
```

### Issue 5: Phone Number Format Issues
**Problem**: Phone numbers not being recognized correctly

**Check** (in WhatsAppLeadController):
```php
// Phone comes as: "919876543210@c.us" from WhatsApp
// Cleaned to: "919876543210"
$phone = str_replace('@c.us', '', $request->input('phone'));

// If you need specific formatting:
// For India: Extract last 10 digits
$phone = substr($phone, -10); // "9876543210"
```

---

## Production Deployment

### Important Notes for Production

1. **Use Environment Variables**
```bash
# .env file
WHATSAPP_API_URL=https://yourdomain.com/api/whatsapp/lead
WHATSAPP_BOT_ENABLED=true
```

2. **Secure API Endpoint**
```php
// In routes/api.php
Route::post('/whatsapp/lead', [WhatsAppLeadController::class, 'store'])
    ->middleware('throttle:60,1'); // Rate limiting
```

3. **Run Bot as Service** (Linux)
```bash
# Create systemd service
sudo nano /etc/systemd/system/whatsapp-bot.service

[Unit]
Description=WhatsApp Bot Service
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

4. **Use PM2 for Process Management** (Recommended)
```bash
npm install -g pm2

# Start bot with PM2
pm2 start whatsapp-bot/index.js --name "whatsapp-bot"

# Make it run on restart
pm2 startup
pm2 save
```

---

## Testing with Multiple Accounts

You can run multiple bot instances for different WhatsApp accounts:

```bash
# Create separate authentication folders
mkdir -p whatsapp-bot/auth1
mkdir -p whatsapp-bot/auth2

# Create separate index files or use environment variables
# Then run multiple instances with different auth strategies
```

---

## API Response Examples

### Success Response
```json
{
  "status": "success",
  "message": "Lead captured successfully."
}
```

### Error Response
```json
{
  "status": "error",
  "message": "Failed to capture lead.",
  "error": "Phone number is invalid"
}
```

---

## Database Schema

### scam_leads Table Structure
```sql
CREATE TABLE scam_leads (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NULLABLE,
    email VARCHAR(255) NULLABLE,
    country_code VARCHAR(2) DEFAULT 'in',
    dial_code VARCHAR(5) NULLABLE,
    phone_number VARCHAR(20) NOT NULL,
    scam_amount DECIMAL(12, 2) NULLABLE,
    scam_type_id BIGINT NULLABLE,
    customer_description LONGTEXT NULLABLE,
    source VARCHAR(50) DEFAULT 'whatsapp_bot',
    is_duplicate BOOLEAN DEFAULT FALSE,
    existing_customer_id BIGINT NULLABLE,
    scam_source_id BIGINT NULLABLE,
    count INT DEFAULT 1,
    errors JSON NULLABLE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (existing_customer_id) REFERENCES customers(id),
    FOREIGN KEY (scam_source_id) REFERENCES scam_sources(id),
    FOREIGN KEY (scam_type_id) REFERENCES scam_types(id),
    INDEX idx_phone (phone_number),
    INDEX idx_source (source),
    INDEX idx_is_duplicate (is_duplicate)
);
```

---

## Quick Start Command Summary

```bash
# Terminal 1: Start Laravel
cd c:\Users\aDMIN\Desktop\scamfreeindia
php artisan migrate
php artisan serve

# Terminal 2: Start WhatsApp Bot
cd c:\Users\aDMIN\Desktop\scamfreeindia\whatsapp-bot
npm install
node index.js

# Terminal 3: Monitor Logs (Optional)
cd c:\Users\aDMIN\Desktop\scamfreeindia
tail -f storage/logs/laravel.log
```

---

## Support & Debugging

### Enable Debug Mode
```bash
# In .env
APP_DEBUG=true
LOG_LEVEL=debug

# Restart Laravel
php artisan serve
```

### View Full Logs
```bash
# Real-time log viewing
tail -f storage/logs/laravel.log

# View bot output with more verbose logging
NODE_DEBUG=* node whatsapp-bot/index.js
```

### Test API with Different Tools

**Postman**:
1. Create POST request to `http://localhost:8000/api/whatsapp/lead`
2. Body (raw JSON):
```json
{
  "phone": "919876543210@c.us",
  "message": "Test message"
}
```

**cURL**:
```bash
curl -X POST http://localhost:8000/api/whatsapp/lead \
  -H "Content-Type: application/json" \
  -d '{"phone":"919876543210@c.us","message":"Test message"}'
```

---

## Summary

Your WhatsApp Lead Capture System is now fully set up with:

✅ Laravel API endpoint for receiving WhatsApp leads  
✅ Node.js bot for autonomous WhatsApp message listening  
✅ Automatic lead storage with duplicate detection  
✅ Customer integration and tracking  
✅ Comprehensive error handling and logging  

Follow the verification steps above to ensure everything is working correctly!

---

Last Updated: March 18, 2026
