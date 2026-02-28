<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Category;
use App\Models\Supplier;
use App\Models\Product;
use App\Models\Client;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Maintenance;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Créer les utilisateurs
        $admin = User::firstOrCreate(
            ['email' => 'admin@istym.ma'],
            [
                'name' => 'Admin ISTYM',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'phone' => '+212 600 000 001',
            ]
        );

        // Employé Chaimae
        $chaimae = User::firstOrCreate(
            ['email' => 'chaimae@istym.ma'],
            [
                'name' => 'Chaimae',
                'password' => Hash::make('password'),
                'role' => 'employee',
                'phone' => '',
                'is_active' => true,
            ]
        );

        // Employé Ismail
        $ismail = User::firstOrCreate(
            ['email' => 'ismail@istym.ma'],
            [
                'name' => 'Ismail',
                'password' => Hash::make('password'),
                'role' => 'employee',
                'phone' => '',
                'is_active' => true,
            ]
        );

        // Créer les catégories
        $categories = [
            ['name' => 'Roulements', 'slug' => 'roulements', 'description' => 'Roulements à billes, rouleaux, aiguilles'],
            ['name' => 'Courroies', 'slug' => 'courroies', 'description' => 'Courroies de transmission'],
            ['name' => 'Joints', 'slug' => 'joints', 'description' => 'Joints hydrauliques et pneumatiques'],
            ['name' => 'Chaînes', 'slug' => 'chaines', 'description' => 'Chaînes de transmission'],
            ['name' => 'Poulies', 'slug' => 'poulies', 'description' => 'Poulies et tendeurs'],
            ['name' => 'Vérins', 'slug' => 'verins', 'description' => 'Vérins hydrauliques et pneumatiques'],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }

        // Créer les fournisseurs
        $suppliers = [
            ['name' => 'SKF Group', 'code' => 'SKF', 'email' => 'contact@skf.com', 'country' => 'Suède'],
            ['name' => 'FAG Bearings', 'code' => 'FAG', 'email' => 'contact@fag.com', 'country' => 'Allemagne'],
            ['name' => 'NTN Corporation', 'code' => 'NTN', 'email' => 'contact@ntn.com', 'country' => 'Japon'],
            ['name' => 'Megadyne Group', 'code' => 'Megadyne', 'email' => 'contact@megadyne.com', 'country' => 'Italie'],
            ['name' => 'Gates Corporation', 'code' => 'Gates', 'email' => 'contact@gates.com', 'country' => 'USA'],
        ];

        foreach ($suppliers as $supplier) {
            Supplier::create($supplier);
        }

        // Créer les produits
        $products = [
            ['name' => 'Roulement SKF 6205-2RS', 'reference' => 'SKF-6205-2RS', 'category_id' => 1, 'supplier_id' => 1, 'purchase_price' => 45.00, 'selling_price' => 75.00, 'quantity_in_stock' => 5, 'minimum_stock' => 20, 'maximum_stock' => 100],
            ['name' => 'Roulement SKF 6206-2Z', 'reference' => 'SKF-6206-2Z', 'category_id' => 1, 'supplier_id' => 1, 'purchase_price' => 55.00, 'selling_price' => 90.00, 'quantity_in_stock' => 45, 'minimum_stock' => 15, 'maximum_stock' => 80],
            ['name' => 'Roulement SKF 6208-2RS', 'reference' => 'SKF-6208-2RS', 'category_id' => 1, 'supplier_id' => 1, 'purchase_price' => 85.00, 'selling_price' => 140.00, 'quantity_in_stock' => 30, 'minimum_stock' => 10, 'maximum_stock' => 60],
            ['name' => 'Roulement FAG 22210', 'reference' => 'FAG-22210', 'category_id' => 1, 'supplier_id' => 2, 'purchase_price' => 250.00, 'selling_price' => 420.00, 'quantity_in_stock' => 12, 'minimum_stock' => 5, 'maximum_stock' => 30],
            ['name' => 'Roulement FAG 32212', 'reference' => 'FAG-32212', 'category_id' => 1, 'supplier_id' => 2, 'purchase_price' => 180.00, 'selling_price' => 300.00, 'quantity_in_stock' => 8, 'minimum_stock' => 10, 'maximum_stock' => 40],
            ['name' => 'Roulement NTN 6310', 'reference' => 'NTN-6310', 'category_id' => 1, 'supplier_id' => 3, 'purchase_price' => 120.00, 'selling_price' => 200.00, 'quantity_in_stock' => 25, 'minimum_stock' => 8, 'maximum_stock' => 50],
            ['name' => 'Courroie Megadyne XPZ 1400', 'reference' => 'MDY-XPZ1400', 'category_id' => 2, 'supplier_id' => 4, 'purchase_price' => 35.00, 'selling_price' => 58.00, 'quantity_in_stock' => 3, 'minimum_stock' => 15, 'maximum_stock' => 60],
            ['name' => 'Courroie Megadyne SPB 2000', 'reference' => 'MDY-SPB2000', 'category_id' => 2, 'supplier_id' => 4, 'purchase_price' => 65.00, 'selling_price' => 110.00, 'quantity_in_stock' => 18, 'minimum_stock' => 10, 'maximum_stock' => 50],
            ['name' => 'Courroie Gates HTD 8M-1200', 'reference' => 'GATES-HTD8M1200', 'category_id' => 2, 'supplier_id' => 5, 'purchase_price' => 95.00, 'selling_price' => 160.00, 'quantity_in_stock' => 15, 'minimum_stock' => 5, 'maximum_stock' => 30],
            ['name' => 'Joint hydraulique DN40', 'reference' => 'HYD-JNT40', 'category_id' => 3, 'supplier_id' => 3, 'purchase_price' => 12.00, 'selling_price' => 22.00, 'quantity_in_stock' => 8, 'minimum_stock' => 25, 'maximum_stock' => 100],
            ['name' => 'Joint torique 50x5', 'reference' => 'TOR-50X5', 'category_id' => 3, 'supplier_id' => 1, 'purchase_price' => 3.50, 'selling_price' => 7.00, 'quantity_in_stock' => 150, 'minimum_stock' => 50, 'maximum_stock' => 500],
            ['name' => 'Joint pneumatique DN25', 'reference' => 'PNE-JNT25', 'category_id' => 3, 'supplier_id' => 2, 'purchase_price' => 8.00, 'selling_price' => 15.00, 'quantity_in_stock' => 45, 'minimum_stock' => 20, 'maximum_stock' => 100],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }

        // Créer des clients
        $clientsData = [
            ['company_name' => 'COSUMAR', 'contact_name' => 'Ahmed Benali', 'email' => 'ahmed@cosumar.ma', 'sector' => 'Agroalimentaire', 'city' => 'Casablanca'],
            ['company_name' => 'OCP Group', 'contact_name' => 'Fatima Zahra', 'email' => 'fzahra@ocp.ma', 'sector' => 'Mines', 'city' => 'Khouribga'],
            ['company_name' => 'Renault Maroc', 'contact_name' => 'Karim Idrissi', 'email' => 'k.idrissi@renault.ma', 'sector' => 'Automobile', 'city' => 'Tanger'],
            ['company_name' => 'Ciments du Maroc', 'contact_name' => 'Hassan Tazi', 'email' => 'htazi@cimentsdumaroc.ma', 'sector' => 'Cimenterie', 'city' => 'Marrakech'],
            ['company_name' => 'Centrale Danone', 'contact_name' => 'Salma Alaoui', 'email' => 'salma@danone.ma', 'sector' => 'Agroalimentaire', 'city' => 'Fès'],
        ];

        $clients = [];
        foreach ($clientsData as $clientData) {
            $user = User::create([
                'name' => $clientData['contact_name'],
                'email' => $clientData['email'],
                'password' => Hash::make('password'),
                'role' => 'client',
            ]);

            $clients[] = Client::create([
                'user_id' => $user->id,
                'company_name' => $clientData['company_name'],
                'contact_name' => $clientData['contact_name'],
                'email' => $clientData['email'],
                'sector' => $clientData['sector'],
                'city' => $clientData['city'],
                'country' => 'Maroc',
                'credit_limit' => rand(50000, 200000),
            ]);
        }

        // Créer des commandes
        $statuses = ['pending', 'confirmed', 'processing', 'delivered'];
        
        for ($i = 0; $i < 15; $i++) {
            $client = $clients[array_rand($clients)];
            $status = $statuses[array_rand($statuses)];
            
            $order = Order::create([
                'order_number' => Order::generateOrderNumber('sale'),
                'client_id' => $client->id,
                'user_id' => $employee->id,
                'type' => 'sale',
                'status' => $status,
                'tax_rate' => 20,
                'discount' => rand(0, 500),
                'created_at' => now()->subDays(rand(1, 90)),
                'delivered_at' => $status === 'delivered' ? now()->subDays(rand(1, 30)) : null,
            ]);

            $productIds = Product::inRandomOrder()->take(rand(1, 4))->pluck('id');
            
            foreach ($productIds as $productId) {
                $product = Product::find($productId);
                $quantity = rand(1, 10);
                
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $productId,
                    'quantity' => $quantity,
                    'unit_price' => $product->selling_price,
                    'discount' => rand(0, 50),
                    'total' => ($product->selling_price * $quantity) - rand(0, 50),
                ]);
            }

            $order->calculateTotals();
        }

        // Créer des maintenances
        $maintenanceTypes = ['preventive', 'corrective', 'inspection'];
        $priorities = ['low', 'medium', 'high', 'urgent'];
        $maintenanceStatuses = ['scheduled', 'in_progress', 'completed'];
        
        $equipments = [
            'Convoyeur principal',
            'Pompe hydraulique #1',
            'Compresseur central',
            'Moteur électrique 75kW',
            'Réducteur de vitesse',
            'Ventilateur industriel',
        ];

        for ($i = 0; $i < 12; $i++) {
            $client = $clients[array_rand($clients)];
            $status = $maintenanceStatuses[array_rand($maintenanceStatuses)];
            $scheduledDate = now()->addDays(rand(-30, 30));
            
            Maintenance::create([
                'reference' => Maintenance::generateReference(),
                'client_id' => $client->id,
                'user_id' => $employee->id,
                'title' => 'Maintenance ' . $equipments[array_rand($equipments)],
                'description' => 'Maintenance programmée selon le planning annuel',
                'type' => $maintenanceTypes[array_rand($maintenanceTypes)],
                'priority' => $priorities[array_rand($priorities)],
                'status' => $status,
                'equipment' => $equipments[array_rand($equipments)],
                'location' => $client->city,
                'scheduled_date' => $scheduledDate,
                'scheduled_time' => sprintf('%02d:00', rand(8, 17)),
                'duration_hours' => rand(2, 8),
                'completed_date' => $status === 'completed' ? $scheduledDate : null,
                'labor_cost' => $status === 'completed' ? rand(500, 3000) : 0,
                'parts_cost' => $status === 'completed' ? rand(200, 2000) : 0,
                'total_cost' => $status === 'completed' ? rand(700, 5000) : 0,
            ]);
        }

        $this->command->info('Base de données initialisée avec succès!');
        $this->command->info('');
        $this->command->info('Comptes de test:');
        $this->command->info('Admin: admin@istym.ma / password');
        $this->command->info('Employé: employee@istym.ma / password');
        $this->command->info('Client: ahmed@cosumar.ma / password');
    }
}
