<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alerte Stock - ISTYM</title>
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

        <!-- Alert Banner -->
        <tr>
            <td style="padding: 0;">
                @if($alertType === 'out_of_stock')
                <div style="background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); padding: 20px 40px; text-align: center;">
                    <span style="font-size: 32px;">❌</span>
                    <h2 style="margin: 10px 0 0; color: #1f2937; font-size: 20px;">RUPTURE DE STOCK</h2>
                </div>
                @elseif($alertType === 'critical')
                <div style="background: linear-gradient(135deg, #f97316 0%, #ea580c 100%); padding: 20px 40px; text-align: center;">
                    <span style="font-size: 32px;">🔴</span>
                    <h2 style="margin: 10px 0 0; color: #1f2937; font-size: 20px;">STOCK CRITIQUE</h2>
                </div>
                @else
                <div style="background: linear-gradient(135deg, #eab308 0%, #ca8a04 100%); padding: 20px 40px; text-align: center;">
                    <span style="font-size: 32px;">⚠️</span>
                    <h2 style="margin: 10px 0 0; color: #1f2937; font-size: 20px;">STOCK BAS</h2>
                </div>
                @endif
            </td>
        </tr>

        <!-- Content -->
        <tr>
            <td style="padding: 40px;">
                <p style="margin: 0 0 20px; color: #374151; font-size: 16px; line-height: 1.6;">
                    Bonjour,
                </p>
                <p style="margin: 0 0 30px; color: #374151; font-size: 16px; line-height: 1.6;">
                    @if($alertType === 'out_of_stock')
                    Le produit suivant est maintenant <strong style="color: #ef4444;">en rupture de stock</strong> et nécessite une action immédiate :
                    @elseif($alertType === 'critical')
                    Le stock du produit suivant a atteint un <strong style="color: #f97316;">niveau critique</strong> :
                    @else
                    Le stock du produit suivant est <strong style="color: #eab308;">bas</strong> et devrait être réapprovisionné :
                    @endif
                </p>

                <!-- Product Card -->
                <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f9fafb; border-radius: 12px; overflow: hidden; margin-bottom: 30px;">
                    <tr>
                        <td style="padding: 25px;">
                            <table width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td width="80" style="vertical-align: top;">
                                        <div style="width: 70px; height: 70px; background: linear-gradient(135deg, #10b981 0%, #059669 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                                            <span style="font-size: 28px; display: block; text-align: center; line-height: 70px;">📦</span>
                                        </div>
                                    </td>
                                    <td style="vertical-align: top; padding-left: 15px;">
                                        <h3 style="margin: 0 0 5px; color: #111827; font-size: 18px; font-weight: 600;">{{ $product->name }}</h3>
                                        <p style="margin: 0 0 10px; color: #6b7280; font-size: 14px;">Réf: {{ $product->reference }}</p>
                                        <table cellpadding="0" cellspacing="0">
                                            <tr>
                                                <td style="padding-right: 20px;">
                                                    <span style="color: #9ca3af; font-size: 12px; text-transform: uppercase;">Stock actuel</span>
                                                    <p style="margin: 2px 0 0; color: {{ $product->quantity == 0 ? '#ef4444' : ($product->quantity <= 5 ? '#f97316' : '#eab308') }}; font-size: 24px; font-weight: 700;">{{ $product->quantity }}</p>
                                                </td>
                                                <td style="padding-right: 20px;">
                                                    <span style="color: #9ca3af; font-size: 12px; text-transform: uppercase;">Seuil minimum</span>
                                                    <p style="margin: 2px 0 0; color: #374151; font-size: 24px; font-weight: 700;">{{ $product->min_quantity ?? 10 }}</p>
                                                </td>
                                                <td>
                                                    <span style="color: #9ca3af; font-size: 12px; text-transform: uppercase;">Prix unitaire</span>
                                                    <p style="margin: 2px 0 0; color: #374151; font-size: 24px; font-weight: 700;">{{ number_format($product->price, 2) }} <span style="font-size: 14px; font-weight: 400;">MAD</span></p>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>

                <!-- Additional Info -->
                <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom: 30px;">
                    <tr>
                        <td style="padding: 15px 20px; background-color: #fef3c7; border-left: 4px solid #f59e0b; border-radius: 0 8px 8px 0;">
                            <p style="margin: 0; color: #92400e; font-size: 14px;">
                                <strong>💡 Action recommandée :</strong> Passez une commande fournisseur pour réapprovisionner ce produit dès que possible.
                            </p>
                        </td>
                    </tr>
                </table>

                @if($product->supplier)
                <!-- Supplier Info -->
                <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom: 30px;">
                    <tr>
                        <td style="padding: 20px; background-color: #f0fdf4; border-radius: 8px; border: 1px solid #bbf7d0;">
                            <h4 style="margin: 0 0 10px; color: #166534; font-size: 14px; text-transform: uppercase;">Fournisseur</h4>
                            <p style="margin: 0; color: #374151; font-size: 16px; font-weight: 600;">{{ $product->supplier->name }}</p>
                            @if($product->supplier->email)
                            <p style="margin: 5px 0 0; color: #6b7280; font-size: 14px;">📧 {{ $product->supplier->email }}</p>
                            @endif
                            @if($product->supplier->phone)
                            <p style="margin: 5px 0 0; color: #6b7280; font-size: 14px;">📞 {{ $product->supplier->phone }}</p>
                            @endif
                        </td>
                    </tr>
                </table>
                @endif

                <!-- CTA Button -->
                <table width="100%" cellpadding="0" cellspacing="0">
                    <tr>
                        <td style="text-align: center;">
                            <a href="{{ config('app.frontend_url', 'http://localhost:5173') }}/inventory" style="display: inline-block; padding: 14px 32px; background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; text-decoration: none; font-size: 16px; font-weight: 600; border-radius: 10px;">
                                Gérer l'inventaire →
                            </a>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>

        <!-- Footer -->
        <tr>
            <td style="background-color: #f9fafb; padding: 30px 40px; text-align: center; border-top: 1px solid #e5e7eb;">
                <p style="margin: 0 0 10px; color: #6b7280; font-size: 14px;">
                    Cet email a été envoyé automatiquement par le système ISTYM ERP.
                </p>
                <p style="margin: 0; color: #9ca3af; font-size: 12px;">
                    © {{ date('Y') }} ISTYM - Zone Industrielle, Casablanca, Maroc
                </p>
            </td>
        </tr>
    </table>
</body>
</html>
