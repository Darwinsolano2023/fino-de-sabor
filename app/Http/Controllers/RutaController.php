<?php

namespace App\Http\Controllers;

use App\Models\Ruta;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RutaController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $usuario = $request->user();

        $query = Ruta::query()
            ->select('rutas.*')
            ->selectSub(
                DB::table('clientes')
                    ->whereColumn('clientes.ruta_id', 'rutas.id')
                    ->whereNull('clientes.deleted_at')
                    ->selectRaw('count(*)'),
                'clientes_count'
            )
            ->orderBy('rutas.codigo');

        // El vendedor solo ve las rutas que tiene asignadas
        if (! $usuario->can('clientes.ver_todos')) {
            $query->whereIn('rutas.id', DB::table('usuario_rutas')->where('usuario_id', $usuario->id)->select('ruta_id'));
        }

        return response()->json($query->get());
    }
}
