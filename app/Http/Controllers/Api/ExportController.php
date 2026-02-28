<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Order;
use App\Models\Client;
use App\Models\Maintenance;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Response;

class ExportController extends Controller
{
    // ==================== EXPORT PRODUITS ====================
    
    public function exportProductsPdf(Request $request)
    {
        $query = Product::with(['category', 'supplier']);
        
        if ($request->has('category_id') && $request->category_id) {
            $query->where('category_id', $request->category_id);
        }
        if ($request->has('stock_status') && $request->stock_status === 'critical') {
            $query->whereRaw('quantity_in_stock <= minimum_stock');
        }
        
        $products = $query->orderBy('name')->get();
        
        $data = [
            'title' => 'Liste des Produits',
            'date' => now()->format('d/m/Y H:i'),
            'company' => 'ISTYM - Gestion de Stock',
            'products' => $products,
            'total' => $products->count(),
            'total_value' => $products->sum(fn($p) => $p->quantity_in_stock * $p->selling_price),
        ];
        
        $pdf = Pdf::loadView('exports.products-pdf', $data);
        $pdf->setPaper('a4', 'landscape');
        
        return $pdf->download('produits_' . now()->format('Y-m-d_His') . '.pdf');
    }
    
    public function exportProductsExcel(Request $request)
    {
        $query = Product::with(['category', 'supplier']);
        
        if ($request->has('category_id') && $request->category_id) {
            $query->where('category_id', $request->category_id);
        }
        if ($request->has('stock_status') && $request->stock_status === 'critical') {
            $query->whereRaw('quantity_in_stock <= minimum_stock');
        }
        
        $products = $query->orderBy('name')->get();
        
        $filename = 'produits_' . now()->format('Y-m-d_His') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];
        
        $callback = function() use ($products) {
            $file = fopen('php://output', 'w');
            // BOM pour UTF-8 dans Excel
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // En-têtes
            fputcsv($file, ['ID', 'Référence', 'Nom', 'Catégorie', 'Fournisseur', 'Quantité', 'Stock Min', 'Prix Unitaire', 'Valeur Stock', 'Statut'], ';');
            
            foreach ($products as $product) {
                $status = $product->quantity_in_stock <= $product->minimum_stock ? 'Critique' : 
                         ($product->quantity_in_stock <= $product->minimum_stock * 1.5 ? 'Bas' : 'OK');
                
                fputcsv($file, [
                    $product->id,
                    $product->reference,
                    $product->name,
                    $product->category->name ?? 'N/A',
                    $product->supplier->name ?? 'N/A',
                    $product->quantity_in_stock,
                    $product->minimum_stock,
                    number_format($product->selling_price, 2, ',', ' ') . ' MAD',
                    number_format($product->quantity_in_stock * $product->selling_price, 2, ',', ' ') . ' MAD',
                    $status,
                ], ';');
            }
            
            fclose($file);
        };
        
