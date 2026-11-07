<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Una escala: "desde X unidades, aplica un porcentaje o un precio fijo".
 * Sin borrado logico a proposito: al quitar una escala se elimina de verdad,
 * porque la tabla tiene UNIQUE (descuento_id, cantidad_minima).
 */
class DescuentoVolumenEscala extends Model
{
    use HasUuids;

    protected $table = 'descuento_volumen_escalas';

    protected $fillable = ['descuento_id', 'cantidad_minima', 'tipo', 'valor'];

    public function descuento(): BelongsTo
    {
        return $this->belongsTo(DescuentoVolumen::class, 'descuento_id');
    }
}
