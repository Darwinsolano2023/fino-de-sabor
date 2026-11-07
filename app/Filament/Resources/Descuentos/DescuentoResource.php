<?php

namespace App\Filament\Resources\Descuentos;

use App\Filament\Concerns\UsaPermisos;
use App\Filament\Resources\Descuentos\Pages\CreateDescuento;
use App\Filament\Resources\Descuentos\Pages\EditDescuento;
use App\Filament\Resources\Descuentos\Pages\ListDescuentos;
use App\Filament\Resources\Descuentos\Schemas\DescuentoForm;
use App\Filament\Resources\Descuentos\Tables\DescuentosTable;
use App\Models\DescuentoVolumen;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class DescuentoResource extends Resource
{
    use UsaPermisos;

    protected static ?string $model = DescuentoVolumen::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedReceiptPercent;

    protected static string|UnitEnum|null $navigationGroup = 'Catálogo';

    protected static ?int $navigationSort = 3;

    protected static ?string $modelLabel = 'descuento';

    protected static ?string $pluralModelLabel = 'descuentos por volumen';

    protected static ?string $recordTitleAttribute = 'nombre';

    protected static function modulo(): string
    {
        return 'descuentos';
    }

    // El modulo de descuentos solo tiene "ver" y "editar": crear y eliminar quedan bajo "editar".
    protected static function accion(string $accion): string
    {
        return in_array($accion, ['crear', 'eliminar'], true) ? 'editar' : $accion;
    }

    public static function form(Schema $schema): Schema
    {
        return DescuentoForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DescuentosTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListDescuentos::route('/'),
            'create' => CreateDescuento::route('/create'),
            'edit'   => EditDescuento::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([SoftDeletingScope::class])
            ->with(['producto', 'categoria', 'escalas']);
    }

    /**
     * El campo "aplica_a" solo existe en el formulario. Segun su valor se deja
     * el producto, la categoria o ninguno (todos los productos), y se limpia el resto.
     */
    public static function normalizar(array $data): array
    {
        $aplicaA = $data['aplica_a'] ?? 'todos';
        unset($data['aplica_a']);

        if ($aplicaA !== 'producto') {
            $data['producto_id'] = null;
        }
        if ($aplicaA !== 'categoria') {
            $data['categoria_id'] = null;
        }

        return $data;
    }
}
