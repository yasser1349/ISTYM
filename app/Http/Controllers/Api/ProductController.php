<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Notification;
use App\Services\EmailNotificationService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ProductController extends Controller
{
    protected EmailNotificationService $emailService;

    public function __construct(EmailNotificationService $emailService)
    {
        $this->emailService = $emailService;
    }

    /**
     * Liste des produits
     */
    public function index(Request $request): JsonResponse
    {
        $query = Product::with(['category', 'supplier']);

        // Filtres (utiliser filled() au lieu de has() pour ignorer les strings vides)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('reference', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        if ($request->has('critical_only') && $request->critical_only) {
            $query->criticalStock();
        }

        if ($request->has('in_stock') && $request->in_stock) {
            $query->inStock();
        }

        // Tri
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Pagination
        $perPage = $request->get('per_page', 15);
        $products = $query->paginate($perPage);

        return response()->json($products);
    }

    /**
     * Créer un produit
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'reference' => 'required|string|unique:products',
            'description' => 'nullable|string',
            'category_id' => 'required|exists:categories,id',
            'supplier_id' => 'required|exists:suppliers,id',
            'purchase_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'quantity_in_stock' => 'required|integer|min:0',
            'minimum_stock' => 'required|integer|min:0',
            'maximum_stock' => 'required|integer|min:0',
            'unit' => 'nullable|string',
            'location' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('products', 'public');
        }

        $product = Product::create($validated);

        // Enregistrer le mouvement de stock initial
        if ($product->quantity_in_stock > 0) {
            StockMovement::create([
                'product_id' => $product->id,
                'user_id' => $request->user()->id,
                'type' => 'in',
                'quantity' => $product->quantity_in_stock,
                'quantity_before' => 0,
                'quantity_after' => $product->quantity_in_stock,
                'reason' => 'Stock initial',
            ]);
        }

        return response()->json([
            'product' => $product->load(['category', 'supplier']),
            'message' => 'Produit créé avec succès'
        ], 201);
    }

    /**
     * Afficher un produit
     */
    public function show(Product $product): JsonResponse
    {
        return response()->json(
            $product->load(['category', 'supplier', 'stockMovements' => function ($query) {
                $query->latest()->take(10);
            }])
        );
    }

    /**
     * Mettre à jour un produit
     */
    public function update(Request $request, Product $product): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'reference' => 'sometimes|string|unique:products,reference,' . $product->id,
            'description' => 'nullable|string',
            'category_id' => 'sometimes|exists:categories,id',
            'supplier_id' => 'sometimes|exists:suppliers,id',
            'purchase_price' => 'sometimes|numeric|min:0',
            'selling_price' => 'sometimes|numeric|min:0',
            'minimum_stock' => 'sometimes|integer|min:0',
            'maximum_stock' => 'sometimes|integer|min:0',
            'unit' => 'nullable|string',
            'location' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
            'is_active' => 'sometimes|boolean',
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('products', 'public');
        }

        $product->update($validated);

        return response()->json([
            'product' => $product->load(['category', 'supplier']),
            'message' => 'Produit mis à jour'
        ]);
    }

    /**
     * Supprimer un produit
     */
    public function destroy(Product $product): JsonResponse
    {
        $product->delete();

        return response()->json([
            'message' => 'Produit supprimé'
        ]);
    }

    /**
     * Ajuster le stock
     */
    public function adjustStock(Request $request, Product $product): JsonResponse
    {
        $request->validate([
            'type' => 'required|in:in,out,adjustment',
            'quantity' => 'required|integer|min:1',
            'reason' => 'required|string',
            'notes' => 'nullable|string',
        ]);

        $quantityBefore = $product->quantity_in_stock;

        // Vérifier si sortie possible
        if ($request->type === 'out' && $request->quantity > $product->quantity_in_stock) {
            return response()->json([
                'message' => 'Stock insuffisant'
            ], 422);
        }

        // Ajuster le stock
        if ($request->type === 'in') {
            $product->quantity_in_stock += $request->quantity;
        } elseif ($request->type === 'out') {
            $product->quantity_in_stock -= $request->quantity;
        } else {
            $product->quantity_in_stock = $request->quantity;
        }
        $product->save();

        // Enregistrer le mouvement
        StockMovement::create([
            'product_id' => $product->id,
            'user_id' => $request->user()->id,
            'type' => $request->type,
            'quantity' => $request->quantity,
            'quantity_before' => $quantityBefore,
            'quantity_after' => $product->quantity_in_stock,
            'reason' => $request->reason,
            'notes' => $request->notes,
        ]);

        // Notifier si stock critique
        if ($product->isCriticalStock()) {
            Notification::notifyLowStock($product);
            // Envoyer email d'alerte stock
            $this->emailService->checkAndSendStockAlert($product);
        }

        return response()->json([
            'product' => $product,
            'message' => 'Stock ajusté avec succès'
        ]);
    }

    /**
     * Produits en stock critique
     */
    public function criticalStock(): JsonResponse
    {
        $products = Product::with(['category', 'supplier'])
            ->criticalStock()
            ->get();

        return response()->json($products);
    }

    /**
     * Historique des mouvements de stock
     */
    public function stockMovements(Product $product): JsonResponse
    {
        $movements = $product->stockMovements()
            ->with('user')
            ->latest()
            ->paginate(20);

        return response()->json($movements);
    }
}