        return Response::stream($callback, 200, $headers);
    }
    
    // ==================== EXPORT COMMANDES ====================
    
    public function exportOrdersPdf(Request $request)
    {
        $query = Order::with(['client', 'items.product']);
        
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }
        if ($request->has('from_date') && $request->from_date) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        if ($request->has('to_date') && $request->to_date) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }
        
        $orders = $query->orderBy('created_at', 'desc')->get();
        
        $data = [
            'title' => 'Liste des Commandes',
            'date' => now()->format('d/m/Y H:i'),
            'company' => 'ISTYM - Gestion de Stock',
            'orders' => $orders,
            'total' => $orders->count(),
            'total_amount' => $orders->sum('total'),
        ];
        
        $pdf = Pdf::loadView('exports.orders-pdf', $data);
        $pdf->setPaper('a4', 'portrait');
        
        return $pdf->download('commandes_' . now()->format('Y-m-d_His') . '.pdf');
    }
    
    public function exportOrdersExcel(Request $request)
    {
        $query = Order::with(['client', 'items.product']);
        
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }
        if ($request->has('from_date') && $request->from_date) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        if ($request->has('to_date') && $request->to_date) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }
        
        $orders = $query->orderBy('created_at', 'desc')->get();
        
        $filename = 'commandes_' . now()->format('Y-m-d_His') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];
        
        $callback = function() use ($orders) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            fputcsv($file, ['N° Commande', 'Date', 'Client', 'Nb Articles', 'Total', 'Statut', 'Paiement'], ';');
            
            foreach ($orders as $order) {
                $statusLabel = match($order->status) {
                    'pending' => 'En attente',
                    'confirmed' => 'Confirmée',
                    'shipped' => 'Expédiée',
                    'delivered' => 'Livrée',
                    'cancelled' => 'Annulée',
                    default => $order->status,
                };
                
                fputcsv($file, [
                    $order->reference ?? 'CMD-' . str_pad($order->id, 5, '0', STR_PAD_LEFT),
                    $order->created_at->format('d/m/Y'),
                    $order->client->company ?? $order->client->name ?? 'N/A',
                    $order->items->count() . ' article(s)',
                    number_format($order->total, 2, ',', ' ') . ' MAD',
                    $statusLabel,
                    $order->payment_status === 'paid' ? 'Payé' : 'En attente',
                ], ';');
            }
            
            fclose($file);
        };
        
        return Response::stream($callback, 200, $headers);
    }
    
    // ==================== EXPORT CLIENTS ====================
    
    public function exportClientsPdf(Request $request)
    {
        $clients = Client::withCount('orders')
            ->withSum('orders', 'total')
            ->orderBy('company')
            ->get();
        
        $data = [
            'title' => 'Liste des Clients',
            'date' => now()->format('d/m/Y H:i'),
            'company' => 'ISTYM - Gestion de Stock',
            'clients' => $clients,
            'total' => $clients->count(),
        ];
        
        $pdf = Pdf::loadView('exports.clients-pdf', $data);
        $pdf->setPaper('a4', 'portrait');
        
        return $pdf->download('clients_' . now()->format('Y-m-d_His') . '.pdf');
    }
    
    public function exportClientsExcel(Request $request)
    {
        $clients = Client::withCount('orders')
            ->withSum('orders', 'total')
            ->orderBy('company')
            ->get();
        
        $filename = 'clients_' . now()->format('Y-m-d_His') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];
        
        $callback = function() use ($clients) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            fputcsv($file, ['ID', 'Entreprise', 'Contact', 'Email', 'Téléphone', 'Ville', 'Nb Commandes', 'Total Achats'], ';');
            
            foreach ($clients as $client) {
                fputcsv($file, [
                    $client->id,
                    $client->company ?? 'N/A',
                    $client->contact_name ?? $client->name ?? 'N/A',
                    $client->email,
                    $client->phone ?? 'N/A',
                    $client->city ?? 'N/A',
                    $client->orders_count,
                    number_format($client->orders_sum_total ?? 0, 2, ',', ' ') . ' MAD',
                ], ';');
            }
            
            fclose($file);
        };
        
        return Response::stream($callback, 200, $headers);
    }
    
    // ==================== EXPORT MAINTENANCES ====================
    
    public function exportMaintenancesPdf(Request $request)
    {
        $query = Maintenance::with(['product', 'technician']);
        
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }
        
        $maintenances = $query->orderBy('scheduled_date', 'desc')->get();
        
        $data = [
            'title' => 'Liste des Maintenances',
            'date' => now()->format('d/m/Y H:i'),
            'company' => 'ISTYM - Gestion de Stock',
            'maintenances' => $maintenances,
            'total' => $maintenances->count(),
        ];
        
        $pdf = Pdf::loadView('exports.maintenances-pdf', $data);
        $pdf->setPaper('a4', 'portrait');
        
        return $pdf->download('maintenances_' . now()->format('Y-m-d_His') . '.pdf');
    }
    
    public function exportMaintenancesExcel(Request $request)
    {
        $query = Maintenance::with(['product', 'technician']);
        
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }
        
        $maintenances = $query->orderBy('scheduled_date', 'desc')->get();
        
        $filename = 'maintenances_' . now()->format('Y-m-d_His') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];
        
        $callback = function() use ($maintenances) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            fputcsv($file, ['ID', 'Titre', 'Équipement', 'Type', 'Date Prévue', 'Technicien', 'Statut', 'Priorité'], ';');
            
            foreach ($maintenances as $maintenance) {
                $statusLabel = match($maintenance->status) {
                    'scheduled' => 'Planifiée',
                    'in_progress' => 'En cours',
                    'completed' => 'Terminée',
                    'cancelled' => 'Annulée',
                    default => $maintenance->status,
                };
                
                fputcsv($file, [
                    $maintenance->id,
                    $maintenance->title,
                    $maintenance->product->name ?? $maintenance->equipment ?? 'N/A',
                    $maintenance->type === 'preventive' ? 'Préventive' : 'Corrective',
                    $maintenance->scheduled_date ? date('d/m/Y', strtotime($maintenance->scheduled_date)) : 'N/A',
                    $maintenance->technician->name ?? 'Non assigné',
                    $statusLabel,
                    ucfirst($maintenance->priority ?? 'normal'),
                ], ';');
            }
            
            fclose($file);
        };
        
        return Response::stream($callback, 200, $headers);
    }
    
    // ==================== EXPORT STOCK CRITIQUE ====================
    
    public function exportCriticalStockPdf()
    {
        $products = Product::with(['category', 'supplier'])
            ->whereRaw('quantity_in_stock <= minimum_stock')
            ->orderBy('quantity_in_stock')
            ->get();
        
        $data = [
            'title' => 'Rapport Stock Critique',
            'date' => now()->format('d/m/Y H:i'),
            'company' => 'ISTYM - Gestion de Stock',
            'products' => $products,
            'total' => $products->count(),
            'alert_level' => 'URGENT',
        ];
        
        $pdf = Pdf::loadView('exports.critical-stock-pdf', $data);
        $pdf->setPaper('a4', 'portrait');
        
        return $pdf->download('stock_critique_' . now()->format('Y-m-d_His') . '.pdf');
    }
    
    // ==================== RAPPORT COMPLET ====================
    
    public function exportFullReportPdf(Request $request)
    {
        $data = [
            'title' => 'Rapport Complet',
            'date' => now()->format('d/m/Y H:i'),
            'company' => 'ISTYM - Gestion de Stock',
            'period' => $request->period ?? 'Mensuel',
            'stats' => [
                'total_products' => Product::count(),
                'total_value' => Product::selectRaw('SUM(quantity_in_stock * selling_price) as value')->value('value') ?? 0,
                'critical_stock' => Product::whereRaw('quantity_in_stock <= minimum_stock')->count(),
                'total_orders' => Order::count(),
                'orders_this_month' => Order::whereMonth('created_at', now()->month)->count(),
                'revenue_this_month' => Order::whereMonth('created_at', now()->month)->sum('total') ?? 0,
                'total_clients' => Client::count(),
                'active_maintenances' => Maintenance::whereIn('status', ['scheduled', 'in_progress'])->count(),
            ],
            'top_products' => Product::orderBy('quantity_in_stock', 'desc')->limit(10)->get(),
            'recent_orders' => Order::with('client')->latest()->limit(10)->get(),
                'critical_products' => Product::with('supplier')
                ->whereRaw('quantity_in_stock <= minimum_stock')
                ->limit(10)
                ->get(),
        ];
        
        $pdf = Pdf::loadView('exports.full-report-pdf', $data);
        $pdf->setPaper('a4', 'portrait');
        
        return $pdf->download('rapport_complet_' . now()->format('Y-m-d_His') . '.pdf');
    }
}
