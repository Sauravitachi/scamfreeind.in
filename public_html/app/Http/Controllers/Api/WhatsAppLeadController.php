<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ScamLead;
use Illuminate\Support\Facades\Log;

class WhatsAppLeadController extends Controller
{
    public function store(Request $request)
    {
        Log::info('WhatsApp Lead Incoming:', $request->all());

        $request->validate([
            'phone' => 'required|string',
            'message' => 'nullable|string',
            'name' => 'nullable|string'
        ]);

        try {
            $phone = preg_replace('/\D/', '', $request->phone);
            
            $service = \App\Services\ScamLeadService::getInstance();

            // Check if number is bypassed
            if ($service->isBypassedNumber($phone)) {
                return response()->json(['status' => 'success', 'description' => 'number bypassed']);
            }
            $scamLead = new ScamLead([
                'phone_number' => $phone,
                'customer_description' => $request->message,
                'name' => $request->name,
                'scam_source_id' => 2, // WhatsApp
            ]);

            $service = \App\Services\ScamLeadService::getInstance();
            $service->fixCountryCodeForIndia($scamLead);

            // Check if customer already exists (transferred lead)
            $customer = \App\Models\Customer::wherePhoneDetails($scamLead->phone_number, $scamLead->country_code ?? 'in')->first();

            if ($customer) {
                // For existing customers, create a guest enquiry rather than a lead
                $service->createCustomerEnquiry($customer, 'whatsapp', $request->message);
                
                return response()->json(['status' => 'enquiry recorded']);
            }

            // check for existing lead (duplicate check with 1-min debounce)
            $existingLead = ScamLead::wherePhoneDetails($scamLead->phone_number, $scamLead->country_code ?? 'in')
                ->where('created_at', '>=', now()->subMinutes(1))
                ->first();

            if ($existingLead) {
                $existingLead->update([
                    'customer_description' => $request->message,
                    'name' => $request->name ?: $existingLead->name
                ]);

                return response()->json(['status' => 'lead updated']);
            }

            // Create new lead (syncing is handled by model observers)
            $scamLead->save();

            return response()->json(['status' => 'success']);

        } catch (\Exception $e) {
            Log::error('WhatsApp Lead Error: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'description' => 'Something went wrong while processing the lead.'
            ], 500);
        }
    }
}