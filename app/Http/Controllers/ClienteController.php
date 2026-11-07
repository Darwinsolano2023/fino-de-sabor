<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ClienteController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'per_page' => ['nullable', 'integer', 'min:1', 'max:200'],
            'ruta_id'  => ['nullable', 'uuid'],
        ]);

        $query = $this->visibles($request)
            ->leftJoin('rutas', 'rutas.id', '=', 'clientes.ruta_id')
            ->select('clientes.*')
            ->with('ruta:id,codigo,nombre');

        if ($request->filled('ruta_id')) {
            $query->where('clientes.ruta_id', $request->input('ruta_id'));
        }

        if ($request->filled('buscar')) {
            $texto = '%' . $request->input('buscar') . '%';
            $query->where(fn ($q) => $q
                ->where('clientes.nombre_negocio', 'ilike', $texto)
                ->orWhere('clientes.barrio', 'ilike', $texto)
                ->orWhere('clientes.documento', 'ilike', $texto)
                ->orWhere('clientes.telefono', 'ilike', $texto));
        }

        if ($request->has('activo')) {
            $query->where('clientes.activo', $request->boolean('activo'));
        }

        return $query
            ->orderBy('rutas.codigo')
            ->orderBy('clientes.orden_visita')
            ->orderBy('clientes.nombre_negocio')
            ->paginate($request->integer('per_page', 50));
    }

    public function show(Request $request, string $id): JsonResponse
    {
        return response()->json($this->visibles($request)->with('ruta:id,codigo,nombre')->findOrFail($id));
    }

    public function store(Request $request): JsonResponse
    {
        $datos = $request->validate($this->reglas(true));
        $this->verificarRuta($request, $datos['ruta_id'] ?? null);

        $cliente = Cliente::create($datos);

        return response()->json($cliente->load('ruta:id,codigo,nombre'), 201);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $cliente = $this->visibles($request)->findOrFail($id);

        $datos = $request->validate($this->reglas(false, $cliente->id));
        if (array_key_exists('ruta_id', $datos)) {
            $this->verificarRuta($request, $datos['ruta_id']);
        }

        $cliente->update($datos);

        return response()->json($cliente->load('ruta:id,codigo,nombre'));
    }

    public function destroy(Request $request, string $id)
    {
        $this->visibles($request)->findOrFail($id)->delete();   // borrado logico

        return response()->noContent();
    }

    /** Clientes que este usuario puede ver: todos, o solo los de sus rutas. */
    private function visibles(Request $request): Builder
    {
        $usuario = $request->user();
        $query = Cliente::query();

        if (! $usuario->can('clientes.ver_todos')) {
            $query->whereIn('clientes.ruta_id', DB::table('usuario_rutas')
                ->where('usuario_id', $usuario->id)->select('ruta_id'));
        }

        return $query;
    }

    /** Quien no ve todas las rutas solo puede crear/mover clientes dentro de las suyas. */
    private function verificarRuta(Request $request, ?string $rutaId): void
    {
        $usuario = $request->user();

        if ($usuario->can('clientes.ver_todos')) {
            return;
        }

        $permitido = $rutaId && DB::table('usuario_rutas')
            ->where('usuario_id', $usuario->id)->where('ruta_id', $rutaId)->exists();

        abort_unless($permitido, 403, 'Solo puede usar clientes de sus rutas asignadas.');
    }

    private function reglas(bool $creando, ?string $ignorarId = null): array
    {
        $req = $creando ? 'required' : 'sometimes';

        return [
            'codigo'               => ['nullable', 'string', 'max:20', Rule::unique('clientes', 'codigo')->ignore($ignorarId)],
            'nombre_negocio'       => [$req, 'string', 'max:150'],
            'nombre_contacto'      => ['nullable', 'string', 'max:120'],
            'tipo_negocio'         => ['nullable', Rule::in(['tienda', 'supermercado', 'micromercado', 'autoservicio', 'otro'])],
            'tipo_documento'       => ['nullable', Rule::in(['CC', 'NIT', 'CE', 'PAS', 'OTRO'])],
            'documento'            => ['nullable', 'string', 'max:30'],
            'telefono'             => ['nullable', 'string', 'max:30'],
            'whatsapp'             => ['nullable', 'string', 'max:30'],
            'direccion'            => ['nullable', 'string', 'max:200'],
            'barrio'               => ['nullable', 'string', 'max:100'],
            'ciudad'               => ['nullable', 'string', 'max:80'],
            'referencia_ubicacion' => ['nullable', 'string'],
            'latitud'              => ['nullable', 'numeric', 'between:-90,90'],
            'longitud'             => ['nullable', 'numeric', 'between:-180,180'],
            'ruta_id'              => ['nullable', 'uuid', Rule::exists('rutas', 'id')->whereNull('deleted_at')],
            'orden_visita'         => ['nullable', 'integer', 'min:0'],
            'permite_credito'      => ['nullable', 'boolean'],
            'cupo_credito'         => ['nullable', 'numeric', 'min:0'],
            'dias_credito'         => ['nullable', 'integer', 'min:0'],
            'notas'                => ['nullable', 'string'],
            'activo'               => ['nullable', 'boolean'],
        ];
    }
}
