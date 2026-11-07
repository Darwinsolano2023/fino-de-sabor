<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class ClientesPorRutaChart extends ChartWidget
{
    protected static ?int $sort = 3;

    protected ?string $heading = 'Clientes por ruta';

    protected int | string | array $columnSpan = 1;

    public static function canView(): bool
    {
        return (bool) auth()->user()?->can('rutas.ver');
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getMaxHeight(): ?string
    {
        return '260px';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => ['legend' => ['position' => 'bottom']],
        ];
    }

    protected function getData(): array
    {
        $rutas = DB::table('rutas')
            ->leftJoin('clientes', fn ($join) => $join
                ->on('clientes.ruta_id', '=', 'rutas.id')
                ->whereNull('clientes.deleted_at'))
            ->whereNull('rutas.deleted_at')
            ->groupBy('rutas.id', 'rutas.codigo', 'rutas.nombre')
            ->orderBy('rutas.codigo')
            ->selectRaw('rutas.nombre, count(clientes.id) as total')
            ->get();

        return [
            'datasets' => [[
                'label' => 'Clientes',
                'data' => $rutas->pluck('total')->map(fn ($n) => (int) $n)->all(),
                'backgroundColor' => ['#185768', '#52a0b5', '#aeb9be', '#d9ecf1', '#2f8499'],
                'borderWidth' => 0,
            ]],
            'labels' => $rutas->pluck('nombre')->all(),
        ];
    }
}
