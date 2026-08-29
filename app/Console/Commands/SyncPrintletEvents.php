<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Services\PrintletApiService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Artisan command designed to run on a scheduled cron job.
 * Polls the Printlet GET /events cursor feed to fetch missed lifecycle events
 * and reconcile order states with the local database.
 */
class SyncPrintletEvents extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'printlet:sync-events {--reset-cursor : Reset the stored cursor and sync from start}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Poll Printlet GET /events cursor feed to reconcile missed order lifecycle updates.';

    /**
     * Cache key used for storing the last processed event cursor.
     */
    protected const CURSOR_CACHE_KEY = 'printlet_events_last_cursor';

    /**
     * Execute the console command.
     *
     * @return int Exit code
     */
    public function handle(PrintletApiService $printletApi): int
    {
        $this->info('Starting Printlet events sync cron job...');

        if ($this->option('reset-cursor')) {
            Cache::forget(self::CURSOR_CACHE_KEY);
            $this->warn('Event cursor has been reset.');
        }

        $cursor = Cache::get(self::CURSOR_CACHE_KEY);

        $this->info('Current event cursor: '.($cursor ?? 'NONE (fetching from start)'));

        $totalProcessed = 0;
        $hasMore = true;

        try {
            while ($hasMore) {
                // Fetch next page of events from Printlet API
                $response = $printletApi->getEvents($cursor, limit: 100);

                $events = $response['data'] ?? [];
                $nextCursor = $response['next_cursor'] ?? null;

                if (empty($events)) {
                    $this->info('No new events found.');
                    break;
                }

                $this->info(sprintf('Fetched %d event(s) from Printlet.', count($events)));

                foreach ($events as $eventItem) {
                    $this->reconcileEvent($eventItem);
                    $totalProcessed++;
                }

                // If next cursor exists and is different from current, update cursor
                if ($nextCursor && $nextCursor !== $cursor) {
                    $cursor = $nextCursor;
                    Cache::forever(self::CURSOR_CACHE_KEY, $cursor);
                    $this->line('Updated cursor to: '.$cursor);
                } else {
                    $hasMore = false;
                }
            }

            $this->info(sprintf('Printlet events sync completed successfully. Total processed: %d', $totalProcessed));

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('Printlet events sync failed: '.$e->getMessage());
            Log::error('SyncPrintletEvents command failed', [
                'cursor' => $cursor,
                'error' => $e->getMessage(),
            ]);

            return Command::FAILURE;
        }
    }

    /**
     * Reconcile an individual event item with the local database.
     */
    protected function reconcileEvent(array $eventItem): void
    {
        $eventType = $eventItem['type'] ?? $eventItem['event'] ?? '';
        $resource = $eventItem['data'] ?? $eventItem['resource'] ?? [];
        $printletOrderId = $resource['id'] ?? $resource['order_id'] ?? $eventItem['resource_id'] ?? null;

        if (! $printletOrderId || ! str_starts_with($eventType, 'order.')) {
            return;
        }

        $order = Order::where('printlet_order_id', $printletOrderId)->first();

        if (! $order) {
            return;
        }

        $statusMapping = [
            'order.pending_verification' => ['printlet_status' => 'pending_verification', 'status' => 'pending_verification'],
            'order.accepted' => ['printlet_status' => 'accepted', 'status' => 'processing', 'payment_status' => 'paid'],
            'order.in_production' => ['printlet_status' => 'in_production', 'status' => 'in_production'],
            'order.shipped' => ['printlet_status' => 'shipped', 'status' => 'shipped'],
            'order.delivered' => ['printlet_status' => 'delivered', 'status' => 'completed'],
            'order.cancelled' => ['printlet_status' => 'cancelled', 'status' => 'cancelled'],
            'order.payment_required' => ['printlet_status' => 'payment_required', 'status' => 'on_hold'],
        ];

        if (isset($statusMapping[$eventType])) {
            $updates = $statusMapping[$eventType];
            $order->update($updates);

            $this->line(sprintf('Reconciled Order #%d (Printlet ID: %s) -> Status: %s', $order->id, $printletOrderId, $updates['printlet_status']));
        }
    }
}
