<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;

class ClientController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Client::with(['user', 'orders']);

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('company_name', 'like', "%{$search}%")
                  ->orWhere('contact_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->has('sector')) {
            $query->where('sector', $request->sector);
        }

        if ($request->has('active_only') && $request->active_only) {
            $query->active();
        }

        $clients = $query->latest()->paginate($request->get('per_page', 15));
        
        // Ajouter les statistiques pour chaque client
        $clients->getCollection()->transform(function ($client) {
            $client->total_orders = $client->orders->count();
            $client->total_amount = $client->orders->where('status', 'delivered')->sum('total');
            $client->last_order_date = $client->orders->max('created_at');
            return $client;
        });

        return response()->json($clients);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'contact_name' => 'required|string|max:255',
            'email' => 'required|email|unique:clients',
            'phone' => 'nullable|string',
            'address' => 'nullable|string',
            'city' => 'nullable|string',
            'country' => 'nullable|string',
            'ice' => 'nullable|string',
            'sector' => 'nullable|string',
            'notes' => 'nullable|string',
            'credit_limit' => 'nullable|numeric|min:0',
            'create_user_account' => 'sometimes|boolean',
            'password' => 'required_if:create_user_account,true|min:8',
        ]);

        // Créer le compte utilisateur si demandé
        $userId = null;
        if ($request->get('create_user_account', false)) {
            $user = User::create([
                'name' => $validated['contact_name'],
                'email' => $validated['email'],
                'password' => Hash::make($request->password),
                'phone' => $validated['phone'] ?? null,
                'role' => 'client',
            ]);
            $userId = $user->id;
        }

        $client = Client::create([
            ...$validated,
            'user_id' => $userId,
        ]);

        return response()->json([
            'client' => $client->load('user'),
            'message' => 'Client créé avec succès'
        ], 201);
    }

    public function show(Client $client): JsonResponse
    {
        $client->load([
            'user',
            'orders' => function ($query) {
                $query->latest()->take(10);
            },
            'maintenances' => function ($query) {
                $query->latest()->take(5);
            },
        ]);

        $client->total_purchases = $client->totalPurchases();
        $client->pending_orders = $client->pendingOrders();

        return response()->json($client);
    }

    public function update(Request $request, Client $client): JsonResponse
    {
        $validated = $request->validate([
            'company_name' => 'sometimes|string|max:255',
            'contact_name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:clients,email,' . $client->id,
            'phone' => 'nullable|string',
            'address' => 'nullable|string',
            'city' => 'nullable|string',
            'country' => 'nullable|string',
            'ice' => 'nullable|string',
            'sector' => 'nullable|string',
            'notes' => 'nullable|string',
            'credit_limit' => 'nullable|numeric|min:0',
            'is_active' => 'sometimes|boolean',
        ]);

        $client->update($validated);

        // Mettre à jour l'email de l'utilisateur associé si changé
        if ($client->user && isset($validated['email'])) {
            $client->user->update(['email' => $validated['email']]);
        }

        return response()->json([
            'client' => $client->load('user'),
            'message' => 'Client mis à jour'
        ]);
    }

    public function destroy(Client $client): JsonResponse
    {
        if ($client->orders()->count() > 0) {
            return response()->json([
                'message' => 'Impossible de supprimer un client avec des commandes'
            ], 422);
        }

        // Supprimer le compte utilisateur associé
        if ($client->user) {
            $client->user->delete();
        }

        $client->delete();

        return response()->json(['message' => 'Client supprimé']);
    }

    /**
     * Historique des achats d'un client
     */
    public function purchaseHistory(Client $client): JsonResponse
    {
        $orders = $client->orders()
            ->with(['items.product', 'user'])
            ->delivered()
            ->latest()
            ->paginate(20);

        return response()->json($orders);
    }

    /**
     * Secteurs industriels disponibles
     */
    public function sectors(): JsonResponse
    {
        $sectors = Client::whereNotNull('sector')
            ->distinct()
            ->pluck('sector');

        return response()->json($sectors);
    }
}
