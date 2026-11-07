<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ProductoController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'per_page'     => ['nullable', 'integer', 'min:1', 'max:200'],
            'categoria_id' => ['nullable', 'uuid'],
        ]);

        $query = $this->consulta($request)->with('categoria:id,nombre');

        if ($request->filled('categoria_id')) {
            $query->where('productos.categoria_id', $request->input('categoria_id'));
        }

        if ($request->filled('buscar')) {
            $texto = '%' . $request->input('buscar') . '%';
            $query->where(fn ($q) => $q
                ->where('productos.nombre', 'ilike', $texto)
                ->orWhere('productos.codigo', 'ilike', $texto)
                ->orWhere('productos.codigo_barras', 'ilike', $texto));
        }

        if ($request->has('activo')) {
            $query->where('productos.activo', $request->boolean('activo'));
        }

        $paginado = $query
            ->orderBy('productos.orden')
            ->orderBy('productos.nombre')
            ->paginate($request->integer('per_page', 50));

        $paginado->getCollection()->each(fn ($p) => $this->filtrarCampos($request, $p));

        return $paginado;
    }

    public function show(Request $request, string $id): JsonResponse
    {
        $producto = $this->consulta($request)->with('categoria:id,nombre')->findOrFail($id);

        return response()->json($this->filtrarCampos($request, $producto));
    }

    public function store(Request $request): JsonResponse
    {
        $producto = Producto::create($request->validate($this->reglas(true)));

        return response()->json($this->filtrarCampos($request, $producto->load('categoria:id,nombre')), 201);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $producto = Producto::findOrFail($id);
        $producto->update($request->validate($this->reglas(false, $producto->id)));

        return response()->json($this->filtrarCampos($request, $producto->load('categoria:id,nombre')));
    }

    public function destroy(string $id)
    {
        Producto::findOrFail($id)->delete();   // borrado logico

        return response()->noContent();
    }

    /** Productos con su stock actual (suma de movimientos). */
    private function consulta(Request $request): Builder
    {
        return Producto::query()
            ->leftJoin('v_stock_productos as s', 's.producto_id', '=', 'productos.id')
            ->select('productos.*', DB::raw('COALESCE(s.stock, 0) as stock'));
    }

    /** Quita lo que este usuario no debe ver: el costo y, si no tiene permiso, el stock. */
    private function filtrarCampos(Request $request, Producto $producto): Producto
    {
        $usuario = $request->user();

        if (! $usuario->can('productos.ver_costo')) {
            $producto->makeHidden('precio_costo');
        }
        if (! $usuario->can('inventario.ver')) {
            $producto->makeHidden(['stock', 'stock_minimo']);
        }

        return $producto;
    }

    private function reglas(bool $creando, ?string $ignorarId = null): array
    {
        $req = $creando ? 'required' : 'sometimes';

        return [
            'codigo'               => [$req, 'string', 'max:30', Rule::unique('productos', 'codigo')->ignore($ignorarId)],
            'codigo_barras'        => ['nullable', 'string', 'max:40'],
            'nombre'               => [$req, 'string', 'max:150'],
            'descripcion'          => ['nullable', 'string'],
            'categoria_id'         => ['nullable', 'uuid', Rule::exists('categorias_producto', 'id')->whereNull('deleted_at')],
            'presentacion'         => ['nullable', Rule::in(['papeleta', 'bolsa', 'frasco', 'libra', 'kilo', 'unidad'])],
            'contenido_gramos'     => ['nullable', 'numeric', 'min:0'],
            'unidad_medida'        => ['nullable', 'string', 'max:10'],
            'precio_venta'         => ['nullable', 'numeric', 'min:0'],
            'precio_costo'         => ['nullable', 'numeric', 'min:0'],
            'stock_minimo'         => ['nullable', 'numeric', 'min:0'],
            'imagen_url'           => ['nullable', 'string', 'max:500'],
            'imagen_miniatura_url' => ['nullable', 'string', 'max:500'],
            'orden'                => ['nullable', 'integer', 'min:0'],
            'activo'               => ['nullable', 'boolean'],
        ];
    }
}
