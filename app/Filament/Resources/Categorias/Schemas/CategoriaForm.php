<?php

namespace App\Filament\Resources\Categorias\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CategoriaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()
                ->columns(2)
                ->schema([
                    TextInput::make('nombre')->required()->maxLength(80)->unique(ignoreRecord: true),
                    TextInput::make('orden')->numeric()->default(0)->minValue(0),
                    Toggle::make('activo')->label('Activa')->default(true),
                ]),
        ]);
    }
}
