# Quirka — Customer Trust Audit

> **Platform:** Laravel 13 · Livewire 4 · SQLite · Tailwind CSS v4
> **Audit date:** 2026-08-22
> **Reviewer:** Antigravity

---

## Executive Summary

Quirka is a hyper-lean e-commerce platform with a solid technical foundation: proper CSRF on all forms, auth-gated purchasing, an `is_available` product flag, and a DB-transaction-wrapped checkout. However, several critical signals that **customers judge a store's legitimacy by** are entirely absent. This document catalogs every gap with a priority rating and concrete fix direction.

---

## Priority Legend

| Symbol | Meaning |
|--------|---------|
| 🔴 **P0** | Blocking trust — customers will likely abandon without this |
| 🟠 **P1** | Strong trust signal — high-impact, relatively quick to add |
| 🟡 **P2** | Moderate trust signal — adds polish and reassurance |
| 🟢 **P3** | Nice-to-have — differentiates from competitors |

---

## 1. 🔴 P0 — HTTPS / SSL Certificate

**Current state:** No HTTPS enforcement found anywhere in the codebase (`routes/web.php`, middleware, `.env`).

**Why it kills trust:** Every modern browser shows "Not Secure" on HTTP. Customers see this before they even read the product names.

**Fix:**
- Force HTTPS in `AppServiceProvider` (or `TrustProxies` if behind a load balancer):
  ```php
  // AppServiceProvider::boot()
  if (app()->isProduction()) {
      URL::forceScheme('https');
  }
  ```
- Set `FORCE_HTTPS=true` in production `.env` and handle at the web server/reverse-proxy level.

---

## 2. 🔴 P0 — No Legal Pages (Privacy Policy, Terms of Service, Return Policy)

**Current state:** Zero legal pages exist. No links in any layout. The `welcome.blade.php` footer is empty.

**Why it kills trust:** Customers actively look for these before buying. Their absence is a red flag for scam sites. Payment processors (Stripe, PayPal) also *require* them.

**Fix — minimum viable set:**

| Page | Route | Content |
|------|-------|---------|
| Privacy Policy | `/privacy` | How you store/use personal data |
| Terms of Service | `/terms` | Purchase rules, disclaimers |
| Return & Refund Policy | `/returns` | Who pays return shipping, refund timeline |
| Shipping Policy | `/shipping` | Estimated delivery times, carriers |

Add to a shared footer component visible on every page.

---

## 3. 🔴 P0 — Only One Payment Method (Cash on Delivery)

**Current state:** `CashOnDeliveryPayment.php` is the only payment driver registered.

**Why it kills trust:**
- COD-only stores are a known vector for fraud — customers know this.
- International customers cannot buy at all.
- No card payment means no Stripe/PayPal trust badge on the checkout page.

**Fix:**
- Integrate **Stripe** (or PayPal/Paymob for MENA) using the existing `PaymentMethodInterface` contract — the architecture already supports it.
- Display the provider's official trust badge on the checkout page.
- Add card logos (Visa, Mastercard) in the footer.

---

## 4. 🔴 P0 — No Order Tracking for Customers

**Current state:** After `order-confirmation.blade.php`, customers have no way to view their order history or check status. There is no `/orders` customer-facing route — only an admin one.

**Why it kills trust:** "Where is my order?" is the #1 support ticket for e-commerce. Without self-service tracking, customers feel they've sent money into a void.

**Fix:**
- Add a `/my-orders` route returning a customer's own orders list.
- Add a `/my-orders/{order}` detail page with status timeline.
- Note: `Order::$fillable` currently only includes `customer_id`, `status`, `total_price` — `payment_method`, `payment_status`, and `notes` are missing from `$fillable` (they will be silently ignored by mass-assignment). **Fix this immediately.**

---

## 5. 🔴 P0 — No Transactional Email (Order Confirmation Email)

**Current state:** After `CheckoutController::store()` completes, no email is sent. The customer gets a success screen and nothing else.

**Why it kills trust:** An email with the order number is proof-of-purchase. Without it, customers have no receipt, and chargebacks are harder to dispute.

**Fix:**
- Create an `OrderConfirmed` Mailable and dispatch it in `CheckoutController::store()` after the transaction.
- Minimum email contents: order number, item list, total, payment method, estimated delivery note.
- Configure `MAIL_MAILER` in `.env` (Resend, Mailgun, SES).

