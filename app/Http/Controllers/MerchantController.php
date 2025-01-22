<?php

namespace App\Http\Controllers;

use App\Models\Merchant;
use App\Services\MerchantService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Models\Order;

class MerchantController extends Controller
{
    public function __construct(
        MerchantService $merchantService
    ) {}

    /**
     * Useful order statistics for the merchant API.
     * 
     * @param Request $request Will include a from and to date
     * @return JsonResponse Should be in the form {count: total number of orders in range, commission_owed: amount of unpaid commissions for orders with an affiliate, revenue: sum order subtotals}
     */
    public function orderStats(Request $request): JsonResponse
    {
        // TODO: Complete this method
        $from = $request->date_from;
        $to = $request->date_to;
        $result = Order::selectRaw('COUNT(*) as count,
        SUM(CASE WHEN affiliate_id IS NOT NULL AND commission_paid = false THEN commission ELSE 0 END) as commission_owed,
        SUM(subtotal) as revenue')->whereBetween('created_at', [$from, $to])->first();
        return response()->json([
            'count' => $result->count,
            'commission_owed' => $result->commission_owed,
            'revenue' => $result->revenue,
        ]);
    }
}
