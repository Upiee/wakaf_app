<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    /**
     * The application's route middleware.
     *
     * These middleware may be assigned to groups or used individually.
     *
     * @var array
     */
    protected $routeMiddleware = [
        // ...middleware lain...
        'onlyAdmin' => \App\Http\Middleware\onlyAdmin::class,
        'onlyEmployeeOrAdmin' => \App\Http\Middleware\onlyEmployeeOrAdmin::class,
    ];
}