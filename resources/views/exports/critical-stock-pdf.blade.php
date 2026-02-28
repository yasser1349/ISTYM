<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>{{ $title }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 10px; color: #333; line-height: 1.4; }
        .header {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white; padding: 20px; margin-bottom: 20px;
        }
        .header h1 { font-size: 22px; margin-bottom: 5px; }
        .header .subtitle { font-size: 11px; opacity: 0.9; }
        .header .date { position: absolute; right: 20px; top: 25px; font-size: 10px; }
        .alert-banner {
            background: #fef2f2;
            border: 2px solid #ef4444;
            border-radius: 8px;
            padding: 15px 20px;
            margin: 0 20px 20px;
            display: flex;
            align-items: center;
        }
        .alert-icon {
            width: 40px;
            height: 40px;
            background: #ef4444;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            color: white;
            font-size: 20px;
            font-weight: bold;
        }
        .alert-text h3 { color: #991b1b; font-size: 14px; margin-bottom: 3px; }
        .alert-text p { color: #7f1d1d; font-size: 11px; }
        table { width: calc(100% - 40px); border-collapse: collapse; margin: 0 20px; font-size: 9px; }
        th { background: #991b1b; color: white; padding: 10px 8px; text-align: left; font-weight: 600; }
        td { padding: 8px; border-bottom: 1px solid #fecaca; }
        tr:nth-child(even) { background: #fef2f2; }
        .progress-bar {
            width: 60px;
            height: 8px;
            background: #fee2e2;
            border-radius: 4px;
            overflow: hidden;
        }
        .progress-fill {
            height: 100%;
            border-radius: 4px;
        }
        .footer {
            position: fixed; bottom: 0; left: 0; right: 0; padding: 10px 20px;
            background: #fef2f2; font-size: 8px; color: #991b1b; text-align: center;
            border-top: 2px solid #ef4444;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>⚠️ {{ $title }}</h1>
        <div class="subtitle">{{ $company }} - ALERTE {{ $alert_level }}</div>
        <div class="date">Généré le {{ $date }}</div>
    </div>

    <div class="alert-banner">
        <div class="alert-icon">!</div>
        <div class="alert-text">
            <h3>Attention : {{ $total }} produit(s) en stock critique</h3>
            <p>Ces produits nécessitent un réapprovisionnement urgent pour éviter les ruptures de stock.</p>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Réf.</th>
                <th>Nom du Produit</th>
                <th>Fournisseur</th>
                <th style="text-align: center;">Stock Actuel</th>
                <th style="text-align: center;">Stock Min</th>
                <th style="text-align: center;">Manquant</th>
                <th>Niveau</th>
            </tr>
        </thead>
        <tbody>
            @foreach($products as $product)
            @php
                $missing = max(0, $product->minimum_quantity - $product->quantity);
                $percentage = $product->minimum_quantity > 0 ? min(100, ($product->quantity / $product->minimum_quantity) * 100) : 0;
                $color = $percentage <= 25 ? '#dc2626' : ($percentage <= 50 ? '#f59e0b' : '#f97316');
            @endphp
            <tr>
                <td><strong>{{ $product->reference }}</strong></td>
                <td>{{ $product->name }}</td>
                <td>{{ $product->supplier->name ?? 'N/A' }}</td>
                <td style="text-align: center; color: #dc2626; font-weight: bold;">{{ $product->quantity }}</td>
                <td style="text-align: center;">{{ $product->minimum_quantity }}</td>
                <td style="text-align: center; color: #dc2626; font-weight: bold;">-{{ $missing }}</td>
                <td>
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: {{ $percentage }}%; background: {{ $color }};"></div>
                    </div>
                    <span style="font-size: 8px; color: #991b1b;">{{ round($percentage) }}%</span>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        ⚠️ RAPPORT URGENT - {{ $company }} - Généré le {{ $date }} - Action requise immédiatement
    </div>
</body>
</html>
