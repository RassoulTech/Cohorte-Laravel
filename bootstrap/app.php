<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Depuis Laravel 11, app/Http/Kernel.php n'existe plus : les alias de
        // middleware se declarent ici. L'alias est le nom court utilisable
        // dans les routes, par exemple Route::middleware(['auth', 'promotion']).
        $middleware->alias([
            'promotion' => \App\Http\Middleware\ExigePromotion::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
