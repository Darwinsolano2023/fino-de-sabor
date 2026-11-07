<?php

namespace App\Filament\Resources\Productos\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ProductoForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Producto')
                ->columns(2)
                ->schema([
                    TextInput::make('codigo')->label('Código')->required()->maxLength(30)->unique(ignoreRecord: true),
                    TextInput::make('codigo_barras')->label('Código de barras')->maxLength(40),
                    TextInput::make('nombre')->required()->maxLength(150)->columnSpanFull(),
                    Select::make('categoria_id')->label('Categoría')->relationship('categoria', 'nombre')->searchable()->preload(),
                    Select::make('presentacion')->label('Presentación')->native(false)->default('papeleta')->options([
                        'papeleta' => 'Papeleta', 'bolsa' => 'Bolsa', 'frasco' => 'Frasco',
                        'libra' => 'Libra', 'kilo' => 'Kilo', 'unidad' => 'Unidad',
                    ]),
                    TextInput::make('contenido_gramos')->label('Contenido (gramos)')->numeric()->minValue(0),
                    TextInput::make('unidad_medida')->label('Unidad de medida')->default('und')->maxLength(10),
                    Textarea::make('descripcion')->label('Descripción')->rows(2)->columnSpanFull(),
                ]),

            Section::make('Precios y stock')
                ->columns(3)
                ->schema([
                    TextInput::make('precio_venta')->label('Precio de venta')->numeric()->prefix('$')->minValue(0)->default(0)->required(),
                    TextInput::make('precio_costo')->label('Precio de costo')->numeric()->prefix('$')->minValue(0)->default(0)
                        ->visible(fn (): bool => (bool) auth()->user()?->can('productos.ver_costo')),
                    TextInput::make('stock_minimo')->label('Stock mínimo')->numeric()->minValue(0)->default(0)
                        ->helperText('Avisa cuando el stock llega a esta cantidad.'),
                ]),

            Section::make('Foto')
                ->schema([
                    FileUpload::make('imagen_url')
                        ->hiddenLabel()
                        ->image()
                        ->disk('public')
                        ->directory('productos')
                        ->maxSize(2048)
                        ->helperText('JPG o PNG, máximo 2 MB.'),
                    Toggle::make('activo')->label('Activo')->default(true),
                ]),
        ]);
    }
}
