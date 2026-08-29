# Printlet API (v1) Integration Task List

- [x] **1. Configuration & Services Setup**
  - [x] Add `printlet` array to `config/services.php` (`base_url`, `api_key`, `webhook_secret`, default market/shipping parameters).

- [x] **2. Database Migrations**
  - [x] Create `2026_08_29_170000_add_printlet_artwork_id_to_products_table.php`
  - [x] Create `2026_08_29_170001_create_variants_table.php`
  - [x] Create `2026_08_29_170002_add_printlet_fields_to_orders_table.php`

- [x] **3. Models Implementation**
  - [x] Update `app/Models/Product.php` (add `printlet_artwork_id` to `$fillable`, add `variants()` relation)
  - [x] Create `app/Models/Variant.php` (fields: `product_id`, `size`, `color`, `price`, `sku`, `printlet_variant_id`, etc.)
  - [x] Update `app/Models/Order.php` (add printlet fields to `$fillable` & `$casts`)

- [x] **4. PrintletApiService Implementation**
  - [x] Create `app/Services/PrintletApiService.php`
    - [x] `createQuote(array $data)` -> `POST /quotes`
    - [x] `createOrder(array $data)` -> `POST /orders`
    - [x] `verifyCodOrder(string $printletOrderId, ?string $reference = null)` -> `POST /orders/{id}/cod-verification`
    - [x] `getEvents(?string $afterCursor = null, int $limit = 100)` -> `GET /events`

- [x] **5. Controllers Implementation**
  - [x] Refactor `app/Http/Controllers/CheckoutController.php` (JIT quote + immediate order creation for prepaid/COD)
  - [x] Create `app/Http/Controllers/AdminOrderController.php` (`verifyCod` method)
  - [x] Create `app/Http/Controllers/WebhookController.php` (`handle` method + `verifySignature` method)

- [x] **6. Console Command Implementation**
  - [x] Create `app/Console/Commands/SyncPrintletEvents.php` (`printlet:sync-events` command using cursor feed)

- [x] **7. Route Registration & Verification**
  - [x] Register routes for checkout, admin COD verification, and Printlet webhook endpoint.
  - [x] Exclude `/webhooks/printlet` from CSRF in `bootstrap/app.php`.
  - [x] Verify PHP syntax.
