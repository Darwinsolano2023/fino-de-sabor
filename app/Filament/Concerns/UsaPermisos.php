<?php

namespace App\Filament\Concerns;

use Illuminate\Database\Eloquent\Model;

/**
 * Conecta un Resource de Filament con la tabla de permisos de Fino de Sabor.
 * Cada Resource indica su modulo ('clientes', 'productos'...) y Filament
 * muestra u oculta menus y botones segun los permisos del rol.
 */
trait UsaPermisos
{
    abstract protected static function modulo(): string;

    /** Permite que un Resource cambie que permiso corresponde a una accion. */
    protected static function accion(string $accion): string
    {
        return $accion;
    }

    protected static function permitido(string $accion): bool
    {
        return auth()->user()?->can(static::modulo() . '.' . static::accion($accion)) ?? false;
    }

    public static function canViewAny(): bool { return static::permitido('ver'); }
    public static function canView(Model $record): bool { return static::permitido('ver'); }
    public static function canCreate(): bool { return static::permitido('crear'); }
    public static function canEdit(Model $record): bool { return static::permitido('editar'); }
    public static function canDelete(Model $record): bool { return static::permitido('eliminar'); }
    public static function canDeleteAny(): bool { return static::permitido('eliminar'); }
    public static function canRestore(Model $record): bool { return static::permitido('eliminar'); }
    public static function canRestoreAny(): bool { return static::permitido('eliminar'); }

    // Nunca se borra definitivamente desde el panel: solo borrado logico.
    public static function canForceDelete(Model $record): bool { return false; }
    public static function canForceDeleteAny(): bool { return false; }
}
