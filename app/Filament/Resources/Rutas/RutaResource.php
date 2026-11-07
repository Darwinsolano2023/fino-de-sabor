<?php

namespace App\Filament\Resources\Rutas;

use App\Filament\Concerns\UsaPermisos;
use App\Filament\Resources\Rutas\Pages\CreateRuta;
use App\Filament\Resources\Rutas\Pages\EditRuta;
use App\Filament\Resources\Rutas\Pages\ListRutas;
use App\Filament\Resources\Rutas\Schemas\RutaForm;
use App\Filament\Resources\Rutas\Tables\RutasTable;
use App\Models\Ruta;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class RutaResource extends Resource
{
    use UsaPermisos;

    protected static ?string $model = Ruta::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMap;

    protected static string|UnitEnum|null $navigationGroup = 'Ventas';

    protected static ?int $navigationSort = 2;

    protected static ?string $modelLabel = 'ruta';

    protected static ?string $pluralModelLabel = 'rutas';

    protected static ?string $recordTitleAttribute = 'nombre';

    protected static function modulo(): string
    {
        return 'rutas';
    }

    // El modulo de rutas solo tiene los permisos "ver" y "editar":
    // crear y eliminar quedan bajo "editar".
    protected static function accion(string $accion): string
    {
        return in_array($accion, ['crear', 'eliminar'], true) ? 'editar' : $accion;
    }

    public static function form(Schema $schema): Schema
    {
        return RutaForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RutasTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListRutas::route('/'),
            'create' => CreateRuta::route('/create'),
            'edit'   => EditRuta::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withoutGlobalScopes([SoftDeletingScope::class]);
    }
}
