<?php

namespace App\Providers;

use App\Services\SwapiService;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(SwapiService::class, fn () => new SwapiService(
            urlBase: config('services.swapi.base_url'),
            timeoutEnSegundos: config('services.swapi.timeout'),
        ));
    }

    public function boot(): void
    {
        Route::resourceVerbs([
            'create' => 'crear',
            'edit' => 'editar',
        ]);
    }
}
