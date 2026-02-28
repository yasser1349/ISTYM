<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Facture {{ $order->order_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            color: #333;
            line-height: 1.4;
        }
        .container {
            padding: 20px;
        }
        .header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
            border-bottom: 2px solid #10b981;
            padding-bottom: 20px;
        }
        .logo {
            font-size: 28px;
            font-weight: bold;
            color: #10b981;
        }
        .logo span {
            color: #333;
        }
        .invoice-info {
            text-align: right;
        }
        .invoice-info h1 {
            font-size: 24px;
            color: #10b981;
            margin-bottom: 5px;
        }
        .invoice-info p {
            margin: 3px 0;
        }
        .parties {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }
        .party {
            width: 48%;
        }
        .party h3 {
            background: #10b981;
            color: white;
            padding: 8px 12px;
            margin-bottom: 10px;
            font-size: 14px;
        }
        .party p {
            margin: 5px 0;
            padding-left: 12px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        th {
            background: #1f2937;
            color: white;
            padding: 12px 8px;
            text-align: left;
            font-size: 11px;
            text-transform: uppercase;
        }
        td {
            padding: 12px 8px;
            border-bottom: 1px solid #e5e7eb;
        }
        tr:nth-child(even) {
            background: #f9fafb;
        }
        .text-right {
            text-align: right;
        }
        .totals {
            width: 300px;
            margin-left: auto;
        }
        .totals table {
            margin-bottom: 0;
        }
        .totals td {
            padding: 8px;
        }
        .totals .total-row {
            background: #10b981;
            color: white;
            font-weight: bold;
            font-size: 14px;
        }
        .footer {
            margin-top: 50px;
            text-align: center;
            color: #6b7280;
            font-size: 10px;
            border-top: 1px solid #e5e7eb;
            padding-top: 20px;
        }
        .status {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .status-delivered { background: #d1fae5; color: #065f46; }
        .status-pending { background: #fef3c7; color: #92400e; }
        .status-processing { background: #dbeafe; color: #1e40af; }
    </style>
</head>
<body>
    <div class="container">
        <table style="width: 100%; border: none; margin-bottom: 30px;">
            <tr>
                <td style="border: none; padding: 0;">
                    <div class="logo">
                        IS<span>TYM</span>
                    </div>
                    <p style="margin-top: 5px; color: #6b7280;">
                        Composants industriels<br>
                        Transmission mécanique, hydraulique, pneumatique
                    </p>
                </td>
                <td style="border: none; padding: 0; text-align: right;">
                    <h1 style="font-size: 24px; color: #10b981; margin-bottom: 5px;">FACTURE</h1>
                    <p><strong>N°:</strong> {{ $order->order_number }}</p>
                    <p><strong>Date:</strong> {{ $order->created_at->format('d/m/Y') }}</p>
                    <p>
                        <span class="status status-{{ $order->status }}">
                            {{ $order->status }}
                        </span>
                    </p>
                </td>
            </tr>
        </table>

        <table style="width: 100%; border: none; margin-bottom: 30px;">
            <tr>
                <td style="border: none; padding: 0; width: 48%; vertical-align: top;">
                    <h3 style="background: #10b981; color: white; padding: 8px 12px; margin-bottom: 10px;">ÉMETTEUR</h3>
                    <p style="padding-left: 12px; margin: 5px 0;"><strong>ISTYM Industrie</strong></p>
                    <p style="padding-left: 12px; margin: 5px 0;">Zone Industrielle</p>
                    <p style="padding-left: 12px; margin: 5px 0;">Casablanca, Maroc</p>
                    <p style="padding-left: 12px; margin: 5px 0;">Tél: +212 5XX XX XX XX</p>
                    <p style="padding-left: 12px; margin: 5px 0;">Email: contact@istym.ma</p>
                </td>
                <td style="border: none; padding: 0; width: 4%;"></td>
                <td style="border: none; padding: 0; width: 48%; vertical-align: top;">
                    <h3 style="background: #1f2937; color: white; padding: 8px 12px; margin-bottom: 10px;">CLIENT</h3>
                    <p style="padding-left: 12px; margin: 5px 0;"><strong>{{ $order->client->company_name }}</strong></p>
                    <p style="padding-left: 12px; margin: 5px 0;">{{ $order->client->contact_name }}</p>
                    @if($order->client->address)
                    <p style="padding-left: 12px; margin: 5px 0;">{{ $order->client->address }}</p>
                    @endif
                    <p style="padding-left: 12px; margin: 5px 0;">{{ $order->client->city ?? '' }}, {{ $order->client->country }}</p>
                    @if($order->client->ice)
                    <p style="padding-left: 12px; margin: 5px 0;"><strong>ICE:</strong> {{ $order->client->ice }}</p>
                    @endif
                </td>
            </tr>
        </table>

        <table>
            <thead>
                <tr>
                    <th style="width: 40%;">Désignation</th>
                    <th style="width: 15%;">Référence</th>
                    <th style="width: 10%;" class="text-right">Qté</th>
                    <th style="width: 15%;" class="text-right">Prix Unit.</th>
                    <th style="width: 10%;" class="text-right">Remise</th>
                    <th style="width: 10%;" class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->items as $item)
                <tr>
                    <td>{{ $item->product->name }}</td>
                    <td>{{ $item->product->reference }}</td>
                    <td class="text-right">{{ $item->quantity }}</td>
                    <td class="text-right">{{ number_format($item->unit_price, 2, ',', ' ') }} MAD</td>
                    <td class="text-right">{{ number_format($item->discount, 2, ',', ' ') }} MAD</td>
                    <td class="text-right">{{ number_format($item->total, 2, ',', ' ') }} MAD</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="totals">
            <table>
                <tr>
                    <td><strong>Sous-total HT</strong></td>
                    <td class="text-right">{{ number_format($order->subtotal, 2, ',', ' ') }} MAD</td>
                </tr>
                @if($order->discount > 0)
                <tr>
                    <td>Remise</td>
                    <td class="text-right">-{{ number_format($order->discount, 2, ',', ' ') }} MAD</td>
                </tr>
                @endif
                <tr>
                    <td>TVA ({{ $order->tax_rate }}%)</td>
                    <td class="text-right">{{ number_format($order->tax_amount, 2, ',', ' ') }} MAD</td>
                </tr>
                <tr class="total-row">
                    <td><strong>TOTAL TTC</strong></td>
                    <td class="text-right"><strong>{{ number_format($order->total, 2, ',', ' ') }} MAD</strong></td>
                </tr>
            </table>
        </div>

        @if($order->notes)
        <div style="margin-top: 30px; padding: 15px; background: #f9fafb; border-left: 4px solid #10b981;">
            <strong>Notes:</strong><br>
            {{ $order->notes }}
        </div>
        @endif

        <div class="footer">
            <p><strong>ISTYM Industrie</strong> - Votre partenaire en solutions de transmission industrielle</p>
            <p>Merci pour votre confiance !</p>
            <p style="margin-top: 10px;">Ce document a été généré automatiquement le {{ now()->format('d/m/Y à H:i') }}</p>
        </div>
    </div>
</body>
</html>
