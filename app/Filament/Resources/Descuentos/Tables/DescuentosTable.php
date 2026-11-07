<?php

namespace App\Filament\Resources\Descuentos\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class DescuentosTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nombre')->weight('bold')->searchable()->sortable(),
                TextColumn::make('alcance')->label('Aplica a')
                    ->state(fn ($record): string => $record->producto
                        ? 'Producto: ' . $record->producto->nombre
                        : ($record->categoria ? 'Categoría: ' . $record->categoria->nombre : 'Todos los productos')),
                TextColumn::make('escalas_resumen')->label('Escalas')->wrap()
                    ->state(fn ($record): string => $record->escalas
                        ->sortBy('cantidad_minima')
                        ->map(function ($escala): string {
                            $desde = rtrim(rtrim(number_format((float) $escala->cantidad_minima, 3, '.', ''), '0'), '.');
                            $valor = $escala->tipo === 'porcentaje'
                                ? rtrim(rtrim(number_format((float) $escala->valor, 2, '.', ''), '0'), '.') . '%'
                                : '$ ' . number_format((float) $escala->valor, 0, ',', '.');

                            return "desde {$desde}: {$valor}";
                        })
                        ->implode(' · ')),
                TextColumn::make('vigente_desde')->label('Desde')->date('d/m/Y')->placeholder('—')->toggleable(),
                TextColumn::make('vigente_hasta')->label('Hasta')->date('d/m/Y')->placeholder('—')->toggleable(),
                IconColumn::make('activo')->label('Activo')->boolean(),
            ])
            ->defaultSort('nombre')
            ->filters([
                TernaryFilter::make('activo')->label('Activo'),
                TrashedFilter::make()->label('Eliminados'),
            ])
            ->recordActions([EditAction::make()])
            ->toolbarActions([
                BulkActionGroup::make([DeleteBulkAction::make(), RestoreBulkAction::make()]),
            ]);
    }
}
