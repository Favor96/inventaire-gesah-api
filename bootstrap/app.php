<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
    $middleware->alias([
        'admin.auth' => \App\Http\Middleware\AdminMiddleware::class, // Alias du middleware admin
        'account.verified' => \App\Http\Middleware\AcountVerifed::class, // Alias du middleware admin
        'chef.auth' => \App\Http\Middleware\ChefMiddleware::class, // Alias du middleware chef
        'agent.auth' => \App\Http\Middleware\AgentMiddleware::class, // Alias du middleware agent
    ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
