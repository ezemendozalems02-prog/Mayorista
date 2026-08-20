<?php

namespace Database\Seeders;

use App\Enums\AccountMovementType;
use App\Enums\StockMovementType;
use App\Enums\UserRole;
use App\Models\Category;
use App\Models\Client;
use App\Models\Organization;
use App\Models\PriceList;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\User;
use App\Services\AccountService;
use App\Services\CashService;
use App\Services\OrderService;
use App\Services\SaleService;
use App\Services\StockService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Datos de referencia para instalar un entorno de Mito Yamile desde cero
     * (dev/staging/CI). No usar en la base de producción real -- esa ya tiene
     * su propia organización, usuarios y catálogo cargados a mano.
     */
    public function run(): void
    {
        $this->call(SubscriptionPlanSeeder::class);

        if (Organization::count() > 0) {
            $this->command?->warn('Ya existe una organización, se omite el seed de datos de referencia.');

            return;
        }

        $org = Organization::create([
            'name' => 'Mito Yamile',
            'slug' => 'mito-yamile',
            'country' => 'Argentina',
            'currency' => 'ARS',
            'is_active' => true,
        ]);

        $password = 'Mito2026!';

        $owner = User::create([
            'organization_id' => $org->id,
            'name' => 'Dueño Mito',
            'email' => 'dueno@mitoyamile.com',
            'password' => Hash::make($password),
            'role' => UserRole::OWNER,
            'is_active' => true,
        ]);
        $manager = User::create([
            'organization_id' => $org->id,
            'name' => 'Encargado Mito',
            'email' => 'encargado@mitoyamile.com',
            'password' => Hash::make($password),
            'role' => UserRole::MANAGER,
            'is_active' => true,
        ]);
        $seller = User::create([
            'organization_id' => $org->id,
            'name' => 'Vendedor Mito',
            'email' => 'vendedor@mitoyamile.com',
            'password' => Hash::make($password),
            'role' => UserRole::SELLER,
            'is_active' => true,
        ]);

        // Necesario para que HasOrganization complete organization_id solo en
        // los modelos creados a partir de aca.
        Auth::login($owner);

        // Categorías reales del rubro (bazar/mayorista), no las de un módulo
        // de otro rubro.
        $categoryNames = [
            'Novedades', 'Artículos de temporada', 'Bazar', 'Ferretería',
            'Indumentaria', 'Inflables', 'Juguetería', 'Librería',
            'Mochilas', 'Regalería', 'Tecnología', 'Varios',
        ];

        $categories = collect($categoryNames)->mapWithKeys(function (string $name) {
            $category = Category::create([
                'name' => $name,
                'slug' => Str::slug($name),
                'is_active' => true,
            ]);

            return [$name => $category];
        });

        $supplier = Supplier::create([
            'business_name' => 'Distribuidora Central S.R.L.',
            'cuit' => '30-71234567-8',
            'phone' => '11-4000-0000',
        ]);

        $stockService = app(StockService::class);

        $products = [
            ['name' => 'Muñeca articulada 30cm', 'category' => 'Juguetería', 'cost' => 4000, 'retail_price' => 8500, 'wholesale_price' => 6200, 'min_stock' => 5, 'stock' => 18],
            ['name' => 'Pelota de fútbol N°5', 'category' => 'Juguetería', 'cost' => 2000, 'retail_price' => 4200, 'wholesale_price' => 3100, 'min_stock' => 8, 'stock' => 3],
            ['name' => 'Cuaderno tapa dura A4 100 hojas', 'category' => 'Librería', 'cost' => 700, 'retail_price' => 1800, 'wholesale_price' => 1200, 'min_stock' => 20, 'stock' => 60],
            ['name' => 'Cartuchera doble', 'category' => 'Librería', 'cost' => 1500, 'retail_price' => 3500, 'wholesale_price' => 2400, 'min_stock' => 10, 'stock' => 25],
            ['name' => 'Taza de cerámica personalizable', 'category' => 'Regalería', 'cost' => 1200, 'retail_price' => 3200, 'wholesale_price' => 2100, 'min_stock' => 6, 'stock' => 14],
            ['name' => 'Vela aromática grande', 'category' => 'Regalería', 'cost' => 1000, 'retail_price' => 2800, 'wholesale_price' => 1900, 'min_stock' => 6, 'stock' => 20],
            ['name' => 'Pelota inflable de playa', 'category' => 'Inflables', 'cost' => 900, 'retail_price' => 2200, 'wholesale_price' => 1500, 'min_stock' => 10, 'stock' => 30],
            ['name' => 'Mochila escolar reforzada', 'category' => 'Mochilas', 'cost' => 5500, 'retail_price' => 12000, 'wholesale_price' => 8800, 'min_stock' => 5, 'stock' => 12],
            ['name' => 'Set de destornilladores x6', 'category' => 'Ferretería', 'cost' => 1800, 'retail_price' => 4500, 'wholesale_price' => 3200, 'min_stock' => 6, 'stock' => 15],
            ['name' => 'Cargador USB-C 20W', 'category' => 'Tecnología', 'cost' => 2500, 'retail_price' => 6000, 'wholesale_price' => 4300, 'min_stock' => 8, 'stock' => 22],
        ];

        $createdProducts = [];
        foreach ($products as $p) {
            $product = Product::create([
                'category_id' => $categories[$p['category']]->id,
                'name' => $p['name'],
                'cost' => $p['cost'],
                'retail_price' => $p['retail_price'],
                'wholesale_price' => $p['wholesale_price'],
                'min_stock' => $p['min_stock'],
            ]);
            $stockService->recordMovement($product, $p['stock'], StockMovementType::INITIAL, notes: 'Carga inicial de referencia');
            $product->suppliers()->attach($supplier->id, ['cost' => $p['cost'], 'is_primary' => true]);
            $createdProducts[] = $product;
        }

        $priceList = PriceList::create([
            'name' => 'Lista Mayorista',
            'description' => 'Precios para clientes mayoristas frecuentes',
            'is_active' => true,
        ]);
        $priceList->items()->create(['product_id' => $createdProducts[0]->id, 'price' => 5900]);

        $clientMayorista = Client::create([
            'full_name' => 'Kiosco La Esquina',
            'business_name' => 'Kiosco La Esquina',
            'client_type' => 'wholesale',
            'phone' => '11-5555-1111',
            'price_list_id' => $priceList->id,
            'credit_limit' => 100000,
        ]);
        $clientMinorista = Client::create([
            'full_name' => 'Rosa Gómez',
            'client_type' => 'retail',
            'phone' => '11-5555-2222',
        ]);

        $saleService = app(SaleService::class);
        $saleService->createSale(
            ['client_id' => $clientMayorista->id, 'payment_method' => 'cash'],
            [
                ['product_id' => $createdProducts[0]->id, 'quantity' => 2],
                ['product_id' => $createdProducts[3]->id, 'quantity' => 5],
            ],
            $seller,
        );

        $accountService = app(AccountService::class);
        $accountService->recordMovement($clientMinorista, 1800, AccountMovementType::SALE, notes: 'Compra a cuenta - referencia');

        $orderService = app(OrderService::class);
        $orderService->create($clientMayorista, [['product_id' => $createdProducts[2]->id, 'quantity' => 30]], $manager, 'Pedido para reponer kiosco');

        $cashService = app(CashService::class);
        $cashService->openSession($org->id, $owner, 5000, 'Apertura de caja de referencia');

        Auth::logout();

        $this->command?->info("Organización '{$org->name}' creada con {$categories->count()} categorías y ".count($createdProducts).' productos de referencia.');
        $this->command?->info("Usuarios (contraseña '{$password}'): dueno@mitoyamile.com, encargado@mitoyamile.com, vendedor@mitoyamile.com");
    }
}
