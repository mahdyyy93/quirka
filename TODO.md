# Quirka — Production Readiness & Trust Audit

> **Platform:** Laravel 13 · Livewire 4 · SQLite/PostgreSQL · Tailwind CSS v4  
> **Last updated:** 2026-08-29  
> **Status:** Printlet v1 Integration Complete · Production Polish In Progress

---

## Priority Legend

| Symbol | Meaning |
|--------|---------|
| 🔴 **P0** | Blocking release — critical security, legal, or payment requirement |
| 🟠 **P1** | High priority — strong UX/trust signal, high conversion impact |
| 🟡 **P2** | Polish & reassurance — branding, pages, state persistence |
| 🟢 **P3** | Nice-to-have — advanced features |

---

## 1. 🔴 P0 — Infrastructure & Production Deployment Setup

- [ ] **Force HTTPS in Production**
  - Enforce `URL::forceScheme('https')` in `AppServiceProvider` when `app()->isProduction()`.
  - Set `APP_URL=https://yourdomain.com` in production `.env`.
- [x] **Security Headers Middleware**
  - Added `App\Http\Middleware\SecurityHeaders` returning `X-Frame-Options`, `X-Content-Type-Options`, `Referrer-Policy`.
- [x] **Remove Debug Routes**
  - Removed unauthenticated `/php-info` debug route from `routes/web.php`.
- [ ] **Production Optimization Commands**
  - Run `php artisan config:cache`, `php artisan route:cache`, `php artisan view:cache` on deployment.
  - Set `APP_DEBUG=false` in production `.env`.

---

## 2. 🔴 P0 — Printlet Fulfillment Integration (v1)

- [x] **Database Schema & Models**
  - [x] Added `printlet_artwork_id` to `products` table (`2026_08_29_170000_add_printlet_artwork_id_to_products_table.php`).
  - [x] Created `variants` table mapping size, color, price, sku, and `printlet_variant_id` (`2026_08_29_170001_create_variants_table.php`).
  - [x] Added `printlet_order_id`, `printlet_quote_id`, `printlet_status`, `cod_reference`, `shipping_amount` to `orders` table.
- [x] **Printlet API Service (`PrintletApiService`)**
  - [x] Implemented `POST /quotes`, `POST /orders`, `POST /orders/{id}/cod-verification`, and `GET /events` cursor feed.
  - [x] Added automatic Bearer token auth and UUID `Idempotency-Key` headers.
- [x] **Just-in-Time Checkout Flow (`CheckoutController`)**
  - [x] Hits `POST /quotes` during checkout submission to calculate fulfillment & shipping costs.
  - [x] Immediately places order via `POST /orders`.
  - [x] Supports `prepaid` and `cod` (leaving COD in `pending_verification` state).
  - [x] Wrapped in DB transaction.
- [x] **Admin COD Order Verification**
  - [x] Implemented `AdminOrderController::verifyCod` (`POST /admin/orders/{order}/verify-cod`).
  - [x] Updated Admin Orders UI (`resources/views/orders.blade.php`) with Printlet metadata and interactive `✓ Verify COD Order` banner.
- [x] **Webhooks (`WebhookController`)**
  - [x] Implemented `POST /webhooks/printlet` endpoint handling `order.shipped`, `order.in_production`, `order.delivered`, `order.cancelled`.
  - [x] Implemented HMAC-SHA256 signature verification in `verifySignature()`.
- [x] **Resiliency & Event Sync (`SyncPrintletEvents`)**
  - [x] Created scheduled Artisan command `php artisan printlet:sync-events` querying cursor feed to reconcile missed state updates.

---

## 3. 🔴 P0 — Legal Pages & Customer Trust

- [x] **Legal Pages (Footer Links)**
  - [x] Privacy Policy page (`/privacy`) — Pre-designed catalog policy + Printlet 3rd-party manufacturing data sharing clause.
  - [x] Terms of Service page (`/terms`) — Pre-designed catalog terms, pre-processing cancellation window, and 60-day COD storage/abandonment policy.
  - [x] Return & Refund Policy page (`/returns`) — 14-day defect/transit damage replacement guarantee for catalog items.
  - [x] Shipping & Delivery Policy page (`/shipping`) — Pre-designed catalog fulfillment, Sun-Thu business days, 1-2d processing, 1-3d transit, 3 delivery attempts max.
- [x] **Customer Order Tracking & History**
  - [x] Added customer-facing `/my-orders` list and `/my-orders/{order}` detail page with order status timeline.
- [x] **Transaction Proof / Email**
  - [x] Created `OrderConfirmed` Mailable queued on checkout.

---

## 4. 🟠 P1 — Product Catalog & Inventory Polish

- [ ] **Product Detail Page (`/shop/{product}`)**
  - Dedicated product detail page allowing variant selection (size & frame color) before adding to cart.
- [ ] **Product Descriptions**
  - Surface detailed product specs, dimensions, and frame descriptions.
- [ ] **Stock Level Validation**
  - Surfacing inventory availability ("In Stock", "Low Stock") and atomic stock decrement on checkout.

---

## 5. 🟡 P2 — Storefront Polish & Brand Identity

- [ ] **Custom Brand Favicon**
  - Commit favicon assets (`favicon.ico`, `favicon.svg`, `apple-touch-icon.png`).
- [ ] **Storefront Landing Page**
  - Custom brand hero section replacing default welcome screen.
- [ ] **About Us & Contact Page**
  - `/about` story page and `/contact` support form.

---

## Summary Status Table

| # | Feature / Component | Priority | Status |
|---|---------------------|----------|--------|
| 1 | Force HTTPS in production | 🔴 P0 | [ ] Pending |
| 2 | Legal pages (Privacy, ToS, Returns, Shipping) | 🔴 P0 | [x] **Completed** |
| 3 | Printlet API Integration (v1) | 🔴 P0 | [x] **Completed** |
| 4 | Customer order tracking (`/my-orders`) | 🔴 P0 | [x] **Completed** |
| 5 | Transactional email confirmation | 🔴 P0 | [x] **Completed** |
| 6 | Security headers middleware | 🟠 P1 | [x] **Completed** |
| 7 | Fix `Order::$fillable` | 🟠 P1 | [x] **Completed** |
| 8 | Remove `/php-info` debug route | 🟠 P1 | [x] **Completed** |
| 9 | Product detail page (`/shop/{product}`) | 🟠 P1 | [ ] Pending |
| 10 | Stock validation & low stock indicators | 🟠 P1 | [ ] Pending |
| 11 | Custom favicon & brand identity | 🟡 P2 | [ ] Pending |
| 12 | About & Contact pages | 🟡 P2 | [ ] Pending |
