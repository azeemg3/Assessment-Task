<?php

namespace App\Jobs;

use App\Models\Order;
use App\Services\ApiService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;


class PayoutOrderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(
        public Order $order
    ) {}

    /**
     * Use the API service to send a payout of the correct amount.
     * Note: The order status must be paid if the payout is successful, or remain unpaid in the event of an exception.
     *
     * @return void
     */
    public function handle(ApiService $apiService)
    {
        // TODO: Complete this method
        try{
            $email=$this->order->affiliate->user->email;
            $amount=$this->order->commission_owed;
            if(!$email && $amount<0){
                throw new \RuntimeException("Invalid payout details");  
            }
            $apiService->sendPayout($email, $amount);
            $this->order->update(['payout_status' => 'paid']);
        }catch(\Exception $e){
            Log::error("Failed to process payout for Order ID: {$this->order->id}. Error: {$e->getMessage()}");
            throw $e;
        }
    }
}
