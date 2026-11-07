<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\HasApiTokens;

class Usuario extends Authenticatable
{
    use HasApiTokens, HasUuids, SoftDeletes;

    protected $table = 'usuarios';

    protected $fillable = ['nombre', 'email', 'password', 'rol_id', 'activo', 'debe_cambiar_password'];

    protected $hidden = ['password', 'remember_token'];

    private ?array $permisosCache = null;

    protected function casts(): array
    {
        return [
            'password'              => 'hashed',
            'activo'                => 'boolean',
            'debe_cambiar_password' => 'boolean',
            'bloqueado_hasta'       => 'datetime',
            'ultimo_login'          => 'datetime',
        ];
    }

    public function rol(): BelongsTo
    {
        return $this->belongsTo(Rol::class, 'rol_id');
    }

    /** Lista de codigos de permiso, ej: ['pedidos.ver', 'pedidos.crear'] */
    public function permisos(): array
    {
        return $this->permisosCache ??= DB::table('v_permisos_usuario')
            ->where('usuario_id', $this->id)
            ->pluck('permiso')
            ->all();
    }

    public function tienePermiso(string $codigo): bool
    {
        return in_array($codigo, $this->permisos(), true);
    }

    /** Menu segun el rol (viene de la vista v_menu_por_rol) */
    public function menu(): array
    {
        return DB::table('v_menu_por_rol')
            ->where('rol', $this->rol->codigo)
            ->orderBy('orden')
            ->get(['modulo', 'nombre', 'ruta_app', 'icono'])
            ->all();
    }
}
