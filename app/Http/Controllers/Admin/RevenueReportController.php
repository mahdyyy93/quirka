<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class RevenueReportController extends Controller
{
    public function index(Request $request): View
    {
        // Totals aggregated by party
        $totals = OrderItem::query()
            ->selectRaw('
                SUM(quantity * unit_price) as gross_sales,
                SUM(quantity * COALESCE(print_cost, 0)) as total_print_cost,
                SUM(quantity * COALESCE(artist_profit, 0)) as total_artist_profit,
                SUM(quantity * COALESCE(platform_cut, 0)) as total_platform_cut
            ')
            ->first();

        // Per-artist revenue breakdown
        $artistPayouts = OrderItem::query()
            ->join('users', 'order_items.artist_id', '=', 'users.id')
            ->select(
                'users.id as artist_id',
                'users.name as artist_name',
                DB::raw('SUM(order_items.quantity * order_items.artist_profit) as total_profit'),
                DB::raw('SUM(order_items.quantity) as items_sold')
            )
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('total_profit')
            ->get();

        return view('admin.revenue-report', [
            'totals' => $totals,
            'artistPayouts' => $artistPayouts,
        ]);
    }
}
