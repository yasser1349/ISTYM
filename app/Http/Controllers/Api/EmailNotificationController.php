<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\EmailNotificationService;
use App\Models\Product;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class EmailNotificationController extends Controller
{
    protected EmailNotificationService $emailService;

    public function __construct(EmailNotificationService $emailService)
    {
        $this->emailService = $emailService;
    }

    /**
     * Send test stock alert email
     */
    public function testStockAlert(Request $request): JsonResponse
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'alert_type' => 'nullable|in:low,critical,out_of_stock',
        ]);

        $product = Product::findOrFail($request->product_id);
        $alertType = $request->alert_type ?? 'low';

        $sent = $this->emailService->sendStockAlert($product, $alertType);

        return response()->json([
            'success' => $sent,
            'message' => $sent 
                ? "Email d'alerte stock envoyé pour {$product->name}"
                : "Échec de l'envoi de l'email (pas de destinataires ou erreur)"
        ]);
    }

    /**
     * Send test order confirmation email
     */
    public function testOrderConfirmation(Request $request): JsonResponse
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
        ]);

        $order = Order::findOrFail($request->order_id);
        $sent = $this->emailService->sendOrderConfirmation($order);

        return response()->json([
            'success' => $sent,
            'message' => $sent 
                ? "Email de confirmation envoyé pour la commande {$order->order_number}"
                : "Échec de l'envoi de l'email (client sans email ou erreur)"
        ]);
    }

    /**
     * Send test order status update email
     */
    public function testOrderStatusUpdate(Request $request): JsonResponse
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'new_status' => 'required|in:confirmed,shipped,delivered,cancelled',
        ]);

        $order = Order::findOrFail($request->order_id);
        $previousStatus = $order->status;
        $newStatus = $request->new_status;

        $sent = $this->emailService->sendOrderStatusUpdate($order, $previousStatus, $newStatus);

        return response()->json([
            'success' => $sent,
            'message' => $sent 
                ? "Email de mise à jour de statut envoyé pour la commande {$order->order_number}"
                : "Échec de l'envoi de l'email (client sans email ou erreur)"
        ]);
    }

    /**
     * Trigger check for all low stock products
     */
    public function checkAllStock(): JsonResponse
    {
        $count = $this->emailService->checkAllProductsStock();

        return response()->json([
            'success' => true,
            'count' => $count,
            'message' => "{$count} alerte(s) de stock envoyée(s)"
        ]);
    }

    /**
     * Trigger check for upcoming maintenances
     */
    public function checkMaintenances(): JsonResponse
    {
        $count = $this->emailService->checkUpcomingMaintenances();

        return response()->json([
            'success' => true,
            'count' => $count,
            'message' => "{$count} rappel(s) de maintenance envoyé(s)"
        ]);
    }

    /**
     * Get email notification statistics
     */
    public function statistics(): JsonResponse
    {
        // Get counts for products with low stock
        $lowStockCount = Product::whereRaw('quantity_in_stock <= minimum_stock')
            ->where('quantity_in_stock', '>', 0)
            ->count();

        $criticalStockCount = Product::whereRaw('quantity_in_stock <= minimum_stock / 2')
            ->where('quantity_in_stock', '>', 0)
            ->count();

        $outOfStockCount = Product::where('quantity_in_stock', '<=', 0)->count();

        // Get upcoming maintenances
        $upcomingMaintenances = \App\Models\Maintenance::where('status', 'scheduled')
            ->whereBetween('scheduled_date', [now()->startOfDay(), now()->addDays(7)])
            ->count();

        // Get recent orders needing notification
        $pendingOrders = Order::where('status', 'pending')
            ->where('created_at', '>=', now()->subDay())
            ->count();

        return response()->json([
            'stock_alerts' => [
                'low_stock' => $lowStockCount,
                'critical_stock' => $criticalStockCount,
                'out_of_stock' => $outOfStockCount,
                'total' => $lowStockCount + $criticalStockCount + $outOfStockCount
            ],
            'maintenance_reminders' => $upcomingMaintenances,
            'pending_orders' => $pendingOrders
        ]);
    }
}
