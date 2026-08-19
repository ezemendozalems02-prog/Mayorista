<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\InventoryItem;
use App\Models\Repair;
use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SearchController extends Controller
{
    public function search(Request $request)
    {
        $query = $request->get('q');
        $orgId = auth()->user()->organization_id;
        $user = auth()->user();

        if (empty($query) || strlen($query) < 2) {
            return response()->json(['results' => []]);
        }

        $results = [];

        // 0. Navegación / Módulos (Filtrado por roles)
        $modules = [
            ['title' => 'Dashboard', 'match' => 'dashboard inicio home', 'route' => 'dashboard', 'roles' => '*', 'icon' => 'layout-dashboard'],
            ['title' => 'Inventario', 'match' => 'inventario stock equipos mercaderia articulos', 'route' => 'inventory.index', 'roles' => ['owner', 'manager', 'technician', 'seller'], 'icon' => 'package'],
            ['title' => 'Ventas', 'match' => 'ventas facturacion ingresos cobrar', 'route' => 'sale.index', 'roles' => ['owner', 'manager', 'seller'], 'icon' => 'shopping-cart'],
            ['title' => 'Reparaciones', 'match' => 'reparaciones servicio tecnico ordenes equipos taller', 'route' => 'repair.index', 'roles' => ['owner', 'manager', 'technician'], 'icon' => 'wrench'],
            ['title' => 'Clientes', 'match' => 'clientes personas compradores contactos', 'route' => 'client.index', 'roles' => ['owner', 'manager', 'seller', 'technician'], 'icon' => 'users'],
            ['title' => 'Staff / Técnicos', 'match' => 'staff tecnicos empleados personal equipo', 'route' => 'technician.index', 'roles' => ['owner', 'manager'], 'icon' => 'users-round'],
            ['title' => 'Reportes', 'match' => 'reportes metricas estadisticas graficos ganancias balances', 'route' => 'report.index', 'roles' => ['owner', 'manager'], 'icon' => 'bar-chart-3'],
            ['title' => 'Configuración Negocio', 'match' => 'configuracion negocio empresa organizacion datos logo perfil', 'route' => 'organization.settings', 'roles' => ['owner', 'manager'], 'icon' => 'settings'],
            ['title' => 'Sucursales', 'match' => 'sucursales locales sedes puntos de venta branches', 'route' => 'branch.index', 'roles' => ['owner', 'manager'], 'icon' => 'map-pin'],
            ['title' => 'Mi Suscripción', 'match' => 'suscripcion plan pagos facturacion premium pro', 'route' => 'subscription.index', 'roles' => ['owner', 'manager'], 'icon' => 'credit-card'],
        ];

        foreach ($modules as $mod) {
            $searchString = strtolower($mod['title'] . ' ' . $mod['match']);
            $queryLower = strtolower($query);
            
            if (strpos($searchString, $queryLower) !== false) {
                // Check roles
                if ($mod['roles'] === '*' || $user->hasRole($mod['roles'])) {
                    $results[] = [
                        'title' => $mod['title'],
                        'subtitle' => 'Ir a este módulo',
                        'url' => route($mod['route']),
                        'icon' => $mod['icon'],
                        'type' => 'Navegación'
                    ];
                }
            }
        }

        // 1. Clientes (Accessible by all except maybe Super Admin? - Restricted to organization)
        if ($user->role !== \App\Enums\UserRole::SUPER_ADMIN) {
            $clients = Client::where('organization_id', $orgId)
                ->where(function($q) use ($query) {
                    $q->where('full_name', 'like', "%{$query}%")
                      ->orWhere('phone', 'like', "%{$query}%");
                })
                ->limit(5)
                ->get();
            
            foreach ($clients as $client) {
                $results[] = [
                    'title' => $client->full_name,
                    'subtitle' => 'Cliente • ' . ($client->phone ?? 'Sin teléfono'),
                    'url' => route('client.index', ['search' => $client->full_name]),
                    'icon' => 'users',
                    'type' => 'Cliente'
                ];
            }
        }

        // 2. Inventario (Accessible by owner, manager, technician, seller)
        if ($user->hasRole([\App\Enums\UserRole::OWNER, \App\Enums\UserRole::MANAGER, \App\Enums\UserRole::TECHNICIAN, \App\Enums\UserRole::SELLER])) {
            $items = InventoryItem::where('organization_id', $orgId)
                ->where(function($q) use ($query) {
                    $q->where('model', 'like', "%{$query}%")
                      ->orWhere('imei', 'like', "%{$query}%")
                      ->orWhere('serial_number', 'like', "%{$query}%")
                      ->orWhere('brand', 'like', "%{$query}%");
                })
                ->limit(5)
                ->get();

            foreach ($items as $item) {
                $status = $item->status instanceof \UnitEnum ? $item->status->value : $item->status;
                $results[] = [
                    'title' => "{$item->brand} {$item->model}",
                    'subtitle' => "Inv • {$item->imei} • {$status}",
                    'url' => route('inventory.index', ['search' => $item->imei]),
                    'icon' => 'package',
                    'type' => 'Stock'
                ];
            }
        }

        // 3. Reparaciones (Owner, Manager, Technician)
        if ($user->hasRole([\App\Enums\UserRole::OWNER, \App\Enums\UserRole::MANAGER, \App\Enums\UserRole::TECHNICIAN])) {
            $repairs = Repair::where('organization_id', $orgId)
                ->where(function($q) use ($query) {
                    $q->where('repair_number', 'like', "%{$query}%")
                      ->orWhere('device_model', 'like', "%{$query}%");
                })
                ->limit(5)
                ->get();

            foreach ($repairs as $repair) {
                $status = $repair->status instanceof \UnitEnum ? $repair->status->value : $repair->status;
                $results[] = [
                    'title' => "Reparación #{$repair->repair_number}",
                    'subtitle' => "{$repair->device_model} • {$status}",
                    'url' => route('repair.index', ['search' => $repair->repair_number]),
                    'icon' => 'wrench',
                    'type' => 'Reparación'
                ];
            }
        }

        // 4. Ventas (Owner, Manager, Seller)
        if ($user->hasRole([\App\Enums\UserRole::OWNER, \App\Enums\UserRole::MANAGER, \App\Enums\UserRole::SELLER])) {
            $sales = Sale::where('organization_id', $orgId)
                ->where('sale_number', 'like', "%{$query}%")
                ->limit(5)
                ->get();

            foreach ($sales as $sale) {
                $results[] = [
                    'title' => "Venta #{$sale->sale_number}",
                    'subtitle' => "Total: USD " . number_format($sale->total, 2) . " • {$sale->status}",
                    'url' => route('sale.index', ['search' => $sale->sale_number]),
                    'icon' => 'shopping-cart',
                    'type' => 'Venta'
                ];
            }
        }

        return response()->json(['results' => $results]);
    }
}
