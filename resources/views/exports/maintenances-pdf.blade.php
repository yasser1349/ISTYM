<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>{{ $title }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 10px; color: #333; line-height: 1.4; }
        .header {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
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
        .status-scheduled { background: #dbeafe; color: #1e40af; }
        .status-in_progress { background: #fef3c7; color: #92400e; }
        .status-completed { background: #d1fae5; color: #065f46; }
        .status-cancelled { background: #fee2e2; color: #991b1b; }
        .priority-high { background: #fee2e2; color: #991b1b; }
        .priority-normal { background: #dbeafe; color: #1e40af; }
        .priority-low { background: #f3f4f6; color: #6b7280; }
        .footer {
            position: fixed; bottom: 0; left: 0; right: 0; padding: 10px 20px;
            background: #f3f4f6; font-size: 8px; color: #6b7280; text-align: center;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $title }}</h1>
        <div class="subtitle">{{ $company }}</div>
        <div class="date">Généré le {{ $date }}</div>
    </div>

    <table>
        <tr>
            <td style="border: none; padding: 10px; background: #f3f4f6; border-left: 4px solid #f59e0b;">
                <span style="font-size: 9px; color: #6b7280;">TOTAL MAINTENANCES</span><br>
                <strong style="font-size: 16px;">{{ $total }}</strong>
            </td>
        </tr>
    </table>

    <br>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Titre</th>
                <th>Équipement</th>
                <th>Type</th>
                <th>Date Prévue</th>
                <th>Technicien</th>
                <th style="text-align: center;">Statut</th>
                <th style="text-align: center;">Priorité</th>
            </tr>
        </thead>
        <tbody>
            @foreach($maintenances as $maintenance)
            @php
                $statusClass = str_replace(' ', '_', $maintenance->status);
                $statusLabel = match($maintenance->status) {
                    'scheduled' => 'Planifiée',
                    'in_progress' => 'En cours',
                    'completed' => 'Terminée',
                    'cancelled' => 'Annulée',
                    default => $maintenance->status,
                };
            @endphp
            <tr>
                <td>{{ $maintenance->id }}</td>
                <td><strong>{{ $maintenance->title }}</strong></td>
                <td>{{ $maintenance->product->name ?? $maintenance->equipment ?? 'N/A' }}</td>
                <td>{{ $maintenance->type === 'preventive' ? 'Préventive' : 'Corrective' }}</td>
                <td>{{ $maintenance->scheduled_date ? date('d/m/Y', strtotime($maintenance->scheduled_date)) : 'N/A' }}</td>
                <td>{{ $maintenance->technician->name ?? 'Non assigné' }}</td>
                <td style="text-align: center;">
                    <span class="status status-{{ $statusClass }}">{{ $statusLabel }}</span>
                </td>
                <td style="text-align: center;">
                    <span class="status priority-{{ $maintenance->priority ?? 'normal' }}">
                        {{ ucfirst($maintenance->priority ?? 'Normal') }}
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
