# PLAN: Three-Party Pricing Strategy

## Overview
Retail price of every variant is the sum of three portions:

| Party | Source |
|-------|--------|
| **Printing** | Printlet base cost per variant (synced from catalog) |
| **Artist** | Linear interpolation between artist's `min_profit` / `max_profit` by print cost |
| **Platform** | `platform_percent` × (print + artist) + `platform_fixed_fee_per_item` per item |

Today `variants.price` is a hand-set/fallback value (`SyncPrintletPosterCatalog` uses `150.00`) and `order_items` only stores `unit_price`. This plan makes price **computed**, and **snapshots** the breakdown on each order.

**Project Type:** WEB (Laravel + Livewire/Blade, admin panel)

## Decisions (from Socratic Gate)
1. **Mapping:** Cheapest variant of a product → `min_profit`, most expensive → `max_profit`, in-between interpolated linearly by print cost.
2. **Platform cut:** % of (print cost + artist profit) **plus** fixed fee per item (`platform_fixed_fee_per_item`).
3. **Limits:** Admin-configurable global floor/ceiling for artist profit, stored in DB settings.
4. **Scope:** Artist-level default; optional per-product override.
5. **Cost changes:** Prices recalculated on catalog sync; orders snapshot breakdown at checkout (immutable history).

## Formula
```
t              = (cost - minCost) / (maxCost - minCost)     // 0 if single variant / equal costs
artistProfit   = minProfit + t * (maxProfit - minProfit)
subtotal       = cost + artistProfit
platformCut    = (subtotal * platformPercent) + platformFixedFee
unitPrice      = roundUp(subtotal + platformCut, rounding_step)   // rounding remainder → platform
orderTotal     = Σ(unitPrice × qty) + shipping
```
Constraints: `floor ≤ minProfit ≤ maxProfit ≤ ceiling`. All money stored as `decimal(10,2)`; calculation in integer piastres to avoid float drift.

## Success Criteria
- [ ] Artist can set min/max profit; validation rejects values outside admin limits or min > max.
- [ ] Every variant price equals formula output after `printlet:sync` or profit change.
- [ ] Each `order_item` stores print_cost, artist_profit, platform_cut, platform_fixed_fee, unit_price snapshot (fixed fee included in each item).
- [ ] Sum of item parts + shipping == order total (to the piastre).
- [ ] Changing Printlet cost later does not alter historical orders.
- [ ] Admin can view per-party revenue totals.

## File Structure
```
app/
  Services/Pricing/PricingCalculator.php      # pure formula, no I/O
  Services/Pricing/PriceBreakdown.php         # readonly DTO
  Services/Pricing/RepriceProductVariants.php # applies calculator to a product
  Models/PricingSetting.php                   # (or spatie/laravel-settings)
  Models/ArtistPricingProfile.php
  Http/Requests/UpdateArtistPricingRequest.php
  Livewire/Artist/PricingSettings.php          # artist min/max form + live preview
  Livewire/Admin/PricingLimits.php             # floor/ceiling, %, fixed fee
  Console/Commands/SyncPrintletPosterCatalog.php  (modify: store cost, reprice)
  Http/Controllers/CheckoutController.php         (modify: snapshot breakdown)
database/migrations/
  xxxx_add_print_cost_to_variants_table.php
  xxxx_create_pricing_settings_table.php
  xxxx_create_artist_pricing_profiles_table.php
  xxxx_add_pricing_override_to_products_table.php
  xxxx_add_breakdown_to_order_items_table.php
tests/Unit/Pricing/PricingCalculatorTest.php
tests/Feature/Pricing/*.php
```

## Task Breakdown

