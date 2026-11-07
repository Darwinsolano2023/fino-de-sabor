<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    private const MAX_INTENTOS = 5;
    private const MINUTOS_BLOQUEO = 15;

    public function login(Request $request): JsonResponse
    {
        $datos = $request->validate([
            'email'          => ['required', 'email'],
            'password'       => ['required', 'string'],
            'dispositivo_id' => ['nullable', 'uuid'],
        ]);

        $usuario = Usuario::with('rol')->where('email', $datos['email'])->first();

        if ($usuario && $usuario->bloqueado_hasta && $usuario->bloqueado_hasta->isFuture()) {
            $this->auditar($usuario->id, 'login_bloqueado', $request, $datos['dispositivo_id'] ?? null);
            return response()->json(['message' => 'Usuario bloqueado temporalmente. Intente mas tarde.'], 423);
        }

        if (! $usuario || ! Hash::check($datos['password'], $usuario->password)) {
            if ($usuario) {
                $intentos = $usuario->intentos_fallidos + 1;
                $usuario->forceFill([
                    'intentos_fallidos' => $intentos,
                    'bloqueado_hasta'   => $intentos >= self::MAX_INTENTOS ? now()->addMinutes(self::MINUTOS_BLOQUEO) : null,
                ])->saveQuietly();
            }
            $this->auditar($usuario?->id, 'login_fallido', $request, $datos['dispositivo_id'] ?? null, ['email' => $datos['email']]);
            return response()->json(['message' => 'Correo o contrasena incorrectos.'], 401);
        }

        if (! $usuario->activo || ! $usuario->rol?->activo) {
            return response()->json(['message' => 'Usuario inactivo.'], 403);
        }

        $usuario->forceFill([
            'intentos_fallidos' => 0,
            'bloqueado_hasta'   => null,
            'ultimo_login'      => now(),
        ])->saveQuietly();

        $this->auditar($usuario->id, 'login', $request, $datos['dispositivo_id'] ?? null);

        $token = $usuario->createToken($datos['dispositivo_id'] ?? 'web')->plainTextToken;

        return response()->json(['token' => $token] + $this->perfil($usuario));
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json($this->perfil($request->user()->load('rol')));
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();
        $this->auditar($request->user()->id, 'logout', $request);

        return response()->json(['message' => 'Sesion cerrada.']);
    }

    public function cambiarPassword(Request $request): JsonResponse
    {
        $datos = $request->validate([
            'password_actual' => ['required', 'string'],
            'password_nuevo'  => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $usuario = $request->user();

        if (! Hash::check($datos['password_actual'], $usuario->password)) {
            return response()->json(['message' => 'La contrasena actual no es correcta.'], 422);
        }

        $usuario->forceFill([
            'password'              => $datos['password_nuevo'],   // el cast 'hashed' la encripta
            'debe_cambiar_password' => false,
        ])->save();

        // Cierra las demas sesiones, deja solo la actual
        $usuario->tokens()->where('id', '!=', $usuario->currentAccessToken()->id)->delete();

        $this->auditar($usuario->id, 'cambio_password', $request);

        return response()->json(['message' => 'Contrasena actualizada.']);
    }

    private function perfil(Usuario $usuario): array
    {
        return [
            'usuario' => [
                'id'                    => $usuario->id,
                'nombre'                => $usuario->nombre,
                'email'                 => $usuario->email,
                'debe_cambiar_password' => $usuario->debe_cambiar_password,
            ],
            'rol'      => ['codigo' => $usuario->rol->codigo, 'nombre' => $usuario->rol->nombre],
            'permisos' => $usuario->permisos(),
            'menu'     => $usuario->menu(),
        ];
    }

    private function auditar(?string $usuarioId, string $accion, Request $request, ?string $dispositivoId = null, ?array $detalle = null): void
    {
        DB::table('auditoria')->insert([
            'usuario_id'     => $usuarioId,
            'dispositivo_id' => $dispositivoId && DB::table('dispositivos')->where('id', $dispositivoId)->exists() ? $dispositivoId : null,
            'accion'         => $accion,
            'detalle'        => $detalle ? json_encode($detalle) : null,
            'ip'             => $request->ip(),
        ]);
    }
}
