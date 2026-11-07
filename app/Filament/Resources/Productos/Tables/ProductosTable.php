<?php

namespace App\Filament\Resources\Productos\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ProductosTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('imagen_url')->label('Foto')->disk('public')->square()->size(40),
                TextColumn::make('codigo')->label('Código')->searchable()->sortable(),
                TextColumn::make('nombre')->weight('bold')->searchable()->sortable(),
                TextColumn::make('categoria.nombre')->label('Categoría')->sortable(),
                TextColumn::make('presentacion')->label('Presentación')->badge(),
                TextColumn::make('precio_venta')->label('Precio')->money('COP', locale: 'es_CO')->sortable(),
                TextColumn::make('precio_costo')->label('Costo')->money('COP', locale: 'es_CO')
                    ->visible(fn (): bool => (bool) auth()->user()?->can('productos.ver_costo')),
                TextColumn::make('stock')->label('Stock')->numeric()->badge()
                    ->color(fn ($state, $record): string => $state < 0
                        ? 'danger'
                        : ($record->stock_minimo > 0 && $state <= $record->stock_minimo ? 'warning' : 'success'))
                    ->sortable(query: fn (Builder $query, string $direction) => $query
                        ->orderByRaw('COALESCE(s.stock, 0) ' . ($direction === 'desc' ? 'desc' : 'asc')))
                    ->visible(fn (): bool => (bool) auth()->user()?->can('inventario.ver')),
                IconColumn::make('activo')->label('Activo')->boolean()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('nombre')
            ->filters([
                SelectFilter::make('categoria_id')->label('Categoría')->relationship('categoria', 'nombre'),
                Filter::make('stock_bajo')->label('Stock bajo o negativo')
                    ->query(fn (Builder $query) => $query->whereRaw('COALESCE(s.stock, 0) <= productos.stock_minimo')),
                TernaryFilter::make('activo')->label('Activo'),
                TrashedFilter::make()->label('Eliminados'),
            ])
            ->recordActions([EditAction::make()])
            ->toolbarActions([
                BulkActionGroup::make([DeleteBulkAction::make(), RestoreBulkAction::make()]),
            ]);
    }
}
