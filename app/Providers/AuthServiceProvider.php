<?php

namespace App\Providers;
use App\Models\Incident;
use App\Models\User;
use App\Models\Resource;
use App\Models\Reservation;
use App\Policies\UserPolicy;
use App\Policies\ResourcePolicy;
use App\Policies\ReservationPolicy;
use App\Policies\IncidentPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        User::class => UserPolicy::class,
        Resource::class => ResourcePolicy::class,
        Reservation::class => ReservationPolicy::class,
        Incident::class => IncidentPolicy::class,
    ];

    protected $routeMiddleware = [
        'admin' => \App\Http\Middleware\AdminMiddleware::class,
        'responsable' => \App\Http\Middleware\ResponsableMiddleware::class,
        'admin_or_responsable' => \App\Http\Middleware\AdminOrResponsableMiddleware::class,
        'active' => \App\Http\Middleware\EnsureUserIsActive::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot()
    {
        $this->registerPolicies();

        // Enregistrer les middlewares personnalisés
        Route::aliasMiddleware('admin', \App\Http\Middleware\AdminMiddleware::class);
        Route::aliasMiddleware('responsable', \App\Http\Middleware\ResponsableMiddleware::class);
        Route::aliasMiddleware('active', \App\Http\Middleware\EnsureUserIsActive::class);
    }
}