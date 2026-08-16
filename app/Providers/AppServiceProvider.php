<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;

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
        // En developpement seulement : lever une exception des qu'une relation
        // est utilisee sans avoir ete chargee avec with(). C'est ce qui rend
        // le probleme du N+1 visible immediatement au lieu de passer inapercu.
        Model::preventLazyLoading(! app()->isProduction());
    }
}
