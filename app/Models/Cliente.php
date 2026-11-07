<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cliente extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'clientes';

    protected $fillable = [
        'codigo', 'nombre_negocio', 'nombre_contacto', 'tipo_negocio',
        'tipo_documento', 'documento', 'telefono', 'whatsapp',
        'direccion', 'barrio', 'ciudad', 'referencia_ubicacion',
        'latitud', 'longitud', 'ruta_id', 'orden_visita',
        'permite_credito', 'cupo_credito', 'dias_credito', 'notas', 'activo',
    ];

    protected function casts(): array
    {
        return [
            'permite_credito' => 'boolean',
            'activo'          => 'boolean',
            'cupo_credito'    => 'decimal:2',
        ];
    }

    public function ruta(): BelongsTo
    {
        return $this->belongsTo(Ruta::class, 'ruta_id');
    }
}
