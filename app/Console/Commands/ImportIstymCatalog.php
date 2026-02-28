<?php

namespace App\Console\Commands;

use Database\Seeders\IstymProductSeeder;
use Illuminate\Console\Command;

class ImportIstymCatalog extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'products:import-catalog 
                            {--fresh : Supprimer tous les produits existants avant import}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Importer le catalogue complet des produits ISTYM Industrie';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('');
        $this->info('╔══════════════════════════════════════════════════════════╗');
        $this->info('║       🏭 ISTYM Industrie - Import du Catalogue           ║');
        $this->info('╚══════════════════════════════════════════════════════════╝');
        $this->info('');

        if ($this->option('fresh')) {
            if ($this->confirm('⚠️ Voulez-vous vraiment supprimer tous les produits existants?')) {
                $this->warn('🗑️ Suppression des produits existants...');
                \App\Models\Product::truncate();
                $this->info('✅ Produits supprimés');
            }
        }

        $this->info('📦 Lancement de l\'import du catalogue ISTYM...');
        $this->newLine();

        // Exécuter le seeder
        $seeder = new IstymProductSeeder();
        $seeder->setCommand($this);
        $seeder->run();

        $this->newLine();
        $this->info('╔══════════════════════════════════════════════════════════╗');
        $this->info('║                 ✅ Import terminé!                       ║');
        $this->info('╚══════════════════════════════════════════════════════════╝');
        $this->newLine();

        // Afficher les statistiques
        $this->displayStatistics();

        return Command::SUCCESS;
    }

    /**
     * Afficher les statistiques après import
     */
    protected function displayStatistics(): void
    {
        $stats = [
            ['Métrique', 'Valeur'],
            ['───────────────────', '─────────'],
            ['Fournisseurs', \App\Models\Supplier::count()],
            ['Catégories', \App\Models\Category::count()],
            ['Produits', \App\Models\Product::count()],
            ['Produits actifs', \App\Models\Product::where('is_active', true)->count()],
            ['Stock total (pièces)', number_format(\App\Models\Product::sum('quantity_in_stock'))],
            ['Valeur stock (achat)', number_format(\App\Models\Product::selectRaw('SUM(purchase_price * quantity_in_stock) as total')->value('total'), 2) . ' MAD'],
            ['Valeur stock (vente)', number_format(\App\Models\Product::selectRaw('SUM(selling_price * quantity_in_stock) as total')->value('total'), 2) . ' MAD'],
        ];

        $this->info('📊 Statistiques du catalogue:');
        $this->newLine();

        foreach ($stats as $row) {
            $this->line(sprintf('   %-25s %s', $row[0], $row[1]));
        }

        $this->newLine();
    }
}
