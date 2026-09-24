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

    /**
     * Discover supported fulfillment countries.
     * Endpoint: GET /catalog/countries
     *
     * @return array List of countries with code, name, and stable country_id.
     *
     * @throws RequestException
     */
    public function getCountries(): array
    {
        $response = $this->client()->get('/catalog/countries');

        if ($response->failed()) {
            Log::error('Printlet getCountries failed', ['status' => $response->status()]);

            if ($response->status() === 401 && app()->isLocal() && config('services.printlet.mock_fallback', true)) {
                return [
                    'data' => [
                        [
                            'id' => config('services.printlet.country_id', 'c1839db2-37eb-4b21-82ea-b248aeef1e97'),
                            'code' => 'EG',
                            'name' => 'Egypt',
                            'currency' => 'EGP',
                        ],
                    ],
                ];
            }

            $response->throw();
        }

        return $response->json();
    }

    /**
     * Discover shipping methods for a specific country.
     * Endpoint: GET /catalog/shipping-methods?country_id={country_id}
     *
     * @param  string  $countryId  Stable Printlet country ID.
     * @return array List of shipping methods with ID, name, cost, and delivery estimates.
     *
     * @throws RequestException
     */
    public function getShippingMethods(string $countryId): array
    {
        $response = $this->client()->get('/catalog/shipping-methods', ['country_id' => $countryId]);

        if ($response->failed()) {
            Log::error('Printlet getShippingMethods failed', ['status' => $response->status(), 'country_id' => $countryId]);

            if ($response->status() === 401 && app()->isLocal() && config('services.printlet.mock_fallback', true)) {
                return [
                    'data' => [
                        [
                            'id' => 'sm_standard',
                            'name' => 'Standard Doorstep Delivery (Cairo & Cities)',
                            'cost' => 45.00,
                            'estimated_days' => '2-4 business days',
                        ],
                    ],
                ];
            }

            $response->throw();
        }

        return $response->json();
    }

    /**
     * Discover catalog products available in a destination country.
     * Endpoint: GET /catalog/products?country_id={country_id}
     *
     * @param  string  $countryId  Stable Printlet country ID.
     * @return array List of products with IDs, titles, and variant summaries.
     *
     * @throws RequestException
     */
    public function getProducts(string $countryId): array
    {
        $response = $this->client()->get('/catalog/products', ['country_id' => $countryId]);

        if ($response->failed()) {
            Log::error('Printlet getProducts failed', ['status' => $response->status(), 'country_id' => $countryId]);

            if ($response->status() === 401 && app()->isLocal() && config('services.printlet.mock_fallback', true)) {
                return [
                    'data' => [
                        [
                            'id' => 'prod_framed_poster',
                            'title' => 'Framed Poster',
                            'status' => 'active',
                            'catalog_revision' => 1,
                        ],
                    ],
                ];
            }

            $response->throw();
        }

        return $response->json();
    }

    /**
     * Get detailed product configuration (variants, print areas, techniques, placement presets).
     * Endpoint: GET /catalog/products/{product_id}?country_id={country_id}
     *
     * @param  string  $productId  Stable Printlet product ID.
     * @param  string  $countryId  Stable Printlet country ID.
     * @return array Full product schema containing variants, print areas, and techniques.
     *
     * @throws RequestException
     */
    public function getProduct(string $productId, string $countryId): array
    {
        $response = $this->client()->get("/catalog/products/{$productId}", ['country_id' => $countryId]);

        if ($response->failed()) {
            Log::error('Printlet getProduct failed', ['status' => $response->status(), 'product_id' => $productId]);

            if ($response->status() === 401 && app()->isLocal() && config('services.printlet.mock_fallback', true)) {
                return [
                    'id' => $productId,
                    'title' => 'Framed Poster',
                    'variants' => [
                        ['id' => 'v_a3_black', 'name' => 'A3 / Black Frame', 'sku' => 'POSTER-A3-BLK', 'available' => true],
                        ['id' => 'v_a3_white', 'name' => 'A3 / White Frame', 'sku' => 'POSTER-A3-WHT', 'available' => true],
                        ['id' => 'v_a4_black', 'name' => 'A4 / Black Frame', 'sku' => 'POSTER-A4-BLK', 'available' => true],
                        ['id' => 'v_a4_white', 'name' => 'A4 / White Frame', 'sku' => 'POSTER-A4-WHT', 'available' => true],
                    ],
                    'print_areas' => [
                        ['id' => 'pa_front', 'name' => 'Front Print Area'],
                    ],
                    'techniques' => [
                        ['id' => 'tech_digital_print', 'name' => 'Fine Art Digital Printing'],
                    ],
                    'placement_presets' => ['centered', 'fill'],
                ];
            }

            $response->throw();
        }

        return $response->json();
    }
}
