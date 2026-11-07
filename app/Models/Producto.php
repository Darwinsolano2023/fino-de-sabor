<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Producto extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'productos';

    protected $fillable = [
        'codigo', 'codigo_barras', 'nombre', 'descripcion', 'categoria_id',
        'presentacion', 'contenido_gramos', 'unidad_medida',
        'precio_venta', 'precio_costo', 'stock_minimo',
        'imagen_url', 'imagen_miniatura_url', 'orden', 'activo',
    ];

    protected function casts(): array
    {
        return [
            'activo'       => 'boolean',
            'precio_venta' => 'decimal:2',
            'precio_costo' => 'decimal:2',
        ];
    }

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class, 'categoria_id');
    }
}
