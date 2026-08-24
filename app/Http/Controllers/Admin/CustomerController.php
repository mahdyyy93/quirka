<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\JsonResponse;

class CustomerController extends Controller
{
    public function index(): JsonResponse
    {
        $customers = Customer::withCount('orders')
            ->withSum('orders', 'total_price')
            ->latest()
            ->get();

        return response()->json($customers);
    }
}
