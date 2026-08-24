<?php
namespace App\Services;

use App\Contracts\SupplierSyncServiceInterface;
use Illuminate\Support\Facades\Log;

class MockSupplierSyncService implements SupplierSyncServiceInterface
{
    public function syncProducts(array $data): void
    {
        Log::info('Mocking product sync from supplier', $data);
    }

    public function syncStock(string $sku, int $quantity): void
    {
        Log::info("Mocking stock sync for SKU: {$sku} with quantity: {$quantity}");
    }
}
