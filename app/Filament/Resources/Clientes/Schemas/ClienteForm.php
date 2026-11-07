<?php

namespace App\Filament\Resources\Clientes\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class ClienteForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Negocio')
                ->columns(2)
                ->schema([
                    TextInput::make('nombre_negocio')->label('Nombre del negocio')->required()->maxLength(150)->columnSpanFull(),
                    TextInput::make('codigo')->label('Código')->maxLength(20)->unique(ignoreRecord: true),
                    TextInput::make('nombre_contacto')->label('Persona de contacto')->maxLength(120),
                    Select::make('tipo_negocio')->label('Tipo de negocio')->native(false)->options([
                        'tienda' => 'Tienda', 'supermercado' => 'Supermercado', 'micromercado' => 'Micromercado',
                        'autoservicio' => 'Autoservicio', 'otro' => 'Otro',
                    ]),
                    Toggle::make('activo')->label('Activo')->default(true),
                ]),

            Section::make('Ruta de visita')
                ->columns(2)
                ->schema([
                    Select::make('ruta_id')->label('Ruta')->relationship('ruta', 'nombre')->searchable()->preload(),
                    TextInput::make('orden_visita')->label('Orden dentro de la ruta')->numeric()->minValue(0),
                ]),

            Section::make('Ubicación y contacto')
                ->columns(2)
                ->schema([
                    TextInput::make('direccion')->label('Dirección')->maxLength(200),
                    TextInput::make('barrio')->label('Barrio')->maxLength(100),
                    TextInput::make('ciudad')->label('Ciudad')->default('Santa Marta')->maxLength(80),
                    TextInput::make('telefono')->label('Teléfono')->tel()->maxLength(30),
                    TextInput::make('whatsapp')->label('WhatsApp')->tel()->maxLength(30),
                    Textarea::make('referencia_ubicacion')->label('Referencia para llegar')->rows(2)->columnSpanFull(),
                    TextInput::make('latitud')->numeric()->minValue(-90)->maxValue(90),
                    TextInput::make('longitud')->numeric()->minValue(-180)->maxValue(180),
                ]),

            Section::make('Documento y crédito')
                ->columns(2)
                ->schema([
                    Select::make('tipo_documento')->label('Tipo de documento')->native(false)->options([
                        'CC' => 'Cédula', 'NIT' => 'NIT', 'CE' => 'Cédula de extranjería', 'PAS' => 'Pasaporte', 'OTRO' => 'Otro',
                    ]),
                    TextInput::make('documento')->label('Número de documento')->maxLength(30),
                    Toggle::make('permite_credito')->label('Permite crédito')->live(),
                    TextInput::make('cupo_credito')->label('Cupo de crédito')->numeric()->prefix('$')->minValue(0)->default(0)
                        ->hidden(fn (Get $get): bool => ! $get('permite_credito')),
                    TextInput::make('dias_credito')->label('Días de crédito')->numeric()->minValue(0)->default(0)
                        ->hidden(fn (Get $get): bool => ! $get('permite_credito')),
                ]),

            Section::make('Notas')
                ->schema([
                    Textarea::make('notas')->hiddenLabel()->rows(3),
                ]),
        ]);
    }
}
