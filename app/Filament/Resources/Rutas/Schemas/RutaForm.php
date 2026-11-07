<?php

namespace App\Filament\Resources\Rutas\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class RutaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()
                ->columns(2)
                ->schema([
                    TextInput::make('codigo')->label('Número de ruta')->numeric()->required()->minValue(1)->unique(ignoreRecord: true),
                    TextInput::make('nombre')->required()->maxLength(80),
                    Select::make('dia_visita')->label('Día de visita')->native(false)->options([
                        'lunes' => 'Lunes', 'martes' => 'Martes', 'miercoles' => 'Miércoles', 'jueves' => 'Jueves',
                        'viernes' => 'Viernes', 'sabado' => 'Sábado', 'domingo' => 'Domingo',
                    ]),
                    Toggle::make('activo')->label('Activa')->default(true),
                    Textarea::make('descripcion')->label('Descripción')->rows(2)->columnSpanFull(),
                ]),
        ]);
    }
}
