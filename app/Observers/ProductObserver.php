<?php

namespace App\Observers;

use App\Models\Product;
use App\Events\StockAlert;
use App\Events\DashboardUpdate;
use App\Services\NotificationService;

class ProductObserver
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Handle the Product "created" event.
     */
    public function created(Product $product): void
    {
        // Refresh dashboard stats
        $this->notificationService->clearStatsCache();
        $this->notificationService->updateDashboardStats();
    }

    /**
     * Handle the Product "updated" event.
     */
    public function updated(Product $product): void
    {
        // Check if stock changed
        if ($product->isDirty('quantity_in_stock') || $product->isDirty('minimum_stock')) {
            $this->checkStockLevel($product);
        }
        
        // Refresh dashboard stats
        $this->notificationService->clearStatsCache();
    }

    /**
     * Check stock level and broadcast alert if needed
     */
    private function checkStockLevel(Product $product): void
    {
        $quantity = $product->quantity_in_stock;
        $minimum = $product->minimum_stock;

        if ($quantity == 0) {
            broadcast(new StockAlert($product, 'out_of_stock'));
        } elseif ($quantity <= ($minimum / 2)) {
            broadcast(new StockAlert($product, 'critical'));
        } elseif ($quantity <= $minimum) {
            broadcast(new StockAlert($product, 'low_stock'));
        }
    }

    /**
     * Handle the Product "deleted" event.
     */
    public function deleted(Product $product): void
    {
        $this->notificationService->clearStatsCache();
        $this->notificationService->updateDashboardStats();
    }

    /**
     * Handle the Product "restored" event.
     */
    public function restored(Product $product): void
    {
        $this->notificationService->clearStatsCache();
    }

    /**
     * Handle the Product "force deleted" event.
     */
    public function forceDeleted(Product $product): void
    {
        //
    }
}
