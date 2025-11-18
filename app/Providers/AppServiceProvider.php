<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton('report.pdf', function () {
            return new \App\Support\ReportPdf();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Paksa base URL mengikuti request (termasuk subfolder) agar
        // helper route()/asset menghasilkan URL yang benar di hosting.
        if (!app()->runningInConsole()) {
            $base = rtrim(request()->getSchemeAndHttpHost() . request()->getBaseUrl(), '/');
            if (!empty($base)) {
                URL::forceRootUrl($base);
            }

            // Jika request melalui HTTPS, pastikan URL yang dihasilkan juga HTTPS
            if (request()->isSecure()) {
                URL::forceScheme('https');
            }
        }
    }
}
