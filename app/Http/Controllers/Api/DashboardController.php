<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Order;
use App\Models\Client;
use App\Models\Maintenance;
use App\Models\UserDashboardPreference;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }
    /**
     * Statistiques du tableau de bord
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        
        // Si c'est un client, retourner ses propres stats
        if ($user->isClient()) {
            return $this->clientDashboard($user);
        }

        // Stats générales pour admin/employé
        return $this->adminDashboard($request);
    }

    /**
     * Dashboard Admin/Employé
     */
    private function adminDashboard(Request $request): JsonResponse
    {
        $period = $request->get('period', 'month'); // week, month, year

        // Total produits
        $totalProducts = Product::active()->count();
        $previousProducts = Product::where('created_at', '<', now()->subMonth())->count();
        $productsGrowth = $previousProducts > 0 
            ? round((($totalProducts - $previousProducts) / $previousProducts) * 100, 1) 
            : 0;

        // Ventes ce mois (toutes les ventes, indépendamment du statut)
        $currentMonthSales = Order::sales()
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('total');

        $lastMonthSales = Order::sales()
            ->whereMonth('created_at', now()->subMonth()->month)
            ->whereYear('created_at', now()->subMonth()->year)
            ->sum('total');

        $salesGrowth = $lastMonthSales > 0 
            ? round((($currentMonthSales - $lastMonthSales) / $lastMonthSales) * 100, 1) 
            : 0;

        // Stock critique
        $criticalStock = Product::criticalStock()->count();
        $previousCriticalStock = 9; // Valeur de référence
        $criticalStockChange = $criticalStock - $previousCriticalStock;

        // Maintenances prévues cette semaine (toutes les maintenances, pas seulement "scheduled")
        $maintenanceThisWeek = Maintenance::whereBetween('scheduled_date', [
            now()->startOfWeek(),
            now()->endOfWeek()
        ])->count();

        // Graphique ventes & commandes
        $chartData = $this->getSalesChart($request->get('year', now()->year));

        // Produits en stock critique
        $criticalProducts = Product::with('supplier')
            ->criticalStock()
            ->take(5)
            ->get()
            ->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'reference' => $product->reference,
                    'supplier' => $product->supplier->code ?? 'N/A',
                    'quantity' => $product->quantity_in_stock,
                    'minimum' => $product->minimum_stock,
                    'percentage' => $product->stockPercentage(),
                ];
            });

        // Commandes récentes
        $recentOrders = Order::with(['client', 'user'])
            ->sales()
            ->latest()
            ->take(5)
            ->get();

        // Maintenances à venir
        $upcomingMaintenance = Maintenance::with('client')
            ->upcoming()
            ->take(5)
            ->get();

        return response()->json([
            'stats' => [
                'total_products' => [
                    'value' => $totalProducts,
                    'growth' => $productsGrowth,
                ],
                'monthly_sales' => [
                    'value' => $currentMonthSales,
                    'growth' => $salesGrowth,
                ],
                'critical_stock' => [
                    'value' => $criticalStock,
                    'change' => $criticalStockChange,
                ],
                'maintenance_this_week' => [
                    'value' => $maintenanceThisWeek,
                ],
            ],
            'chart' => $chartData,
            'critical_products' => $criticalProducts,
            'recent_orders' => $recentOrders,
            'upcoming_maintenance' => $upcomingMaintenance,
        ]);
    }

    /**
     * Dashboard Client
     */
    private function clientDashboard($user): JsonResponse
    {
        $client = $user->client;

        if (!$client) {
            return response()->json(['error' => 'Client non trouvé'], 404);
        }

        // Commandes du client
        $totalOrders = $client->orders()->count();
        $pendingOrders = $client->orders()->whereIn('status', ['pending', 'confirmed', 'processing'])->count();
        $totalSpent = $client->orders()->sum('total'); // Comptabiliser TOUTES les commandes, pas juste "delivered"

        // Maintenances
        $scheduledMaintenance = $client->maintenances()->scheduled()->count();

        // Commandes récentes
        $recentOrders = $client->orders()
            ->with('items.product')
            ->latest()
            ->take(5)
            ->get();

        // Maintenances à venir
        $upcomingMaintenance = $client->maintenances()
            ->upcoming()
            ->take(5)
            ->get();

        return response()->json([
            'stats' => [
                'total_orders' => $totalOrders,
                'pending_orders' => $pendingOrders,
                'total_spent' => $totalSpent,
                'scheduled_maintenance' => $scheduledMaintenance,
            ],
            'recent_orders' => $recentOrders,
            'upcoming_maintenance' => $upcomingMaintenance,
        ]);
    }

    /**
     * Données pour le graphique des ventes
     */
    private function getSalesChart(int $year): array
    {
        $months = [];
        $sales = [];
        $orders = [];

        for ($i = 1; $i <= 12; $i++) {
            $months[] = Carbon::create($year, $i, 1)->translatedFormat('M');
            
            $monthSales = Order::sales()
                ->delivered()
                ->whereMonth('created_at', $i)
                ->whereYear('created_at', $year)
                ->sum('total');
            
            $monthOrders = Order::whereMonth('created_at', $i)
                ->whereYear('created_at', $year)
                ->count();

            $sales[] = (float) $monthSales;
            $orders[] = $monthOrders;
        }

        return [
            'labels' => $months,
            'datasets' => [
                [
                    'label' => 'Ventes (MAD)',
                    'data' => $sales,
                    'borderColor' => '#10b981',
                    'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                ],
                [
                    'label' => 'Commandes',
                    'data' => $orders,
                    'borderColor' => '#3b82f6',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                ],
            ],
        ];
    }

    /**
     * Statistiques rapides
     */
    public function quickStats(): JsonResponse
    {
        return response()->json([
            'products' => Product::active()->count(),
            'clients' => Client::active()->count(),
            'orders_pending' => Order::pending()->count(),
            'maintenance_scheduled' => Maintenance::scheduled()->count(),
        ]);
    }

    /**
     * Get real-time notifications
     */
    public function notifications(): JsonResponse
    {
        $user = request()->user();
        $notifications = [];
        
        // Stock alerts (seulement pour admin/employee, pas pour clients)
        if (!$user->isClient()) {
            $criticalStock = Product::criticalStock()->with('supplier')->get();
            
            foreach ($criticalStock as $product) {
                $type = $product->quantity_in_stock == 0 ? 'danger' : 'warning';
                $notifications[] = [
                    'id' => 'stock_' . $product->id,
                    'type' => $type,
                    'category' => 'stock',
                    'title' => $product->quantity_in_stock == 0 ? 'Rupture de stock' : 'Stock critique',
                    'message' => "{$product->name} - {$product->quantity_in_stock}/{$product->minimum_stock}",
                    'product' => [
                        'id' => $product->id,
                        'name' => $product->name,
                        'reference' => $product->reference,
                        'quantity' => $product->quantity_in_stock,
                        'minimum' => $product->minimum_stock,
                    ],
                    'created_at' => now()->toISOString(),
                    'read' => false,
                ];
            }
        }
        
        // Maintenance alerts (due within 3 days)
        $upcomingMaintenance = Maintenance::where('status', 'scheduled')
            ->whereDate('scheduled_date', '<=', now()->addDays(3))
            ->with('client')
            ->get();
        
        foreach ($upcomingMaintenance as $maintenance) {
            $isOverdue = $maintenance->scheduled_date < now();
            $notifications[] = [
                'id' => 'maintenance_' . $maintenance->id,
                'type' => $isOverdue ? 'danger' : 'info',
                'category' => 'maintenance',
                'title' => $isOverdue ? 'Maintenance en retard' : 'Maintenance à venir',
                'message' => ($maintenance->client?->name ?? 'Équipement') . ' - ' . $maintenance->scheduled_date->format('d/m/Y'),
                'maintenance' => [
                    'id' => $maintenance->id,
                    'type' => $maintenance->type,
                    'scheduled_date' => $maintenance->scheduled_date->format('d/m/Y'),
                ],
                'created_at' => $maintenance->scheduled_date->toISOString(),
                'read' => false,
            ];
        }
        
        // Recent orders (last 24h)
        $recentOrders = Order::where('created_at', '>=', now()->subHours(24))
            ->with('client')
            ->latest()
            ->limit(5)
            ->get();
        
        foreach ($recentOrders as $order) {
            $notifications[] = [
                'id' => 'order_' . $order->id,
                'type' => 'success',
                'category' => 'order',
                'title' => 'Nouvelle commande',
                'message' => "#{$order->reference} - {$order->total} MAD",
                'order' => [
                    'id' => $order->id,
                    'reference' => $order->reference,
                    'total' => $order->total,
                    'client' => $order->client?->name,
                ],
                'created_at' => $order->created_at->toISOString(),
                'read' => false,
            ];
        }
        
        // Sort by date (newest first)
        usort($notifications, fn($a, $b) => strtotime($b['created_at']) - strtotime($a['created_at']));
        
        return response()->json([
            'notifications' => array_slice($notifications, 0, 20),
            'unread_count' => count($notifications),
            'categories' => [
                'stock' => count(array_filter($notifications, fn($n) => $n['category'] === 'stock')),
                'maintenance' => count(array_filter($notifications, fn($n) => $n['category'] === 'maintenance')),
                'order' => count(array_filter($notifications, fn($n) => $n['category'] === 'order')),
            ],
        ]);
    }

    /**
     * Broadcast dashboard update (for testing)
     */
    public function broadcast(Request $request): JsonResponse
    {
        $type = $request->get('type', 'stats');
        
        switch ($type) {
            case 'stock':
                $alerts = $this->notificationService->checkStockAlerts();
                return response()->json(['message' => 'Stock alerts broadcast', 'alerts' => $alerts]);
                
            case 'maintenance':
                $alerts = $this->notificationService->checkMaintenanceAlerts();
                return response()->json(['message' => 'Maintenance alerts broadcast', 'alerts' => $alerts]);
                
            case 'stats':
            default:
                $this->notificationService->updateDashboardStats();
                return response()->json(['message' => 'Dashboard stats broadcast']);
        }
    }

    /**
     * Get live stats for real-time updates
     */
    public function liveStats(): JsonResponse
    {
        return response()->json([
            'stats' => $this->notificationService->getDashboardStats(),
            'timestamp' => now()->toISOString(),
        ]);
    }

    /**
     * Get user dashboard preferences
     */
    public function getPreferences(Request $request): JsonResponse
    {
        $user = $request->user();
        
        $preferences = UserDashboardPreference::where('user_id', $user->id)->first();
        
        if (!$preferences) {
            return response()->json([
                'layout' => null,
                'filters' => null,
                'theme' => 'dark',
            ]);
        }
        
        return response()->json($preferences);
    }

    /**
     * Save user dashboard preferences
     */
    public function savePreferences(Request $request): JsonResponse
    {
        $user = $request->user();
        
        $validated = $request->validate([
            'layout' => 'nullable|array',
            'filters' => 'nullable|array',
            'theme' => 'nullable|string|in:dark,light',
        ]);
        
        $preferences = UserDashboardPreference::updateOrCreate(
            ['user_id' => $user->id],
            [
                'layout' => $validated['layout'] ?? null,
                'filters' => $validated['filters'] ?? null,
                'theme' => $validated['theme'] ?? 'dark',
            ]
        );
        
        return response()->json([
            'message' => 'Préférences sauvegardées',
            'preferences' => $preferences,
        ]);
    }
}
