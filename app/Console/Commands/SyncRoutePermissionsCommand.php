<?php

namespace App\Console\Commands;

use App\Services\RoutePermissionSynchronizer;
use Illuminate\Console\Command;

class SyncRoutePermissionsCommand extends Command
{
    protected $signature = 'permissions:sync-routes';

    protected $description = 'Scan registered application routes and populate missing permissions into the database.';

    public function handle(RoutePermissionSynchronizer $synchronizer): int
    {
        $this->info('Scanning routes for permissions...');

        $result = $synchronizer->sync();
        $created = $result['created'];
        $total = $result['total_scanned'];

        if (empty($created)) {
            $this->info("All permissions are up to date! Scanned {$total} routes; no new permissions were added.");
        } else {
            $this->info('Successfully added '.count($created)." new permission(s) across {$total} routes scanned:");
            foreach ($created as $perm) {
                $this->line(" - <comment>{$perm}</comment>");
            }
        }

        return self::SUCCESS;
    }
}
