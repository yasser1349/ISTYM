<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>{{ $title }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 10px; color: #333; line-height: 1.4; }
        .header {
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            color: white; padding: 20px; margin-bottom: 20px;
        }
        .header h1 { font-size: 22px; margin-bottom: 5px; }
        .header .subtitle { font-size: 11px; opacity: 0.9; }
        .header .date { position: absolute; right: 20px; top: 25px; font-size: 10px; }
        table { width: calc(100% - 40px); border-collapse: collapse; margin: 0 20px; font-size: 9px; }
        th { background: #1f2937; color: white; padding: 10px 8px; text-align: left; font-weight: 600; }
        td { padding: 8px; border-bottom: 1px solid #e5e7eb; }
        tr:nth-child(even) { background: #f9fafb; }
        .status { padding: 3px 8px; border-radius: 12px; font-size: 8px; font-weight: 600; }
        .status-pending { background: #fef3c7; color: #92400e; }
        .status-confirmed { background: #dbeafe; color: #1e40af; }
        .status-shipped { background: #e0e7ff; color: #4338ca; }
        .status-delivered { background: #d1fae5; color: #065f46; }
        .status-cancelled { background: #fee2e2; color: #991b1b; }
        .payment-paid { background: #d1fae5; color: #065f46; }
        .payment-pending { background: #fef3c7; color: #92400e; }
        .footer {
            position: fixed; bottom: 0; left: 0; right: 0; padding: 10px 20px;
            background: #f3f4f6; font-size: 8px; color: #6b7280; text-align: center;
            border-top: 1px solid #e5e7eb;
        }
        .summary-row td { border: none; padding: 10px; background: #f3f4f6; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $title }}</h1>
        <div class="subtitle">{{ $company }}</div>
        <div class="date">Généré le {{ $date }}</div>
    </div>

    <table>
        <tr class="summary-row">
            <td style="border-left: 4px solid #3b82f6;">
                <span style="font-size: 9px; color: #6b7280;">TOTAL COMMANDES</span><br>
                <strong style="font-size: 16px;">{{ $total }}</strong>
            </td>
            <td style="border-left: 4px solid #10b981;">
                <span style="font-size: 9px; color: #6b7280;">MONTANT TOTAL</span><br>
                <strong style="font-size: 16px;">{{ number_format($total_amount, 2, ',', ' ') }} MAD</strong>
            </td>
        </tr>
    </table>

    <br>

    <table>
        <thead>
            <tr>
                <th>N° Commande</th>
                <th>Date</th>
                <th>Client</th>
                <th>Articles</th>
                <th style="text-align: right;">Total</th>
                <th style="text-align: center;">Statut</th>
                <th style="text-align: center;">Paiement</th>
            </tr>
        </thead>
        <tbody>
            @foreach($orders as $order)
            @php
                $statusClass = match($order->status) {
                    'pending' => 'pending',
                    'confirmed' => 'confirmed',
                    'shipped' => 'shipped',
                    'delivered' => 'delivered',
                    'cancelled' => 'cancelled',
                    default => 'pending',
                };
                $statusLabel = match($order->status) {
                    'pending' => 'En attente',
                    'confirmed' => 'Confirmée',
                    'shipped' => 'Expédiée',
                    'delivered' => 'Livrée',
                    'cancelled' => 'Annulée',
                    default => $order->status,
                };
            @endphp
            <tr>
                <td><strong>{{ $order->reference ?? 'CMD-' . str_pad($order->id, 5, '0', STR_PAD_LEFT) }}</strong></td>
                <td>{{ $order->created_at->format('d/m/Y') }}</td>
                <td>{{ $order->client->company ?? $order->client->name ?? 'N/A' }}</td>
                <td>{{ $order->items->count() }} article(s)</td>
                <td style="text-align: right;"><strong>{{ number_format($order->total, 2, ',', ' ') }} MAD</strong></td>
                <td style="text-align: center;">
                    <span class="status status-{{ $statusClass }}">{{ $statusLabel }}</span>
                </td>
                <td style="text-align: center;">
                    <span class="status payment-{{ $order->payment_status === 'paid' ? 'paid' : 'pending' }}">
                        {{ $order->payment_status === 'paid' ? 'Payé' : 'En attente' }}
                    </span>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        {{ $company }} - Document généré automatiquement le {{ $date }}
    </div>
</body>
</html>
