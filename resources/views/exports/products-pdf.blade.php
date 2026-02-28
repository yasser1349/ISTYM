<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>{{ $title }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 10px;
            color: #333;
            line-height: 1.4;
        }
        .header {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            padding: 20px;
            margin-bottom: 20px;
        }
        .header h1 {
            font-size: 22px;
            margin-bottom: 5px;
        }
        .header .subtitle {
            font-size: 11px;
            opacity: 0.9;
        }
        .header .date {
            position: absolute;
            right: 20px;
            top: 25px;
            font-size: 10px;
        }
        .summary {
            display: flex;
            margin-bottom: 20px;
            padding: 0 20px;
        }
        .summary-box {
            background: #f3f4f6;
            padding: 12px 20px;
            border-radius: 8px;
            margin-right: 15px;
            text-align: center;
            border-left: 4px solid #10b981;
        }
        .summary-box .label {
            font-size: 9px;
            color: #6b7280;
            text-transform: uppercase;
        }
        .summary-box .value {
            font-size: 18px;
            font-weight: bold;
            color: #111827;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 0 20px;
            font-size: 9px;
        }
        th {
            background: #1f2937;
            color: white;
            padding: 10px 8px;
            text-align: left;
            font-weight: 600;
        }
        td {
            padding: 8px;
            border-bottom: 1px solid #e5e7eb;
        }
        tr:nth-child(even) {
            background: #f9fafb;
        }
        .status {
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 8px;
            font-weight: 600;
        }
        .status-ok {
            background: #d1fae5;
            color: #065f46;
        }
        .status-warning {
            background: #fef3c7;
            color: #92400e;
        }
        .status-critical {
            background: #fee2e2;
            color: #991b1b;
        }
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            padding: 10px 20px;
            background: #f3f4f6;
            font-size: 8px;
            color: #6b7280;
            text-align: center;
            border-top: 1px solid #e5e7eb;
        }
        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $title }}</h1>
        <div class="subtitle">{{ $company }}</div>
        <div class="date">Généré le {{ $date }}</div>
    </div>

    <table style="width: calc(100% - 40px);">
        <tr>
            <td style="border: none; padding: 10px; background: #f3f4f6; border-left: 4px solid #10b981;">
                <span style="font-size: 9px; color: #6b7280;">TOTAL PRODUITS</span><br>
                <strong style="font-size: 16px;">{{ $total }}</strong>
            </td>
            <td style="border: none; padding: 10px; background: #f3f4f6; border-left: 4px solid #3b82f6;">
                <span style="font-size: 9px; color: #6b7280;">VALEUR TOTALE</span><br>
                <strong style="font-size: 16px;">{{ number_format($total_value, 2, ',', ' ') }} MAD</strong>
            </td>
        </tr>
    </table>

    <br>

    <table style="width: calc(100% - 40px);">
        <thead>
            <tr>
                <th>Réf.</th>
                <th>Nom du Produit</th>
                <th>Catégorie</th>
                <th>Fournisseur</th>
                <th style="text-align: center;">Qté</th>
                <th style="text-align: center;">Min</th>
                <th style="text-align: right;">Prix Unit.</th>
                <th style="text-align: right;">Valeur</th>
                <th style="text-align: center;">Statut</th>
            </tr>
        </thead>
        <tbody>
            @foreach($products as $product)
            @php
                $percentage = $product->minimum_quantity > 0 ? ($product->quantity / $product->minimum_quantity) * 100 : 100;
                $status = $percentage <= 100 ? 'critical' : ($percentage <= 150 ? 'warning' : 'ok');
                $statusLabel = $percentage <= 100 ? 'Critique' : ($percentage <= 150 ? 'Bas' : 'OK');
            @endphp
            <tr>
                <td><strong>{{ $product->reference }}</strong></td>
                <td>{{ Str::limit($product->name, 30) }}</td>
                <td>{{ $product->category->name ?? 'N/A' }}</td>
                <td>{{ $product->supplier->name ?? 'N/A' }}</td>
                <td style="text-align: center;">{{ $product->quantity }}</td>
                <td style="text-align: center;">{{ $product->minimum_quantity }}</td>
                <td style="text-align: right;">{{ number_format($product->unit_price, 2, ',', ' ') }}</td>
                <td style="text-align: right;">{{ number_format($product->quantity * $product->unit_price, 2, ',', ' ') }}</td>
                <td style="text-align: center;">
                    <span class="status status-{{ $status }}">{{ $statusLabel }}</span>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        {{ $company }} - Document généré automatiquement le {{ $date }} - Page 1
    </div>
</body>
</html>
