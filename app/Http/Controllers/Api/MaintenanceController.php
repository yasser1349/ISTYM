<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Maintenance;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class MaintenanceController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $query = Maintenance::with(['client', 'user']);

        // Si client, ne montrer que ses maintenances
        if ($user->isClient() && $user->client) {
            $query->where('client_id', $user->client->id);
        }

        // Filtres (utiliser filled() au lieu de has() pour ignorer les strings vides)
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', "%{$request->search}%")
                  ->orWhere('description', 'like', "%{$request->search}%")
                  ->orWhere('equipment', 'like', "%{$request->search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->filled('client_id')) {
            $query->where('client_id', $request->client_id);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('scheduled_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('scheduled_date', '<=', $request->date_to);
        }

        // Tri
        $sortBy = $request->get('sort_by', 'scheduled_date');
        $sortOrder = $request->get('sort_order', 'asc');
        $query->orderBy($sortBy, $sortOrder);

        $maintenances = $query->paginate($request->get('per_page', 15));

        return response()->json($maintenances);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:preventive,corrective,inspection',
            'priority' => 'required|in:low,medium,high,urgent',
            'equipment' => 'nullable|string',
            'location' => 'nullable|string',
            'scheduled_date' => 'required|date|after_or_equal:today',
            'scheduled_time' => 'nullable|date_format:H:i',
            'duration_hours' => 'nullable|integer|min:1',
            'notes' => 'nullable|string',
        ]);

        $maintenance = Maintenance::create([
            ...$validated,
            'reference' => Maintenance::generateReference(),
            'user_id' => $request->user()->id,
            'status' => 'scheduled',
        ]);

        return response()->json([
            'maintenance' => $maintenance->load(['client', 'user']),
            'message' => 'Maintenance planifiée avec succès'
        ], 201);
    }

    public function show(Maintenance $maintenance): JsonResponse
    {
        $user = request()->user();

        // Vérifier accès client
        if ($user->isClient() && $user->client && $maintenance->client_id !== $user->client->id) {
            return response()->json(['message' => 'Non autorisé'], 403);
        }

        return response()->json(
            $maintenance->load(['client', 'user'])
        );
    }

    public function update(Request $request, Maintenance $maintenance): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'type' => 'sometimes|in:preventive,corrective,inspection',
            'priority' => 'sometimes|in:low,medium,high,urgent',
            'status' => 'sometimes|in:scheduled,in_progress,completed,cancelled',
            'equipment' => 'nullable|string',
            'location' => 'nullable|string',
            'scheduled_date' => 'sometimes|date',
            'scheduled_time' => 'nullable|date_format:H:i',
            'duration_hours' => 'nullable|integer|min:1',
            'work_done' => 'nullable|string',
            'parts_used' => 'nullable|array',
            'labor_cost' => 'nullable|numeric|min:0',
            'parts_cost' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'next_maintenance' => 'nullable|date',
        ]);

        // Si complété, ajouter la date
        if (isset($validated['status']) && $validated['status'] === 'completed' && !$maintenance->completed_date) {
            $validated['completed_date'] = now();
        }

        $maintenance->update($validated);

        // Recalculer le coût total
        if (isset($validated['labor_cost']) || isset($validated['parts_cost'])) {
            $maintenance->calculateTotalCost();
        }

        return response()->json([
            'maintenance' => $maintenance->load(['client', 'user']),
            'message' => 'Maintenance mise à jour'
        ]);
    }

    public function destroy(Maintenance $maintenance): JsonResponse
    {
        if ($maintenance->status === 'completed') {
            return response()->json([
                'message' => 'Impossible de supprimer une maintenance terminée'
            ], 422);
        }

        $maintenance->delete();

        return response()->json(['message' => 'Maintenance supprimée']);
    }

    /**
     * Maintenances de cette semaine
     */
    public function thisWeek(): JsonResponse
    {
        $maintenances = Maintenance::with(['client', 'user'])
            ->thisWeek()
            ->orderBy('scheduled_date')
            ->orderBy('scheduled_time')
            ->get();

        return response()->json($maintenances);
    }

    /**
     * Maintenances à venir
     */
    public function upcoming(): JsonResponse
    {
        $maintenances = Maintenance::with(['client', 'user'])
            ->upcoming()
            ->take(10)
            ->get();

        return response()->json($maintenances);
    }

    /**
     * Calendrier des maintenances
     */
    public function calendar(Request $request): JsonResponse
    {
        $month = $request->get('month', now()->month);
        $year = $request->get('year', now()->year);

        $maintenances = Maintenance::with(['client'])
            ->whereMonth('scheduled_date', $month)
            ->whereYear('scheduled_date', $year)
            ->get()
            ->map(function ($m) {
                return [
                    'id' => $m->id,
                    'title' => $m->title,
                    'date' => $m->scheduled_date->format('Y-m-d'),
                    'time' => $m->scheduled_time?->format('H:i'),
                    'client' => $m->client->company_name,
                    'status' => $m->status,
                    'priority' => $m->priority,
                    'type' => $m->type,
                ];
            });

        return response()->json($maintenances);
    }

    /**
     * Statistiques des maintenances
     */
    public function statistics(Request $request): JsonResponse
    {
        $year = $request->get('year', now()->year);

        $stats = [
            'total' => Maintenance::whereYear('scheduled_date', $year)->count(),
            'completed' => Maintenance::completed()->whereYear('scheduled_date', $year)->count(),
            'scheduled' => Maintenance::scheduled()->count(),
            'in_progress' => Maintenance::inProgress()->count(),
            'preventive' => Maintenance::preventive()->whereYear('scheduled_date', $year)->count(),
            'total_cost' => Maintenance::completed()->whereYear('scheduled_date', $year)->sum('total_cost'),
        ];

        return response()->json($stats);
    }
}
