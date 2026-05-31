<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php', // Đảm bảo API route được kích hoạt nếu dùng Laravel 11/12
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Đăng ký bí danh (Alias) cho các Middleware tùy chỉnh của Cyberbloom tại đây
        $middleware->alias([
            'admin'    => \App\Http\Middleware\CheckIsAdmin::class,
            'staff'    => \App\Http\Middleware\CheckIsStaff::class,
            'customer' => \App\Http\Middleware\CheckIsCustomer::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();