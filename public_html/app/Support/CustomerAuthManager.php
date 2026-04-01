<?php

namespace App\Support;

use App\Models\Customer;
use Illuminate\Support\Facades\Session;

class CustomerAuthManager
{
    protected string $sessionKey = 'customer:auth';

    protected ?Customer $user = null;

    protected string $redirectUsersToUrl;

    protected string $redirectGuestsToUrl;

    public function __construct()
    {
        $this->redirectUsersToUrl = route('customer.home.index');
        $this->redirectGuestsToUrl = route('customer.login');
    }

    public function usersRedirectUrl(): string
    {
        return $this->redirectUsersToUrl ?? 'customer/login';
    }

    public function guestsRedirectUrl(): string
    {
        return $this->redirectGuestsToUrl ?? 'customer/dashboard';
    }

    public function login(int|Customer $customer): void
    {
        if (is_int($customer)) {
            $customer = Customer::findOrFail($customer, ['id']);
        }
        Session::put($this->sessionKey, $customer->id);
    }

    public function logout(): void
    {
        Session::forget($this->sessionKey);
        $this->user = null;
    }

    public function id(): ?int
    {
        return Session::get($this->sessionKey);
    }

    public function check(): bool
    {
        return $this->user() !== null;
    }

    public function user(): ?Customer
    {
        if ($this->user) {
            return $this->user;
        }

        $id = $this->id();

        if ($id) {

            $this->user = Customer::where('id', $id)->first();

            if (! $this->user) {
                $this->logout(); // Clear session if customer not found
            }
        }

        return $this->user;
    }
}
