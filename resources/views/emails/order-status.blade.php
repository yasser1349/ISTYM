<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mise à jour de Commande - ISTYM</title>
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

        <!-- Status Banner -->
        <tr>
            <td style="padding: 0;">
                @php
                    $statusConfig = [
                        'confirmed' => ['color' => '#10b981', 'gradient' => 'linear-gradient(135deg, #10b981 0%, #059669 100%)', 'emoji' => '✅', 'label' => 'Commande Confirmée', 'message' => 'Votre commande a été confirmée et sera bientôt préparée.'],
                        'shipped' => ['color' => '#3b82f6', 'gradient' => 'linear-gradient(135deg, #3b82f6 0%, #2563eb 100%)', 'emoji' => '🚚', 'label' => 'Commande Expédiée', 'message' => 'Votre commande est en route vers vous !'],
                        'delivered' => ['color' => '#8b5cf6', 'gradient' => 'linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%)', 'emoji' => '📦', 'label' => 'Commande Livrée', 'message' => 'Votre commande a été livrée avec succès.'],
                        'cancelled' => ['color' => '#ef4444', 'gradient' => 'linear-gradient(135deg, #ef4444 0%, #dc2626 100%)', 'emoji' => '❌', 'label' => 'Commande Annulée', 'message' => 'Votre commande a été annulée.'],
                    ];
                    $config = $statusConfig[$newStatus] ?? ['color' => '#6b7280', 'gradient' => 'linear-gradient(135deg, #6b7280 0%, #4b5563 100%)', 'emoji' => '📋', 'label' => 'Mise à jour', 'message' => 'Le statut de votre commande a été mis à jour.'];
                @endphp
                <div style="background: {{ $config['gradient'] }}; padding: 30px 40px; text-align: center;">
                    <span style="font-size: 48px;">{{ $config['emoji'] }}</span>
                    <h2 style="margin: 15px 0 5px; color: #1f2937; font-size: 24px; font-weight: 700;">{{ $config['label'] }}</h2>
                    <p style="margin: 0; color: rgba(255,255,255,0.9); font-size: 16px;">{{ $config['message'] }}</p>
                </div>
            </td>
        </tr>

        <!-- Content -->
        <tr>
            <td style="padding: 40px;">
                <p style="margin: 0 0 20px; color: #374151; font-size: 16px; line-height: 1.6;">
                    Bonjour <strong>{{ $order->client->contact_name ?? $order->client->company }}</strong>,
                </p>

                <!-- Order Info Card -->
                <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f9fafb; border-radius: 12px; margin-bottom: 30px;">
                    <tr>
                        <td style="padding: 25px;">
                            <table width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td>
                                        <span style="color: #6b7280; font-size: 12px; text-transform: uppercase;">Commande</span>
                                        <p style="margin: 5px 0 0; color: #111827; font-size: 20px; font-weight: 700;">#{{ $order->order_number }}</p>
                                    </td>
                                    <td style="text-align: right;">
                                        <span style="color: #6b7280; font-size: 12px; text-transform: uppercase;">Total</span>
                                        <p style="margin: 5px 0 0; color: #10b981; font-size: 20px; font-weight: 700;">{{ number_format($order->total, 2) }} MAD</p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>

                <!-- Status Timeline -->
                <h3 style="margin: 0 0 20px; color: #111827; font-size: 16px; font-weight: 600;">Suivi de votre commande</h3>
                <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom: 30px;">
                    @php
                        $statuses = ['pending', 'confirmed', 'shipped', 'delivered'];
                        $statusLabels = ['En attente', 'Confirmée', 'Expédiée', 'Livrée'];
                        $currentIndex = array_search($newStatus, $statuses);
                        if ($newStatus === 'cancelled') $currentIndex = -1;
                    @endphp
                    @foreach($statuses as $index => $status)
                    <tr>
                        <td style="padding: 0 0 {{ $index < 3 ? '20px' : '0' }} 0;">
                            <table cellpadding="0" cellspacing="0">
                                <tr>
                                    <td style="vertical-align: top; width: 40px;">
                                        <div style="width: 32px; height: 32px; border-radius: 50%; background-color: {{ $index <= $currentIndex ? '#10b981' : '#e5e7eb' }}; display: flex; align-items: center; justify-content: center;">
                                            @if($index <= $currentIndex)
                                            <span style="color: white; font-size: 14px; display: block; text-align: center; line-height: 32px;">✓</span>
                                            @else
                                            <span style="color: #9ca3af; font-size: 14px; display: block; text-align: center; line-height: 32px;">{{ $index + 1 }}</span>
                                            @endif
                                        </div>
                                        @if($index < 3)
                                        <div style="width: 2px; height: 20px; background-color: {{ $index < $currentIndex ? '#10b981' : '#e5e7eb' }}; margin: 4px auto;"></div>
                                        @endif
                                    </td>
                                    <td style="padding-left: 15px; vertical-align: top;">
                                        <p style="margin: 0; color: {{ $index <= $currentIndex ? '#111827' : '#9ca3af' }}; font-size: 14px; font-weight: {{ $index == $currentIndex ? '700' : '500' }};">
                                            {{ $statusLabels[$index] }}
                                            @if($index == $currentIndex)
                                            <span style="display: inline-block; padding: 2px 8px; background-color: {{ $config['color'] }}20; color: {{ $config['color'] }}; font-size: 11px; border-radius: 10px; margin-left: 8px;">Actuel</span>
                                            @endif
                                        </p>
                                        @if($index == $currentIndex && $newStatus !== 'cancelled')
                                        <p style="margin: 4px 0 0; color: #6b7280; font-size: 13px;">{{ now()->format('d/m/Y à H:i') }}</p>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    @endforeach
                </table>

                @if($newStatus === 'shipped')
                <!-- Tracking Info -->
                <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom: 30px;">
                    <tr>
                        <td style="padding: 20px; background-color: #eff6ff; border-radius: 12px; border: 1px solid #bfdbfe;">
                            <h4 style="margin: 0 0 10px; color: #1e40af; font-size: 14px; font-weight: 600;">🚚 Informations de livraison</h4>
                            <p style="margin: 0; color: #374151; font-size: 14px; line-height: 1.6;">
                                Votre commande est en cours de livraison. Vous serez contacté par notre transporteur pour convenir d'un créneau de livraison.
                            </p>
                        </td>
                    </tr>
                </table>
                @endif

                @if($newStatus === 'delivered')
                <!-- Feedback Request -->
                <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom: 30px;">
                    <tr>
                        <td style="padding: 20px; background-color: #f0fdf4; border-radius: 12px; border: 1px solid #bbf7d0;">
                            <h4 style="margin: 0 0 10px; color: #166534; font-size: 14px; font-weight: 600;">🌟 Votre avis compte !</h4>
                            <p style="margin: 0; color: #374151; font-size: 14px; line-height: 1.6;">
                                Nous espérons que vous êtes satisfait de votre commande. N'hésitez pas à nous faire part de vos commentaires.
                            </p>
                        </td>
                    </tr>
                </table>
                @endif

                @if($newStatus === 'cancelled')
                <!-- Cancellation Info -->
                <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom: 30px;">
                    <tr>
                        <td style="padding: 20px; background-color: #fef2f2; border-radius: 12px; border: 1px solid #fecaca;">
                            <h4 style="margin: 0 0 10px; color: #991b1b; font-size: 14px; font-weight: 600;">ℹ️ Annulation de commande</h4>
                            <p style="margin: 0; color: #374151; font-size: 14px; line-height: 1.6;">
                                Votre commande a été annulée. Si un paiement a été effectué, le remboursement sera traité dans les plus brefs délais.
                            </p>
                        </td>
                    </tr>
                </table>
                @endif

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
