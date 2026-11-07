<?php

namespace App\Filament\Resources\Descuentos\Pages;

use App\Filament\Resources\Descuentos\DescuentoResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditDescuento extends EditRecord
{
    protected static string $resource = DescuentoResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make(), RestoreAction::make()];
    }

    /** Al abrir el formulario, deduce a que aplica el descuento segun lo guardado. */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['aplica_a'] = filled($data['producto_id'] ?? null)
            ? 'producto'
            : (filled($data['categoria_id'] ?? null) ? 'categoria' : 'todos');

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return DescuentoResource::normalizar($data);
    }
}
