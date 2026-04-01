# WhatsApp Lead Capture System - QUICK START GUIDE

Complete setup in 5 minutes!

## Prerequisites Check

- ✅ PHP 8.1+
- ✅ Node.js 14+
- ✅ Laravel 10+ installed
- ✅ MySQL running
- ✅ WhatsApp account (without 2FA for testing)

---

## Step 1: Verify System Components (2 minutes)

### Windows Users:
```cmd
cd c:\Users\aDMIN\Desktop\scamfreeindia
verify-whatsapp-system.bat
```

### Linux/Mac Users:
```bash
cd /path/to/scamfreeindia
bash verify-whatsapp-system.sh
```

**Expected Output**: All checks passed ✓

---

## Step 2: Run Database Migrations (1 minute)

```bash
php artisan migrate
```

This creates the `scam_leads` table to store WhatsApp messages.

---

## Step 3: Start Laravel Server (Terminal 1)

```bash
php artisan serve
```

**Expected Output**:
```
INFO  Laravel development server started at http://127.0.0.1:8000
```

---

## Step 4: Install WhatsApp Bot Dependencies (Terminal 2)

```bash
cd whatsapp-bot
npm install
```

---

## Step 5: Start WhatsApp Bot (Terminal 2)

```bash
npm start
```

**Expected Output**:
```
QR RECEIVED. Scan the QR code below:
[ASCII QR Code appears here]
```

---

## Step 6: Authenticate with WhatsApp

1. **Open WhatsApp** on your mobile phone
2. **Go to**: Settings → Linked Devices → Link a Device
3. **Point your phone** at the QR code displayed in the terminal
4. **Wait** for the bot to say: `WhatsApp Client is ready!`

---

## Step 7: Test the System

### Option A: Send a WhatsApp Message (Recommended)
1. Open WhatsApp on your phone
2. Send any message to the authenticated WhatsApp account
3. Watch the bot terminal - you should see:
   ```
   Message received from: 919876543210
   Message content: "Your message"
   
   ✓ Lead captured successfully for 919876543210
   ```

### Option B: Test with API (Terminal 3)

```bash
cd whatsapp-bot
npm test
```

This runs automated API tests.

---

## Step 8: Verify Data in Database

### Using Laravel Tinker:
```bash
php artisan tinker

>>> \App\Models\ScamLead::latest()->first();
```

You should see your lead data:
```
ScamLead {
  id: 1,
  phone_number: "9876543210",
  customer_description: "Your message",
  source: "whatsapp_bot",
  country_code: "in",
  created_at: "2026-03-18 10:30:45"
}
```

### Using MySQL:
```bash
mysql -u root -p

use scamfreeindiatest;
SELECT * FROM scam_leads WHERE source = 'whatsapp_bot' ORDER BY created_at DESC LIMIT 5;
```

---

## Verification Checklist

