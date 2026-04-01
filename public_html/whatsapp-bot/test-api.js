const axios = require('axios');

/**
 * WhatsApp Lead Capture - Manual API Test Script
 * 
 * This script tests the Laravel API endpoint with various scenarios
 * Usage: node test-api.js
 */

const API_URL = process.env.API_URL || 'http://127.0.0.1:8000/api/whatsapp/lead';

// Color codes for console output
const colors = {
    reset: '\x1b[0m',
    bright: '\x1b[1m',
    green: '\x1b[32m',
    red: '\x1b[31m',
    yellow: '\x1b[33m',
    blue: '\x1b[34m',
};

/**
 * Print colored console message
 */
function log(color, title, message = '') {
    console.log(`${colors[color]}${colors.bright}${title}${colors.reset} ${message}`);
}

/**
 * Test API endpoint with given parameters
 */
async function testAPI(testName, payload) {
    log('blue', `\n[TEST] ${testName}`);
    console.log(`Payload: ${JSON.stringify(payload, null, 2)}`);
    
    try {
        const response = await axios.post(API_URL, payload, {
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            },
            timeout: 10000,
        });
        
        log('green', '✓ SUCCESS', `Status: ${response.status}`);
        console.log(`Response: ${JSON.stringify(response.data, null, 2)}`);
        return true;
        
    } catch (error) {
        log('red', '✗ FAILED');
        
        if (error.response) {
            console.log(`Status: ${error.response.status}`);
            console.log(`Response: ${JSON.stringify(error.response.data, null, 2)}`);
        } else if (error.request) {
            console.log('No response from server');
            console.log('Make sure Laravel is running: php artisan serve');
        } else {
            console.log(`Error: ${error.message}`);
        }
        
        return false;
    }
}

/**
 * Run all tests
 */
async function runTests() {
    log('bright', '\n╔════════════════════════════════════════════════════════════╗');
    log('bright', '║  WhatsApp Lead Capture - API Test Suite                      ║');
    log('bright', '╚════════════════════════════════════════════════════════════╝');
    
    log('blue', `\nTesting API at: ${API_URL}\n`);
    
    let passedTests = 0;
    let failedTests = 0;
    
    // Test 1: Valid lead with phone in WhatsApp format
    if (await testAPI('Valid Lead - WhatsApp Format', {
        phone: '919876543210@c.us',
        message: 'I want to register a scam complaint'
    })) {
        passedTests++;
    } else {
        failedTests++;
    }
    
    // Test 2: Valid lead with phone without @c.us
    if (await testAPI('Valid Lead - Phone Only', {
        phone: '919876543210',
        message: 'I lost money to a fraudulent investment scheme'
    })) {
        passedTests++;
    } else {
        failedTests++;
    }
    
    // Test 3: Valid lead with longer message
    if (await testAPI('Valid Lead - Long Message', {
        phone: '918765432109',
        message: 'I received a call from someone claiming to be from a bank. They asked for my account details. I am worried this might be a scam.'
    })) {
        passedTests++;
    } else {
        failedTests++;
    }
    
    // Test 4: Invalid - Missing phone number
    if (!(await testAPI('Invalid - Missing Phone Number', {
        message: 'Test message without phone'
    }))) {
        log('green', '✓ Correctly rejected invalid request');
        passedTests++;
    } else {
        log('red', '✗ Should have rejected request with missing phone');
        failedTests++;
    }
    
    // Test 5: Invalid - Missing message
    if (await testAPI('Edge Case - Missing Message', {
        phone: '919876543210',
        message: ''
    })) {
        passedTests++;
    } else {
        failedTests++;
    }
    
    // Test 6: Invalid - Empty payload
    if (!(await testAPI('Invalid - Empty Payload', {}))) {
        log('green', '✓ Correctly rejected empty payload');
        passedTests++;
    } else {
        log('red', '✗ Should have rejected empty payload');
        failedTests++;
    }
    
    // Test 7: Duplicate phone (if same phone sent twice)
    log('yellow', '\nℹ Note: Test duplicate detection by running test 1 again');
    
    // Test 8: Different phone number variations
    if (await testAPI('Variation - Phone with +91 prefix', {
        phone: '+919876543210',
        message: 'Testing phone number format variation'
    })) {
        passedTests++;
    } else {
        failedTests++;
    }
    
    // Test 9: Phone with spaces
    if (await testAPI('Variation - Phone with spaces', {
        phone: '91 9876 543210',
        message: 'Testing phone with spaces'
    })) {
        passedTests++;
    } else {
        failedTests++;
    }
    
    // Print summary
    log('bright', '\n\n╔════════════════════════════════════════════════════════════╗');
    log('bright', '║  Test Summary                                              ║');
    log('bright', '╚════════════════════════════════════════════════════════════╝');
    log('green', `Passed: ${passedTests}`);
    log('red', `Failed: ${failedTests}`);
    
    if (failedTests === 0) {
        log('green', '\n✓ All tests completed successfully!');
        log('blue', 'Next steps:');
        console.log('  1. Start Laravel: php artisan serve');
        console.log('  2. Start WhatsApp Bot: cd whatsapp-bot && node index.js');
        console.log('  3. Send a WhatsApp message to the connected account');
        console.log('  4. Check Laravel logs: tail -f storage/logs/laravel.log');
        console.log('  5. Check database: SELECT * FROM scam_leads;');
    } else {
        log('red', '\n✗ Some tests failed!');
        log('yellow', 'Troubleshooting:');
        console.log('  - Ensure Laravel is running: php artisan serve');
        console.log('  - Check .env file: APP_URL should match API_URL');
        console.log('  - Check route: routes/api.php should have /api/whatsapp/lead');
        console.log('  - Check controller: WhatsAppLeadController should exist');
    }
    
    console.log('\n');
}

// Run tests
runTests();
