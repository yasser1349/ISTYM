<?php

namespace App\Services;

use App\Events\StockAlert;
use App\Events\NewOrder;
use App\Events\OrderStatusChanged;
use App\Events\MaintenanceAlert;
use App\Events\DashboardUpdate;
use App\Models\Product;
use App\Models\Order;
use App\Models\Maintenance;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class NotificationService
{
    /**
     * Check and broadcast stock alerts
     */
    public function checkStockAlerts(): array
    {
        $alerts = [];
        
        // Products with critical stock (quantity = 0)
        $outOfStock = Product::where('quantity_in_stock', 0)->get();
        foreach ($outOfStock as $product) {
            broadcast(new StockAlert($product, 'out_of_stock'))->toOthers();
            $alerts[] = ['type' => 'out_of_stock', 'product' => $product->name];
        }
        
        // Products with low stock (quantity <= minimum_stock)
        $lowStock = Product::whereColumn('quantity_in_stock', '<=', 'minimum_stock')
            ->where('quantity_in_stock', '>', 0)
            ->get();
        
        foreach ($lowStock as $product) {
            $alertType = $product->quantity_in_stock <= ($product->minimum_stock / 2) ? 'critical' : 'low_stock';
            broadcast(new StockAlert($product, $alertType))->toOthers();
            $alerts[] = ['type' => $alertType, 'product' => $product->name];
        }
        
        return $alerts;
    }

    /**
     * Broadcast new order notification
     */
    public function notifyNewOrder(Order $order): void
    {
        broadcast(new NewOrder($order))->toOthers();
        $this->updateDashboardStats();
    }

    /**
     * Broadcast order status change
     */
    public function notifyOrderStatusChange(Order $order, string $oldStatus, string $newStatus): void
    {
        broadcast(new OrderStatusChanged($order, $oldStatus, $newStatus))->toOthers();
        $this->updateDashboardStats();
    }

    /**
     * Check and broadcast maintenance alerts
     */
    public function checkMaintenanceAlerts(): array
    {
        $alerts = [];
        $today = now()->startOfDay();
        
        // Maintenances due today
        $dueToday = Maintenance::where('status', 'scheduled')
            ->whereDate('scheduled_date', $today)
            ->with('client')
            ->get();
        
        foreach ($dueToday as $maintenance) {
            broadcast(new MaintenanceAlert($maintenance, 'due_today'))->toOthers();
            $alerts[] = ['type' => 'due_today', 'maintenance' => $maintenance->id];
        }
        
        // Overdue maintenances
        $overdue = Maintenance::where('status', 'scheduled')
            ->whereDate('scheduled_date', '<', $today)
            ->with('client')
            ->get();
        
        foreach ($overdue as $maintenance) {
            broadcast(new MaintenanceAlert($maintenance, 'overdue'))->toOthers();
            $alerts[] = ['type' => 'overdue', 'maintenance' => $maintenance->id];
        }
        
        return $alerts;
    }

    /**
     * Broadcast maintenance completion
     */
    public function notifyMaintenanceCompleted(Maintenance $maintenance): void
    {
        broadcast(new MaintenanceAlert($maintenance, 'completed'))->toOthers();
        $this->updateDashboardStats();
    }

    /**
     * Update and broadcast dashboard statistics
     */
    public function updateDashboardStats(): void
    {
        $stats = $this->getDashboardStats();
        broadcast(new DashboardUpdate($stats, 'full'))->toOthers();
    }

    /**
     * Get current dashboard statistics
     */
    public function getDashboardStats(): array
    {
        return Cache::remember('dashboard_stats', 60, function () {
            $today = now()->startOfDay();
            $thisMonth = now()->startOfMonth();
            
            return [
                'total_products' => Product::count(),
                'total_stock_value' => Product::sum(DB::raw('quantity_in_stock * selling_price')),
                'critical_stock_count' => Product::whereColumn('quantity_in_stock', '<=', 'minimum_stock')->count(),
                'out_of_stock_count' => Product::where('quantity_in_stock', 0)->count(),
                'orders_today' => Order::whereDate('created_at', $today)->count(),
                'orders_this_month' => Order::where('created_at', '>=', $thisMonth)->count(),
                'pending_orders' => Order::where('status', 'pending')->count(),
                'revenue_today' => Order::whereDate('created_at', $today)
                    ->where('status', '!=', 'cancelled')
                    ->sum('total'),
                'revenue_this_month' => Order::where('created_at', '>=', $thisMonth)
                    ->where('status', '!=', 'cancelled')
                    ->sum('total'),
                'pending_maintenances' => Maintenance::where('status', 'scheduled')->count(),
                'overdue_maintenances' => Maintenance::where('status', 'scheduled')
                    ->whereDate('scheduled_date', '<', $today)
                    ->count(),
                'maintenances_today' => Maintenance::whereDate('scheduled_date', $today)->count(),
                'updated_at' => now()->format('Y-m-d H:i:s'),
            ];
        });
    }

    /**
     * Clear dashboard stats cache
     */
    public function clearStatsCache(): void
    {
        Cache::forget('dashboard_stats');
    }
}