---

## 6. 🟠 P1 — No Product Descriptions

**Current state:** `Product` model has `title`, `sku`, `price`, `is_available`, `customizations` — **no `description` field**. The `shop.blade.php` card shows only the title and price.

**Why it matters:** Customers make purchase decisions based on product details. No description = no differentiation = no confidence they're buying the right thing.

**Fix:**
- Add `description` (text) and optionally `short_description` (varchar 255) to the products migration.
- Render `short_description` on the product card and full `description` on a dedicated product detail page (`/shop/{product}`).

---

## 7. 🟠 P1 — No Product Detail Page

**Current state:** There is no `/shop/{product}` route. Customers can only see the card in the grid; there is no way to see more images, specs, or description.

**Why it matters:** Buying without seeing product detail is a barrier. Multiple images and specs are prerequisites for high-AOV products.

**Fix:**
- Add `ShopController::show(Product $product)` method.
- Create a `shop/show.blade.php` with an image gallery (using `$product->images`), full description, customization options, and the Add-to-Cart form.

---

## 8. 🟠 P1 — Missing `$fillable` Fields on Order Model

**Current state:** `Order::$fillable` is:
```php
protected $fillable = ['customer_id', 'status', 'total_price'];
```
But `CheckoutController::store()` calls `Order::create([..., 'payment_method', 'payment_status', 'notes'])`. Those 3 fields are silently dropped by Eloquent.

**Why it matters:** Orders are missing payment data. The admin panel shows incomplete data and trust-critical features like "payment status" don't work.

**Fix — immediate, 1 line:**
```php
protected $fillable = [
    'customer_id', 'status', 'total_price',
    'payment_method', 'payment_status', 'notes',
];
```

---

## 9. 🟠 P1 — No Social Proof (Reviews / Ratings)

**Current state:** Zero review or rating functionality exists.

**Why it matters:** 93% of shoppers read reviews before buying (BrightLocal 2024). An empty review section signals a new/untested store.

**Fix:**
- Add a `reviews` table (`product_id`, `customer_id`, `rating`, `body`, `approved_at`).
- Display average star rating on product cards and individual review comments on the detail page.
- Gate submission to verified purchasers (customer has a delivered `OrderItem` for that product).

---

## 10. 🟠 P1 — No Stock/Inventory Feedback

**Current state:** `StockLevel` model exists but is never surfaced to customers. There is no "Only 3 left!" message, no out-of-stock indicator, and no validation preventing over-ordering in `CheckoutController`.

**Why it matters:** Customers who order an item that turns out to be out of stock lose trust permanently.

**Fix:**
- Show `stock_quantity` on product cards ("In Stock" / "Low Stock" / "Out of Stock").
- Validate available stock in `CheckoutController::store()` before committing the transaction.
- Decrement stock atomically inside the existing DB transaction.

---

## 11. 🟠 P1 — No Security Headers

**Current state:** No `Content-Security-Policy`, `X-Frame-Options`, `X-Content-Type-Options`, or `Referrer-Policy` headers are set.

**Why it matters:** Security headers are inspected by browser security tools (and technically-savvy customers). Their absence also enables clickjacking.

**Fix:**
```php
// In a new SecurityHeaders middleware, registered globally:
$response->header('X-Frame-Options', 'SAMEORIGIN');
$response->header('X-Content-Type-Options', 'nosniff');
$response->header('Referrer-Policy', 'strict-origin-when-cross-origin');
```

---

## 12. 🟠 P1 — Exposed Debug Route in Production

**Current state:** `routes/web.php` L63-69:
```php
Route::get('/php-info', function () {
    return [
        'php_ini' => php_ini_loaded_file(),
        'upload_max_filesize' => ini_get('upload_max_filesize'),
        'post_max_size' => ini_get('post_max_size'),
    ];
});
```
This is a public, unauthenticated route that leaks server configuration.

**Fix:** Delete this route entirely or guard it with `EnsureUserIsAdmin`.

---

## 13. 🟡 P2 — No "About Us" / Brand Story Page

**Current state:** The storefront goes directly from a login screen to a product grid. There is no brand identity page.

**Why it matters:** First-time visitors who don't know the brand look for an "About" page to verify the company is real.

**Fix:** A single static `/about` page with: who you are, where you're based, your mission, and a contact email.

---

## 14. 🟡 P2 — No Contact / Support Channel

