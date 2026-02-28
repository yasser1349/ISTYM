<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Order;
use App\Models\Client;
use App\Models\Maintenance;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    /**
     * Global search across all entities
     */
    public function search(Request $request)
    {
        $query = $request->get('q', '');
        $limit = $request->get('limit', 5);

        if (strlen($query) < 2) {
            return response()->json([
                'products' => [],
                'orders' => [],
                'clients' => [],
                'maintenances' => [],
                'total' => 0
            ]);
        }

        // Search Products
        $products = Product::where(function($q) use ($query) {
            $q->where('name', 'like', "%{$query}%")
              ->orWhere('reference', 'like', "%{$query}%")
              ->orWhere('description', 'like', "%{$query}%");
        })
        ->with('category:id,name')
        ->select('id', 'name', 'reference', 'price', 'quantity', 'category_id', 'image')
        ->limit($limit)
        ->get()
        ->map(function($product) {
            return [
                'id' => $product->id,
                'type' => 'product',
                'title' => $product->name,
                'subtitle' => $product->reference,
                'description' => $product->category->name ?? 'Sans catégorie',
                'meta' => number_format($product->price, 2) . ' MAD',
                'badge' => $product->quantity . ' en stock',
                'badge_color' => $product->quantity > 10 ? 'green' : ($product->quantity > 0 ? 'orange' : 'red'),
                'image' => $product->image,
                'url' => '/inventory/' . $product->id
            ];
        });

        // Search Orders
        $orders = Order::where(function($q) use ($query) {
            $q->where('order_number', 'like', "%{$query}%")
              ->orWhere('notes', 'like', "%{$query}%");
        })
        ->orWhereHas('client', function($q) use ($query) {
            $q->where('company', 'like', "%{$query}%")
              ->orWhere('contact_name', 'like', "%{$query}%");
        })
        ->with('client:id,company,contact_name')
        ->select('id', 'order_number', 'client_id', 'status', 'total', 'created_at')
        ->limit($limit)
        ->get()
        ->map(function($order) {
            $statusColors = [
                'pending' => 'orange',
                'confirmed' => 'blue',
                'shipped' => 'purple',
                'delivered' => 'green',
                'cancelled' => 'red'
            ];
            $statusLabels = [
                'pending' => 'En attente',
                'confirmed' => 'Confirmée',
                'shipped' => 'Expédiée',
                'delivered' => 'Livrée',
                'cancelled' => 'Annulée'
            ];
            return [
                'id' => $order->id,
                'type' => 'order',
                'title' => $order->order_number,
                'subtitle' => $order->client->company ?? $order->client->contact_name ?? 'Client inconnu',
                'description' => $order->created_at->format('d/m/Y'),
                'meta' => number_format($order->total, 2) . ' MAD',
                'badge' => $statusLabels[$order->status] ?? $order->status,
                'badge_color' => $statusColors[$order->status] ?? 'gray',
                'url' => '/orders/' . $order->id
            ];
        });

        // Search Clients
        $clients = Client::where(function($q) use ($query) {
            $q->where('company', 'like', "%{$query}%")
              ->orWhere('contact_name', 'like', "%{$query}%")
              ->orWhere('email', 'like', "%{$query}%")
              ->orWhere('phone', 'like', "%{$query}%")
              ->orWhere('ice', 'like', "%{$query}%");
        })
        ->select('id', 'company', 'contact_name', 'email', 'phone', 'sector', 'status')
        ->limit($limit)
        ->get()
        ->map(function($client) {
            return [
                'id' => $client->id,
                'type' => 'client',
                'title' => $client->company ?? $client->contact_name,
                'subtitle' => $client->contact_name,
                'description' => $client->email,
                'meta' => $client->phone,
                'badge' => $client->sector ?? 'Non défini',
                'badge_color' => $client->status === 'active' ? 'green' : 'gray',
                'url' => '/clients/' . $client->id
            ];
        });

        // Search Maintenances
        $maintenances = Maintenance::where(function($q) use ($query) {
            $q->where('title', 'like', "%{$query}%")
              ->orWhere('description', 'like', "%{$query}%");
        })
        ->orWhereHas('equipment', function($q) use ($query) {
            $q->where('name', 'like', "%{$query}%");
        })
        ->with('equipment:id,name')
        ->select('id', 'title', 'equipment_id', 'type', 'status', 'scheduled_date')
        ->limit($limit)
        ->get()
        ->map(function($maintenance) {
            $statusColors = [
                'scheduled' => 'blue',
                'in_progress' => 'orange',
                'completed' => 'green',
                'cancelled' => 'red'
            ];
            $statusLabels = [
                'scheduled' => 'Planifiée',
                'in_progress' => 'En cours',
                'completed' => 'Terminée',
                'cancelled' => 'Annulée'
            ];
            return [
                'id' => $maintenance->id,
                'type' => 'maintenance',
                'title' => $maintenance->title,
                'subtitle' => $maintenance->equipment->name ?? 'Équipement inconnu',
                'description' => $maintenance->scheduled_date?->format('d/m/Y') ?? '',
                'meta' => ucfirst($maintenance->type),
                'badge' => $statusLabels[$maintenance->status] ?? $maintenance->status,
                'badge_color' => $statusColors[$maintenance->status] ?? 'gray',
                'url' => '/maintenance/' . $maintenance->id
            ];
        });

        $total = $products->count() + $orders->count() + $clients->count() + $maintenances->count();

        return response()->json([
            'products' => $products,
            'orders' => $orders,
            'clients' => $clients,
            'maintenances' => $maintenances,
            'total' => $total,
            'query' => $query
        ]);
    }

    /**
     * Get search suggestions based on recent searches and popular items
     */
    public function suggestions(Request $request)
    {
        $query = $request->get('q', '');

        // Popular/Recent suggestions
        $suggestions = [];

        if (strlen($query) >= 1) {
            // Product names matching
            $productNames = Product::where('name', 'like', "{$query}%")
                ->limit(3)
                ->pluck('name')
                ->map(fn($name) => ['text' => $name, 'type' => 'product']);

            // Client companies matching
            $clientNames = Client::where('company', 'like', "{$query}%")
                ->limit(3)
                ->pluck('company')
                ->map(fn($name) => ['text' => $name, 'type' => 'client']);

            // Order numbers matching
            $orderNumbers = Order::where('order_number', 'like', "{$query}%")
                ->limit(2)
                ->pluck('order_number')
                ->map(fn($num) => ['text' => $num, 'type' => 'order']);

            $suggestions = $productNames->concat($clientNames)->concat($orderNumbers)->values();
        }

        return response()->json([
            'suggestions' => $suggestions
        ]);
    }
}
