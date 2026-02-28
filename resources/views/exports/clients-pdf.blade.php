<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>{{ $title }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 10px; color: #333; line-height: 1.4; }
        .header {
            background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
            color: white; padding: 20px; margin-bottom: 20px;
        }
        .header h1 { font-size: 22px; margin-bottom: 5px; }
        .header .subtitle { font-size: 11px; opacity: 0.9; }
        .header .date { position: absolute; right: 20px; top: 25px; font-size: 10px; }
        table { width: calc(100% - 40px); border-collapse: collapse; margin: 0 20px; font-size: 9px; }
        th { background: #1f2937; color: white; padding: 10px 8px; text-align: left; font-weight: 600; }
        td { padding: 8px; border-bottom: 1px solid #e5e7eb; }
        tr:nth-child(even) { background: #f9fafb; }
        .footer {
            position: fixed; bottom: 0; left: 0; right: 0; padding: 10px 20px;
            background: #f3f4f6; font-size: 8px; color: #6b7280; text-align: center;
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
            <td style="border-left: 4px solid #8b5cf6;">
                <span style="font-size: 9px; color: #6b7280;">TOTAL CLIENTS</span><br>
                <strong style="font-size: 16px;">{{ $total }}</strong>
            </td>
        </tr>
    </table>

    <br>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Entreprise</th>
                <th>Contact</th>
                <th>Email</th>
                <th>Téléphone</th>
                <th>Ville</th>
                <th style="text-align: center;">Commandes</th>
                <th style="text-align: right;">Total Achats</th>
            </tr>
        </thead>
        <tbody>
            @foreach($clients as $client)
            <tr>
                <td>{{ $client->id }}</td>
                <td><strong>{{ $client->company ?? 'N/A' }}</strong></td>
                <td>{{ $client->contact_name ?? $client->name ?? 'N/A' }}</td>
                <td>{{ $client->email }}</td>
                <td>{{ $client->phone ?? 'N/A' }}</td>
                <td>{{ $client->city ?? 'N/A' }}</td>
                <td style="text-align: center;">{{ $client->orders_count }}</td>
                <td style="text-align: right;"><strong>{{ number_format($client->orders_sum_total ?? 0, 2, ',', ' ') }} MAD</strong></td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        {{ $company }} - Document généré automatiquement le {{ $date }}
    </div>
</body>
</html>
