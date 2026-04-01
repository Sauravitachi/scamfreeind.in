<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Http\Requests\Api\ContactMessageRequest;
use App\Models\ContactMessage;

class ContactController extends Controller
{
    /**
     * Store a newly created contact message in storage.
     */
    public function store(ContactMessageRequest $request)
    {
        $contactMessage = ContactMessage::create($request->validated());

        try {
            $recipient = env('CONTACT_EMAIL', config('mail.from.address'));
            \Illuminate\Support\Facades\Mail::to($recipient)
                ->send(new \App\Mail\ContactReceived($contactMessage));
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to send contact email: ' . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => 'Thank you for contacting us. We will get back to you soon!',
            'data' => $contactMessage
        ], 201);
    }
}
