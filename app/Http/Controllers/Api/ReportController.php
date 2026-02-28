<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Maintenance;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportController extends Controller
{
    /**
     * Récupérer les statistiques générales des rapports
     */
    public function getStats(Request $request): JsonResponse
    {
        $period = $request->get('period', 'month');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        // Déterminer les dates selon la période
        switch ($period) {
            case 'week':
                $dateFrom = now()->startOfWeek();
                $dateTo = now()->endOfWeek();
                break;
            case 'month':
                $dateFrom = now()->startOfMonth();
                $dateTo = now()->endOfMonth();
                break;
            case 'year':
                $dateFrom = now()->startOfYear();
                $dateTo = now()->endOfYear();
                break;
            default:
                $dateFrom = $startDate ? Carbon::parse($startDate) : now()->startOfMonth();
                $dateTo = $endDate ? Carbon::parse($endDate) : now()->endOfMonth();
        }

        // Total produits
        $totalProducts = Product::active()->count();
        $totalValue = Product::active()->sum(DB::raw('quantity_in_stock * selling_price'));

        // Commandes dans la période
        $ordersCount = Order::whereBetween('created_at', [$dateFrom, $dateTo])->count();
        $ordersValue = Order::whereBetween('created_at', [$dateFrom, $dateTo])->sum('total');

        // Maintenances dans la période
        $maintenanceCount = Maintenance::whereBetween('created_at', [$dateFrom, $dateTo])->count();

        // Inventaire par catégorie
        $inventoryByCategory = DB::table('products')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->where('products.is_active', true)
            ->selectRaw('categories.name as category, COUNT(*) as count, SUM(products.quantity_in_stock * products.selling_price) as value')
            ->groupBy('categories.id', 'categories.name')
            ->get()
            ->map(fn($cat) => [
                'category' => $cat->category,
                'count' => $cat->count,
                'value' => (int) $cat->value,
            ]);

        // Commandes mensuelles (derniers 6 mois)
        $monthlyOrders = collect(range(0, 5))->reverse()->map(function ($i) {
            $month = now()->subMonths($i);
            $count = Order::whereMonth('created_at', $month->month)
                ->whereYear('created_at', $month->year)
                ->count();
            $value = Order::whereMonth('created_at', $month->month)
                ->whereYear('created_at', $month->year)
                ->sum('total');
            
            return [
                'month' => $month->format('M'),
                'count' => $count,
                'value' => (int) $value,
            ];
        })->reverse()->values();

        // Top produits les plus vendus
        $topProducts = DB::table('order_items')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->whereBetween('order_items.created_at', [$dateFrom, $dateTo])
            ->selectRaw('products.name, COUNT(*) as sales, CAST(SUM(order_items.quantity * order_items.unit_price) AS UNSIGNED) as revenue')
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('sales')
            ->limit(5)
            ->get()
            ->map(fn($item) => [
                'name' => $item->name,
                'sales' => $item->sales,
                'revenue' => (int) $item->revenue,
            ]);

        return response()->json([
            'stats' => [
                'totalProducts' => $totalProducts,
                'totalValue' => (int) $totalValue,
                'ordersCount' => $ordersCount,
                'ordersValue' => (int) $ordersValue,
                'maintenanceCount' => $maintenanceCount,
            ],
            'inventoryByCategory' => $inventoryByCategory,
            'monthlyOrders' => $monthlyOrders,
            'topProducts' => $topProducts,
        ]);
    }

    /**
     * Rapport inventaire détaillé
     */
    public function inventoryReport(Request $request): JsonResponse
    {
        $products = Product::with('category', 'supplier')
            ->active()
            ->orderBy('created_at', 'desc')
            ->paginate(50);

        return response()->json($products);
    }

    /**
     * Rapport commandes
     */
    public function ordersReport(Request $request): JsonResponse
    {
        $orders = Order::with('client', 'items')
            ->orderBy('created_at', 'desc')
            ->paginate(50);

        return response()->json($orders);
    }

    /**
     * Rapport maintenances
     */
    public function maintenanceReport(Request $request): JsonResponse
    {
        $maintenances = Maintenance::with('client', 'user')
            ->orderBy('created_at', 'desc')
            ->paginate(50);

        return response()->json($maintenances);
    }
}
