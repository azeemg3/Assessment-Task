<?php

namespace App\Services;

use App\Models\Affiliate;
use App\Models\Merchant;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\Log;
class OrderService
{
    public function __construct(
        protected AffiliateService $affiliateService
    ) {}

    /**
     * Process an order and log any commissions.
     * This should create a new affiliate if the customer_email is not already associated with one.
     * This method should also ignore duplicates based on order_id.
     *
     * @param  array{order_id: string, subtotal_price: float, merchant_domain: string, discount_code: string, customer_email: string, customer_name: string} $data
     * @return void
     */
    public function processOrder(array $data)
    {
        // TODO: Complete this method
        $customer_detail = User::where(['email' => $data['customer_email'], 'type' => User::TYPE_AFFILIATE])->first();
        $merchant_detail = Merchant::where('domain',$data['merchant_domain'])->first();
        if (empty($customer_detail)) {
            // Create a new Affiliate record
            $user = new User();
            $user->name = $data['customer_name'];
            $user->email = $data['customer_email'];
            $user->type = User::TYPE_AFFILIATE;
            $user->save(); // Save the user
            // Create a new Affiliate record
            $customer_detail = new Affiliate();
            $customer_detail->user_id = $user->id;
            $customer_detail->commission_rate =0.1;
            $customer_detail->discount_code ='4889929';
            $customer_detail->merchant_id = $merchant_detail->id;
            $customer_detail->save(); // Save the affiliate
        }
        $order = new Order();
        $order->affiliate_id = $customer_detail->id;
        $order->merchant_id = $merchant_detail->id;
        $order->subtotal=$data['subtotal_price']; 
        $order->commission_owed=$data['subtotal_price']*$customer_detail->commission_rate; 
        $order->external_order_id=$data['order_id'];
        $order->save();
        return $order;    

    }
}
