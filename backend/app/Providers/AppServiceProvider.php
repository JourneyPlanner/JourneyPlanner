<?php

namespace App\Providers;

use App\Mail\EmailConfirmation;
use Dedoc\Scramble\Scramble;
use Illuminate\Support\ServiceProvider;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Routing\Route;
use Illuminate\Support\Str;

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
        VerifyEmail::toMailUsing(function (object $notifiable, string $url) {
            return (new EmailConfirmation($url))->to($notifiable->email);
        });

        Scramble::routes(function (Route $route) {
            return Str::startsWith($route->uri, "api/") ||
                Str::startsWith($route->uri, "auth/") ||
                Str::startsWith($route->uri, "email/") ||
                Str::startsWith($route->uri, "logout") ||
                Str::startsWith($route->uri, "register") ||
                Str::startsWith($route->uri, "login") ||
                Str::startsWith($route->uri, "forgot-password") ||
                Str::startsWith($route->uri, "reset-password");
        });
    }
}
