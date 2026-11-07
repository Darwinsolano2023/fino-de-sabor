<?php

namespace App\Filament\Widgets;

use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class ResumenVentasWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 2;

    protected ?string $heading = 'Ventas';

    public static function canView(): bool
    {
        $usuario = auth()->user();

        return $usuario && ($usuario->can('facturacion.ver') || $usuario->can('pedidos.ver') || $usuario->can('pagos.ver'));
    }

    protected function getStats(): array
    {
        $usuario = auth()->user();
        $stats = [];

        if ($usuario->can('facturacion.ver')) {
            $ventasHoy = DB::table('facturas')
                ->where('anulada', false)
                ->whereBetween('fecha', [now()->startOfDay()->toIso8601String(), now()->endOfDay()->toIso8601String()])
                ->sum('total');

            $stats[] = Stat::make('Ventas de hoy', $this->pesos($ventasHoy))
                ->description('Facturas del día, sin anuladas')
                ->icon(Heroicon::OutlinedBanknotes);
        }

        if ($usuario->can('pedidos.ver')) {
            $porFacturar = DB::table('pedidos')->where('estado', 'tomado')->whereNull('deleted_at')->count();

            $stats[] = Stat::make('Pedidos por facturar', $porFacturar)
                ->description('Tomados y sin factura')
                ->color($porFacturar > 0 ? 'warning' : 'success')
                ->icon(Heroicon::OutlinedClipboardDocumentList);
        }

        if ($usuario->can('pagos.ver')) {
            $porCobrar = DB::table('v_saldo_facturas')->sum('saldo');

            $stats[] = Stat::make('Por cobrar', $this->pesos($porCobrar))
                ->description('Saldo pendiente de clientes')
                ->icon(Heroicon::OutlinedCurrencyDollar);
        }

        return $stats;
    }

    private function pesos(float | int | string | null $valor): string
    {
        return '$ ' . number_format((float) $valor, 0, ',', '.');
    }
}
