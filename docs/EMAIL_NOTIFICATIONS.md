# Configuration des Notifications Email - ISTYM ERP

## 📧 Configuration SMTP

Ajoutez ces variables dans votre fichier `.env` :

```env
# Configuration Mail SMTP
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=votre-email@gmail.com
MAIL_PASSWORD=votre-mot-de-passe-application
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@istym.ma
MAIL_FROM_NAME="ISTYM ERP"

# URL Frontend (pour les liens dans les emails)
FRONTEND_URL=http://localhost:5173
```

### Pour Gmail :
1. Activez l'authentification à 2 facteurs sur votre compte Google
2. Générez un "Mot de passe d'application" : https://myaccount.google.com/apppasswords
3. Utilisez ce mot de passe dans `MAIL_PASSWORD`

### Pour Mailtrap (développement) :
```env
MAIL_MAILER=smtp
MAIL_HOST=sandbox.smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=votre-username
MAIL_PASSWORD=votre-password
MAIL_ENCRYPTION=tls
```

## 📬 Types de Notifications

### 1. Alertes Stock
- **Stock Bas** ⚠️ : Quand le stock descend sous le seuil minimum
- **Stock Critique** 🔴 : Quand le stock descend à 50% du minimum
- **Rupture** ❌ : Quand le stock atteint 0

### 2. Commandes
- **Confirmation** ✅ : Email au client à la création de commande
- **Statut Confirmé** ✅ : Quand la commande est confirmée
- **Expédié** 🚚 : Quand la commande est expédiée
- **Livré** 📦 : Quand la commande est livrée
- **Annulé** ❌ : Quand la commande est annulée

### 3. Maintenance
- **Rappel J-7** 🔵 : 7 jours avant la maintenance
- **Rappel J-3** 🟠 : 3 jours avant la maintenance
- **Rappel J-1** 🔴 : 1 jour avant la maintenance
- **Rappel J** 🔴 : Le jour de la maintenance

## 🕐 Planification Automatique

Ajoutez cette commande dans votre crontab pour envoyer les notifications automatiquement :

```bash
# Exécuter chaque jour à 8h00
0 8 * * * cd /chemin/vers/istym/backend && php artisan notifications:send --all >> /dev/null 2>&1
```

Ou avec le scheduler Laravel (dans `app/Console/Kernel.php`) :

```php
protected function schedule(Schedule $schedule)
{
    // Vérifier stock et maintenances chaque jour à 8h
    $schedule->command('notifications:send --all')
             ->dailyAt('08:00')
             ->emailOutputTo('admin@istym.ma');
}
```

## 🧪 Test des Emails

### Via API (Admin uniquement)

```bash
# Tester alerte stock
POST /api/email-notifications/test/stock-alert
{
    "product_id": 1,
    "alert_type": "critical"  // "low", "critical", "out_of_stock"
}

# Tester confirmation commande
POST /api/email-notifications/test/order-confirmation
{
    "order_id": 1
}

# Tester mise à jour statut
POST /api/email-notifications/test/order-status
{
    "order_id": 1,
    "new_status": "shipped"  // "confirmed", "shipped", "delivered", "cancelled"
}

# Vérifier tous les stocks bas
POST /api/email-notifications/check/stock

# Vérifier maintenances à venir
POST /api/email-notifications/check/maintenances

# Obtenir statistiques
GET /api/email-notifications/statistics
```

### Via Artisan

```bash
# Envoyer toutes les notifications
php artisan notifications:send --all

# Envoyer uniquement les alertes stock
php artisan notifications:send --stock

# Envoyer uniquement les rappels maintenance
php artisan notifications:send --maintenance
```

## ⚙️ Préférences Utilisateur

Chaque utilisateur peut activer/désactiver ses alertes email dans les paramètres :

- `email_alerts` : Activer/désactiver toutes les alertes
- `stock_alerts` : Alertes de stock
- `order_alerts` : Alertes de commande
- `maintenance_alerts` : Rappels de maintenance

## 🎨 Personnalisation des Templates

Les templates email se trouvent dans :
```
resources/views/emails/
├── stock-alert.blade.php
├── order-confirmation.blade.php
├── order-status.blade.php
└── maintenance-reminder.blade.php
```

Chaque template utilise :
- Design responsive compatible tous clients mail
- Couleurs ISTYM (vert #10b981)
- Emojis pour une meilleure visibilité
- Boutons d'action vers l'application
