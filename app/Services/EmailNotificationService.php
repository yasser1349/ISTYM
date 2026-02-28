<?php

namespace App\Services;

use App\Mail\StockAlertMail;
use App\Mail\OrderConfirmationMail;
use App\Mail\OrderStatusMail;
use App\Mail\MaintenanceReminderMail;
use App\Models\Product;
use App\Models\Order;
use App\Models\Maintenance;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class EmailNotificationService
{
    /**
     * Get admin emails for notifications
     */
    protected function getAdminEmails(): array
    {
        return User::where('role', 'admin')
            ->where('email_alerts', true)
            ->pluck('email')
            ->toArray();
    }

    /**
     * Get employee emails for notifications
     */
    protected function getEmployeeEmails(): array
    {
        return User::whereIn('role', ['admin', 'employee'])
            ->where('email_alerts', true)
            ->pluck('email')
            ->toArray();
    }

    /**
     * Send stock alert email
     */
    public function sendStockAlert(Product $product, string $alertType = 'low'): bool
    {
        try {
            $recipients = $this->getEmployeeEmails();
            
            if (empty($recipients)) {
                Log::info("No recipients for stock alert: {$product->name}");
                return false;
            }

            foreach ($recipients as $email) {
                Mail::to($email)->queue(new StockAlertMail($product, $alertType));
            }

            Log::info("Stock alert sent for product: {$product->name} ({$alertType})");
            return true;
        } catch (\Exception $e) {
            Log::error("Failed to send stock alert: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Check and send stock alerts based on product quantity
     */
    public function checkAndSendStockAlert(Product $product): void
    {
        $minQuantity = $product->min_quantity ?? 10;
        $criticalThreshold = max(1, $minQuantity / 2);

        if ($product->quantity <= 0) {
            $this->sendStockAlert($product, 'out_of_stock');
        } elseif ($product->quantity <= $criticalThreshold) {
            $this->sendStockAlert($product, 'critical');
        } elseif ($product->quantity <= $minQuantity) {
            $this->sendStockAlert($product, 'low');
        }
    }

    /**
     * Send order confirmation email to client
     */
    public function sendOrderConfirmation(Order $order): bool
    {
        try {
            $order->load('client');
            
            if (!$order->client || !$order->client->email) {
                Log::info("No client email for order confirmation: {$order->order_number}");
                return false;
            }

            Mail::to($order->client->email)->queue(new OrderConfirmationMail($order));

            Log::info("Order confirmation sent for: {$order->order_number}");
            return true;
        } catch (\Exception $e) {
            Log::error("Failed to send order confirmation: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send order status update email to client
     */
    public function sendOrderStatusUpdate(Order $order, string $previousStatus, string $newStatus): bool
    {
        try {
            $order->load('client');

            if (!$order->client || !$order->client->email) {
                Log::info("No client email for order status update: {$order->order_number}");
                return false;
            }

            // Only send for significant status changes
            $notifiableStatuses = ['confirmed', 'shipped', 'delivered', 'cancelled'];
            if (!in_array($newStatus, $notifiableStatuses)) {
                return false;
            }

            Mail::to($order->client->email)->queue(new OrderStatusMail($order, $previousStatus, $newStatus));

            Log::info("Order status update sent for: {$order->order_number} ({$previousStatus} -> {$newStatus})");
            return true;
        } catch (\Exception $e) {
            Log::error("Failed to send order status update: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send maintenance reminder email
     */
    public function sendMaintenanceReminder(Maintenance $maintenance, int $daysUntil = 0): bool
    {
        try {
            $recipients = $this->getEmployeeEmails();

            if (empty($recipients)) {
                Log::info("No recipients for maintenance reminder: {$maintenance->title}");
                return false;
            }

            foreach ($recipients as $email) {
                Mail::to($email)->queue(new MaintenanceReminderMail($maintenance, $daysUntil));
            }

            Log::info("Maintenance reminder sent for: {$maintenance->title} (in {$daysUntil} days)");
            return true;
        } catch (\Exception $e) {
            Log::error("Failed to send maintenance reminder: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Check all products for low stock and send alerts
     */
    public function checkAllProductsStock(): int
    {
        $count = 0;
        $products = Product::whereColumn('quantity', '<=', 'min_quantity')
            ->orWhere('quantity', '<=', 10)
            ->get();

        foreach ($products as $product) {
            $this->checkAndSendStockAlert($product);
            $count++;
        }

        return $count;
    }

    /**
     * Check upcoming maintenances and send reminders
     */
    public function checkUpcomingMaintenances(): int
    {
        $count = 0;
        $today = now()->startOfDay();
        
        // Get maintenances scheduled in the next 7 days
        $maintenances = Maintenance::where('status', 'scheduled')
            ->whereBetween('scheduled_date', [$today, $today->copy()->addDays(7)])
            ->get();

        foreach ($maintenances as $maintenance) {
            $daysUntil = (int) $today->diffInDays($maintenance->scheduled_date, false);
            
            // Send reminders at 7 days, 3 days, 1 day, and day of
            if (in_array($daysUntil, [0, 1, 3, 7])) {
                $this->sendMaintenanceReminder($maintenance, $daysUntil);
                $count++;
            }
        }

        return $count;
    }
}
