<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

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

    // 'imagen_url' guarda la ruta del archivo subido (ej. productos/abc.jpg).
    // 'imagen_completa' es la URL lista para mostrar en las apps.
    protected $appends = ['imagen_completa'];

    protected function casts(): array
    {
        return [
            'activo'       => 'boolean',
            'precio_venta' => 'decimal:2',
            'precio_costo' => 'decimal:2',
        ];
    }

    protected function imagenCompleta(): Attribute
    {
        return Attribute::get(function () {
            $ruta = $this->imagen_url;
            if (! $ruta) {
                return null;
            }

            return str_starts_with($ruta, 'http') ? $ruta : Storage::disk('public')->url($ruta);
        });
    }

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class, 'categoria_id');
    }
}
