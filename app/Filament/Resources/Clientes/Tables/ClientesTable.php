<?php

namespace App\Filament\Resources\Clientes\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class ClientesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('ruta.nombre')->label('Ruta')->badge()->sortable(),
                TextColumn::make('orden_visita')->label('Orden')->sortable(),
                TextColumn::make('nombre_negocio')->label('Cliente')->weight('bold')->searchable()->sortable(),
                TextColumn::make('barrio')->searchable()->sortable(),
                TextColumn::make('telefono')->label('Teléfono')->searchable(),
                TextColumn::make('nombre_contacto')->label('Contacto')->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('documento')->toggleable(isToggledHiddenByDefault: true)->searchable(),
                IconColumn::make('permite_credito')->label('Crédito')->boolean(),
                IconColumn::make('activo')->label('Activo')->boolean()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('nombre_negocio')
            ->filters([
                SelectFilter::make('ruta_id')->label('Ruta')->relationship('ruta', 'nombre'),
                TernaryFilter::make('permite_credito')->label('Crédito'),
                TrashedFilter::make()->label('Eliminados'),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
