<?php

namespace App\Services;

use App\Exceptions\AffiliateCreateException;
use App\Mail\AffiliateCreated;
use App\Models\Affiliate;
use App\Models\Merchant;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
class AffiliateService
{
    public function __construct(
        protected ApiService $apiService
    ) {}

    /**
     * Create a new affiliate for the merchant with the given commission rate.
     *
     * @param  Merchant $merchant
     * @param  string $email
     * @param  string $name
     * @param  float $commissionRate
     * @return Affiliate
     */
    public function register($email, $name, $merchant, $commissionRate)
    {
        $user_detail = User::where("email", $email)->first();
        try {
            DB::beginTransaction();
            if (!empty($user_detail)) {
                // Create a new User record
                $user = new User();
                $user->name = $name;
                $user->email = $email;
                $user->type = User::TYPE_AFFILIATE;

                $user->save(); // Save the user
                Log::info('User created: ', $user->toArray());
                // Create a new Affiliate record
                $affiliate = new Affiliate();
                $affiliate->user_id = $user->id;
                $affiliate->merchant_id = $merchant;
                $affiliate->commission_rate = $commissionRate;
                $affiliate->discount_code = '2399933'; // Example code
                $affiliate->save(); // Save the affiliate
                Log::info('Affiliate created: ', $affiliate->toArray());
                DB::commit();
                return $affiliate;
            } else {
                DB::rollBack();
                Log::info('Has error');
                throw new AffiliateCreateException("User with email $email already exists.");
            }
        } catch (QueryException $qe) {
            Log::error('QueryException: ', ['error' => $qe->getMessage()]);
            throw new AffiliateCreateException('Failed to create affiliate due to a database error.', 0, $qe);
        } catch (Exception $e) {
            Log::error('Exception: ', ['error' => $e->getMessage()]);
            throw new AffiliateCreateException('Failed to create affiliate.', 0, $e);
        }
    }
}
