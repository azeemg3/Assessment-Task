<?php

namespace App\Services;

use App\Models\Affiliate;
use App\Models\Merchant;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * You don't need to do anything here. This is just to help
 */
class ApiService
{
    /**
     * Create a new discount code for an affiliate
     *
     * @param Merchant $merchant
     *
     * @return array{id: int, code: string}
     */
    public function createDiscountCode(Merchant $merchant): array
    {
        return [
            'id' => rand(0, 100000),
            'code' => Str::uuid()
        ];
    }

    /**
     * Send a payout to an email
     *
     * @param  string $email
     * @param  float $amount
     * @return void
     * @throws RuntimeException
     */
    public function sendPayout(string $email, float $amount)
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \RuntimeException("Invalid email address: $email");
        }
        if ($amount <= 0) {
            throw new \RuntimeException("Invalid payout amount: $amount");
        }
        Mail::raw(
            "This is your Commission Amount ".number_format($amount,2)."",
            function ($message) use ($email) {
                $message->to($email)
                        ->subject('Payout Confirmation');
            }
        );
    }
}
