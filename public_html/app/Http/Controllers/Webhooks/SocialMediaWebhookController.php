<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Services\ScamLeadService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SocialMediaWebhookController extends Controller
{
    /**
     * Handle incoming social media messages.
     * 
     * Expects a JSON payload like:
     * {
     *    "source": "whatsapp",
     *    "identifier": "911234567890",
     *    "message": "Hello, I need help"
     * }
     */
    public function handle(Request $request)
    {
        Log::info('Social Media Webhook received', ['payload' => $request->all()]);

        $data = $request->validate([
            'source' => 'required|string', // e.g., 'whatsapp', 'instagram'
            'identifier' => 'required|string', // phone number or social ID
            'message' => 'required|string',
        ]);

        $source = $data['source'];
        $identifier = $data['identifier'];
        $message = $data['message'];

        // 1. Find Customer
        $customer = null;
        if ($source === 'whatsapp') {
            // For WhatsApp, identifier is often the full phone number
            $phoneNumber = $this->sanitizePhoneNumber($identifier);
            $customer = Customer::where('phone_number', $phoneNumber)->first();
        } else {
            // For Instagram/Facebook, we could match by name or a generic search if logic is added later
            // For now, we try a generic search on the identifier
            $customer = Customer::where('email', $identifier)->first(); 
        }

        if ($customer) {
            // 2. Reuse ScamLeadService to log enquiry and notify assigned user
            app(ScamLeadService::class)->createCustomerEnquiry(
                $customer,
                $source,
                "Social Media Message: $message"
            );

            return response()->json(['status' => 'success', 'message' => 'Notification sent to assigned user.']);
        }

        Log::warning('Customer matching failed for social media message', [
            'source' => $source,
            'identifier' => $identifier
        ]);

        // Return 200 to prevent webhook retries, but indicate ignored
        return response()->json(['status' => 'ignored', 'message' => 'Customer matching failed.']);
    }

    private function sanitizePhoneNumber($number)
    {
        // Remove non-numeric characters
        $number = preg_replace('/\D/', '', $number);
        
        // Handle common Indian format (91 prefix)
        if (strlen($number) === 12 && str_starts_with($number, '91')) {
            return substr($number, 2);
        }
        
        // Handle +91 format
        if (strlen($number) === 13 && str_starts_with($number, '+91')) {
            return substr($number, 3);
        }
        
        return $number;
    }
}
