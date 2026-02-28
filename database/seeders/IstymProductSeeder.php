<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class IstymProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Catalogue complet des produits industriels ISTYM
     * Basé sur les marques partenaires: SKF, FAG, NTN, NSK, MEGADYNE, SEDIS
     */
    public function run(): void
    {
        $this->command->info('🏭 Création du catalogue ISTYM Industrie...');

        // Créer les fournisseurs (marques partenaires)
        $suppliers = $this->createSuppliers();
        
        // Créer les catégories
        $categories = $this->createCategories();
        
        // Créer les produits
        $this->createProducts($suppliers, $categories);

        $this->command->info('✅ Catalogue ISTYM créé avec succès!');
    }

    /**
     * Créer les fournisseurs/marques partenaires
     */
    protected function createSuppliers(): array
    {
        $this->command->info('📦 Création des fournisseurs...');

        $suppliersData = [
            [
                'name' => 'SKF',
                'code' => 'SKF',
                'email' => 'contact@skf.com',
                'phone' => '+46 31 337 10 00',
                'address' => 'Hornsgatan 1',
                'city' => 'Göteborg',
                'country' => 'Suède',
                'contact_person' => 'Service Commercial',
                'notes' => 'Leader mondial des roulements et solutions de rotation',
            ],
            [
                'name' => 'FAG (Schaeffler)',
                'code' => 'FAG',
                'email' => 'contact@schaeffler.com',
                'phone' => '+49 9132 82 0',
                'address' => 'Industriestraße 1-3',
                'city' => 'Herzogenaurach',
                'country' => 'Allemagne',
                'contact_person' => 'Service Commercial',
                'notes' => 'Roulements de précision et solutions industrielles',
            ],
            [
                'name' => 'NTN Corporation',
                'code' => 'NTN',
                'email' => 'contact@ntn.co.jp',
                'phone' => '+81 6 6449 3111',
                'address' => 'Nishi-ku',
                'city' => 'Osaka',
                'country' => 'Japon',
                'contact_person' => 'Service Export',
                'notes' => 'Roulements haute performance et composants automobiles',
            ],
            [
                'name' => 'NSK Ltd',
                'code' => 'NSK',
                'email' => 'contact@nsk.com',
                'phone' => '+81 3 3779 7111',
                'address' => 'Nissei Bldg',
                'city' => 'Tokyo',
                'country' => 'Japon',
                'contact_person' => 'Service Commercial',
                'notes' => 'Technologies de mouvement linéaire et roulements',
            ],
            [
                'name' => 'MEGADYNE',
                'code' => 'MDY',
                'email' => 'info@megadynegroup.com',
                'phone' => '+39 011 9594 111',
                'address' => 'Corso Orbassano 366',
                'city' => 'Turin',
                'country' => 'Italie',
                'contact_person' => 'Service Commercial',
                'notes' => 'Courroies de transmission et solutions polymères',
            ],
            [
                'name' => 'SEDIS',
                'code' => 'SED',
                'email' => 'contact@sedis.com',
                'phone' => '+33 4 72 89 04 04',
                'address' => 'Zone Industrielle',
                'city' => 'Lyon',
                'country' => 'France',
                'contact_person' => 'Service Technique',
                'notes' => 'Chaînes industrielles et transmission mécanique',
            ],
            [
                'name' => 'TIMKEN',
                'code' => 'TMK',
                'email' => 'contact@timken.com',
                'phone' => '+1 234 262 3000',
                'address' => '4500 Mount Pleasant St NW',
                'city' => 'North Canton, Ohio',
                'country' => 'USA',
                'contact_person' => 'Service Commercial',
                'notes' => 'Roulements à rouleaux coniques et solutions mécaniques',
            ],
            [
                'name' => 'INA (Schaeffler)',
                'code' => 'INA',
                'email' => 'ina@schaeffler.com',
                'phone' => '+49 9132 82 0',
                'address' => 'Industriestraße 1-3',
                'city' => 'Herzogenaurach',
                'country' => 'Allemagne',
                'contact_person' => 'Service Technique',
                'notes' => 'Guidages linéaires et roulements spéciaux',
            ],
        ];

        $suppliers = [];
        foreach ($suppliersData as $data) {
            $supplier = Supplier::updateOrCreate(
                ['code' => $data['code']],
                array_merge($data, ['is_active' => true])
            );
            $suppliers[$data['code']] = $supplier;
        }

        return $suppliers;
    }

    /**
     * Créer les catégories de produits
     */
    protected function createCategories(): array
    {
        $this->command->info('📁 Création des catégories...');

        $categoriesData = [
            ['name' => 'Roulements à billes', 'slug' => 'roulements-billes', 'description' => 'Roulements à billes rigides, à contact oblique, à rotule'],
            ['name' => 'Roulements à rouleaux', 'slug' => 'roulements-rouleaux', 'description' => 'Roulements à rouleaux cylindriques, coniques, sphériques'],
            ['name' => 'Paliers et supports', 'slug' => 'paliers-supports', 'description' => 'Paliers à semelle, paliers appliques, supports de roulements'],
            ['name' => 'Courroies de transmission', 'slug' => 'courroies', 'description' => 'Courroies trapézoïdales, synchrones, poly-V'],
            ['name' => 'Chaînes industrielles', 'slug' => 'chaines', 'description' => 'Chaînes à rouleaux, chaînes de manutention, chaînes spéciales'],
            ['name' => 'Joints et étanchéité', 'slug' => 'joints', 'description' => 'Joints à lèvre, joints toriques, garnitures mécaniques'],
            ['name' => 'Hydraulique', 'slug' => 'hydraulique', 'description' => 'Vérins, pompes, distributeurs, filtres hydrauliques'],
            ['name' => 'Pneumatique', 'slug' => 'pneumatique', 'description' => 'Vérins pneumatiques, électrovannes, raccords'],
            ['name' => 'Lubrification', 'slug' => 'lubrification', 'description' => 'Graisses, huiles, systèmes de lubrification automatique'],
            ['name' => 'Transmission mécanique', 'slug' => 'transmission', 'description' => 'Poulies, pignons, accouplements, réducteurs'],
            ['name' => 'Outillage industriel', 'slug' => 'outillage', 'description' => 'Extracteurs, chauffeurs inductifs, outils de montage'],
            ['name' => 'Maintenance prédictive', 'slug' => 'maintenance', 'description' => 'Capteurs vibratoires, analyseurs, thermographie'],
        ];

        $categories = [];
        foreach ($categoriesData as $data) {
            $category = Category::updateOrCreate(
                ['slug' => $data['slug']],
                array_merge($data, ['type' => 'product', 'is_active' => true])
            );
            $categories[$data['slug']] = $category;
        }

        return $categories;
    }

    /**
     * Créer les produits du catalogue
     */
    protected function createProducts(array $suppliers, array $categories): void
    {
        $this->command->info('🔧 Création des produits...');

        $products = [
            // ===== ROULEMENTS À BILLES =====
            [
                'name' => 'Roulement à billes 6205-2RS',
                'reference' => 'SKF-6205-2RS',
                'description' => 'Roulement à billes à gorge profonde, étanche des deux côtés (2RS). Diamètre intérieur: 25mm, extérieur: 52mm, largeur: 15mm. Idéal pour applications générales.',
                'category' => 'roulements-billes',
                'supplier' => 'SKF',
                'purchase_price' => 12.50,
                'selling_price' => 24.90,
                'quantity_in_stock' => 250,
                'minimum_stock' => 50,
                'maximum_stock' => 500,
                'unit' => 'pièce',
                'location' => 'A1-01',
            ],
            [
                'name' => 'Roulement à billes 6206-ZZ',
                'reference' => 'SKF-6206-ZZ',
                'description' => 'Roulement à billes à gorge profonde avec flasques métalliques (ZZ). Dimensions: 30x62x16mm. Pour moteurs électriques et ventilateurs.',
                'category' => 'roulements-billes',
                'supplier' => 'SKF',
                'purchase_price' => 15.80,
                'selling_price' => 31.50,
                'quantity_in_stock' => 180,
                'minimum_stock' => 40,
                'maximum_stock' => 400,
                'unit' => 'pièce',
                'location' => 'A1-02',
            ],
            [
                'name' => 'Roulement à billes à contact oblique 7206 BECBP',
                'reference' => 'SKF-7206-BECBP',
                'description' => 'Roulement à contact oblique à une rangée. Angle de contact 40°. Pour charges combinées radiales et axiales. Cage polyamide.',
                'category' => 'roulements-billes',
                'supplier' => 'SKF',
                'purchase_price' => 35.00,
                'selling_price' => 69.90,
                'quantity_in_stock' => 80,
                'minimum_stock' => 20,
                'maximum_stock' => 200,
                'unit' => 'pièce',
                'location' => 'A1-03',
            ],
            [
                'name' => 'Roulement à rotule sur billes 2206 ETN9',
                'reference' => 'SKF-2206-ETN9',
                'description' => 'Roulement à rotule sur billes auto-aligneur. Tolère défauts d\'alignement jusqu\'à 3°. Cage polyamide renforcée.',
                'category' => 'roulements-billes',
                'supplier' => 'SKF',
                'purchase_price' => 28.00,
                'selling_price' => 55.00,
                'quantity_in_stock' => 65,
                'minimum_stock' => 15,
                'maximum_stock' => 150,
                'unit' => 'pièce',
                'location' => 'A1-04',
            ],
            [
                'name' => 'Roulement à billes 6308-2RS1',
                'reference' => 'FAG-6308-2RS',
                'description' => 'Roulement à gorge profonde FAG, étanche. Dimensions: 40x90x23mm. Haute capacité de charge. Applications industrielles lourdes.',
                'category' => 'roulements-billes',
                'supplier' => 'FAG',
                'purchase_price' => 32.00,
                'selling_price' => 63.00,
                'quantity_in_stock' => 120,
                'minimum_stock' => 25,
                'maximum_stock' => 300,
                'unit' => 'pièce',
                'location' => 'A1-05',
            ],
            [
                'name' => 'Roulement miniature 608-2RS',
                'reference' => 'NTN-608-2RS',
                'description' => 'Roulement miniature à billes. Dimensions: 8x22x7mm. Pour petits moteurs, skateboards, imprimantes 3D.',
                'category' => 'roulements-billes',
                'supplier' => 'NTN',
                'purchase_price' => 2.50,
                'selling_price' => 5.90,
                'quantity_in_stock' => 500,
                'minimum_stock' => 100,
                'maximum_stock' => 1000,
                'unit' => 'pièce',
                'location' => 'A1-06',
            ],
            [
                'name' => 'Roulement à billes 6310-2Z',
                'reference' => 'NSK-6310-2Z',
                'description' => 'Roulement NSK haute qualité. Dimensions: 50x110x27mm. Flasques métalliques. Excellente durée de vie.',
                'category' => 'roulements-billes',
                'supplier' => 'NSK',
                'purchase_price' => 45.00,
                'selling_price' => 89.00,
                'quantity_in_stock' => 75,
                'minimum_stock' => 20,
                'maximum_stock' => 180,
                'unit' => 'pièce',
                'location' => 'A1-07',
            ],

            // ===== ROULEMENTS À ROULEAUX =====
            [
                'name' => 'Roulement à rouleaux coniques 32206',
                'reference' => 'TMK-32206',
                'description' => 'Roulement à rouleaux coniques TIMKEN. Supporte charges radiales et axiales importantes. Pour moyeux de roues, boîtes de vitesses.',
                'category' => 'roulements-rouleaux',
                'supplier' => 'TMK',
                'purchase_price' => 28.00,
                'selling_price' => 55.00,
                'quantity_in_stock' => 90,
                'minimum_stock' => 25,
                'maximum_stock' => 200,
                'unit' => 'pièce',
                'location' => 'A2-01',
            ],
            [
                'name' => 'Roulement à rotule sur rouleaux 22210 E',
                'reference' => 'SKF-22210-E',
                'description' => 'Roulement à rotule sur rouleaux SKF Explorer. Auto-aligneur, haute capacité. Pour convoyeurs et machines lourdes.',
                'category' => 'roulements-rouleaux',
                'supplier' => 'SKF',
                'purchase_price' => 85.00,
                'selling_price' => 165.00,
                'quantity_in_stock' => 45,
                'minimum_stock' => 10,
                'maximum_stock' => 100,
                'unit' => 'pièce',
                'location' => 'A2-02',
            ],
            [
                'name' => 'Roulement à rouleaux cylindriques NU206 ECP',
                'reference' => 'SKF-NU206-ECP',
                'description' => 'Roulement à rouleaux cylindriques, bague intérieure amovible. Haute précision. Pour broches de machines-outils.',
                'category' => 'roulements-rouleaux',
                'supplier' => 'SKF',
                'purchase_price' => 42.00,
                'selling_price' => 82.00,
                'quantity_in_stock' => 55,
                'minimum_stock' => 15,
                'maximum_stock' => 120,
                'unit' => 'pièce',
                'location' => 'A2-03',
            ],
            [
                'name' => 'Roulement à aiguilles HK2016',
                'reference' => 'INA-HK2016',
                'description' => 'Douille à aiguilles INA avec fond. Encombrement réduit, haute capacité de charge radiale. Pour bielles et leviers.',
                'category' => 'roulements-rouleaux',
                'supplier' => 'INA',
                'purchase_price' => 8.50,
                'selling_price' => 17.00,
                'quantity_in_stock' => 200,
                'minimum_stock' => 50,
                'maximum_stock' => 400,
                'unit' => 'pièce',
                'location' => 'A2-04',
            ],

            // ===== PALIERS ET SUPPORTS =====
            [
                'name' => 'Palier à semelle UCP205',
                'reference' => 'SKF-UCP205',
                'description' => 'Palier à semelle avec roulement insert UC205. Arbre 25mm. Fixation par vis. Graisseur intégré.',
                'category' => 'paliers-supports',
                'supplier' => 'SKF',
                'purchase_price' => 22.00,
                'selling_price' => 44.00,
                'quantity_in_stock' => 85,
                'minimum_stock' => 20,
                'maximum_stock' => 180,
                'unit' => 'pièce',
                'location' => 'A3-01',
            ],
            [
                'name' => 'Palier applique UCFL206',
                'reference' => 'SKF-UCFL206',
                'description' => 'Palier ovale 2 trous avec roulement insert. Arbre 30mm. Montage sur paroi verticale ou horizontale.',
                'category' => 'paliers-supports',
                'supplier' => 'SKF',
                'purchase_price' => 26.00,
                'selling_price' => 52.00,
                'quantity_in_stock' => 70,
                'minimum_stock' => 15,
                'maximum_stock' => 150,
                'unit' => 'pièce',
                'location' => 'A3-02',
            ],
            [
                'name' => 'Palier tendeur UCT208',
                'reference' => 'NTN-UCT208',
                'description' => 'Palier tendeur coulissant NTN. Arbre 40mm. Pour tension de courroies et chaînes. Réglage facile.',
                'category' => 'paliers-supports',
                'supplier' => 'NTN',
                'purchase_price' => 38.00,
                'selling_price' => 75.00,
                'quantity_in_stock' => 40,
                'minimum_stock' => 10,
                'maximum_stock' => 80,
                'unit' => 'pièce',
                'location' => 'A3-03',
            ],

            // ===== COURROIES DE TRANSMISSION =====
            [
                'name' => 'Courroie trapézoïdale SPZ 1250',
                'reference' => 'MDY-SPZ1250',
                'description' => 'Courroie trapézoïdale profil SPZ, longueur 1250mm. Section étroite haute performance. Résistante à l\'huile.',
                'category' => 'courroies',
                'supplier' => 'MDY',
                'purchase_price' => 8.50,
                'selling_price' => 16.90,
                'quantity_in_stock' => 120,
                'minimum_stock' => 30,
                'maximum_stock' => 250,
                'unit' => 'pièce',
                'location' => 'B1-01',
            ],
            [
                'name' => 'Courroie trapézoïdale SPA 1400',
                'reference' => 'MDY-SPA1400',
                'description' => 'Courroie trapézoïdale profil SPA, longueur 1400mm. Pour transmissions industrielles moyennes puissances.',
                'category' => 'courroies',
                'supplier' => 'MDY',
                'purchase_price' => 12.00,
                'selling_price' => 23.90,
                'quantity_in_stock' => 95,
                'minimum_stock' => 25,
                'maximum_stock' => 200,
                'unit' => 'pièce',
                'location' => 'B1-02',
            ],
            [
                'name' => 'Courroie synchrone HTD 5M 450',
                'reference' => 'MDY-HTD5M-450',
                'description' => 'Courroie crantée HTD pas 5mm, développée 450mm. Transmission synchrone sans glissement. Largeur 15mm.',
                'category' => 'courroies',
                'supplier' => 'MDY',
                'purchase_price' => 15.00,
                'selling_price' => 29.90,
                'quantity_in_stock' => 80,
                'minimum_stock' => 20,
                'maximum_stock' => 160,
                'unit' => 'pièce',
                'location' => 'B1-03',
            ],
            [
                'name' => 'Courroie Poly-V PJ 1168',
                'reference' => 'MDY-PJ1168',
                'description' => 'Courroie Poly-V 6 nervures, longueur 1168mm. Compacte, silencieuse. Pour électroménager et automobile.',
                'category' => 'courroies',
                'supplier' => 'MDY',
                'purchase_price' => 18.00,
                'selling_price' => 35.00,
                'quantity_in_stock' => 60,
                'minimum_stock' => 15,
                'maximum_stock' => 120,
                'unit' => 'pièce',
                'location' => 'B1-04',
            ],
            [
                'name' => 'Courroie synchrone T10 800',
                'reference' => 'MDY-T10-800',
                'description' => 'Courroie synchrone pas T10 (10mm), développée 800mm. Pour automatisation et machines CNC.',
                'category' => 'courroies',
                'supplier' => 'MDY',
                'purchase_price' => 22.00,
                'selling_price' => 43.00,
                'quantity_in_stock' => 45,
                'minimum_stock' => 12,
                'maximum_stock' => 90,
                'unit' => 'pièce',
                'location' => 'B1-05',
            ],

            // ===== CHAÎNES INDUSTRIELLES =====
            [
                'name' => 'Chaîne à rouleaux 08B-1 (1m)',
                'reference' => 'SED-08B1-1M',
                'description' => 'Chaîne à rouleaux simple ISO 08B-1, pas 12.7mm. Vendue au mètre. Résistance à la rupture: 18kN.',
                'category' => 'chaines',
                'supplier' => 'SED',
                'purchase_price' => 12.00,
                'selling_price' => 24.00,
                'quantity_in_stock' => 150,
                'minimum_stock' => 40,
                'maximum_stock' => 300,
                'unit' => 'mètre',
                'location' => 'B2-01',
            ],
            [
                'name' => 'Chaîne à rouleaux 10B-1 (1m)',
                'reference' => 'SED-10B1-1M',
                'description' => 'Chaîne à rouleaux simple ISO 10B-1, pas 15.875mm. Vendue au mètre. Résistance à la rupture: 22.2kN.',
                'category' => 'chaines',
                'supplier' => 'SED',
                'purchase_price' => 15.00,
                'selling_price' => 30.00,
                'quantity_in_stock' => 120,
                'minimum_stock' => 35,
                'maximum_stock' => 250,
                'unit' => 'mètre',
                'location' => 'B2-02',
            ],
            [
                'name' => 'Chaîne double 10B-2 (1m)',
                'reference' => 'SED-10B2-1M',
                'description' => 'Chaîne à rouleaux double 10B-2. Double capacité de charge. Pour transmissions lourdes.',
                'category' => 'chaines',
                'supplier' => 'SED',
                'purchase_price' => 28.00,
                'selling_price' => 55.00,
                'quantity_in_stock' => 80,
                'minimum_stock' => 20,
                'maximum_stock' => 160,
                'unit' => 'mètre',
                'location' => 'B2-03',
            ],
            [
                'name' => 'Maillon rapide 10B-1',
                'reference' => 'SED-MR10B1',
                'description' => 'Maillon rapide de jonction pour chaîne 10B-1. Montage sans outil. Pack de 5 pièces.',
                'category' => 'chaines',
                'supplier' => 'SED',
                'purchase_price' => 5.00,
                'selling_price' => 10.00,
                'quantity_in_stock' => 200,
                'minimum_stock' => 50,
                'maximum_stock' => 400,
                'unit' => 'lot',
                'location' => 'B2-04',
            ],

            // ===== JOINTS ET ÉTANCHÉITÉ =====
            [
                'name' => 'Joint à lèvre 35x62x7 NBR',
                'reference' => 'SKF-CR35627',
                'description' => 'Joint radial à lèvre SKF CR, simple lèvre. Nitrile (NBR). Pour arbres tournants. Température -40°C à +100°C.',
                'category' => 'joints',
                'supplier' => 'SKF',
                'purchase_price' => 4.50,
                'selling_price' => 9.90,
                'quantity_in_stock' => 180,
                'minimum_stock' => 40,
                'maximum_stock' => 350,
                'unit' => 'pièce',
                'location' => 'C1-01',
            ],
            [
                'name' => 'Joint à lèvre 50x72x10 FPM',
                'reference' => 'SKF-CR50728V',
                'description' => 'Joint radial double lèvre SKF. Viton (FPM) résistant aux huiles et températures élevées (-25°C à +200°C).',
                'category' => 'joints',
                'supplier' => 'SKF',
                'purchase_price' => 12.00,
                'selling_price' => 24.00,
                'quantity_in_stock' => 100,
                'minimum_stock' => 25,
                'maximum_stock' => 200,
                'unit' => 'pièce',
                'location' => 'C1-02',
            ],
            [
                'name' => 'Joint torique 30x3 NBR',
                'reference' => 'ORM-30X3-NBR',
                'description' => 'Joint torique (O-Ring) diamètre 30mm, section 3mm. Nitrile 70 Shore. Pack de 10 pièces.',
                'category' => 'joints',
                'supplier' => 'SKF',
                'purchase_price' => 3.00,
                'selling_price' => 6.50,
                'quantity_in_stock' => 300,
                'minimum_stock' => 80,
                'maximum_stock' => 600,
                'unit' => 'lot',
                'location' => 'C1-03',
            ],
            [
                'name' => 'Garniture mécanique 20mm',
                'reference' => 'GM-20-CARB-SIC',
                'description' => 'Garniture mécanique simple effet, arbre 20mm. Faces carbone/carbure de silicium. Pour pompes.',
                'category' => 'joints',
                'supplier' => 'SKF',
                'purchase_price' => 45.00,
                'selling_price' => 89.00,
                'quantity_in_stock' => 35,
                'minimum_stock' => 10,
                'maximum_stock' => 70,
                'unit' => 'pièce',
                'location' => 'C1-04',
            ],

            // ===== HYDRAULIQUE =====
            [
                'name' => 'Vérin hydraulique double effet Ø50/30 course 200',
                'reference' => 'HYD-VDE5030-200',
                'description' => 'Vérin hydraulique double effet. Piston Ø50mm, tige Ø30mm, course 200mm. Pression max 250 bar.',
                'category' => 'hydraulique',
                'supplier' => 'SKF',
                'purchase_price' => 180.00,
                'selling_price' => 350.00,
                'quantity_in_stock' => 25,
                'minimum_stock' => 5,
                'maximum_stock' => 50,
                'unit' => 'pièce',
                'location' => 'D1-01',
            ],
            [
                'name' => 'Flexible hydraulique DN10 1000mm',
                'reference' => 'HYD-FLEX10-1M',
                'description' => 'Flexible haute pression DN10 (3/8"), longueur 1000mm. Pression 400 bar. Embouts sertis BSP femelle.',
                'category' => 'hydraulique',
                'supplier' => 'SKF',
                'purchase_price' => 28.00,
                'selling_price' => 55.00,
                'quantity_in_stock' => 60,
                'minimum_stock' => 15,
                'maximum_stock' => 120,
                'unit' => 'pièce',
                'location' => 'D1-02',
            ],
            [
                'name' => 'Filtre hydraulique retour 25µm',
                'reference' => 'HYD-FLT-RET25',
                'description' => 'Élément filtrant pour filtre retour, finesse 25 microns. Débit 60 l/min. Référence universelle.',
                'category' => 'hydraulique',
                'supplier' => 'SKF',
                'purchase_price' => 22.00,
                'selling_price' => 44.00,
                'quantity_in_stock' => 45,
                'minimum_stock' => 12,
                'maximum_stock' => 90,
                'unit' => 'pièce',
                'location' => 'D1-03',
            ],
            [
                'name' => 'Raccord hydraulique droit mâle 1/2 BSP',
                'reference' => 'HYD-RAC-12BSP',
                'description' => 'Raccord droit mâle 1/2" BSP pour tube Ø12mm. Acier zingué. Pression 350 bar.',
                'category' => 'hydraulique',
                'supplier' => 'SKF',
                'purchase_price' => 8.00,
                'selling_price' => 16.00,
                'quantity_in_stock' => 150,
                'minimum_stock' => 40,
                'maximum_stock' => 300,
                'unit' => 'pièce',
                'location' => 'D1-04',
            ],

            // ===== PNEUMATIQUE =====
            [
                'name' => 'Vérin pneumatique ISO Ø32 course 100',
                'reference' => 'PNE-ISO32-100',
                'description' => 'Vérin pneumatique normalisé ISO 15552. Piston Ø32mm, course 100mm. Double effet avec amortissement.',
                'category' => 'pneumatique',
                'supplier' => 'SKF',
                'purchase_price' => 45.00,
                'selling_price' => 89.00,
                'quantity_in_stock' => 40,
                'minimum_stock' => 10,
                'maximum_stock' => 80,
                'unit' => 'pièce',
                'location' => 'D2-01',
            ],
            [
                'name' => 'Électrovanne 5/2 monostable 24VDC',
                'reference' => 'PNE-EV52-24V',
                'description' => 'Électrovanne 5 orifices 2 positions, rappel ressort. Bobine 24V DC. Raccords G1/4.',
                'category' => 'pneumatique',
                'supplier' => 'SKF',
                'purchase_price' => 38.00,
                'selling_price' => 75.00,
                'quantity_in_stock' => 55,
                'minimum_stock' => 15,
                'maximum_stock' => 110,
                'unit' => 'pièce',
                'location' => 'D2-02',
            ],
            [
                'name' => 'Raccord instantané droit Ø8 G1/4',
                'reference' => 'PNE-RID8-14',
                'description' => 'Raccord pneumatique enfichable droit pour tube Ø8mm. Filetage G1/4. Technologie push-in.',
                'category' => 'pneumatique',
                'supplier' => 'SKF',
                'purchase_price' => 2.50,
                'selling_price' => 5.50,
                'quantity_in_stock' => 300,
                'minimum_stock' => 80,
                'maximum_stock' => 600,
                'unit' => 'pièce',
                'location' => 'D2-03',
            ],
            [
                'name' => 'Tube polyuréthane Ø8x5.5 bleu (25m)',
                'reference' => 'PNE-TUBE8-25M',
                'description' => 'Tube pneumatique polyuréthane Ø8mm extérieur, Ø5.5mm intérieur. Bobine 25m. Pression max 10 bar.',
                'category' => 'pneumatique',
                'supplier' => 'SKF',
                'purchase_price' => 25.00,
                'selling_price' => 49.00,
                'quantity_in_stock' => 30,
                'minimum_stock' => 8,
                'maximum_stock' => 60,
                'unit' => 'bobine',
                'location' => 'D2-04',
            ],

            // ===== LUBRIFICATION =====
            [
                'name' => 'Graisse SKF LGMT 2 (1kg)',
                'reference' => 'SKF-LGMT2-1KG',
                'description' => 'Graisse industrielle polyvalente SKF. Base huile minérale, savon lithium. Plage -30°C à +120°C.',
                'category' => 'lubrification',
                'supplier' => 'SKF',
                'purchase_price' => 18.00,
                'selling_price' => 35.00,
                'quantity_in_stock' => 80,
                'minimum_stock' => 20,
                'maximum_stock' => 160,
                'unit' => 'kg',
                'location' => 'E1-01',
            ],
            [
                'name' => 'Graisse haute température LGHP 2 (1kg)',
                'reference' => 'SKF-LGHP2-1KG',
                'description' => 'Graisse SKF pour hautes températures. Polyurée, huile synthétique. Jusqu\'à +150°C en continu.',
                'category' => 'lubrification',
                'supplier' => 'SKF',
                'purchase_price' => 32.00,
                'selling_price' => 62.00,
                'quantity_in_stock' => 50,
                'minimum_stock' => 12,
                'maximum_stock' => 100,
                'unit' => 'kg',
                'location' => 'E1-02',
            ],
            [
                'name' => 'Huile hydraulique HLP 46 (20L)',
                'reference' => 'HYD-HLP46-20L',
                'description' => 'Huile hydraulique ISO VG 46, type HLP. Bidon 20 litres. Anti-usure, anti-oxydation, anti-mousse.',
                'category' => 'lubrification',
                'supplier' => 'SKF',
                'purchase_price' => 55.00,
                'selling_price' => 105.00,
                'quantity_in_stock' => 35,
                'minimum_stock' => 10,
                'maximum_stock' => 70,
                'unit' => 'bidon',
                'location' => 'E1-03',
            ],
            [
                'name' => 'Graisseur automatique LAGD 125',
                'reference' => 'SKF-LAGD125',
                'description' => 'Lubrificateur mono-point automatique SKF. Cartouche 125ml. Durée réglable 1-12 mois.',
                'category' => 'lubrification',
                'supplier' => 'SKF',
                'purchase_price' => 42.00,
                'selling_price' => 82.00,
                'quantity_in_stock' => 45,
                'minimum_stock' => 12,
                'maximum_stock' => 90,
                'unit' => 'pièce',
                'location' => 'E1-04',
            ],

            // ===== TRANSMISSION MÉCANIQUE =====
            [
                'name' => 'Poulie trapézoïdale SPA 2 gorges Ø125',
                'reference' => 'TRM-PL-SPA2-125',
                'description' => 'Poulie en fonte pour courroies SPA. 2 gorges, diamètre primitif 125mm. Moyeu amovible Taper 1610.',
                'category' => 'transmission',
                'supplier' => 'SKF',
                'purchase_price' => 35.00,
                'selling_price' => 69.00,
                'quantity_in_stock' => 30,
                'minimum_stock' => 8,
                'maximum_stock' => 60,
                'unit' => 'pièce',
                'location' => 'F1-01',
            ],
            [
                'name' => 'Pignon simple 10B Z=19 alésage 20',
                'reference' => 'TRM-PIG-10B19',
                'description' => 'Pignon pour chaîne 10B-1. 19 dents, moyeu simple. Alésage 20mm avec rainure de clavette.',
                'category' => 'transmission',
                'supplier' => 'SED',
                'purchase_price' => 18.00,
                'selling_price' => 36.00,
                'quantity_in_stock' => 55,
                'minimum_stock' => 15,
                'maximum_stock' => 110,
                'unit' => 'pièce',
                'location' => 'F1-02',
            ],
            [
                'name' => 'Accouplement élastique Rotex 24',
                'reference' => 'TRM-ROTEX24',
                'description' => 'Accouplement élastique à mâchoires. Couple nominal 65 Nm. Absorbe vibrations et défauts d\'alignement.',
                'category' => 'transmission',
                'supplier' => 'SKF',
                'purchase_price' => 28.00,
                'selling_price' => 55.00,
                'quantity_in_stock' => 40,
                'minimum_stock' => 10,
                'maximum_stock' => 80,
                'unit' => 'pièce',
                'location' => 'F1-03',
            ],
            [
                'name' => 'Moyeu amovible Taper 1610-25',
                'reference' => 'TRM-TB1610-25',
                'description' => 'Moyeu amovible type Taper 1610, alésage 25mm. Pour montage rapide de poulies et pignons.',
                'category' => 'transmission',
                'supplier' => 'SKF',
                'purchase_price' => 12.00,
                'selling_price' => 24.00,
                'quantity_in_stock' => 70,
                'minimum_stock' => 20,
                'maximum_stock' => 140,
                'unit' => 'pièce',
                'location' => 'F1-04',
            ],

            // ===== OUTILLAGE INDUSTRIEL =====
            [
                'name' => 'Extracteur de roulement TMMA 100',
                'reference' => 'SKF-TMMA100',
                'description' => 'Extracteur mécanique SKF à 3 griffes. Capacité 16-100mm. Force extraction 30kN. Qualité professionnelle.',
                'category' => 'outillage',
                'supplier' => 'SKF',
                'purchase_price' => 180.00,
                'selling_price' => 350.00,
                'quantity_in_stock' => 15,
                'minimum_stock' => 3,
                'maximum_stock' => 30,
                'unit' => 'pièce',
                'location' => 'G1-01',
            ],
            [
                'name' => 'Chauffeur à induction TIH 030m',
                'reference' => 'SKF-TIH030M',
                'description' => 'Chauffeur inductif portable SKF. Chauffe roulements jusqu\'à 50kg. Température max 250°C.',
                'category' => 'outillage',
                'supplier' => 'SKF',
                'purchase_price' => 1200.00,
                'selling_price' => 2200.00,
                'quantity_in_stock' => 5,
                'minimum_stock' => 2,
                'maximum_stock' => 10,
                'unit' => 'pièce',
                'location' => 'G1-02',
            ],
            [
                'name' => 'Kit de montage de roulements TMFT 36',
                'reference' => 'SKF-TMFT36',
                'description' => 'Kit complet de montage. 36 bagues d\'impact en aluminium. Pour montage sans chocs sur arbres et logements.',
                'category' => 'outillage',
                'supplier' => 'SKF',
                'purchase_price' => 280.00,
                'selling_price' => 540.00,
                'quantity_in_stock' => 12,
                'minimum_stock' => 3,
                'maximum_stock' => 24,
                'unit' => 'kit',
                'location' => 'G1-03',
            ],

            // ===== MAINTENANCE PRÉDICTIVE =====
            [
                'name' => 'Stéthoscope électronique TMST 3',
                'reference' => 'SKF-TMST3',
                'description' => 'Stéthoscope électronique SKF pour diagnostic acoustique. Détecte défauts de roulements, fuites d\'air.',
                'category' => 'maintenance',
                'supplier' => 'SKF',
                'purchase_price' => 450.00,
                'selling_price' => 850.00,
                'quantity_in_stock' => 8,
                'minimum_stock' => 2,
                'maximum_stock' => 15,
                'unit' => 'pièce',
                'location' => 'H1-01',
            ],
            [
                'name' => 'Stylo vibratoire CMAS 100-SL',
                'reference' => 'SKF-CMAS100',
                'description' => 'Mesureur de vibrations portable. Mesure vitesse vibratoire (mm/s). Écran LCD, alarmes configurables.',
                'category' => 'maintenance',
                'supplier' => 'SKF',
                'purchase_price' => 320.00,
                'selling_price' => 620.00,
                'quantity_in_stock' => 10,
                'minimum_stock' => 3,
                'maximum_stock' => 20,
                'unit' => 'pièce',
                'location' => 'H1-02',
            ],
            [
                'name' => 'Thermomètre infrarouge TKTL 11',
                'reference' => 'SKF-TKTL11',
                'description' => 'Thermomètre laser SKF. Plage -30°C à +350°C. Rapport distance/spot 12:1. Émissivité réglable.',
                'category' => 'maintenance',
                'supplier' => 'SKF',
                'purchase_price' => 85.00,
                'selling_price' => 165.00,
                'quantity_in_stock' => 20,
                'minimum_stock' => 5,
                'maximum_stock' => 40,
                'unit' => 'pièce',
                'location' => 'H1-03',
            ],
        ];

        $bar = $this->command->getOutput()->createProgressBar(count($products));
        $bar->start();

        foreach ($products as $productData) {
            $category = $categories[$productData['category']] ?? null;
            $supplier = $suppliers[$productData['supplier']] ?? null;

            if (!$category || !$supplier) {
                $bar->advance();
                continue;
            }

            Product::updateOrCreate(
                ['reference' => $productData['reference']],
                [
                    'name' => $productData['name'],
                    'description' => $productData['description'],
                    'category_id' => $category->id,
                    'supplier_id' => $supplier->id,
                    'purchase_price' => $productData['purchase_price'],
                    'selling_price' => $productData['selling_price'],
                    'quantity_in_stock' => $productData['quantity_in_stock'],
                    'minimum_stock' => $productData['minimum_stock'],
                    'maximum_stock' => $productData['maximum_stock'],
                    'unit' => $productData['unit'],
                    'location' => $productData['location'],
                    'is_active' => true,
                ]
            );

            $bar->advance();
        }

        $bar->finish();
        $this->command->newLine(2);
        $this->command->info("✅ {$this->count($products)} produits créés/mis à jour");
    }

    protected function count(array $items): int
    {
        return count($items);
    }
}
