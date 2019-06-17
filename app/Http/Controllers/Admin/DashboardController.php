<?php

namespace App\Http\Controllers\Admin;

use App\Models\Order\Customer;
use App\Models\Order\Order;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $data = array();

        if (auth()->user()->hasRole('Distributor')) {
            $data['order_count'] = Order::where('distributor_id', auth()->user()->distributor->id)->count() + 0;
            $data['customer_count'] = Customer::where('distributor_id', auth()->user()->distributor->id)->count() + 0;
            $data['revenue'] = Order::where('distributor_id', auth()->user()->distributor->id)
                    ->where('settlement_id', 0)
                    ->where('type', Order::TYPE_PAID)
                    ->sum('commission') + 0;
        } else {
            $data['order_count'] = Order::count() + 0;
            $data['customer_count'] = Customer::count() + 0;
            $data['revenue'] = Order::where('settlement_id', 0)
                    ->where('type', Order::TYPE_PAID)
                    ->sum('profit') + 0;
        }

        return view('admin.dashboard', $data);
    }
}
