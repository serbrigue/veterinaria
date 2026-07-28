<?php

namespace App\Providers;

use App\Policies\PagosVeterinariosPolicy;
use App\Policies\PanelPolicy;
use App\Policies\ImportPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        Gate::define('ver-panel', [PanelPolicy::class, 'ver']);
        Gate::define('pagos-veterinarios.verTodas', [PagosVeterinariosPolicy::class, 'verTodas']);
        Gate::define('pagos-veterinarios.ver', [PagosVeterinariosPolicy::class, 'ver']);
        Gate::define('pagos-veterinarios.crear', [PagosVeterinariosPolicy::class, 'crear']);
        Gate::define('importar-datos', [ImportPolicy::class, 'importar']);
        
        Gate::define('gestionar-inventario', function (\App\Models\User $user) {
            return $user->isAdmin() || $user->isVeterinario() || $user->tienePermiso('gestionar_inventario');
        });
    }
}
