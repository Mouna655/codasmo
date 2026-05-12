<?php

namespace App\Filament\Resources\ShipmentImportResource\Pages;

use App\Filament\Resources\ShipmentImportResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditShipmentImport extends EditRecord
{
    protected static string $resource = ShipmentImportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
