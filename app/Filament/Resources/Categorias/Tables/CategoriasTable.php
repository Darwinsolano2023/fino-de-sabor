<?php

namespace App\Filament\Resources\Categorias\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class CategoriasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('orden')->sortable(),
                TextColumn::make('nombre')->weight('bold')->searchable()->sortable(),
                TextColumn::make('productos_count')->label('Productos')->counts('productos'),
                IconColumn::make('activo')->label('Activa')->boolean(),
            ])
            ->defaultSort('orden')
            ->reorderable('orden')
            ->filters([TrashedFilter::make()->label('Eliminadas')])
            ->recordActions([EditAction::make()])
            ->toolbarActions([
                BulkActionGroup::make([DeleteBulkAction::make(), RestoreBulkAction::make()]),
            ]);
    }
}
