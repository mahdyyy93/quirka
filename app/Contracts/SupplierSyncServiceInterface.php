<?php
namespace App\Contracts;

interface SupplierSyncServiceInterface
{
    public function syncProducts(array $data): void;
    public function syncStock(string $sku, int $quantity): void;
}
