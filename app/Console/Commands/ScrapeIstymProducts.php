<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Models\Category;
use App\Models\Supplier;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class ScrapeIstymProducts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'products:scrape-istym 
                            {--dry-run : Afficher les produits sans les importer}
                            {--force : Forcer la mise à jour des produits existants}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scraper les produits depuis le site istym.ma';

    /**
     * URLs à scraper
     */
    protected array $urls = [
        'https://istym.ma/shop/',
        'https://istym.ma/product-category/roulement-a-billes/',
    ];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Démarrage du scraping de istym.ma...');
        $this->newLine();

        $products = $this->scrapeProducts();

        if (empty($products)) {
            $this->warn('⚠️ Aucun produit trouvé sur le site.');
            $this->info('💡 Utilisez plutôt: php artisan db:seed --class=IstymProductSeeder');
            return Command::FAILURE;
        }

        $this->info("📦 {$this->count($products)} produit(s) trouvé(s)");
        $this->newLine();

        if ($this->option('dry-run')) {
            $this->displayProducts($products);
            return Command::SUCCESS;
        }

        $this->importProducts($products);

        $this->newLine();
        $this->info('✅ Scraping terminé avec succès!');

        return Command::SUCCESS;
    }

    /**
     * Scraper les produits depuis le site
     */
    protected function scrapeProducts(): array
    {
        $products = [];

        foreach ($this->urls as $url) {
            $this->info("📥 Récupération de: {$url}");

            try {
                $response = Http::timeout(30)
                    ->withHeaders([
                        'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                        'Accept' => 'text/html,application/xhtml+xml',
                    ])
                    ->get($url);

                if ($response->successful()) {
                    $html = $response->body();
                    $extracted = $this->extractProductsFromHtml($html);
                    $products = array_merge($products, $extracted);
                } else {
                    $this->error("❌ Erreur HTTP: {$response->status()}");
                }
            } catch (\Exception $e) {
                $this->error("❌ Erreur: {$e->getMessage()}");
            }
        }

        // Supprimer les doublons basés sur le slug
        return collect($products)->unique('slug')->values()->toArray();
    }

    /**
     * Extraire les produits du HTML
     */
    protected function extractProductsFromHtml(string $html): array
    {
        $products = [];

        // Pattern pour les liens produits WooCommerce
        $pattern = '/href="https:\/\/istym\.ma\/shop\/([^\/]+)\/([^\/]+)\/"/';
        
        if (preg_match_all($pattern, $html, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $category = $match[1];
                $slug = $match[2];
                
                // Extraire le nom du produit depuis le slug
                $name = $this->slugToName($slug);
                
                // Extraire la marque (ex: SKF, FAG, NTN)
                $brand = $this->extractBrand($slug);
                
                $products[] = [
                    'slug' => $slug,
                    'name' => $name,
                    'category' => $this->slugToName($category),
                    'brand' => $brand,
                    'url' => "https://istym.ma/shop/{$category}/{$slug}/",
                ];
            }
        }

        return $products;
    }

    /**
     * Convertir un slug en nom lisible
     */
    protected function slugToName(string $slug): string
    {
        return Str::title(str_replace('-', ' ', $slug));
    }

    /**
     * Extraire la marque du slug
     */
    protected function extractBrand(string $slug): string
    {
        $brands = ['skf', 'fag', 'ntn', 'nsk', 'megadyne', 'sedis', 'timken', 'ina'];
        
        foreach ($brands as $brand) {
            if (Str::contains(strtolower($slug), $brand)) {
                return strtoupper($brand);
            }
        }
        
        return 'SKF'; // Marque par défaut
    }

    /**
     * Afficher les produits (dry-run)
     */
    protected function displayProducts(array $products): void
    {
        $headers = ['Nom', 'Catégorie', 'Marque', 'URL'];
        $rows = collect($products)->map(fn($p) => [
            Str::limit($p['name'], 40),
            $p['category'],
            $p['brand'],
            Str::limit($p['url'], 50),
        ])->toArray();

        $this->table($headers, $rows);
    }

    /**
     * Importer les produits en base de données
     */
    protected function importProducts(array $products): void
    {
        $this->info('📥 Import des produits en base de données...');
        
        $bar = $this->output->createProgressBar(count($products));
        $bar->start();

        $imported = 0;
        $updated = 0;
        $skipped = 0;

        foreach ($products as $productData) {
            // Récupérer ou créer la catégorie
            $category = Category::firstOrCreate(
                ['slug' => Str::slug($productData['category'])],
                [
                    'name' => $productData['category'],
                    'type' => 'product',
                    'is_active' => true,
                ]
            );

            // Récupérer ou créer le fournisseur
            $supplier = Supplier::firstOrCreate(
                ['code' => $productData['brand']],
                [
                    'name' => $productData['brand'],
                    'email' => strtolower($productData['brand']) . '@supplier.com',
                    'phone' => '+212 5 00 00 00 00',
                    'country' => 'International',
                    'is_active' => true,
                ]
            );

            // Générer une référence unique
            $reference = $productData['brand'] . '-' . strtoupper(Str::random(6));

            // Vérifier si le produit existe déjà
            $existing = Product::where('name', $productData['name'])->first();

            if ($existing && !$this->option('force')) {
                $skipped++;
                $bar->advance();
                continue;
            }

            if ($existing && $this->option('force')) {
                $existing->update([
                    'category_id' => $category->id,
                    'supplier_id' => $supplier->id,
                ]);
                $updated++;
            } else {
                Product::create([
                    'name' => $productData['name'],
                    'reference' => $reference,
                    'description' => "Produit importé depuis istym.ma - {$productData['category']}",
                    'category_id' => $category->id,
                    'supplier_id' => $supplier->id,
                    'purchase_price' => rand(50, 500), // Prix par défaut (à ajuster manuellement)
                    'selling_price' => rand(80, 800),
                    'quantity_in_stock' => rand(10, 100),
                    'minimum_stock' => 5,
                    'maximum_stock' => 200,
                    'unit' => 'pièce',
                    'is_active' => true,
                ]);
                $imported++;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->info("✅ Importés: {$imported}");
        $this->info("🔄 Mis à jour: {$updated}");
        $this->info("⏭️ Ignorés: {$skipped}");
    }

    /**
     * Compter les éléments
     */
    protected function count(array $items): int
    {
        return count($items);
    }
}
