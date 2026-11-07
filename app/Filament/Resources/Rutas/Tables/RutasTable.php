<?php

namespace App\Filament\Resources\Rutas\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class RutasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('codigo')->label('N.º')->sortable(),
                TextColumn::make('nombre')->weight('bold')->searchable()->sortable(),
                TextColumn::make('dia_visita')->label('Día de visita')->formatStateUsing(fn (?string $state) => $state ? ucfirst($state) : null),
                TextColumn::make('clientes_count')->label('Clientes')->counts('clientes')->sortable(),
                IconColumn::make('activo')->label('Activa')->boolean(),
            ])
            ->defaultSort('codigo')
            ->filters([TrashedFilter::make()->label('Eliminadas')])
            ->recordActions([EditAction::make()])
            ->toolbarActions([
                BulkActionGroup::make([DeleteBulkAction::make(), RestoreBulkAction::make()]),
            ]);
    }
}
