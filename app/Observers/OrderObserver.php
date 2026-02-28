<?php

namespace App\Observers;

use App\Models\Order;
use App\Events\NewOrder;
use App\Events\OrderStatusChanged;
use App\Services\NotificationService;

class OrderObserver
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Handle the Order "created" event.
     */
    public function created(Order $order): void
    {
        // Broadcast new order notification
        broadcast(new NewOrder($order));
        
        // Refresh dashboard stats
        $this->notificationService->clearStatsCache();
        $this->notificationService->updateDashboardStats();
    }

    /**
     * Handle the Order "updated" event.
     */
    public function updated(Order $order): void
    {
        // Check if status changed
        if ($order->isDirty('status')) {
            $oldStatus = $order->getOriginal('status');
            $newStatus = $order->status;
            
            broadcast(new OrderStatusChanged($order, $oldStatus, $newStatus));
        }
        
        // Refresh dashboard stats
        $this->notificationService->clearStatsCache();
    }

    /**
     * Handle the Order "deleted" event.
     */
    public function deleted(Order $order): void
    {
        $this->notificationService->clearStatsCache();
        $this->notificationService->updateDashboardStats();
    }

    /**
     * Handle the Order "restored" event.
     */
    public function restored(Order $order): void
    {
        $this->notificationService->clearStatsCache();
    }

    /**
     * Handle the Order "force deleted" event.
     */
    public function forceDeleted(Order $order): void
    {
        //
    }
}