**Current state:** No contact page, email address, live chat, or support link anywhere in the UI.

**Why it matters:** Customers need to know they can reach you if something goes wrong. Its absence is a significant trust killer.

**Fix:** At minimum, a `/contact` page with a contact form or mailto link. Bonus: a live chat widget (Crisp/Tawk.to — both free).

---

## 15. 🟡 P2 — Cart is Session-Based Only (Lost on Login Expiry)

**Current state:** Cart is stored in the Laravel session. If the session expires, the cart is silently lost.

**Why it matters:** Customers returning the next day find an empty cart and feel frustrated.

**Fix:** Persist cart to a `carts` table for authenticated users and merge session cart on login.

---

## 16. 🟡 P2 — No Favicon / Brand Identity

**Current state:** `welcome.blade.php` references `/favicon.ico` and `/apple-touch-icon.png` but no custom favicon files were committed. Browser shows a generic icon.

**Why it matters:** A missing favicon makes the tab look like a dev environment, not a real store.

**Fix:** Create and commit a proper favicon set (favicon.ico, favicon.svg, apple-touch-icon.png).

---

## 17. 🟡 P2 — The Welcome Page is the Laravel Default

**Current state:** `welcome.blade.php` renders the stock Laravel welcome screen (links to Laravel docs, Laracasts, etc.) and is served on `route('shop.index')` — which actually points to `ShopController::index()`, not the welcome page. However the welcome page is still accessible at the root path and contains Laravel boilerplate text.

**Why it matters:** Sophisticated customers who land on the root URL and see "Let's get started — Read the Documentation" will immediately recognize this as an unconfigured demo.

**Fix:** Replace `welcome.blade.php` with a proper landing/hero page, or redirect `/` to `/shop`.

---

## 18. 🟢 P3 — No Wishlist

No wishlist functionality. Moderate trust/retention signal — customers can save items for later.

---

## 19. 🟢 P3 — No Loyalty / Coupon System

No discount codes or loyalty points. Positive repeat-purchase signal but not a first-visit trust factor.

---

## Summary Checklist

| # | Issue | Priority | Est. Effort | Done |
|---|-------|----------|-------------|------|
| 1 | Force HTTPS in production | 🔴 P0 | 30 min | [ ] |
| 2 | Privacy Policy, ToS, Returns, Shipping pages | 🔴 P0 | 2–4 h | [ ] |
| 3 | Real payment gateway (Stripe/PayPal) | 🔴 P0 | 4–8 h | [ ] |
| 4 | Customer-facing order history + tracking | 🔴 P0 | 3–5 h | [ ] |
| 5 | Order confirmation email | 🔴 P0 | 2–3 h | [ ] |
| 6 | Product descriptions field | 🟠 P1 | 1–2 h | [ ] |
| 7 | Product detail page (`/shop/{product}`) | 🟠 P1 | 2–4 h | [ ] |
| 8 | Fix `Order::$fillable` (missing fields) | 🟠 P1 | 5 min | [ ] |
| 9 | Reviews & ratings | 🟠 P1 | 4–6 h | [ ] |
| 10 | Stock level display + over-order guard | 🟠 P1 | 2–3 h | [ ] |
| 11 | Security headers middleware | 🟠 P1 | 1 h | [ ] |
| 12 | Remove `/php-info` debug route | 🟠 P1 | 5 min | [ ] |
| 13 | About Us page | 🟡 P2 | 1–2 h | [ ] |
| 14 | Contact / Support page | 🟡 P2 | 1–2 h | [ ] |
| 15 | Persistent cart (DB-backed) | 🟡 P2 | 3–4 h | [ ] |
| 16 | Custom favicon | 🟡 P2 | 30 min | [ ] |
| 17 | Real landing/hero page | 🟡 P2 | 2–4 h | [ ] |
| 18 | Wishlist | 🟢 P3 | 4–6 h | [ ] |
| 19 | Coupon/loyalty system | 🟢 P3 | 6–10 h | [ ] |

---

> [!CAUTION]
> Items #8 (missing `$fillable`) and #12 (exposed debug route) are **silent bugs affecting live data and security right now**. Fix them before anything else.

> [!IMPORTANT]
> The P0 items (#1–#5) should be treated as a release blocker. A store without HTTPS, legal pages, and order emails will struggle to get customers past the checkout page — regardless of product quality.
