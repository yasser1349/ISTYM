<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmation de Commande - ISTYM</title>
</head>
<body style="margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f4f4f5;">
    <table width="100%" cellpadding="0" cellspacing="0" style="max-width: 600px; margin: 0 auto; background-color: #ffffff;">
        <!-- Header -->
        <tr>
            <td style="background: linear-gradient(135deg, #0a0a0a 0%, #1a1a1a 100%); padding: 30px 40px; text-align: center;">
                <h1 style="margin: 0; color: #10b981; font-size: 28px; font-weight: 700;">ISTYM</h1>
                <p style="margin: 5px 0 0; color: #9ca3af; font-size: 14px;">Système ERP</p>
            </td>
        </tr>

        <!-- Success Banner -->
        <tr>
            <td style="padding: 0;">
                <div style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); padding: 30px 40px; text-align: center;">
                    <span style="font-size: 48px;">✅</span>
                    <h2 style="margin: 15px 0 5px; color: #1f2937; font-size: 24px; font-weight: 700;">Commande Confirmée</h2>
                    <p style="margin: 0; color: rgba(255,255,255,0.9); font-size: 16px;">Merci pour votre confiance !</p>
                </div>
            </td>
        </tr>

        <!-- Content -->
        <tr>
            <td style="padding: 40px;">
                <p style="margin: 0 0 20px; color: #374151; font-size: 16px; line-height: 1.6;">
                    Bonjour <strong>{{ $order->client->contact_name ?? $order->client->company }}</strong>,
                </p>
                <p style="margin: 0 0 30px; color: #374151; font-size: 16px; line-height: 1.6;">
                    Nous avons bien reçu votre commande et nous la traitons actuellement. Voici le récapitulatif :
                </p>

                <!-- Order Info Card -->
                <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f0fdf4; border-radius: 12px; margin-bottom: 30px;">
                    <tr>
                        <td style="padding: 25px;">
                            <table width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td width="50%">
                                        <span style="color: #6b7280; font-size: 12px; text-transform: uppercase;">Numéro de commande</span>
                                        <p style="margin: 5px 0 0; color: #10b981; font-size: 20px; font-weight: 700;">#{{ $order->order_number }}</p>
                                    </td>
                                    <td width="50%" style="text-align: right;">
                                        <span style="color: #6b7280; font-size: 12px; text-transform: uppercase;">Date</span>
                                        <p style="margin: 5px 0 0; color: #374151; font-size: 16px; font-weight: 600;">{{ $order->created_at->format('d/m/Y à H:i') }}</p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>

                <!-- Order Items -->
                <h3 style="margin: 0 0 15px; color: #111827; font-size: 16px; font-weight: 600;">Détail de la commande</h3>
                <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom: 20px; border: 1px solid #e5e7eb; border-radius: 12px; overflow: hidden;">
                    <thead>
                        <tr style="background-color: #f9fafb;">
                            <th style="padding: 12px 15px; text-align: left; color: #6b7280; font-size: 12px; text-transform: uppercase; font-weight: 600;">Produit</th>
                            <th style="padding: 12px 15px; text-align: center; color: #6b7280; font-size: 12px; text-transform: uppercase; font-weight: 600;">Qté</th>
                            <th style="padding: 12px 15px; text-align: right; color: #6b7280; font-size: 12px; text-transform: uppercase; font-weight: 600;">Prix</th>
                            <th style="padding: 12px 15px; text-align: right; color: #6b7280; font-size: 12px; text-transform: uppercase; font-weight: 600;">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($order->items as $item)
                        <tr style="border-top: 1px solid #e5e7eb;">
                            <td style="padding: 15px;">
                                <p style="margin: 0; color: #111827; font-size: 14px; font-weight: 600;">{{ $item->product->name ?? 'Produit' }}</p>
                                <p style="margin: 3px 0 0; color: #9ca3af; font-size: 12px;">{{ $item->product->reference ?? '' }}</p>
                            </td>
                            <td style="padding: 15px; text-align: center; color: #374151; font-size: 14px;">{{ $item->quantity }}</td>
                            <td style="padding: 15px; text-align: right; color: #374151; font-size: 14px;">{{ number_format($item->unit_price, 2) }} MAD</td>
                            <td style="padding: 15px; text-align: right; color: #374151; font-size: 14px; font-weight: 600;">{{ number_format($item->quantity * $item->unit_price, 2) }} MAD</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>

                <!-- Order Totals -->
                <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom: 30px;">
                    <tr>
                        <td width="60%"></td>
                        <td width="40%">
                            <table width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td style="padding: 8px 0; color: #6b7280; font-size: 14px;">Sous-total</td>
                                    <td style="padding: 8px 0; text-align: right; color: #374151; font-size: 14px;">{{ number_format($order->subtotal ?? $order->total / 1.2, 2) }} MAD</td>
                                </tr>
                                <tr>
                                    <td style="padding: 8px 0; color: #6b7280; font-size: 14px;">TVA (20%)</td>
                                    <td style="padding: 8px 0; text-align: right; color: #374151; font-size: 14px;">{{ number_format(($order->total ?? 0) - ($order->subtotal ?? $order->total / 1.2), 2) }} MAD</td>
                                </tr>
                                @if($order->shipping_cost > 0)
                                <tr>
                                    <td style="padding: 8px 0; color: #6b7280; font-size: 14px;">Livraison</td>
                                    <td style="padding: 8px 0; text-align: right; color: #374151; font-size: 14px;">{{ number_format($order->shipping_cost, 2) }} MAD</td>
                                </tr>
                                @endif
                                <tr style="border-top: 2px solid #e5e7eb;">
                                    <td style="padding: 12px 0; color: #111827; font-size: 16px; font-weight: 700;">Total</td>
                                    <td style="padding: 12px 0; text-align: right; color: #10b981; font-size: 20px; font-weight: 700;">{{ number_format($order->total, 2) }} MAD</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>

                <!-- Shipping Address -->
                @if($order->shipping_address || $order->client->address)
                <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom: 30px;">
                    <tr>
                        <td style="padding: 20px; background-color: #f9fafb; border-radius: 12px;">
                            <h4 style="margin: 0 0 10px; color: #111827; font-size: 14px; font-weight: 600;">📍 Adresse de livraison</h4>
                            <p style="margin: 0; color: #374151; font-size: 14px; line-height: 1.6;">
                                {{ $order->shipping_address ?? $order->client->address }}<br>
                                @if($order->client->city){{ $order->client->city }}, @endif
                                @if($order->client->postal_code){{ $order->client->postal_code }}@endif
                            </p>
                        </td>
                    </tr>
                </table>
                @endif

                <!-- Next Steps -->
                <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom: 30px;">
                    <tr>
                        <td style="padding: 20px; background-color: #eff6ff; border-radius: 12px; border: 1px solid #bfdbfe;">
                            <h4 style="margin: 0 0 15px; color: #1e40af; font-size: 14px; font-weight: 600;">📋 Prochaines étapes</h4>
                            <table cellpadding="0" cellspacing="0">
                                <tr>
                                    <td style="padding: 5px 10px 5px 0; vertical-align: top;">
                                        <span style="display: inline-block; width: 24px; height: 24px; background-color: #10b981; color: white; border-radius: 50%; text-align: center; line-height: 24px; font-size: 12px; font-weight: 700;">1</span>
                                    </td>
                                    <td style="padding: 5px 0; color: #374151; font-size: 14px;">Votre commande est en cours de préparation</td>
                                </tr>
                                <tr>
                                    <td style="padding: 5px 10px 5px 0; vertical-align: top;">
                                        <span style="display: inline-block; width: 24px; height: 24px; background-color: #d1d5db; color: white; border-radius: 50%; text-align: center; line-height: 24px; font-size: 12px; font-weight: 700;">2</span>
                                    </td>
                                    <td style="padding: 5px 0; color: #374151; font-size: 14px;">Vous recevrez un email lors de l'expédition</td>
                                </tr>
                                <tr>
                                    <td style="padding: 5px 10px 5px 0; vertical-align: top;">
                                        <span style="display: inline-block; width: 24px; height: 24px; background-color: #d1d5db; color: white; border-radius: 50%; text-align: center; line-height: 24px; font-size: 12px; font-weight: 700;">3</span>
                                    </td>
                                    <td style="padding: 5px 0; color: #374151; font-size: 14px;">Livraison à l'adresse indiquée</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>

                <!-- Contact -->
                <p style="margin: 0; color: #6b7280; font-size: 14px; line-height: 1.6; text-align: center;">
                    Des questions ? Contactez-nous à <a href="mailto:contact@istym.ma" style="color: #10b981; text-decoration: none;">contact@istym.ma</a>
                </p>
            </td>
        </tr>

        <!-- Footer -->
        <tr>
            <td style="background-color: #f9fafb; padding: 30px 40px; text-align: center; border-top: 1px solid #e5e7eb;">
                <p style="margin: 0 0 10px; color: #6b7280; font-size: 14px;">
                    Merci d'avoir choisi ISTYM !
                </p>
                <p style="margin: 0; color: #9ca3af; font-size: 12px;">
                    © {{ date('Y') }} ISTYM - Zone Industrielle, Casablanca, Maroc
                </p>
            </td>
        </tr>
    </table>
</body>
</html>
