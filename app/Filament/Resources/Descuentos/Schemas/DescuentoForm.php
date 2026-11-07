<?php

namespace App\Filament\Resources\Descuentos\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class DescuentoForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Descuento')
                ->columns(2)
                ->schema([
                    TextInput::make('nombre')->required()->maxLength(120)->columnSpanFull()
                        ->placeholder('Ej. Papeletas por volumen'),

                    Select::make('aplica_a')->label('Aplica a')->native(false)->live()->default('todos')
                        ->options([
                            'todos'     => 'Todos los productos',
                            'categoria' => 'Una categoría',
                            'producto'  => 'Un producto',
                        ]),

                    Select::make('categoria_id')->label('Categoría')
                        ->relationship('categoria', 'nombre')->searchable()->preload()
                        ->required(fn (Get $get): bool => $get('aplica_a') === 'categoria')
                        ->visible(fn (Get $get): bool => $get('aplica_a') === 'categoria'),

                    Select::make('producto_id')->label('Producto')
                        ->relationship('producto', 'nombre')->searchable()->preload()
                        ->required(fn (Get $get): bool => $get('aplica_a') === 'producto')
                        ->visible(fn (Get $get): bool => $get('aplica_a') === 'producto'),

                    DatePicker::make('vigente_desde')->label('Vigente desde')->native(false)->displayFormat('d/m/Y')
                        ->helperText('Déjalo vacío si no tiene fecha de inicio.'),
                    DatePicker::make('vigente_hasta')->label('Vigente hasta')->native(false)->displayFormat('d/m/Y')
                        ->afterOrEqual('vigente_desde')
                        ->helperText('Déjalo vacío si no vence.'),

                    Toggle::make('activo')->label('Activo')->default(true),
                ]),

            Section::make('Escalas por volumen')
                ->description('Desde qué cantidad se aplica y cuánto baja el precio. Si varias escalas aplican a la vez, se usa la que deja el precio más bajo.')
                ->schema([
                    Repeater::make('escalas')
                        ->hiddenLabel()
                        ->relationship('escalas', modifyQueryUsing: fn (Builder $query) => $query->orderBy('cantidad_minima'))
                        ->minItems(1)
                        ->defaultItems(1)
                        ->addActionLabel('Agregar escala')
                        ->itemLabel(fn (array $state): ?string => filled($state['cantidad_minima'] ?? null)
                            ? 'Desde ' . $state['cantidad_minima'] . ' unidades'
                            : null)
                        ->columns(3)
                        ->schema([
                            TextInput::make('cantidad_minima')->label('Desde (unidades)')
                                ->numeric()->required()->minValue(1)->distinct(),
                            Select::make('tipo')->label('Tipo de descuento')->native(false)->live()->required()->default('precio_fijo')
                                ->options([
                                    'precio_fijo' => 'Precio fijo por unidad ($)',
                                    'porcentaje'  => 'Porcentaje de descuento (%)',
                                ]),
                            TextInput::make('valor')->numeric()->required()->minValue(0)
                                ->maxValue(fn (Get $get): ?int => $get('tipo') === 'porcentaje' ? 100 : null)
                                ->prefix(fn (Get $get): ?string => $get('tipo') === 'precio_fijo' ? '$' : null)
                                ->suffix(fn (Get $get): ?string => $get('tipo') === 'porcentaje' ? '%' : null),
                        ]),
                ]),
        ]);
    }
}
