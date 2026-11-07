<?php

namespace App\Providers;

use App\Models\Usuario;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Cada permiso de la base (ej. 'facturacion.anular') funciona como un Gate.
        // Uso en rutas:  ->middleware('can:facturacion.anular')
        // Uso en codigo: $request->user()->can('facturacion.anular')
        Gate::before(function ($usuario, string $permiso) {
            return $usuario instanceof Usuario && $usuario->tienePermiso($permiso) ? true : null;
        });
    }
}