### P0 — Foundation (Database)
| ID | Task | Agent / Skill | Deps | INPUT → OUTPUT → VERIFY |
|----|------|---------------|------|--------------------------|
| T1 | Add `print_cost decimal(10,2)` to `variants` | backend-specialist / database-design | – | Migration → column exists → `php artisan migrate` + rollback clean |
| T2 | `pricing_settings` (artist_profit_floor, ceiling, platform_percent, platform_fixed_fee_per_item, rounding_step) + seeder | backend-specialist / database-design | – | Migration+seeder → one row with defaults → tinker read |
| T3 | `artist_pricing_profiles` (user_id unique, min_profit, max_profit) | backend-specialist / eloquent-expert | – | Migration+model → `User hasOne` → factory test |
| T4 | Nullable `min_profit`,`max_profit` override on `products` | backend-specialist / eloquent-expert | – | Migration → `Product::effectiveProfitRange()` → unit test fallback to artist |
| T5 | Snapshot cols on `order_items`: `variant_id`, `print_cost`, `artist_profit`, `platform_cut`, `platform_fixed_fee`, `artist_id` | backend-specialist / database-design | – | Migration → columns nullable for legacy rows → migrate on copy of prod data |

### P1 — Core Logic
| ID | Task | Agent / Skill | Deps | INPUT → OUTPUT → VERIFY |
|----|------|---------------|------|--------------------------|
| T6 | `PricingCalculator` + `PriceBreakdown` DTO (pure) | backend-specialist / clean-code, tdd-workflow | T2 | Costs + range + settings → breakdown per variant → Pest unit: single variant, equal costs, min=max, rounding, sum==price |
| T7 | `RepriceProductVariants` service: load variants, compute, persist `price` | backend-specialist / laravel-best-practices | T1,T4,T6 | Product → updated variant prices → feature test |
| T8 | Sync command stores Printlet cost into `print_cost` and calls reprice (remove `150.00` fallback) | backend-specialist / laravel-queues | T7 | Fake Printlet HTTP → costs + prices updated → `Http::fake` test |
| T9 | Event/observer: reprice when artist profile, product override, or pricing settings change (queued job for settings change → all products) | backend-specialist / job-patterns | T7 | Change → `RepriceJob` dispatched → `Queue::fake` test |
| T10 | Checkout snapshots breakdown into `order_items` (fixed fee included in each item); total recomputed server-side | backend-specialist / secure-coding-patterns | T5,T6 | Cart → order with breakdown → test sum of parts == `total_price` |

### P2 — UI
| ID | Task | Agent / Skill | Deps | INPUT → OUTPUT → VERIFY |
|----|------|---------------|------|--------------------------|
| T11 | Artist pricing page: min/max sliders bounded by floor/ceiling, live per-size preview (cost / your profit / platform / customer price) | frontend-specialist / livewire-expert | T3,T6 | Form → saved profile → validation errors render; preview matches calculator |
| T12 | Per-product override toggle in product edit | frontend-specialist / livewire-expert | T4 | Toggle → override saved/cleared → feature test |
| T13 | Admin pricing limits screen (floor, ceiling, %, fixed fee, rounding) — permission-gated | frontend-specialist / laravel-security | T2,T9 | Form → settings saved + reprice job → policy test non-admin 403 |
| T14 | Admin revenue report: totals per party per period, per-artist payouts | frontend-specialist / eloquent-expert | T10 | Orders → aggregated table → numbers match seeded fixture |

### P3 — Polish
| ID | Task | Agent / Skill | Deps | INPUT → OUTPUT → VERIFY |
|----|------|---------------|------|--------------------------|
| T15 | Backfill: compute `print_cost` for existing variants (sync) and reprice | backend-specialist / artisan-mastery | T8 | Command → all variants priced → no variant with null cost |
| T16 | Edge cases: ceiling lowered below an artist's max → clamp + notify artist | backend-specialist / error-handling-mastery | T9 | Lower ceiling → profiles clamped → test |

## Risks
- **Printlet cost unknown/zero** → block publishing variant (no price) rather than sell at a loss.
- **Currency/rounding drift** → integer piastre math; rounding remainder goes to platform.
- **Mass reprice on settings change** → queued, chunked job.
- **Price changes between cart and checkout** → recompute at checkout; show notice if changed.

## Phase X: Verification
- [ ] `composer audit`
- [ ] `vendor/bin/pint --test`
- [ ] `vendor/bin/phpstan analyse`
- [ ] `php artisan test --filter=Pricing` (and full suite)
- [ ] `php artisan migrate:fresh --seed` then rollback
- [ ] Manual: artist sets 20–80 → preview sizes show 20…80; place order; breakdown persisted
- [ ] `php .agent/scripts/checklist.php`
- [ ] No purple/violet colors; Socratic Gate respected ✅