- [ ] Laravel server running (http://127.0.0.1:8000)
- [ ] WhatsApp bot authenticated (shows "QR RECEIVED")
- [ ] First WhatsApp message received in bot console
- [ ] API response shows "Lead captured successfully"
- [ ] Data appears in database `scam_leads` table
- [ ] Source field shows "whatsapp_bot"

---

## Next Steps

### 1. **Check Logs** (Real-time monitoring)

**Laravel Logs**:
```bash
tail -f storage/logs/laravel.log
```

**Bot Logs**:
```bash
tail -f whatsapp-bot/bot.log
```

### 2. **Test Different Scenarios**

- Send multiple messages (see they're captured)
- Send from different phone numbers
- Send longer messages
- Check for duplicate detection

### 3. **Configure for Production** (Later)

In `whatsapp-bot/.env`:
```env
WHATSAPP_API_URL=https://yourdomain.com/api/whatsapp/lead
NODE_ENV=production
```

### 4. **Set Up Process Manager** (For production)

```bash
npm install -g pm2

pm2 start whatsapp-bot/index.js --name "whatsapp-bot"
pm2 save
pm2 startup
```

---

## Troubleshooting Quick Reference

| Problem | Solution |
|---------|----------|
| No QR code appears | `npm run clear-auth` in whatsapp-bot, then restart |
| "Connection refused" to API | Ensure Laravel is running: `php artisan serve` |
| Messages not forwarding | Check API URL in `whatsapp-bot/index.js` |
| Database table not found | Run `php artisan migrate` |
| Port 8000 already in use | Kill process: `php artisan serve --port=8001` |
| Bot disconnects | Automatic reconnection enabled, check internet |

---

## API Reference

### Send Lead via API

```bash
curl -X POST http://127.0.0.1:8000/api/whatsapp/lead \
  -H "Content-Type: application/json" \
  -d '{
    "phone": "919876543210@c.us",
    "message": "I want to report a scam"
  }'
```

### Response (Success)
```json
{
  "status": "success",
  "message": "Lead captured successfully."
}
```

### Response (Error)
```json
{
  "status": "error",
  "message": "Failed to capture lead.",
  "error": "Phone number validation failed"
}
```

---

## Files Created/Modified

✅ **New Files**:
- `WHATSAPP_BOT_SETUP.md` - Comprehensive setup guide
- `whatsapp-bot/test-api.js` - API test suite
- `whatsapp-bot/.env.example` - Environment template
- `whatsapp-bot/.gitignore` - Git ignore rules
- `whatsapp-bot/README.md` - Bot documentation
- `verify-whatsapp-system.bat` - Windows verification script
- `verify-whatsapp-system.sh` - Linux verification script
- `WhatsApp_Lead_Capture_API.postman_collection.json` - Postman collection

✅ **Enhanced Files**:
- `whatsapp-bot/index.js` - Improved with better logging and error handling
- `whatsapp-bot/package.json` - Added useful scripts

✅ **Existing Files** (Already in place):
- `app/Http/Controllers/Api/WhatsAppLeadController.php`
- `app/Models/ScamLead.php`
- `app/Services/ScamLeadService.php`
- `routes/api.php`
- `database/migrations/2025_12_22_121311_create_scam_leads_table.php`

---

## Architecture Summary

```
WhatsApp Message
    ↓
Node.js Bot (whatsapp-web.js)
    ↓
Creates POST request with phone & message
    ↓
HTTP → http://127.0.0.1:8000/api/whatsapp/lead
    ↓
WhatsAppLeadController validates & processes
    ↓
ScamLeadService stores in database
    ↓
✓ Lead saved with source="whatsapp_bot"
```

---

## Support Resources

1. **Full Setup Guide**: See `WHATSAPP_BOT_SETUP.md`
2. **Bot Documentation**: See `whatsapp-bot/README.md`
3. **API Testing**: Run `npm test` in whatsapp-bot directory
4. **Console Logs**: Watch both terminals for real-time feedback
5. **Database Queries**: Use Laravel Tinker or MySQL directly

---

## Success Indicators

✓ **Bot Terminal Shows**:
```
WhatsApp Client is ready!
Message received from: 919876543210
✓ Lead captured successfully for 919876543210
```

✓ **Laravel Logs Show**:
```
[2026-03-18 10:30:45] local.INFO: WhatsApp Webhook Received: 
{"phone":"919876543210@c.us","message":"test message"}
```

✓ **Database Query Returns**:
```
SELECT * FROM scam_leads WHERE source = 'whatsapp_bot' LIMIT 1;

id | phone_number | customer_description | source | created_at
1  | 9876543210   | test message        | whatsapp_bot | 2026-03-18
```

---

## System Status Commands

Check if everything is running:

```bash
# Laravel status
curl http://127.0.0.1:8000/api/whatsapp/lead -X GET
# Should return 405 Method Not Allowed (expected)

# Bot running?
ps aux | grep "node index.js"

# MySQL running?
mysql -u root -e "SELECT 1;"

# Ports in use?
lsof -i :8000  # Laravel
lsof -i :3306  # MySQL
```

---

## Ready to Go! 🚀

You now have a fully functional WhatsApp Lead Capture System:

- ✅ Autonomous WhatsApp bot
- ✅ Laravel API backend
- ✅ Automatic database storage
- ✅ Real-time message capture
- ✅ Comprehensive logging
- ✅ Error handling

**Start capturing leads immediately!**

---

*Last Updated: March 18, 2026*
*For detailed documentation, see WHATSAPP_BOT_SETUP.md*
