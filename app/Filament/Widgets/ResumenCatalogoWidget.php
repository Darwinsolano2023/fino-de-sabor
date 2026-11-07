<?php

namespace App\Filament\Widgets;

use App\Models\Cliente;
use App\Models\Producto;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class ResumenCatalogoWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected ?string $heading = 'Catálogo';

    public static function canView(): bool
    {
        $usuario = auth()->user();

        return $usuario && ($usuario->can('clientes.ver') || $usuario->can('productos.ver') || $usuario->can('inventario.ver'));
    }

    protected function getStats(): array
    {
        $usuario = auth()->user();
        $stats = [];

        if ($usuario->can('clientes.ver')) {
            $stats[] = Stat::make('Clientes activos', Cliente::where('activo', true)->count())
                ->description('En las 3 rutas')
                ->icon(Heroicon::OutlinedUsers);
        }

        if ($usuario->can('productos.ver')) {
            $stats[] = Stat::make('Productos activos', Producto::where('activo', true)->count())
                ->description('En el catálogo')
                ->icon(Heroicon::OutlinedCube);
        }

        if ($usuario->can('inventario.ver')) {
            $alertas = $this->productosConAlerta();

            $stats[] = Stat::make('Alertas de stock', $alertas)
                ->description($alertas > 0 ? 'Productos bajos o en negativo' : 'Todo en orden')
                ->color($alertas > 0 ? 'danger' : 'success')
                ->icon($alertas > 0 ? Heroicon::OutlinedExclamationTriangle : Heroicon::OutlinedCheckCircle);
        }

        return $stats;
    }

    /** Productos con stock negativo, o en/por debajo de su stock minimo (si tiene uno definido). */
    private function productosConAlerta(): int
    {
        return DB::table('v_stock_productos')
            ->where(fn ($q) => $q
                ->where('stock', '<', 0)
                ->orWhere(fn ($q2) => $q2->where('stock_minimo', '>', 0)->whereColumn('stock', '<=', 'stock_minimo')))
            ->count();
    }
}
