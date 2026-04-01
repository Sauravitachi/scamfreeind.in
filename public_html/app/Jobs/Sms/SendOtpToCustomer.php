<?php

namespace App\Jobs\Sms;

use App\Libraries\Sms;
use App\Models\Customer;
use App\Models\Otp;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendOtpToCustomer implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    protected int $customerId;

    protected ?string $userAgent = null;

    protected ?string $ip_address = null;

    protected string $sessionId;

    public function __construct(int $customerId, string $sessionId, ?string $userAgent = null, ?string $ip_address = null)
    {
        $this->customerId = $customerId;
        $this->sessionId = $sessionId;
        $this->userAgent = $userAgent;
        $this->ip_address = $ip_address;
    }

    public function handle(): void
    {
        $customer = Customer::findOrFail($this->customerId, ['id', 'phone_number']);
        $phoneNumber = "91{$customer->phone_number}";
        $otp = substr(str_shuffle('0123456789'), 0, 4);
        $otpExpireInMinutes = 60;

        // For now, do NOT send OTP via Msg91, just store and return it for popup
        // $response = Sms::make()
        //     ->template('')
        //     ->withPostParams([
        //         'otp' => $otp,
        //     ])
        //     ->withQueryParams([
        //         'otp_expiry' => $otpExpireInMinutes,
        //     ])
        //     ->send($phoneNumber);

        Otp::create([
            'phone_number' => $phoneNumber,
            'customer_id' => $customer->id,
            'otp' => $otp,
            'response_body' => null, // No SMS sent
            'session_id' => $this->sessionId,
            'expire_at' => $otpExpireInMinutes ? now()->addMinutes($otpExpireInMinutes) : null,
            'user_agent' => $this->userAgent,
            'ip_address' => $this->ip_address,
        ]);

        // Optionally: return or expose $otp for popup display (handled in controller/service)
    }
}
