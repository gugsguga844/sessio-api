<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;

class SwaggerServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if (app()->environment('production')) {
            URL::forceScheme('https');
        }
    }
} 