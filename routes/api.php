<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\SupplierController;
use App\Http\Controllers\Api\ClientController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\QuoteController;
use App\Http\Controllers\Api\MaintenanceController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\ExportController;
use App\Http\Controllers\Api\SettingsController;
use App\Http\Controllers\Api\SearchController;
use App\Http\Controllers\Api\EmailNotificationController;
use App\Http\Controllers\Api\ReportController;

/*
|--------------------------------------------------------------------------
| API Routes - ISTYM ERP System
|--------------------------------------------------------------------------
*/

// Routes publiques
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

// Routes protégées
Route::middleware('auth:sanctum')->group(function () {
    
    // Authentification
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::put('/profile', [AuthController::class, 'updateProfile']);
    Route::put('/change-password', [AuthController::class, 'changePassword']);
    
    // Recherche globale
    Route::get('/search', [SearchController::class, 'search']);
    Route::get('/search/suggestions', [SearchController::class, 'suggestions']);
    
    // Dashboard - tous les utilisateurs authentifiés
    Route::get('/dashboard', [DashboardController::class, 'index']);
    Route::get('/dashboard/quick-stats', [DashboardController::class, 'quickStats']);
    Route::get('/dashboard/notifications', [DashboardController::class, 'notifications']);
    Route::get('/dashboard/live-stats', [DashboardController::class, 'liveStats']);
    Route::post('/dashboard/broadcast', [DashboardController::class, 'broadcast']);
    Route::get('/dashboard/preferences', [DashboardController::class, 'getPreferences']);
    Route::post('/dashboard/preferences', [DashboardController::class, 'savePreferences']);
    
    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::get('/notifications/unread', [NotificationController::class, 'unread']);
    Route::put('/notifications/{notification}/read', [NotificationController::class, 'markAsRead']);
    Route::put('/notifications/read-all', [NotificationController::class, 'markAllAsRead']);
    Route::delete('/notifications/{notification}', [NotificationController::class, 'destroy']);
    
    // Settings - Paramètres
    Route::prefix('settings')->group(function () {
        // Company settings (Admin/Employee)
        Route::get('/company', [SettingsController::class, 'getCompany']);
        Route::put('/company', [SettingsController::class, 'updateCompany']);
        Route::post('/company/logo', [SettingsController::class, 'uploadLogo']);
        
        // Notifications preferences
        Route::get('/notifications', [SettingsController::class, 'getNotifications']);
        Route::put('/notifications', [SettingsController::class, 'updateNotifications']);
        
        // Security (password change)
        Route::put('/security', [SettingsController::class, 'updateSecurity']);
    });
    
    // Email Notifications (Admin only)
    Route::prefix('email-notifications')->middleware('role:admin')->group(function () {
        Route::get('/statistics', [EmailNotificationController::class, 'statistics']);
        Route::post('/test/stock-alert', [EmailNotificationController::class, 'testStockAlert']);
        Route::post('/test/order-confirmation', [EmailNotificationController::class, 'testOrderConfirmation']);
        Route::post('/test/order-status', [EmailNotificationController::class, 'testOrderStatusUpdate']);
        Route::post('/check/stock', [EmailNotificationController::class, 'checkAllStock']);
        Route::post('/check/maintenances', [EmailNotificationController::class, 'checkMaintenances']);
    });
    
    // Routes publiques auth (lecture seule) pour admin/employee/client
    Route::middleware('role:admin,employee,client')->group(function () {
        Route::get('/products', [ProductController::class, 'index']);
        Route::get('/products/{product}', [ProductController::class, 'show']);
        Route::get('/categories', [CategoryController::class, 'index']);
    });

    // Routes pour Admin et Employés
    Route::middleware('role:admin,employee')->group(function () {
        
        // Catégories (gestion complète sauf lecture déjà exposée ci-dessus)
        Route::apiResource('categories', CategoryController::class)->except(['index', 'show']);
        
        // Fournisseurs
        Route::apiResource('suppliers', SupplierController::class);
        
        // Produits
        Route::get('/products/critical-stock', [ProductController::class, 'criticalStock']);
        Route::get('/products/{product}/stock-movements', [ProductController::class, 'stockMovements']);
        Route::post('/products/{product}/adjust-stock', [ProductController::class, 'adjustStock']);
        Route::apiResource('products', ProductController::class)->except(['index', 'show']);
        
        // Clients
        Route::get('/clients/sectors', [ClientController::class, 'sectors']);
        Route::get('/clients/{client}/purchase-history', [ClientController::class, 'purchaseHistory']);
        Route::apiResource('clients', ClientController::class);
        
        // Commandes (gestion complète)
        Route::get('/orders/statistics', [OrderController::class, 'statistics']);
        Route::get('/orders/{order}/invoice', [OrderController::class, 'generateInvoice']);
        Route::apiResource('orders', OrderController::class);
        
        // Maintenances (gestion complète)
        Route::get('/maintenances/this-week', [MaintenanceController::class, 'thisWeek']);
        Route::get('/maintenances/upcoming', [MaintenanceController::class, 'upcoming']);
        Route::get('/maintenances/calendar', [MaintenanceController::class, 'calendar']);
        Route::get('/maintenances/statistics', [MaintenanceController::class, 'statistics']);
        Route::apiResource('maintenances', MaintenanceController::class);
        
        // Export PDF & Excel
        Route::prefix('export')->group(function () {
            // Produits
            Route::get('/products/pdf', [ExportController::class, 'exportProductsPdf']);
            Route::get('/products/excel', [ExportController::class, 'exportProductsExcel']);
            
            // Commandes
            Route::get('/orders/pdf', [ExportController::class, 'exportOrdersPdf']);
            Route::get('/orders/excel', [ExportController::class, 'exportOrdersExcel']);
            
            // Clients
            Route::get('/clients/pdf', [ExportController::class, 'exportClientsPdf']);
            Route::get('/clients/excel', [ExportController::class, 'exportClientsExcel']);
            
            // Maintenances
            Route::get('/maintenances/pdf', [ExportController::class, 'exportMaintenancesPdf']);
            Route::get('/maintenances/excel', [ExportController::class, 'exportMaintenancesExcel']);
            
            // Stock critique
            Route::get('/critical-stock/pdf', [ExportController::class, 'exportCriticalStockPdf']);
            
            // Rapport complet
            Route::get('/full-report/pdf', [ExportController::class, 'exportFullReportPdf']);
        });
        
        // Rapports et statistiques
        Route::prefix('reports')->group(function () {
            Route::get('/stats', [ReportController::class, 'getStats']);
            Route::get('/inventory', [ReportController::class, 'inventoryReport']);
            Route::get('/orders', [ReportController::class, 'ordersReport']);
            Route::get('/maintenance', [ReportController::class, 'maintenanceReport']);
        });
    });
    
    // Routes pour Clients uniquement (consultation de leurs propres données)
    Route::middleware('role:client')->prefix('my')->group(function () {
        Route::get('/orders', [OrderController::class, 'index']);
        Route::get('/orders/{order}', [OrderController::class, 'show']);
        Route::post('/orders', [OrderController::class, 'store']); // Créer une commande
        Route::put('/orders/{order}', [OrderController::class, 'update']); // Annuler une commande
        Route::get('/maintenances', [MaintenanceController::class, 'index']);
        Route::get('/maintenances/{maintenance}', [MaintenanceController::class, 'show']);
    });
    
    // Routes client pour consulter le catalogue et actions client
    Route::middleware('role:client')->group(function () {
        // Factures - Les clients peuvent télécharger leurs factures
        Route::get('/orders/{order}/invoice', [OrderController::class, 'generateInvoice']);
        
        // Devis (quotes)
        Route::get('/quotes', [QuoteController::class, 'index']);
        Route::post('/quotes', [QuoteController::class, 'store']);
        Route::get('/quotes/{quote}', [QuoteController::class, 'show']);
    });
    
    // Routes réservées aux Admins
    Route::middleware('role:admin')->prefix('admin')->group(function () {
        // Gestion des utilisateurs
        Route::get('/users', function () {
            return \App\Models\User::with('client')->paginate(15);
        });
        
        Route::post('/users', function (Request $request) {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users',
                'password' => 'required|min:8',
                'role' => 'required|in:admin,employee,client',
                'phone' => 'nullable|string',
            ]);
            
            $validated['password'] = bcrypt($validated['password']);
            $user = \App\Models\User::create($validated);
            
            return response()->json(['user' => $user, 'message' => 'Utilisateur créé'], 201);
        });
        
        Route::put('/users/{user}', function (Request $request, \App\Models\User $user) {
            $validated = $request->validate([
                'name' => 'sometimes|string|max:255',
                'email' => 'sometimes|email|unique:users,email,' . $user->id,
                'role' => 'sometimes|in:admin,employee,client',
                'phone' => 'nullable|string',
                'is_active' => 'sometimes|boolean',
            ]);
            
            $user->update($validated);
            
            return response()->json(['user' => $user, 'message' => 'Utilisateur mis à jour']);
        });
        
        Route::delete('/users/{user}', function (\App\Models\User $user) {
            if ($user->id === request()->user()->id) {
                return response()->json(['message' => 'Vous ne pouvez pas supprimer votre propre compte'], 422);
            }
            
            $user->delete();
            return response()->json(['message' => 'Utilisateur supprimé']);
        });
    });
});
