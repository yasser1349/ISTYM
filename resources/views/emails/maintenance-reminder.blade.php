<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rappel de Maintenance - ISTYM</title>
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
                @if($daysUntil <= 1)
                <div style="background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); padding: 25px 40px; text-align: center;">
                    <span style="font-size: 40px;">🔴</span>
                    <h2 style="margin: 10px 0 5px; color: #1f2937; font-size: 22px; font-weight: 700;">Maintenance Urgente</h2>
                    <p style="margin: 0; color: rgba(255,255,255,0.9); font-size: 16px;">
                        @if($daysUntil == 0)
                        Prévue aujourd'hui !
                        @else
                        Prévue demain !
                        @endif
                    </p>
                </div>
                @elseif($daysUntil <= 3)
                <div style="background: linear-gradient(135deg, #f97316 0%, #ea580c 100%); padding: 25px 40px; text-align: center;">
                    <span style="font-size: 40px;">🟠</span>
                    <h2 style="margin: 10px 0 5px; color: #1f2937; font-size: 22px; font-weight: 700;">Rappel de Maintenance</h2>
                    <p style="margin: 0; color: rgba(255,255,255,0.9); font-size: 16px;">Dans {{ $daysUntil }} jours</p>
                </div>
                @else
                <div style="background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); padding: 25px 40px; text-align: center;">
                    <span style="font-size: 40px;">🔵</span>
                    <h2 style="margin: 10px 0 5px; color: #1f2937; font-size: 22px; font-weight: 700;">Maintenance Planifiée</h2>
                    <p style="margin: 0; color: rgba(255,255,255,0.9); font-size: 16px;">Dans {{ $daysUntil }} jours</p>
                </div>
                @endif
            </td>
        </tr>

        <!-- Content -->
        <tr>
            <td style="padding: 40px;">
                <p style="margin: 0 0 30px; color: #374151; font-size: 16px; line-height: 1.6;">
                    Bonjour,<br><br>
                    Ceci est un rappel concernant une maintenance planifiée qui nécessite votre attention :
                </p>

                <!-- Maintenance Card -->
                <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f9fafb; border-radius: 12px; margin-bottom: 30px; overflow: hidden;">
                    <tr>
                        <td style="padding: 25px;">
                            <table width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 20px;">
                                            <div style="width: 50px; height: 50px; background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                                                <span style="font-size: 24px; display: block; text-align: center; line-height: 50px;">🔧</span>
                                            </div>
                                        </div>
                                        <h3 style="margin: 0 0 10px; color: #111827; font-size: 20px; font-weight: 700;">{{ $maintenance->title }}</h3>
                                        @if($maintenance->description)
                                        <p style="margin: 0 0 20px; color: #6b7280; font-size: 14px; line-height: 1.6;">{{ $maintenance->description }}</p>
                                        @endif
                                    </td>
                                </tr>
                            </table>

                            <!-- Details Grid -->
                            <table width="100%" cellpadding="0" cellspacing="0" style="border-top: 1px solid #e5e7eb; padding-top: 20px;">
                                <tr>
                                    <td width="50%" style="padding: 10px 0;">
                                        <span style="color: #9ca3af; font-size: 11px; text-transform: uppercase; letter-spacing: 0.05em;">Équipement</span>
                                        <p style="margin: 5px 0 0; color: #374151; font-size: 15px; font-weight: 600;">{{ $maintenance->equipment->name ?? 'Non spécifié' }}</p>
                                    </td>
                                    <td width="50%" style="padding: 10px 0;">
                                        <span style="color: #9ca3af; font-size: 11px; text-transform: uppercase; letter-spacing: 0.05em;">Type</span>
                                        <p style="margin: 5px 0 0; color: #374151; font-size: 15px; font-weight: 600;">
                                            @if($maintenance->type === 'preventive')
                                            🛡️ Préventive
                                            @elseif($maintenance->type === 'corrective')
                                            🔧 Corrective
                                            @else
                                            📋 {{ ucfirst($maintenance->type) }}
                                            @endif
                                        </p>
                                    </td>
                                </tr>
                                <tr>
                                    <td width="50%" style="padding: 10px 0;">
                                        <span style="color: #9ca3af; font-size: 11px; text-transform: uppercase; letter-spacing: 0.05em;">Date prévue</span>
                                        <p style="margin: 5px 0 0; color: {{ $daysUntil <= 1 ? '#ef4444' : ($daysUntil <= 3 ? '#f97316' : '#374151') }}; font-size: 15px; font-weight: 700;">
                                            📅 {{ $maintenance->scheduled_date?->format('d/m/Y') ?? 'Non définie' }}
                                        </p>
                                    </td>
                                    <td width="50%" style="padding: 10px 0;">
                                        <span style="color: #9ca3af; font-size: 11px; text-transform: uppercase; letter-spacing: 0.05em;">Priorité</span>
                                        <p style="margin: 5px 0 0;">
                                            @if($maintenance->priority === 'high' || $daysUntil <= 1)
                                            <span style="display: inline-block; padding: 4px 12px; background-color: #fef2f2; color: #dc2626; font-size: 13px; font-weight: 600; border-radius: 20px;">🔴 Haute</span>
                                            @elseif($maintenance->priority === 'medium' || $daysUntil <= 3)
                                            <span style="display: inline-block; padding: 4px 12px; background-color: #fff7ed; color: #ea580c; font-size: 13px; font-weight: 600; border-radius: 20px;">🟠 Moyenne</span>
                                            @else
                                            <span style="display: inline-block; padding: 4px 12px; background-color: #eff6ff; color: #2563eb; font-size: 13px; font-weight: 600; border-radius: 20px;">🔵 Normale</span>
                                            @endif
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>

                <!-- Action Required -->
                <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom: 30px;">
                    <tr>
                        <td style="padding: 15px 20px; background-color: {{ $daysUntil <= 1 ? '#fef2f2' : '#fef3c7' }}; border-left: 4px solid {{ $daysUntil <= 1 ? '#ef4444' : '#f59e0b' }}; border-radius: 0 8px 8px 0;">
                            <p style="margin: 0; color: {{ $daysUntil <= 1 ? '#991b1b' : '#92400e' }}; font-size: 14px;">
                                <strong>⚡ Action requise :</strong> 
                                @if($daysUntil <= 1)
                                Cette maintenance est urgente et doit être effectuée immédiatement.
                                @elseif($daysUntil <= 3)
                                Veuillez préparer le nécessaire pour cette maintenance prochaine.
                                @else
                                Assurez-vous que tout est prévu pour cette maintenance.
                                @endif
                            </p>
                        </td>
                    </tr>
                </table>

                <!-- CTA Button -->
                <table width="100%" cellpadding="0" cellspacing="0">
                    <tr>
                        <td style="text-align: center;">
                            <a href="{{ config('app.frontend_url', 'http://localhost:5173') }}/maintenance" style="display: inline-block; padding: 14px 32px; background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); color: white; text-decoration: none; font-size: 16px; font-weight: 600; border-radius: 10px;">
                                Voir les maintenances →
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
