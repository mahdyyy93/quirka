<?php

namespace App\Services;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Route as RouteFacade;
use Illuminate\Support\Str;
use Spatie\Permission\PermissionRegistrar;

class RoutePermissionSynchronizer
{
    /**
     * Patterns of route names or URIs to ignore when generating permissions.
     */
    protected array $ignorePatterns = [
        'debugbar.*',
        'ignition.*',
        'sanctum.*',
        'livewire.*',
        'flux.*',
        'webhooks.*',
        '_boost.*',
    ];

    /**
     * Scan registered routes and sync guarded ones to the permissions table.
     * Permission name matches the route name (or permission middleware parameter).
     *
     * @return array{created: array<string>, total_scanned: int}
     */
    public function sync(): array
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $routes = RouteFacade::getRoutes()->getRoutes();
        $created = [];
        $scannedCount = 0;

        foreach ($routes as $route) {
            $permissionData = $this->extractGuardedPermissionData($route);

            if (! $permissionData) {
                continue;
            }

            $scannedCount++;
            $permissionName = $permissionData['name'];

            $existing = Permission::where('name', $permissionName)->first();

            if (! $existing) {
                Permission::create([
                    'name' => $permissionName,
                    'guard_name' => 'web',
                    'group_name' => $permissionData['group_name'],
                ]);

                $created[] = $permissionName;
            }
        }

        // Always ensure admin role keeps all permissions up-to-date
        $adminRole = Role::where('name', 'admin')->first();
        if ($adminRole && count($created) > 0) {
            $adminRole->syncPermissions(Permission::all());
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        return [
            'created' => $created,
            'total_scanned' => $scannedCount,
        ];
    }

    /**
     * Inspect route middleware to check if route is guarded by permission: or can:
     * and extract permission name matching the route name.
     */
    protected function extractGuardedPermissionData(Route $route): ?array
    {
        $name = $route->getName();

        // Check if ignored pattern
        if ($name) {
            foreach ($this->ignorePatterns as $pattern) {
                if (Str::is($pattern, $name)) {
                    return null;
                }
            }
        }

        $middleware = $route->gatherMiddleware();
        $permissionName = null;

        foreach ($middleware as $m) {
            if (is_string($m)) {
                // If route uses permission:name or can:name middleware
                if (Str::startsWith($m, ['permission:', 'can:'])) {
                    $parts = explode(':', $m, 2);
                    $permissionName = $parts[1] ?? null;
                    break;
                }
            }
        }

        // If route does not have a permission/can middleware guard, skip it
        if (! $permissionName) {
            return null;
        }

        // Standardize permission name (matches route name when defined)
        $permissionName = $name ?: $permissionName;

        // Derive group name from permission/route name without role prefixes
        $groupName = $this->determineGroupName($permissionName);

        return [
            'name' => $permissionName,
            'group_name' => $groupName,
        ];
    }

    /**
     * Derive a human-readable module group without role names.
     */
    protected function determineGroupName(string $permissionName): string
    {
        $parts = explode('.', $permissionName);
        $first = $parts[0];

        return match (strtolower($first)) {
            'products' => 'Products',
            'orders' => 'Orders',
            'customers' => 'Customers',
            'roles' => 'Roles & Permissions',
            'permissions' => 'Roles & Permissions',
            'pricing-limits' => 'Pricing Limits',
            'revenue-report' => 'Reports',
            'artist' => 'Artist Pricing',
            'checkout' => 'Checkout',
            'cart' => 'Cart',
            'profile' => 'Profile',
            default => ucfirst(str_replace(['-', '_'], ' ', $first)),
        };
    }
}
