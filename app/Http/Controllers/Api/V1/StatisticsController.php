<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class StatisticsController extends Controller
{
    public function index()
    {
        $cacheDuration = now()->addMinutes(30); // Cache duration of 30 minutes

        // Total sales
        $totalSales = Cache::remember('total_sales', $cacheDuration, function () {
            return Order::sum('total_amount');
        });

        // Number of orders
        $totalOrders = Cache::remember('total_orders', $cacheDuration, function () {
            return Order::count();
        });

        // Number of users
        $totalUsers = Cache::remember('total_users', $cacheDuration, function () {
            return User::count();
        });

        // Top selling products
        $topSellingProducts = Cache::remember('top_selling_products', $cacheDuration, function () {
            return OrderItem::select('products.name', DB::raw('SUM(order_items.quantity) as total_quantity'))
                ->join('products', 'order_items.product_id', '=', 'products.id')
                ->groupBy('products.name')
                ->orderBy('total_quantity', 'desc')
                ->take(5)
                ->get();
        });

        // Monthly sales
        $monthlySales = Cache::remember('monthly_sales', $cacheDuration, function () {
            return Order::select(
                DB::raw('YEAR(created_at) as year'),
                DB::raw('MONTH(created_at) as month'),
                DB::raw('SUM(total_amount) as total')
            )
            ->groupBy('year', 'month')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->get();
        });

        // Average order value
        $averageOrderValue = Cache::remember('average_order_value', $cacheDuration, function () {
            return Order::avg('total_amount');
        });

        // Payment statuses
        $paymentStatuses = Cache::remember('payment_statuses', $cacheDuration, function () {
            return Payment::select('status', DB::raw('COUNT(*) as count'))
                ->groupBy('status')
                ->get();
        });

        // User roles distribution
        $userRoles = Cache::remember('user_roles', $cacheDuration, function () {
            return User::select('role', DB::raw('COUNT(*) as count'))
                ->groupBy('role')
                ->get();
        });

        // Monthly new users
        $monthlyNewUsers = Cache::remember('monthly_new_users', $cacheDuration, function () {
            return User::select(
                DB::raw('YEAR(created_at) as year'),
                DB::raw('MONTH(created_at) as month'),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('year', 'month')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->get();
        });

        return response()->json([
            'total_sales' => $totalSales,
            'total_orders' => $totalOrders,
            'total_users' => $totalUsers,
            'top_selling_products' => $topSellingProducts,
            'monthly_sales' => $monthlySales,
            'average_order_value' => $averageOrderValue,
            'payment_statuses' => $paymentStatuses,
            'user_roles' => $userRoles,
            'monthly_new_users' => $monthlyNewUsers,
        ]);
    }
}
