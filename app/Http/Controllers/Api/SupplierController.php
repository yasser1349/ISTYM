<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class SupplierController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Supplier::withCount('products');

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        if ($request->has('active_only') && $request->active_only) {
            $query->active();
        }

        $suppliers = $query->orderBy('name')->paginate($request->get('per_page', 15));

        return response()->json($suppliers);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|unique:suppliers',
            'email' => 'nullable|email',
            'phone' => 'nullable|string',
            'address' => 'nullable|string',
            'city' => 'nullable|string',
            'country' => 'nullable|string',
            'contact_person' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $supplier = Supplier::create($validated);

        return response()->json([
            'supplier' => $supplier,
            'message' => 'Fournisseur créé'
        ], 201);
    }

    public function show(Supplier $supplier): JsonResponse
    {
        return response()->json(
            $supplier->load(['products' => function ($query) {
                $query->take(10);
            }])
        );
    }

    public function update(Request $request, Supplier $supplier): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'code' => 'sometimes|string|unique:suppliers,code,' . $supplier->id,
            'email' => 'nullable|email',
            'phone' => 'nullable|string',
            'address' => 'nullable|string',
            'city' => 'nullable|string',
            'country' => 'nullable|string',
            'contact_person' => 'nullable|string',
            'notes' => 'nullable|string',
            'is_active' => 'sometimes|boolean',
        ]);

        $supplier->update($validated);

        return response()->json([
            'supplier' => $supplier,
            'message' => 'Fournisseur mis à jour'
        ]);
    }

    public function destroy(Supplier $supplier): JsonResponse
    {
        if ($supplier->products()->count() > 0) {
            return response()->json([
                'message' => 'Impossible de supprimer un fournisseur avec des produits'
            ], 422);
        }

        $supplier->delete();

        return response()->json(['message' => 'Fournisseur supprimé']);
    }
}
