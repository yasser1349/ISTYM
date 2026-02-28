<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Quote;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class QuoteController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $query = Quote::with(['client', 'user']);

        // Si client, ne montrer que ses devis
        if ($user->isClient() && $user->client) {
            $query->where('client_id', $user->client->id);
        }

        // Filtres
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('client_id')) {
            $query->where('client_id', $request->client_id);
        }

        $quotes = $query->latest()->paginate($request->get('per_page', 15));

        return response()->json($quotes);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'client_id' => 'nullable|exists:clients,id',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|integer',
            'items.*.product_name' => 'required|string',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        $user = $request->user();
        
        // Si pas de client_id fourni et que l'utilisateur est un client, créer ou récupérer son profil client
        $clientId = $validated['client_id'] ?? null;
        if (!$clientId && $user->isClient()) {
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
        }
        
        if (!$clientId) {
            return response()->json(['message' => 'client_id est requis'], 422);
        }

        $quote = Quote::create([
            'quote_number' => Quote::generateQuoteNumber(),
            'client_id' => $clientId,
            'user_id' => $user->id,
            'status' => 'pending',
            'notes' => $validated['notes'] ?? null,
            'items' => $validated['items'],
            'valid_until' => now()->addDays(30),
        ]);

        $quote->calculateTotals();

        return response()->json([
            'quote' => $quote->load(['client', 'user']),
            'message' => 'Demande de devis créée avec succès'
        ], 201);
    }

    public function show(Quote $quote): JsonResponse
    {
        $user = request()->user();

        // Vérifier accès client
        if ($user->isClient() && $user->client && $quote->client_id !== $user->client->id) {
            return response()->json(['message' => 'Non autorisé'], 403);
        }

        return response()->json($quote->load(['client', 'user']));
    }

    public function update(Request $request, Quote $quote): JsonResponse
    {
        $validated = $request->validate([
            'status' => 'sometimes|in:pending,approved,rejected,converted',
            'notes' => 'nullable|string',
        ]);

        $quote->update($validated);

        return response()->json([
            'quote' => $quote->load(['client', 'user']),
            'message' => 'Devis mis à jour'
        ]);
    }

    public function destroy(Quote $quote): JsonResponse
    {
        $quote->delete();
        return response()->json(['message' => 'Devis supprimé']);
    }
}
