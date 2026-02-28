<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\StockMovement;
use App\Events\NewOrder;
use App\Events\StockAlert;
use App\Events\DashboardUpdate;
use App\Services\EmailNotificationService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class OrderController extends Controller
{
    protected EmailNotificationService $emailService;

    public function __construct(EmailNotificationService $emailService)
    {
        $this->emailService = $emailService;
    }

    /**
     * Liste des commandes selon le rôle de l'utilisateur
     * - Admin : Toutes les commandes
     * - Employee : Toutes les commandes (lecture seule sur certaines)
     * - Client : Uniquement ses propres commandes
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $query = Order::with(['client', 'user', 'supplier', 'items.product']);

        // RBAC : Filtrage selon le rôle
        if ($user->isClient()) {
            // Client : uniquement ses propres commandes
            $client = $user->client;
            if ($client) {
                $query->where('client_id', $client->id);
            } else {
                // Client sans profil = aucune commande
                return response()->json(['data' => [], 'total' => 0]);
            }
        }
        // Admin et Employee : voient toutes les commandes (pas de filtre)

        // Filtres additionnels (utiliser filled() au lieu de has() pour ignorer les strings vides)
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('client_id') && !$user->isClient()) {
            // Seuls admin/employee peuvent filtrer par client_id
            $query->where('client_id', $request->client_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $orders = $query->latest()->paginate($request->get('per_page', 15));

        return response()->json($orders);
    }

    /**
     * Création de commande selon le rôle
     * - Admin : Peut créer tous types de commandes (vente/achat)
     * - Employee : Peut créer des commandes pour les clients
     * - Client : Peut créer uniquement des commandes de vente pour lui-même
     */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        
        // RBAC : Validation selon le rôle
        $rules = [
            'type' => 'required|in:sale,purchase',
            'supplier_id' => 'required_if:type,purchase|nullable|exists:suppliers,id',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'discount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'expected_delivery' => 'nullable|date',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.discount' => 'nullable|numeric|min:0',
        ];
        
        // Client ne peut pas spécifier un client_id (c'est automatiquement lui-même)
        // Client ne peut créer que des commandes de vente
        if ($user->isClient()) {
            if ($request->type === 'purchase') {
                return response()->json(['message' => 'Les clients ne peuvent pas créer de commandes d\'achat'], 403);
            }
        } else {
            // Admin et Employee peuvent spécifier un client_id
            $rules['client_id'] = 'nullable|exists:clients,id';
        }
        
        $validated = $request->validate($rules);

        return DB::transaction(function () use ($request, $validated, $user) {
            // Déterminer le client_id selon le rôle
            $clientId = null;
            
            if ($user->isClient()) {
                // Client : utilise son propre profil client
                $client = $user->client;
                if (!$client) {
                    // Créer automatiquement un profil client
                    $client = \App\Models\Client::create([
                        'user_id' => $user->id,
                        'company_name' => $user->name,
                        'contact_name' => $user->name,
                        'email' => $user->email,
                        'phone' => $user->phone ?? '',
                        'is_active' => true,
                    ]);
                }
                $clientId = $client->id;
            } else {
                // Admin/Employee : peut spécifier un client_id ou le laisser vide pour les achats
                $clientId = $validated['client_id'] ?? null;
                
                // Pour les ventes, client_id est requis
                if ($validated['type'] === 'sale' && !$clientId) {
                    return response()->json(['message' => 'client_id est requis pour les ventes'], 422);
                }
            }
            
            // Créer la commande
            $order = Order::create([
                'order_number' => Order::generateOrderNumber($validated['type']),
                'client_id' => $clientId,
                'user_id' => $user->id,
                'type' => $validated['type'],
                'supplier_id' => $validated['supplier_id'] ?? null,
                'status' => 'pending',
                'tax_rate' => $validated['tax_rate'] ?? 20,
                'discount' => $validated['discount'] ?? 0,
                'notes' => $validated['notes'] ?? null,
                'expected_delivery' => $validated['expected_delivery'] ?? null,
            ]);

            // Ajouter les articles
            foreach ($validated['items'] as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'discount' => $item['discount'] ?? 0,
                    'total' => ($item['unit_price'] * $item['quantity']) - ($item['discount'] ?? 0),
                ]);
            }

            // Calculer les totaux
            $order->calculateTotals();

            // Envoyer email de confirmation au client
            $this->emailService->sendOrderConfirmation($order);

            return response()->json([
                'order' => $order->load(['client', 'items.product']),
                'message' => 'Commande créée avec succès'
            ], 201);
        });
    }

    public function show(Order $order): JsonResponse
    {
        $user = request()->user();

        // Vérifier accès client
        if ($user->isClient() && $user->client && $order->client_id !== $user->client->id) {
            return response()->json(['message' => 'Non autorisé'], 403);
        }

        return response()->json(
            $order->load(['client', 'user', 'supplier', 'items.product'])
        );
    }

    /**
     * Mettre à jour une commande
     * 
     * RBAC:
     * - Admin: Peut modifier toutes les commandes (tous les champs)
     * - Employee: Peut modifier les commandes (statut, notes, dates)
     * - Client: Peut annuler uniquement ses propres commandes en attente
     */
    public function update(Request $request, Order $order): JsonResponse
    {
        $user = $request->user();
        
        // ========== VÉRIFICATION DES DROITS D'ACCÈS ==========
        
        // Client: ne peut modifier que ses propres commandes
        if ($user->isClient()) {
            // Vérifier que le client a un profil
            if (!$user->client) {
                return response()->json(['message' => 'Profil client non trouvé'], 403);
            }
            
            // Vérifier que c'est sa commande
            if ($order->client_id !== $user->client->id) {
                return response()->json(['message' => 'Non autorisé'], 403);
            }
            
            // Client ne peut que annuler une commande en attente
            if ($order->status !== 'pending') {
                return response()->json([
                    'message' => 'Vous ne pouvez modifier que les commandes en attente'
                ], 422);
            }
            
            // Client ne peut que changer le statut en "cancelled"
            $validated = $request->validate([
                'status' => 'required|in:cancelled',
                'notes' => 'nullable|string',
            ]);
            
            $order->update($validated);
            
            return response()->json([
                'order' => $order->load(['client', 'items.product']),
                'message' => 'Commande annulée avec succès'
            ]);
        }
        
        // ========== ADMIN & EMPLOYEE ==========
        
        // Ne pas modifier une commande livrée ou annulée (sauf admin)
        if (!$user->isAdmin() && in_array($order->status, ['delivered', 'cancelled'])) {
            return response()->json([
                'message' => 'Cette commande ne peut plus être modifiée'
            ], 422);
        }

        $validated = $request->validate([
            'status' => 'sometimes|in:pending,confirmed,processing,shipped,delivered,cancelled',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'discount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'expected_delivery' => 'nullable|date',
        ]);

        $oldStatus = $order->status;
        $order->update($validated);

        // Si le statut a changé, envoyer email de mise à jour
        if (isset($validated['status']) && $validated['status'] !== $oldStatus) {
            $this->emailService->sendOrderStatusUpdate($order, $oldStatus, $validated['status']);
        }

        // Si livré, mettre à jour le stock
        if (isset($validated['status']) && $validated['status'] === 'delivered' && $oldStatus !== 'delivered') {
            $this->updateStockOnDelivery($order, $request->user()->id);
            $order->delivered_at = now();
            $order->save();
        }

        return response()->json([
            'order' => $order->load(['client', 'items.product']),
            'message' => 'Commande mise à jour'
        ]);
    }

    /**
     * Mettre à jour le stock lors de la livraison
     */
    private function updateStockOnDelivery(Order $order, int $userId): void
    {
        foreach ($order->items as $item) {
            $product = $item->product;
            $quantityBefore = $product->quantity_in_stock;

            // Vente = sortie de stock, Achat = entrée de stock
            if ($order->type === 'sale') {
                $product->quantity_in_stock -= $item->quantity;
                $type = 'out';
            } else {
                $product->quantity_in_stock += $item->quantity;
                $type = 'in';
            }
            $product->save();

            StockMovement::create([
                'product_id' => $product->id,
                'user_id' => $userId,
                'order_id' => $order->id,
                'type' => $type,
                'quantity' => $item->quantity,
                'quantity_before' => $quantityBefore,
                'quantity_after' => $product->quantity_in_stock,
                'reason' => $order->type === 'sale' ? 'Vente' : 'Achat fournisseur',
            ]);
        }
    }

    /**
     * Supprimer une commande
     * 
     * RBAC:
     * - Admin: Peut supprimer toutes les commandes (même livrées/annulées)
     * - Employee: Peut supprimer uniquement les commandes en attente
     * - Client: Ne peut PAS supprimer (seulement annuler via update)
     */
    public function destroy(Order $order): JsonResponse
    {
        $user = request()->user();
        
        // Client: pas de suppression autorisée
        if ($user->isClient()) {
            return response()->json([
                'message' => 'Vous n\'êtes pas autorisé à supprimer des commandes. Utilisez l\'option d\'annulation.'
            ], 403);
        }
        
        // Employee: seulement commandes en attente
        if ($user->isEmployee() && $order->status !== 'pending') {
            return response()->json([
                'message' => 'Seules les commandes en attente peuvent être supprimées'
            ], 422);
        }
        
        // Admin: peut supprimer n'importe quelle commande (aucune restriction supplémentaire)

        $order->delete();

        return response()->json(['message' => 'Commande supprimée']);
    }

    /**
     * Générer la facture PDF
     */
    public function generateInvoice(Order $order)
    {
        $order->load(['client', 'items.product']);

        $pdf = Pdf::loadView('pdf.invoice', compact('order'));

        return $pdf->download("facture-{$order->order_number}.pdf");
    }

    /**
     * Statistiques des commandes
     */
    public function statistics(Request $request): JsonResponse
    {
        $year = $request->get('year', now()->year);

        $stats = [
            'total_orders' => Order::whereYear('created_at', $year)->count(),
            'total_sales' => Order::sales()->whereYear('created_at', $year)->sum('total'),
            'total_purchases' => Order::purchases()->whereYear('created_at', $year)->sum('total'),
            'pending_orders' => Order::pending()->count(),
            'average_order_value' => Order::delivered()->whereYear('created_at', $year)->avg('total'),
        ];

        return response()->json($stats);
    }
}
