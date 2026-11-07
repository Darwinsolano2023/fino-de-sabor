<?php

namespace App\Filament\Resources\Descuentos\Pages;

use App\Filament\Resources\Descuentos\DescuentoResource;
use App\Models\Producto;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\DB;

class ListDescuentos extends ListRecords
{
    protected static string $resource = DescuentoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Calculadora: muestra el precio exacto que el sistema cobraria por una cantidad.
            Action::make('probar')
                ->label('Probar precio')
                ->icon('heroicon-o-calculator')
                ->color('gray')
                ->modalHeading('Probar precio por volumen')
                ->modalSubmitActionLabel('Calcular')
                ->schema([
                    Select::make('producto_id')->label('Producto')->required()->searchable()
                        ->options(fn (): array => Producto::query()->orderBy('nombre')->pluck('nombre', 'id')->all()),
                    TextInput::make('cantidad')->label('Cantidad')->numeric()->required()->minValue(1)->default(30),
                ])
                ->action(function (array $data): void {
                    $r = DB::selectOne('select * from precio_por_volumen(?, ?)', [$data['producto_id'], $data['cantidad']]);

                    $pesos = fn ($v): string => '$ ' . number_format((float) $v, 0, ',', '.');
                    $total = (float) $r->precio_unitario * (float) $data['cantidad'];

                    Notification::make()
                        ->title($r->descuento_id ? 'Se aplica un descuento' : 'No aplica ningún descuento')
                        ->body("Precio de lista: {$pesos($r->precio_base)} · Precio por unidad: {$pesos($r->precio_unitario)} · Total: {$pesos($total)}")
                        ->color($r->descuento_id ? 'success' : 'gray')
                        ->persistent()
                        ->send();
                }),
            CreateAction::make(),
        ];
    }
}
