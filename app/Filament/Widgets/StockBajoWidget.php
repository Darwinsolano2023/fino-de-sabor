<?php

namespace App\Filament\Widgets;

use App\Models\Producto;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class StockBajoWidget extends TableWidget
{
    protected static ?int $sort = 4;

    protected static ?string $heading = 'Productos con stock bajo';

    protected int | string | array $columnSpan = 1;

    public static function canView(): bool
    {
        return (bool) auth()->user()?->can('inventario.ver');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => Producto::query()
                ->joinSub(
                    DB::table('v_stock_productos')->select('producto_id', 'stock'),
                    's',
                    's.producto_id', '=', 'productos.id'
                )
                ->select('productos.*', 's.stock')
                ->where('productos.activo', true)
                ->where(fn ($q) => $q
                    ->where('s.stock', '<', 0)
                    ->orWhere(fn ($q2) => $q2
                        ->where('productos.stock_minimo', '>', 0)
                        ->whereColumn('s.stock', '<=', 'productos.stock_minimo')))
                ->orderBy('s.stock'))
            ->columns([
                TextColumn::make('codigo')->label('Código'),
                TextColumn::make('nombre')->label('Producto')->weight('bold'),
                TextColumn::make('stock')->label('Stock')->numeric()->badge()
                    ->color(fn ($state): string => $state < 0 ? 'danger' : 'warning'),
                TextColumn::make('stock_minimo')->label('Mínimo')->numeric(),
            ])
            ->paginated([5, 10])
            ->defaultPaginationPageOption(5)
            ->emptyStateIcon(Heroicon::OutlinedCheckCircle)
            ->emptyStateHeading('Sin alertas de stock')
            ->emptyStateDescription('Ningún producto está bajo su mínimo ni en negativo.');
    }
}
