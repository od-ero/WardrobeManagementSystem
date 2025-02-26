<?php

namespace App\Providers;
use App\Http\Middleware\TeamsPermissions;
use Illuminate\Support\Facades\Gate;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Http\Kernel;
use Illuminate\Routing\Middleware\SubstituteBindings;
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
        /** @var Kernel $kernel */
        $kernel = app()->make(Kernel::class);

        $kernel->addToMiddlewarePriorityBefore(
            SubstituteBindings::class,
            TeamsPermissions::class,
        );

        ResetPassword::createUrlUsing(function (object $notifiable, string $token) {
            return config('app.frontend_url')."/password-reset/$token?email={$notifiable->getEmailForPasswordReset()}";
        });

        Gate::before(function ($user, $ability) {
            $super_admin = $user->hasRole('super-admin') || $user->special_access == 1 ;
            return $super_admin ? true : null;
        });
    }



}
