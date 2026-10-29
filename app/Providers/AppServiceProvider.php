<?php

namespace App\Providers;

use App\Models\ArtistPricingProfile;
use App\Models\PricingSetting;
use App\Models\Product;
use App\Observers\ArtistPricingProfileObserver;
use App\Observers\PricingSettingObserver;
use App\Observers\ProductPricingObserver;
use App\Payments\CashOnDeliveryPayment;
use App\Payments\InstapayPayment;
use App\Services\PaymentService;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(PaymentService::class, function () {
            $service = new PaymentService;

            // Register payment drivers here.
            // To add Stripe, PayPal, etc. — just call $service->register(new StripePayment());
            $service->register(new CashOnDeliveryPayment);
            $service->register(new InstapayPayment);

            return $service;
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (app()->isProduction()) {
            URL::forceScheme('https');
        }

        Gate::before(function ($user, string $ability) {
            return ($user->hasRole('admin') || $user->role === 'admin') ? true : null;
        });

        Product::observe(ProductPricingObserver::class);
        ArtistPricingProfile::observe(ArtistPricingProfileObserver::class);
        PricingSetting::observe(PricingSettingObserver::class);

        $this->configureDefaults();
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}
