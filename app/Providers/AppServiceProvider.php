<?php

namespace App\Providers;

use App\Events\MenuActivated;
use App\Listeners\SendMenuPublishedMail;
use App\Models\Subscription;
use App\Models\Tenant;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Laravel\Cashier\Cashier;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Cashier::useCustomerModel(Tenant::class);
        Cashier::useSubscriptionModel(Subscription::class);

        Event::listen(MenuActivated::class, SendMenuPublishedMail::class);
    }
}
