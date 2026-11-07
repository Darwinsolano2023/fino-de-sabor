<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class DescuentoVolumen extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'descuentos_volumen';

    protected $fillable = ['nombre', 'producto_id', 'categoria_id', 'vigente_desde', 'vigente_hasta', 'activo'];

    protected function casts(): array
    {
        return [
            'activo'        => 'boolean',
            'vigente_desde' => 'date',
            'vigente_hasta' => 'date',
        ];
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class, 'categoria_id');
    }

    public function escalas(): HasMany
    {
        return $this->hasMany(DescuentoVolumenEscala::class, 'descuento_id');
    }
}
