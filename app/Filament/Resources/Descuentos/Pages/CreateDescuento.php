<?php

namespace App\Filament\Resources\Descuentos\Pages;

use App\Filament\Resources\Descuentos\DescuentoResource;
use Filament\Resources\Pages\CreateRecord;

class CreateDescuento extends CreateRecord
{
    protected static string $resource = DescuentoResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return DescuentoResource::normalizar($data);
    }
}
