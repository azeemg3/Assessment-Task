<?php

namespace App\Services;

use App\Jobs\PayoutOrderJob;
use App\Models\Affiliate;
use App\Models\Merchant;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MerchantService
{
    /**
     * Register a new user and associated merchant.
     * Hint: Use the password field to store the API key.
     * Hint: Be sure to set the correct user type according to the constants in the User model.
     *
     * @param array{domain: string, name: string, email: string, api_key: string} $data
     * @return Merchant
     */
    public function register(array $data): Merchant
    {
        // TODO: Complete this method
        try {
            DB::beginTransaction();
            $user = new User();
            $user->name = $data["name"];
            $user->email = $data["email"];
            $user->password = $data['api_key'];
            $user->type = User::TYPE_MERCHANT;
            $user->save();
            Log::info($user);
            //register Associate Merchant
            if ($user) {
                $merchant = new Merchant();
                $merchant->domain = $data['domain'];;
                $merchant->display_name = $data['name'];
                $merchant->user_id = $user->id;
                $merchant->save();
                DB::commit();
                return $merchant;
            }
        } catch (\Exception $e) {
            DB::rollBack();
            throw 
        }
    }

    /**
     * Update the user
     *
     * @param array{domain: string, name: string, email: string, api_key: string} $data
     * @return void
     */
    public function updateMerchant(User $user, array $data)
    {
        // TODO: Complete this method
        $user->name = $data['name'];
        $user->email = $data['email'];
        if (isset($data['api_key'])) {
            $user->password = bcrypt($data['api_key']);
        }
        $user->save();
        $merchant = $user->merchant;
        if ($merchant) {
            $merchant->domain = $data['domain'];
            $merchant->display_name = $data['name'];
            $merchant->save();
            return $merchant;
        }
    }

    /**
     * Find a merchant by their email.
     * Hint: You'll need to look up the user first.
     *
     * @param string $email
     * @return Merchant|null
     */
    public function findMerchantByEmail(string $email): ?Merchant
    {
        // TODO: Complete this method
        $merchant = Merchant::whereHas('user', function ($query) use ($email) {
            $query->where('email', $email);
        })->first();
        return $merchant ? $merchant : null;
    }

    /**
     * Pay out all of an affiliate's orders.
     * Hint: You'll need to dispatch the job for each unpaid order.
     *
     * @param Affiliate $affiliate
     * @return void
     */
    public function payout(Affiliate $affiliate)
    {
        // TODO: Complete this method
        $affiliate_orders = $affiliate->orders->where('payout_status', 'unpaid')->get();
        if ($affiliate_orders->isEmpty()) {
            Log::info('No unpaid order Exist');
        }
        foreach ($affiliate_orders as $order) {
            try {
                PayoutOrderJob::dispatch($order);
            } catch (\Exception $e) {
                Log::error('Failed to update order Against orderId:' . $order->id . ' Error:' . $e->getMessage());
            }
        }
    }
}
