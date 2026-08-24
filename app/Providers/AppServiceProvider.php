<?php

namespace App\Providers;

use App\Contracts\PaymentMethodInterface;
use App\Contracts\SupplierSyncServiceInterface;
use App\Payments\CashOnDeliveryPayment;
use App\Services\MockSupplierSyncService;
use App\Services\PaymentService;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
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
        $this->app->bind(SupplierSyncServiceInterface::class, MockSupplierSyncService::class);

        $this->app->singleton(PaymentService::class, function () {
            $service = new PaymentService();

            // Register payment drivers here.
            // To add Stripe, PayPal, etc. — just call $service->register(new StripePayment());
            $service->register(new CashOnDeliveryPayment());

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
