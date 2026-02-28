<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>{{ $title }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 10px; color: #333; line-height: 1.4; }
        .header {
            background: linear-gradient(135deg, #1f2937 0%, #111827 100%);
            color: white; padding: 25px; margin-bottom: 20px;
        }
        .header h1 { font-size: 24px; margin-bottom: 5px; }
        .header .subtitle { font-size: 12px; opacity: 0.9; }
        .header .date { position: absolute; right: 25px; top: 30px; font-size: 10px; }
        .section { margin: 0 20px 25px; }
        .section-title {
            font-size: 14px; font-weight: bold; color: #1f2937;
            padding-bottom: 8px; border-bottom: 2px solid #10b981;
            margin-bottom: 15px;
        }
        .stats-grid {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }
        .stat-box {
            display: table-cell;
            width: 25%;
            padding: 15px;
            background: #f9fafb;
            border-radius: 8px;
            text-align: center;
            margin: 5px;
        }
        .stat-box .value { font-size: 24px; font-weight: bold; color: #1f2937; }
        .stat-box .label { font-size: 9px; color: #6b7280; text-transform: uppercase; margin-top: 5px; }
        table { width: 100%; border-collapse: collapse; font-size: 9px; }
        th { background: #374151; color: white; padding: 8px 6px; text-align: left; font-weight: 600; }
        td { padding: 6px; border-bottom: 1px solid #e5e7eb; }
        tr:nth-child(even) { background: #f9fafb; }
        .status { padding: 2px 6px; border-radius: 10px; font-size: 8px; font-weight: 600; }
        .status-ok { background: #d1fae5; color: #065f46; }
        .status-warning { background: #fef3c7; color: #92400e; }
        .status-critical { background: #fee2e2; color: #991b1b; }
        .footer {
            position: fixed; bottom: 0; left: 0; right: 0; padding: 10px 20px;
            background: #1f2937; font-size: 8px; color: white; text-align: center;
        }
        .page-break { page-break-after: always; }
        .highlight-box {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white; padding: 15px; border-radius: 8px; margin-bottom: 15px;
        }
        .highlight-box .big { font-size: 28px; font-weight: bold; }
        .highlight-box .small { font-size: 10px; opacity: 0.9; }
    </style>
</head>
<body>
    <div class="header">
        <h1>📊 {{ $title }}</h1>
        <div class="subtitle">{{ $company }} - Période : {{ $period }}</div>
        <div class="date">Généré le {{ $date }}</div>
    </div>

    <!-- Statistiques Clés -->
    <div class="section">
        <div class="section-title">📈 Statistiques Clés</div>
        <table style="border: none;">
            <tr>
                <td style="width: 25%; padding: 10px; background: #f0fdf4; border-left: 4px solid #10b981; border-bottom: none;">
                    <div style="font-size: 20px; font-weight: bold; color: #065f46;">{{ number_format($stats['total_products']) }}</div>
                    <div style="font-size: 9px; color: #6b7280;">Total Produits</div>
                </td>
                <td style="width: 25%; padding: 10px; background: #eff6ff; border-left: 4px solid #3b82f6; border-bottom: none;">
                    <div style="font-size: 20px; font-weight: bold; color: #1e40af;">{{ number_format($stats['total_value'], 0, ',', ' ') }} MAD</div>
                    <div style="font-size: 9px; color: #6b7280;">Valeur du Stock</div>
                </td>
                <td style="width: 25%; padding: 10px; background: #fef2f2; border-left: 4px solid #ef4444; border-bottom: none;">
                    <div style="font-size: 20px; font-weight: bold; color: #991b1b;">{{ $stats['critical_stock'] }}</div>
                    <div style="font-size: 9px; color: #6b7280;">Stock Critique</div>
                </td>
                <td style="width: 25%; padding: 10px; background: #fefce8; border-left: 4px solid #f59e0b; border-bottom: none;">
                    <div style="font-size: 20px; font-weight: bold; color: #92400e;">{{ $stats['active_maintenances'] }}</div>
                    <div style="font-size: 9px; color: #6b7280;">Maintenances Actives</div>
                </td>
            </tr>
        </table>
        <br>
        <table style="border: none;">
            <tr>
                <td style="width: 33%; padding: 10px; background: #f3f4f6; border-left: 4px solid #6b7280; border-bottom: none;">
                    <div style="font-size: 18px; font-weight: bold;">{{ $stats['total_orders'] }}</div>
                    <div style="font-size: 9px; color: #6b7280;">Total Commandes</div>
                </td>
                <td style="width: 33%; padding: 10px; background: #f3f4f6; border-left: 4px solid #8b5cf6; border-bottom: none;">
                    <div style="font-size: 18px; font-weight: bold;">{{ $stats['orders_this_month'] }}</div>
                    <div style="font-size: 9px; color: #6b7280;">Commandes ce mois</div>
                </td>
                <td style="width: 33%; padding: 10px; background: #f3f4f6; border-left: 4px solid #10b981; border-bottom: none;">
                    <div style="font-size: 18px; font-weight: bold;">{{ number_format($stats['revenue_this_month'], 0, ',', ' ') }} MAD</div>
                    <div style="font-size: 9px; color: #6b7280;">CA ce mois</div>
                </td>
            </tr>
        </table>
    </div>

    <!-- Stock Critique -->
    @if($critical_products->count() > 0)
    <div class="section">
        <div class="section-title" style="border-color: #ef4444;">⚠️ Produits en Stock Critique</div>
        <table>
            <thead>
                <tr style="background: #991b1b;">
                    <th>Référence</th>
                    <th>Produit</th>
                    <th>Fournisseur</th>
                    <th style="text-align: center;">Stock</th>
                    <th style="text-align: center;">Minimum</th>
                </tr>
            </thead>
            <tbody>
                @foreach($critical_products as $product)
                <tr>
                    <td><strong>{{ $product->reference }}</strong></td>
                    <td>{{ Str::limit($product->name, 25) }}</td>
                    <td>{{ $product->supplier->name ?? 'N/A' }}</td>
                    <td style="text-align: center; color: #dc2626; font-weight: bold;">{{ $product->quantity }}</td>
                    <td style="text-align: center;">{{ $product->minimum_quantity }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <!-- Top Produits -->
    <div class="section">
        <div class="section-title" style="border-color: #3b82f6;">📦 Top 10 Produits (par quantité)</div>
        <table>
            <thead>
                <tr>
                    <th>Référence</th>
                    <th>Produit</th>
                    <th style="text-align: center;">Quantité</th>
                    <th style="text-align: right;">Valeur</th>
                </tr>
            </thead>
            <tbody>
                @foreach($top_products as $product)
                <tr>
                    <td><strong>{{ $product->reference }}</strong></td>
                    <td>{{ Str::limit($product->name, 30) }}</td>
                    <td style="text-align: center;">{{ $product->quantity }}</td>
                    <td style="text-align: right;">{{ number_format($product->quantity * $product->unit_price, 0, ',', ' ') }} MAD</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Commandes Récentes -->
    <div class="section">
        <div class="section-title" style="border-color: #8b5cf6;">🛒 Dernières Commandes</div>
        <table>
            <thead>
                <tr>
                    <th>N° Commande</th>
                    <th>Date</th>
                    <th>Client</th>
                    <th style="text-align: right;">Montant</th>
                    <th style="text-align: center;">Statut</th>
                </tr>
            </thead>
            <tbody>
                @foreach($recent_orders as $order)
                @php
                    $statusLabel = match($order->status ?? 'pending') {
                        'pending' => 'En attente',
                        'confirmed' => 'Confirmée',
                        'shipped' => 'Expédiée',
                        'delivered' => 'Livrée',
                        default => $order->status,
                    };
                    $statusClass = match($order->status ?? 'pending') {
                        'delivered' => 'ok',
                        'pending' => 'warning',
                        default => 'warning',
                    };
                @endphp
                <tr>
                    <td><strong>{{ $order->reference ?? 'CMD-' . str_pad($order->id, 5, '0', STR_PAD_LEFT) }}</strong></td>
                    <td>{{ $order->created_at->format('d/m/Y') }}</td>
                    <td>{{ $order->client->company ?? $order->client->name ?? 'N/A' }}</td>
                    <td style="text-align: right;">{{ number_format($order->total ?? 0, 0, ',', ' ') }} MAD</td>
                    <td style="text-align: center;">
                        <span class="status status-{{ $statusClass }}">{{ $statusLabel }}</span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="footer">
        {{ $company }} - Rapport Complet - Généré automatiquement le {{ $date }}
    </div>
</body>
</html>
