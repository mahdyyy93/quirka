<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Service class responsible for communicating with the Printlet Fulfillment API (v1).
 * Handles outgoing HTTP requests for quoting, order creation, COD verification, and event fetching.
 */
class PrintletApiService
{
    protected string $baseUrl;

    protected string $apiKey;

    /**
     * Initialize the Printlet API Service with configuration parameters.
     */
    public function __construct()
    {
        $this->baseUrl = rtrim(config('services.printlet.base_url', 'https://stg-portal.printleteg.com/api/integrations/v1'), '/');
        $this->apiKey = (string) config('services.printlet.api_key', '');
    }

    /**
     * Create an HTTP client instance with authentication and default headers.
     *
     * @param  string|null  $idempotencyKey  Optional idempotency key for POST requests.
     */
    protected function client(?string $idempotencyKey = null): PendingRequest
    {
        $request = Http::baseUrl($this->baseUrl)
            ->withToken($this->apiKey)
            ->acceptJson()
            ->contentType('application/json');

        if ($idempotencyKey) {
            $request->withHeaders([
                'Idempotency-Key' => $idempotencyKey,
            ]);
        }

        return $request;
    }

    /**
     * Send a POST request to create a fulfillment quote.
     * Endpoint: POST /quotes
     *
     * @param  array  $payload  Detailed quote request payload including items, shipping, and market configuration.
     * @return array Returned quote object (contains quote ID, expiration, total fulfillment cost).
     *
     * @throws RequestException
     */
    public function createQuote(array $payload): array
    {
        $idempotencyKey = 'quote-'.Str::uuid();

        Log::info('Sending quote request to Printlet API', [
            'idempotency_key' => $idempotencyKey,
            'merchant_quote_id' => $payload['merchant_quote_id'] ?? null,
        ]);

        $response = $this->client($idempotencyKey)
            ->post('/quotes', $payload);

        if ($response->failed()) {
            Log::error('Printlet createQuote failed', [
                'status' => $response->status(),
                'body' => $response->json() ?? $response->body(),
            ]);

            if ($response->status() === 401 && app()->isLocal() && config('services.printlet.mock_fallback', true)) {
                Log::warning('Printlet API key rejected (401). Falling back to mock quote for local development.');

                return [
                    'id' => 'quote_mock_'.Str::random(12),
                    'status' => 'valid',
                    'totals' => [
                        'items' => 150.00,
                        'shipping' => 45.00,
                        'total' => 195.00,
                    ],
                ];
            }

            $response->throw();
        }

        return $response->json();
    }

    /**
     * Send a POST request to place a fulfillment order.
     * Endpoint: POST /orders
     *
     * @param  array  $payload  Order request payload referencing a valid quote_id and payment method.
     * @return array Created order response data (contains order ID, normalized status).
     *
     * @throws RequestException
     */
    public function createOrder(array $payload): array
    {
        $idempotencyKey = 'order-'.Str::uuid();

        Log::info('Sending order creation request to Printlet API', [
            'idempotency_key' => $idempotencyKey,
            'merchant_order_id' => $payload['merchant_order_id'] ?? null,
            'quote_id' => $payload['quote_id'] ?? null,
        ]);

        $response = $this->client($idempotencyKey)
            ->post('/orders', $payload);

        if ($response->failed()) {
            Log::error('Printlet createOrder failed', [
                'status' => $response->status(),
                'body' => $response->json() ?? $response->body(),
            ]);

            if ($response->status() === 401 && app()->isLocal() && config('services.printlet.mock_fallback', true)) {
                Log::warning('Printlet API key rejected (401). Falling back to mock order placement for local development.');

                return [
                    'id' => 'ord_mock_'.Str::random(12),
                    'status' => ($payload['end_customer_payment']['method'] ?? '') === 'cod' ? 'pending_verification' : 'accepted',
                ];
            }

            $response->throw();
        }

        return $response->json();
    }

    /**
     * Send a POST request to manually verify a Cash on Delivery (COD) order.
     * Endpoint: POST /orders/{id}/cod-verification
     *
     * @param  string  $printletOrderId  The external Printlet order ID.
     * @param  string|null  $reference  Merchant verification reference code.
     * @return array Response payload with updated order status.
     *
     * @throws RequestException
     */
    public function verifyCodOrder(string $printletOrderId, ?string $reference = null): array
    {
        $idempotencyKey = 'cod-verify-'.Str::uuid();

        $payload = [
            'status' => 'verified',
            'reference' => $reference ?? ('VERIFY-'.time()),
        ];

        Log::info('Sending COD verification request to Printlet API', [
            'printlet_order_id' => $printletOrderId,
            'idempotency_key' => $idempotencyKey,
            'reference' => $payload['reference'],
        ]);

        $response = $this->client($idempotencyKey)
            ->post("/orders/{$printletOrderId}/cod-verification", $payload);

        if ($response->failed()) {
            Log::error('Printlet verifyCodOrder failed', [
                'printlet_order_id' => $printletOrderId,
                'status' => $response->status(),
                'body' => $response->json() ?? $response->body(),
            ]);

            if ($response->status() === 401 && app()->isLocal() && config('services.printlet.mock_fallback', true)) {
                Log::warning('Printlet API key rejected (401). Falling back to mock verification for local development.');

                return [
                    'id' => $printletOrderId,
                    'status' => 'verified',
                    'reference' => $payload['reference'],
                ];
            }

            $response->throw();
        }

        return $response->json();
    }

    /**
     * Fetch events feed for cursor-based reconciliation of missed webhooks.
     * Endpoint: GET /events?limit={limit}&after={afterCursor}
     *
     * @param  string|null  $afterCursor  Optional cursor to fetch events occurred after this point.
     * @param  int  $limit  Maximum number of event items to return (default 100).
     * @return array Events list containing 'data' array and 'next_cursor'.
     *
     * @throws RequestException
     */
    public function getEvents(?string $afterCursor = null, int $limit = 100): array
    {
        $queryParams = array_filter([
            'limit' => $limit,
            'after' => $afterCursor,
        ]);

        $response = $this->client()
            ->get('/events', $queryParams);

        if ($response->failed()) {
            Log::error('Printlet getEvents failed', [
                'status' => $response->status(),
                'after' => $afterCursor,
            ]);

            if ($response->status() === 401 && app()->isLocal() && config('services.printlet.mock_fallback', true)) {
                return [
                    'data' => [],
                    'next_cursor' => null,
                ];
            }

            $response->throw();
        }

        return $response->json();
    }
}
